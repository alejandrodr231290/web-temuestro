<?php

namespace App\Entity;

use App\Repository\ImagenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagenRepository::class)]
class Imagen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $idproducto = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $data = null;

    #[ORM\ManyToOne(inversedBy: 'imagenes')]
    private ?Unidad $unidad = null;

 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdproducto(): ?int
    {
        return $this->idproducto;
    }

    public function setIdproducto(int $idproducto): static
    {
        $this->idproducto = $idproducto;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): static
    {
        $this->data = $data;

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

   
}
