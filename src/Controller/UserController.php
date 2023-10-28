<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UnidadRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/usuarios', name: 'app_user')]
    public function index(RoleRepository $roleRepository, UnidadRepository $unidadRepository, UserRepository $usuarioRepository)
    {
 
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

            $roles = $roleRepository->findAll();
            foreach ($roles as $rol) {
                $array = array();
                $array['id'] = $rol->getId();
                $array['nombre'] = $rol->getNombre();
                $arrayRoles[] = $array;
            }
        } else {
            //es comercial
            $roles = $roleRepository->findByNivel(2);
            foreach ($roles as $rol) {
                $array = array();
                $array['id'] = $rol->getId();
                $array['nombre'] = $rol->getNombre();
                $arrayRoles[] = $array;
            }

            $username = $this->getUser()->getUserIdentifier();
            $useractual = $usuarioRepository->findOneBy(array('username' => $username));
            $unidadactual = $useractual->getUnidad();
            $array = array();
            $array['id'] = $unidadactual->getId();
            $array['nombre'] = $unidadactual->getNombre();
            $arrayUnidades[] = $array;
        }
        return $this->render('user/index.html.twig', [
            'roles' => $arrayRoles,
            'unidades' => $arrayUnidades
        ]);
    }

    #[Route('/usuarios/get', name: 'app_get_usuarios', methods: ['POST', 'GET'])]
    public function getusuarios(Request $request, UserRepository $usuarioRepository, UnidadRepository $unidadRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }

        $id = $request->get('id');
        //parameros incorrctos chequeo
        if ($id) {
            //check si existe
            $usuDB = $usuarioRepository->find((int)$id);
            if (!$usuDB) {
                return $this->json(array('code' => 202, 'mensaje' => "Usuario inexistente"));
            }
            $resp = array(
                'code' => 200,
                'mensaje' => "Datos de Usuario",
                'id' => $usuDB->getId(),
                'nombre' => $usuDB->getNombre(),
                'apellidos' => $usuDB->getApellidos(),
                'usuario' => $usuDB->getUserIdentifier(),
                'rol' =>  $usuDB->getRol()->getId(),
                'unidad' =>   $unidad = $usuDB->getUnidad()->getId(),

            );
            return $this->json($resp);
        }

        if ($this->isGranted('ROLE_COMERCIAL')) {
            //solo usuarios de su unidad
            $username = $this->getUser()->getUserIdentifier();
            $useractual = $usuarioRepository->findOneBy(array('username' => $username));
            $unidadactual = $useractual->getUnidad();
            $usuarios =  $unidadactual->getUsuarios();
        } else {
            //role adin
            $unidad = $request->get('unidad');
            if ($unidad) {
                $uniDB = $unidadRepository->find($unidad);
                if (!$uniDB) {
                    return $this->json(array('code' => 202, 'mensaje' => "Unidad inexistente"));
                }
                $usuarios = $uniDB->getUsuarios();
            } else {

                $usuarios = $usuarioRepository->findAll();
            }
        }
        $arrayUsuarios = array();
        foreach ($usuarios as $usuario) {
            $array = array();
            $array['id'] = $usuario->getId();
            $array['username'] = $usuario->getUserIdentifier();
            $array['nombre'] = $usuario->getNombre();
            $array['apellidos'] = $usuario->getApellidos();
            $array['rol'] = $usuario->getRol()->getNombre();
            $array['unidad'] =  $usuario->getUnidad()->getNombre();
            $arrayUsuarios[] = $array;
        }
        return $this->json(array('code' => 200, 'mensaje' => "Usuarios", 'usuarios' => $arrayUsuarios,));
    }

    #[Route('/usuarios/add', name: 'app_add_usuarios', methods: ['POST', 'GET'])]
    public function app_add_usuarios(Request $request, UnidadRepository $unidadRepository, RoleRepository $roleRepository,  UserRepository $usuarioRepository, UserPasswordHasherInterface $passwordEncoder): JsonResponse
    {

        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }
        //data: { nombre: nombre, apellidos: apellidos, rol: rol, usuario: usuario, password: password, ci: ci, newpassword: newpassword }
        try {
            $nombre = $request->get('nombre');
            $apellidos = $request->get('apellidos');

            $rol = $request->get('rol');
           
            $unidad = $request->get('unidad');
            $usuario = $request->get('usuario');
            $password = $request->get('password');
            $unidad = $request->get('unidad');


            //parameros incorrctos chequeo
            if (strlen($nombre) > 255 || !preg_match("/^[a-zA-Z0-9ñÑüÜñáéíóúÁÉÍÓÚ ]+$/i", $nombre)) {
                return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
            }
            if (strlen($usuario) > 255 || !preg_match("/^[a-z]+$/i", $usuario)) {
                return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
            }
            if (strlen($apellidos) > 255 || !preg_match("/^[a-zA-Z0-9ñÑüÜñáéíóúÁÉÍÓÚ ]+$/i", $apellidos)) {
                return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
            }


            if (strlen($password) > 20 || strlen($password) < 8) {
                return $this->json(array('code' => 300, 'mensaje' => "Contraseña inválida"));
            }
           
            if (!$unidad || !$rol) { //opcional
                return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
            }
            $uniDB = $unidadRepository->find($unidad);
            if (!$uniDB) {
                return $this->json(array('code' => 202, 'mensaje' => "Unidad inexistente"));
            }
            $rolDB = $roleRepository->find($rol);
            if (!$rolDB) {
                return $this->json(array('code' => 202, 'mensaje' => "Rol inexistente"));
            }




            if ($this->isGranted('ROLE_COMERCIAL')) {  //check para comercial
                //solo usuarios de su unidad
                if ($this->getUser()->getUnidad()->getId() != $uniDB->getId()) {  //chequeo q sea en la misma unidad
                    return $this->json(array('code' => 202, 'mensaje' => "No tiene permiso para  aregar en la Unidad " . $uniDB->getNombre()));
                }
                if ($rolDB->getNivel() > 2) { //hasta nivel 2 q es comercial
                    return $this->json(array('code' => 202, 'mensaje' => "No tiene permiso para aregar el Rol " . $rolDB->getNombre()));
                }
            }


            //check si existe
            $usuDB = $usuarioRepository->findOneBy(array('username' => $usuario));
            if ($usuDB) {
                return $this->json(array('code' => 300, 'mensaje' => "Usuario " . $usuario . " en uso"));
            }

            $user = new User();
            $user->setNombre($nombre);
            $user->setApellidos($apellidos);
          
            $user->setUsername($usuario);
            $user->setPassword($passwordEncoder->hashPassword($user, $password));
            $user->setUnidad($uniDB);
            $user->setRol($rolDB);
            $usuarioRepository->save($user, true);


            return $this->json(array('code' => 200, 'mensaje' => "Usuario ". $usuario." creado"." ".  $rolDB->getNombre(),));
        } catch (Exception $e) {
            return $this->json(array('code' => 500, 'mensaje' => "Error Interno: " . $e->getMessage(),));
        }
    }

    #[Route('/usuarios/edt', name: 'app_edt_usuarios', methods: ['POST'])]
    public function app_edt_usuarios(Request $request, UnidadRepository $unidadRepository, RoleRepository $roleRepository,  UserRepository $usuarioRepository): JsonResponse
    {

        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }
        //data: { nombre: nombre, apellidos: apellidos, rol: rol, usuario: usuario, password: password, ci: ci, newpassword: newpassword }
        try {
            $nombre = $request->get('nombre');
            $apellidos = $request->get('apellidos');
            $rol = $request->get('rol');
           
            $unidad = $request->get('unidad');
            $usuario = $request->get('usuario');
            $unidad = $request->get('unidad');

            //parameros incorrctos chequeo
            if (strlen($nombre) > 255 || !preg_match("/^[a-zA-Z0-9ñÑüÜñáéíóúÁÉÍÓÚ ]+$/i", $nombre)) {
                return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
            }
            if (strlen($usuario) > 255 || !preg_match("/^[a-z]+$/i", $usuario)) {
                return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
            }
            if (strlen($apellidos) > 255 || !preg_match("/^[a-zA-Z0-9ñÑüÜñáéíóúÁÉÍÓÚ ]+$/i", $apellidos)) {
                return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
            }
           
            if (!$unidad || !$rol) { //opcional
                return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
            }
            $uniDB = $unidadRepository->find($unidad);
            if (!$uniDB) {
                return $this->json(array('code' => 202, 'mensaje' => "Unidad inexistente"));
            }
            $rolDB = $roleRepository->find($rol);
            if (!$rolDB) {
                return $this->json(array('code' => 202, 'mensaje' => "Rol inexistente"));
            }
            if ($this->isGranted('ROLE_COMERCIAL')) {  //check para comercial
                //solo usuarios de su unidad

                $unidadactual =  $this->getUser()->getUnidad();
                if ($unidadactual->getId() != $uniDB->getId()) {
                    return $this->json(array('code' => 202, 'mensaje' => "No tiene permiso para aregar en la Unidad " . $uniDB->getNombre()));
                }
                if ($rolDB->getNivel() > 2) { //hasta nivel 2 q es comercial
                    return $this->json(array('code' => 202, 'mensaje' => "No tiene permiso para aregar el Rol " . $rolDB->getNombre()));
                }
            }
            //check si existe
            $usuDB = $usuarioRepository->findOneBy(array('username' => $usuario));
            if (!$usuDB) {
                return $this->json(array('code' => 300, 'mensaje' => "Usuario Inexistente"));
            }

            $usuDB->setNombre($nombre);
            $usuDB->setApellidos($apellidos);
            $usuDB->setUnidad($uniDB);
            $usuDB->setRol($rolDB);
            $usuarioRepository->save($usuDB, true);
            return $this->json(array('code' => 200, 'mensaje' => "Usuario " . $nombre . " actualizado",));
        } catch (Exception $e) {
            return $this->json(array('code' => 500, 'mensaje' => "Error Interno: " . $e->getMessage(),));
        }
    }

    #[Route('/usuarios/resetpass', name: 'app_resetpass_usuarios', methods: ['POST'])]
    public function app_resetpass_usuarios(Request $request, UnidadRepository $unidadRepository, RoleRepository $roleRepository,  UserPasswordHasherInterface $passwordEncoder, UserRepository $usuarioRepository): JsonResponse
    {

        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }
        //data: { nombre: nombre, apellidos: apellidos, rol: rol, usuario: usuario, password: password, ci: ci, newpassword: newpassword }
        try {
            $id = $request->get('id');
            $password = $request->get('password');

            //parameros incorrctos chequeo
            if (!$id) {
                return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
            }
            $usuDB = $usuarioRepository->find($id);
            if (!$usuDB) {
                return $this->json(array('code' => 300, 'mensaje' => "Usuario Inexistente"));
            }
            if (strlen($password) > 20 || strlen($password) < 8) {
                return $this->json(array('code' => 300, 'mensaje' => "Contraseña inválida"));
            }
            if ($this->isGranted('ROLE_COMERCIAL')) {
                //no puede editar admin u otro nivel +
                if ($usuDB->getRol()->getNivel() > 2) {
                    return $this->json(array('code' => 300, 'mensaje' => "No tiene permiso para editar el usuario"));
                }
            }


            $usuDB->setPassword($passwordEncoder->hashPassword($usuDB, $password));
            $usuarioRepository->save($usuDB, true);

            return $this->json(array('code' => 200, 'mensaje' => "Se ha cambiado la contraseña de " . $usuDB->getNombre() . " ",));
        } catch (Exception $e) {
            return $this->json(array('code' => 500, 'mensaje' => "Error Interno: " . $e->getMessage(),));
        }
    }

    #[Route('/usuarios/del', name: 'app_del_usuarios', methods: ['POST', 'GET'])]
    public function app_del_usuarios(Request $request, UserRepository $usuarioRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_COMERCIAL')) {
            return $this->json(array('code' => 201, 'mensaje' => "No tiene permiso a esta url",));
        }

        $id = $request->get('id');
        //parameros incorrctos chequeo
        //parameros incorrctos chequeo
        if (!$id) {
            return $this->json(array('code' => 300, 'mensaje' => "Formato inválido"));
        }
        if ($this->getUser()->getId() == $id) {
            return $this->json(array('code' => 300, 'mensaje' => "No se puede eliminar al usuario actual"));
        }
        $usuDB = $usuarioRepository->find($id);
        if (!$usuDB) {
            return $this->json(array('code' => 300, 'mensaje' => "Usuario Inexistente"));
        }



        if ($this->isGranted('ROLE_COMERCIAL')) { //solo puede eliminar en su propia unidad
            //solo usuarios de su unidad

            if ($this->getUser()->getUnidad()->getId() != $usuDB->getUnidad()->getId()) {  //chequeo q sea en la misma unidad
                return $this->json(array('code' => 202, 'mensaje' => "No tiene permiso para eliminar usuarios en la  Unidad " . $usuDB->getUnidad()->getNombre()));
            }

            if ($usuDB->getRol()->getNivel() > 2) {
                return $this->json(array('code' => 300, 'mensaje' => "No tiene permiso para editar el usuario"));
            }
        }
        $usuarioRepository->remove($usuDB, true);
        return $this->json(array('code' => 200, 'mensaje' => "Se ha eliminado a " . $usuDB->getNombre()));
    }
}
