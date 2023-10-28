<?php

namespace App\Controller;

use App\Repository\UnidadRepository;
use Exception;
use PDO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductoController extends AbstractController
{
    #[Route('/productos', name: 'app_productos')]
    public function index(UnidadRepository $unidadRepository): Response
    {
        $arrayAlmacenes = array();
        $almacenid = '';
        $seleccionado = false;

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DIRECTIVO')) {

            $unidades = $unidadRepository->findAll();
            $unidaduser = $this->getUser()->getUnidad()->getID();

            foreach ($unidades as $unidad) {
                $conexiones = $unidad->getConexion();
                foreach ($conexiones as $conexion) {
                    if ($conexion->getTipo() == 1) {  //tipo ventas
                        $almacenes = $conexion->getAlmacenes();
                        foreach ($almacenes as $almacen) {
                            if ($almacen->isSeleccionado()) {
                                $array = array();
                                $array['id'] = $unidad->getId() . '-' . $conexion->getId() . '-' . $almacen->getId();
                                $array['nombre'] = $unidad->getCodigo() . ' - ' . $almacen->getNombre();
                                $arrayAlmacenes[] = $array;
                                if (!$seleccionado && $unidad->getId() == $unidaduser) { //selecciono el primer almacen de la unidad a la q pertenezca el usuario
                                    $almacenid = $unidad->getId() . '-' . $conexion->getId() . '-' . $almacen->getId();
                                    $seleccionado = true;
                                }
                            }
                        }
                    }
                }
                $array = array();
                $array['id'] = $unidad->getId();
                $array['nombre'] = $unidad->getNombre();
                $arrayUnidades[] = $array;
            }
        } else {            //es ROLE_COMERCIAL o ROLE_TRABAJADOR
            $unidad = $this->getUser()->getUnidad();
            $conexiones = $unidad->getConexion();
            foreach ($conexiones as $conexion) {
                if ($conexion->getTipo() == 1) {  //tipo ventas
                    $almacenes = $conexion->getAlmacenes();
                    foreach ($almacenes as $almacen) {

                        if ($almacen->isSeleccionado()) {
                            $array = array();
                            $array['id'] = $unidad->getId() . '-' . $conexion->getId() . '-' . $almacen->getId();
                            $array['nombre'] = $unidad->getCodigo() . ' - ' . $almacen->getNombre();
                            $arrayAlmacenes[] = $array;
                            if (!$seleccionado && $unidad->getId() == $unidad->getID()) { //selecciono el primer almacen de la unidad a la q pertenezca el usuario
                                $almacenid = $unidad->getId() . '-' . $conexion->getId() . '-' . $almacen->getId();
                                $seleccionado = true;
                            }
                        }
                    }
                }
            }
        }


        return $this->render('producto/index.html.twig', [
            'almacenes' => $arrayAlmacenes,
            'amacenselected' => $almacenid
        ]);
    }

    #[Route('/productos/get', name: 'app_productos_get', methods: ['POST', 'GET'])]
    public function app_productos_get(Request $request, UnidadRepository $unidadRepository)
    {
        /* if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }*/

        $cod = $request->get('id');
        //5-1-2 idunidad  id-conexion-idalmacen

        //parameros incorrctos chequeo
        if (!$cod) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        $data = explode('-', $cod);
        if (count($data) != 3) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        $id_unidad = $data[0];
        $id_conexion = $data[1];
        $id_almacen = $data[2];
        $unidadRepository->find($id_unidad);

        $uniDB = $unidadRepository->find((int)$id_unidad);
        if (!$uniDB) {
            return $this->json(array('code' => 202, 'mensaje' => "Unidad inexistente"));
        }
        $cxDB = $uniDB->getConexionID($id_conexion);
        if (!$cxDB) {
            return $this->json(array('code' => 202, 'mensaje' => "Conexión inexistente"));
        }
        $almDB = $cxDB->getAlmacenID($id_almacen);
        if (!$almDB) {
            return $this->json(array('code' => 202, 'mensaje' => "Conexión inexistente"));
        }
        //aqui solom llega almacen d productos configurados,pero se puden otrossegun la conexion 
        //como son ventas es solo facsinv en esta version
        try {
            $PDO = $cxDB->getPDO();
            $productos = array();
            if ($cxDB->getSistema() == 2) {  //facsinv
                $SQL_PRODUCTOS = 'SELECT p.idproducto, p.codigo, p.descripcion , al.existencia, al.bloqueados, um.umedida, al.pcostomn, al.pventamn
                                  FROM almacenesdef AS a,almacenes AS al,productos AS p,umedidas AS um               
                                  WHERE al.almacen_id= a.idalmacen AND al.idproducto= p.idproducto AND um.idumedida= p.idumedida AND al.existencia > 0 
                                  AND a.idalmacen= ' .  $almDB->getIdAlmacen() . ' ORDER BY p.descripcion';
                $stmtp = $PDO->prepare($SQL_PRODUCTOS);
                $stmtp->setFetchMode(PDO::FETCH_ASSOC);
                $stmtp->execute();
                $rowsp = $stmtp->fetchAll();

                foreach ($rowsp as $pro) {
                    $precio_costo = (float)$pro['pcostomn'];
                    $precio_venta = (float)$pro['pventamn'];
                    if ($precio_venta == 0) {  //es formula 
                        $precio_venta = (float)$precio_costo + $precio_costo * $uniDB->getMargencomercial() / 100;
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
                        'valor' => (float)($pro['pcostomn'] * $pro['existencia']),
                    );
                }
                return $this->json(array('code' => 200, 'mensaje' => "Lista de Productos de Almacén " . $almDB->getNombre(), 'productos' => $productos, 'sql' => $SQL_PRODUCTOS));
            }
            return $this->json(array('code' => 200, 'mensaje' => "Lista de Productos de Almacén " . $almDB->getNombre(), 'productos' => $productos,));
        } catch (Exception $e) {
            return $this->json(array('code' => 500, 'mensaje' => "Error Interno: " . $e->getMessage(),));
        }
        //check si existe
        $planes = $uniDB->getPlanes();
        $arrayPlanes = array();
        foreach ($planes as $plan) {
            $array = array();
            $array['id'] = $plan->getId();
            $array['mes'] = $plan->getMes()->getNombre();
            $array['servicio'] = $plan->getServicio();
            $array['venta'] = $plan->getVenta();
            $array['total'] = $plan->getVenta() + $plan->getServicio();
            $arrayPlanes[] = $array;
        }
        $resp = array(
            'code' => 200,
            'mensaje' => "Datos de Unidad",
            'id' => $uniDB->getId(),
            'nombre' => $uniDB->getNombre(),
            'codigo' => $uniDB->getCodigo(),
            'margencomercial' => $uniDB->getMargencomercial(),
            'planes' => $arrayPlanes,
        );
        return $this->json($resp);

        if ($this->isGranted('ROLE_COMERCIAL')) {  //comercial solo al id
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }


        $unidades = $unidadRepository->findAll();
        $arrayUnidades = array();
        foreach ($unidades as $unidad) {
            $array = array();
            $array['id'] = $unidad->getId();
            $array['nombre'] = $unidad->getNombre();
            $array['codigo'] = $unidad->getCodigo();
            $array['margencomercial'] = $unidad->getMargencomercial();


            $arrayUnidades[] = $array;
        }
        return $this->json(array('code' => 200, 'mensaje' => "Unidades", 'unidades' => $arrayUnidades,));
    }
}
