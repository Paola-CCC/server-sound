<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use IntlDateFormatter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['messages' ,'conversation'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['messages'])]
    private ?string $contentText = null;

    #[ORM\Column]
    #[Groups(['messages' ,'conversation'])]
    private ?\DateTimeImmutable $sentDate = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['messages' ,'conversation'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContentText(): ?string
    {
        return $this->contentText;
    }

    public function setContentText(string $contentText): static
    {
        $this->contentText = $contentText;

        return $this;
    }

    public function getSentDate(): ?string
    {
        if ($this->sentDate instanceof \DateTimeImmutable) {
            $locale = 'fr_FR';
            $formatter = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
            $formatter->setPattern('dd/MM/yyyy HH:mm');

            return $formatter->format($this->sentDate);
        }

        return null;
    }

    public function setSentDate(\DateTimeImmutable $sentDate): static
    {
        $this->sentDate = $sentDate;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }
}
