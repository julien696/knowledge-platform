<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Lesson;
use App\Repository\LessonRepository;
use App\Repository\EnrollmentLessonRepository;
use App\Repository\EnrollmentCursusRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LessonContentProvider implements ProviderInterface
{
    public function __construct(
        private LessonRepository $lessonRepository,
        private EnrollmentLessonRepository $enrollmentLessonRepository,
        private EnrollmentCursusRepository $enrollmentCursusRepository,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Lesson
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            throw new AccessDeniedHttpException('Utilisateur non authentifié');
        }

        $lessonId = $uriVariables['id'] ?? null;
        
        if (!$lessonId) {
            return null;
        }

        $lesson = $this->lessonRepository->find($lessonId);
        
        if (!$lesson) {
            return null;
        }

        $enrollmentLesson = $this->enrollmentLessonRepository->findOneBy([
            'user' => $user,
            'lesson' => $lesson
        ]);

        if ($enrollmentLesson) {
            return $lesson;
        }

        if ($lesson->getCursus()) {
            $enrollmentCursus = $this->enrollmentCursusRepository->findOneBy([
                'user' => $user,
                'cursus' => $lesson->getCursus()
            ]);

            if ($enrollmentCursus) {
                return $lesson;
            }
        }

        throw new AccessDeniedHttpException('Vous n\'avez pas accès à ce contenu');
    }
}
