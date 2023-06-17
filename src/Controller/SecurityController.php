<?php

namespace App\Controller;

use App\Entity\User;
use App\Model\SecurityModel;
use App\Service\SecurityService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

class SecurityController extends AbstractController
{
    public function __construct(
        private SecurityService $securityService
    ) {}

    #[Route("/api/register", name: "register_user", methods: ["POST"])]
    #[OA\Tag(name: 'security')]
    #[OA\Response(
        response: JsonResponse::HTTP_CREATED,
        description: "Returned when register new user",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\Parameter(
        name: 'register_user',
        description: 'Register new user',
        in: 'query',
        content: new Model(type: SecurityModel::class)
    )]
    public function register(Request $request): JsonResponse
    {
        return new JsonResponse($this->securityService->regiserUser(
            $request->get('email'),
            $request->get('password'),
        ), JsonResponse::HTTP_CREATED);
    }
    
    #[Route('/api/login', name: 'login_user', methods: ['POST'])]
    #[OA\Tag(name: 'security')]
    #[OA\Response(
        response: JsonResponse::HTTP_ACCEPTED,
        description: "Returned when user login",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\Parameter(
        name: 'login_user',
        description: 'Login user',
        in: 'query',
        content: new Model(type: SecurityModel::class)
    )]
    public function loginAction(Request $request)
    {
        /*$user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $request->get('email')]);

        if (!$user) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }

        if (!$passwordEncoder->isPasswordValid($user, $request->get('password'))) {
            return new Response('Invalid password', Response::HTTP_BAD_REQUEST);
        }

        $token = $jwtManager->create($user);

        return $authenticationSuccessHandler->onAuthenticationSuccess($request, new JWTAuthenticatedToken($token));
    */}
}

