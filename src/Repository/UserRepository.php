<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Recherche et tri via QueryBuilder
     *
     * @param string|null $search   Terme de recherche (email)
     * @param string      $sortBy   Champ de tri (id, email)
     * @param string      $sortOrder Direction du tri (ASC, DESC)
     * @param array|null  $restrictIds Restreindre aux IDs donnés (résultat du filtre DQL)
     * @return User[]
     */
    public function searchAndSort(?string $search = null, string $sortBy = 'id', string $sortOrder = 'DESC', ?array $restrictIds = null): array
    {
        $qb = $this->createQueryBuilder('u');

        // Recherche par email, rôle ou statut via QueryBuilder
        if ($search) {
            $searchLower = mb_strtolower($search);

            // Vérifier si le terme de recherche correspond à un statut
            $statusSearch = null;
            if (str_contains($searchLower, 'bloqu') || str_contains($searchLower, 'block')) {
                $statusSearch = true;
            } elseif (str_contains($searchLower, 'actif') || str_contains($searchLower, 'activ')) {
                $statusSearch = false;
            }

            if ($statusSearch !== null) {
                $qb->andWhere('u.email LIKE :search OR u.roles LIKE :search OR u.isBlocked = :statusSearch')
                   ->setParameter('search', '%' . $search . '%')
                   ->setParameter('statusSearch', $statusSearch);
            } else {
                $qb->andWhere('u.email LIKE :search OR u.roles LIKE :search')
                   ->setParameter('search', '%' . $search . '%');
            }
        }

        // Si le filtre DQL a retourné des IDs, on restreint
        if ($restrictIds !== null) {
            if (empty($restrictIds)) {
                return []; // Aucun résultat si le filtre DQL n'a rien trouvé
            }
            $qb->andWhere('u.id IN (:ids)')
               ->setParameter('ids', $restrictIds);
        }

        // Tri via QueryBuilder
        $allowedSorts = ['id', 'email'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('u.' . $sortBy, $sortOrder);

        return $qb->getQuery()->getResult();
    }

    /**
     * Filtre par rôle et/ou statut via DQL
     *
     * @param string|null $role   Rôle à filtrer (ROLE_ADMIN, ROLE_COACH, ROLE_PLAYER)
     * @param string|null $status Statut à filtrer (blocked, active)
     * @return User[]
     */
    public function filterByDQL(?string $role = null, ?string $status = null): array
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT u FROM App\Entity\User u WHERE 1=1';
        $params = [];

        if ($role) {
            $dql .= ' AND u.roles LIKE :role';
            $params['role'] = '%"' . $role . '"%';
        }

        if ($status === 'blocked') {
            $dql .= ' AND u.isBlocked = :blocked';
            $params['blocked'] = true;
        } elseif ($status === 'active') {
            $dql .= ' AND u.isBlocked = :blocked';
            $params['blocked'] = false;
        }

        $query = $em->createQuery($dql);
        foreach ($params as $key => $value) {
            $query->setParameter($key, $value);
        }

        return $query->getResult();
    }
}
