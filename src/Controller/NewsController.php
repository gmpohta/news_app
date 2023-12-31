<?php

namespace App\Controller;

use App\Entity\News;
use App\Model\News\CreateNewsModel;
use App\Model\News\PatchNewsModel;
use App\Model\News\ReadNewsModel;
use App\Service\NewsService;
use App\Service\UtilsService;
use App\Exception\AppBadRequestHttpException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

#[Route("/api")] 
class NewsController extends AbstractController
{
    public function __construct(
        private NewsService $newsService,
        private UtilsService $utilsService
    ) {}
    
    #[Route("/read/{newsId}", methods: ["GET"])] 
    #[OA\Tag(name: "news")]
    #[Security(name: null)]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: "Returned when news not found",
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returned when success",
        content: new Model(type: News::class, groups: ["read_news"])
    )]
    public function readNewsById(Request $request, $newsId): Response
    {
        try {
            $data = $this->newsService->getNewsById((int)$newsId);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new Response($data, Response::HTTP_OK);
    }

    #[Route("/read", methods: ["POST"])] 
    #[OA\Tag(name: "news")]
    #[Security(name: null)]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: "Returned when not found",
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returned when success",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref:  new Model(type: News::class, groups: ["read_news"]))
        )
    )]
    #[OA\RequestBody(
        description: "You can filter the news by user.",
        content: new Model(type: ReadNewsModel::class)
    )]
    public function readNews(Request $request): Response
    {
        $form = $this->createForm(ReadNewsModel::class);
        
        try {
            $form->submit(
                $this->utilsService->validateAndDecodeJson($request)
            );

            $data = $this->newsService->getNewsWithParam($form);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new Response($data, Response::HTTP_OK);
    }

    #[Route("/create", methods: ["POST"])] 
    #[OA\Tag(name: "news")]
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
        content: new Model(type: News::class, groups: ["read_news"])
    )]
    #[OA\RequestBody(
        description: "Create news",
        content: new Model(type: CreateNewsModel::class)
    )]
    public function createNews(Request $request): Response
    {
        $form = $this->createForm(CreateNewsModel::class, new News);

        try {
            $form->submit(
                $this->utilsService->validateAndDecodeJson($request)
            );

            $data = $this->newsService->createNews($form);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new Response($data, Response::HTTP_OK);
    }

    #[Route("/patch/{newsId}", methods: ["PATCH"])] 
    #[OA\Tag(name: "news")]
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
        content: new Model(type: News::class, groups: ["read_news"])
    )]
    #[OA\RequestBody(
        description: "Edit news",
        content: new Model(type: PatchNewsModel::class)
    )]
    public function patchNews(Request $request, $newsId): Response
    {    
        $form = $this->createForm(PatchNewsModel::class, new News);

        try {
            $form->submit(
                $this->utilsService->validateAndDecodeJson($request)
            );
            
            $data = $this->newsService->patchNews($form, (int)$newsId);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }

        return new Response($data, Response::HTTP_OK);
    }

    #[Route("/delete/{newsId}", methods: ["DELETE"])] 
    #[OA\Tag(name: "news")]
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
    public function deleteNews(Request $request, $newsId): Response
    {
        try {
            $success = $this->newsService->deleteNews((int)$newsId);
        } catch (AppBadRequestHttpException $ex) {
            return new JsonResponse(['errors' => $ex->getErrors()], $ex->getCode());
        }
        
        return new Response($success, Response::HTTP_OK);
    }
}
