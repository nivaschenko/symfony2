<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;

class UserController extends Controller
{
    public function indexAction(Request $request)
    {
        $sessionServise =  $this->get('session_service')
            ->setSession($request->getSession());
        
        if ( !$sessionServise->isLogin() ) {
            return $this->redirect($this->generateUrl('index'), 301);
        }
        
        $form = $this->createFormBuilder([])->getForm();
        
        if ($request->getMethod() == 'POST') {
            $sessionServise->logout();
            return $this->redirect($this->generateUrl('index'), 301);
        }

        return $this->render('AppBundle:User:index.html.twig', array(
            'form' => $form->createView(), ['username' => $sessionServise->getuserName()]
        ));
    }
}