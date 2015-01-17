<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Tests\Session;

use Ajgl\Security\Http\Session\CompositeSessionAuthenticationStrategy;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class CompositeSessionAuthenticationStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testAuthenticationDelegation()
    {
        $strategies = array(
            $this->getDelegateAuthenticationStrategy(),
            $this->getDelegateAuthenticationStrategy(),
            $this->getDelegateAuthenticationStrategy(),
        );

        $request = $this->getRequest();

        $strategy = new CompositeSessionAuthenticationStrategy($strategies);
        $strategy->onAuthentication($request, $this->getToken());
    }

    private function getRequest()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request');
    }

    private function getToken()
    {
        return $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
    }

    private function getDelegateAuthenticationStrategy()
    {
        $strategy = $this->getMock('Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface');
        $strategy->expects($this->once())->method('onAuthentication');

        return $strategy;
    }
}
