<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Request\JsonRequestHelper;
use App\Helper\Response\JsonResponseHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;

abstract class AbstractController extends SymfonyAbstractController
{
    protected EntityManagerInterface $em;
    protected JsonRequestHelper $requestHelper;
    protected JsonResponseHelper $responseHelper;

    public function __construct(EntityManagerInterface $em, JsonRequestHelper $requestHelper, JsonResponseHelper $responseHelper)
    {
        $this->em = $em;
        $this->responseHelper = $responseHelper;
        $this->requestHelper = $requestHelper;
    }
}
