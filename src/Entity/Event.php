<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idevent = null;

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le titre de l'event ne peut pas être vide.")]
    #[Assert\Length(max: 20, maxMessage: "Le titre de l'event ne peut pas dépasser {{ 20 }} caractères.")]
    #[Assert\Regex(pattern: '/^[a-zA-Z\s]+$/', message: "Le titre ne peut contenir que des lettres et des espaces.")]
    #[Assert\Regex(pattern: '/^[^\/]+$/')]
    private string $title;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message:"La date ne peut pas être vide.")]
    #[Assert\GreaterThan("today", message: "La date doit être postérieure à aujourd'hui.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(name: "nbrpersonne", type: "integer", nullable: true)]
    #[Assert\NotBlank(message: "Le nombre de personnes ne peut pas être vide.")]
    #[Assert\PositiveOrZero(message: "Le nombre de personnes doit être positif ou zéro.")]
    private ?int $nbrPersonne = null;

    #[ORM\Column(name: "listeInvites",length: 255, type: "string", nullable: true)]
    #[Assert\NotBlank(message: "La liste des invités ne peut pas être vide.")]
    #[Assert\Regex(pattern: '/^[a-zA-Z\s]+$/', message: "La liste des invités ne peut contenir que des lettres et des espaces.")]
    #[Assert\Regex(pattern: '/^[^\/]+$/', message: "La liste des invités ne peut pas contenir le caractère '/'")]
    private ?string $listeInvites = null;

    #[ORM\ManyToOne(targetEntity: Espace::class)]
    #[ORM\JoinColumn(name: "idEspace", referencedColumnName: "idEspace", onDelete: "CASCADE")]
    #[Assert\NotBlank(message: "L'espace associé ne peut pas être vide.")]
    private ?Espace $idEspace = null;

    #[ORM\ManyToOne(targetEntity: User::class)] 
    #[ORM\JoinColumn(name: "id", referencedColumnName: "id")]
    private $id; 

    // Getters and setters

    public function getId(): ?User
    {
        return $this->id;
    }

    public function setId(?User $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getIdEvent(): ?int
    {
        return $this->idevent;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setDate(?\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getNbrPersonne(): ?int
    {
        return $this->nbrPersonne;
    }

    public function setNbrPersonne(?int $nbrPersonne): void
    {
        $this->nbrPersonne = $nbrPersonne;
    }

    public function getListeInvites(): ?string
    {
        return $this->listeInvites;
    }

    public function setListeInvites(?string $listeInvites): void
    {
        $this->listeInvites = $listeInvites;
    }

    public function setEspace(?Espace $idEspace): void
    {
        $this->idEspace = $idEspace;
    }

    public function getEspace(): ?Espace
    {
        return $this->idEspace;
    }

    public function __toString(): string
    {
        return $this->title . ' - ' . ($this->date ? $this->date->format('Y-m-d') : 'Date non définie');
    }

    public function getIdEspace(): ?Espace
    {
        return $this->idEspace;
    }

    public function setIdEspace(?Espace $idEspace): static
    {
        $this->idEspace = $idEspace;

        return $this;
    }
}
