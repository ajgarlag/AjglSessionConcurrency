<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\EventListener;

use Ajgl\Security\Http\Session\Registry\SessionRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * RefreshSessionRegistryMetadataListener refresh session last used timestamp.
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class RefreshSessionRegistryLastUsedListener implements EventSubscriberInterface
{
    private $registry;

    /**
     * @param SessionRegistry $registry
     */
    public function __construct(SessionRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Refresh the last used timestamp for the given session if registered.
     *
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (!$event->getRequest()->hasSession()) {
            return;
        }

        $session = $event->getRequest()->getSession();

        $this->registry->refreshLastUsed($session->getId(), $session->getMetadataBag()->getLastUsed());
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::TERMINATE => array(array('onKernelTerminate')),
        );
    }
}
