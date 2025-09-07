<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class Order
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\DateTime]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    #[Assert\NotNull]
    #[Assert\Positive(message : 'Le prix du produit doit être positif')]
    #[Assert\Type(type: 'numeric')]
    private ?string $total = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'orderId', orphanRemoval: true)]
    private Collection $OrderItems;

    public function __construct()
    {
        $this->OrderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;

        return $this;
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
            $OrderItem->setOrderId($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $OrderItem): static
    {
        if ($this->OrderItems->removeElement($OrderItem)) {
            // set the owning side to null (unless already changed)
            if ($OrderItem->getOrderId() === $this) {
                $OrderItem->setOrderId(null);
            }
        }

        return $this;
    }
}
