<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\User;
use App\Exception\AppBadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class NewsService
{
    public function __construct(
        private EntityManagerInterface $em, 
        private JWTTokenManagerInterface $jwtManager,
        private TokenStorageInterface $tokenStorageInterface,
        private ValidatorInterface $validator,
    ) {}
    
    public function createNews(string $name, string $body): ?bool
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $user = $this->em->getRepository(User::class)->findOneBy([
            'email' => $decodedJwtToken['email']
        ]);

        if (empty($user)) {
            throw new AppBadRequestHttpException(errors: ['User not found'], code: JsonResponse::HTTP_NOT_FOUND);
        }

        $news = new News();
        $news->setName($name);
        $news->setBody($body);
        $news->setUser($user);

        $violations = $this->validator->validate($news);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    $violation->getPropertyPath() => $violation->getMessage()
                ];
            }

            throw new AppBadRequestHttpException(errors: $errors, code: JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->persist($news);
        $this->em->flush();

        return true;
    }

    public function deleteEntity(News $news): void
    {
        $this->em->remove($news);
        $this->em->flush();
    }
}