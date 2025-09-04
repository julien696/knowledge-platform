<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Trait\TimestampableTrait;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class Theme
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du Théme est obligatoire')]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'themes')]
    private ?User $created_by = null;

    /**
     * @var Collection<int, Cursus>
     */
    #[ORM\OneToMany(targetEntity: Cursus::class, mappedBy: 'theme', orphanRemoval: true)]
    private Collection $cursus;

    /**
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'theme')]
    private Collection $certifications;

    public function __construct()
    {
        $this->cursus = new ArrayCollection();
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

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $created_by): static
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * @return Collection<int, Cursus>
     */
    public function getCursuses(): Collection
    {
        return $this->cursus;
    }

    public function addCursus(Cursus $cursus): static
    {
        if (!$this->cursus->contains($cursus)) {
            $this->cursus->add($cursus);
            $cursus->setTheme($this);
        }

        return $this;
    }

    public function removeCursus(Cursus $cursus): static
    {
        if ($this->cursus->removeElement($cursus)) {
            // set the owning side to null (unless already changed)
            if ($cursus->getTheme() === $this) {
                $cursus->setTheme(null);
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
            $certification->setTheme($this);
        }

        return $this;
    }

    public function removeCertification(Certification $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
            // set the owning side to null (unless already changed)
            if ($certification->getTheme() === $this) {
                $certification->setTheme(null);
            }
        }

        return $this;
    }
}
