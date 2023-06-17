<?php

namespace App\Controller;

use App\Entity\News;
use App\Model\NewsModel;
use App\Model\ReadNewsModel;
use App\Service\NewsService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

#[Route('/api')] 
class NewsController extends AbstractController
{
    public function __construct(
        private NewsService $newsService
    ) {}
    
    #[Route('/read/{news}', methods: ['GET'])] 
    #[OA\Tag(name: 'news')]
    #[OA\Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Returned when news not found",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_OK,
        description: "Returned when success",
        content: new Model(type: NewsModel::class)
    )]
    public function readOneAction(Request $request, News $news): JsonResponse
    {
        return new JsonResponse(['news' => 'read'], 200);
    }

    #[Route('/read', methods: ['POST'])] 
    #[OA\Tag(name: 'news')]
    #[OA\Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Returned when not found",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_OK,
        description: "Returned when success",
        content: new Model(type: ReadNewsModel::class)
    )]
    public function readAction(Request $request): JsonResponse
    {
        return new JsonResponse(['news' => 'read'], 200);
    }

    #[Route('/create', methods: ['POST'])] 
    #[OA\Tag(name: 'news')]
    #[Security(name: 'Bearer')]
    #[OA\Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_UNAUTHORIZED,
        description: "Returned when authorization is required",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_CREATED,
        description: "Returned when new news is success created",
    )]
    #[OA\Parameter(
        name: 'create_news',
        description: 'Create news',
        in: 'query',
        content: new Model(type: NewsModel::class)
    )]
    public function createAction(Request $request)
    {
        return new JsonResponse(['create'], 200);
    }

    #[Route('/edit/{news}', methods: ['PATCH'])] 
    #[OA\Tag(name: 'news')]
    #[Security(name: 'Bearer')]
    #[OA\Response(
        response: JsonResponse::HTTP_UNAUTHORIZED,
        description: "Returned when authorization is required",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Returned when news not found",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_BAD_REQUEST,
        description: "Returned when input data not valid",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_OK,
        description: "Returned when new news is success edited",
    )]
    #[OA\Parameter(
        name: 'edit_news',
        description: 'Edit news',
        in: 'query',
        content: new Model(type: NewsModel::class)
    )]
    public function updateAction(Request $request, News $news)
    {    
        return new JsonResponse(['edit'], 200);
    }

    #[Route('/delete/{news}', methods: ['DELETE'])] 
    #[OA\Tag(name: 'news')]
    #[Security(name: 'Bearer')]
    #[OA\Response(
        response: JsonResponse::HTTP_UNAUTHORIZED,
        description: "Returned when authorization is required",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Returned when news not found",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_OK,
        description: "Returned when new news is success deleted",
    )]
    public function deleteAction(Request $request, News $news)
    {
        return new JsonResponse($this->newsService->delete($news), JsonResponse::HTTP_OK);
    }
}
