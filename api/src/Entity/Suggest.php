<?php

namespace App\Entity;

use App\Repository\SuggestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: SuggestRepository::class)]
class Suggest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['question'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['question'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'suggests')]
    private ?Question $question = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['question'])]
    private ?bool $response_expected = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function isResponseExpected(): ?bool
    {
        return $this->response_expected;
    }

    public function setResponseExpected(?bool $response_expected): static
    {
        $this->response_expected = $response_expected;

        return $this;
    }
}
