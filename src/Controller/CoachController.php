<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Form\CoachType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/coach')]
final class CoachController extends AbstractController
{
    #[Route('/', name: 'dashboard_coach')]
    public function coach(): Response
    {
        return $this->render('coach/dashboard.html.twig');
    }

    #[Route('/profile', name: 'profil_coach')]
    public function profilecoach(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $coach = $this->getUser();

        if (!$coach instanceof Coach) {
            throw new AccessDeniedHttpException('Accès réservé aux coachs.');
        }

        $originalEmail = $coach->getEmail();

        $form = $this->createForm(CoachType::class, $coach, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $coach->setPassword($passwordHasher->hashPassword($coach, $plainPassword));
            }
            $em->flush();
            $this->addFlash('success', 'Profil modifié avec succès !');
            return $this->redirectToRoute('profil_coach');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $coach->setEmail($originalEmail);
        }

        return $this->render('coach/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $coach,
        ]);
    }
}
