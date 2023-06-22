<?php

namespace App\Service;

use App\Exception\AppBadRequestHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\FormInterface;

class UtilsService
{
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
                code: JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }
}