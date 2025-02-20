<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The product name cannot be blank.")]
    #[Assert\Length(max: 255, maxMessage: "The name cannot be longer than 255 characters.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Please provide a description.")]
    #[Assert\Length(max: 1000, maxMessage: "The description cannot be longer than {{ limit }} characters.")]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Please enter a price.")]
    #[Assert\GreaterThanOrEqual(0, message: "The price must be a positive number.")]
    private ?float $price = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Please enter a category.")]
    #[Assert\Type(
        type: "string",
        message: "The category must be a string, not a number."
    )]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "The category name must be at least {{ limit }} characters long.",
        maxMessage: "The category name cannot be longer than {{ limit }} characters."
    )]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
   
#[Assert\Length(
    max: 255,
    maxMessage: "The image path cannot exceed 255 characters."
)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'Products')]
    private ?User $user = null;

    /**
     * @var Collection<int, Cart>
     */
    #[ORM\ManyToMany(targetEntity: Cart::class, mappedBy: 'products')]
    private Collection $carts;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    

    public function __construct()
    {
        $this->carts = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): static
    {
        if (!$this->carts->contains($cart)) {
            $this->carts->add($cart);
            $cart->addProduct($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): static
    {
        if ($this->carts->removeElement($cart)) {
            $cart->removeProduct($this);
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }
}