<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title is required.")]
    #[Assert\Length(
        min: 5,
        max: 100,
        minMessage: "Title must contain at least 5 characters..",
        maxMessage: "Title cannot exceed 100 characters.."
    )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Content is required")]
    #[Assert\Length(
        min: 10,
        minMessage: "Content must contain at least 10 characters.."
    )]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "Publication date is required.")]
#[Assert\Type(type: \DateTimeInterface::class, message: "Please enter a valid date.")]
#[Assert\EqualTo(
    value: "today",
    message: "Vous devez entrer la date d’aujourd’hui."
)]
    private ?\DateTimeInterface $datepub = null;

    #[ORM\Column(length: 255)]

   
    private ?string $image = null;
    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "Category is required.")]

    private string $categorie;
    
    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Author name is required.")]
    #[Assert\Length(
        min: 8,
        max: 25,
        minMessage: "Author name must contain at least 8 characters..",
        maxMessage: "Author name cannot exceed 15 characters"
    )]
    #[Assert\Regex(
        pattern: "/^[A-ZÀ-Ÿ][\p{L}\s]*$/u",
        message: "Author name must start with a capital letter and contain only letters and spaces."
    )]
    
    private string $nomAuteur;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'article')]
    private Collection $Comment;

    public function __construct()
    {
        $this->Comment = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDatepub(): ?\DateTimeInterface
    {
        return $this->datepub;
    }

    public function setDatepub(\DateTimeInterface $datepub): static
    {
        $this->datepub = $datepub;

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
    public function getCategorie(): ?string
{
    return $this->categorie;
}

public function setCategorie(string $categorie): self
{
    $this->categorie = $categorie;
    return $this;
}

public function getNomAuteur(): ?string
{
    return $this->nomAuteur;
}

public function setNomAuteur(string $nomAuteur): self
{
    $this->nomAuteur = $nomAuteur;
    return $this;
}

/**
 * @return Collection<int, Comment>
 */
public function getComment(): Collection
{
    return $this->Comment;
}

public function addComment(Comment $comment): static
{
    if (!$this->Comment->contains($comment)) {
        $this->Comment->add($comment);
        $comment->setArticle($this);
    }

    return $this;
}

public function removeComment(Comment $comment): static
{
    if ($this->Comment->removeElement($comment)) {
        // set the owning side to null (unless already changed)
        if ($comment->getArticle() === $this) {
            $comment->setArticle(null);
        }
    }

    return $this;
}
}
