<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Tests\EventListener;

use Ajgl\Security\Http\EventListener\SessionRegistryGarbageCollectorListener;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionRegistryGarbageCollectorListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testGarbageCollectorIsCalled()
    {
        $maxlifetime = 1;

        $sessionRegistry = $this->getSessionRegistry();
        $sessionRegistry
            ->expects($this->once())
            ->method('collectGarbage')
            ->with($this->equalTo($maxlifetime));

        $event = $this->getPostResponseEvent();

        $listener = new SessionRegistryGarbageCollectorListener(
            $sessionRegistry,
            $maxlifetime,
            1,
            1
        );

        $listener->onKernelTerminate($event);
    }

    public function testGarbageCollectorIsNotCalled()
    {
        $maxlifetime = 1;

        $sessionRegistry = $this->getSessionRegistry();
        $sessionRegistry
            ->expects($this->never())
            ->method('collectGarbage');

        $event = $this->getPostResponseEvent();

        $listener = new SessionRegistryGarbageCollectorListener(
            $sessionRegistry,
            $maxlifetime,
            0
        );

        $listener->onKernelTerminate($event);
    }

    private function getSessionRegistry()
    {
        return $this->getMockBuilder('Ajgl\Security\Http\Session\Registry\SessionRegistry')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getPostResponseEvent()
    {
        return $this->getMockBuilder('Symfony\Component\HttpKernel\Event\PostResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
