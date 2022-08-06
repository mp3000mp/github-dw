<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'app.home', methods: ['GET'])]
    public function home(ParameterBagInterface $parameterBag): Response
    {
        return $this->redirect($parameterBag->get('app.front_url'));
    }
}
