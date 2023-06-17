<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class NewsService
{
    public function __construct(
        private EntityManagerInterface $em, 
    ) {}

    public function deleteEntity($entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
}