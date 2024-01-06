<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['forum_answer'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['forum_answer'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['forum_answer'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'responses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['forum_answer'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'responses')]
    // WIP : Set it to null (It should be)
    #[ORM\JoinColumn(nullable: false)]
    private ?Forum $forum = null;

    public function __construct() 
    {
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        if ($this->createdAt instanceof \DateTimeImmutable) {
            return $this->createdAt->format('Y-m-d H:i:s');
        }   

        return null;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        if ($this->createdAt instanceof \DateTimeImmutable) {
            return $this->createdAt->format('Y-m-d H:i:s');
        }   

        return null;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): self
    {
        $this->forum = $forum;

        return $this;
    }
}
