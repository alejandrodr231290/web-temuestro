<?php

namespace App\Entity;

use App\Repository\AlmacenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlmacenRepository::class)]
class Almacen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_almacen = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\ManyToOne(inversedBy: 'almacenes')]
    private ?Conexion $conexion = null;

    #[ORM\Column]
    private ?bool $seleccionado = null;

 
    public function __construct()
    {
    
        $seleccionado = false;
     
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdAlmacen(): ?int
    {
        return $this->id_almacen;
    }

    public function setIdAlmacen(int $id_almacen): static
    {
        $this->id_almacen = $id_almacen;

        return $this;
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

    public function getConexion(): ?Conexion
    {
        return $this->conexion;
    }

    public function setConexion(?Conexion $conexion): static
    {
        $this->conexion = $conexion;

        return $this;
    }

    public function isSeleccionado(): ?bool
    {
        return $this->seleccionado;
    }

    public function setSeleccionado(bool $seleccionado): static
    {
        $this->seleccionado = $seleccionado;

        return $this;
    }

  
}
