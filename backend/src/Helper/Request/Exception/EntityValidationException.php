<?php

namespace App\Helper\Request\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class EntityValidationException extends HttpException implements HttpExceptionInterface
{
}
