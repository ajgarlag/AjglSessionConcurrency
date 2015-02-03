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
    private $pdo;

    protected function setUp()
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        parent::setUp();
    }

    protected function tearDown()
    {
        $this->pdo = null;

        parent::tearDown();
    }

    protected function cleanDir($dir)
    {
        $fs = new Filesystem();
        if ($fs->exists($dir)) {
            $fs->remove($dir);
        }
    }

    protected function buildSessionRegistryStorage()
    {
        $storage = new PdoSessionRegistryStorage($this->pdo);
        $storage->createTable();

        return $storage;
    }
}
