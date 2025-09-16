<?php

namespace App\Controller;

use App\Entity\Cursus;
use App\Repository\CursusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CursusController extends AbstractController
{
    public function __construct(
        private CursusRepository $cursusRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('/api/cursus/optimized', name: 'api_cursus_optimized', methods: ['GET'])]
    public function getOptimizedCursusList(Request $request): JsonResponse
    {
        // Récupérer les paramètres de pagination
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        
        // Récupérer les cursus avec leurs relations optimisées
        $cursusList = $this->cursusRepository->findAllWithRelations($page, $limit) ?? [];
        
        // Sérialisation personnalisée
        $data = array_map(function(Cursus $cursus) {
            return [
                'id' => $cursus->getId(),
                'name' => $cursus->getName(),
                'theme' => $cursus->getTheme() ? [
                    'id' => $cursus->getTheme()->getId(),
                    'name' => $cursus->getTheme()->getName()
                ] : null,
                'lessons' => array_map(function($lesson) {
                    return [
                        'id' => $lesson->getId(),
                        'title' => $lesson->getTitle(),
                        'description' => $lesson->getDescription(),
                        'videoUrl' => $lesson->getVideoUrl()
                    ];
                }, $cursus->getLessons()->toArray()),
                'createdAt' => $cursus->getCreatedAt() ? $cursus->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'updatedAt' => $cursus->getUpdatedAt() ? $cursus->getUpdatedAt()->format('Y-m-d H:i:s') : null
            ];
        }, $cursusList);

        return new JsonResponse($data);
    }
}
