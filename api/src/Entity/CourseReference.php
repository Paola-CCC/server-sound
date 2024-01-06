<?php

namespace App\Entity;

use App\Entity\Course;
use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CourseReferenceRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CourseReferenceRepository::class)]
class CourseReference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('main')]
    private ?int $id = null;
    
    #[ORM\ManyToOne(inversedBy: 'courseReferences')]
    #[ORM\JoinColumn(nullable: false)]
    // #[Groups('main')]
    private ?Course $course = null;

    #[Groups('main')]
    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    #[Groups('main')]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255)]
    #[Groups('main')]
    private ?string $mimeType = null;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    // public function setCourse(?Course $course): static
    // {
    //     $this->course = $course;

    //     return $this;
    // }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFilePath(): string
    {
        return UploaderHelper::SCORE_FOLDER.'/'.$this->getFilename();
    }
}
