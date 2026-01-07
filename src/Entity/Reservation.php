<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: "Seats Reserved must be between 1 and 5.")]
    private ?int $seatsReserved = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Notes must be at least {{ limit }} characters long.",
        maxMessage: "Notes cannot be longer than {{ limit }} characters."
    )]

    private ?string $notes = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateReservation  = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uniqueCode = null;


    #[ORM\ManyToOne(inversedBy: 'Reservation')]
/*     #[ORM\JoinColumn(onDelete: 'CASCADE')]
 */    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'Reservation', cascade: ['REMOVE'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Workshop $workshop = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeatsReserved(): ?int
    {
        return $this->seatsReserved;
    }

    public function setSeatsReserved(int $seatsReserved): static
    {
        $this->seatsReserved = $seatsReserved;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

 
    
    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->dateReservation;
    }
    
    public function setDateReservation(\DateTimeInterface $dateReservation): self
    {
        $this->dateReservation = $dateReservation;
        return $this;
    }
    public function getUniqueCode(): ?string
    {
        return $this->uniqueCode;
    }

    public function setUniqueCode(?string $uniqueCode): static
    {
        $this->uniqueCode = $uniqueCode;

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

    public function getWorkshop(): ?Workshop
    {
        return $this->workshop;
    }

    public function setWorkshop(?Workshop $workshop): static
    {
        $this->workshop = $workshop;

        return $this;
    }
}
