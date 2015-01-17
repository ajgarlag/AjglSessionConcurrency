<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Session;

use Ajgl\Security\Http\Session\Registry\SessionRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

/**
 * Strategy used to register a user with the SessionRegistry after
 * successful authentication.
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class RegisterSessionAuthenticationStrategy implements SessionAuthenticationStrategyInterface
{
    /**
     * @var SessionRegistry
     */
    private $registry;

    public function __construct(SessionRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthentication(Request $request, TokenInterface $token)
    {
        if (null !== $session = $request->getSession()) {
            $this->registry->registerNewSession($session->getId(), $token->getUsername());
        }
    }
}
