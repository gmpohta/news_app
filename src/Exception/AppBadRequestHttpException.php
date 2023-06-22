<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AppBadRequestHttpException extends BadRequestHttpException {

    private ?array $errors;

    public function __construct($errors = null, $message = 'Bad Request', \Exception $previous = null, $code = 0) {
        parent::__construct($message, $previous, $code);
        $this->errors = $errors;
    }

    public function getErrors(): ?array 
    {
        return $this->errors;
    }
}