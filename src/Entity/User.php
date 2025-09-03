<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(enumType: UserRole::class)]
    private ?UserRole $role = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, Theme>
     */
    #[ORM\OneToMany(targetEntity: Theme::class, mappedBy: 'created_by')]
    private Collection $themes;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'created_by')]
    private Collection $products;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'updated_by')]
    private Collection $updateProducts;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    /**
     * @var Collection<int, EnrollmentCursus>
     */
    #[ORM\OneToMany(targetEntity: EnrollmentCursus::class, mappedBy: 'user')]
    private Collection $enrollmentCursuses;

    /**
     * @var Collection<int, EnrollmentLesson>
     */
    #[ORM\OneToMany(targetEntity: EnrollmentLesson::class, mappedBy: 'user')]
    private Collection $enrollmentLessons;

    /**
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'user')]
    private Collection $certifications;

    public function __construct()
    {
        $this->themes = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->updateProducts = new ArrayCollection();
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

    public function getRole(): ?UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
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
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCreatedBy($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCreatedBy() === $this) {
                $product->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getUpdateProducts(): Collection
    {
        return $this->updateProducts;
    }

    public function addUpdateProduct(Product $updateProduct): static
    {
        if (!$this->updateProducts->contains($updateProduct)) {
            $this->updateProducts->add($updateProduct);
            $updateProduct->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeUpdateProduct(Product $updateProduct): static
    {
        if ($this->updateProducts->removeElement($updateProduct)) {
            // set the owning side to null (unless already changed)
            if ($updateProduct->getUpdatedBy() === $this) {
                $updateProduct->setUpdatedBy(null);
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
