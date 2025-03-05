<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex(
        pattern: "/^[A-ZÀ-Ÿ][\p{L}\s]*$/u",
        message: "Author name must start with a capital letter and contain only letters and spaces."
    )]
    #[Assert\NotBlank(message: "The name is required.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex(
        pattern: "/^[A-ZÀ-Ÿ][\p{L}\s]*$/u",
        message: "Author name must start with a capital letter and contain only letters and spaces."
    )]
    #[Assert\NotBlank(message: "The Lastname is required.")]
    private ?string $lastname = null;
//************************************************ */
    #[ORM\Column]
    private ?array $roles = [];

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The password is required.")]
    #[Assert\Length(min: 8, minMessage: "Your password have to contain at least {{ limit }} leters.")]
        private ?string $password = '';
     
     #[ORM\Column(length:255,unique:true)]
    #[Assert\NotBlank(message: "The l'Email is required.")]
#[Assert\Email( message: "L'Email '{{ value }}' is not valid.")]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false], nullable: true)]
    private bool $isBanned = false;
   

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

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

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }
// **************************************************

/* 
public function getRoles(): array
{
    return [$this->roles?? 'ROLE_USER'];  // Default to 'ROLE_USER' if type is not set
}

public function setRoles(array $roles): self
{
    $this->roles = $roles;
} */

/* public function getRoles(): array
{
    $roles = $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = '';

    return array_unique($roles);
} */

public function getRoles(): array 
{
    return $this->roles ?? [];
}

public function setRoles(array $roles): static
{
    // Ensure only one role is set
    $this->roles = [reset($roles)];
    return $this;
}


/**
 * @param list<string> $roles
 */
/* public function setRoles(array $roles): static
{
    $this->roles = $roles;

    return $this;
} */





    public function getPassword(): ?string
    {
        return $this->password;
    }

   /*  public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    } */

    public function setPassword(?string $password): self
    {
        // If password is null or empty, do nothing (keep the old one)
        if (!empty($password)) {
            $this->password = $password;
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }




  


    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
 

    public function eraseCredentials(): void
    {
        // If you store sensitive data like plain passwords or tokens, erase them here
        // For example:
        // $this->plainPassword = null;
    }


    public function getIsBanned(): bool
{
    return $this->isBanned;
}

// Setter
public function setIsBanned(bool $isBanned): self
{
    $this->isBanned = $isBanned;
    return $this;
}

}
