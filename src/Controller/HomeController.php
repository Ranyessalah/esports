<?php

namespace App\Controller;

use App\Repository\TrainingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TrainingRepository $trainingRepository): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_COACH')) {
                return $this->redirectToRoute('app_admin_dashboard');
            }
            
            // Player Specific Dashboard View
            $upcomingTrainings = $trainingRepository->createQueryBuilder('t')
                ->where('t.date >= :today')
                ->setParameter('today', new \DateTime())
                ->orderBy('t.date', 'ASC')
                ->setMaxResults(3)
                ->getQuery()
                ->getResult();

            return $this->render('home/player_dashboard.html.twig', [
                'upcomingTrainings' => $upcomingTrainings
            ]);
        }

        return $this->render('home/index.html.twig');
    }
}
