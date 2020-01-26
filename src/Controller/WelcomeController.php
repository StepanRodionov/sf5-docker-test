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
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->connect();
        $connected = $em->getConnection()->isConnected();

        //dump($connected);
        phpinfo();
        die;

        return $this->render(
            'welcome/index.html.twig',
            [
                'controller_name' => 'WelcomeController',
            ]
        );
    }
}
