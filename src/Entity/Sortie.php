<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\LessThan(
     *     "Le nom est trop long",
     *     value="50")
     *
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     * @Assert\Expression("this.getDateDebut() < ",
     *      message="La date de fin doit être prévue après la date de début")
     */
    private $dateDebut;

    /**
     * @Assert\Positive()
     * @Assert\GreaterThan(
     *     message="Choisir une durée minimale de 15 minutes",
     *     value=15
     * )
     * @ORM\Column(type="integer")
     */
    private $duree;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     * @Assert\Expression("value >= this.getDateDebut()",
     *     message="La date de fin doit être prévue après la date de début")
     */
    private $dateCloture;

    /**
     * @Assert\Positive()
     * @ORM\Column(type="integer")
     */
    private $nbreInscriptionMax;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\LessThan(
     *     "Le texte de description est trop long",
     *     value=250)
     *@Assert\GreaterThan(
     *     message="Le texte de description mériterait d'être un peu plus long",
     *     value=10)
     *
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $urlImage;

    /**
     * @ORM\ManyToOne(targetEntity=Lieu::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $lieu;

    /**
     * @ORM\ManyToOne(targetEntity=Etat::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="sorties")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\OneToMany(targetEntity=CommentaireSortie::class, mappedBy="sortie", orphanRemoval=true)
     */
    private $commentaireSorties;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->commentaireSorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateCloture(): ?\DateTimeInterface
    {
        return $this->dateCloture;
    }

    public function setDateCloture(\DateTimeInterface $dateCloture): self
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    public function getNbreInscriptionMax(): ?int
    {
        return $this->nbreInscriptionMax;
    }

    public function setNbreInscriptionMax(int $nbreInscriptionMax): self
    {
        $this->nbreInscriptionMax = $nbreInscriptionMax;

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

    public function getUrlImage(): ?string
    {
        return $this->urlImage;
    }

    public function setUrlImage(?string $urlImage): self
    {
        $this->urlImage = $urlImage;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getOrganisateur(): ?User
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?User $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection|CommentaireSortie[]
     */
    public function getCommentaireSorties(): Collection
    {
        return $this->commentaireSorties;
    }

    public function addCommentaireSorty(CommentaireSortie $commentaireSorty): self
    {
        if (!$this->commentaireSorties->contains($commentaireSorty)) {
            $this->commentaireSorties[] = $commentaireSorty;
            $commentaireSorty->setSortie($this);
        }

        return $this;
    }

    public function removeCommentaireSorty(CommentaireSortie $commentaireSorty): self
    {
        if ($this->commentaireSorties->removeElement($commentaireSorty)) {
            // set the owning side to null (unless already changed)
            if ($commentaireSorty->getSortie() === $this) {
                $commentaireSorty->setSortie(null);
            }
        }

        return $this;
    }
}
