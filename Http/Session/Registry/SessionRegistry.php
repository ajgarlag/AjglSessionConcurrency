<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) 2010-2015 Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Session\Registry;

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
     * @param  string               $username               the given user.
     * @param  bool                 $includeExpiredSessions
     * @return SessionInformation[] An array of SessionInformation objects.
     */
    public function getAllSessions($username, $includeExpiredSessions = false)
    {
        return $this->sessionRegistryStorage->getSessionInformations($username, $includeExpiredSessions);
    }

    /**
     * Obtains the session information for the given sessionId.
     *
     * @param  string                  $sessionId the session identifier key.
     * @return SessionInformation|null $sessionInformation
     */
    public function getSessionInformation($sessionId)
    {
        return $this->sessionRegistryStorage->getSessionInformation($sessionId);
    }

    /**
     * Sets a SessionInformation object.
     *
     * @param SessionInformation $sessionInformation
     */
    private function saveSessionInformation(SessionInformation $sessionInformation)
    {
        $this->sessionRegistryStorage->saveSessionInformation($sessionInformation);
    }

    /**
     * Updates the given sessionId so its last request time is equal to the present date and time.
     *
     * @param string $sessionId the session identifier key.
     */
    public function refreshLastRequest($sessionId)
    {
        if ($sessionInformation = $this->getSessionInformation($sessionId)) {
            $sessionInformation->refreshLastRequest();
            $this->saveSessionInformation($sessionInformation);
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
     * @param string    $sessionId   the session identifier key.
     * @param string    $username    the given user.
     * @param \DateTime $lastRequest
     */
    public function registerNewSession($sessionId, $username, \DateTime $lastRequest = null)
    {
        $lastRequest = $lastRequest ?: new \DateTime();
        $sessionInformation = new SessionInformation($sessionId, $username, $lastRequest);

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
}
