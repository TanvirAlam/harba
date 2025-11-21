<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Service name is required')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Service name must be at least {{ limit }} characters',
        maxMessage: 'Service name cannot be longer than {{ limit }} characters'
    )]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Service duration is required')]
    #[Assert\Positive(message: 'Service duration must be a positive number')]
    #[Assert\Range(
        min: 1,
        max: 480,
        notInRangeMessage: 'Service duration must be between {{ min }} and {{ max }} minutes'
    )]
    private ?int $duration = null; // in minutes

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }
}