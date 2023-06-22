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
use Symfony\Component\Form\FormInterface;

class NewsService
{
    public function __construct(
        private EntityManagerInterface $em, 
        private JWTTokenManagerInterface $jwtManager,
        private TokenStorageInterface $tokenStorageInterface,
        private ValidatorInterface $validator,
    ) {}
    
    public function getNewsById(int $newsId): ?array
    {
        $news = $this->em->getRepository(News::class)->findOneBy(['id' => $newsId]);

        if (empty($news)) {
            throw new AppBadRequestHttpException(
                errors: [sprintf('News with id %d not found', $newsId)], 
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }
        return $news;
    }

    public function getNewsWithParam(FormInterface $form): ?array
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            return $this->em->getRepository(News::class)
                ->getNewsWithParam(
                    [], 
                    $data['limit'], 
                    $data['offset']
                );
        } 

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $errors[$field] = $error->getMessage();
        }
        
        if (count($errors) > 0) {
            throw new AppBadRequestHttpException(
                errors: $errors, 
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function createNews(FormInterface $form): ?bool
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $user = $this->em->getRepository(User::class)->findOneBy([
            'email' => $decodedJwtToken['email']
        ]);

        if (empty($user)) {
            throw new AppBadRequestHttpException(
                errors: ['Current user not found. Maybe you access token is not valid'], 
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $news->setUser($user);
    
            $this->em->persist($news);
            $this->em->flush();
    
            return true;
        } 

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $errors[$field] = $error->getMessage();
        }
        
        if (count($errors) > 0) {
            throw new AppBadRequestHttpException(
                errors: $errors, 
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function patchNews(FormInterface $form, int $newsId): ?bool
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $user = $this->em->getRepository(User::class)->findOneBy([
            'email' => $decodedJwtToken['email']
        ]);

        if (empty($user)) {
            throw new AppBadRequestHttpException(
                errors: ['Current user not found. Maybe you access token is not valid'], 
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        $news = $this->em->getRepository(News::class)->findOneBy(['id' => $newsId]);

        if (empty($news)) {
            throw new AppBadRequestHttpException(
                errors: [sprintf('News with id %d not found', $newsId)], 
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($news->getUser()->getEmail() != $decodedJwtToken['email']) {
            throw new AppBadRequestHttpException(
                errors: [sprintf("You can't edit news with id %d. Access denied.", $newsId)], 
                code: JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
    
            if ($formData->getName()) {
                $news->setName($formData->getName());
            }
    
            if ($formData->getBody()) {
                $news->setBody($formData->getBody());
            }

            $this->em->persist($news);
            $this->em->flush();
    
            return true;
        } 

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $errors[$field] = $error->getMessage();
        }
        
        if (count($errors) > 0) {
            throw new AppBadRequestHttpException(
                errors: $errors, 
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function deleteNews(int $newsId): ?bool
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $user = $this->em->getRepository(User::class)->findOneBy([
            'email' => $decodedJwtToken['email']
        ]);

        if (empty($user)) {
            throw new AppBadRequestHttpException(
                errors: ['Current user not found. Maybe you access token is not valid'], 
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        $news = $this->em->getRepository(News::class)->findOneBy(['id' => $newsId]);

        if (empty($news)) {
            throw new AppBadRequestHttpException(
                errors: [sprintf('News with id %d not found', $newsId)], 
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        if ($news->getUser()->getEmail() != $decodedJwtToken['email']) {
            throw new AppBadRequestHttpException(
                errors: [sprintf("You can't delete news with id %d. Access denied.", $newsId)], 
                code: JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $this->em->remove($news);
        $this->em->flush();

        return true;
    }

    private function validateForm(): void
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $errors[$field] = $error->getMessage();
        }
        
        if (count($errors) > 0) {
            throw new AppBadRequestHttpException(
                errors: $errors, 
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }
}