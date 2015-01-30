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
 * Clear session information from registry for idle sessions
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionRegistryGarbageCollectorListener implements EventSubscriberInterface
{
    private $registry;
    private $maxLifetime;
    private $probability;
    private $divisor;

    /**
     * @param SessionRegistry $registry
     * @param int             $maxLifetime
     * @param int             $probability
     * @param int             $divisor
     */
    public function __construct(SessionRegistry $registry, $maxLifetime = null, $probability = null, $divisor = null)
    {
        $this->registry = $registry;
        $this->maxLifetime = $maxLifetime ?: ini_get('session.gc_maxlifetime');
        $this->probability = $probability ?: ini_get('session.gc_probability');
        $this->divisor = $divisor ?: ini_get('session.gc_divisor');
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        if ($this->probability / $this->divisor > lcg_value()) {
            $this->registry->collectGarbage($this->maxLifetime);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::TERMINATE => array(array('onKernelTerminate')),
        );
    }
}
