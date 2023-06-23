<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\User;
use App\Exception\AppBadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\SerializerInterface;

class NewsService
{
    public function __construct(
        private EntityManagerInterface $em, 
        private JWTTokenManagerInterface $jwtManager,
        private TokenStorageInterface $tokenStorageInterface,
        private UtilsService $utilsService,
        private SerializerInterface $serializer,
    ) {}
    
    public function getNewsById(int $newsId): ?string
    {
        $news = $this->em->getRepository(News::class)->findOneBy(['id' => $newsId]);

        if (empty($news)) {
            throw new AppBadRequestHttpException(
                errors: [sprintf('News with id %d not found', $newsId)], 
                code: JsonResponse::HTTP_NOT_FOUND
            );
        }

        return $this->serializer->serialize($news, 'json', [
            'groups' => ['read_news'],
        ]);
    }

    public function getNewsWithParam(FormInterface $form): ?string
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $news = $this->em->getRepository(News::class)
                ->getNewsWithParam(
                    [
                        $data['userEmail'],
                        $data['userId']
                    ], 
                    $data['limit'], 
                    $data['offset']
                );

            return $this->serializer->serialize($news, 'json', [
                'groups' => ['read_news'],
            ]);
        } 

        $this->utilsService->validateForm($form);
        return null;
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

        $this->utilsService->validateForm($form);
        return null;
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

        $this->utilsService->validateForm($form);
        return null;
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
}