<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Player;
use App\Form\CoachType;
use App\Form\PlayerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RecaptchaService;
use App\Service\FacePlusPlusService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;

final class UserController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google')]
    public function connectGoogle(Request $request, ClientRegistry $clientRegistry): Response
    {
        $type = $request->query->get('type');
        if (in_array($type, ['player', 'coach'], true)) {
            $request->getSession()->set('google_signup_type', $type);
        }

        return $clientRegistry->getClient('google')->redirect(['email', 'profile'], []);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectGoogleCheck(): Response
    {
        throw new \LogicException('This should be handled by the authenticator.');
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'recaptcha_site_key' => $this->getParameter('recaptcha.site_key'),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/login/faceid', name: 'app_login_faceid', methods: ['GET', 'POST'])]
    public function loginFaceId(
        Request $request,
        UserRepository $userRepository,
        FacePlusPlusService $facePlusPlusService
    ): Response {
        $error = null;

        if ($request->isMethod('POST')) {
            $faceImage = $request->files->get('face_image');
            $faceBase64 = $request->request->get('face_base64');

            $projectDir = $this->getParameter('kernel.project_dir');
            $loginImagePath = null;
            $tempFile = false;

            if ($faceImage) {
                $newFilename = 'login-' . uniqid() . '.' . $faceImage->guessExtension();
                $tempDir = $projectDir . '/var/tmp';
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }
                $faceImage->move($tempDir, $newFilename);
                $loginImagePath = $tempDir . '/' . $newFilename;
                $tempFile = true;
            } elseif ($faceBase64) {
                // Decode base64 camera capture
                $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $faceBase64);
                $imageData = base64_decode($base64Data);
                if ($imageData) {
                    $tempDir = $projectDir . '/var/tmp';
                    if (!is_dir($tempDir)) {
                        mkdir($tempDir, 0777, true);
                    }
                    $loginImagePath = $tempDir . '/login-' . uniqid() . '.jpg';
                    file_put_contents($loginImagePath, $imageData);
                    $tempFile = true;
                }
            }

            if (!$loginImagePath) {
                $error = 'Veuillez fournir une photo (capture caméra ou téléchargement).';
            } else {
                // Search all users with a profile image and compare via Face++
                $result = $facePlusPlusService->findMatchingUser($loginImagePath, $userRepository, $projectDir);

                // Clean up temp file
                if ($tempFile && file_exists($loginImagePath)) {
                    unlink($loginImagePath);
                }

                if ($result['error']) {
                    $error = $result['error'];
                } elseif (!$result['user']) {
                    $error = 'Aucun utilisateur reconnu par Face ID. Veuillez réessayer ou utiliser la connexion classique.';
                } else {
                    // Face match found! Manually authenticate the user
                    return $this->loginUserManually($result['user'], $request);
                }
            }
        }

        return $this->render('user/login_faceid.html.twig', [
            'error' => $error,
        ]);
    }

    /**
     * Manually authenticate a user (for Face ID login).
     */
    private function loginUserManually(User $user, Request $request): Response
    {
        // If user has 2FA enabled, redirect to TOTP verification
        if ($user->isTotpEnabled() && $user->getTotpSecret()) {
            $token = new \Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken(
                $user,
                'main',
                $user->getRoles()
            );

            $session = $request->getSession();
            $session->set('2fa_pending_user_id', $user->getId());
            $session->set('2fa_pending_secret', $user->getTotpSecret());
            $session->set('2fa_pending_token', serialize($token));

            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $session->set('2fa_target_url', $this->generateUrl('backoffice_home'));
            } else {
                $session->set('2fa_target_url', $this->generateUrl('app_home'));
            }

            return $this->redirectToRoute('app_login_2fa_verify');
        }

        $token = new \Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken(
            $user,
            'main',
            $user->getRoles()
        );

        $this->container->get('security.token_storage')->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));

        // Redirect based on role
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('backoffice_home');
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/signup_coach', name: 'signup_coach')]
    public function signupCoach(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, RecaptchaService $recaptchaService, SluggerInterface $slugger): Response
    {
        $coach = new Coach();
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);
        $recaptchaError = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            if (!$recaptchaService->verify($recaptchaResponse)) {
                $recaptchaError = 'Veuillez valider le reCAPTCHA.';
            } else {
                $plainPassword = $form->get('plainPassword')->getData();
                $coach->setPassword($hasher->hashPassword($coach, $plainPassword));
                $coach->setRoles(['ROLE_COACH']);

                // Handle profile image upload
                $imageFile = $form->get('profileImage')->getData();
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $imageFile->move($uploadDir, $newFilename);
                    $coach->setProfileImage('uploads/profiles/' . $newFilename);
                }

                $em->persist($coach);
                $em->flush();

                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('user/signup_coach.html.twig', [
            'form' => $form->createView(),
            'recaptcha_site_key' => $this->getParameter('recaptcha.site_key'),
            'recaptcha_error' => $recaptchaError,
        ]);
    }


    #[Route('/signup_player', name: 'signup_player')]
    public function signupPlayer(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, RecaptchaService $recaptchaService, SluggerInterface $slugger): Response
    {
        $player = new Player();
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);
        $recaptchaError = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            if (!$recaptchaService->verify($recaptchaResponse)) {
                $recaptchaError = 'Veuillez valider le reCAPTCHA.';
            } else {
                $plainPassword = $form->get('plainPassword')->getData();
                $player->setPassword($hasher->hashPassword($player, $plainPassword));
                $player->setRoles(['ROLE_PLAYER']);

                // Handle profile image upload
                $imageFile = $form->get('profileImage')->getData();
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $imageFile->move($uploadDir, $newFilename);
                    $player->setProfileImage('uploads/profiles/' . $newFilename);
                }

                $em->persist($player);
                $em->flush();

                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('user/signup_player.html.twig', [
            'form' => $form->createView(),
            'recaptcha_site_key' => $this->getParameter('recaptcha.site_key'),
            'recaptcha_error' => $recaptchaError,
        ]);
    }

    #[Route('/edit/{id}', name: 'backoffice_user_edit')]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher  // ✅ Ajouté
    ): Response {
        if ($user instanceof Coach) {
            $form = $this->createForm(CoachType::class, $user, ['is_edit' => true]);
        } elseif ($user instanceof Player) {
            $form = $this->createForm(PlayerType::class, $user, ['is_edit' => true]);
        } else {
            $this->addFlash('error', 'Impossible de modifier cet utilisateur.');
            return $this->redirectToRoute('backoffice_home');  // ✅ Corrigé
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ NOUVEAU : Hachage du mot de passe
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
            }
            // ✅ FIN NOUVEAU

            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès !');
            return $this->redirectToRoute('backoffice_home');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
