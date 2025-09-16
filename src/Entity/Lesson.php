<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/lesson',
            name: 'lesson_collection',
            security: null,
            normalizationContext: ['groups' => ['lesson:read'], 'enable_max_depth' => true]
        ),
        new Get(
            uriTemplate: '/lesson/{id}',
            name: 'lesson_id',
            security: null,
            normalizationContext: ['groups' => ['lesson:read'], 'enable_max_depth' => true]
        ),
        new Post(
            uriTemplate: '/lesson',
            name: 'lesson_create',
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['admin:write']],
            normalizationContext: ['groups' => ['admin:read'], 'enable_max_depth' => true]
        ),
        new Put(
            uriTemplate: '/lesson/{id}',
            name: 'lesson_update',
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['admin:write']],
            normalizationContext: ['groups' => ['admin:read'], 'enable_max_depth' => true]
        ),
        new Delete(
            uriTemplate: '/lesson/{id}',
            name: 'lesson_delete',
            security: "is_granted('ROLE_ADMIN')"
        )
    ]
)]
class Lesson extends Product
{

    #[ORM\Column(type: 'text')]
    #[Groups(['lesson:read'])]
    private string $description;

    #[ORM\Column(length: 255)]
    #[Assert\Url(message: 'L’URL de la vidéo doit être valide.')]
    #[Groups(['lesson:read'])]
    private string $videoUrl;

    #[ORM\ManyToOne(inversedBy: 'lessons', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['lesson:read', 'cursus:read'])]
    private ?Cursus $cursus = null;

    #[ORM\ManyToOne(inversedBy: 'lessonsCreated')]
    private ?User $created_by = null;

    #[ORM\ManyToOne(inversedBy: 'lessonsUpdated')]
    private ?User $updated_by = null;

    /**
     * @var Collection<int, EnrollmentLesson>
     */
    #[ORM\OneToMany(mappedBy: 'lesson', targetEntity: EnrollmentLesson::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private Collection $enrollmentLessons;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(mappedBy: 'lesson', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private Collection $orderItems;

    public function __construct()
    {
        parent::__construct();
        $this->enrollmentLessons = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getVideoUrl(): string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(string $videoUrl): self
    {
        $this->videoUrl = $videoUrl;
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

    public function getEnrollmentLessons(): Collection
    {
        return $this->enrollmentLessons;
    }

    public function addEnrollmentLesson(EnrollmentLesson $enrollmentLesson): static
    {
        if (!$this->enrollmentLessons->contains($enrollmentLesson)) {
            $this->enrollmentLessons->add($enrollmentLesson);
            $enrollmentLesson->setLesson($this);
        }
        return $this;
    }

    public function removeEnrollmentLesson(EnrollmentLesson $enrollmentLesson): static
    {
        if ($this->enrollmentLessons->removeElement($enrollmentLesson)) {
            if ($enrollmentLesson->getLesson() === $this) {
                $enrollmentLesson->setLesson(null);
            }
        }
        return $this;
    }

    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setLesson($this);
        }
        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getLesson() === $this) {
                $orderItem->setLesson(null);
            }
        }
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
}
