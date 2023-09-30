<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[Groups(['task'])]
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;

    #[Groups(['task'])]
    #[ORM\Column]
    protected array $input = [];

    #[Groups(['task'])]
    #[ORM\Column]
    protected array $output = [];

    #[Groups(['task'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $reserved_at = null;

    #[Groups(['task'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $finished_at = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    protected ?User $user = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function getInput(): array
    {
        return $this->input;
    }

    public function setInput(array $input): static
    {
        $this->input = $input;

        return $this;
    }

    public function getOutput(): array
    {
        return $this->output;
    }

    public function setOutput(array $output): static
    {
        $this->output = $output;

        return $this;
    }

    public function getReservedAt(): ?\DateTimeInterface
    {
        return $this->reserved_at;
    }

    public function setReservedAt(?\DateTimeInterface $reserved_at): static
    {
        $this->reserved_at = $reserved_at;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finished_at;
    }

    public function setFinishedAt(\DateTimeInterface $finished_at): static
    {
        $this->finished_at = $finished_at;

        return $this;
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
}
