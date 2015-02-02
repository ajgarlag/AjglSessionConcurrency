<?php

/*
 * This file is part of the AJGL packages
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Security\Http\Session\Registry\Storage;

use Ajgl\Security\Http\Session\Registry\SessionInformation;

/**
 * MockFileSessionRegistryStorage.
 *
 * Session registry storage implementation for testing purpose
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class MockFileSessionRegistryStorage implements SessionRegistryStorageInterface
{
    private $savePath;

    /**
     * @param string $savePath
     */
    public function __construct($savePath = null)
    {
        if (null === $savePath) {
            $savePath = sys_get_temp_dir();
        }

        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }

        $this->savePath = $savePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionInformation($sessionId)
    {
        $filename = $this->getFilePath($sessionId);
        if (file_exists($filename)) {
            return $this->fileToSessionInfo($filename);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionInformations($username, $includeExpiredSessions = false)
    {
        $result = array();

        foreach (glob($this->getFilePath('*')) as $filename) {
            $sessionInfo = $this->fileToSessionInfo($filename);
            if ($sessionInfo->getUsername() == $username && ($includeExpiredSessions || !$sessionInfo->isExpired())) {
                $result[] = $sessionInfo;
            }
        }

        usort($result, function($a, $b){return $a->getLastUsed()==$b->getLastUsed()?0:$a->getLastUsed()>$b->getLastUsed()?-1:1;});

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function saveSessionInformation(SessionInformation $sessionInformation)
    {
        file_put_contents($this->getFilePath($sessionInformation->getSessionId()), serialize($sessionInformation));
    }

    /**
     * {@inheritdoc}
     */
    public function removeSessionInformation($sessionId)
    {
        $filename = $this->getFilePath($sessionId);

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collectGarbage($maxLifetime)
    {
        $now = time();
        foreach (glob($this->getFilePath('*')) as $filename) {
            $sessionInfo = $this->fileToSessionInfo($filename);
            if ($now - $sessionInfo->getLastUsed() > $maxLifetime) {
                $this->removeSessionInformation($sessionInfo->getSessionId());
            }
        }
    }

    private function getFilePath($sessionId)
    {
        return $this->savePath.'/'.$sessionId.'.mocksessinfo';
    }

    private function fileToSessionInfo($filename)
    {
        return unserialize(file_get_contents($filename));
    }
}
