<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
     
    #[Assert\NotBlank(message: 'comment can not be emplty.')]
   
     
    private ?string $contenuComment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
   
    private ?\DateTimeInterface $datecom = null;

    
    private ?int $rating = null;

    #[ORM\ManyToOne(inversedBy: 'Comment', cascade: ['REMOVE'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'Comment')]
    private ?Article $article = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuComment(): ?string
    {
        return $this->contenuComment;
    }

    public function setContenuComment(string $contenuComment): static
    {
        $this->contenuComment = $contenuComment;

        return $this;
    }

    public function getDateCom(): ?\DateTimeInterface
    {
        return $this->datecom;
    }

    public function setDateCom(\DateTimeInterface $datecom): static
    {
        $this->datecom = $datecom;

        return $this;
    }
    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): static
    {
        $this->rating = $rating;
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

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }
}
