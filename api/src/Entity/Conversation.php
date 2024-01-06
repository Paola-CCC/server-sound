<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([ 'conversation' , 'messages'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'conversations_One')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'conversation' , 'messages'])]
    private ?User $userOne = null;

    #[ORM\ManyToOne(inversedBy: 'conversations_two')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'conversation' , 'messages'])]
    private ?User $userTwo = null;

    #[ORM\Column]
    #[Groups([ 'conversation' , 'messages'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: Message::class, orphanRemoval: true)]
    #[Groups([ 'conversation' , 'messages'])]
    private ?Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserOne(): ?User
    {
        return $this->userOne;
    }

    public function setUserOne(?User $userOne): static
    {
        $this->userOne = $userOne;

        return $this;
    }

    public function getUserTwo(): ?User
    {
        return $this->userTwo;
    }

    public function setUserTwo(?User $userTwo): static
    {
        $this->userTwo = $userTwo;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

        return $this;
    }
}
