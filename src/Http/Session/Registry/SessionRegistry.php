<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Session\Registry;

use Ajgl\Security\Http\Session\Registry\Storage\SessionRegistryStorageInterface;

/**
 * SessionRegistry.
 *
 * Stores a registry of SessionInformation instances.
 *
 * @author Stefan Paschke <stefan.paschke@gmail.com>
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionRegistry
{
    private $sessionRegistryStorage;

    public function __construct(SessionRegistryStorageInterface $sessionRegistryStorage)
    {
        $this->sessionRegistryStorage = $sessionRegistryStorage;
    }

    /**
     * Returns all the sessions stored for the given user ordered from newest to oldest.
     *
     * @param string $username               the given user.
     * @param bool   $includeExpiredSessions
     *
     * @return SessionInformation[] An array of SessionInformation objects.
     */
    public function getAllSessions($username, $includeExpiredSessions = false)
    {
        return $this->sessionRegistryStorage->getSessionInformations($username, $includeExpiredSessions);
    }

    /**
     * Returns the session information for the given sessionId.
     *
     * @param string $sessionId the session identifier key.
     *
     * @return SessionInformation|null $sessionInformation
     */
    public function getSessionInformation($sessionId)
    {
        return $this->sessionRegistryStorage->getSessionInformation($sessionId);
    }

    /**
     * Updates the given sessionId so its last request time is equal to the present date and time.
     *
     * @param string   $sessionId the session identifier key.
     * @param int|null $lastUsed  the last request timestamp
     */
    public function refreshLastUsed($sessionId, $lastUsed = null)
    {
        if ($sessionInformation = $this->getSessionInformation($sessionId)) {
            if ($sessionInformation->getLastUsed() !== $lastUsed) {
                $sessionInformation->refreshLastUsed($lastUsed);
                $this->saveSessionInformation($sessionInformation);
            }
        }
    }

    /**
     * Expires the given sessionId.
     *
     * @param string $sessionId the session identifier key.
     */
    public function expireNow($sessionId)
    {
        if ($sessionInformation = $this->getSessionInformation($sessionId)) {
            $sessionInformation->expireNow();
            $this->saveSessionInformation($sessionInformation);
        }
    }

    /**
     * Registers a new session for the given user.
     *
     * @param string $sessionId the session identifier key.
     * @param string $username  the given user.
     * @param int    $lastUsed
     */
    public function registerNewSession($sessionId, $username, $lastUsed = null)
    {
        $sessionInformation = new SessionInformation($sessionId, $username, $lastUsed ?: time());

        $this->saveSessionInformation($sessionInformation);
    }

    /**
     * Deletes the stored information of one session.
     *
     * @param string $sessionId the session identifier key.
     */
    public function removeSessionInformation($sessionId)
    {
        $this->sessionRegistryStorage->removeSessionInformation($sessionId);
    }

    /**
     * Removes sessions information which last used timestamp is older
     * than the given lifetime.
     *
     * @param int $maxLifetime
     */
    public function collectGarbage($maxLifetime = null)
    {
        $maxLifetime = $maxLifetime ?: ini_get('session.gc_maxlifetime');
        $this->sessionRegistryStorage->collectGarbage($maxLifetime);
    }

    private function saveSessionInformation(SessionInformation $sessionInformation)
    {
        $this->sessionRegistryStorage->saveSessionInformation($sessionInformation);
    }
}
