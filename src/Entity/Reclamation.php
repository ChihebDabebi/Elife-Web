<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $idrec = null;

    #[ORM\Column(type: "text", length: 65535, nullable: true, options: ["charset" => "utf8mb4", "collation" => "utf8mb4_unicode_ci"])]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Length(max: 200, maxMessage: "La description ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $descrirec = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $daterec = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $categorierec = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $statutrec = 'en cours'; // Valeur par défaut : 'en cours'

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $imagedata = null;
    
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "idU", referencedColumnName: "id")]
    private $idu;

    // Constructeur pour s'assurer que statutrec est toujours initialisé
    public function __construct()
    {
        $this->statutrec = 'en cours'; // Valeur par défaut : 'en cours'
    }

    // Getters et setters
    public function getIdrec(): ?int
    {
        return $this->idrec;
    }

    public function setIdrec(?int $idrec): self
    {
        $this->idrec = $idrec;
        return $this;
    }

    public function getDescrirec(): ?string
    {
        return $this->descrirec;
    }

    public function setDescrirec(?string $descrirec): self
    {
        $this->descrirec = $descrirec;
        return $this;
    }

    public function getDaterec(): ?\DateTimeInterface
    {
        return $this->daterec;
    }

    public function setDaterec(?\DateTimeInterface $daterec): self
    {
        $this->daterec = $daterec;
        return $this;
    }

    public function getCategorierec(): ?string
    {
        return $this->categorierec;
    }

    public function setCategorierec(?string $categorierec): self
    {
        $this->categorierec = $categorierec;
        return $this;
    }

    public function getStatutrec(): ?string
    {
        return $this->statutrec;
    }

    public function setStatutrec(?string $statutrec): self
    {
        $this->statutrec = $statutrec;
        return $this;
    }

    public function getImagedata(): ?string
    {
        return $this->imagedata;
    }

    public function setImagedata(?string $imagedata): self
    {
        $this->imagedata = $imagedata;
        return $this;
    }

    public function getIdu(): ?User
    {
        return $this->idu;
    }

    public function setIdu(?User $idu): self
    {
        $this->idu = $idu;
        return $this;
    }
}