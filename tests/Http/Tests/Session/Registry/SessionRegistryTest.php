<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Tests\Session\Registry;

use Ajgl\Security\Http\Session\Registry\SessionRegistry;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAllSessions()
    {
        $storage = $this->getSessionRegistryStorage();
        $storage->expects($this->once())->method('getSessionInformations')->with('foo', true);
        $registry = $this->getSessionRegistry($storage);
        $registry->getAllSessions('foo', true);
    }

    public function testGetSessionInformation()
    {
        $storage = $this->getSessionRegistryStorage();
        $storage->expects($this->once())->method('getSessionInformation')->with('foobar');
        $registry = $this->getSessionRegistry($storage);
        $registry->getSessionInformation('foobar');
    }

    public function testRefreshLastUsed()
    {
        $sessionInformation = $this->getSessionInformation();
        $sessionInformation->expects($this->any())->method('getLastUsed')->willReturn(time()-1);
        $sessionInformation->expects($this->once())->method('refreshLastUsed');
        $storage = $this->getSessionRegistryStorage();
        $storage->expects($this->any())->method('getSessionInformation')->with('foobar')->will($this->returnValue($sessionInformation));
        $storage->expects($this->once())->method('saveSessionInformation')->with($sessionInformation);
        $registry = $this->getSessionRegistry($storage);
        $registry->refreshLastUsed('foobar');
    }

    public function testExpireNow()
    {
        $sessionInformation = $this->getSessionInformation();
        $sessionInformation->expects($this->once())->method('expireNow');
        $storage = $this->getSessionRegistryStorage();
        $storage->expects($this->any())->method('getSessionInformation')->with('foobar')->will($this->returnValue($sessionInformation));
        $storage->expects($this->once())->method('saveSessionInformation')->with($this->identicalTo($sessionInformation));
        $registry = $this->getSessionRegistry($storage);
        $registry->expireNow('foobar');
    }

    public function testRegisterNewSession()
    {
        $storage = $this->getSessionRegistryStorage();
        $storage->expects($this->once())->method('saveSessionInformation')->with($this->isInstanceOf('Ajgl\Security\Http\Session\Registry\SessionInformation'));
        $registry = $this->getSessionRegistry($storage);
        $registry->registerNewSession('foo', 'bar', time());
    }

    public function testRemoveSessionInformation()
    {
        $storage = $this->getSessionRegistryStorage();
        $storage->expects($this->once())->method('removeSessionInformation')->with('foobar');
        $registry = $this->getSessionRegistry($storage);
        $registry->removeSessionInformation('foobar');
    }

    public function testCollectGarbage()
    {
        $storage = $this->getSessionRegistryStorage();
        $storage->expects($this->once())->method('collectGarbage')->with(ini_get('session.gc_maxlifetime'));
        $registry = $this->getSessionRegistry($storage);
        $registry->collectGarbage();
    }

    private function getSessionRegistryStorage()
    {
        return $this->getMock('Ajgl\Security\Http\Session\Registry\SessionRegistryStorageInterface');
    }

    private function getSessionInformation()
    {
        return $this->getMockBuilder('Ajgl\Security\Http\Session\Registry\SessionInformation')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getSessionRegistry($storage)
    {
        return new SessionRegistry($storage);
    }
}
