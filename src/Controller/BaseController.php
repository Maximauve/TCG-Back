<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{

    #[Route('/', name: 'app_base')]
    public function index(): Response
    {
        return $this->json(
            [
                'message' => 'Welcome to the API',
            ]
        );
    }
}
