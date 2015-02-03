<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Tests\Session\Registry\Storage;

use Ajgl\Security\Http\Session\Registry\SessionInformation;
use Ajgl\Security\Http\Session\Registry\Storage\SessionRegistryStorageInterface;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
abstract class AbstractSessionRegistryStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SessionRegistryStorageInterface
     */
    protected $storage;

    /**
     * @return SessionRegistryStorageInterface
     */
    abstract protected function buildSessionRegistryStorage();

    protected function setUp()
    {
        parent::setUp();
        $this->storage = $this->buildSessionRegistryStorage();
    }

    public function testGetSessionInformationReturnsNullIfNotFound()
    {
        $this->assertNull($this->storage->getSessionInformation('foo'));
    }

    public function testSaveSessionInformationAddingNewSession()
    {
        $this->assertNull($this->storage->getSessionInformation('foo'));
        $this->storage->saveSessionInformation(new SessionInformation('foo', 'bar', time()));
        $this->assertInstanceOf(
            'Ajgl\Security\Http\Session\Registry\SessionInformation',
            $this->storage->getSessionInformation('foo')
        );
    }

    public function testSaveSessionInformationUpdatingExistingSession()
    {
        $now = time();
        $past = $now-3600;
        $this->storage->saveSessionInformation(new SessionInformation('foo', 'bar', $past));
        $this->storage->saveSessionInformation(new SessionInformation('foo', 'bar', $now));
        $this->assertEquals($now, $this->storage->getSessionInformation('foo')->getLastUsed());
    }

    public function testRemoveSessionInformationForNonExistingSession()
    {
        $this->storage->removeSessionInformation(uniqid());
    }

    public function testRemoveSessionInformation()
    {
        $this->storage->saveSessionInformation(new SessionInformation('foo', 'bar', time()));
        $this->assertInstanceOf(
            'Ajgl\Security\Http\Session\Registry\SessionInformation',
            $this->storage->getSessionInformation('foo')
        );
        $this->storage->removeSessionInformation('foo');
        $this->assertNull($this->storage->getSessionInformation('foo'));
    }

    public function testGetSessionInformationsReturnsEmptyIfNotFound()
    {
        $result = $this->storage->getSessionInformations('bar');
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testGetSessionInformationsWithoutExpiredSessions()
    {
        $this->loadFixtureSessions();
        $result = $this->storage->getSessionInformations('bar');
        $this->assertCount(2, $result);
        $this->assertGreaterThan(
            $result[1]->getLastUsed(),
            $result[0]->getLastUsed()
        );
        foreach ($result as $sessionInformation) {
            $this->assertFalse($sessionInformation->isExpired());
        }
    }

    public function testGetSessionInformationsWithExpiredSessions()
    {
        $this->loadFixtureSessions();
        $result = $this->storage->getSessionInformations('bar', true);
        $this->assertCount(3, $result);

        foreach ($result as $sessionInformation) {
            if (!isset($previous)) {
                $previous = $sessionInformation;
            } else {
                $this->assertGreaterThan(
                    $sessionInformation->getLastUsed(),
                    $previous->getLastUsed()
                );
            }
        }

        $expired = array_pop($result);
        $this->assertTrue($expired->isExpired());
        foreach ($result as $sessionInformation) {
            $this->assertFalse($sessionInformation->isExpired());
        }
    }

    public function testCollectGarbage()
    {
        $this->loadFixtureSessions();
        $this->assertCount(3, $this->storage->getSessionInformations('bar', true));
        $this->storage->collectGarbage(900);
        $result = $this->storage->getSessionInformations('bar', true);
        $this->assertCount(1, $result);
        $this->assertEquals('qux', $result[0]->getSessionId());
    }

    protected function loadFixtureSessions()
    {
        $expiredSessionInformation = new SessionInformation('foo', 'bar', time()-3600);
        $expiredSessionInformation->expireNow();
        $this->storage->saveSessionInformation($expiredSessionInformation);
        $this->storage->saveSessionInformation(new SessionInformation('baz', 'bar', time()-1800));
        $this->storage->saveSessionInformation(new SessionInformation('qux', 'bar', time()));
        $this->storage->saveSessionInformation(new SessionInformation('barfoo', 'foobar', time()));
    }
}
