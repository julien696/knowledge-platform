<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function index(): JsonResponse
    {
    
        return $this->json([
            'error' => 'Authentication failed',
            'message' => 'Authentication should be handled by the security system'
        ], 401);
    }
}
