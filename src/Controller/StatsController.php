<?php

namespace App\Controller;

use App\Service\StatsService;
use App\Entity\Equipe;
use App\Service\QuickChartApi;
use Dompdf\Dompdf;
use Dompdf\Options;
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
public function equipeStats(
    Equipe $equipe,
    StatsService $statsService,
    QuickChartApi $chartApi
): Response
{
    $stats = $statsService->getStatsEquipe($equipe);

    // appel API externe
    $chart = $chartApi->generatePerformanceChart($stats, $equipe->getNom());

    return $this->render('stats/equipe.html.twig', [
        'stats' => $stats,
        'chart' => $chart
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









    #[Route('/stats/pdf', name: 'app_stats_pdf')]
    public function downloadPdf(
        StatsService $statsService,
        QuickChartApi $chartApi
    ): Response
    {
        $classement = $statsService->getClassementEquipes();
    
        $top = $classement[0];
        $chart = $chartApi->generatePerformanceChart($top, $top['equipe']->getNom());
    
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setIsRemoteEnabled(true);
    
        $dompdf = new Dompdf($options);
    
        $html = $this->renderView('stats/pdf.html.twig', [
            'classement' => $classement,
            'chart' => $chart,
            'date' => new \DateTime()
        ]);
    
        $dompdf->loadHtml($html);
        $dompdf->render();
    
        return new Response($dompdf->stream("classement.pdf", ["Attachment"=>true]));
    }
    







 
}