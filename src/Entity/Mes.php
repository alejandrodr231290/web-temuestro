<?php

namespace App\Entity;

use App\Repository\MesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MesRepository::class)]
class Mes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\OneToMany(mappedBy: 'mes', targetEntity: Plan::class)]
    private Collection $planes;

    public function __construct()
    {
        $this->planes = new ArrayCollection();
    }
  
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return Collection<int, Plan>
     */
    public function getPlanes(): Collection
    {
        return $this->planes;
    }

    public function addPlane(Plan $plane): static
    {
        if (!$this->planes->contains($plane)) {
            $this->planes->add($plane);
            $plane->setMes($this);
        }

        return $this;
    }

    public function removePlane(Plan $plane): static
    {
        if ($this->planes->removeElement($plane)) {
            // set the owning side to null (unless already changed)
            if ($plane->getMes() === $this) {
                $plane->setMes(null);
            }
        }

        return $this;
    }

      
}
