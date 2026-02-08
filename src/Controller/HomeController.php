<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
}
