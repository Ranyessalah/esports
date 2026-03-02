<?php

namespace App\Controller;

use App\Entity\Fixture;
use App\Form\Fixture1Type;
use App\Repository\FixtureRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/fixture/admin')]
final class FixtureAdminController extends AbstractController
{
    // ══════════════════════════════════════════════════════════════════════════
    // IMPORTANT: Static routes (/new, /calendar, /statistics, /export/pdf)
    // MUST be declared BEFORE the /{id} wildcard routes, otherwise Symfony
    // will try to resolve "calendar", "statistics", etc. as entity IDs → 404.
    // ══════════════════════════════════════════════════════════════════════════

    // ─── Index ────────────────────────────────────────────────────────────────

    #[Route('', name: 'app_fixture_admin_index', methods: ['GET'])]
    public function index(
        FixtureRepository $fixtureRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $search = $request->query->get('search', '');

        $qb = $fixtureRepository->createQueryBuilder('f');

        if ($search) {
            $qb->where('f.status LIKE :s OR f.round LIKE :s')
               ->setParameter('s', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('fixture_admin/index.html.twig', [
            'fixtures'    => $pagination,
            'searchQuery' => $search,
        ]);
    }

    // ─── New (static path) ────────────────────────────────────────────────────

    #[Route('/new', name: 'app_fixture_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fixture = new Fixture();
        $form    = $this->createForm(Fixture1Type::class, $fixture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fixture);
            $entityManager->flush();
            $this->addFlash('success', 'Fixture created successfully!');
            return $this->redirectToRoute('app_fixture_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fixture_admin/new.html.twig', [
            'fixture' => $fixture,
            'form'    => $form,
        ]);
    }

    // ─── Calendar (static path) ───────────────────────────────────────────────

    #[Route('/calendar', name: 'app_fixture_admin_calendar', methods: ['GET'])]
    public function calendar(FixtureRepository $fixtureRepository): Response
    {
        $fixtures = $fixtureRepository->findAll();

        return $this->render('fixture_admin/calendar.html.twig', [
            'fixtures' => $fixtures,
        ]);
    }

    // ─── Statistics (static path) ─────────────────────────────────────────────

    #[Route('/statistics', name: 'app_fixture_admin_statistics', methods: ['GET'])]
    public function statistics(FixtureRepository $fixtureRepository): Response
    {
        $fixtures = $fixtureRepository->findAll();

        $byStatus = [];
        $byRound  = [];
        $byMonth  = [];

        foreach ($fixtures as $fixture) {
            $status = $fixture->getStatus() ?? 'unknown';
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;

            $round = 'Round ' . ($fixture->getRound() ?? '?');
            $byRound[$round] = ($byRound[$round] ?? 0) + 1;

            if ($fixture->getMatchDate()) {
                $month = $fixture->getMatchDate()->format('Y-m');
                $byMonth[$month] = ($byMonth[$month] ?? 0) + 1;
            }
        }

        ksort($byMonth);

        return $this->render('fixture_admin/statistics.html.twig', [
            'byStatus' => $byStatus,
            'byRound'  => $byRound,
            'byMonth'  => $byMonth,
            'total'    => count($fixtures),
        ]);
    }

    // ─── Export PDF (static path) ─────────────────────────────────────────────

    #[Route('/export/pdf', name: 'app_fixture_admin_export_pdf', methods: ['GET'])]
    public function exportPdf(FixtureRepository $fixtureRepository): Response
    {
        $fixtures = $fixtureRepository->findAll();

        $html = $this->renderView('fixture_admin/pdf.html.twig', [
            'fixtures' => $fixtures,
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
                'Content-Disposition' => 'attachment; filename="fixtures_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }

    // ─── Show (/{id} wildcard — after all static routes) ─────────────────────

    #[Route('/{id}', name: 'app_fixture_admin_show', methods: ['GET'])]
    public function show(Fixture $fixture): Response
    {
        return $this->render('fixture_admin/show.html.twig', [
            'fixture' => $fixture,
        ]);
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    #[Route('/{id}/edit', name: 'app_fixture_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fixture $fixture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Fixture1Type::class, $fixture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Fixture updated successfully!');
            return $this->redirectToRoute('app_fixture_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fixture_admin/edit.html.twig', [
            'fixture' => $fixture,
            'form'    => $form,
        ]);
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    #[Route('/{id}', name: 'app_fixture_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Fixture $fixture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $fixture->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($fixture);
            $entityManager->flush();
            $this->addFlash('success', 'Fixture deleted successfully!');
        }

        return $this->redirectToRoute('app_fixture_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}