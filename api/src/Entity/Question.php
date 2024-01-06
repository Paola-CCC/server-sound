<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Quizz;


#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['question'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['question'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'question')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Quizz $quizz = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Suggest::class, cascade: ['remove'])]
    #[Groups(['question'])]
    private Collection $suggests;

    public function __construct()
    {
        $this->suggests = new ArrayCollection();
    }

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

    public function getQuizz(): ?Quizz
    {
        return $this->quizz;
    }

    public function setQuizz(?Quizz $quizz): static
    {
        $this->quizz = $quizz;

        return $this;
    }

    /**
     * @return Collection<int, Suggest>
     */
    public function getSuggests(): Collection
    {
        return $this->suggests;
    }

    public function addSuggest(Suggest $suggest): static
    {
        if (!$this->suggests->contains($suggest)) {
            $this->suggests->add($suggest);
            $suggest->setQuestion($this);
        }

        return $this;
    }

    public function removeSuggest(Suggest $suggest): static
    {
        if ($this->suggests->removeElement($suggest)) {
            // set the owning side to null (unless already changed)
            if ($suggest->getQuestion() === $this) {
                $suggest->setQuestion(null);
            }
        }

        return $this;
    }

}
