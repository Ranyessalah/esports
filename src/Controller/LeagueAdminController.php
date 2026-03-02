<?php

namespace App\Controller;

use App\Entity\League;
use App\Form\League1Type;
use App\Repository\LeagueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/league/admin')]
final class LeagueAdminController extends AbstractController
{
    // ══════════════════════════════════════════════════════════════════════════
    // IMPORTANT: Static routes (/new, /calendar, /statistics, /export/pdf)
    // MUST be declared BEFORE the /{id} wildcard routes, otherwise Symfony
    // will try to resolve "calendar", "statistics", etc. as entity IDs → 404.
    // ══════════════════════════════════════════════════════════════════════════

    // ─── Index ────────────────────────────────────────────────────────────────

    #[Route('', name: 'app_league_admin_index', methods: ['GET'])]
    public function index(
        LeagueRepository $leagueRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $search = $request->query->get('search', '');

        $qb = $leagueRepository->createQueryBuilder('l');

        if ($search) {
            $qb->where('l.name LIKE :s OR l.game LIKE :s OR l.status LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('league_admin/index.html.twig', [
            'leagues'     => $pagination,
            'searchQuery' => $search,
        ]);
    }

    // ─── New (static path) ────────────────────────────────────────────────────

    #[Route('/new', name: 'app_league_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $league = new League();
        $form   = $this->createForm(League1Type::class, $league);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($league);
            $entityManager->flush();
            $this->addFlash('success', 'League created successfully!');
            return $this->redirectToRoute('app_league_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('league_admin/new.html.twig', [
            'league' => $league,
            'form'   => $form,
        ]);
    }

    // ─── Calendar (static path) ───────────────────────────────────────────────

    #[Route('/calendar', name: 'app_league_admin_calendar', methods: ['GET'])]
    public function calendar(LeagueRepository $leagueRepository): Response
    {
        $leagues = $leagueRepository->findAll();

        return $this->render('league_admin/calendar.html.twig', [
            'leagues' => $leagues,
        ]);
    }

    // ─── Statistics (static path) ─────────────────────────────────────────────

    #[Route('/statistics', name: 'app_league_admin_statistics', methods: ['GET'])]
    public function statistics(LeagueRepository $leagueRepository): Response
    {
        $leagues = $leagueRepository->findAll();

        $byStatus  = [];
        $byGame    = [];
        $prizeData = [];
        $teamsData = [];

        foreach ($leagues as $league) {
            $status = $league->getStatus() ?? 'unknown';
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;

            $game = $league->getGame() ?? 'unknown';
            $byGame[$game] = ($byGame[$game] ?? 0) + 1;

            $prizeData[$league->getName()] = $league->getPrizePool();
            $teamsData[$league->getName()] = $league->getNumTeams();
        }

        arsort($prizeData);
        $topPrize = array_slice($prizeData, 0, 10, true);

        return $this->render('league_admin/statistics.html.twig', [
            'byStatus'  => $byStatus,
            'byGame'    => $byGame,
            'topPrize'  => $topPrize,
            'teamsData' => $teamsData,
            'total'     => count($leagues),
        ]);
    }

    // ─── Export PDF (static path) ─────────────────────────────────────────────

    #[Route('/export/pdf', name: 'app_league_admin_export_pdf', methods: ['GET'])]
    public function exportPdf(LeagueRepository $leagueRepository): Response
    {
        $leagues = $leagueRepository->findAll();

        $html = $this->renderView('league_admin/pdf.html.twig', [
            'leagues' => $leagues,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="leagues_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }

    // ─── Show (/{id} wildcard — after all static routes) ─────────────────────

    #[Route('/{id}', name: 'app_league_admin_show', methods: ['GET'])]
    public function show(League $league): Response
    {
        return $this->render('league_admin/show.html.twig', [
            'league' => $league,
        ]);
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    #[Route('/{id}/edit', name: 'app_league_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, League $league, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(League1Type::class, $league);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'League updated successfully!');
            return $this->redirectToRoute('app_league_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('league_admin/edit.html.twig', [
            'league' => $league,
            'form'   => $form,
        ]);
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    #[Route('/{id}', name: 'app_league_admin_delete', methods: ['POST'])]
    public function delete(Request $request, League $league, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $league->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($league);
            $entityManager->flush();
            $this->addFlash('success', 'League deleted successfully!');
        }

        return $this->redirectToRoute('app_league_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}