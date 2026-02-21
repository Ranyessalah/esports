<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TotpService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class TwoFactorController extends AbstractController
{
    #[Route('/2fa/setup', name: 'app_2fa_setup', methods: ['GET'])]
    public function setup(TotpService $totpService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Generate a new secret (don't save yet — only on confirm)
        $secret = $totpService->generateSecret();
        $provisioningUri = $totpService->getProvisioningUri($user, $secret);
        $qrCodeDataUri = $totpService->getQrCodeDataUri($provisioningUri);

        return $this->render('2fa/setup.html.twig', [
            'qrCode' => $qrCodeDataUri,
            'secret' => $secret,
        ]);
    }

    #[Route('/2fa/confirm', name: 'app_2fa_confirm', methods: ['POST'])]
    public function confirm(
        Request $request,
        TotpService $totpService,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $secret = $request->request->get('secret');
        $code = $request->request->get('code');

        if (!$secret || !$code) {
            $this->addFlash('error', 'Veuillez saisir le code à 6 chiffres.');
            return $this->redirectToRoute('app_2fa_setup');
        }

        if ($totpService->verifyCode($secret, $code)) {
            $user->setTotpSecret($secret);
            $user->setIsTotpEnabled(true);
            $em->flush();

            $this->addFlash('success', 'Authentification à deux facteurs activée avec succès !');

            // Redirect back to profile based on role
            return $this->redirectToProfile($user);
        }

        $this->addFlash('error', 'Code invalide. Veuillez réessayer.');
        return $this->redirectToRoute('app_2fa_setup');
    }

    #[Route('/2fa/disable', name: 'app_2fa_disable', methods: ['POST'])]
    public function disable(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $user->setTotpSecret(null);
        $user->setIsTotpEnabled(false);
        $em->flush();

        $this->addFlash('success', 'Authentification à deux facteurs désactivée.');
        return $this->redirectToProfile($user);
    }

    #[Route('/login/2fa-verify', name: 'app_login_2fa_verify', methods: ['GET', 'POST'])]
    public function loginVerify(
        Request $request,
        TotpService $totpService,
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorage
    ): Response {
        $session = $request->getSession();
        $pendingUserId = $session->get('2fa_pending_user_id');

        if (!$pendingUserId) {
            return $this->redirectToRoute('app_login');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $code = $request->request->get('totp_code', '');
            $secret = $session->get('2fa_pending_secret');

            if ($secret && $totpService->verifyCode($secret, $code)) {
                // Code valid — complete authentication
                $targetUrl = $session->get('2fa_target_url', '/');

                // Reload the user fresh from the database
                $user = $userRepository->find($pendingUserId);

                if (!$user) {
                    $session->invalidate();
                    return $this->redirectToRoute('app_login');
                }

                // Create a fresh authentication token
                $token = new PostAuthenticationToken(
                    $user,
                    'main',
                    $user->getRoles()
                );

                // Set the token in session and token storage
                $tokenStorage->setToken($token);
                $session->set('_security_main', serialize($token));

                // Clean up 2FA session data
                $session->remove('2fa_pending_user_id');
                $session->remove('2fa_pending_secret');
                $session->remove('2fa_target_url');
                $session->remove('2fa_pending_token');

                return $this->redirect($targetUrl);
            }

            $error = 'Code invalide. Veuillez réessayer.';
        }

        return $this->render('2fa/login_verify.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/login/2fa-cancel', name: 'app_login_2fa_cancel')]
    public function cancel(Request $request): Response
    {
        $session = $request->getSession();
        $session->remove('2fa_pending_user_id');
        $session->remove('2fa_pending_secret');
        $session->remove('2fa_target_url');
        $session->remove('2fa_pending_token');
        $session->invalidate();

        return $this->redirectToRoute('app_login');
    }

    private function redirectToProfile(User $user): Response
    {
        if (in_array('ROLE_COACH', $user->getRoles())) {
            return $this->redirectToRoute('profil_coach');
        }
        if (in_array('ROLE_PLAYER', $user->getRoles())) {
            return $this->redirectToRoute('profil_player');
        }
        return $this->redirectToRoute('app_home');
    }
}
