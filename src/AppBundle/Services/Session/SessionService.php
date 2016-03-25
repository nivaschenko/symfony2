<?php

namespace AppBundle\Services\Session;

class SessionService
{
    static $attempts = 3;
    static $blockingTime = 5; // minutes
    
    private $session;

    public function setSession( \Symfony\Component\HttpFoundation\Session\Session $session)
    {
        $this->session = $session;
        return $this;
    }
    
    public function login($username)
    {
        $this->removeTimer();
        $this->session->set('username', $username);
        return $this;
    }
    
    public function logout()
    {
        $this->session->set('username', false);
        return $this;
    }
    
    public function islogin()
    {
        return !!$this->session->get('username');
    }
    
    public function getUserName()
    {
        return $this->session->get('username');
    }

    public function checkCounts()
    {
        if ( $this->session->get('count') >= SessionService::$attempts )
        {
            if ( !($this->session->get('time') instanceof \DateTime) ) {
                $this->setTime();
            }
            if ( SessionService::$blockingTime <= $this->session->get('time')->diff(new \DateTime())->format('%i') )
            {
                return $this->removeTimer();
            }
            $time = explode(':', $this->session->get('time')->diff(new \DateTime())->format('%i:%s'));
            return ($time[0] * 60) + $time[1];
        }
        return false;
    }

    public function addCount()
    {
            $count = $this->session->get('count') ? $this->session->get('count') : 1;
            $this->session->set('count', $count + 1);
            return $this;
    }

    private function setTime()
    {
        $this->session->set('time', new \DateTime());
        return $this;
    }

    private function removeTimer()
    {
        $this->session->set('count', NULL);
        $this->session->set('time', NULL);
        
        return false;
    }
}
