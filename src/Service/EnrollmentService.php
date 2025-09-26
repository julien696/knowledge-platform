<?php

namespace App\Service;

use App\Entity\EnrollmentCursus;
use App\Entity\EnrollmentLesson;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class EnrollmentService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function createEnrollmentsFromOrder(Order $order): void
    {
        $user = $order->getUser();
        
        if (!$user) {
            throw new \InvalidArgumentException('Order must have a user');
        }

        foreach ($order->getOrderItems() as $orderItem) {
            if ($orderItem->getLesson()) {
                $this->createLessonEnrollment($user, $orderItem->getLesson());
            }
            
            if ($orderItem->getCursus()) {
                $this->createCursusEnrollment($user, $orderItem->getCursus());
            }
        }
    }

    private function createLessonEnrollment(User $user, $lesson): void
    {
        $existingEnrollment = $this->em->getRepository(EnrollmentLesson::class)
            ->findOneBy(['user' => $user, 'lesson' => $lesson]);

        if (!$existingEnrollment) {
            $enrollment = new EnrollmentLesson();
            $enrollment->setUser($user);
            $enrollment->setLesson($lesson);
            $enrollment->setInscription(new \DateTime());

            $this->em->persist($enrollment);
        }
    }

    private function createCursusEnrollment(User $user, $cursus): void
    {
        $existingEnrollment = $this->em->getRepository(EnrollmentCursus::class)
            ->findOneBy(['user' => $user, 'cursus' => $cursus]);

        if (!$existingEnrollment) {
            $enrollment = new EnrollmentCursus();
            $enrollment->setUser($user);
            $enrollment->setCursus($cursus);
            $enrollment->setInscription(new \DateTime());
            $enrollment->setTotalLessons(count($cursus->getLessons()));

            $this->em->persist($enrollment);
        }
    }

    public function hasAccessToLesson(User $user, $lesson): bool
    {
        if (is_numeric($lesson)) {
            $lesson = $this->em->getRepository(\App\Entity\Lesson::class)->find($lesson);
            if (!$lesson) {
                return false;
            }
        }

        $enrollmentLesson = $this->em->getRepository(EnrollmentLesson::class)
            ->findOneBy(['user' => $user, 'lesson' => $lesson]);

        if ($enrollmentLesson) {
            return true;
        }

        if ($lesson->getCursus()) {
            $enrollmentCursus = $this->em->getRepository(EnrollmentCursus::class)
                ->findOneBy(['user' => $user, 'cursus' => $lesson->getCursus()]);

            if ($enrollmentCursus) {
                return true;
            }
        }

        return false;
    }

    public function hasAccessToCursus(User $user, $cursus): bool
    {

        if (is_numeric($cursus)) {
            $cursus = $this->em->getRepository(\App\Entity\Cursus::class)->find($cursus);
            if (!$cursus) {
                return false;
            }
        }

        $enrollment = $this->em->getRepository(EnrollmentCursus::class)
            ->findOneBy(['user' => $user, 'cursus' => $cursus]);

        return $enrollment !== null;
    }

    public function getLessonEnrollments(int $lessonId): array
    {
        return $this->em->getRepository(EnrollmentLesson::class)
            ->findBy(['lesson' => $lessonId]);
    }

    public function getCursusEnrollments(int $cursusId): array
    {
        return $this->em->getRepository(EnrollmentCursus::class)
            ->findBy(['cursus' => $cursusId]);
    }
}

