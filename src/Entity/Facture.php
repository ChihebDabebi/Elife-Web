<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;



#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private $idfacture;
 

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "Bill Number must be non-empty")]
    private $numfacture;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "Type must be non-empty")]
    #[Assert\Choice(choices: ["Eau", "Gaz","Dechets","Electricite"], message: "Invalid Bill Type")]

    private $type;


    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\NotBlank(message: "Montant must be non-empty")]
    #[Assert\GreaterThanOrEqual(value: 0, message: "Montant must be a positive number")]
    private $montant;

    #[ORM\Column(name: "descriptionFacture", length: 255)]
    #[Assert\NotBlank(message: "Description must be non-empty")]
    private $descriptionFacture;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "Consommation must be non-empty")]
    #[Assert\GreaterThanOrEqual(value: 0, message: "Consommation must be a positive number")]
    private $consommation;



  
   
    #[ORM\ManyToOne(targetEntity: Appartement::class)]
    #[ORM\JoinColumn(name: "idAppartement", referencedColumnName: "idAppartement")]
    private $idAppartement;
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $payee;
    public function getPayee(): ?bool
    {
        return $this->payee;
    }

    public function setPayee(bool $payee): self
    {
        $this->payee = $payee;
        return $this;
    }
    public function getIdfacture(): ?int
    {
        return $this->idfacture;
    }

    public function setIdfacture(int $idfacture): self
    {
        $this->idfacture = $idfacture;
        return $this;
    }

    // Getter et setter pour numFacture
    public function getNumFacture(): ?int
    {
        return $this->numfacture;
    }

    public function setNumFacture(int $numFacture): self
    {
        $this->numfacture = $numFacture;
        return $this;
    }

  
  

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getDescriptionfacture(): ?string
    {
        return $this->descriptionFacture;
    }

    public function setDescriptionfacture(string $descriptionfacture): self
    {
        $this->descriptionFacture = $descriptionfacture;
        return $this;
    }
    public function getConsommation(): ?float
    {
        return $this->consommation;
    }

    public function setConsommation(float $consommation): self
    {
        $this->consommation = $consommation;
        return $this;
    }
    public function getIdappartement(): ?Appartement
    {
        return $this->idAppartement;
    }

    public function setIdappartement(?Appartement $idAppartement): self
    {
        $this->idAppartement = $idAppartement;
        return $this;
    }


}
