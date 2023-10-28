<?php

namespace App\Controller;

use App\Repository\MesRepository;
use App\Repository\UnidadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Collection;

class PanelController extends AbstractController
{
    #[Route('/', name: 'app_panel')]
    public function index(UnidadRepository $unidadRepository, MesRepository $mesRepository): Response
    {

        $arrayUnidades = array();
        $meses = array();
        $mesactual =(int)date("n");
        $mesesDB = $mesRepository->findAll();
        foreach ($mesesDB as $m) {
            $array = array();
            $array['id'] = $m->getNumero();
            $array['nombre'] = $m->getNombre();
            $meses[] = $array;
        }

       
     
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DIRECTIVO')) {
            $unidades = $unidadRepository->findAll();
            foreach ($unidades as $unidad) {
                $array = array();
                $array['id'] = $unidad->getId();
                $array['nombre'] = $unidad->getNombre();
                $array['planventa'] = number_format($unidad->getPlaneMesVenta((int)date("n")), 2, '.', ',');
                $array['planservicio'] = number_format($unidad->getPlaneMesServicio((int)date("n")), 2, '.', ',');
                $arrayUnidades[] = $array;
            }
        } else {
            //es ROLE_DIRECTIVO o ROLE_TRABAJADOR
            $unidad = $this->getUser()->getUnidad();
            $array = array();
            $array['id'] = $unidad->getId();
            $array['nombre'] = $unidad->getNombre();
            $array['planventa'] = number_format($unidad->getPlaneMesVenta((int)date("n")), 2, '.', ',');
            $array['planservicio'] = number_format($unidad->getPlaneMesServicio((int)date("n")), 2, '.', ',');
            $arrayUnidades[] = $array;
        }

