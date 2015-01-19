<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Logout;

use Ajgl\Security\Http\Session\Registry\SessionRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler as BaseHandler;

/**
 * Handler for clearing invalidating the current session and removing session
 * information from registry.
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class SessionLogoutHandler extends BaseHandler
{
    private $registry;

    public function __construct(SessionRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->registry->removeSessionInformation($request->getSession()->getId());
        parent::logout($request, $response, $token);
    }
}
