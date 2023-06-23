<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\AppBadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\FormInterface;

class SecurityService
{
    public function __construct(
        private EntityManagerInterface $em, 
        private JWTTokenManagerInterface $jwtManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UtilsService $utilsService,
    ) {}

    public function regiserUser(FormInterface $form): ?string
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user, 
                    $user->getPassword()
                )
            );
    
            $this->em->persist($user);
            $this->em->flush();
    
            return $this->jwtManager->create($user);
        } 

        $this->utilsService->validateForm($form);
        return null;
    }

    public function loginUser(FormInterface $form): ?string
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
    
            $user = $this->em
                ->getRepository(User::class)
                ->findOneBy([
                    'email' => $formData['email']
                ])
            ;

            if (empty($user)) {
                throw new AppBadRequestHttpException(
                    errors: ['User not found'], 
                    code: JsonResponse::HTTP_NOT_FOUND
                );
            }

            if (!$this->passwordHasher->isPasswordValid($user, $formData['password'])) {
                throw new AppBadRequestHttpException(
                    errors: ['Invalid credentials'], 
                    code: JsonResponse::HTTP_UNAUTHORIZED
                );
            }

            return $this->jwtManager->create($user);
        } 

        $this->utilsService->validateForm($form);
        return null;
    }
}