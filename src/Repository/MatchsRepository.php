<?php

namespace App\Repository;

use App\Entity\Matchs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Matchs>
 */
class MatchsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Matchs::class);
    }// src/Repository/MatchsRepository.php
// src/Repository/MatchsRepository.php

public function getMatchsWithScores()
{
    return $this->createQueryBuilder('m')
        ->where('m.statut = :statut')
        ->setParameter('statut', 'termine')
        ->andWhere('m.scoreEquipe1 IS NOT NULL')
        ->andWhere('m.scoreEquipe2 IS NOT NULL')
        ->getQuery()
        ->getResult();
}
public function countMatchesByStatus(): array
{
    return $this->createQueryBuilder('m')
        ->select('m.statut, COUNT(m.id) as total')
        ->groupBy('m.statut')
        ->getQuery()
        ->getResult();
}
// src/Repository/MatchsRepository.php

public function getTeamStats(): array
{
    $qb = $this->createQueryBuilder('m')
        ->select('IDENTITY(m.equipe1) AS equipe_id, 
                  SUM(CASE WHEN m.scoreEquipe1 > m.scoreEquipe2 THEN 1 ELSE 0 END) AS wins,
                  SUM(CASE WHEN m.scoreEquipe1 < m.scoreEquipe2 THEN 1 ELSE 0 END) AS losses,
                  SUM(CASE WHEN m.scoreEquipe1 = m.scoreEquipe2 THEN 1 ELSE 0 END) AS draws')
        ->groupBy('m.equipe1');

    $result1 = $qb->getQuery()->getResult();

    // Répéter pour equipe2
    $qb2 = $this->createQueryBuilder('m')
        ->select('IDENTITY(m.equipe2) AS equipe_id, 
                  SUM(CASE WHEN m.scoreEquipe2 > m.scoreEquipe1 THEN 1 ELSE 0 END) AS wins,
                  SUM(CASE WHEN m.scoreEquipe2 < m.scoreEquipe1 THEN 1 ELSE 0 END) AS losses,
                  SUM(CASE WHEN m.scoreEquipe2 = m.scoreEquipe1 THEN 1 ELSE 0 END) AS draws')
        ->groupBy('m.equipe2');

    $result2 = $qb2->getQuery()->getResult();

    // Fusionner les stats
    $stats = [];
    foreach (array_merge($result1, $result2) as $r) {
        $id = $r['equipe_id'];
        if (!isset($stats[$id])) {
            $stats[$id] = ['wins' => 0, 'losses' => 0, 'draws' => 0];
        }
        $stats[$id]['wins'] += $r['wins'];
        $stats[$id]['losses'] += $r['losses'];
        $stats[$id]['draws'] += $r['draws'];
    }

    // Optionnel : trier par nombre de victoires (classement)
    uasort($stats, fn($a, $b) => $b['wins'] <=> $a['wins']);

    return $stats;
}


    //    /**
    //     * @return Matchs[] Returns an array of Matchs objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Matchs
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
