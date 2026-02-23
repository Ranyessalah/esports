<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Entity\Training;
use App\Form\TrainingType;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/training')]
class TrainingController extends AbstractController
{
    #[Route('/', name: 'app_training_index', methods: ['GET'])]
    public function index(Request $request, TrainingRepository $trainingRepository): Response
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

        $themes = $trainingRepository->createQueryBuilder('t')
            ->select('DISTINCT t.theme')
            ->getQuery()
            ->getScalarResult();

        return $this->render('training/index.html.twig', [
            'trainings' => $trainings,
            'themes' => array_column($themes, 'theme'),
            'currentFilters' => [
                'search' => $search,
                'theme' => $theme,
                'date' => $date,
            ]
        ]);
    }

    #[IsGranted('ROLE_COACH')]
    #[Route('/new', name: 'app_training_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $training = new Training();
        $training->setCreatedBy($this->getUser());
        $form = $this->createForm(TrainingType::class, $training);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentFile = $form->get('document')->getData();
            if ($documentFile) {
                $originalFilename = pathinfo($documentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$documentFile->getClientOriginalExtension();

                try {
                    $documentFile->move(
                        $this->getParameter('documents_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // handle exception if something happens during file upload
                }

                $training->setDocumentName($newFilename);
            }

            $entityManager->persist($training);
            $entityManager->flush();

            return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training/new.html.twig', [
            'training' => $training,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_training_show', methods: ['GET'])]
    public function show(Training $training): Response
    {
        return $this->render('training/show.html.twig', [
            'training' => $training,
        ]);
    }

    #[IsGranted('ROLE_COACH')]
    #[Route('/{id}/edit', name: 'app_training_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Training $training, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(TrainingType::class, $training);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentFile = $form->get('document')->getData();
            if ($documentFile) {
                $originalFilename = pathinfo($documentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$documentFile->getClientOriginalExtension();

                try {
                    $documentFile->move(
                        $this->getParameter('documents_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $training->setDocumentName($newFilename);
            }

            $training->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training/edit.html.twig', [
            'training' => $training,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_COACH')]
    #[Route('/{id}', name: 'app_training_delete', methods: ['POST'])]
    public function delete(Request $request, Training $training, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$training->getId(), $request->request->get('_token'))) {
            $entityManager->remove($training);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/participate', name: 'app_training_participate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function participate(Training $training, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Check if already participating
        foreach ($training->getAttendances() as $attendance) {
            if ($attendance->getPlayer() === $user) {
                $this->addFlash('info', 'You are already registered for this session.');
                return $this->redirectToRoute('app_training_show', ['id' => $training->getId()]);
            }
        }

        $attendance = new Attendance();
        $attendance->setTraining($training);
        $attendance->setPlayer($user);
        $attendance->setStatus('registered'); // New status
        $attendance->setRecordedAt(new \DateTimeImmutable());

        $entityManager->persist($attendance);
        $entityManager->flush();

        $this->addFlash('success', 'You have successfully signed up for this training session!');

        return $this->redirectToRoute('app_training_show', ['id' => $training->getId()]);
    }
}
