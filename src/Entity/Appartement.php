<?php

namespace App\Entity;
use Doctrine\Common\Collections\Collection;

use App\Repository\AppartementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: AppartementRepository::class)]
class Appartement
{
    public function __construct()
    {
        $this->factures = new ArrayCollection();
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idAppartement", type: "integer")]
    private $idappartement;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "Bill Number must be non-empty")]
    private $numappartement;
    
    
    #[ORM\Column(name: "nomResident", type: "string", length: 50)]
    #[Assert\NotBlank(message: "First name must be non-empty")]
    #[Assert\Length(max: 50, maxMessage: "First name cannot exceed {{ limit }} characters")]
    private $nomresident;
    
    #[ORM\Column(name: "taille", type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Taille must be non-empty")]
    #[Assert\Length(max: 255, maxMessage: "Taille cannot exceed {{ limit }} characters")]
    private $taille;
    
    #[ORM\Column(name: "nbrEtage", type: "integer", nullable: true)]
    #[Assert\NotBlank(message: "Floor Number must be non-empty")]
    #[Assert\PositiveOrZero(message: "Floor Number must be a positive integer or zero")]
    private $nbretage;
    
    #[ORM\Column(name: "statutAppartement", type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Appartement Status must be non-empty")]
    #[Assert\Choice(choices: ["occupied", "vacant"], message: "Invalid Appartement Status")]
    private $statutappartement;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id", referencedColumnName: "id")]
    private $id;

    #[ORM\OneToMany(targetEntity: Facture::class, mappedBy: "idappartement")]
    private $factures;

    public function getIdappartement(): ?int
    {
        return $this->idappartement;
    }

    public function getNumappartement(): ?int
    {
        return $this->numappartement;
    }

    public function setNumappartement(int $numappartement): self
    {
        $this->numappartement = $numappartement;
        return $this;
    }

    public function getNomresident(): ?string
    {
        return $this->nomresident;
    }

    public function setNomresident(string $nomresident): self
    {
        $this->nomresident = $nomresident;
        return $this;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(string $taille): self
    {
        $this->taille = $taille;
        return $this;
    }

    public function getNbretage(): ?int
    {
        return $this->nbretage;
    }

    public function setNbretage(int $nbretage): self
    {
        $this->nbretage = $nbretage;
        return $this;
    }

    public function getStatutappartement(): ?string
    {
        return $this->statutappartement;
    }

    public function setStatutappartement(string $statutappartement): self
    {
        $this->statutappartement = $statutappartement;
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

    /**
     * @return Collection|Facture[]
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures[] = $facture;
            $facture->setIdappartement($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getIdappartement() === $this) {
                $facture->setIdappartement(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->nomresident;
    }
    
}
