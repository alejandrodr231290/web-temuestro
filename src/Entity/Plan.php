<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $venta = null;

    #[ORM\Column]
    private ?float $servicio = null;

    #[ORM\ManyToOne(inversedBy: 'planes')]
    private ?Unidad $unidad = null;

    #[ORM\ManyToOne(inversedBy: 'planes')]
    private ?Mes $mes = null;

    


    public function getId(): ?int
    {
        return $this->id;
    }

 
    public function getVenta(): ?float
    {
        return $this->venta;
    }

    public function setVenta(float $venta): static
    {
        $this->venta = $venta;

        return $this;
    }

    public function getServicio(): ?float
    {
        return $this->servicio;
    }

    public function setServicio(float $servicio): static
    {
        $this->servicio = $servicio;

        return $this;
    }
    public function getUnidad(): ?Unidad
    {
        return $this->unidad;
    }

    public function setUnidad(?Unidad $unidad): static
    {
        $this->unidad = $unidad;

        return $this;
    }

    public function getMes(): ?Mes
    {
        return $this->mes;
    }

    public function setMes(?Mes $mes): static
    {
        $this->mes = $mes;

        return $this;
    }


      
}
