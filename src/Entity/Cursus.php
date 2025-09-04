<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CursusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CursusRepository::class)]
#[ApiResource]
class Cursus extends Product
{
    #[ORM\ManyToOne(inversedBy: 'cursus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Theme $theme = null;

    /**
     * @var Collection<int, Lesson>
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'cursus')]
    private Collection $lessons;

    /**
     * @var Collection<int, EnrollmentCursus>
     */
    #[ORM\OneToMany(targetEntity: EnrollmentCursus::class, mappedBy: 'cursus')]
    private Collection $enrollmentCursus;

    public function __construct()
    {
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

    /**
     * @return Collection<int, Lesson>
     */
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
            // set the owning side to null (unless already changed)
            if ($lesson->getCursus() === $this) {
                $lesson->setCursus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EnrollmentCursus>
     */
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
            // set the owning side to null (unless already changed)
            if ($enrollmentCursus->getCursus() === $this) {
                $enrollmentCursus->setCursus(null);
            }
        }

        return $this;
    }
}
