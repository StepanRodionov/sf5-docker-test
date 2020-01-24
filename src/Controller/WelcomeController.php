<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeController extends AbstractController
{
    /**
     * @Route("/", name="welcome")
     */
    public function index()
    {
        $s = $_SERVER;
        phpinfo();

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->connect();
        $connected = $em->getConnection()->isConnected();

        //phpinfo();
        //dump($connected);
        //die;

        return $this->render(
            'welcome/index.html.twig',
            [
                'controller_name' => 'WelcomeController',
            ]
        );
    }
}
