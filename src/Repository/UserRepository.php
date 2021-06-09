<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param UserInterface $user
     * @param string $newEncodedPassword
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param Company $company
     * @param int $per_page
     * @param int $current_page
     * @param string $order
     * @param string $direction
     * @return User[] Returns an array of User objects
     */
    public function Pagination(Company $company, $per_page, $current_page, $order, $direction)
    {
        return $this->createQueryBuilder('user')
            ->andWhere('user.company = :val')
            ->setParameter('val', $company)
            ->orderBy('user.'.$order, $direction)
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
    public function paginationCount(Company $company)
    {
        return $this->createQueryBuilder('user')
            ->andWhere('user.company = :val')
            ->setParameter('val', $company)
            ->select('count(user.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Company $company
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function verifiedCount(Company $company)
    {
        return $this->createQueryBuilder('user')
            ->andWhere('user.company = :val')
            ->setParameter('val', $company)
            ->andWhere('user.isVerified = :ver')
            ->setParameter('ver', '1')
            ->select('count(user.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Company $company
     * @param int $id
     * @return mixed
     */
    public function removeCompanyUser(Company $company, int $id)
    {
        return $this->createQueryBuilder('user')
            ->delete()
            ->andWhere('user.company = :val')
            ->setParameter('val', $company)
            ->andWhere('user.id = :id')
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
    public function doesUserIdBelongToCompany(Company $company, int $id) {
        return $this->createQueryBuilder('user')
            ->andWhere('user.company = :val')
            ->setParameter('val', $company)
            ->andWhere('user.id = :id')
            ->setParameter('id', $id)
            ->select('count(user.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $username
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function doesUsernameExist(string $username)
    {
        return $this->createQueryBuilder('user')
            ->andWhere('user.username = :username')
            ->setParameter('username', $username)
            ->select('count(user.id)')
            ->getQuery()
            ->getSingleScalarResult() ? TRUE : FALSE;
    }
}
