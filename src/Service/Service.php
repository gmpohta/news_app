<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class Service
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $em, 
    ) {
        $this->em = $em;
    }
}