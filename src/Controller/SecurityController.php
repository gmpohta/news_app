<?php

namespace App\Controller;

use App\Entity\User;
use App\Model\SecurityModel;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

class SecurityController extends AbstractController
{
    #[Route("/api/register", name: "register_user", methods: ["POST"])]
    #[OA\Tag(name: 'security')]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: "Returned when register new user",
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\Parameter(
        name: 'register_user',
        description: 'Register new user',
        in: 'query',
        content: new Model(type: SecurityModel::class)
    )]
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, JWTTokenManagerInterface $jwtManager, AuthenticationSuccessHandler $authenticationSuccessHandler, AuthenticationFailureHandler $authenticationFailureHandler): Response
    {
        $user = new User();
        $user->setEmail($request->get('email'));
        $user->setPassword($passwordEncoder->encodePassword($user, $request->get('password')));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $token = $jwtManager->create($user);

        return $authenticationSuccessHandler->onAuthenticationSuccess($request, new JWTAuthenticatedToken($token));
    }
    
    #[Route('/api/login', name: 'login_user', methods: ['POST'])]
    #[OA\Tag(name: 'security')]
    #[OA\Response(
        response: Response::HTTP_ACCEPTED,
        description: "Returned when user login",
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\Parameter(
        name: 'login_user',
        description: 'Login user',
        in: 'query',
        content: new Model(type: SecurityModel::class)
    )]
    public function loginAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, JWTTokenManagerInterface $jwtManager, AuthenticationSuccessHandler $authenticationSuccessHandler, AuthenticationFailureHandler $authenticationFailureHandler): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $request->get('email')]);

        if (!$user) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }

        if (!$passwordEncoder->isPasswordValid($user, $request->get('password'))) {
            return new Response('Invalid password', Response::HTTP_BAD_REQUEST);
        }

        $token = $jwtManager->create($user);

        return $authenticationSuccessHandler->onAuthenticationSuccess($request, new JWTAuthenticatedToken($token));
    }
}

