<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 30)]
  private ?string $nom = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  private ?\DateTimeInterface $dateHeureDebut = null;

  #[ORM\Column]
  private ?int $duree = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  private ?\DateTimeInterface $dateLimiteInscription = null;

  #[ORM\Column]
  private ?int $nbInscriptionsMax = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $infosSortie = null;

  #[ORM\ManyToOne(inversedBy: 'sorties')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Etat $etat = null;

  #[ORM\ManyToOne(inversedBy: 'sorties')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Lieu $lieu = null;

  #[ORM\ManyToOne(inversedBy: 'sorties')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Site $site = null;


  #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Participant $organisateur = null;

  /**
   * @var Collection<int, Inscription>
   */
  #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'sortie', orphanRemoval: true)]
  private Collection $inscriptions;

  public function __construct()
  {
      $this->inscriptions = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
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

  public function getDateHeureDebut(): ?\DateTimeInterface
  {
    return $this->dateHeureDebut;
  }

  public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): static
  {
    $this->dateHeureDebut = $dateHeureDebut;

    return $this;
  }

  public function getDuree(): ?int
  {
    return $this->duree;
  }

  public function setDuree(int $duree): static
  {
    $this->duree = $duree;

    return $this;
  }

  public function getDateLimiteInscription(): ?\DateTimeInterface
  {
    return $this->dateLimiteInscription;
  }

  public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): static
  {
    $this->dateLimiteInscription = $dateLimiteInscription;

    return $this;
  }

  public function getNbInscriptionsMax(): ?int
  {
    return $this->nbInscriptionsMax;
  }

  public function setNbInscriptionsMax(int $nbInscriptionsMax): static
  {
    $this->nbInscriptionsMax = $nbInscriptionsMax;

    return $this;
  }

  public function getInfosSortie(): ?string
  {
    return $this->infosSortie;
  }

  public function setInfosSortie(?string $infosSortie): static
  {
    $this->infosSortie = $infosSortie;

    return $this;
  }

  public function getEtat(): ?Etat
  {
    return $this->etat;
  }

  public function setEtat(?Etat $etat): static
  {
    $this->etat = $etat;

    return $this;
  }

  public function getLieu(): ?Lieu
  {
    return $this->lieu;
  }

  public function setLieu(?Lieu $lieu): static
  {
    $this->lieu = $lieu;

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


  public function getOrganisateur(): ?Participant
  {
    return $this->organisateur;
  }

  public function setOrganisateur(?Participant $organisateur): static
  {
    $this->organisateur = $organisateur;

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
          $inscription->setSortie($this);
      }

      return $this;
  }

  public function removeInscription(Inscription $inscription): static
  {
      if ($this->inscriptions->removeElement($inscription)) {
          // set the owning side to null (unless already changed)
          if ($inscription->getSortie() === $this) {
              $inscription->setSortie(null);
          }
      }

      return $this;
  }

}
