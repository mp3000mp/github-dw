<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Request\JsonRequestHelper;
use App\Helper\Response\JsonResponseHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;

abstract class AbstractController extends SymfonyAbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly JsonRequestHelper $requestHelper,
        protected readonly JsonResponseHelper $responseHelper,
    ) {
    }
}
