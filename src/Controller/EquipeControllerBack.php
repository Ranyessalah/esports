<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/equipe')]
final class EquipeControllerBack extends AbstractController
{
    #[Route(name: 'app_equipe_index_admin', methods: ['GET'])]
    public function index(EquipeRepository $equipeRepository): Response
    {
        return $this->render('equipe/admin/index.html.twig', [
            'equipes' => $equipeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_equipe_new_admin', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $logoFile = $form->get('logoFile')->getData();
        if ($logoFile) {
            $newName = uniqid().'.'.$logoFile->guessExtension();
            $logoFile->move($this->getParameter('kernel.project_dir').'/public/uploads/logos/', $newName);
            $equipe->setLogo('/uploads/logos/'.$newName);
        }
            $entityManager->persist($equipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_equipe_index_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe/admin/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_show_admin', methods: ['GET'])]
    public function show(Equipe $equipe): Response
    {
        return $this->render('equipe/admin/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_equipe_edit_admin', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_equipe_index_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe/admin/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_delete_admin', methods: ['POST'])]
    public function delete(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$equipe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_equipe_index_admin', [], Response::HTTP_SEE_OTHER);
    }
}
