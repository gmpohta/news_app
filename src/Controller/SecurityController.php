<?php

namespace App\Controller;

use App\Entity\User;
use App\Model\SecurityModel;
use App\Service\SecurityService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;

#[Route('/auth')] 
class SecurityController extends AbstractController
{
    public function __construct(
        private SecurityService $securityService
    ) {}

    #[Route("/register", name: "register_user", methods: ["POST"])]
    #[OA\Tag(name: 'security')]
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
    public function registerAction(Request $request): JsonResponse
    {
        $params = json_decode($request->getContent(), true);
        try {
            $user = $this->securityService->regiserUser(
                $params['email'],
                $params['password'],
            );
        } catch (BadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            ['token' => $user], 
            JsonResponse::HTTP_CREATED
        );
    }
    
    #[Route('/login', name: 'login_user', methods: ['POST'])]
    #[OA\Tag(name: 'security')]
    #[OA\Response(
        response: JsonResponse::HTTP_ACCEPTED,
        description: "Returned when user login",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    public function loginAction(Request $request)
    {
        return new JsonResponse(
            $this->securityService->loginUser(
                $request->get('email'),
                $request->get('password'),
            ), 
            JsonResponse::HTTP_ACCEPTED
        );
    }
}

