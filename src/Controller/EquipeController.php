<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/equipe')]
final class EquipeController extends AbstractController
{
    #[Route( name: 'app_equipe_index', methods: ['GET'])]
public function index(Request $request, EquipeRepository $repo): Response
{
    $search = $request->query->get('search');
    $game   = $request->query->get('game');
    $sort   = $request->query->get('sort');

    $equipes = $repo->findAllWithSearch($search, $game, $sort);

    $template = $this->isGranted('ROLE_ADMIN') 
        ? 'equipe/admin/index.html.twig' 
        : 'equipe/index.html.twig';

    return $this->render($template, [
        'equipes' => $equipes,
        'search' => $search,
        'game' => $game,
        'sort' => $sort,
    ]);
}

    #[Route('/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $equipe = new Equipe();
    $form = $this->createForm(EquipeType::class, $equipe);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $logoFile = $form->get('logoFile')->getData();

        if ($logoFile) {
            $newFilename = uniqid() . '.' . $logoFile->guessExtension();

            // Utilise le paramètre défini dans services.yaml
            $logoFile->move(
                $this->getParameter('equipe_directory'),
                $newFilename
            );

            // On enregistre seulement le nom en base (meilleure pratique)
            $equipe->setLogo($newFilename);
        }else {
            $equipe->setLogo("default.jpg"); // logo par défaut si aucun fichier uploadé
        }

        $entityManager->persist($equipe);
        $entityManager->flush();

        return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('equipe/new.html.twig', [
        'equipe' => $equipe,
        'form' => $form->createView(), // ⚠ important
    ]);
}


#[Route('/{id}', name: 'app_equipe_show', methods: ['GET'])]
public function show(Equipe $equipe): Response
{
    // ========= TEXTE DU QR =========
    $text = "Equipe : ".$equipe->getNom()."\n\nJoueurs :\n";

    foreach ($equipe->getJoueur() as $joueur) {
        $text .= "- ".$joueur->getEmail()."\n";
    }

    // ========= QR CODE (ancienne version) =========
    $qrCode = new QrCode($text);
 

    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    // convertir en base64
    $qrImage = base64_encode($result->getString());

    // ADMIN
    if ($this->isGranted('ROLE_ADMIN')) {
        return $this->render('equipe/admin/show.html.twig', [
            'equipe' => $equipe,
            'qrCode' => $qrImage
        ]);
    }

    // CLIENT
    return $this->render('equipe/show.html.twig', [
        'equipe' => $equipe,
        'qrCode' => $qrImage
    ]);
}



#[Route('/{id}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
{
    $oldLogo = $equipe->getLogo(); // sauvegarder ancien logo

    $form = $this->createForm(EquipeType::class, $equipe);
    $form->handleRequest($request);

 
if ($form->isSubmitted() && $form->isValid()) {

    /** @var UploadedFile $logoFile */
    $logoFile = $form->get('logoFile')->getData();

    // récupérer ancien logo AVANT modification
    $oldLogo = $equipe->getLogo();

    if ($logoFile !== null) {

        // sécuriser extension
        $extension = $logoFile->guessExtension();
        if (!$extension) {
            $extension = 'png'; // fallback
        }

        // nom unique sécurisé
        $newFilename = md5(uniqid()).'.'.$extension;

        try {
            // upload
            $logoFile->move(
                $this->getParameter('equipe_directory'),
                $newFilename
            );

            // supprimer ancien logo (uniquement s'il existe)
            if ($oldLogo && file_exists(
                $this->getParameter('equipe_directory').'/'.$oldLogo
            )) {
                unlink(
                    $this->getParameter('equipe_directory').'/'.$oldLogo
                );
            }

            // sauvegarder nouveau logo
            $equipe->setLogo($newFilename);

        } catch (FileException $e) {
            $this->addFlash('danger', 'Erreur lors de l\'upload du logo.');
        }

    } 
    // ⚠️ IMPORTANT : PAS de else !
    // Si aucun nouveau fichier → on garde l'ancien logo automatiquement

    $entityManager->persist($equipe);
    $entityManager->flush();

    $this->addFlash('success', 'Equipe enregistrée avec succès !');

    return $this->redirectToRoute('app_equipe_index');
}


    return $this->render('equipe/edit.html.twig', [
        'equipe' => $equipe,
        'form' => $form->createView(), // ⚠ important
    ]);
}


    #[Route('/{id}', name: 'app_equipe_delete', methods: ['POST'])]
    public function delete(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$equipe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
