<?php

/**
 * Created by PhpStorm.
 * User: David
 * Date: 17/5/2022
 * Time: 19:39
 */

namespace App\Command;

use App\Entity\Conexion;
use App\Entity\Mes;
use App\Entity\Plan;
use App\Entity\Role;
use App\Entity\Unidad;
use App\Entity\User;
use App\Repository\ConexionRepository;
use App\Repository\MesRepository;
use App\Repository\PlanRepository;
use App\Repository\RoleRepository;
use App\Repository\UnidadRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class Configurar extends Command
{

    private $passwordEncoder = null;
    private $userRepository = null;
    private $roleRepository = null;
    private $mesRepository = null;
    private $planRepository = null;
    private $unidadRepository = null;
    private $conexionRepository = null;

    public function __construct(
        // ContainerInterface $container,
        UserPasswordHasherInterface $passwordEncoder,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        MesRepository  $mesRepository,
        PlanRepository $planRepository,
        UnidadRepository $unidadRepository,
        ConexionRepository $conexionRepository
    ) {

        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->mesRepository = $mesRepository;
        $this->planRepository = $planRepository;
        $this->planRepository = $planRepository;
        $this->unidadRepository = $unidadRepository;
        $this->conexionRepository = $conexionRepository;

        parent::__construct();
    }
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'configurar';
    protected static $defaultDescription = 'Creates a new user';

    protected function configure(): void
    {
        $this->setName('configurar');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        /*************ROLES*************/
        $output->write(['Creando los roles de seguridad en la BD']);
        $roles = array(
            array(
                'rol' => 'ROLE_TRABAJADOR',
                'nombre' => 'Trabajador',

                'nivel' => 1
            ),

            array(
                'rol' => 'ROLE_COMERCIAL',
                'nombre' => 'Comercial',

                'nivel' => 2
            ),
            array(
                'rol' => 'ROLE_DIRECTIVO',
                'nombre' => 'Directivo',
                'nivel' => 3
            ),
            array(
                'rol' => 'ROLE_ADMIN',
                'nombre' => 'Administrador',
                'nivel' => 4
            ),
        );
        foreach ($roles as $rol) {
            $roleBD =  $this->roleRepository->findOneBy(array(
                'rol' => $rol['rol']
            ));
            if (!$roleBD) {
                $roleBD = new Role();
                $roleBD->setRol($rol['rol']);
                $roleBD->setNombre($rol['nombre']);
                $roleBD->setNivel($rol['nivel']);
                $this->roleRepository->save($roleBD, true);
                $output->write(['..']);
            }
        }
        $output->writeln(['  OK']);

        /**Unidad***/
        $output->write(['Creando Unidad      ']);
        $unidad = $this->unidadRepository->findOneBy(array(
            'nombre' => 'Sitrans'
        ));

        if (!$unidad) {
            $unidad = new Unidad();
            $unidad->setNombre('Sitrans');
            $unidad->setCodigo('SIT');
            $unidad->setMargencomercial(12); 
        }
         //por defecto margen comercial
        $this->unidadRepository->save($unidad, true);
        $output->writeln(['  OK']);
        /*************Usuario*************/

        $username = 'admin';
        $pass = 'admin';
        $output->write(['Creando usuario    ']);
        $user = $this->userRepository->findOneBy(array(
            'username' => $username
        ));

        if (!$user) {
            $user = new User();
            $user->setUsername($username);
        }
        $user->setNombre($username);
        $user->setApellidos('');
       
        $roleBD =  $this->roleRepository->findOneBy(array('nivel' => 4));
        $user->setRol($roleBD);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $pass));
        $this->userRepository->save($user, true);
        $output->writeln(['  OK']);

        //***meses y plan*/
        $meses = array(
            array(
                'nombre' => 'Enero',
                'numero' => 1
            ),
            array(
                'nombre' => 'Febrero',
                'numero' => 2
            ),
            array(
                'nombre' => 'Marzo',
                'numero' => 3
            ),
            array(
                'nombre' => 'Abril',
                'numero' => 4
            ),
            array(
                'nombre' => 'Mayo',
                'numero' => 5
            ),
            array(
                'nombre' => 'Junio',
                'numero' => 6
            ),
            array(
                'nombre' => 'Julio',
                'numero' => 7
            ),
            array(
                'nombre' => 'Agosto',
                'numero' => 8
            ),
            array(
                'nombre' => 'Septiembre',
                'numero' => 9
            ),
            array(
                'nombre' => 'Octubre',
                'numero' => 10
            ),
            array(
                'nombre' => 'Noviembre',
                'numero' => 11
            ),
            array(
                'nombre' => 'Diciembre',
                'numero' => 12
            )
        );
        $output->write(['Creando los meses ']);

        foreach ($meses as $mes) {
            $mesBD = $this->mesRepository->findOneBy(array(
                'nombre' => $mes['nombre']
            ));

            if (!$mesBD) {
                $output->write(['.']);
                $mesBD = new Mes();
                $mesBD->setNombre($mes['nombre']);
                $mesBD->setNumero($mes['numero']);
               
            }
            $mesBD->setNumero((int)$mes['numero']);
            $this->mesRepository->save($mesBD, true);
        }
        $output->writeln([' OK']);

        //agregando usuario a unidad
        $output->write(['Agregando usuario admin a Unidad ' . $unidad->getNombre() . '   ']);

        $user->setUnidad($unidad);
        $this->userRepository->save($user, true);
        $output->writeln(['  OK']);

        //agregando plnes a unidad
        $output->write(['Agregando planes a Unidad ' . $unidad->getNombre() . '   ']);
        $meses = $this->mesRepository->findAll();
        foreach ($meses as $mes) {
            $planBD = $this->planRepository->findOneBy(array(
                'mes' => $mes,
                'unidad' => $unidad,

            ));
            if (!$planBD) {
                $plan = new Plan();
                $plan->setVenta(0);
                $plan->setServicio(0);
                $plan->setMes($mes);
                $plan->setUnidad($unidad);
                $output->write(['.']);
                $this->planRepository->save($plan, true);
            }
        }
        $output->writeln(['  OK']);

        $output->write(['Agregando conexion Venta a Unidad ' . $unidad->getNombre() . '   ']);
        //agregando conexiones de venta y servicio en blanco
        $cxBD = $this->conexionRepository->findOneBy(array(
            'tipo' => 1,  
            'unidad' => $unidad,

        ));
        if(!$cxBD){
            $cx = new Conexion();
            $cx->setTipo(1);
            $cx->setUnidad($unidad);
            $this->conexionRepository->save($cx, true);
        }
        $output->writeln(['  OK']);
 
        $output->write(['Agregando conexion Servicio a Unidad ' . $unidad->getNombre() . '   ']);
        $cxBD = $this->conexionRepository->findOneBy(array(
            'tipo' => 2,  
            'unidad' => $unidad,
        ));
        if(!$cxBD){
            $cx = new Conexion();
            $cx->setTipo(2);
            $cx->setUnidad($unidad);
            $this->conexionRepository->save($cx, true);
        }
        $output->writeln(['  OK']);

        return Command::SUCCESS;
    }
}
