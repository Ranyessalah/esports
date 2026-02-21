<?php

namespace App\Controller;

use App\Entity\Matchs;
use App\Form\MatchsType;
use App\Form\MatchsTypeedit;
use App\Repository\MatchsRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\MatchPredictionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/matchs')]
final class MatchsController extends AbstractController
{
    
#[Route(name: 'app_matchs_index', methods: ['GET'])]
public function index(MatchsRepository $matchsRepository, Request $request): Response
{
    $minScore = $request->query->get('minScore');
    $maxScore = $request->query->get('maxScore');
    $search = $request->query->get('search');
    $sort = $request->query->get('sort'); // 'asc' ou 'desc'

    $qb = $matchsRepository->createQueryBuilder('m')
        ->leftJoin('m.equipe1', 'e1')
        ->leftJoin('m.equipe2', 'e2')
        ->addSelect('e1, e2');

     
    // Recherche par nom des équipes
    if ($search) {
        $qb->andWhere('e1.nom LIKE :search OR e2.nom LIKE :search OR m.nom_match LIKE :search')
           ->setParameter('search', '%'.$search.'%');
    }

    // Tri par score total
    if ($sort === 'asc') {
        $qb->orderBy('m.scoreEquipe1 + m.scoreEquipe2', 'ASC');
    } elseif ($sort === 'desc') {
        $qb->orderBy('m.scoreEquipe1 + m.scoreEquipe2', 'DESC');
    } else {
        $qb->orderBy('m.dateMatch', 'DESC'); // Tri par défaut par date
    }

    $matchs = $qb->getQuery()->getResult();

    if ($this->isGranted('ROLE_ADMIN')) {
       return $this->render('matchs/admin/index.html.twig', [
            'matchs' => $matchsRepository->findAll(),    'minScore' => $minScore,
        'maxScore' => $maxScore,
        'search' => $search,
        'sort' => $sort,
        ]);
     }
    return $this->render('matchs/index.html.twig', [
        'matchs' => $matchs,
        'minScore' => $minScore,
        'maxScore' => $maxScore,
        'search' => $search,
        'sort' => $sort,
    ]);
}


    #[Route('/new', name: 'app_matchs_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailService $mailService, UserRepository $userRepository): Response
    {
        $match = new Matchs();
        $form = $this->createForm(MatchsType::class, $match);
        $form->handleRequest($request);
           
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($match);
            $entityManager->flush();





 
// team 1 coaches
$coachesTeam1 = $match->getEquipe1()->getCoach();

// team 2 coaches
$coachesTeam2 = $match->getEquipe2()->getCoach();

 
        $mailService->sendMatchNotification($coachesTeam2->getEmail(), $match);
 

 
        $mailService->sendMatchNotification($coachesTeam2->getEmail(), $match);
 






            return $this->redirectToRoute('app_matchs_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('matchs/new.html.twig', [
            'match' => $match,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_matchs_show', methods: ['GET'])]
    public function show(Matchs $match, MatchPredictionService $ai): Response
    {
        $prediction = null;
    
        
    
        // ADMIN PAGE
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('matchs/admin/show.html.twig', [
                'match' => $match
             ]);
        }
    
        // USER PAGE
        return $this->render('matchs/show.html.twig', [
            'match' => $match
         ]);
    }
    

    #[Route('/{id}/edit', name: 'app_matchs_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Matchs $match, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MatchsTypeEdit::class, $match);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_matchs_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('matchs/edit.html.twig', [
            'match' => $match,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_matchs_delete', methods: ['POST'])]
    public function delete(Request $request, Matchs $match, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$match->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($match);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_matchs_index', [], Response::HTTP_SEE_OTHER);
    }
    // src/Controller/MatchsController.php











    #[Route('/{id}/calendar', name: 'app_matchs_calendar')]
    public function addToCalendar(Matchs $match): Response
    {
        // format Google : YYYYMMDDTHHMMSS
        $start = $match->getDateMatch()->format('Ymd\THis');
        $end   = $match->getDateFinMatch()->format('Ymd\THis');
    
        // Titre
        $title = urlencode(
            $match->getEquipe1()->getNom() . ' vs ' . $match->getEquipe2()->getNom()
        );
    
        // Description
        $details = urlencode(
            "Match officiel du tournoi : " . $match->getNomMatch()
        );
    
        // Lieu (tu peux mettre Online, Arena, ou ton site)
        $location = urlencode("Online E-Sport Tournament");
    
        $googleUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE"
            . "&text=$title"
            . "&dates=$start/$end"
            . "&details=$details"
            . "&location=$location";
    
        return $this->redirect($googleUrl);
    }
    





    #[Route('/predict/{id}', name: 'app_matchs_predict', methods: ['GET'])]
    public function predictAjax(Matchs $match, MatchPredictionService $ai): JsonResponse
    {
        if ($match->getStatut() === 'termine') {
            return new JsonResponse([
                'error' => 'Match already finished'
            ], 400);
        }
    
        $prediction = $ai->predict(
            $match->getEquipe1()->getId(),
            $match->getEquipe2()->getId()
        );
    
        return new JsonResponse([
            'prediction' => $prediction
        ]);
    }
    

}
