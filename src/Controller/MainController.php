<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    #[Route('/read')] 
    public function createAction(Request $request)
    {
        return $this->render();
    }

    #[Route('/create')] 
    public function createAction(Request $request)
    {
        return $this->render();
    }

    #[Route('/edit')] 
    public function updateAction(int $id, Request $request)
    {    
        return $this->redirectToRoute('create_new_article');
    }

    #[Route('/delete')] 
    public function deleteAction(int $id, Request $request)
    {
        return $this->render();
    }
}
