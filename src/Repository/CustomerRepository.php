<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\Store;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findByPage(Store $store, int $page, int $maxPerPage = 10)
    {
        return $this->createQueryBuilder('p')
            ->where('p.store = :store')
            ->setParameter('store', $store)
            ->setMaxResults($maxPerPage)
            ->setFirstResult($maxPerPage * ($page - 1))
            ->getQuery()
            ->getResult();
    }
}
