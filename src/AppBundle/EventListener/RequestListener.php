<?php
namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use AppBundle\Entity\User;

class RequestListener
{
    protected $em;

    public function __construct($em) {
        $this->em = $em;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $event->getRequest()->getSession()->start();
        $user = $this->em->getManager()->getRepository('AppBundle\Entity\User')->findOneBy(array('sessionId' => $event->getRequest()->getSession()->getId()));
        if(!$user && $event->getRequest()->get('_route') != 'resume') {
            // Create user
            $user = new User();
            $user->setSessionId($event->getRequest()->getSession()->getId());
            $this->em->getManager()->persist($user);
            $this->em->getManager()->flush();
        }
    }
}