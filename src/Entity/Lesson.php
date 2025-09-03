<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ApiResource]
class Lesson extends Product
{
    #[ORM\ManyToOne(inversedBy: 'lessons')]
    private ?Cursus $cursus = null;

    /**
     * @var Collection<int, EnrollmentLesson>
     */
    #[ORM\OneToMany(targetEntity: EnrollmentLesson::class, mappedBy: 'lesson')]
    private Collection $enrollmentLessons;

    public function __construct()
    {
        parent::__construct();
        $this->enrollmentLessons = new ArrayCollection();
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
            $enrollmentLesson->setLesson($this);
        }

        return $this;
    }

    public function removeEnrollmentLesson(EnrollmentLesson $enrollmentLesson): static
    {
        if ($this->enrollmentLessons->removeElement($enrollmentLesson)) {
            // set the owning side to null (unless already changed)
            if ($enrollmentLesson->getLesson() === $this) {
                $enrollmentLesson->setLesson(null);
            }
        }

        return $this;
    }
}
