<?php

namespace App\Controller;

use App\Entity\Almacen;
use App\Entity\Conexion;
use App\Repository\AlmacenRepository;
use App\Repository\ConexionRepository;
use App\Repository\UnidadRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ConexionController extends AbstractController
{
    #[Route('/conexiones', name: 'app_conexiones')]
    public function index(Request $request, ConexionRepository $conexionRepository, UnidadRepository $unidadRepository)
    {

        if (!($this->isGranted('ROLE_ADMIN'))) {
            return $this->render('acceso/index.html.twig', ['message' => 'No tiene acceso a esta url']);
        }

        $unidades = $unidadRepository->findAll();
        $arrayUnidades = array();
        foreach ($unidades as $unidad) {
            $array = array();
            $array['id'] = $unidad->getId();
            $array['nombre'] = $unidad->getNombre();
            $arrayUnidades[] = $array;
        }
        $conexion = new Conexion();
        return $this->render('conexion/index.html.twig', [
            'unidades' => $arrayUnidades,
            'sistemas' => $conexion->getSistemas(),
        ]);
    }

    #[Route('/conexiones/get', name: 'app_get_conexiones', methods: ['POST', 'GET'])]
    public function getconexiones(Request $request, ConexionRepository $conexionRepository, UnidadRepository $unidadRepository, AlmacenRepository $almacenRepository): JsonResponse
    {
        if (!($this->isGranted('ROLE_ADMIN'))) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }
        $id = $request->get('id');
        $unidad = $request->get('unidad');
        //parameros incorrctos chequeo
        if ($id) {
            //check si existe
            $cxDB = $conexionRepository->find((int)$id);
            if (!$cxDB) {
                return $this->json(array('code' => 202, 'mensaje' => "Conexión inexistente"));
            }

            $arrayalmacenes = array();
            foreach ($cxDB->getAlmacenes() as $alm) {
                $array = array();
                $array['id'] = $alm->getIdAlmacen();
                $array['nombre'] = $alm->getNombre();
                $array['seleccionado'] = $alm->isSeleccionado();
                $arrayalmacenes[] = $array;
            }

            $resp = array(
                'code' => 200,
                'mensaje' => "Datos Conexión",
                'id' => $cxDB->getId(),
                'tipo' => $cxDB->getTipoStr(),
                'sistema' => $cxDB->getSistema(),
                'host' => $cxDB->getHost(),
                'instancia' => $cxDB->getInstancia(),
                'db' => $cxDB->getDb(),
                'usuario' => $cxDB->getUsuario(),
                'contrasna' => $cxDB->getContrasena(),
                'almacenes' => $arrayalmacenes,

            );
            return $this->json($resp);
        }

        if ($unidad) {
            //check si existe
            $uniDB = $unidadRepository->find((int)$unidad);
            if (!$uniDB) {
                return $this->json(array('code' => 202, 'mensaje' => "Unidad inexistente"));
            }
            $arrayconexiones = array();
            foreach ($uniDB->getConexion() as $conexion) {
                $array = array();

                $array['id'] = $conexion->getId();
                $array['tipo'] = $conexion->getTipoStr();
                $array['sistema'] = $conexion->getSistemaStr();
                $array['host']  = $conexion->getHost();
                $array['instancia'] = $conexion->getInstancia();
                $array['db'] = $conexion->getDb();
                $array['usuario'] = $conexion->getUsuario();
                $array['contrasna'] = $conexion->getContrasena();

                $alm = $conexion->getAlmacenes();
                $stralmacenes = '';
                $can=0;
                for ($i = 0; $i < $alm->count(); $i++) {
                    if ($alm[$i]->isSeleccionado()){
                        if ($can== 0) {
                            $stralmacenes = '' . $alm[$i]->getNombre();
                        } else {
                            $stralmacenes = $stralmacenes . ' - ' . $alm[$i]->getNombre();
                        }
                        $can++;
                    }
                       
                }
                $array['almacenes'] = $stralmacenes;

                $arrayconexiones[] = $array;
            }
            return $this->json(array('code' => 200, 'mensaje' => "conexiones", 'conexiones' => $arrayconexiones,));
        }
        return $this->json(array('code' => 300, 'mensaje' => "Parámetros Incorrectos"));
    }


    #[Route('/conexiones/edt', name: 'app_edt_conexiones', methods: ['POST'])]
    public function app_edt_conexiones(Request $request, ConexionRepository $conexionRepository, AlmacenRepository $almacenRepository): JsonResponse
    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }
        //data: { nombre: nombre, apellidos: apellidos, rol: rol, usuario: usuario, password: password, ci: ci, newpassword: newpassword }

        try {

            $id = $request->get('id');

            $sistema = $request->get('sistema');
            $host = $request->get('host');
            $instancia = $request->get('instancia');
            $bd = $request->get('bd');
            $usuario = $request->get('usuario');
            $contrasena = $request->get('contrasena');
            $almacenes = $request->get('almacenes');

            //parameros incorrctos chequeo
            if (!$sistema || !$host || !$bd || !$usuario || !$contrasena) {
                return $this->json(array('code' => 300, 'mensaje' => "Parámetro Incorrcto"));
            }
            //check si existe
            $cxDB = $conexionRepository->find($id);
            if (!$cxDB) {
                return $this->json(array('code' => 300, 'mensaje' => "Conexión Inexistente"));
            }

            $cxDB->setSistema($sistema);
            $cxDB->setHost($host);
            $cxDB->setDb($bd);
            $cxDB->setInstancia($instancia);
            $cxDB->setContrasena($contrasena);
            $cxDB->setUsuario($usuario);

            //agrego almacenes
            $txt = "";

            if ($almacenes) {
                $almacenes = json_decode($almacenes);
                //  $cxDB->clearAlmacenes();
                foreach ($almacenes as $alm) {
                    $almacen = $almacenRepository->findOneBy(['conexion' => $cxDB, 'nombre' => $alm[1]]);
                    if (!$almacen) {
                        $almacen = new Almacen();
                        $almacen->setNombre($alm[1]);
                    }
                    $almacen->setIdAlmacen($alm[0]);
                    $almacen->setSeleccionado($alm[2]);
                    $almacenRepository->save($almacen, true);
                    $cxDB->addAlmacen($almacen);
                }
            }
            $conexionRepository->save($cxDB, true);

            return $this->json(array('code' => 200, 'mensaje' => "Conexión  actualizada",));
        } catch (Exception $e) {
            return $this->json(array('code' => 200, 'mensaje' => "Error Interno: " . $e->getMessage(),));
        }
    }

    #[Route('/conexiones/check', name: 'app_check_conexiones', methods: ['POST'])]
    public function app_check_conexiones(Request $request,): JsonResponse
    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }
        try {
            $host = $request->get('host');
            $instancia = $request->get('instancia');
            $bd = $request->get('bd');
            $usuario = $request->get('usuario');
            $contrasena = $request->get('contrasena');
            $almacenes = $request->get('almacenes');

            //parameros incorrctos chequeo
            if (!$host || !$bd || !$usuario || !$contrasena) {
                return $this->json(array('code' => 300, 'mensaje' => "Parámetro Incorrcto"));
            }
            $cxDB = new Conexion();
            $cxDB->setHost($host);
            $cxDB->setDb($bd);
            $cxDB->setInstancia($instancia);
            $cxDB->setContrasena($contrasena);
            $cxDB->setUsuario($usuario);



            if ($almacenes) {  //carga de almacenes
                $almacenes = $cxDB->cargarAlmacenes();
                return $this->json(array('code' => 200, 'mensaje' => "Almacenes de Conexión", 'almacenes' => $almacenes));
            }
            //return $this->json(array('code' => 200, 'mensaje' => "cadenaPDO: " . $cxDB->cadenaPDO()));
            $result = $cxDB->check();
            return $this->json(array('code' => 200, 'mensaje' => "Conexión  Exitosa "));
        } catch (Exception $e) {
            return $this->json(array('code' => 201, 'mensaje' => "Error Interno: " . $e->getMessage(),));
        }
    }
}
