<?php

namespace App\Entity;

use App\Repository\ConexionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use PDO;

#[ORM\Entity(repositoryClass: ConexionRepository::class)]
class Conexion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $host = null;

    #[ORM\Column(length: 255)]
    private ?string $instancia = null;

    #[ORM\Column(length: 255)]
    private ?string $db = null;

    #[ORM\Column(length: 255)]
    private ?string $usuario = null;

    #[ORM\Column(length: 255)]
    private ?string $contrasena = null;

    #[ORM\Column]
    private ?int $tipo = null;

    #[ORM\Column]
    private ?int $sistema = null;

    #[ORM\ManyToOne(inversedBy: 'conexiones')]
    private ?Unidad $unidad = null;

    private $tipos = array();
    private $sistemas = array();

    #[ORM\OneToMany(mappedBy: 'conexion', targetEntity: Almacen::class)]
    private Collection $almacenes;

    public function __construct()
    {

        $array = array();
        $array['id'] = 1;
        $array['nombre'] = 'Ventas';
        $this->tipos[] = $array;

        $array['id'] = 2;
        $array['nombre'] = 'Servicios';
        $this->tipos[] = $array;

        $array['id'] = 1;
        $array['nombre'] = 'facsi';
        $this->sistemas[] = $array;

        $array['id'] = 2;
        $array['nombre'] = 'facsinv';
        $this->sistemas[] = $array;

        $this->host = '';
        $this->instancia = '';
        $this->db = '';
        $this->usuario = '';
        $this->contrasena = '';
        $this->tipo = 0;  //cambiar al iniciar a 1 o 2
        $this->sistema = 1;
        $this->almacenes = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function getInstancia(): ?string
    {
        return $this->instancia;
    }

    public function setInstancia(string $instancia): static
    {
        $this->instancia = $instancia;

        return $this;
    }

    public function getDb(): ?string
    {
        return $this->db;
    }

    public function setDb(string $db): static
    {
        $this->db = $db;

        return $this;
    }

    public function getUsuario(): ?string
    {
        return $this->usuario;
    }

    public function setUsuario(string $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getContrasena(): ?string
    {
        return $this->contrasena;
    }

    public function setContrasena(string $contrasena): static
    {
        $this->contrasena = $contrasena;

        return $this;
    }
    public function getTipo(): ?int
    {
        return $this->tipo;
    }

    public function setTipo(int $tipo): static
    {
        $this->tipo = $tipo;

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

    public function getSistema(): ?int
    {
        return $this->sistema;
    }

    public function setSistema(int $sistema): static
    {
        $this->sistema = $sistema;

        return $this;
    }

    public function getTipos(): array
    {
        return $this->tipos;
    }
    public function getTipoStr(): String
    {
        if ($this->tipo == 1) {
            return 'Ventas';
        }
        if ($this->tipo == 2) {
            return 'Servicios';
        }
        return 'No definido';
    }
    public function getSistemas(): array
    {
        return $this->sistemas;
    }

    public function getSistemaStr(): String
    {
        if ($this->sistema == 1) {
            return 'facsi';
        }
        if ($this->sistema == 2) {
            return 'facsinv';
        }
        return 'No definido';
    }

    public function CadenaPDO(): String
    {
        $cadena = "sqlsrv:server=" . $this->host;
        //   HOST="172.16.6.122\SQLEXPRESS"
        if ($this->instancia) {
            $cadena = $cadena . "\\" . $this->instancia;
        }
        return $cadena = $cadena . ";database=" . $this->db . ";TrustServerCertificate=true;";
    }
    public function getPDO(): PDO
    {
        $pdo = new PDO($this->CadenaPDO(), $this->usuario, $this->contrasena );
        // $pdo->setAttribute(PDO::ATTR_TIMEOUT,3);
       // $pdo->setAttribute(PDO::SQLSRV_ATTR_QUERY_TIMEOUT,5);
       // $pdo->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY ,5);
         
        return $pdo;
    }
    public function check(): String
    {
        $cadena = "";
        $pdo = $this->getPDO();

        $stmt = $pdo->prepare("SELECT GETDATE() AS fecha");
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->setAttribute(PDO::SQLSRV_ATTR_QUERY_TIMEOUT,5);
       // $stmt->setAttribute(PDO::SQLSRV_ATTR_QUERY_TIMEOUT,5);
       // $stmt->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY ,5);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            return  "Fecha: " . $row['fecha'];
        }
        return $cadena;
    }
    public function cargarAlmacenes(): array
    {

        //if es sistema 1 o sistema 2 cambia para verst o rodas
        $almacenes = array();
        $pdo = $this->getPDO();
        $SQL_ALMACENES_ACTIVOS = "SELECT idalmacen, nombre FROM [facsinv].[dbo].[almacenesdef] WHERE activo = 1 ORDER BY idalmacen";
        $stmt = $pdo->prepare($SQL_ALMACENES_ACTIVOS);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $almacenes[] = array(
                'id' => $row['idalmacen'],
                'nombre' => $row['nombre'],
            );
        }
        return  $almacenes;
    }

    /**
     * @return Collection<int, Almacen>
     */
    public function getAlmacenes(): Collection
    {
        return $this->almacenes;
    }

    public function addAlmacen(Almacen $almacen): static
    {
        if (!$this->almacenes->contains($almacen)) {
            $this->almacenes->add($almacen);
            $almacen->setConexion($this);
        }

        return $this;
    }

    public function removeAlmacen(Almacen $almacen): static
    {
        if ($this->almacenes->removeElement($almacen)) {
            // set the owning side to null (unless already changed)
            if ($almacen->getConexion() === $this) {
                $almacen->setConexion(null);
            }
        }

        return $this;
    }
    public function clearAlmacenes(): static
    {
        $alma = $this->getAlmacenes();
        foreach ($alma as $a) {
            $this->removeAlmacen($a);
        }
        return $this;
    }


    public function getAlmacenID($id): null|Almacen
    {
        $alms = $this->getAlmacenes();
        foreach ($alms as $a) {
            if ($a->getId() == $id) {

                return $a;
            }
        }
        return null;
    }

   
}
