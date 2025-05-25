<?php

namespace App\Entity;

use App\Repository\GuideRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuideRepository::class)]
class Guide
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pays = null;

    #[ORM\Column(length: 255)]
    private ?string $photo = null;

    #[ORM\Column(length: 255)]
    private ?string $OneToMany = null;

    /**
     * @var Collection<int, visite>
     */
    #[ORM\OneToMany(targetEntity: visite::class, mappedBy: 'guide', orphanRemoval: true)]
    private Collection $visite;

    public function __construct()
    {
        $this->visite = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getOneToMany(): ?string
    {
        return $this->OneToMany;
    }

    public function setOneToMany(string $OneToMany): static
    {
        $this->OneToMany = $OneToMany;

        return $this;
    }

    /**
     * @return Collection<int, visite>
     */
    public function getVisite(): Collection
    {
        return $this->visite;
    }

    public function addVisite(visite $visite): static
    {
        if (!$this->visite->contains($visite)) {
            $this->visite->add($visite);
            $visite->setGuide($this);
        }

        return $this;
    }

    public function removeVisite(visite $visite): static
    {
        if ($this->visite->removeElement($visite)) {
            // set the owning side to null (unless already changed)
            if ($visite->getGuide() === $this) {
                $visite->setGuide(null);
            }
        }

        return $this;
    }
}
