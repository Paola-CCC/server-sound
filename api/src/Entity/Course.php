<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\User;
use App\Entity\Composer;
use App\Entity\Quizz;
use App\Entity\Images;
use IntlDateFormatter;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['course', 'composer', 'instruments_composers', 'rating_course'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['course', 'composer', 'instruments_composers', 'course_title'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['course'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['course'])]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['course'])]
    private ?int $ratingScore = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $files = [];

    #[ORM\Column(length: 255)]
    #[Groups(['course'])]
    private ?string $linkVideo = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'courses')]
    #[Groups(['course_users'])]
    #[MaxDepth(1)]
    private Collection $users;

    #[ORM\ManyToOne(inversedBy: 'coursesGiven')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['course_professor'])]
    #[MaxDepth(1)]
    private ?User $professor = null;

    // nullable
    #[ORM\ManyToMany(targetEntity: Composer::class, inversedBy: 'courses')]
    #[Groups(['course_composers'])]
    #[MaxDepth(1)]
    private Collection $composers;

    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'course')]
    #[Groups(['course_category'])]
    private Collection $categories;
// true nullable
    #[ORM\ManyToOne(inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['course_instruments'])]
    #[MaxDepth(1)]
    private ?Instrument $instrument = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Comment::class, cascade: ["remove"])]
    #[Groups(['course_comments'])]
    #[MaxDepth(1)]
    private Collection $comments;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['course'])]
    private ?string $preview = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['course'])]
    private ?string $photo = null;

    #[ORM\Column(nullable: false)]
    #[Groups(['course'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['course'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Rating::class, cascade: ["remove"])]
    #[Groups(['course', 'progression' ])]
    private Collection $ratings;

    #[ORM\OneToOne(mappedBy: 'course', cascade: ['remove'])]
    #[Groups(['quizz_course'])]
    private ?Quizz $quizz = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Progression::class,cascade: ["remove"])]
    private Collection $progressions;

    #[ORM\OneToOne(targetEntity: Images::class, inversedBy: "course", cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(name: "image_id", referencedColumnName: "id", nullable: true)]
    private ?Images $image = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: CourseReference::class)]
    private Collection $courseReferences;
    
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->composers = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->progressions = new ArrayCollection();
        $this->image = null;
        $this->courseReferences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getRatingScore(): ?int
    {
        return $this->ratingScore;
    }

    public function setRatingScore(?int $ratingScore): self
    {
        $this->ratingScore = $ratingScore;

        return $this;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(?array $files): self
    {
        $this->files = $files;

        return $this;
    }

    public function getLinkVideo(): ?string
    {
        return $this->linkVideo;
    }

    public function setLinkVideo(string $linkVideo): self
    {
        $this->linkVideo = $linkVideo;

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
            $user->addCourse($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeCourse($this);
        }

        return $this;
    }

    public function getProfessor(): ?User
    {
        return $this->professor;
    }

    public function setProfessor(?User $professor): self
    {
        $this->professor = $professor;

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
        }

        return $this;
    }

    public function removeComposer(Composer $composer): self
    {
        $this->composers->removeElement($composer);

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addCourse($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            $category->removeCourse($this);
        }

        return $this;
    }

    public function getInstrument(): ?Instrument
    {
        return $this->instrument;
    }

    public function setInstrument(?Instrument $instrument): self
    {
        $this->instrument = $instrument;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setCourse($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCourse() === $this) {
                $comment->setCourse(null);
            }
        }

        return $this;
    }

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function setPreview(?string $preview): static
    {
        $this->preview = $preview;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        if ($this->createdAt instanceof \DateTimeImmutable) {
            $locale = 'fr_FR';
            $formatter = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
            $formatter->setPattern('dd/MM/yyyy');

            return $formatter->format($this->createdAt);
        } 

        return null;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        if ($this->updatedAt instanceof \DateTimeImmutable) {
            return $this->updatedAt->format('Y-m-d H:i:s');
        }   

        return null;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setCourse($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getCourse() === $this) {
                $rating->setCourse(null);
            }
        }

        return $this;
    }

    public function getQuizz(): ?Quizz
    {
        return $this->quizz;
    }

    public function setQuizz(Quizz $quizz): static
    {
        // set the owning side of the relation if necessary
        if ($quizz->getCourse() !== $this) {
            $quizz->setCourse($this);
        }

        $this->quizz = $quizz;

        return $this;
    }

    /**
     * @return Collection<int, Progression>
     */
    public function getProgressions(): Collection
    {
        return $this->progressions;
    }

    public function addProgression(Progression $progression): static
    {
        if (!$this->progressions->contains($progression)) {
            $this->progressions->add($progression);
            $progression->setCourse($this);
        }

        return $this;
    }

    public function removeProgression(Progression $progression): static
    {
        if ($this->progressions->removeElement($progression)) {
            // set the owning side to null (unless already changed)
            if ($progression->getCourse() === $this) {
                $progression->setCourse(null);
            }
        }

        return $this;
    }

    public function getImage(): ?Images {
        return $this->image;
    }

    public function setImage(?Images $image): self {
        $this->image = $image;

        // set (or unset) the owning side of the relation if necessary
        $newCourse = null === $image ? null : $this;
        if ($image && $image->getCourse() !== $newCourse) {
            $image->setCourse($newCourse);
        }

        return $this;
    }

    /**
     * @return Collection<int, CourseReference>
     */
    public function getCourseReferences(): Collection
    {
        return $this->courseReferences;
    }

    public function addCourseReference(CourseReference $courseReference): static
    {
        if (!$this->courseReferences->contains($courseReference)) {
            $this->courseReferences->add($courseReference);
            $courseReference->setCourse($this);
        }

        return $this;
    }

    public function removeCourseReference(CourseReference $courseReference): static
    {
        if ($this->courseReferences->removeElement($courseReference)) {
            // set the owning side to null (unless already changed)
            if ($courseReference->getCourse() === $this) {
                $courseReference->setCourse(null);
            }
        }

        return $this;
    }
}
