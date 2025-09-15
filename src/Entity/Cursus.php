<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CursusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: CursusRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/cursus',
            name: 'cursus_collection',
            security: null,
            normalizationContext: ['groups' => ['cursus:read']]
        ),
        new Get(
            uriTemplate: '/cursus/{id}',
            name: 'cursus_id',
            security: null,
            normalizationContext: ['groups' => ['cursus:read']]
        ),
        new Post(
            uriTemplate: '/cursus',
            name: 'cursus_create',
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['admin:write']],
            normalizationContext: ['groups' => ['admin:read']]
        ),
        new Put(
            uriTemplate: '/cursus/{id}',
            name: 'cursus_update',
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['admin:write']],
            normalizationContext: ['groups' => ['admin:read']]
        ),
        new Delete(
            uriTemplate: '/cursus/{id}',
            name: 'cursus_delete',
            security: "is_granted('ROLE_ADMIN')"
        )
    ]
)]
class Cursus extends Product
{
    #[ORM\ManyToOne(inversedBy: 'cursus')]
    #[ORM\JoinColumn(nullable: true)]
    #[MaxDepth(1)]
    private ?Theme $theme = null;

    #[ORM\ManyToOne(inversedBy: 'cursusCreated')]
    private ?User $created_by = null;

    #[ORM\ManyToOne(inversedBy: 'cursusUpdated')]
    private ?User $updated_by = null;

    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'cursus')]
    #[MaxDepth(1)]
    #[Groups(['cursus:read'])]
    private Collection $lessons;

    #[ORM\OneToMany(targetEntity: EnrollmentCursus::class, mappedBy: 'cursus')]
    #[MaxDepth(1)]
    private Collection $enrollmentCursus;

    public function __construct()
    {
        parent::__construct();
        $this->lessons = new ArrayCollection();
        $this->enrollmentCursus = new ArrayCollection();
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $user): static
    {
        $this->created_by = $user;
        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(?User $user): static
    {
        $this->updated_by = $user;
        return $this;
    }

    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setCursus($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            if ($lesson->getCursus() === $this) {
                $lesson->setCursus(null);
            }
        }

        return $this;
    }

    public function getEnrollmentCursus(): Collection
    {
        return $this->enrollmentCursus;
    }

    public function addEnrollmentCursus(EnrollmentCursus $enrollmentCursus): static
    {
        if (!$this->enrollmentCursus->contains($enrollmentCursus)) {
            $this->enrollmentCursus->add($enrollmentCursus);
            $enrollmentCursus->setCursus($this);
        }

        return $this;
    }

    public function removeEnrollmentCursus(EnrollmentCursus $enrollmentCursus): static
    {
        if ($this->enrollmentCursus->removeElement($enrollmentCursus)) {
            if ($enrollmentCursus->getCursus() === $this) {
                $enrollmentCursus->setCursus(null);
            }
        }

        return $this;
    }
}

