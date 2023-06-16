<?php

namespace App\Controller;

use App\Entity\News;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')] 
class MainController extends AbstractController
{
    #[Route('/read/{news}', methods: ['GET'])] 
    #[OA\Tag(name: 'news')]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: "Returned when news not found",
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returned when success",
    )]
    public function readOneAction(Request $request, News $news): JsonResponse
    {
        return new JsonResponse(['news' => 'read'], 200);
    }

    #[Route('/read', methods: ['POST'])] 
    #[OA\Tag(name: 'news')]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: "Returned when not found",
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returned when success",
    )]
    public function readAction(Request $request): JsonResponse
    {
        return new JsonResponse(['news' => 'read'], 200);
    }

    #[Route('/create', methods: ['POST'])] 
    #[OA\Tag(name: 'news')]
    #[Security(name: 'Bearer')]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "Returned when authorization is required",
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: "Returned when new news is success created",
    )]
    public function createAction(Request $request)
    {
        return JsonResponse(['create'], 200);
    }

    #[Route('/edit/{news}', methods: ['PATCH'])] 
    #[OA\Tag(name: 'news')]
    #[Security(name: 'Bearer')]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "Returned when authorization is required",
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: "Returned when news not found",
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returned when new news is success edited",
    )]
    public function updateAction(Request $request, News $news)
    {    
        return JsonResponse(['edit'], 200);
    }

    #[Route('/delete/{news}', methods: ['DELETE'])] 
    #[OA\Tag(name: 'news')]
    #[Security(name: 'Bearer')]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "Returned when authorization is required",
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: "Returned when news not found",
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returned when new news is success deleted",
    )]
    public function deleteAction(Request $request, News $news)
    {
        return JsonResponse(['delete'], 200);
    }
}
