<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableTrait;
use App\Repository\EnrollmentCursusRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EnrollmentCursusRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EnrollmentCursus
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'enrollmentCursuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'enrollmentCursuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cursus $cursus = null;

    #[ORM\Column]
    #[Assert\DateTime]
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

    public function getCursus(): ?Cursus
    {
        return $this->cursus;
    }

    public function setCursus(?Cursus $cursus): static
    {
        $this->cursus = $cursus;

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
