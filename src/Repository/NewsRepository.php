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

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('n')
            ->from(News::class, 'n')
            ->join('n.user', 'usr')
        ;

        if (!empty($param['userId']) || !empty($param['userEmail'])) {
            $qb->andWhere($qb->expr()->orX(
               'usr.id IN (:userId)', 
               'usr.email IN (:userEmail)'
            ));
            
            $qb->setParameters($param);
        }
        
        return $qb
            ->addOrderBy('n.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }
}