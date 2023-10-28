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

class RecepcionController extends AbstractController
{
    #[Route('/recepciones', name: 'app_recepciones')]
    public function index(UnidadRepository $unidadRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL') && !$this->isGranted('ROLE_DIRECTIVO')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }
        $arrayUnidades = array();
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DIRECTIVO')) {
            $unidades = $unidadRepository->findAll();
            foreach ($unidades as $unidad) {
                $array = array();
                $array['id'] = $unidad->getId();
                $array['nombre'] = $unidad->getNombre();

                $arrayUnidades[] = $array;
            }
        } else {
            //es ROLE_DIRECTIVO o ROLE_TRABAJADOR
            $unidadactual = $this->getUser()->getUnidad();
            $array = array();
            $array['id'] = $unidadactual->getId();
            $array['nombre'] = $unidadactual->getNombre();

            $arrayUnidades[] = $array;
        }

        return $this->render('recepcion/index.html.twig', [
            'unidades' => $arrayUnidades
        ]);
    }

    #[Route('/recepciones/get', name: 'app_recepciones_get', methods: ['POST', 'GET'])]
    public function app_recepciones_get(Request $request, UnidadRepository $unidadRepository)
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL') && !$this->isGranted('ROLE_DIRECTIVO')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }

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
        $cxDB = $uniDB->getConexionVenta();

        $recepciones = array();
        try {
            if ($cxDB->getSistema() == 2) {  //facsinv
                $PDO = $cxDB->getPDO();
                $SQL = "SELECT  r.codigo,	c.codigo as proveedor,	a.nombre as destino,	r.fechar as fecha,	r.estado,	r.total_mn as total, r.calc 
                        FROM	dbo.clientes AS c,	dbo.recepciones AS r ,	dbo.almacenesdef AS a 
                        WHERE  a.idalmacen=r.almacen_id 	AND r.idproveedor = c.id_Cliente 
                        AND r.fechar>= '" . $desde . "' 
                        AND r.fechar<= '" . $hasta . "'";
                // return $this->json(array('code' => 200, 'mensaje' => "Lista de Ventas de " . $desde . " a " . $hasta, 'ventas' => $SQL,));
                //$SQL = "SELECT idfactura, [total mn], codigo, a.fecha, estado FROM factura a, clientes c WHERE a.idcliente= c.id_Cliente AND ( estado = 0 OR estado = 2 ) AND a.fecha>= '" . $desde . "'   AND a.fecha<= '" . $hasta . "' AND a.almacen_id = '" . $almacen_id . "' ORDER BY a.fecha";
                $stmt = $PDO->prepare($SQL);
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $stmt->execute();
                $rows = $stmt->fetchAll();

                foreach ($rows as $rowv) {
                    $estado = $rowv['estado'];
                    $estadosrt = 'INDEFINIDO-' . $estado;
                    if ($estado == 0) {
                        $estadosrt = ' NO CONFIRMADA';
                    }
                    if ($estado == 1) {
                        $estadosrt = 'CONFIRMADA';
                    }
                    $recepciones[] = array(
                        'codigo' => $rowv['codigo'],
                        'proveedor' => $rowv['proveedor'],
                        'destino' => $rowv['destino'],
                        'fecha' =>  substr($rowv['fecha'], 0, 10),  //2023-05-11 00:00:00.000 a 2023-05-11 
                        'estado' => $estadosrt,
                        'total' => (float)$rowv['total'],
                    );
                }

                
            }
            return $this->json(array('code' => 200, 'mensaje' => "Lista de recepciones de " . $desde . " a " . $hasta, 'recepciones' => $recepciones,));
        } catch (Exception $e) {
            return $this->json(array('code' => 500, 'mensaje' => "Error Interno: " . $e->getMessage(),));
        }
    }
}