        return $this->render('panel/index.html.twig', [
            // 'unidades' => $arrayUnidades,
            'mesactual' => $mesactual,
            'meses' => $meses
        ]);
    }

    #[Route('/panel/getventas', name: 'app_getventas', methods: ['POST', 'GET'])]
    public function app_getventas(Request $request, UnidadRepository $unidadRepository, MesRepository $mesRepository)
    {
        $mes = $request->get('mes'); //int 1-12
        $datapanel = array();
        if (!$mes) {  //parametro mes 1-11
            return $this->json(array('code' => 300, 'mensaje' => "Parámetro Incorrecto"));
        } else if ($mes < 1 || $mes > 12) {
            return $this->json(array('code' => 300, 'mensaje' => "Parámetro Incorrecto"));
        }

        $a = date("Y");  //año actual
        $diasmes = cal_days_in_month(CAL_GREGORIAN, $mes, (int)$a);  //dias del mes 
        $d = '' . $diasmes; //28-29-30-31
        $m = '' . $mes;
        if (strlen($m) == 1) {  //formato mes a //01-12 
            $m = '0' . $m;
        }       
        $desde = $a . "-" . $m . "-01 00:00:00";
        $hasta = $a . "-" . $m . "-" . $d . " 23:59:59";

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DIRECTIVO')) {
            $unidades = $unidadRepository->findAll();
        } else {
            //es ROLE_DIRECTIVO o ROLE_TRABAJADOR
            $unidad = $this->getUser()->getUnidad();
            $unidades = new ArrayCollection();
            $unidades->add($unidad);
        }
        // $unidades = $unidadRepository->findAll();


        foreach ($unidades as $unidad) {
            $array = array();
            $venta = 0;
            $cxDB = $unidad->getConexionVenta();
            $almacenes = $unidad->getAlmacenesVenta();
            try {
                $PDO = $cxDB->getPDO();
                foreach ($almacenes as $almacen) {
                    if ($cxDB->getSistema() == 2) {  //facsinv
                        //mismo pdo para las ventas,del mismo almacen
                        $SQL = "SELECT SUM(ROUND( [total mn], 2))  as VENTA  FROM factura a, clientes c WHERE a.idcliente= c.id_Cliente   AND ( estado = 0 OR estado = 2 )   AND a.fecha>= '" . $desde . "'   AND a.fecha<= '" . $hasta . "'
                                AND a.almacen_id=" . $almacen->getIdAlmacen() . "";

                        $stmt = $PDO->prepare($SQL);
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        $stmt->execute();
                        $rows = $stmt->fetchAll();
                        foreach ($rows as $rowv) {
                            $venta = $venta + $rowv['VENTA'];
                        }
                    }
                }

                $plan = (float)$unidad->getPlaneMesVenta((int)date("n"));
                $array['unidad'] = $unidad->getNombre();
                $array['plan'] = $plan;
                $array['real'] = $venta;
                $array['porciento'] = 0;
                if ($plan > 0) {
                    $array['porciento'] =  $venta * 100 / $plan;
                }
            } catch (Exception $e) {
                $venta = '<strong class="text-danger">Error</strong>';
                $plan = $unidad->getPlaneMesVenta((int)date("n"));

                $array['unidad'] = $unidad->getNombre();
                $array['plan'] = number_format($plan, 2, '.', ',');
                $array['real'] = '<strong class="text-danger">Error</strong>';
                $array['porciento'] = 0;
            }
            $datapanel[] = $array;
        }

        return $this->json(array('code' => 200, 'mensaje' => "Datos de Ventas mes ".$mes, 'ventas' =>  $datapanel));
    }

    #[Route('/panel/getservicios', name: 'app_getservicios', methods: ['POST', 'GET'])]
    public function app_getservicios(Request $request, UnidadRepository $unidadRepository, MesRepository $mesRepository)
    {
        $mes = $request->get('mes'); //int 1-12
        $datapanel = array();
        if (!$mes) {  //parametro mes 1-11
            return $this->json(array('code' => 300, 'mensaje' => "Parámetro Incorrecto"));
        } else if ($mes < 1 || $mes > 12) {
            return $this->json(array('code' => 300, 'mensaje' => "Parámetro Incorrecto"));
        }

        $a = date("Y");  //año actual
        $diasmes = cal_days_in_month(CAL_GREGORIAN, $mes, (int)$a);  //dias del mes 
        $d = '' . $diasmes; //28-29-30-31
        $m = '' . $mes;
        if (strlen($m) == 1) {  //formato mes a //01-12 
            $m = '0' . $m;
        }       
        $desde = $a . "-" . $m . "-01 00:00:00";
        $hasta = $a . "-" . $m . "-" . $d . " 23:59:59";

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DIRECTIVO')) {
            $unidades = $unidadRepository->findAll();
        } else {
            //es ROLE_COMERCIAL o ROLE_TRABAJADOR
            $unidad = $this->getUser()->getUnidad();
            $unidades = new ArrayCollection();
            $unidades->add($unidad);
        }
       

        foreach ($unidades as $unidad) {
            $array = array();
            $venta = 0;
            $servicio = 0;
            $cxDB = $unidad->getConexionServicio();
            try {
                if ($cxDB->getSistema() == 1) {  //facsi 
                    $PDO = $cxDB->getPDO();

                    // $SQL = "SELECT SUM(ROUND( [total mn], 2))  as SERVICIO  FROM factura AS a WHERE estado = 2 AND  a.fecha>= '" . $desde . "' AND a.fecha<= '" . $hasta . "'";
                    $SQL = "SELECT SUM(ROUND( [total mn], 2))  as SERVICIO FROM factura a, clientes c WHERE a.idcliente= c.id_Cliente AND ( estado = 0 OR estado = 2 ) AND a.fecha>= '" . $desde . "'   AND a.fecha<= '" . $hasta . "'";


                    $stmt = $PDO->prepare($SQL);
                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                    $stmt->execute();
                    $rows = $stmt->fetchAll();
                    foreach ($rows as $rowv) {
                        $servicio = $servicio + $rowv['SERVICIO'];
                    }
                }
                if ($cxDB->getSistema() == 2) {  //facsinv
                    $PDO = $cxDB->getPDO();
                    $almacenes = $unidad->getAlmacenesServicio();

                    foreach ($almacenes as $alm) {
                        $almacen_id = $alm->getIdAlmacen();

                        $SQL = "SELECT SUM(ROUND( [total mn], 2))  as SERVICIO  FROM factura a, clientes c WHERE a.idcliente= c.id_Cliente 
                                  AND ( estado = 0 OR estado = 2 ) 
                                  AND a.fecha>= '" . $desde . "' 
                                  AND a.fecha<= '" . $hasta . "'
                                  AND a.almacen_id=" . $almacen_id . "";
                        $stmt = $PDO->prepare($SQL);
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        $stmt->execute();
                        $rows = $stmt->fetchAll();
                        foreach ($rows as $rowv) {
                            $servicio = $servicio + $rowv['SERVICIO'];
                        }
                    }
                    //para formatear salida
                }


                $plan = (float)$unidad->getPlaneMesServicio((int)date("n"));
                $array['unidad'] = $unidad->getNombre();
                $array['plan'] = $plan;
                $array['real'] = $servicio;
                $array['porciento'] = 0;
                if ($plan > 0) {
                    $array['porciento'] =  $servicio * 100 / $plan;
                }
            } catch (Exception $e) {
                $plan = (float)$unidad->getPlaneMesServicio((int)date("n"));
                $array['unidad'] = $unidad->getNombre();
                $array['plan'] = $plan;
                $array['real'] = '<strong class="text-danger">Error</strong>';
                $array['porciento'] = 0;
            }
            $datapanel[] = $array;
        }

        return $this->json(array('code' => 200, 'mensaje' => "Datos de Servicios".$mes, 'servicios' =>  $datapanel));
    }

    #[Route('/panel/getplananual', name: 'app_get_plan_anual', methods: ['POST', 'GET'])]
    public function app_get_plan_anual(Request $request, UnidadRepository $unidadRepository, MesRepository $mesRepository)
    {
        $mes = $request->get('mes'); //int 1-12
        $datapanel = array();
        if (!$mes) {  //parametro mes 1-11
            return $this->json(array('code' => 300, 'mensaje' => "Parámetro Incorrecto"));
        } else if ($mes < 1 || $mes > 12) {
            return $this->json(array('code' => 300, 'mensaje' => "Parámetro Incorrecto"));
        }

        $a = date("Y");  //año actual
        $diasmes = cal_days_in_month(CAL_GREGORIAN, $mes, (int)$a);  //dias del mes 
        $d = '' . $diasmes; //28-29-30-31
        $m = '' . $mes;
        if (strlen($m) == 1) {  //formato mes a //01-12 
            $m = '0' . $m;
        }       
        $desde = $a . "-" . $m . "-01 00:00:00";
        $hasta = $a . "-" . $m . "-" . $d . " 23:59:59";

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_DIRECTIVO')) {
            $unidades = $unidadRepository->findAll();
        } else {
            //es ROLE_COMERCIAL o ROLE_TRABAJADOR
            $unidad = $this->getUser()->getUnidad();
            $unidades = new ArrayCollection();
            $unidades->add($unidad);
        }
        // $unidades = $unidadRepository->findAll();
       

        foreach ($unidades as $unidad) {
            $array = array();
            $plananualtotal = 0;
            $plananualactual = 0;
            $planes = $unidad->getPlanes();

            //obtengo plan anual
            foreach ($planes as $plan) {
                $plananualtotal = $plananualtotal + $plan->getServicio() + $plan->getVenta();  //plan del mes total
                if ($plan->getMes()->getNumero() <= date("n")) {  //sumo hasta el mes actual
                    $plananualactual = $plananualtotal;
                }
            }


            $venta = 0;
            $servicio = 0;
            $cxDB = $unidad->getConexionServicio();
            try {
                if ($cxDB->getSistema() == 1) {  //facsi 
                    $PDO = $cxDB->getPDO();

                    // $SQL = "SELECT SUM(ROUND( [total mn], 2))  as SERVICIO  FROM factura AS a WHERE estado = 2 AND  a.fecha>= '" . $desde . "' AND a.fecha<= '" . $hasta . "'";
                    $SQL = "SELECT SUM(ROUND( [total mn], 2))  as SERVICIO FROM factura a, clientes c WHERE a.idcliente= c.id_Cliente AND ( estado = 0 OR estado = 2 ) AND a.fecha>= '" . $desde . "'   AND a.fecha<= '" . $hasta . "'";


                    $stmt = $PDO->prepare($SQL);
                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                    $stmt->execute();
                    $rows = $stmt->fetchAll();
                    foreach ($rows as $rowv) {
                        $servicio = $servicio + $rowv['SERVICIO'];
                    }
                }
                if ($cxDB->getSistema() == 2) {  //facsinv
                    $PDO = $cxDB->getPDO();
                    $almacenes = $unidad->getAlmacenesServicio();

                    foreach ($almacenes as $alm) {
                        $almacen_id = $alm->getIdAlmacen();

                        $SQL = "SELECT SUM(ROUND( [total mn], 2))  as SERVICIO  FROM factura a, clientes c WHERE a.idcliente= c.id_Cliente 
                                  AND ( estado = 0 OR estado = 2 ) 
                                  AND a.fecha>= '" . $desde . "' 
                                  AND a.fecha<= '" . $hasta . "'
                                  AND a.almacen_id=" . $almacen_id . "";
                        $stmt = $PDO->prepare($SQL);
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        $stmt->execute();
                        $rows = $stmt->fetchAll();
                        foreach ($rows as $rowv) {
                            $servicio = $servicio + $rowv['SERVICIO'];
                        }
                    }
                }



                $array['unidad'] = $unidad->getNombre();
                $array['planactual'] = $plananualactual;
                $array['plantotal'] = $plananualtotal;
                $array['real'] = $servicio;
                $array['porciento'] = 0;
                if ($plan > 0) {
                    $array['porciento'] =  $servicio * 100 / $plan;
                }
            } catch (Exception $e) {
                $plan = (float)$unidad->getPlaneMesServicio((int)date("n"));
                $array['unidad'] = $unidad->getNombre();
                $array['plan'] = $plan;
                $array['real'] = '<strong class="text-danger">Error</strong>';
                $array['porciento'] = 0;
            }
            $datapanel[] = $array;
        }

        return $this->json(array('code' => 200, 'mensaje' => "Datos de Planes", 'planesanueales' =>  $datapanel));
    }
}
