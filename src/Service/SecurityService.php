<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\AppBadRequestHttpException;
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
        private ValidatorInterface $validator,
    ) {}

    public function regiserUser(string $email, string $password): string
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPlainPasswordForCheck($password);
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user, 
                $password
            )
        );

        $violations = $this->validator->validate($user);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    $violation->getPropertyPath() => $violation->getMessage()
                ];
            }

            throw new AppBadRequestHttpException(errors: $errors, code: JsonResponse::HTTP_BAD_REQUEST);
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

        if (empty($user)) {
            throw new AppBadRequestHttpException(errors: ['User not found'], code: JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new AppBadRequestHttpException(errors: ['Invalid credentials'], code: JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->jwtManager->create($user);
    }
}