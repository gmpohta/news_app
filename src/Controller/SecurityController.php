<?php

namespace App\Controller;

use App\Model\Security\SecurityModel;
use App\Service\SecurityService;
use App\Exception\AppBadRequestHttpException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

#[Route('/auth')] 
class SecurityController extends AbstractController
{
    public function __construct(
        private SecurityService $securityService
    ) {}

    #[Route("/register", name: "register_user", methods: ["POST"])]
    #[OA\Tag(name: 'security')]
    #[Security(name: null)]
    #[OA\Response(
        response: JsonResponse::HTTP_CREATED,
        description: "Returned when register new user",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\RequestBody(
        content: new Model(type: SecurityModel::class)
    )]
    public function register(Request $request): JsonResponse
    {
        $params = json_decode($request->getContent(), true);//add validate input
        try {
            $token = $this->securityService->regiserUser(
                $params['email'],
                $params['password'],
            );
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            ['token' => $token], 
            JsonResponse::HTTP_CREATED
        );
    }
    
    #[Route('/login', name: 'login_user', methods: ['POST'])]
    #[OA\Tag(name: 'security')]
    #[Security(name: null)]
    #[OA\Response(
        response: JsonResponse::HTTP_OK,
        description: "Returned when user login",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\RequestBody(
        content: new Model(type: SecurityModel::class)
    )]
    public function login(Request $request): JsonResponse
    {
        $params = json_decode($request->getContent(), true); //add validate input
        try {
            $token = $this->securityService->loginUser(
                $params['email'],
                $params['password'],
            );
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }
        
        return new JsonResponse(
            ['token' => $token], 
            JsonResponse::HTTP_OK
        );
    }
}

