<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ResetpasswordController extends AbstractController
{
    #[Route('/emailVerfication', name: 'app_emailVerfication', methods: ['GET', 'POST'])]
    public function emailVerfication(
        Request $request,
        UserRepository $userRepository,
        MailerService $mailerService
    ): Response {
        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email', ''));

            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Aucun compte associé à cet email.');
                return $this->render('resetPassword/emailVerfication.html.twig', [
                    'error' => 'Aucun compte associé à cet email.',
                    'lastEmail' => $email,
                ]);
            }

            $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            $session = $request->getSession();
            $session->set('reset_otp', $otp);
            $session->set('reset_email', $email);
            $session->set('reset_otp_expires', time() + 300); // 5 minutes

            $mailerService->sendEmail(
                $email,
                'Code de réinitialisation - ClutchX',
                '<div style="font-family:Inter,Arial,sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#1a1a2e;border-radius:16px;color:#fff;">
                    <h2 style="text-align:center;color:#a855f7;margin-bottom:8px;">ClutchX</h2>
                    <p style="text-align:center;color:rgba(255,255,255,0.6);font-size:14px;">Réinitialisation de mot de passe</p>
                    <div style="text-align:center;margin:28px 0;">
                        <div style="display:inline-block;background:linear-gradient(135deg,#7c3aed,#a855f7);padding:16px 32px;border-radius:12px;font-size:28px;font-weight:700;letter-spacing:8px;">' . $otp . '</div>
                    </div>
                    <p style="text-align:center;color:rgba(255,255,255,0.5);font-size:13px;">Ce code expire dans 5 minutes.<br>Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                </div>'
            );

            return $this->redirectToRoute('app_otpVerfication');
        }

        return $this->render('resetPassword/emailVerfication.html.twig', [
            'error' => null,
            'lastEmail' => '',
        ]);
    }

    #[Route('/otpVerfication', name: 'app_otpVerfication', methods: ['GET', 'POST'])]
    public function otpVerfication(Request $request): Response
    {
        $session = $request->getSession();

        if (!$session->has('reset_otp') || !$session->has('reset_email')) {
            return $this->redirectToRoute('app_emailVerfication');
        }

        if ($request->isMethod('POST')) {
            $otp1 = $request->request->get('otp1', '');
            $otp2 = $request->request->get('otp2', '');
            $otp3 = $request->request->get('otp3', '');
            $otp4 = $request->request->get('otp4', '');
            $enteredOtp = $otp1 . $otp2 . $otp3 . $otp4;

            $storedOtp = $session->get('reset_otp');
            $expiresAt = $session->get('reset_otp_expires');

            if (time() > $expiresAt) {
                $session->remove('reset_otp');
                $session->remove('reset_email');
                $session->remove('reset_otp_expires');

                return $this->render('resetPassword/emailVerfication.html.twig', [
                    'error' => 'Le code a expiré. Veuillez recommencer.',
                ]);
            }

            if ($enteredOtp !== $storedOtp) {
                return $this->render('resetPassword/otp.html.twig', [
                    'error' => 'Code incorrect. Veuillez réessayer.',
                ]);
            }

            // OTP is valid - allow password reset
            $session->set('reset_verified', true);
            $session->remove('reset_otp');
            $session->remove('reset_otp_expires');

            return $this->redirectToRoute('app_modifypassword');
        }

        return $this->render('resetPassword/otp.html.twig', [
            'error' => null,
        ]);
    }

    #[Route('/resetpassword', name: 'app_modifypassword', methods: ['GET', 'POST'])]
    public function modifypassword(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $session = $request->getSession();

        // Ensure the user completed OTP verification
        if (!$session->get('reset_verified') || !$session->has('reset_email')) {
            return $this->redirectToRoute('app_emailVerfication');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password', '');
            $confirmPassword = $request->request->get('confirmpassword', '');

            // Server-side validation
            if (empty($password) || empty($confirmPassword)) {
                return $this->render('resetPassword/modifyPassword.html.twig', [
                    'error' => 'Tous les champs sont requis.',
                ]);
            }

            if ($password !== $confirmPassword) {
                return $this->render('resetPassword/modifyPassword.html.twig', [
                    'error' => 'Les mots de passe ne correspondent pas.',
                ]);
            }

            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
                return $this->render('resetPassword/modifyPassword.html.twig', [
                    'error' => 'Le mot de passe ne respecte pas les normes requises.',
                ]);
            }

            $email = $session->get('reset_email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                return $this->redirectToRoute('app_emailVerfication');
            }

            // Hash and update password
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            // Clean up session
            $session->remove('reset_email');
            $session->remove('reset_verified');

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('resetPassword/modifyPassword.html.twig', [
            'error' => null,
        ]);
    }

    #[Route('/resend-otp', name: 'app_resend_otp', methods: ['GET'])]
    public function resendOtp(
        Request $request,
        UserRepository $userRepository,
        MailerService $mailerService
    ): Response {
        $session = $request->getSession();
        $email = $session->get('reset_email');

        if (!$email) {
            return $this->redirectToRoute('app_emailVerfication');
        }

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            return $this->redirectToRoute('app_emailVerfication');
        }

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $session->set('reset_otp', $otp);
        $session->set('reset_otp_expires', time() + 300);

        $mailerService->sendEmail(
            $email,
            'Nouveau code de réinitialisation - ClutchX',
            '<div style="font-family:Inter,Arial,sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#1a1a2e;border-radius:16px;color:#fff;">
                <h2 style="text-align:center;color:#a855f7;margin-bottom:8px;">ClutchX</h2>
                <p style="text-align:center;color:rgba(255,255,255,0.6);font-size:14px;">Nouveau code de réinitialisation</p>
                <div style="text-align:center;margin:28px 0;">
                    <div style="display:inline-block;background:linear-gradient(135deg,#7c3aed,#a855f7);padding:16px 32px;border-radius:12px;font-size:28px;font-weight:700;letter-spacing:8px;">' . $otp . '</div>
                </div>
                <p style="text-align:center;color:rgba(255,255,255,0.5);font-size:13px;">Ce code expire dans 5 minutes.</p>
            </div>'
        );

        return $this->redirectToRoute('app_otpVerfication');
    }
}
