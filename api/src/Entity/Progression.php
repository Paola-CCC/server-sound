<?php

namespace App\Entity;

use App\Repository\ProgressionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use IntlDateFormatter;

#[ORM\Entity(repositoryClass: ProgressionRepository::class)]
class Progression
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['course', 'progression' ,'user'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'progressions')]
    #[Groups(['course', 'progression' ,'user'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'progressions')]
    #[Groups(['course', 'progression' ,'user'])]
    private ?Course $course = null;


    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['course', 'progression' ,'user'])]
    private ?string $videoTimestamp = null;

    #[ORM\Column]
    #[Groups(['course', 'progression' ,'user'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['course', 'progression' ,'user'])]
    private ?\DateTimeImmutable $updateAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['course', 'progression' ,'user'])]
    private ?int $percentageWatched = null;

    #[ORM\Column(length: 255)]
    #[Groups(['course', 'progression' ,'user'])]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['course', 'progression' ,'user'])]
    private ?string $QuizzStatus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getVideoTimestamp(): ?string
    {
        return $this->videoTimestamp;
    }

    public function setVideoTimestamp(?string $videoTimestamp): static
    {
        $this->videoTimestamp = $videoTimestamp;

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
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): ?string
    {

        if ($this->updateAt instanceof \DateTimeImmutable) {
            $locale = 'fr_FR';
            $formatter = new IntlDateFormatter($locale, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
            $formatter->setPattern('dd/MM/yyyy HH:mm');

            return $formatter->format($this->updateAt);
        }
    }

    public function setUpdateAt(?\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getPercentageWatched(): ?int
    {
        return $this->percentageWatched;
    }

    public function setPercentageWatched(?int $percentageWatched): static
    {
        $this->percentageWatched = $percentageWatched;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getQuizzStatus(): ?string
    {
        return $this->QuizzStatus;
    }

    public function setQuizzStatus(?string $QuizzStatus): static
    {
        $this->QuizzStatus = $QuizzStatus;

        return $this;
    }
}
