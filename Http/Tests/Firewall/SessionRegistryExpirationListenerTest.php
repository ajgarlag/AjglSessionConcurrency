<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Tests\Firewall;

use Ajgl\Security\Http\Firewall\SessionRegistryExpirationListener;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionRegistryExpirationListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Ajgl\Security\Core\Exception\SessionExpiredException
     */
    public function testSessionMarkedAsExpiredIsDetectedAndRemoved()
    {
        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('foobar'));

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session));

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $securityContext
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $sessionInformation = $this->getSessionInformation();
        $sessionInformation
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);

        $sessionRegistry = $this->getSessionRegistry();
        $sessionRegistry
            ->expects($this->any())
            ->method('getSessionInformation')
            ->with($this->equalTo('foobar'))
            ->will($this->returnValue($sessionInformation));

        $sessionRegistry
            ->expects($this->once())
            ->method('removeSessionInformation')
            ->with($this->equalTo('foobar'));

        $event = $this->getResponseEvent();
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new SessionRegistryExpirationListener(
            $sessionRegistry,
            $securityContext,
            $this->getHttpUtils(),
            1440
        );

        $listener->handle($event);
    }

    private function getSessionInformation()
    {
        return $this->getMockBuilder('Ajgl\Security\Http\Session\Registry\SessionInformation')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getSessionRegistry()
    {
        return $this->getMockBuilder('Ajgl\Security\Http\Session\Registry\SessionRegistry')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getHttpUtils()
    {
        return $this->getMockBuilder('Symfony\Component\Security\Http\HttpUtils')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getResponseEvent()
    {
        return $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
