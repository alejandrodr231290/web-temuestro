<?php

namespace App\Controller;

use App\Repository\PlanRepository;
use App\Entity\User;
use App\Repository\MesRepository;
use App\Repository\RoleRepository;
use App\Repository\UnidadRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class PlanController extends AbstractController
{
    #[Route('/planes', name: 'app_plan')]
    public function index(UnidadRepository $unidadRepository)
    {

        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->render('acceso/index.html.twig', ['message' => 'No tiene acceso a esta url']);
        }
        $arrayUnidades = array();
        $arrayRoles = array();
        if ($this->isGranted('ROLE_ADMIN')) {
            $unidades = $unidadRepository->findAll();
            foreach ($unidades as $unidad) {
                $array = array();
                $array['id'] = $unidad->getId();
                $array['nombre'] = $unidad->getNombre();
                $arrayUnidades[] = $array;
            }
        } else {
            //es comercial
            $unidadactual = $this->getUser()->getUnidad();
            $array = array();
            $array['id'] = $unidadactual->getId();
            $array['nombre'] = $unidadactual->getNombre();
            $arrayUnidades[] = $array;
        }
        return $this->render('plan/index.html.twig', [
            'unidades' => $arrayUnidades
        ]);
    }

    #[Route('/planes/get', name: 'app_get_plan', methods: ['POST', 'GET'])]
    public function app_get_plan(Request $request, PlanRepository $planRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }

        $id = $request->get('id');
        //parameros incorrctos chequeo
        if (!$id) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        //check si existe
        $planDB = $planRepository->find($id);
        if (!$planDB) {
            return $this->json(array('code' => 202, 'mensaje' => "Plan inexistente"));
        }

        $resp = array(
            'code' => 200,
            'mensaje' => "Datos de Plan",
            'id' => $planDB->getId(),
            'venta' => $planDB->getVenta(),
            'servicio' => $planDB->getServicio(),
            'unidad' =>  $planDB->getUnidad()->getNombre(),
            'mes' =>  $planDB->getMes()->getNombre(),
        );
        return $this->json($resp);
    }

    #[Route('/planes/edt', name: 'app_edt_plan', methods: ['POST', 'GET'])]
    public function app_edt_plan(Request $request, PlanRepository $planRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }

        $id = $request->get('id');
        $servicio = $request->get('servicio');
        $venta = $request->get('venta');
        //parameros incorrctos chequeo
        if (!$id) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        if (!is_numeric($servicio)) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        if (!is_numeric($venta)) {
            return $this->json(array('code' => 202, 'mensaje' => "Parámetros incorrectos"));
        }
        //check si existe
        $planDB = $planRepository->find($id);
        if (!$planDB) {
            return $this->json(array('code' => 202, 'mensaje' => "Plan inexistente"));
        }
        if ($this->isGranted('ROLE_COMERCIAL')) {  //check para comercial
            //solo usuarios de su unidad
            $unidadactual =  $this->getUser()->getUnidad();
            if ($unidadactual->getId() != $planDB->getUnidad()->getId()) {
                return $this->json(array('code' => 202, 'mensaje' => "No tiene permiso editar el plan en la Unidad " . $planDB->getUnidad()->getNombre()));
            }
           
        }
        $planDB->setServicio($servicio);
        $planDB->setVenta($venta);
        $planRepository->save($planDB, true);

        return $this->json(array('code' => 200, 'mensaje' => "Plan de  " .  $planDB->getMes()->getNombre() . " actualizado",));
    }
}
