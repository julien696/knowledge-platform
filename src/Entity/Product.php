<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\TimestampableTrait;
use App\Enum\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "discr", type: "string")]
#[ORM\DiscriminatorMap(["cursus" => Cursus::class, "lesson" => Lesson::class])]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class Product
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message : 'Le nom du produit est obligatoire')]
    #[Groups(['me:read'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive(message : 'Le prix du produit doit être positif')]
    #[Assert\Type(type: 'numeric')]
    #[Groups(['me:read'])]
    private ?float $price = null;

    #[ORM\Column(enumType: ProductType::class)]
    #[Assert\NotNull(message : 'Choisir un type de produit')]
    private ?ProductType $type = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?User $created_by = null;

    #[ORM\ManyToOne(inversedBy: 'updateProducts')]
    private ?User $updated_by = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'product')]
    private Collection $OrderItems;

    public function __construct()
    {
        $this->OrderItems = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getType(): ?ProductType
    {
        return $this->type;
    }

    public function setType(ProductType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $created_by): static
    {
        $this->created_by = $created_by;

        return $this;
    }


    public function getUpdatedBy(): ?User
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(?User $updated_by): static
    {
        $this->updated_by = $updated_by;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->OrderItems;
    }

    public function addOrderItem(OrderItem $OrderItem): static
    {
        if (!$this->OrderItems->contains($OrderItem)) {
            $this->OrderItems->add($OrderItem);
            $OrderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $OrderItem): static
    {
        if ($this->OrderItems->removeElement($OrderItem)) {
            // set the owning side to null (unless already changed)
            if ($OrderItem->getProduct() === $this) {
                $OrderItem->setProduct(null);
            }
        }

        return $this;
    }
}
