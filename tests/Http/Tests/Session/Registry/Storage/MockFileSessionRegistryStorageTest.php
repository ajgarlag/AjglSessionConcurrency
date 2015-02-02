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

use Ajgl\Security\Http\Session\Registry\Storage\MockFileSessionRegistryStorage;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class MockFileSessionRegistryStorageTest extends AbstractSessionRegistryStorageTest
{
    private $dir;

    protected function setUp()
    {
        $this->dir = sys_get_temp_dir().'/'.uniqid('mock_storage_');
        $this->cleanDir($this->dir);
        mkdir($this->dir);

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->cleanDir($this->dir);

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
        return new MockFileSessionRegistryStorage($this->dir);
    }
}
