<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   /*  #[ORM\ManyToOne(inversedBy: 'reclamations')]
    #[ORM\JoinColumn(nullable: false)] */

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reclamations')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")] 
    private ?User $user = null;

    

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message:"Subject is required")]
    #[Assert\Length(
        min: 5,
        minMessage: "Subject must be at least {{ limit }} characters long"
    )]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message:"message is required")]
    #[Assert\Length(
        min: 8,
        minMessage: "Message must be at least {{ limit }} characters long"
    )]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    private ?string $status ='open';

    #[ORM\Column]
    private ?bool $is_marked = null;

    #[ORM\Column(type: 'datetime_immutable')] // ✅ Ensure correct type
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Reponse>
     */
    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'reclamation', orphanRemoval: true)]
    private Collection $reponses;

    public function __construct()
    {
        
/*         $this->created_at = new \DateTimeImmutable(); // Set default timestamp
 */
        $this->createdAt = new \DateTimeImmutable(); // Set default timestamp
        $this->status = 'open'; // ✅ Ensures status is set when a new object is created
        $this->is_marked = false;
        $this->reponses = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

  /*   public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    } */



    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }



    
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

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

    public function isMarked(): ?bool
    {
        return $this->is_marked;
    }

    public function setIsMarked(bool $is_marked): static
    {
        $this->is_marked = $is_marked;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->createdAt = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setReclamation($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getReclamation() === $this) {
                $reponse->setReclamation(null);
            }
        }

        return $this;
    }
}
