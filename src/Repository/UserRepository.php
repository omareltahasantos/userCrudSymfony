<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }


    public function paginate($query, $page = 1, $limit = 2)
    {
        $paginator = new Paginator($query);

        $paginator->getQuery()
                    ->setFirstResult($limit * ($page - 1)) //Offset
                    ->setMaxResults($limit); //Limit
        return $paginator;
    }

    public function getAllUsers($currentPage = 1, $limit = 2)
    {
        $query = $this->createQueryBuilder('u')->getQuery();

        $paginator = $this->paginate($query, $currentPage, $limit);

        return $paginator;
    }

    public function save(User $entity): void
    {
       $this->getEntityManager()->persist($entity);
       $this->getEntityManager()->flush();
    }

    public function remove(User $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

    }

}