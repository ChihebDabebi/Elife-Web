<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\VoitureRepository;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: VoitureRepository::class)]
class Voiture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idVoiture", type: "integer")]
    private $idvoiture;

    #[Assert\NotBlank(message: "La marque est obligatoire")]
    #[Assert\Length(max: 20, maxMessage: "La marque ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\Type(type: "string", message: "La marque doit être une chaîne de caractères")]
    #[Assert\Regex(pattern: "/^[a-zA-Z ]+$/", message: "La marque ne peut contenir que des lettres")]

    #[ORM\Column(name: "marque", type: "string", length: 20)]
    private $marque;

    #[Assert\NotBlank(message: "Le modèle est obligatoire")]
    #[Assert\Length(max: 20, maxMessage: "Le modèle ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\Type(type: "string", message: "Le modèle doit être une chaîne de caractères")]
    #[ORM\Column(name: "model", type: "string", length: 20)]
    #[Assert\Regex(
        pattern: "/^[\p{L}0-9]*$/u",
        message: "Le modèle doit contenir uniquement des lettres et des chiffres"
    )]
    
    private $model;

    #[Assert\NotBlank(message: "La couleur est obligatoire")]
    #[Assert\Regex(pattern: "/^[a-zA-Z]+$/", message: "La couleur ne doit contenir que des lettres")]
    #[Assert\Length(max: 20, maxMessage: "La couleur ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\Type(type: "string", message: "La couleur doit être une chaîne de caractères")]
    #[ORM\Column(name: "couleur", type: "string", length: 20)]
    private $couleur;

    #[Assert\NotBlank(message: "Le matricule est obligatoire")]
    #[Assert\Positive(message: "Le matricule doit être un entier positif")]
    #[ORM\Column(name: "matricule", type: "integer")]
    private $matricule;

    #[ORM\ManyToOne(targetEntity: Parking::class)]
    #[ORM\JoinColumn(name: "idParking", referencedColumnName: "idParking",onDelete: "CASCADE", nullable: true)]
    private $idparking;
    
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id", referencedColumnName: "id", nullable: true)]
    private $id;
    
    public function getIdvoiture(): ?int
    {
        return $this->idvoiture;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): self
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getMatricule(): ?int
    {
        return $this->matricule;
    }

    public function setMatricule(?int $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getIdparking(): ?Parking
    {
        return $this->idparking;
    }

    public function setIdparking(?Parking $idparking): self
    {
        $this->idparking = $idparking;

        return $this;
    }

    public function getId(): ?User
    {
        return $this->id;
    }

    public function setId(?User $id): self
    {
        $this->id = $id;

        return $this;
    }

 
}