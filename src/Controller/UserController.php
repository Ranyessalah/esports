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

    #[Route('/signup_coach', name: 'signup_coach')]
    public function signupCoach(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, RecaptchaService $recaptchaService): Response
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
    public function signupPlayer(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, RecaptchaService $recaptchaService): Response
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
