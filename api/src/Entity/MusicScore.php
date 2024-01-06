<?php
namespace App\Entity;
use Assert\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class MusicScore
{
    // assert not blank
    #[Assert\NotBlank]
    public $filename;
    // assert not blank
    #[Assert\NotBlank]
    private $data;

    private $decodedData;

    public function setData(?string $data)
    {
        $this->data = $data;
        $this->decodedData = base64_decode($data);
    }

    public function getDecodedData(): ?string
    {
        return $this->decodedData;
    }
}