<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableTrait;
use App\Repository\EnrollmentLessonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EnrollmentLessonRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EnrollmentLesson
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['me:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'enrollmentLessons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'enrollmentLessons')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['me:read'])]
    private ?Lesson $lesson = null;

    #[ORM\Column]
    #[Assert\DateTime]
    #[Groups(['me:read'])]
    private ?\DateTime $inscription = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;

        return $this;
    }

    public function getInscription(): ?\DateTime
    {
        return $this->inscription;
    }

    public function setInscription(\DateTime $inscription): static
    {
        $this->inscription = $inscription;

        return $this;
    }
}
