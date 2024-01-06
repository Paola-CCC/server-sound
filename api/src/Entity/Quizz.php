<?php

namespace App\Entity;

use App\Repository\QuizzRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: QuizzRepository::class)]
class Quizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['quizz', 'quizz_id'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['quizz'])]
    private ?string $title = null;


    #[ORM\ManyToMany(mappedBy: 'Quizz', targetEntity: HistoQuizz::class, cascade: ['remove'])]
    private Collection $histoQuizzs;

    #[ORM\OneToOne(inversedBy: 'quizz')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['course_quizz'])]
    private ?Course $course = null;

    #[ORM\OneToMany(mappedBy: 'quizz', targetEntity: Question::class, cascade: ['remove'])]
    #[Groups(['quizz'])]
    private Collection $question;

    public function __construct()
    {
        $this->histoQuizzs = new ArrayCollection();
        $this->question = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
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
            $histoQuizz->addQuizz($this);
        }

        return $this;
    }

    public function removeHistoQuizz(HistoQuizz $histoQuizz): static
    {
        if ($this->histoQuizzs->removeElement($histoQuizz)) {
            $histoQuizz->removeQuizz($this);
        }

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestion(): Collection
    {
        return $this->question;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->question->contains($question)) {
            $this->question->add($question);
            $question->setQuizz($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->question->removeElement($question)) {
            if ($question->getQuizz() === $this) {
                $question->setQuizz(null);
            }
        }

        return $this;
    }
    
}
