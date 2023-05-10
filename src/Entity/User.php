<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUsers'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['getUsers'])]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères', maxMessage: 'Le prénom doit contenir au maximum {{ limit }} caractères')]
    private ?string $firstname = null;

    #[ORM\Column(length: 100)]
    #[Groups(['getUsers'])]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères', maxMessage: 'Le nom doit contenir au maximum {{ limit }} caractères')]
    private ?string $lastname = null;

    #[ORM\Column(length: 200)]
    #[Groups(['getUsers'])]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'L\'email doit être valide')]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $Client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->Client;
    }

    public function setClient(?Client $Client): self
    {
        $this->Client = $Client;

        return $this;
    }
}
