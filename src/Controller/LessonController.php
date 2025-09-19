<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\EnrollmentService;
use App\Service\LessonValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LessonController extends AbstractController
{
    public function __construct(
        private LessonValidationService $validationService,
        private EnrollmentService $enrollmentService,
        private Security $security
    ) {}

    #[Route('/api/lessons/{id}/validate', name: 'validate_lesson', methods: ['POST'])]
    public function validateLesson(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->enrollmentService->hasAccessToLesson($user, $id)) {
            return new JsonResponse(['error' => 'Accès refusé - Vous devez d\'abord acheter cette leçon'], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->validationService->validateLesson($user, $id);
            
            return new JsonResponse([
                'message' => 'Leçon validée avec succès',
                'lesson_id' => $id,
                'validated_at' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
            
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la validation'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/lessons/{id}/validation-status', name: 'lesson_validation_status', methods: ['GET'])]
    public function getLessonValidationStatus(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->security->isGranted('ROLE_ADMIN') && !$this->enrollmentService->hasAccessToLesson($user, $id)) {
            return new JsonResponse(['error' => 'Accès refusé - Vous devez d\'abord acheter cette leçon'], Response::HTTP_FORBIDDEN);
        }

        $isValidated = $this->validationService->isLessonValidated($user, $id);
        
        return new JsonResponse([
            'lesson_id' => $id,
            'is_validated' => $isValidated,
            'user_id' => $user->getId()
        ]);
    }

    #[Route('/api/cursus/{id}/validation-status', name: 'cursus_validation_status', methods: ['GET'])]
    public function getCursusValidationStatus(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->security->isGranted('ROLE_ADMIN') && !$this->enrollmentService->hasAccessToCursus($user, $id)) {
            return new JsonResponse(['error' => 'Accès refusé - Vous devez d\'abord acheter ce cursus'], Response::HTTP_FORBIDDEN);
        }

        $isCompleted = $this->validationService->isCursusCompleted($user, $id);
        
        return new JsonResponse([
            'cursus_id' => $id,
            'is_completed' => $isCompleted,
            'user_id' => $user->getId()
        ]);
    }

    #[Route('/api/admin/lessons/{id}/validations', name: 'admin_lesson_validations', methods: ['GET'])]
    public function getLessonValidationsForAdmin(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        
        if (!$user || !$this->security->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé - Admin requis'], Response::HTTP_FORBIDDEN);
        }

        $enrollments = $this->enrollmentService->getLessonEnrollments($id);
        
        $validations = [];
        foreach ($enrollments as $enrollment) {
            $validations[] = [
                'user_id' => $enrollment->getUser()->getId(),
                'user_name' => $enrollment->getUser()->getName(),
                'user_email' => $enrollment->getUser()->getEmail(),
                'is_validated' => $enrollment->isValidated(),
                'validated_at' => $enrollment->getValidatedAt()?->format('Y-m-d H:i:s'),
                'inscription_date' => $enrollment->getInscription()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse([
            'lesson_id' => $id,
            'total_enrollments' => count($validations),
            'validations' => $validations
        ]);
    }

    #[Route('/api/admin/cursus/{id}/validations', name: 'admin_cursus_validations', methods: ['GET'])]
    public function getCursusValidationsForAdmin(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        
        if (!$user || !$this->security->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé - Admin requis'], Response::HTTP_FORBIDDEN);
        }
        
        $enrollments = $this->enrollmentService->getCursusEnrollments($id);
        
        $validations = [];
        foreach ($enrollments as $enrollment) {
            $validations[] = [
                'user_id' => $enrollment->getUser()->getId(),
                'user_name' => $enrollment->getUser()->getName(),
                'user_email' => $enrollment->getUser()->getEmail(),
                'is_completed' => $enrollment->isValidated(),
                'completed_at' => $enrollment->getValidatedAt()?->format('Y-m-d H:i:s'),
                'inscription_date' => $enrollment->getInscription()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse([
            'cursus_id' => $id,
            'total_enrollments' => count($validations),
            'validations' => $validations
        ]);
    }
}
