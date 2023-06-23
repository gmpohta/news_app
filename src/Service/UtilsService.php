<?php

namespace App\Service;

use App\Exception\AppBadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class UtilsService
{
    public function validateAndDecodeJson(Request $request): ?array
    {
        $jsonDecoded = json_decode($request->getContent(), true);
        if (is_null($jsonDecoded)) {
            throw new AppBadRequestHttpException(
                errors: ['Bad JSON format.'], 
                code: Response::HTTP_BAD_REQUEST
            );
        }
        return $jsonDecoded;
    }
        
    
    public function validateForm(FormInterface $form): void
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $errors[$field] = $error->getMessage();
        }
        
        if (count($errors) > 0) {
            throw new AppBadRequestHttpException(
                errors: $errors, 
                code: Response::HTTP_BAD_REQUEST
            );
        }
    }
}