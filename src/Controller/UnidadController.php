<?php

namespace App\Controller;

use App\Entity\Conexion;
use App\Entity\Plan;
use App\Entity\Unidad;
use App\Repository\ConexionRepository;
use App\Repository\MesRepository;
use App\Repository\PlanRepository;
use App\Repository\UnidadRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UnidadController extends AbstractController
{
    #[Route('/unidades', name: 'app_unidades')]
    public function index()
    {
        if (!($this->isGranted('ROLE_ADMIN'))) {
            return $this->render('acceso/index.html.twig', [
                'message' => 'No tiene acceso a esta url'
            ]);
        }
        return $this->render('unidad/index.html.twig', []);
    }

    #[Route('/unidades/get', name: 'app_unidades_get', methods: ['POST','GET'])]
    public function app_unidades_get(Request $request, UnidadRepository $unidadRepository)
    {
        if (!$this->isGranted('ROLE_ADMIN')&& !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }

        $id = $request->get('id');
        //parameros incorrctos chequeo
        if ($id) {
            //check si existe
            $uniDB = $unidadRepository->find((int)$id);
            if (!$uniDB) {
                return $this->json(array('code' => 202, 'mensaje' => "Unidad inexistente"));
            }

            $planes=$uniDB->getPlanes();
            $arrayPlanes = array();
            foreach ($planes as $plan) {
                $array = array();
                $array['id'] = $plan->getId();
                $array['mes'] = $plan->getMes()->getNombre();
                $array['servicio'] = $plan->getServicio();
                $array['venta'] = $plan->getVenta();
                $array['total'] = $plan->getVenta()+$plan->getServicio();
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
        }

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


    #[Route('/unidades/add', name: 'app_unidades_add', methods: ['POST'])]
    public function app_unidades_add(Request $request,ConexionRepository $conexionRepository, UnidadRepository $unidadRepository, MesRepository $mesRepository, PlanRepository $planRepository)
    {

        if (!($this->isGranted('ROLE_ADMIN'))) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }

        $nombre = $request->get('nombre');
        $margencomercial = $request->get('margencomercial');
        $codigo = $request->get('codigo');
        //parameros incorrctos chequeo
        if (!$nombre) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        if (!$codigo) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }

        if (strlen($nombre) > 255 || !preg_match("/^[a-zA-Z0-9ñÑüÜñáéíóúÁÉÍÓÚ ]+$/i", $nombre)) {
            return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
        }
        if (!is_numeric($margencomercial)) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        } 
        if ($margencomercial==0) {
            return $this->json(array('code' => 202, 'mensaje' => "El Margen Comercial no puede ser cero"));
        } 


        $buscar = array();
        $buscar['nombre'] = $nombre;
        $uniDB = $unidadRepository->findBy($buscar);
        if ($uniDB) {
            return $this->json(array('code' => 202, 'mensaje' => "Ya existe la unidad " . $nombre));
        }

        $unidad = new Unidad();
        $unidad->setNombre($nombre);
        $unidad->setCodigo($codigo);
        $unidad->setMargencomercial($margencomercial);  //por defecto margen comercial
        $unidadRepository->save($unidad, true);
      
        //agregando plnes a unidad por meses en cero
        $meses = $mesRepository->findAll();
        foreach ($meses as $mes) {
            $plan = new Plan();
            $plan->setVenta(0);
            $plan->setServicio(0);
            $plan->setMes($mes);
            $plan->setUnidad($unidad);
            $planRepository->save($plan, true);
        }
        //agregando conexiones de venta y servicio en blanco
        $cxventa=new Conexion();
        $cxventa->setTipo(1);
        $cxventa->setUnidad($unidad);
        $conexionRepository->save($cxventa, true);

        $cxservicio=new Conexion();
        $cxservicio->setTipo(2);
        $cxservicio->setUnidad($unidad);
        $conexionRepository->save($cxservicio, true);
        return $this->json(array('code' => 200, 'mensaje' => "Se ha creado la unidad " . $unidad->getNombre()));
    }

    #[Route('/unidades/del', name: 'app_unidades_del', methods: ['POST'])]
    public function app_unidades_del(Request $request, UnidadRepository $unidadRepository,PlanRepository $planRepository)
    {

        if (!($this->isGranted('ROLE_ADMIN'))) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }

        $id = $request->get('id');
        //parameros incorrctos chequeo
        if (!$id) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }

        $uniDB = $unidadRepository->find($id);
        if (!$uniDB) {
            return $this->json(array('code' => 202, 'mensaje' => "No existe la unidad "));
        }
        if (count($uniDB->getUsuarios()) > 0) {
            return $this->json(array('code' => 202, 'mensaje' => "No se pude eliminar una unidad que contenga usuarios"));
        }

       
        $unidadRepository->remove($uniDB, true);
       //elimino planes
         $planes= $uniDB->getPlanes();
         foreach ($planes as $plan) {
            $planRepository->remove($plan,true);
         }    
         //elimino las conexiones



        return $this->json(array('code' => 200, 'mensaje' => "Se ha eliminado la unidad " . $uniDB->getNombre()));
    }

    #[Route('/unidades/edt', name: 'app_unidades_edt', methods: ['POST'])]
    public function app_unidades_edt(Request $request, UnidadRepository $unidadRepository)
    {

        if (!($this->isGranted('ROLE_ADMIN'))) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }

        $id = $request->get('id');
        $nombre = $request->get('nombre');
        $margencomercial= $request->get('margencomercial');
        $codigo = $request->get('codigo');
        //parameros incorrctos chequeo
        if (!$id) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        if (!$codigo) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        if (!$nombre) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        if (!is_numeric($margencomercial)) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        } 
        if ($margencomercial==0) {
            return $this->json(array('code' => 202, 'mensaje' => "El Margen Comercial no puede ser cero"));
        } 
        if (strlen($nombre) > 255 || !preg_match("/^[a-zA-Z0-9ñÑüÜñáéíóúÁÉÍÓÚ ]+$/i", $nombre)) {
            return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
        }

        $uniDB = $unidadRepository->find($id);
        if (!$uniDB) {
            return $this->json(array('code' => 202, 'mensaje' => "No existe la unidad "));
        }
        $uniDB ->setNombre($nombre);
        $uniDB ->setCodigo($codigo);        
        $uniDB ->setMargencomercial($margencomercial);
        $unidadRepository->save($uniDB, true);
        return $this->json(array('code' => 200, 'mensaje' => "Se ha actualizado la unidad " . $uniDB->getNombre()));
    }
}
