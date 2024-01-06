<?php

namespace App\Entity;

use App\Entity\Images;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user','forum_user_id' , 'course_users' , 'course_comments', 'forum_answer', 'rating_user' , 'messages' , 'progression' ,'course_professor'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user', 'course_users', 'course_comments', 'forum_answer', 'forum' ,'messages' , 'progression','course_professor'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user', 'course_users', 'course_comments', 'forum_answer', 'forum', 'messages' , 'progression' ,'course_professor'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user','progression'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user','messages','forum_answer','forum','course_professor'])]
    private ?string $photo = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    #[Groups(['user','progression'])]
    private array $roles = [];

    #[ORM\ManyToMany(targetEntity: Course::class, inversedBy: 'users',cascade: ["remove"])]
    #[Groups(['user_courses'])]
    #[MaxDepth(1)]
    private Collection $courses;

    #[ORM\ManyToMany(targetEntity: Instrument::class, inversedBy: 'users')]
    #[Groups(['user_instruments','progression'])]
    #[MaxDepth(1)]
    private Collection $instruments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class,cascade: ["remove"])]
    #[Groups(['user_comments'])]
    #[MaxDepth(1)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'professor', targetEntity: Course::class ,cascade: ["remove"])]
    #[Groups(['user_coursesGiven'])]
    private Collection $coursesGiven;
    
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Forum::class ,cascade: ["remove"])]
    #[Groups(['user_forums'])]
    private Collection $forums;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Answer::class , cascade: ["remove"])]
    #[Groups(['user_responses'])]
    private Collection $responses;

    #[ORM\Column(nullable: true)]
    #[Groups(['user', 'course_users','progression'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['user', 'course_users','progression'])]
    private ?string $biography = null;
    

    #[ORM\ManyToMany(targetEntity: HistoQuizz::class, mappedBy: 'User',cascade: ["remove"])]
    private Collection $histoQuizzs;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $messages;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(['user','progression'])]
    private ?Subscription $subscription = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Progression::class ,cascade: ["remove"])]
    private Collection $progressions;

    #[ORM\OneToOne(targetEntity: Images::class, inversedBy: "user", cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(name: "image_id", referencedColumnName: "id", nullable: true)]
    #[Groups(['user','progression'])]
    private ?Images $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\OneToMany(mappedBy: 'userOne', targetEntity: Conversation::class ,cascade: ["remove"])]
    private Collection $conversations_One;

    #[ORM\OneToMany(mappedBy: 'userTwo', targetEntity: Conversation::class ,cascade: ["remove"])]
    private Collection $conversations_two;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Rating::class, cascade: ["remove"])]
    private Collection $ratingsUsers;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->instruments = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->coursesGiven = new ArrayCollection();
        $this->forums = new ArrayCollection();
        $this->responses = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->histoQuizzs = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->progressions = new ArrayCollection();
        $this->image = null;
        $this->conversations_One = new ArrayCollection();
        $this->conversations_two = new ArrayCollection();
        $this->ratingsUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
    /**
    * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(): static
    {
        $this->username = $this->getFirstName() . ' ' . $this->getLastName();

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données temporaires sensibles sur l'utilisateur, effacez-les ici
        // $this->plainPassword = null;
    }


    public function getRoles(): array
    {
        $roles = $this->roles;
        // // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        // // array_unique
        return $roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

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
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        $this->courses->removeElement($course);

        return $this;
    }

    /**
     * @return Collection<int, Instrument>
     */
    public function getInstruments(): Collection
    {
        return $this->instruments;
    }

    public function addInstrument(Instrument $instrument): self
    {
        if (!$this->instruments->contains($instrument)) {
            $this->instruments->add($instrument);
        }

        return $this;
    }

    public function removeInstrument(Instrument $instrument): self
    {
        $this->instruments->removeElement($instrument);

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // Définissez le côté propriétaire à null (sauf si déjà modifié)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCoursesGiven(): Collection
    {
        return $this->coursesGiven;
    }

    public function addCoursesGiven(Course $coursesGiven): self
    {
        if (!$this->coursesGiven->contains($coursesGiven)) {
            $this->coursesGiven->add($coursesGiven);
            $coursesGiven->setProfessor($this);
        }

        return $this;
    }

    public function removeCoursesGiven(Course $coursesGiven): self
    {
        if ($this->coursesGiven->removeElement($coursesGiven)) {
            // Définissez le côté propriétaire à null (sauf si déjà modifié)
            if ($coursesGiven->getProfessor() === $this) {
                $coursesGiven->setProfessor(null);
            }
        }

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
            $forum->setAuthor($this);
        }

        return $this;
    }

    public function removeForum(Forum $forum): self
    {
        if ($this->forums->removeElement($forum)) {
            // Définissez le côté propriétaire à null (sauf si déjà modifié)
            if ($forum->getAuthor() === $this) {
                $forum->setAuthor(null);
            }
        }

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
            $response->setAuthor($this);
        }

        return $this;
    }

    public function removeResponse(Answer $response): self
    {
        if ($this->responses->removeElement($response)) {
            // Définissez le côté propriétaire à null (sauf si déjà modifié)
            if ($response->getAuthor() === $this) {
                $response->setAuthor(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        if ($this->createdAt instanceof \DateTimeImmutable) {
            return $this->createdAt->format('d/m/Y H:i');
        }   

        return null;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): void
    {
        $this->biography = $biography;
    }
    
    /**
     * @return Collection<int, HistoQuizz>
     */
    public function getHistoQuizzs(): Collection
    {
        return $this->histoQuizzs;
    }

    public function addHistoQuizz(HistoQuizz $histoQuizz): static
    {
        if (!$this->histoQuizzs->contains($histoQuizz)) {
            $this->histoQuizzs->add($histoQuizz);
            $histoQuizz->addUser($this);
        }

        return $this;
    }


    public function removeHistoQuizz(HistoQuizz $histoQuizz): static
    {
        if ($this->histoQuizzs->removeElement($histoQuizz)) {
            $histoQuizz->removeUser($this);
        }

        return $this;
    }
    /**
     * @return Collection<int, Message>
     */

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setAuthor($this);
        }

        return $this;
    }


    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }


    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): static
    {
        $this->subscription = $subscription;

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
            $progression->setUser($this);
        }

        return $this;
    }

    public function removeProgression(Progression $progression): static
    {
        if ($this->progressions->removeElement($progression)) {
            // set the owning side to null (unless already changed)
            if ($progression->getUser() === $this) {
                $progression->setUser(null);
            }
        }

        return $this;
    } 
  
    public function getImage(): ?Images {
        return $this->image;
    }

    public function setImage(?Images $image): self
    {
        $this->image = $image;
    
        // Ceci garantit que les deux côtés de la relation sont mis à jour
        if ($image !== null && $image->getUser() !== $this) {
            $image->setUser($this);
        }
    
        return $this;
    }


    /**
     * @return Collection<int, Conversation>
     */
    public function getConversationsOne(): Collection
    {
        return $this->conversations_One;
    }

    public function addConversationsOne(Conversation $conversationsOne): static
    {
        if (!$this->conversations_One->contains($conversationsOne)) {
            $this->conversations_One->add($conversationsOne);
            $conversationsOne->setUserOne($this);
        }

        return $this;
    }

    public function removeConversationsOne(Conversation $conversationsOne): static
    {
        if ($this->conversations_One->removeElement($conversationsOne)) {
            // set the owning side to null (unless already changed)
            if ($conversationsOne->getUserOne() === $this) {
                $conversationsOne->setUserOne(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversationsTwo(): Collection
    {
        return $this->conversations_two;
    }

    public function addConversationsTwo(Conversation $conversationsTwo): static
    {
        if (!$this->conversations_two->contains($conversationsTwo)) {
            $this->conversations_two->add($conversationsTwo);
            $conversationsTwo->setUserTwo($this);
        }

        return $this;
    }

    public function removeConversationsTwo(Conversation $conversationsTwo): static
    {
        if ($this->conversations_two->removeElement($conversationsTwo)) {
            // set the owning side to null (unless already changed)
            if ($conversationsTwo->getUserTwo() === $this) {
                $conversationsTwo->setUserTwo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatingsUsers(): Collection
    {
        return $this->ratingsUsers;
    }

    public function addRatingsUser(Rating $ratingsUser): static
    {
        if (!$this->ratingsUsers->contains($ratingsUser)) {
            $this->ratingsUsers->add($ratingsUser);
            $ratingsUser->setUser($this);
        }

        return $this;
    }

    public function removeRatingsUser(Rating $ratingsUser): static
    {
        if ($this->ratingsUsers->removeElement($ratingsUser)) {
            // set the owning side to null (unless already changed)
            if ($ratingsUser->getUser() === $this) {
                $ratingsUser->setUser(null);
            }
        }

        return $this;
    }


}
