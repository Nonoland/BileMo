<?php

namespace App\Repository;

use App\Entity\Store;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Store>
 *
 * @method Store|null find($id, $lockMode = null, $lockVersion = null)
 * @method Store|null findOneBy(array $criteria, array $orderBy = null)
 * @method Store[]    findAll()
 * @method Store[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Store::class);
    }

    public function findByPage(UserInterface $user, int $page, int $maxPerPage = 10)
    {
        return $this->createQueryBuilder('s')
            ->where(':user MEMBER OF s.users')
            ->setParameter('user', $user)
            ->setMaxResults($maxPerPage)
            ->setFirstResult($maxPerPage * ($page - 1))
            ->getQuery()
            ->getResult();
    }
}
