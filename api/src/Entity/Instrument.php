<?php

namespace App\Entity;

use App\Repository\InstrumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InstrumentRepository::class)]
class Instrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['instrument', 'composer', 'course_composers'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['instrument', 'composer', 'course_composers'])]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'instruments')]
    #[Groups(['instruments_users'])]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Composer::class, mappedBy: 'instrument')]
    #[Groups(['instruments_composers'])]
    private Collection $composers;

    #[ORM\Column(type: Types::ARRAY)]
    #[Groups(['instrument', 'composer', 'course_composers'])]
    private array $level = [];

    #[ORM\OneToMany(mappedBy: 'instrument', targetEntity: Course::class)]
    #[Groups(['instruments_courses'])]
    private Collection $courses;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->composers = new ArrayCollection();
        $this->courses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addInstrument($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeInstrument($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Composer>
     */
    public function getComposers(): Collection
    {
        return $this->composers;
    }

    public function addComposer(Composer $composer): self
    {
        if (!$this->composers->contains($composer)) {
            $this->composers->add($composer);
            $composer->addInstrument($this);
        }

        return $this;
    }

    public function removeComposer(Composer $composer): self
    {
        if ($this->composers->removeElement($composer)) {
            $composer->removeInstrument($this);
        }

        return $this;
    }

    public function getLevel(): array
    {
        return $this->level;
    }

    public function setLevel(array $level): self
    {
        $this->level = $level;

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
            $course->setInstrument($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        if ($this->courses->removeElement($course)) {
            // set the owning side to null (unless already changed)
            if ($course->getInstrument() === $this) {
                $course->setInstrument(null);
            }
        }

        return $this;
    }
}
