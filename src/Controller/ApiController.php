<?php

namespace App\Controller;

use App\Repository\MesRepository;
use App\Repository\UnidadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/almacenes', name: 'app_api_almacenes')]
    public function index(Request $request, UnidadRepository $unidadRepository, MesRepository $mesRepository)
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DIRECTIVO')) {
            $unidades = $unidadRepository->findAll();
        } else {
            //es ROLE_DIRECTIVO o ROLE_TRABAJADOR
            $unidad = $this->getUser()->getUnidad();
            $unidades = new ArrayCollection();
            $unidades->add($unidad);
        }
        $m = date("m");
        $a = date("Y");
        $d = date("d");
        $desde = $a . "-" . $m . "-01 00:00:00";
        $hasta = $a . "-" . $m . "-" . $d . " 23:59:59"; //hoy
        $errors=array();
        $arrayalmacenes = array();
        foreach ($unidades as $unidad) {
           // $errors[]= $unidad->getNombre();
            $cxDB = $unidad->getConexionVenta();
            $almacenes = $unidad->getAlmacenesVenta();
          
            try {
                $PDO = $cxDB->getPDO();
                foreach ($almacenes as $almacen) {
                   // $errors[]= $almacen->getNombre(). '  '.$cxDB->getSistemaStr().'   IDalmacen:'.$almacen->getIdAlmacen() ;
                    $productos=array();
                    if ($cxDB->getSistema() == 2) {  //facsinv
                        $SQL= 'SELECT p.idproducto, p.codigo, p.descripcion , al.existencia, al.bloqueados, um.umedida, al.pcostomn, al.pventamn
                                          FROM almacenesdef AS a,almacenes AS al,productos AS p,umedidas AS um               
                                          WHERE al.almacen_id= a.idalmacen AND al.idproducto= p.idproducto AND um.idumedida= p.idumedida AND al.existencia > 0 
                                          AND a.idalmacen= ' .  $almacen->getIdAlmacen() . ' ORDER BY p.descripcion';
                         $errors[]= $SQL;
                        $stmtp = $PDO->prepare($SQL);
                        $stmtp->setFetchMode(PDO::FETCH_ASSOC);
                        $stmtp->execute();
                        $rowsp = $stmtp->fetchAll();
                       
                        foreach ($rowsp as $pro) {
                            $precio_costo = (float)$pro['pcostomn'];
                            $precio_venta = (float)$pro['pventamn'];
                            if ($precio_venta == 0) {  //es formula 
                                $precio_venta=(float)$precio_costo +$precio_costo * $unidad->getMargencomercial()/100;
                            }
                            $productos[] = array(
                                'id' => (int)$pro['idproducto'],
                                'codigo' => $pro['codigo'],
                                'descripcion' => $pro['descripcion'],
                                'existencias' => (int)$pro['existencia'],
                                'bloqueados' => (int)$pro['bloqueados'],
                                'unidad_medida' => $pro['umedida'],
                                'precio' => (float)$precio_venta,
                                'costo' => (float)$pro['pcostomn'],
                            );
                        }
                       
                    }
                    $arrayalmacenes[] = array(
                        'id' => $almacen->getIDAlmacen(),
                        'codigo' =>$unidad->getNombre(),
                        'nombre' => $almacen->getNombre(),
                        'productos' => $productos,
                    );
                
                }
            } catch (Exception $e) {
                $errors[]= ''.$e->getMessage();
              
            }
        }

        return $this->json(array('code' => count($unidades), 'mensaje' => "Almacenes",'errores'=>$errors, 'almacenes' =>  $arrayalmacenes),);
    }
}
