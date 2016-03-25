<?php

namespace AppBundle\Services\User;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use AppBundle\Entity\User;
use Exception;
use Monolog\Logger;

class UserService
{
    private $baseDir;
    private $logger;
    private $serializer;

    public function __construct($dir) {
        $this->baseDir = $dir;
        $this->logger = new Logger(__CLASS__);
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }
    
    public function checkUser( User $user)
    {
        if (  ($tmpUser = $this->read($user->getLogin())) instanceof User ) {
            return md5($user->getPassword()) == $tmpUser->getPassword();
        }
        
    }
    
    public function read($login)
    {
        $fileName = strtolower($login) . '.txt';
        $path = $this->baseDir . '/' . $fileName{0} . '/' . $fileName;

        if ( !is_file($path) ) {
            $this->logger->error('User ' . $login . ' not found');
            return false;
        }
        try {
            $str = file_get_contents($path);

            return $this->serializer->deserialize($str,'AppBundle\Entity\User', 'json');
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
        }
        return false;
    }
    
    public function write( User $user)
    {
        $fileName = strtolower($user->getLogin()) . '.txt';
        $encoder=$this->container->get('security.password_encoder');
        $user->setPassword(md5($password));
        $fileContent = $this->serializer->serialize($user, 'json');
        
        $fs = new Filesystem();
        try {
            if( !$fs->exists($this->baseDir . '/' . $fileName{0}) ) {
                $fs->mkdir($this->baseDir . '/' . $fileName{0});
            }
        } catch( IOExceptionInterface $e) {
            $this->logger->error('Can\'t create path ' . $e->getPath());
        }
        
        return !!file_put_contents($fileName, $fileContent);
    }
}
