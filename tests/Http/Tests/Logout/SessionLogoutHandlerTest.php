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

use Ajgl\Security\Http\Logout\SessionLogoutHandler;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionLogoutHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testLogout()
    {
        $registry = $this->getMockBuilder('Ajgl\Security\Http\Session\Registry\SessionRegistry')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $handler = new SessionLogoutHandler($registry);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $request
            ->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session))
        ;

        $session
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('foobar'))
        ;

        $session
            ->expects($this->once())
            ->method('invalidate')
        ;

        $registry
            ->expects($this->once())
            ->method('removeSessionInformation')
            ->with($this->equalTo('foobar'))
        ;

        $handler->logout($request, $response, $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface'));
    }
}
