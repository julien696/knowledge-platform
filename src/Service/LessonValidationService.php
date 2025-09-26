<?php

namespace App\Service;

use App\Entity\Cursus;
use App\Entity\EnrollmentCursus;
use App\Entity\EnrollmentLesson;
use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\CursusRepository;
use App\Repository\EnrollmentCursusRepository;
use App\Repository\EnrollmentLessonRepository;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class LessonValidationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EnrollmentLessonRepository $enrollmentLessonRepository,
        private EnrollmentCursusRepository $enrollmentCursusRepository,
        private LessonRepository $lessonRepository,
        private CursusRepository $cursusRepository
    ) {}


    public function validateLesson(User $user, int $lessonId): void
    {
        $lesson = $this->lessonRepository->find($lessonId);
        if (!$lesson) {
            throw new \InvalidArgumentException("Leçon introuvable avec l'ID: {$lessonId}");
        }

        $enrollment = $this->enrollmentLessonRepository->findOneBy([
            'user' => $user,
            'lesson' => $lesson
        ]);

        if ($enrollment) {
            $enrollment->setIsValidated(true);
            $enrollment->setValidatedAt(new DateTime());
            $this->em->flush();
            return;
        }

        if ($lesson->getCursus()) {
            $cursusEnrollment = $this->enrollmentCursusRepository->findOneBy([
                'user' => $user,
                'cursus' => $lesson->getCursus()
            ]);

            if ($cursusEnrollment) {
                $cursusEnrollment->addValidatedLesson($lesson->getId());
                $this->em->flush();
                $this->checkAndValidateCursus($user, $lesson->getCursus()->getId());
                return;
            }
        }

        throw new \InvalidArgumentException("L'utilisateur n'est pas inscrit à cette leçon");
    }


    public function checkCursusCompletion(User $user, int $cursusId): bool
    {
        $cursus = $this->cursusRepository->find($cursusId);
        if (!$cursus) {
            return false;
        }

        $enrollmentCursus = $this->enrollmentCursusRepository->findOneBy([
            'user' => $user,
            'cursus' => $cursus
        ]);

        if (!$enrollmentCursus) {
            return false;
        }

        return true;
    }


    public function autoValidateCursus(User $user, int $cursusId): void
    {
        $cursus = $this->cursusRepository->find($cursusId);
        if (!$cursus) {
            throw new \InvalidArgumentException("Cursus introuvable avec l'ID: {$cursusId}");
        }

        $enrollmentCursus = $this->enrollmentCursusRepository->findOneBy([
            'user' => $user,
            'cursus' => $cursus
        ]);

        if (!$enrollmentCursus) {
            throw new \InvalidArgumentException("L'utilisateur n'est pas inscrit à ce cursus");
        }

        $enrollmentCursus->setIsValidated(true);
        $enrollmentCursus->setValidatedAt(new DateTime());

        $this->em->flush();
    }


    public function isLessonValidated(User $user, int $lessonId): bool
    {
        $lesson = $this->lessonRepository->find($lessonId);
        if (!$lesson) {
            return false;
        }

        $enrollment = $this->enrollmentLessonRepository->findOneBy([
            'user' => $user,
            'lesson' => $lesson
        ]);

        return $enrollment && $enrollment->isValidated();
    }


    public function isCursusCompleted(User $user, int $cursusId): bool
    {
        $cursus = $this->cursusRepository->find($cursusId);
        if (!$cursus) {
            return false;
        }

        $enrollmentCursus = $this->enrollmentCursusRepository->findOneBy([
            'user' => $user,
            'cursus' => $cursus
        ]);

        return $enrollmentCursus && $enrollmentCursus->isValidated();
    }

    private function checkAndValidateCursus(User $user, int $cursusId): void
    {
        $cursus = $this->cursusRepository->find($cursusId);
        if (!$cursus) {
            return;
        }

        $enrollmentCursus = $this->enrollmentCursusRepository->findOneBy([
            'user' => $user,
            'cursus' => $cursus
        ]);

        if (!$enrollmentCursus) {
            return;
        }

        if ($enrollmentCursus->isAllLessonsValidated()) {
            $this->autoValidateCursus($user, $cursusId);
        }
    }

}
