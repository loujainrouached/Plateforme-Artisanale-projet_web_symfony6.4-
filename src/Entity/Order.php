<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
   /*  #[Assert\NotBlank(message: "Delivery address cannot be empty.")] */
   #[Assert\Length(min: 5, minMessage: "Delivery address must be at least {{ limit }} characters long.")]
   
    private ?string $deliveryAdress = null;

    #[ORM\Column]
/*     #[Assert\NotBlank(message: "Phone number cannot be empty.")] */
#[Assert\Type(type: 'numeric', message: "Phone number must contain only numbers")]

 
#[Assert\Length(min: 8, minMessage: "Phone number must be at leas {{ limit }} digits long")]
 private ?int $phoneNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $DateOrder = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Cart $Cart = null;

    #[ORM\ManyToOne(inversedBy: 'Orders')]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $orderHistory = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentIntenId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeliveryAdress(): ?string
    {
        return $this->deliveryAdress;
    }

    public function setDeliveryAdress(string $deliveryAdress): static
    {
        $this->deliveryAdress = $deliveryAdress;

        return $this;
    }

    public function getPhoneNumber(): ?int
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(int $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getDateOrder(): ?\DateTimeInterface
    {
        return $this->DateOrder;
    }

    public function setDateOrder(\DateTimeInterface $DateOrder): static
    {
        $this->DateOrder = $DateOrder;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->Cart;
    }

    public function setCart(?Cart $Cart): static
    {
        $this->Cart = $Cart;

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

    public function getOrderHistory(): ?string
    {
        return $this->orderHistory;
    }

    public function setOrderHistory(?string $orderHistory): static
    {
        $this->orderHistory = $orderHistory;

        return $this;
    }

    public function getPaid(): ?string
    {
        return $this->paid;
    }

    public function setPaid(?string $paid): static
    {
        $this->paid = $paid;

        return $this;
    }

    public function getPaymentIntenId(): ?string
    {
        return $this->paymentIntenId;
    }

    public function setPaymentIntenId(?string $paymentIntenId): static
    {
        $this->paymentIntenId = $paymentIntenId;

        return $this;
    }

    
}
