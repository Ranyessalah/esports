<?php

namespace App\Controller;

use App\Repository\TrainingRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_COACH')]
class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(Request $request, TrainingRepository $trainingRepository, UserRepository $userRepository): Response
    {
        $search = $request->query->get('search');
        $theme = $request->query->get('theme');
        $date = $request->query->get('date');

        $qb = $trainingRepository->createQueryBuilder('t');

        if ($search) {
            $qb->andWhere('t.title LIKE :search OR t.location LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($theme) {
            $qb->andWhere('t.theme = :theme')
               ->setParameter('theme', $theme);
        }

        if ($date) {
            $qb->andWhere('t.date = :date')
               ->setParameter('date', $date);
        }

        $trainings = $qb->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();

        $totalTrainings = $trainingRepository->count([]);
        
        // Count players (ROLE_PLAYER)
        $players = $userRepository->findAll();
        $totalPlayers = 0;
        foreach ($players as $user) {
            if (in_array('ROLE_PLAYER', $user->getRoles())) {
                $totalPlayers++;
            }
        }

        $upcomingCount = $trainingRepository->createQueryBuilder('t')
            ->select('count(t.id)')
            ->where('t.date >= :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();

        // Get unique themes for filter
        $themes = $trainingRepository->createQueryBuilder('t')
            ->select('DISTINCT t.theme')
            ->getQuery()
            ->getScalarResult();

        return $this->render('dashboard/index.html.twig', [
            'totalTrainings' => $totalTrainings,
            'totalPlayers' => $totalPlayers,
            'upcomingCount' => $upcomingCount,
            'trainings' => $trainings,
            'themes' => array_column($themes, 'theme'),
            'currentFilters' => [
                'search' => $search,
                'theme' => $theme,
                'date' => $date,
            ]
        ]);
    }
}
