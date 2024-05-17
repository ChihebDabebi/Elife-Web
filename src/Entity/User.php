<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpdaterInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private $id;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 180)]
    #[Assert\Regex("/^[a-zA-Z\s]*$/")] // The nom and prenom fields should not contain symbols or numbers
    private $nom;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 180)]
    #[Assert\Regex("/^[a-zA-Z\s]*$/")] // The nom and prenom fields should not contain symbols or numbers
    private $prenom;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 8)]
    #[Assert\Regex("/^(9|5|2)\d{7}$/")] // The number field should begin with 9, 5, or 2 and have exactly 8 digits
    private $number;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email] // Add validation for email format
    private $mail;

    #[ORM\Column(length: 255)] // Increase length for hashed password
    private $password;

    #[ORM\Column(length: 180)]
    private $role;


    /**
     * @var string|null
     */
    private $plainPassword;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->mail; // Use email as user identifier (replace if needed)
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
{
    $roles = [$this->role];

    if (str_contains($roles[0], 'ADMIN')) {
        $roles[] = 'ROLE_USER';
    } else {
        $roles[] = 'ROLE_USER';
    }

    return array_unique($roles);
}
    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getSalt(): ?string
    {
        return null; // Not needed for modern password hashing in Symfony
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->mail;
    }
       // Méthode magique __toString
   public function __toString(): string
   {
       return $this->nom . ' ' . $this->prenom;
   }

}