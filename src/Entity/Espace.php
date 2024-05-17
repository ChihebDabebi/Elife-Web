<?php

namespace App\Entity;

use App\Repository\EspaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EspaceRepository::class)]
class Espace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idEspace", type: "integer")]
    private $idEspace;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le nom de l'espace ne peut pas être vide.")]
    #[Assert\Length(max: 20, maxMessage: "Le nom de l'espace de l'event ne peut pas dépasser {{ 20 }} caractères.")]
    #[Assert\Regex(pattern: '/^[a-zA-Z\s]+$/', message: "Le nom de l'espace ne peut contenir que des lettres et des espaces.")]
    #[Assert\Regex(pattern: '/^[^\/]+$/')]
    private $name;

    #[ORM\Column(name: "etat", type: "string", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "L'etat ne peut pas être vide.")]
    #[Assert\Length(max: 10, maxMessage: "L'état de l'espace ne peut pas dépasser {{ 10 }} caractères.")]
    #[Assert\Regex(pattern: '/^[a-zA-Z\s]+$/', message: "L'état  de l'espace ne peut contenir que des lettres et des espaces.")]
    #[Assert\Regex(pattern: '/^[^\/]+$/')]
    private $etat;

    #[ORM\Column(name: "capacite", type: "integer", nullable: true)]
    #[Assert\PositiveOrZero(message: "La capacité de l'espace doit être un nombre positif ou zéro.")]
    #[Assert\NotBlank(message: "La capacité ne peut pas être vide.")]
    private $capacite;

    #[ORM\Column(length: 500)]
    #[Assert\Length(max: 500, maxMessage: "La description de l'espace ne peut pas dépasser {{ 500 }} caractères.")]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Assert\Regex(pattern: '/^[a-zA-Z\s]+$/', message: "La description de l'espace ne peut contenir que des lettres et des espaces.")]
    #[Assert\Regex(pattern: '/^[^\/]+$/')]
    private $description;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: "idEspace", cascade: ["remove"])]
    private $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getIdEspace(): ?int
    {
        return $this->idEspace;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }
    public function __toString(): string
{
    return $this->getName() ?? ''; // Vous pouvez modifier cela selon votre logique d'affichage
}

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setIdEspace($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getIdEspace() === $this) {
                $event->setIdEspace(null);
            }
        }

        return $this;
    }

}
