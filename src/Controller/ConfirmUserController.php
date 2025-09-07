<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ConfirmUserController extends AbstractController
{
    #[Route('/api/confirm/{token}', name: 'user_confirm', methods: ['GET'])]
    public function confirm(string $token, UserRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user = $repo->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            return new JsonResponse(['error' => 'Token invalide'], 404);
        }

        $user->setIsVerified(true);
        $user->setConfirmationToken(null);
        $em->flush();

        return new JsonResponse(['message' => 'Compte confirmé avec succès.']);
    }
}
