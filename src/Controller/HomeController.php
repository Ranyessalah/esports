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
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function root(): Response
    {
        // Si connecté et admin → backoffice
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('backoffice_home');
        }

        // Sinon → home
        return $this->redirectToRoute('app_home');
    }

    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
    #[Route('/backoffice', name: 'backoffice_home')]
    public function backOffice(UserRepository $userRepository): Response
    {
        // Récupérer tous les utilisateurs
        $users = $userRepository->findAll();

        // Calculer les statistiques
        $totalUsers = count($users);
        $coaches = count(array_filter($users, fn($u) => in_array('ROLE_COACH', $u->getRoles())));
        $players = count(array_filter($users, fn($u) => in_array('ROLE_PLAYER', $u->getRoles())));
        $admins = count(array_filter($users, fn($u) => in_array('ROLE_ADMIN', $u->getRoles())));

        // Récupérer les derniers utilisateurs (limité à 10)
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 10);

        return $this->render('backoffice/index.html.twig', [
            'recentUsers' => $recentUsers,
            'stats' => [
                'total' => $totalUsers,
                'coaches' => $coaches,
                'players' => $players,
                'admins' => $admins,
            ]
        ]);
    }

    

    #[Route('/home/profile', name: 'app_home_profile')]
    public function profile(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user instanceof Player) {
            $form = $this->createForm(PlayerType::class, $user, ['is_edit' => true]);
        } elseif ($user instanceof Coach) {
            $form = $this->createForm(CoachType::class, $user, ['is_edit' => true]);
        } else {
            return $this->redirectToRoute('app_home');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }

            $em->flush();
            $this->addFlash('profile_success', 'Vos informations ont été mises à jour avec succès.');

            return $this->redirectToRoute('app_home_profile');
        }

        // Restore original email if validation failed to keep the security token valid
        if ($form->isSubmitted() && !$form->isValid()) {
            $em->refresh($user);
        }

        return $this->render('home/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}
