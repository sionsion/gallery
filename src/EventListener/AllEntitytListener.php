<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\EventListener;

use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

use App\Entity\SendEmailsLog;

class AllEntitytListener {
    
    private $tokenStorage;
    protected $twig;
    protected $mailer;
    protected $mailto;

    public function __construct(TokenStorage $tokenStorage, \Symfony\Bridge\Monolog\Logger $logs, \Twig_Environment $twig, \Swift_Mailer $mailer, $mailto)
    {
        $this->tokenStorage  = $tokenStorage;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->mailto = $mailto;
    }
    
    public function prePersist(LifecycleEventArgs $args){
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        
        $user = $this->tokenStorage->getToken()->getUser();
        $userId = (int)$user->getId();
        $now = new \DateTime("now");
        
        if (method_exists($entity, 'setCreatedAt')) {
            $entity->setCreatedAt($now);
        }
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt($now);
        }
        if (method_exists($entity, 'setUserId')) {
            $entity->setUserId($userId);
            $entity->setUser($user);
        }

    }
    
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        $user = $this->tokenStorage->getToken()->getUser();
        $userId = (int)$user->getId();
        $em = $args->getEntityManager();

        switch(get_class($entity)){
            case 'App\Entity\Gallery': 
                
                $body = $this->twig->render(
                        'emails/photo.html.twig',
                        ['ename' => $entity->getEname(), 'email_from' => $user->getEmail(), 'id' => $entity->getId() ]
                    );

                $message = (new \Swift_Message('Image From Gallery Site'))
                ->setFrom($this->mailto)
                ->setCc($user->getEmail())
                ->setTo($this->mailto)
                ->setBody(
                    $body,
                    'text/html'
                )->attach(\Swift_Attachment::fromPath( realpath(trim($entity->getFile(),'/')) ));

                $this->mailer->send($message);

                $emaillog = new SendEmailsLog;
                $emaillog->setSendto($this->mailto);
                $emaillog->setUserId($userId);
                $emaillog->setUser($user);
                $emaillog->setBody($body);
                $emaillog->setFile($entity->getFile());
                $now = new \DateTime("now");
                $emaillog->setCreatedAt($now);

                $entityManager->persist($emaillog);
                $entityManager->flush();
                break;
        }
    }
    
    public function preUpdate(LifecycleEventArgs $args){
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
                
        $now = new \DateTime("now");
        $user = $this->tokenStorage->getToken()->getUser();
        $userId = (int)$user->getId();
          
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt($now);
        }
        if (method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($userId);
        }               
        
    }
}
