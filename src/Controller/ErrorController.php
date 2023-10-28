<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ErrorController extends AbstractController
{
    public function show($exception,$logger){
        if($exception->getCode() === 403){
            return $this->render('bundles/TwigBundle/Exception/error403.html.twig', [
               'code'=>403,
               'message'=>$exception->getMessage()
            ]);
        }
        else if($exception->getCode() === 404){
            return $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
               'code'=>404,
               'message'=>$exception->getMessage()
            ]);
        }
        else if($exception->getCode() === 400){
            return $this->render('bundles/TwigBundle/Exception/error400.html.twig', [
               'code'=>400,
               'message'=>$exception->getMessage()
            ]);
        }
        return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
            'code'=>403,
            'message'=>$exception->getMessage()
        ]);
    }
}
