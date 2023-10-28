<?php

namespace App\Entity;

use App\Repository\UnidadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnidadRepository::class)]
class Unidad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\OneToMany(mappedBy: 'unidad', targetEntity: User::class)]
    private Collection $usuarios;

    #[ORM\OneToMany(mappedBy: 'unidad', targetEntity: Plan::class)]
    private Collection $planes;

    #[ORM\OneToMany(mappedBy: 'unidad', targetEntity: Conexion::class)]
    private Collection $conexiones;

    #[ORM\OneToMany(mappedBy: 'unidad', targetEntity: Imagen::class)]
    private Collection $imagenes;

    #[ORM\Column]
    private ?float $margencomercial = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codigo = null;




    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
        $this->planes = new ArrayCollection();
        $this->conexiones = new ArrayCollection();
        $this->imagenes = new ArrayCollection();
        $margencomercial = 12;
        $codigo = '';
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

    /**
     * @return Collection<int, User>
     */
    public function getUsuarios(): Collection
    {
        return $this->usuarios;
    }

    public function addUsuario(User $usuario): static
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios->add($usuario);
            $usuario->setUnidad($this);
        }

        return $this;
    }

    public function removeUsuario(User $usuario): static
    {
        if ($this->usuarios->removeElement($usuario)) {
            // set the owning side to null (unless already changed)
            if ($usuario->getUnidad() === $this) {
                $usuario->setUnidad(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Plan>
     */
    public function getPlanes(): Collection
    {
        return $this->planes;
    }

    public function addPlan(Plan $plan): static
    {
        if (!$this->planes->contains($plan)) {
            $this->planes->add($plan);
            $plan->setUnidad($this);
        }

        return $this;
    }

    public function removePlan(Plan $plan): static
    {
        if ($this->planes->removeElement($plan)) {
            // set the owning side to null (unless already changed)
            if ($plan->getUnidad() === $this) {
                $plan->setUnidad(null);
            }
        }

        return $this;
    }
    public function getPlaneMesServicio(int  $numero): float
    {
        $planes = $this->getPlanes();
        foreach ($planes as $p) {
            if ($p->getMes()->getNumero() == $numero) {
                return $p->getServicio();
            }
        }

        return 0;
    }
    public function getPlaneMesVenta(int  $numero): string
    {
        $planes = $this->getPlanes();
        foreach ($planes as $p) {
            if ($p->getMes()->getNumero() == $numero) {
                return $p->getVenta();
            }
        }
        return 0;
    }
    





    /**
     * @return Collection<int, Conexion>
     */
    public function getConexion(): Collection
    {
        return $this->conexiones;
    }




    public function addConexion(Conexion $conexion): static
    {
        if (!$this->conexiones->contains($conexion)) {
            $this->conexiones->add($conexion);
            $conexion->setUnidad($this);
        }

        return $this;
    }

    public function removeConexion(Conexion $conexion): static
    {
        if ($this->conexiones->removeElement($conexion)) {
            // set the owning side to null (unless already changed)
            if ($conexion->getUnidad() === $this) {
                $conexion->setUnidad(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Imagen>
     */
    public function getImagenes(): Collection
    {
        return $this->imagenes;
    }

    public function addImagene(Imagen $imagene): static
    {
        if (!$this->imagenes->contains($imagene)) {
            $this->imagenes->add($imagene);
            $imagene->setUnidad($this);
        }

        return $this;
    }

    public function removeImagene(Imagen $imagene): static
    {
        if ($this->imagenes->removeElement($imagene)) {
            // set the owning side to null (unless already changed)
            if ($imagene->getUnidad() === $this) {
                $imagene->setUnidad(null);
            }
        }

        return $this;
    }

    public function getMargencomercial(): ?float
    {
        return $this->margencomercial;
    }

    public function setMargencomercial(float $margencomercial): static
    {
        $this->margencomercial = $margencomercial;

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(?string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }


    public function getConexionID($id): null|Conexion
    {
        $cxs = $this->getConexion();
        foreach ($cxs as $cx) {
            if ($cx->getId() == $id) {

                return $cx;
            }
        }
        return null;
    }

    

    /**
     * @return Collection<int, Alamacen>
     */
    public function getAlmacenesVenta(): Collection
    {

        $almv = new ArrayCollection();
        $cxs = $this->getConexion();
        foreach ($cxs as $cx) {
            if ($cx->getTipo() == 1) { //1- Ventas
                foreach ($cx->getAlmacenes() as $al) {
                    if($al->isSeleccionado()){
                        $almv->add($al);
                    }
                    
                }
            }
        }
        return $almv;
    }


 /**
     * @return Collection<int, Alamacen>
     */
    public function getAlmacenesServicio(): Collection
    {

        $alms = new ArrayCollection();
        $cxs = $this->getConexion();
        foreach ($cxs as $cx) {
            if ($cx->getTipo() == 2) { //1- servicios
                foreach ($cx->getAlmacenes() as $al) {
                    if($al->isSeleccionado()){
                        $alms->add($al);
                    }
                    
                }
            }
        }
        return $alms;
    }

  
    public function getConexionServicio(): null|Conexion
    {
        $cxs = $this->getConexion();
        foreach ($cxs as $cx) {
            if ($cx->getTipo() == 2) { //1- Vservicio
                return $cx;
            }
        }
        return null;
    }
    public function getConexionVenta(): null|Conexion
    {
        $cxs = $this->getConexion();
        foreach ($cxs as $cx) {
            if ($cx->getTipo() == 1) { //1- Ventas
                return $cx;
            }
        }
        return null;
    }
}
