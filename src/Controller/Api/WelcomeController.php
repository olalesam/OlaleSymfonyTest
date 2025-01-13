<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class WelcomeController extends AbstractController
{
    #[Route('/api/home', name: 'api_home')]
    public function index(): JsonResponse
    {
        return new JsonResponse(['message' => 'Bienvenue sur l\'API !']);
    }
}
