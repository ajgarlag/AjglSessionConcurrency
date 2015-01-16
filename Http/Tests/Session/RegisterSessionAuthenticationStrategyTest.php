<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) 2010-2015 Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Tests\Session;

use Ajgl\Security\Http\Session\RegisterSessionAuthenticationStrategy;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class RegisterSessionAuthenticationStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterSession()
    {
        $session = $this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->expects($this->any())->method('getId')->will($this->returnValue('bar'));
        $request = $this->getRequest($session);

        $registry = $this->getSessionRegistry();
        $registry->expects($this->once())->method('registerNewSession')->with($this->equalTo('bar'), $this->equalTo('foo'));

        $strategy = new RegisterSessionAuthenticationStrategy($registry);
        $strategy->onAuthentication($request, $this->getToken());
    }

    private function getRequest($session = null)
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        if (null !== $session) {
            $request->expects($this->any())->method('getSession')->will($this->returnValue($session));
        }

        return $request;
    }

    private function getToken()
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->any())->method('getUsername')->will($this->returnValue('foo'));

        return $token;
    }

    private function getSessionRegistry()
    {
        return $this->getMockBuilder('Ajgl\Security\Http\Session\Registry\SessionRegistry')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
