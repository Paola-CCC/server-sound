<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('category','course_category')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('category','course_category')]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Course::class, inversedBy: 'categories')]
    #[Groups('category_course')]
    private Collection $course;

    #[ORM\ManyToMany(targetEntity: Forum::class, mappedBy: 'category')]
    // #[Groups('category_forum')]
    private Collection $forums;

    public function __construct()
    {
        $this->course = new ArrayCollection();
        $this->forums = new ArrayCollection();
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
     * @return Collection<int, Course>
     */
    public function getCourse(): Collection
    {
        return $this->course;
    }

    public function addCourse(Course $course): self
    {
        if (!$this->course->contains($course)) {
            $this->course->add($course);
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        $this->course->removeElement($course);

        return $this;
    }

    /**
     * @return Collection<int, Forum>
     */
    public function getForums(): Collection
    {
        return $this->forums;
    }

    public function addForum(Forum $forum): self
    {
        if (!$this->forums->contains($forum)) {
            $this->forums->add($forum);
            $forum->addCategory($this);
        }

        return $this;
    }

    public function removeForum(Forum $forum): self
    {
        if ($this->forums->removeElement($forum)) {
            $forum->removeCategory($this);
        }

        return $this;
    }
}
