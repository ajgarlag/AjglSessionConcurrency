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
 * SessionRegistryStorageInterface.
 *
 * Stores the SessionInformation instances maintained in the SessionRegistry.
 *
 * @author Stefan Paschke <stefan.paschke@gmail.com>
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
interface SessionRegistryStorageInterface
{
    /**
     * Obtains the session information for the specified sessionId.
     *
     * @param  string                  $sessionId the session identifier key.
     * @return SessionInformation|null $sessionInformation
     */
    public function getSessionInformation($sessionId);

    /**
     * Obtains the maintained information for one user ordered from newest to
     *  oldest
     *
     * @param  string               $username               The user identifier.
     * @param  bool                 $includeExpiredSessions
     * @return SessionInformation[] An array of SessionInformation objects.
     */
    public function getSessionInformations($username, $includeExpiredSessions = false);

    /**
     * Saves a SessionInformation object.
     *
     * @param SessionInformation $sessionInformation
     */
    public function saveSessionInformation(SessionInformation $sessionInformation);

    /**
     * Deletes the maintained information of one session.
     *
     * @param string $sessionId the session identifier key.
     */
    public function removeSessionInformation($sessionId);

    /**
     * Removes sessions information which last used timestamp is older
     * than the given lifetime
     *
     * @param int $maxLifetime
     */
    public function collectGarbage($maxLifetime);
}
