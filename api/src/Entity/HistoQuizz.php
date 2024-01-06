<?php

namespace App\Entity;

use App\Repository\HistoQuizzRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoQuizzRepository::class)]
class HistoQuizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'histoQuizzs')]
    private Collection $User;

    #[ORM\ManyToMany(targetEntity: Quizz::class, inversedBy: 'histoQuizzs')]
    private Collection $Quizz;

    #[ORM\Column(nullable: true)]
    private ?int $Score = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $Date = null;

    public function __construct()
    {
        $this->User = new ArrayCollection();
        $this->Quizz = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->User;
    }

    public function addUser(User $user): static
    {
        if (!$this->User->contains($user)) {
            $this->User->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->User->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, Quizz>
     */
    public function getQuizz(): Collection
    {
        return $this->Quizz;
    }

    public function addQuizz(Quizz $quizz): static
    {
        if (!$this->Quizz->contains($quizz)) {
            $this->Quizz->add($quizz);
        }

        return $this;
    }

    public function removeQuizz(Quizz $quizz): static
    {
        $this->Quizz->removeElement($quizz);

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->Score;
    }

    public function setScore(?int $Score): static
    {
        $this->Score = $Score;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->Date;
    }

    public function setDate(?\DateTimeImmutable $Date): static
    {
        $this->Date = $Date;

        return $this;
    }
}
