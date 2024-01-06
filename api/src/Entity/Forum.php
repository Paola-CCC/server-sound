<?php

namespace App\Entity;

use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ForumRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use IntlDateFormatter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ForumRepository::class)]
class Forum
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['forum'])]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(['forum'])]
    private ?string $subject = null;

    #[ORM\Column(nullable:'false')]
    #[Groups(['forum'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'forums')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['forum'])]
    private ?User $author = null;
   
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'forums')]
    #[Groups(['forum_category','forum'])]
    private Collection $category;

    #[ORM\OneToMany(mappedBy: 'forum', targetEntity: Answer::class, cascade:["remove"])]
    #[Groups(['forum_answer','forum'])]
    private Collection $responses;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable('user_forum_like')]
    private Collection $likes;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable('user_forum_dislike')]
    private Collection $dislikes;
    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['forum'])]
    private ?string $description = null;

    #[Groups(['forum_answers_count'])]
    private ?int $answersCount = 0;

    #[Groups(['likes_forum_count'])]
    private ?int $likesCount = 0;
    
    #[Groups(['dislikes_forum_count'])]
    private ?int $dislikesCount = 0;

    public function __construct()
    {
        $this->category = new ArrayCollection();
        $this->responses = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->dislikes = new ArrayCollection();
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        if ($this->createdAt instanceof \DateTimeImmutable) {
            $locale = 'fr_FR';
            $formatter = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
            $formatter->setPattern('dd/MM/yyyy HH:mm');
            return $formatter->format($this->createdAt);
        }   

        return null;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->category->removeElement($category);

        return $this;
    }

    /**
     * @return Collection<int, Response>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(Answer $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
            $response->setForum($this);
        }

        return $this;
    }

    public function removeResponse(Answer $response): self
    {
        if ($this->responses->removeElement($response)) {
            // set the owning side to null (unless already changed)
            if ($response->getForum() === $this) {
                $response->setForum(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAnswersCount(): int
    {
        return $this->answersCount;
    }

    public function setAnswersCount(int $answersCount): self
    {
        $this->answersCount = $answersCount;
        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(User $like): self
    {
        if(!$this->likes->contains($like)) {
            $this->likes[] = $like;
        }

        return $this;
    }

    public function getLikesCount(): int
    {
        return $this->likesCount;
    }

    public function setLikesCount(int $likes): self
    {
        $this->likesCount = $likes;
        return $this;
    }

    public function removeLike(User $like): self
    {
        $this->likes->removeElement($like);

        return $this;
    }

    public function isLikedByUser(User $user): bool
    {
        return $this->likes->contains($user);
    }

    public function howManyLikes(): int
    {
        return count($this->likes);
    }
    
    public function getDislikes(): Collection
    {
        return $this->dislikes;
    }

    public function addDisLike(User $dislike): self
    {
        if(!$this->dislikes->contains($dislike)) {
            $this->dislikes[] = $dislike;
        }

        return $this;
    }

    public function getDislikesCount(): int
    {
        return $this->dislikesCount;
    }

    public function setDislikesCount(int $dislikes): self
    {
        $this->dislikesCount = $dislikes;
        return $this;
    }

    public function removeDislike(User $dislike): self
    {
        $this->dislikes->removeElement($dislike);

        return $this;
    }

    public function isDislikedByUser(User $user): bool
    {
        return $this->dislikes->contains($user);
    }

    public function howManyDislikes(): int
    {
        return count($this->dislikes);
    }
}
