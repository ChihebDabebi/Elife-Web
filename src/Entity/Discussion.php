<?php

namespace App\Entity;
use App\Repository\DiscussionRepository;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscussionRepository::class)]
class Discussion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[Assert\NotBlank(message: "le titre ne doit pas etre vide.")]
    #[Assert\Length(max: 10,maxMessage: "le titre ne peut pas depasser 10 caractere")]
    #[Assert\Regex(pattern: "/\A[a-zA-Z0-9]*\z/",message: "le titre doit etre alphanumerique .")]
    
    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column]
    private \DateTime $dateCreation;

    #[
        Assert\NotBlank(message: "la description ne doit pas etre vide .")
    ]
    
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(name: "color_code", type: "string", length: 7, nullable: true, options: ["default" => "#FFFFFF"])]
    private ?string $colorCode = '#FFFFFF';

    protected $captchaCode ;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "createur_id", referencedColumnName: "id")]
    private ?User $createur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getColorCode(): ?string
    {
        return $this->colorCode;
    }

    public function setColorCode(?string $colorCode): static
    {
        $this->colorCode = $colorCode;

        return $this;
    }
    public function getCaptchaCode(){
        return $this->captchaCode;
    }
    public function setCaptchaCode($captchaCode){
        $this->captchaCode = $captchaCode;
    }

    public function getCreateur(): ?User
    {
        return $this->createur;
    }

    public function setCreateur(?User $createur): static
    {
        $this->createur = $createur;

        return $this;
    }


}
