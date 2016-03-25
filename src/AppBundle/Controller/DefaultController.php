<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $user = new User();
        $sessionServise =  $this->get('session_service')
            ->setSession($request->getSession());

        $form = $this->createFormBuilder($user)
            ->add('login', 'text')
            ->add('password', 'password')
            ->getForm();
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            
            if ( $time = $sessionServise->checkCounts() ) {
                $request->getSession()->getFlashBag()
                    ->add('notice', 'Try after ' . $time . ' seconds');
            } else {
                if ($form->isValid() && $this->get('user_service')->checkUser($user)) {
                    $sessionServise->login($user->getLogin());
                    return $this->redirect($this->generateUrl('user'), 301);
                } else {
                    $sessionServise->addCount();
                    $request->getSession()->getFlashBag()
                        ->add('notice', 'Invalid data');
                }
            }
            
            
        }

        return $this->render('AppBundle:Default:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
