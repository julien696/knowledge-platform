<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Trait\TimestampableTrait;
use App\State\UserStateProcessor;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        // Opération d'inscription (publique)
        new Post(
            uriTemplate: '/register',
            processor: UserStateProcessor::class,
            name: 'user_register',
            normalizationContext: ['groups' => ['user_register:read']],
            denormalizationContext: ['groups' => ['user_register:write']],
            validationContext: ['groups' => ['user:register']]
        ),
        new GetCollection(
            uriTemplate:'/users/list',
            name:'user_list',
            normalizationContext:['groups' => ['user_list:read']]
        ),
        // Opérations d'administration
        new \ApiPlatform\Metadata\GetCollection(
            uriTemplate:'/users/list_admin',
            name: 'user_list_admin',
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['admin_list:read']]
        ),
        new \ApiPlatform\Metadata\Get(
            uriTemplate: '/user/{id}/admin',
            name: 'user_by_id_admin',
            security: "is_granted('ROLE_ADMIN') or object == user",
            normalizationContext: ['groups' => ['admin_user_id:read']]
        ),
         new \ApiPlatform\Metadata\Get(
            uriTemplate: '/user/{id}',
            name: 'user_by_id',
            normalizationContext: ['groups' => ['user_id:read', 'me:read']]
        ),
        new \ApiPlatform\Metadata\Post(
            uriTemplate: '/admin/users',
            name: 'post_user',
            security: "is_granted('ROLE_ADMIN')",
            processor: UserStateProcessor::class,
            denormalizationContext: ['groups' => ['admin:write']],
            normalizationContext: ['groups' => ['admin:read']],
            output: User::class
        ),
        new \ApiPlatform\Metadata\Get(
            uriTemplate: '/admin/users/{id}',
            name: 'get_user_admin',
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['admin:read']]
        ),
        new \ApiPlatform\Metadata\Get(
            uriTemplate: '/admin/users',
            name: 'get_users_admin',
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['admin:read']]
        ),
        new \ApiPlatform\Metadata\Put(
            uriTemplate: '/admin/users/{id}',
            name: 'put_user',
            security: "is_granted('ROLE_ADMIN') or object == user",
            processor: UserStateProcessor::class,
            denormalizationContext: ['groups' => ['admin:write']],
            normalizationContext: ['groups' => ['admin:read']]
        ),
        new \ApiPlatform\Metadata\Delete(
            security: "is_granted('ROLE_ADMIN')"
        )
    ],
)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('email', message: 'Cet email est déjà utilisé')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'user_list:read',
        'me:read',
        'admin_list:read',
        'admin_user_id:read',
        'user_id:read',
        'user_register:read',
        'admin:read'
        ])]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide.')]
    #[Assert\Length(
        min: 2,
        max: 20,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Groups([
        'user_register:write',
        'user_register:read',
        'user_list:read',
        'me:read',
        'admin_list:read',
        'admin:write',
        'admin:read',
        'admin_user_id:read',
        'user_id:read'
        ])]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email(
        message: "L'email {{ value }} n'est pas valide.",
    )]
    #[Groups([
        'user_register:write',
        'user_register:read',
        'user_list:read',
        'me:read',
        'admin_list:read',
        'admin:read',
        'admin:write',
        'admin_user_id:read',
        'user_id:read'
        ])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;
    
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire', groups: ['user:register'])]
    #[Assert\Length(
        min: 6, 
        max: 255,
        minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères."
        )]
    #[Groups([
        'user_register:write',
        'admin:write',
        'user_register:read'
        ])]
    private ?string $plainPassword = null;

    #[ORM\Column(enumType: UserRole::class)]
    #[Groups([
        'admin_list:read',
        'admin:write',
        'admin:read',
        'admin_user_id:read'
        ])]
    private ?UserRole $role = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups([
        'me:read',
        'admin_list:read',
        'admin:read',
        'admin:write',
        'admin_user_id:read'
        ])]
    private bool $isVerified = false;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $confirmationToken = null;

    /**
     * @var Collection<int, Theme>
     */
    #[ORM\OneToMany(targetEntity: Theme::class, mappedBy: 'created_by')]
    #[Groups(['me:read', 'admin_user_id:read',])]
    #[MaxDepth(1)]
    private Collection $themes;

    /**
     * @var Collection<int, Lesson>
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'created_by')]
    #[MaxDepth(1)]
    private Collection $lessonsCreated;

    /**
     * @var Collection<int, Lesson>
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'updated_by')]
    #[MaxDepth(1)]
    private Collection $lessonsUpdated;

    /**
     * @var Collection<int, Cursus>
     */
    #[ORM\OneToMany(targetEntity: Cursus::class, mappedBy: 'created_by')]
    #[MaxDepth(1)]
    private Collection $cursusCreated;

    /**
     * @var Collection<int, Cursus>
     */
    #[ORM\OneToMany(targetEntity: Cursus::class, mappedBy: 'updated_by')]
    #[MaxDepth(1)]
    private Collection $cursusUpdated;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    #[Groups(['user:read', 'admin_user_id:read'])]
    #[MaxDepth(1)]
    private Collection $orders;

    /**
     * @var Collection<int, EnrollmentCursus>
     */
    #[ORM\OneToMany(targetEntity: EnrollmentCursus::class, mappedBy: 'user')]
    #[Groups(['me:read', 'admin_user_id'])]
    #[MaxDepth(1)]
    private Collection $enrollmentCursuses;

    /**
     * @var Collection<int, EnrollmentLesson>
     */
    #[ORM\OneToMany(targetEntity: EnrollmentLesson::class, mappedBy: 'user')]
    #[Groups(['me:read', 'admin_user_id'])]
    #[MaxDepth(1)]
    private Collection $enrollmentLessons;

    /**
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'user')]
    #[Groups(['me:read', 'admin_user_id:read'])]
    #[MaxDepth(1)]
    private Collection $certifications;

    public function __construct()
    {
        $this->themes = new ArrayCollection();
        $this->lessonsCreated = new ArrayCollection();
        $this->lessonsUpdated = new ArrayCollection();
        $this->cursusCreated = new ArrayCollection();
        $this->cursusUpdated = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->enrollmentCursuses = new ArrayCollection();
        $this->enrollmentLessons = new ArrayCollection();
        $this->certifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getRole(): ?UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return [$this->role ? 'ROLE_' . strtoupper($this->role->value) : 'ROLE_USER'];
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $token): self
    {
        $this->confirmationToken = $token;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Theme>
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): static
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
            $theme->setCreatedBy($this);
        }

        return $this;
    }

    public function removeTheme(Theme $theme): static
    {
        if ($this->themes->removeElement($theme)) {
            // set the owning side to null (unless already changed)
            if ($theme->getCreatedBy() === $this) {
                $theme->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessonsCreated(): Collection
    {
        return $this->lessonsCreated;
    }

    public function addLessonCreated(Lesson $lesson): static
    {
        if (!$this->lessonsCreated->contains($lesson)) {
            $this->lessonsCreated->add($lesson);
            $lesson->setCreatedBy($this);
        }

        return $this;
    }

    public function removeLessonCreated(Lesson $lesson): static
    {
        if ($this->lessonsCreated->removeElement($lesson)) {
            // set the owning side to null (unless already changed)
            if ($lesson->getCreatedBy() === $this) {
                $lesson->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getLessonsUpdated(): Collection
    {
        return $this->lessonsUpdated;
    }

    public function addLessonUpdated(Lesson $lesson): static
    {
        if (!$this->lessonsUpdated->contains($lesson)) {
            $this->lessonsUpdated->add($lesson);
            $lesson->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeLessonUpdated(Lesson $lesson): static
    {
        if ($this->lessonsUpdated->removeElement($lesson)) {
            if ($lesson->getUpdatedBy() === $this) {
                $lesson->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cursus>
     */
    public function getCursusCreated(): Collection
    {
        return $this->cursusCreated;
    }

    public function addCursusCreated(Cursus $cursus): static
    {
        if (!$this->cursusCreated->contains($cursus)) {
            $this->cursusCreated->add($cursus);
            $cursus->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCursusCreated(Cursus $cursus): static
    {
        if ($this->cursusCreated->removeElement($cursus)) {
            if ($cursus->getCreatedBy() === $this) {
                $cursus->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getCursusUpdated(): Collection
    {
        return $this->cursusUpdated;
    }

    public function addCursusUpdated(Cursus $cursus): static
    {
        if (!$this->cursusUpdated->contains($cursus)) {
            $this->cursusUpdated->add($cursus);
            $cursus->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeCursusUpdated(Cursus $cursus): static
    {
        if ($this->cursusUpdated->removeElement($cursus)) {
            if ($cursus->getUpdatedBy() === $this) {
                $cursus->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EnrollmentCursus>
     */
    public function getEnrollmentCursuses(): Collection
    {
        return $this->enrollmentCursuses;
    }

    public function addEnrollmentCursus(EnrollmentCursus $enrollmentCursus): static
    {
        if (!$this->enrollmentCursuses->contains($enrollmentCursus)) {
            $this->enrollmentCursuses->add($enrollmentCursus);
            $enrollmentCursus->setUser($this);
        }

        return $this;
    }

    public function removeEnrollmentCursus(EnrollmentCursus $enrollmentCursus): static
    {
        if ($this->enrollmentCursuses->removeElement($enrollmentCursus)) {
            // set the owning side to null (unless already changed)
            if ($enrollmentCursus->getUser() === $this) {
                $enrollmentCursus->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EnrollmentLesson>
     */
    public function getEnrollmentLessons(): Collection
    {
        return $this->enrollmentLessons;
    }

    public function addEnrollmentLesson(EnrollmentLesson $enrollmentLesson): static
    {
        if (!$this->enrollmentLessons->contains($enrollmentLesson)) {
            $this->enrollmentLessons->add($enrollmentLesson);
            $enrollmentLesson->setUser($this);
        }

        return $this;
    }

    public function removeEnrollmentLesson(EnrollmentLesson $enrollmentLesson): static
    {
        if ($this->enrollmentLessons->removeElement($enrollmentLesson)) {
            // set the owning side to null (unless already changed)
            if ($enrollmentLesson->getUser() === $this) {
                $enrollmentLesson->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Certification>
     */
    public function getCertifications(): Collection
    {
        return $this->certifications;
    }

    public function addCertification(Certification $certification): static
    {
        if (!$this->certifications->contains($certification)) {
            $this->certifications->add($certification);
            $certification->setUser($this);
        }

        return $this;
    }

    public function removeCertification(Certification $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
            // set the owning side to null (unless already changed)
            if ($certification->getUser() === $this) {
                $certification->setUser(null);
            }
        }

        return $this;
    }
}
