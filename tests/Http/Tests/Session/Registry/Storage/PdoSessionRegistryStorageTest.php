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

use Ajgl\Security\Http\Session\Registry\Storage\PdoSessionRegistryStorage;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class PdoSessionRegistryStorageTest extends AbstractSessionRegistryStorageTest
{
    protected function buildSessionRegistryStorage()
    {
        $storage = new PdoSessionRegistryStorage('sqlite::memory:');
        $storage->createTable();

        return $storage;
    }
}
