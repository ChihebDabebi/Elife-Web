<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ParkingRepository;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ParkingRepository::class)]

class Parking
{
   
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idParking", type: "integer")]
    private $idparking;

    #[Assert\NotBlank(message: "Le nom du parking est requis")]
    #[Assert\Length(max: 10, maxMessage: "Le nom du parking ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\Regex(
        pattern: "/^[^\d-]+$/",
        message: "Le nom du parking ne peut pas contenir de chiffres négatifs"
    )]
    #[ORM\Column(name: "nom", type: "string", length: 10)]
    private $nom;
    


    #[Assert\NotBlank(message: "La capacité du parking est requise")]
    #[Assert\Positive(message: "La capacité doit être un entier positif")]
    #[Assert\LessThanOrEqual(value: 5, message: "La capacité maximale est de 5")]
    #[ORM\Column(name: "capacite", type: "integer")]
    private $capacite;

    #[Assert\NotBlank(message: "Le type du parking est requis")]
    #[Assert\Choice(choices: ["sous-sol", "plein air", "couvert"], message: "Le type de parking doit être sous-sol, plein air ou couvert")]
    #[ORM\Column(name: "type", type: "string", length: 50)]
    private $type;

    #[ORM\Column(name: "nombreactuelles", type: "integer")]
    private $nombreactuelles;

    public function __construct()
    {
        $this->nombreactuelles = 0;
    }

    public function getIdParking(): ?int
    {
        return $this->idparking;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(?int $capacite): self
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNombreactuelles(): ?int
    {
        return $this->nombreactuelles;
    }

    public function setNombreactuelles(?int $nombreactuelles): self
    {
        $this->nombreactuelles = $nombreactuelles;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom; // Retourne le nom du parking
    }

    
}