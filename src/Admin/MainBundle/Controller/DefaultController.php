<?php

namespace Admin\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="admin_main_homepage")
     */
    public function indexAction()
    {
        return $this->render('AdminMainBundle:Default:index.html.twig');
    }
}
