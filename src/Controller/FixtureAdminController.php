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

#[Route('/fixture/admin')]
final class FixtureAdminController extends AbstractController
{
    #[Route(name: 'app_fixture_admin_index', methods: ['GET'])]
    public function index(FixtureRepository $fixtureRepository): Response
    {
        return $this->render('fixture_admin/index.html.twig', [
            'fixtures' => $fixtureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_fixture_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fixture = new Fixture();
        $form = $this->createForm(Fixture1Type::class, $fixture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->persist($fixture);
            $entityManager->flush();

            return $this->redirectToRoute('app_fixture_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fixture_admin/new.html.twig', [
            'fixture' => $fixture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fixture_admin_show', methods: ['GET'])]
    public function show(Fixture $fixture): Response
    {
        return $this->render('fixture_admin/show.html.twig', [
            'fixture' => $fixture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fixture_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fixture $fixture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Fixture1Type::class, $fixture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_fixture_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fixture_admin/edit.html.twig', [
            'fixture' => $fixture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fixture_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Fixture $fixture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fixture->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($fixture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_fixture_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
