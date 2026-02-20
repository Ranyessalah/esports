<?php

namespace App\Controller;

use App\Service\StatsService;
use App\Entity\Equipe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app_stats')]
    public function index(StatsService $statsService): Response
    {
        $classement = $statsService->getClassementEquipes();
        
        return $this->render('stats/index.html.twig', [
            'classement' => $classement,
            'total_equipes' => count($classement)
        ]);
    }
    
    #[Route('/stats/equipe/{id}', name: 'app_stats_equipe')]
    public function equipeStats(Equipe $equipe, StatsService $statsService): Response
    {
        $stats = $statsService->getStatsEquipe($equipe);
        
        return $this->render('stats/equipe.html.twig', [
            'stats' => $stats
        ]);
    }
    #[Route('/stats/equipe/client/{id}', name: 'app_stats_equipeclient')]
    public function equipeStatsclient(Equipe $equipe, StatsService $statsService): Response
    {
        $stats = $statsService->getStatsEquipe($equipe);
        
        return $this->render('stats/equipe_client.html.twig', [
            'stats' => $stats
        ]);
    }
}