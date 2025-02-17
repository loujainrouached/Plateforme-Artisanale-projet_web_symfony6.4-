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

#[ORM\Entity(repositoryClass: UserRepository::class)]/* 
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])] */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    private ?string $lastname = null;
//************************************************ */
    #[ORM\Column]
    private ?array $roles = [];

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 8, minMessage: "Votre mot de passe doit contenir au moins {{ limit }} caractères.")]
        private ?string $password = '';
     
     #[ORM\Column(length:255,unique:true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
#[Assert\Email( message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

   

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

public function getRoles(): array
{
    $roles = $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
}

/**
 * @param list<string> $roles
 */
public function setRoles(array $roles): static
{
    $this->roles = $roles;

    return $this;
}





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



}
