<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\ORM\EntityRepository;

class NewsRepository extends EntityRepository
{
    private const MAX_LIMIT_QUERY_RESULTS = 1000;

    public function getNewsWithParam(?array $param, ?int $limit = 100, ?int $offset = 0): ?array 
    {
        if ($limit > self::MAX_LIMIT_QUERY_RESULTS) {
            $limit = self::MAX_LIMIT_QUERY_RESULTS;
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('n')
            ->from(News::class, 'n')
            ->join('n.user', 'usr')
        ;

        if (!empty($param['userId'])) {
            $queryBuilder->andWhere('usr.id IN (:user_id)');
            $queryBuilder->setParameter('user_id', $param['userId']);
        }

        if (!empty($param['userEmail'])) {
            $queryBuilder->andWhere('usr.email IN (:user_email)');
            $queryBuilder->setParameter('user_email', $param['userEmail']);
        }

        return $queryBuilder
            ->addOrderBy('n.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }
}