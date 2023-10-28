<?php

namespace App\Controller;

use App\Repository\UnidadRepository;
use Exception;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServicioController extends AbstractController
{
    #[Route('/servicios', name: 'app_servicios')]
    public function index(UnidadRepository $unidadRepository): Response
    {
        $arrayUnidades = array();
        $unidadactual = $this->getUser()->getUnidad();
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DIRECTIVO')) {
            $unidades = $unidadRepository->findAll();
            foreach ($unidades as $unidad) {
                $array = array();
                $array['id'] = $unidad->getId();
                $array['nombre'] = $unidad->getNombre();

                if ($unidad->getConexionServicio()!=null) {
                    $arrayUnidades[] = $array;
                }
            }
        } else {
            //es ROLE_DIRECTIVO o ROLE_TRABAJADOR
            $array = array();
            $array['id'] = $unidadactual->getId();
            $array['nombre'] = $unidadactual->getNombre();
            if ($unidadactual->getConexionServicio()!=null) {
                $arrayUnidades[] = $array;
            }
        }
        if (count($arrayUnidades())) {
            //mustro rnder q no hay
            return $this->render('acceso/index.html.twig', ['message' => 'No existen unidades con Servicios']);
        }

        return $this->render('servicio/index.html.twig', [
            'unidades' => $arrayUnidades,
            'unidadactual' => $unidadactual->getId()
        ]);
    }

    #[Route('/servicios/get', name: 'app_servicios_get', methods: ['POST', 'GET'])]
    public function app_servicios_get(Request $request, UnidadRepository $unidadRepository)
    {

        $id = $request->get('id');
        $desde = $request->get('desde'); //'yyyy-mm-dd'
        $hasta = $request->get('hasta'); // 'yyyy-mm-dd'

        if (!$desde || !$hasta) {  //algguno vacio pongomes actual

            $m = date("m");
            // Año actual con 4 dígitos
            $a = date("Y");
            // Día del mes con 2 dígitos, y con ceros iniciales, de 01 a 31
            $d = date("d");
            $desde = $a . "-" . $m . "-01 00:00:00";
            $hasta = $a . "-" . $m . "-" . $d . " 23:59:59"; //hoy
        }
        // 20/04/2023

        //parameros incorrctos chequeo
        if (!$id) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }


        $uniDB = $unidadRepository->find((int)$id);
        if (!$uniDB) {
            return $this->json(array('code' => 202, 'mensaje' => "Unidad inexistente"));
        }

        $cxDB = $uniDB->getConexionServicio();

        //  return $this->json(array('code' => 200, 'mensaje' => "Lista de Servicios (".  $cxDB->getSistema().") de " . $desde . " a " . $hasta, 'servicios' => $ventas,));
        try {

            if ($cxDB->getSistema() == 1) {  //facsi 

                $PDO = $cxDB->getPDO();
                $ventas = array();
                // return $this->json(array('code' => 200, 'mensaje' => "Lista de Servicios (facsi) de " . $desde . " a " . $hasta, 'servicios' => $ventas,));
                $SQL = "SELECT idfactura, [total mn], codigo, a.fecha,[total pagado mn], estado FROM factura a, clientes c WHERE a.idcliente= c.id_Cliente   
                AND ( estado = 0 OR estado = 2 OR estado = 1)  
                AND a.fecha>= '" . $desde . "'   
                AND a.fecha<= '" . $hasta . "' 
                ORDER BY a.fecha DESC";
                $stmt = $PDO->prepare($SQL);
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                foreach ($rows as $rowv) {


                    $estado = '<label class="text-secondary">INDEFINIDO</label>';
                    if ($rowv['estado'] == 2) {  //estado pagado
                        $estado = '<label class="text-success">COBRADO</label>';
                    }
                    if ($rowv['estado'] == 0) {  //estado pendiente
                        $estado = '<label class="text-danger">PENDIENTE</label>';
                    }
                    if ($rowv['estado'] == 1) {  //estado pendiente
                        $estado = '<label class="text-warning">COBRO PARCIAL</label>';
                    }

                    $ventas[] = array(
                        'idfactura' => $rowv['idfactura'],
                        'codigo' => $rowv['codigo'],
                        'fecha' =>  substr($rowv['fecha'], 0, 10),  //2023-05-11 00:00:00.000 a 2023-05-11 
                        'estado' =>  $estado,
                        'total' => (float)$rowv['total mn'],
                        'porcobrar' => (float) ($rowv['total mn'] - $rowv['total pagado mn']),
                    );
                }
                return $this->json(array('code' => 200, 'mensaje' => "Lista de Servicios (facsi) de " . $desde . " a " . $hasta, 'servicios' => $ventas,));
            }

            if ($cxDB->getSistema() == 2) {  //facsinv
                $PDO = $cxDB->getPDO();
                $almacenes = $uniDB->getAlmacenesServicio();
                $ventas = array();
                foreach ($almacenes as $alm) {
                    $almacen_id = $alm->getIdAlmacen();

                    $SQL = "SELECT 	f.idfactura, a.Nombre, f.[total mn], f.[total pagado mn], 	c.codigo, 	f.fecha, 	f.estado FROM	factura f,	clientes c,	almacenesdef a WHERE	f.idcliente= c.id_Cliente 	AND a.codigo= f.[codigo cc] 
                    AND ( estado = 0 OR estado = 2 OR estado = 1)  
                    AND f.fecha>='" . $desde . "' 
                    AND f.fecha<='" . $hasta . "'
                    AND a.idalmacen=" . $almacen_id . "  
                    ORDER BY f.fecha DESC";
                    // return $this->json(array('code' => 200, 'mensaje' => "Lista de Ventas de " . $desde . " a " . $hasta, 'ventas' => $SQL,));


                    $stmt = $PDO->prepare($SQL);
                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                    $stmt->execute();
                    $rows = $stmt->fetchAll();

                    foreach ($rows as $rowv) {

                        $estado = '<label class="text-secondary">INDEFINIDO</label>';
                        if ($rowv['estado'] == 2) {  //estado pagado
                            $estado = '<label class="text-success">COBRADO</label>';
                        }
                        if ($rowv['estado'] == 0) {  //estado pendiente
                            $estado = '<label class="text-danger">PENDIENTE</label>';
                        }
                        if ($rowv['estado'] == 1) {  //estado pendiente
                            $estado = '<label class="text-warning">COBRO PARCIAL</label>';
                        }


                        $ventas[] = array(
                            'almacen' => $alm->getNombre(),
                            'idfactura' => $rowv['idfactura'],
                            'codigo' => $rowv['codigo'],
                            'fecha' =>  substr($rowv['fecha'], 0, 10),  //2023-05-11 00:00:00.000 a 2023-05-11 
                            'estado' =>  $estado,
                            'total' => (float)$rowv['total mn'],
                            'porcobrar' => (float) ($rowv['total mn'] - $rowv['total pagado mn']),

                        );
                    }
                }
                return $this->json(array('code' => 200, 'mensaje' => "Lista de Servicios de " . $desde . " a " . $hasta, 'servicios' => $ventas,));
            }

            return $this->json(array('code' => 200, 'mensaje' => "Lista de Servicios (OTRO) de " . $desde . " a " . $hasta, 'servicios' => array(),));
        } catch (Exception $e) {
            return $this->json(array('code' => 500, 'mensaje' => "Error Interno: " . $e->getMessage(), 'servicios' => array(),), 500);
        }
    }
}
