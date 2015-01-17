<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Core\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * MaxSessionsExceededException is thrown when the allowed number of sessions
 * has been exceeded.
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class MaxSessionsExceededException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Allowed number of concurrent sessions exceeded.';
    }
}
