<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class Product
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['admin:read', 'admin:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire')]
    #[Groups(['admin:read', 'admin:write'])]
    private ?string $name = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Le prix du produit doit être positif')]
    #[Groups(['admin:read', 'admin:write'])]
    private ?float $price = null;

    public function __construct() {}

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
}
