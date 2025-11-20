<?php

namespace App\Entity;

use App\Repository\ProviderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProviderRepository::class)]
class Provider
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'json')]
    private array $workingHours = []; // e.g., ['monday' => '09:00-17:00', 'tuesday' => '09:00-17:00', ...]

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

    public function getWorkingHours(): array
    {
        return $this->workingHours;
    }

    public function setWorkingHours(array $workingHours): static
    {
        $this->workingHours = $workingHours;

        return $this;
    }
}