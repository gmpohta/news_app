<?php

namespace App\Controller;

use App\Entity\News;
use App\Model\News\CreateNewsModel;
use App\Model\News\PatchNewsModel;
use App\Model\News\ReadNewsModel;
use App\Service\NewsService;
use App\Exception\AppBadRequestHttpException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

#[Route("/api")] 
class NewsController extends AbstractController
{
    public function __construct(
        private NewsService $newsService
    ) {}
    
    #[Route("/read/{newsId}", methods: ["GET"])] 
    #[OA\Tag(name: "news")]
    #[Security(name: null)]
    #[OA\Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Returned when news not found",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_OK,
        description: "Returned when success",
        content: new Model(type: News::class, groups: ["read_news"])
    )]
    public function readNewsById(Request $request, $newsId): JsonResponse
    {
        try {
            $data = $this->newsService->getNewsById((int)$newsId);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new JsonResponse([
            'data' => $data
        ], JsonResponse::HTTP_OK);
    }

    #[Route("/read", methods: ["POST"])] 
    #[OA\Tag(name: "news")]
    #[Security(name: null)]
    #[OA\Response(
        response: JsonResponse::HTTP_NOT_FOUND,
        description: "Returned when not found",
    )]
    #[OA\Response(
        response: JsonResponse::HTTP_OK,
        description: "Returned when success",
        content: new Model(type: News::class, groups: ["read_news"])
    )]
    #[OA\RequestBody(
        description: "You can filter the news by user.",
        content: new Model(type: ReadNewsModel::class)
    )]
    public function readNews(Request $request): JsonResponse
    {
        $form = $this->createForm(ReadNewsModel::class);
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

    #[Route("/create", methods: ["POST"])] 
    #[OA\Tag(name: "news")]
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
        description: "Create news",
        content: new Model(type: CreateNewsModel::class)
    )]
    public function createNews(Request $request)
    {
        $form = $this->createForm(CreateNewsModel::class, new News);
        $form->submit(json_decode($request->getContent(), true));

        try {
            $data = $this->newsService->createNews($form);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new JsonResponse([
            'data' => $data
        ], JsonResponse::HTTP_OK);
    }

    #[Route("/patch/{newsId}", methods: ["PATCH"])] 
    #[OA\Tag(name: "news")]
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
        description: "Edit news",
        content: new Model(type: PatchNewsModel::class)
    )]
    public function patchNews(Request $request, $newsId)
    {    
        $form = $this->createForm(PatchNewsModel::class, new News);
        $form->submit(json_decode($request->getContent(), true));

        try {
            $data = $this->newsService->patchNews($form, (int)$newsId);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new JsonResponse([
            'data' => $data
        ], JsonResponse::HTTP_OK);
    }

    #[Route("/delete/{newsId}", methods: ["DELETE"])] 
    #[OA\Tag(name: "news")]
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
    public function deleteNews(Request $request, $newsId)
    {
        try {
            $success = $this->newsService->deleteNews((int)$newsId);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }
        
        return new JsonResponse([
            'data' => $success
        ], JsonResponse::HTTP_OK);
    }
}
