<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/player')]
final class PlayerController extends AbstractController
{
    #[Route('/', name: 'dashboard_player')]
    public function player(): Response
    {
        return $this->render('player/dashboard.html.twig');
    }

    #[Route('/profile', name: 'profil_player')]
    public function profileplayer(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $player = $this->getUser();

        if (!$player instanceof Player) {
            throw new AccessDeniedHttpException('Accès réservé aux joueurs.');
        }

        $originalEmail = $player->getEmail();

        $form = $this->createForm(PlayerType::class, $player, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $player->setPassword($passwordHasher->hashPassword($player, $plainPassword));
            }
            $em->flush();
            $this->addFlash('success', 'Profil modifié avec succès !');
            return $this->redirectToRoute('profil_player');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $player->setEmail($originalEmail);
        }

        return $this->render('player/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $player,
        ]);
    }
}

