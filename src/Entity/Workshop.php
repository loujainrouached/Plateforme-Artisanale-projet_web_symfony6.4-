<?php

namespace App\Entity;

use App\Repository\WorkshopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkshopRepository::class)]
class Workshop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Please provide a title.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Title must be at least {{ limit }} characters long.",
        maxMessage: "Title cannot be longer than {{ limit }} characters."
    )]

    private ?string $title = null;
    


    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Please provide a description.")]
    #[Assert\Length(max: 1000, maxMessage: "The description cannot be longer than {{ limit }} characters.")]
    private ?string $description = null;


    #[ORM\Column(length: 255)]
   
  
    
    private ?string $image = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(
        value: "today",
        message: "Date must be greater than today's date."
    )]
    private ?\DateTimeInterface $date = null; 

    #[ORM\Column(length: 255)]
    private ?string $type = null;


    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Location must be at least {{ limit }} characters long.",
        maxMessage: "Location cannot be longer than {{ limit }} characters."
    )]
    
    private ?string $location = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'workshop', cascade: ['REMOVE'], orphanRemoval: true)]
    private Collection $Reservation;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    public function __construct()
    {
        $this->Reservation = new ArrayCollection();
    }
    
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }
  
    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservation(): Collection
    {
        return $this->Reservation;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->Reservation->contains($reservation)) {
            $this->Reservation->add($reservation);
            $reservation->setWorkshop($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->Reservation->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getWorkshop() === $this) {
                $reservation->setWorkshop(null);
            }
        }

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

}
