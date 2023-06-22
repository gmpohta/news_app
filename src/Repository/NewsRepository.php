<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\ORM\EntityRepository;

class NewsRepository extends EntityRepository
{
    public function getNewsWithParam(int $param, int $limit, int $offset): ?array 
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('n')
            ->from(News::class, 'n')
            ->join('n.user', 'usr');
        ;

        if (!empty($param['userId'])) {
            $queryBuilder->andWhere('n.user IN (:user_id)');
            $queryBuilder->setParameter('user_id', $param['userId']);
        }

        if (!empty($param['userEmail'])) {
            $queryBuilder->andWhere('n.user IN (:user_email)');
            $queryBuilder->setParameter('user_email', $param['userEmail']);
        }

        return $queryBuilder
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }
}