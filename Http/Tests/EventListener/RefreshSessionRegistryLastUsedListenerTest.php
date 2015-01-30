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

use Ajgl\Security\Http\EventListener\RefreshSessionRegistryLastUsedListener;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class RefreshSessionRegistryLastUsedListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testSessionLastUsedIsRefreshed()
    {
        $lastUsed = time();

        $metadataBag = $this->getMock('\Symfony\Component\HttpFoundation\Session\Storage\MetadataBag');
        $metadataBag
            ->expects($this->any())
            ->method('getLastUsed')
            ->willReturn($lastUsed);

        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('foobar'));
        $session
            ->expects($this->any())
            ->method('getMetadataBag')
            ->will($this->returnValue($metadataBag));

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->any())
            ->method('hasSession')
            ->will($this->returnValue(true));
        $request
            ->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session));

        $sessionRegistry = $this->getSessionRegistry();

        $sessionRegistry
            ->expects($this->once())
            ->method('refreshLastUsed')
            ->with($this->equalTo('foobar'), $this->equalTo($lastUsed));

        $event = $this->getPostResponseEvent();
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new RefreshSessionRegistryLastUsedListener(
            $sessionRegistry
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
