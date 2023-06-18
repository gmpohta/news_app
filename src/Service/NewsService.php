<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class NewsService
{
    public function __construct(
        private EntityManagerInterface $em, 
        private JWTTokenManagerInterface $jwtManager,
    ) {}
    
    public function createNews($name, $body, $token): bool
    {
        $news = new News();
        $news->setName($name);
        $news->setBody($body);
        $decodedToken = $this->jwtManager->decode($token);
        dump($decodedToken);

        /*$errors = $this->validator->validate($news);
        if (count($errors) > 0) {
            return new JsonResponse($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->persist($news);
        $this->em->flush();*/

        return true;
    }

    public function deleteEntity($entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
}