<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Firewall;

use Ajgl\Security\Http\Session\Registry\SessionRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * SessionExpirationListener controls idle and expired sessions
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionRegistryExpirationListener extends SessionExpirationListener
{
    private $registry;

    public function __construct(SessionRegistry $registry, SecurityContextInterface $securityContext, HttpUtils $httpUtils, $maxIdleTime, $targetUrl = null, LoggerInterface $logger = null)
    {
        parent::__construct($securityContext, $httpUtils, $maxIdleTime, $targetUrl, $logger);
        $this->registry = $registry;
    }

    protected function hasSessionExpired(SessionInterface $session)
    {
        $sessionInformation = $this->registry->getSessionInformation($session->getId());
        if (null !== $sessionInformation && $sessionInformation->isExpired()) {
            return true;
        }

        return parent::hasSessionExpired($session);
    }

    protected function removeSessionData(SessionInterface $session)
    {
        $this->registry->removeSessionInformation($session->getId());
        parent::removeSessionData($session);
    }
}
