<?php

namespace App\Controller;

use App\Entity\News;
use App\Model\NewsModel;
use App\Model\ReadNewsModel;
use App\Service\NewsService;
use App\Exception\AppBadRequestHttpException;
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
    public function readNewsById(Request $request, int $news): JsonResponse
    {
        if (empty($news)) {
            return new JsonResponse('News not found.', JsonResponse::HTTP_NOT_FOUND);
        }
        return new JsonResponse($news, JsonResponse::HTTP_OK);
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
    public function readNews(Request $request): JsonResponse
    {
        $data = $this->em->getRepository(News::class)->getNews($queryParam, $limit, $offset);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/create', methods: ['POST'])] 
    #[OA\Tag(name: 'news')]
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
    #[OA\RequestBody(
        description: 'Create news',
        content: new Model(type: NewsModel::class)
    )]
    public function createNews(Request $request)
    {
        $params = json_decode($request->getContent(), true); ///???????????
        try {
            $success = $this->newsService->createNews(
                $params['name'],
                $params['body'],
            );
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }
        
        return new JsonResponse([
            'success' => $success
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/edit/{news}', methods: ['PATCH'])] 
    #[OA\Tag(name: 'news')]
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
    #[OA\RequestBody(
        description: 'Edit news',
        content: new Model(type: NewsModel::class)
    )]
    public function updateNews(Request $request, News $news)
    {    
        return new JsonResponse(['edit'], JsonResponse::HTTP_OK);
    }

    #[Route('/delete/{news}', methods: ['DELETE'])] 
    #[OA\Tag(name: 'news')]
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
    public function deleteNews(Request $request, News $news)
    {
        $params = json_decode($request->getContent(), true); ///???????????
        try {
            $success = $this->newsService->createNews(
                $params['name'],
                $params['body'],
            );
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }
        
        return new JsonResponse([
            'success' => $success
            ], JsonResponse::HTTP_CREATED
        );
        return new JsonResponse($this->newsService->delete($news), JsonResponse::HTTP_OK);
    }
}
