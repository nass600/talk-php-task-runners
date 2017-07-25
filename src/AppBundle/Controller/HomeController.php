<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $repositories = $this->getDoctrine()->getRepository('AppBundle:Repository')->findAll();

        return $this->render('AppBundle::home/index.html.twig', [
            'repositories' => $repositories
        ]);
    }
}
