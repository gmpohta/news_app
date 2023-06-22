<?php

namespace App\Controller;

use App\Entity\News;
use App\Model\News\NewsModel;
use App\Model\News\ReadNewsModel;
use App\Model\News\ReadNewsRequestModel;
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
    
    #[Route('/read/{newsId}', methods: ['GET'])] 
    #[OA\Tag(name: 'news')]
    #[Security(name: null)]
    #[OA\Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Returned when news not found",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_OK,
        description: "Returned when success",
        content: new Model(type: NewsModel::class)
    )]
    public function readNewsById(Request $request, ?int $newsId): JsonResponse
    {
        $form = $this->createForm(ReadNewsRequestModel::class);
        $form->submit(json_decode($request->getContent(), true));

        try {
            $data = $this->newsService->getNewsById($form);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new JsonResponse([
            'data' => $data
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/read', methods: ['POST'])] 
    #[OA\Tag(name: 'news')]
    #[Security(name: null)]
    #[OA\Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Returned when not found",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_OK,
        description: "Returned when success",
        content: new Model(type: ReadNewsModel::class)
    )]
    #[OA\RequestBody(
        description: 'Create news',
        content: new Model(type: ReadNewsRequestModel::class)
    )]
    public function readNews(Request $request): JsonResponse
    {
        $form = $this->createForm(ReadNewsRequestModel::class);
        $form->submit(json_decode($request->getContent(), true));
        
        try {
            $data = $this->newsService->getNewsWithParam($form);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new JsonResponse([
            'data' => $data
        ], JsonResponse::HTTP_OK);
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
        $params = json_decode($request->getContent(), true); //add validate input
        try {
            $success = $this->newsService->createNews(
                $params['name'],
                $params['body'],
            );
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }
        
        return new JsonResponse([
            'data' => $success
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/patch/{newsId}', methods: ['PATCH'])] 
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
    public function patchNews(Request $request, int $newsId)
    {    
        $params = json_decode($request->getContent(), true); //add validate input
        try {
            $success = $this->newsService->patchNews(
                $params['name'],
                $params['body'],
                $newsId
            );
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }
        
        return new JsonResponse([
            'data' => $success
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/delete/{newsId}', methods: ['DELETE'])] 
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
    public function deleteNews(Request $request, int $newsId)
    {
        //add validate input
        try {
            $success = $this->newsService->deleteNews($newsId);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }
        
        return new JsonResponse([
            'data' => $success
        ], JsonResponse::HTTP_OK);
    }
}
