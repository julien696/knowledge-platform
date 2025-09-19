<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\EnrollmentCursusRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EnrollmentCursusRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['enrollment:read']]),
        new Get(normalizationContext: ['groups' => ['enrollment:read']]),
    ]
)]
class EnrollmentCursus
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['me:read', 'enrollment:read', 'admin:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'enrollmentCursuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'enrollmentCursuses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['me:read', 'enrollment:read', 'admin:read'])]
    private ?Cursus $cursus = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['me:read', 'enrollment:read', 'admin:read'])]
    private bool $isValidated = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['me:read', 'enrollment:read', 'admin:read'])]
    private ?\DateTime $validatedAt = null;

    #[ORM\Column]
    #[Assert\DateTime]
    #[Groups(['me:read', 'enrollment:read', 'admin:read'])]
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

    public function isValidated(): bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): static
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getValidatedAt(): ?\DateTime
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTime $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }
}
