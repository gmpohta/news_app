<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityService
{
    public function __construct(
        private EntityManagerInterface $em, 
        private JWTTokenManagerInterface $jwtManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {}

    public function regiserUser(string $email, string $password) : string
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user, 
                $password
            )
        );

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->jwtManager->create($user);
    }

    public function loginUser(string $email, string $password): string
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy([
                'email' => $email
            ])
        ;

        if (!$user) {
            return new Response('User not found', JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return new Response('Invalid credentials', Response::HTTP_BAD_REQUEST);
        }

        return new JWTAuthenticatedToken(
            $this->jwtManager->create($user)
        );
    }
}