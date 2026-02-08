<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Player;
use App\Form\CoachType;
use App\Form\PlayerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // <-- CORRECT
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;

final class UserController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/signup_coach', name: 'signup_coach')]
public function signupCoach(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
{
    $coach = new Coach();
    $form = $this->createForm(CoachType::class, $coach);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $plainPassword = $form->get('plainPassword')->getData();
        $confirmPassword = $form->get('confirmPassword')->getData();

        if ($plainPassword !== $confirmPassword) {
            $form->get('confirmPassword')->addError(new FormError('Les mots de passe ne correspondent pas'));
        } else {
            $hashedPassword = $hasher->hashPassword($coach, $plainPassword);
            $coach->setPassword($hashedPassword);
            $coach->setRoles(['ROLE_COACH']);
            $em->persist($coach);
            $em->flush();

            return $this->redirectToRoute('app_home'); // ou la route souhaitée après signup
        }
    }

    return $this->render('user/signup_coach.html.twig', [
        'form' => $form->createView(),
    ]);
}


#[Route('/signup_player', name: 'signup_player')]
public function signupPlayer(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
{
    $player = new Player();
    $form = $this->createForm(PlayerType::class, $player);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $plainPassword = $form->get('plainPassword')->getData();
        $confirmPassword = $form->get('confirmPassword')->getData();

        if ($plainPassword !== $confirmPassword) {
            $form->get('confirmPassword')->addError(new FormError('Les mots de passe ne correspondent pas'));
        } else {
            $hashedPassword = $hasher->hashPassword($player, $plainPassword);
            $player->setPassword($hashedPassword);
            $player->setRoles(['ROLE_PLAYER']);
            $em->persist($player);
            $em->flush();

            return $this->redirectToRoute('app_home');
        }
    }

    return $this->render('user/signup_player.html.twig', ['form' => $form->createView()]);
}

#[Route('/ajax-check-email', name: 'ajax_check_email', methods: ['POST'])]
public function checkEmail(Request $request, UserRepository $userRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $email = $data['email'] ?? null;
    
    // Validate email format
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $this->json([
            'exists' => false,
            'error' => 'Invalid email format'
        ], 400);
    }
    
    // Check if email exists in database
    $emailExists = $userRepository->findOneBy(['email' => $email]) !== null;
    
    return $this->json([
        'exists' => $emailExists,
    ]);
}
  #[Route('/edit/{id}', name: 'backoffice_user_edit')]
public function edit(
    User $user, 
    Request $request, 
    EntityManagerInterface $em,
    UserPasswordHasherInterface $passwordHasher  // ✅ Ajouté
): Response
{
    // Déterminer le formulaire selon le type
    if ($user instanceof Coach) {
        $form = $this->createForm(CoachType::class, $user);
    } elseif ($user instanceof Player) {
        $form = $this->createForm(PlayerType::class, $user);
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
