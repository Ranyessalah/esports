<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice', name: 'backoffice_')]
class BackofficeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(UserRepository $userRepository): Response
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
    
  #[Route('/user/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $em, Request $request): Response
    {
        // Vérifier le token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete-user-' . $id, $submittedToken)) {
            $this->addFlash('error', 'Token de sécurité invalide');
            return $this->redirectToRoute('backoffice_home');
        }
        
        // Trouver l'utilisateur
        $user = $userRepository->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé');
            return $this->redirectToRoute('backoffice_home');
        }
        
        // Empêcher la suppression de son propre compte
        if ($this->getUser() && $user->getId() === $this->getUser()->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte');
            return $this->redirectToRoute('backoffice_home');
        }
        
        try {
            // Supprimer l'utilisateur
            $userEmail = $user->getEmail();
            $em->remove($user);
            $em->flush();
            
            $this->addFlash('success', "L'utilisateur {$userEmail} a été supprimé avec succès");
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('backoffice_home');
    }
    
 
}