<?php

namespace App\Entity;

use App\Repository\ComposerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;


#[ORM\Entity(repositoryClass: ComposerRepository::class)]
class Composer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['composer', 'instruments_composers', 'course_composers'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['composer', 'instruments_composers', 'course_composers'])]
    #[MaxDepth(1)]
    private ?string $fullName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['composer'])]
    private ?string $biography = null;

    #[ORM\ManyToMany(targetEntity: Instrument::class, inversedBy: 'composers')]
    #[Groups(['instrument_composer'])]
    private Collection $instrument;

    #[ORM\ManyToMany(targetEntity: Course::class, mappedBy: 'composers')]
    #[Groups(['composer'])]
    private Collection $courses;

    public function __construct()
    {
        $this->instrument = new ArrayCollection();
        $this->courses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(string $biography): self
    {
        $this->biography = $biography;

        return $this;
    }

    /**
     * @return Collection<int, Instrument>
     */
    public function getInstrument(): Collection
    {
        return $this->instrument;
    }

    public function addInstrument(Instrument $instrument): self
    {
        if (!$this->instrument->contains($instrument)) {
            $this->instrument->add($instrument);
        }

        return $this;
    }

    public function removeInstrument(Instrument $instrument): self
    {
        $this->instrument->removeElement($instrument);

        return $this;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): self
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            $course->addComposer($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        if ($this->courses->removeElement($course)) {
            $course->removeComposer($this);
        }

        return $this;
    }
}
