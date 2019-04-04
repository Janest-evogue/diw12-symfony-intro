<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * la classe est liée à une table en bdd
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * clé primaire
     * @ORM\Id()
     * auto-increment
     * @ORM\GeneratedValue()
     * de type integer
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * varchar(50) NOT NULL en bdd
     * @ORM\Column(type="string", length=50)
     */
    private $lastname;

    /**
     *
     * @ORM\Column(type="string", length=50)
     */
    private $firstname;

    /**
     * varchar(255) NOT NULL unique en bdd
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var \DateTime
     *
     * date nullable en bdd
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @var Collection
     *
     * Le OneToMany (falcutatif) permet d'accéder à ses publications
     * depuis un objet User dans cet attribut
     * mappedBy dit quel attribut dans Publication définit la clé étrangère
     * avec ManyToOne
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Publication", mappedBy="author")
     */
    private $publications;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Team", mappedBy="users")
     */
    private $teams;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
        $this->teams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Méthode magique appelée automatiquement quand on utilise un objet User
     * comme une chaîne de caractère (ex : faire un echo dessus)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @return Collection|Publication[]
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): self
    {
        if (!$this->publications->contains($publication)) {
            $this->publications[] = $publication;
            $publication->setAuthor($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): self
    {
        if ($this->publications->contains($publication)) {
            $this->publications->removeElement($publication);
            // set the owning side to null (unless already changed)
            if ($publication->getAuthor() === $this) {
                $publication->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->addUser($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
            $team->removeUser($this);
        }

        return $this;
    }
}
