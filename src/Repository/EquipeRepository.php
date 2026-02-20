<?php

namespace App\Repository;

use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Equipe>
 */
class EquipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipe::class);
    }
public function findAllWithRelations()
{
    return $this->createQueryBuilder('e')
        ->leftJoin('e.coach', 'c')->addSelect('c')
        ->leftJoin('e.joueur', 'j')->addSelect('j')
        ->getQuery()
        ->getResult();
}




public function findAllWithSearch(?string $search = null, ?string $game = null, ?string $sort = null)
{
    $qb = $this->createQueryBuilder('e')
        ->leftJoin('e.coach', 'c')->addSelect('c')
        ->leftJoin('e.joueur', 'j')->addSelect('j');

    // Recherche par nom d'équipe
    if ($search) {
        $qb->andWhere('e.nom LIKE :search')
           ->setParameter('search', '%'.$search.'%');
    }

     if ($game) {
        $qb->andWhere('e.categorie = :categorie')
           ->setParameter('categorie', $game);
    }

    

    return $qb->getQuery()->getResult();
}












    //    /**
    //     * @return Equipe[] Returns an array of Equipe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Equipe
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
