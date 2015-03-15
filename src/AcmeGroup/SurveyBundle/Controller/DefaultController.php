<?php

namespace AcmeGroup\SurveyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AcmeGroupSurveyBundle:Default:index.html.twig', array('name' => $name));
    }
}
