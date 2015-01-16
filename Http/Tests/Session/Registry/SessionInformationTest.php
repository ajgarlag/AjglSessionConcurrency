<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) 2010-2015 Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Tests\Session\Registry;

use Ajgl\Security\Http\Session\Registry\SessionInformation;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionInformationTest extends \PHPUnit_Framework_TestCase
{
    public function testExpiration()
    {
        $sessionInfo = $this->getSessionInformation();
        $this->assertFalse($sessionInfo->isExpired());
        $sessionInfo->expireNow();

        $this->assertTrue($sessionInfo->isExpired());
    }

    public function testRefreshLastRequest()
    {
        $sessionInfo = $this->getSessionInformation();
        $lastRequest = $sessionInfo->getLastRequest();
        $this->assertInstanceOf('DateTime', $lastRequest);
        $sessionInfo->refreshLastRequest();
        $this->assertGreaterThanOrEqual($lastRequest, $sessionInfo->getLastRequest());
    }

    public function testGetSessionId()
    {
        $sessionInfo = $this->getSessionInformation();
        $this->assertEquals('foo', $sessionInfo->getSessionId());
    }

    public function testGetUsername()
    {
        $sessionInfo = $this->getSessionInformation();
        $this->assertEquals('bar', $sessionInfo->getUsername());
    }

    /**
     * @return SessionInformation
     */
    private function getSessionInformation()
    {
        return new SessionInformation('foo', 'bar', new \DateTime());
    }
}
