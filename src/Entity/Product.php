<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\TimestampableTrait;
use App\Enum\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
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
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive(message : 'Le prix du produit doit être positif')]
    #[Assert\Type(type: 'numeric')]
    private ?float $price = null;

    #[ORM\Column(enumType: ProductType::class)]
    #[Assert\NotNull(message : 'Choisir un type de produit')]
    private ?ProductType $type = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?User $created_by = null;

    #[ORM\ManyToOne(inversedBy: 'updateProducts')]
    private ?User $updated_by = null;

    /**
     * @var Collection<int, Orderitem>
     */
    #[ORM\OneToMany(targetEntity: Orderitem::class, mappedBy: 'product')]
    private Collection $orderitems;

    public function __construct()
    {
        $this->orderitems = new ArrayCollection();
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
     * @return Collection<int, Orderitem>
     */
    public function getOrderitems(): Collection
    {
        return $this->orderitems;
    }

    public function addOrderitem(Orderitem $orderitem): static
    {
        if (!$this->orderitems->contains($orderitem)) {
            $this->orderitems->add($orderitem);
            $orderitem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderitem(Orderitem $orderitem): static
    {
        if ($this->orderitems->removeElement($orderitem)) {
            // set the owning side to null (unless already changed)
            if ($orderitem->getProduct() === $this) {
                $orderitem->setProduct(null);
            }
        }

        return $this;
    }
}
