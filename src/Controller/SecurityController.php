<?php

namespace App\Controller;

use App\Model\Security\RegisterSecurityModel;
use App\Model\Security\LoginSecurityModel;
use App\Service\SecurityService;
use App\Service\UtilsService;
use App\Exception\AppBadRequestHttpException;
use App\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

#[Route("/auth")] 
class SecurityController extends AbstractController
{
    public function __construct(
        private SecurityService $securityService,
        private UtilsService $utilsService
    ) {}

    #[Route("/register", name: "register_user", methods: ["POST"])]
    #[OA\Tag(name: "security")]
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
        content: new Model(type: RegisterSecurityModel::class)
    )]
    public function register(Request $request): JsonResponse
    {
        $form = $this->createForm(RegisterSecurityModel::class, new User);
        
        try {
            $form->submit(
                $this->utilsService->validateAndDecodeJson($request)
            );

            $token = $this->securityService->regiserUser($form);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new JsonResponse([
            'token' => $token
        ], JsonResponse::HTTP_CREATED);
    }
    
    #[Route("/login", name: "login_user", methods: ["POST"])]
    #[OA\Tag(name: "security")]
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
        content: new Model(type: LoginSecurityModel::class)
    )]
    public function login(Request $request): JsonResponse
    {
        $form = $this->createForm(LoginSecurityModel::class);
        
        try {
            $form->submit(
                $this->utilsService->validateAndDecodeJson($request)
            );

            $token = $this->securityService->loginUser($form);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new JsonResponse([
            'token' => $token
        ], JsonResponse::HTTP_CREATED);
    }
}

