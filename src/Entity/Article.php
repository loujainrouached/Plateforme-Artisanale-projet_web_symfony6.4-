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
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 100,
        minMessage: "Le titre doit contenir au moins 5 caractères.",
        maxMessage: "Le titre ne peut pas dépasser 100 caractères."
    )]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "Le contenu doit contenir au moins 10 caractères."
    )]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de publication est obligatoire.")]
#[Assert\Type(type: \DateTimeInterface::class, message: "Veuillez entrer une date valide.")]
#[Assert\EqualTo(
    value: "today",
    message: "Vous devez entrer la date d’aujourd’hui."
)]
    private ?\DateTimeInterface $datepub = null;

    #[ORM\Column(length: 255)]

   
    private ?string $image = null;
    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]

    private string $categorie;
    
    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'auteur est obligatoire.")]
    #[Assert\Length(
        min: 8,
        max: 15,
        minMessage: "Le nom de l'auteur doit contenir au moins 8 caractères.",
        maxMessage: "Le nom de l'auteur ne peut pas dépasser 15 caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-ZÀ-Ÿ][\p{L}\s]*$/u",
        message: "Le nom de l'auteur doit commencer par une majuscule et ne contenir que des lettres et des espaces."
    )]
    
    private string $nomAuteur;
    #[ORM\Column(type: 'integer')]
private ?int $views = 0;


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
public function getViews(): ?int
{
    return $this->views;
}

public function setViews(int $views): self
{
    $this->views = $views;
    return $this;
}
public function incrementViews(): self
{
    $this->views++;
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