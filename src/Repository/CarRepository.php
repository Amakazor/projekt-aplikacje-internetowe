<?php

namespace App\Repository;

use App\Entity\Car;
use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Car|null find($id, $lockMode = null, $lockVersion = null)
 * @method Car|null findOneBy(array $criteria, array $orderBy = null)
 * @method Car[]    findAll()
 * @method Car[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    /**
     * @param Company $company
     * @param int $per_page
     * @param int $current_page
     * @param string $order
     * @param string $direction
     * @return Car[] Returns an array of Car objects
     */
    public function carPagination(Company $company, $per_page, $current_page, $order, $direction)
    {
        return $this->createQueryBuilder('car')
            ->andWhere('car.company = :val')
            ->setParameter('val', $company)
            ->orderBy('car.'.$order, $direction)
            ->setMaxResults($per_page)
            ->setFirstResult(($current_page - 1) * $per_page)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Company $company
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function carPaginationCount(Company $company)
    {
        return $this->createQueryBuilder('car')
            ->andWhere('car.company = :val')
            ->setParameter('val', $company)
            ->select('count(car.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Company $company
     * @param int $id
     * @return mixed
     */
    public function removeCompanyCar(Company $company, int $id)
    {
        return $this->createQueryBuilder('car')
            ->delete()
            ->andWhere('car.company = :val')
            ->setParameter('val', $company)
            ->andWhere('car.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Company $company
     * @param int $id
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function doesCarIdBelongToCompany(Company $company, int $id) {
        return $this->createQueryBuilder('car')
            ->andWhere('car.company = :val')
            ->setParameter('val', $company)
            ->andWhere('car.id = :id')
            ->setParameter('id', $id)
            ->select('count(car.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }


    /*
    public function findOneBySomeField($value): ?Car
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
