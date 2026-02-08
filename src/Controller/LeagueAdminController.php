<?php

namespace App\Controller;

use App\Entity\League;
use App\Form\League1Type;
use App\Repository\LeagueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/league/admin')]
final class LeagueAdminController extends AbstractController
{
    #[Route(name: 'app_league_admin_index', methods: ['GET'])]
    public function index(LeagueRepository $leagueRepository): Response
    {
        return $this->render('league_admin/index.html.twig', [
            'leagues' => $leagueRepository->findAll(),
        ]);
    }
    

    #[Route('/new', name: 'app_league_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $league = new League();
        $form = $this->createForm(League1Type::class, $league);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($league);
            $entityManager->flush();

            return $this->redirectToRoute('app_league_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('league_admin/new.html.twig', [
            'league' => $league,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_league_admin_show', methods: ['GET'])]
    public function show(League $league): Response
    {
        return $this->render('league_admin/show.html.twig', [
            'league' => $league,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_league_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, League $league, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(League1Type::class, $league);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_league_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('league_admin/edit.html.twig', [
            'league' => $league,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_league_admin_delete', methods: ['POST'])]
    public function delete(Request $request, League $league, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$league->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($league);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_league_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
