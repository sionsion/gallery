<?php

namespace App\Repository;

use App\Entity\SendEmailsLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SendEmailsLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method SendEmailsLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method SendEmailsLog[]    findAll()
 * @method SendEmailsLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SendEmailsLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SendEmailsLog::class);
    }

    // /**
    //  * @return SendEmailsLog[] Returns an array of SendEmailsLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SendEmailsLog
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
