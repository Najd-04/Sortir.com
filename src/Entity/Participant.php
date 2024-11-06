<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 180)]
  private ?string $email = null;

  /**
   * @var list<string> The user roles
   */
  #[ORM\Column]
  private array $roles = [];

  /**
   * @var string The hashed password
   */
  #[ORM\Column]
  private ?string $password = null;

  #[ORM\Column(length: 30)]
  private ?string $nom = null;

  #[ORM\Column(length: 30)]
  private ?string $prenom = null;

  #[ORM\Column(length: 30)]
  private ?string $pseudo = null;

  #[ORM\Column]
  private ?bool $actif = null;

  #[ORM\Column(length: 10, nullable: true)]
  private ?string $telephone = null;


  /**
   * @var Collection<int, Sortie>
   */
  #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'organisateur', orphanRemoval: true)]
  private Collection $sortiesOrganisees;

  /**
   * @var Collection<int, Inscription>
   */
  #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'participant', orphanRemoval: true)]
  private Collection $inscriptions;

  #[ORM\ManyToOne(inversedBy: 'participants')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Site $site = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $photoProfil = null;

  public function __construct()
  {
    $this->sortiesOrganisees = new ArrayCollection();
    $this->inscriptions = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(string $email): static
  {
    $this->email = $email;

    return $this;
  }

  /**
   * A visual identifier that represents this user.
   *
   * @see UserInterface
   */
  public function getUserIdentifier(): string
  {
    return (string)$this->email;
  }

  /**
   * @return list<string>
   * @see UserInterface
   *
   */
  public function getRoles(): array
  {
    $roles = $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }

  /**
   * @param list<string> $roles
   */
  public function setRoles(array $roles): static
  {
    $this->roles = $roles;

    return $this;
  }

  /**
   * @see PasswordAuthenticatedUserInterface
   */
  public function getPassword(): ?string
  {
    return $this->password;
  }

  public function setPassword(string $password): static
  {
    $this->password = $password;

    return $this;
  }

  /**
   * @see UserInterface
   */
  public function eraseCredentials(): void
  {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
  }

  public function getNom(): ?string
  {
    return $this->nom;
  }

  public function setNom(string $nom): static
  {
    $this->nom = $nom;

    return $this;
  }

  public function getPrenom(): ?string
  {
    return $this->prenom;
  }

  public function setPrenom(string $prenom): static
  {
    $this->prenom = $prenom;

    return $this;
  }

  public function getPseudo(): ?string
  {
    return $this->pseudo;
  }

  public function setPseudo(string $pseudo): static
  {
    $this->pseudo = $pseudo;

    return $this;
  }


  public function getActif(): ?bool
  {
    return $this->actif;
  }

  public function setActif(bool $actif): static
  {
    $this->actif = $actif;

    return $this;
  }

  public function getTelephone(): ?string
  {
    return $this->telephone;
  }

  public function setTelephone(?string $telephone): static
  {
    $this->telephone = $telephone;

    return $this;
  }

  /**
   * @return Collection<int, Sortie>
   */
  public function getSortiesOrganisees(): Collection
  {
    return $this->sortiesOrganisees;
  }

  public function addSortiesOrganisee(Sortie $sortiesOrganisee): static
  {
    if (!$this->sortiesOrganisees->contains($sortiesOrganisee)) {
      $this->sortiesOrganisees->add($sortiesOrganisee);
      $sortiesOrganisee->setOrganisateur($this);
    }

    return $this;
  }

  public function removeSortiesOrganisee(Sortie $sortiesOrganisee): static
  {
    if ($this->sortiesOrganisees->removeElement($sortiesOrganisee)) {
      // set the owning side to null (unless already changed)
      if ($sortiesOrganisee->getOrganisateur() === $this) {
        $sortiesOrganisee->setOrganisateur(null);
      }
    }

    return $this;
  }

  /**
   * @return Collection<int, Inscription>
   */
  public function getInscriptions(): Collection
  {
      return $this->inscriptions;
  }

  public function addInscription(Inscription $inscription): static
  {
      if (!$this->inscriptions->contains($inscription)) {
          $this->inscriptions->add($inscription);
          $inscription->setParticipant($this);
      }

      return $this;
  }

  public function removeInscription(Inscription $inscription): static
  {
      if ($this->inscriptions->removeElement($inscription)) {
          // set the owning side to null (unless already changed)
          if ($inscription->getParticipant() === $this) {
              $inscription->setParticipant(null);
          }
      }

      return $this;
  }

  public function getSite(): ?Site
  {
      return $this->site;
  }

  public function setSite(?Site $site): static
  {
      $this->site = $site;

      return $this;
  }

  public function getPhotoProfil(): ?string
  {
      return $this->photoProfil;
  }

  public function setPhotoProfil(?string $photoProfil): static
  {
      $this->photoProfil = $photoProfil;

      return $this;
  }

}
