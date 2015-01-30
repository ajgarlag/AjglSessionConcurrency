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

/**
 * SessionInformation.
 *
 * Represents a record of a session. This is primarily used for concurrent session support.
 *
 * @author Stefan Paschke <stefan.paschke@gmail.com>
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionInformation
{
    private $sessionId;
    private $username;
    private $expired;
    private $lastUsed;

    /**
     * Class constructor
     *
     * @param string   $sessionId
     * @param string   $username
     * @param int      $lastUsed
     * @param int|null $expired
     */
    public function __construct($sessionId, $username, $lastUsed, $expired = null)
    {
        $this->setSessionId($sessionId);
        $this->setUsername($username);
        $this->setLastUsed($lastUsed);

        if (null !== $expired) {
            $this->setExpired($expired);
        }
    }

    /**
     * Marks the session as expired.
     */
    public function expireNow()
    {
        $this->setExpired(time());
    }

    /**
     * Returns the last request timestamp.
     *
     * @return int the last request timestamp.
     */
    public function getLastUsed()
    {
        return $this->lastUsed;
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the session identifier key.
     *
     * @return string the session identifier key.
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Returns whether this session is expired or not.
     *
     * @return bool
     */
    public function isExpired()
    {
        return null !== $this->getExpired() && $this->getExpired() < microtime(true);
    }

    /**
     * Set the last request date timestamp.
     *
     * It will set the current timestamp if no one is given
     *
     * @param int|null $lastUsed
     */
    public function refreshLastUsed($lastUsed = null)
    {
        $this->setLastUsed($lastUsed?:time());
    }

    private function getExpired()
    {
        return $this->expired;
    }

    private function setExpired( $expired)
    {
        $this->expired = (int) $expired;
    }

    private function setLastUsed( $lastUsed)
    {
        $this->lastUsed = (int) $lastUsed;
    }

    private function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    private function setUsername($username)
    {
        $this->username = $username;
    }
}
