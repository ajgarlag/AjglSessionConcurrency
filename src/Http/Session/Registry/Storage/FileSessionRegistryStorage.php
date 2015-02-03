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
 * Session registry to filesystem storage
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class FileSessionRegistryStorage implements SessionRegistryStorageInterface
{
    private $savePath;

    /**
     * @param string $savePath
     */
    public function __construct($savePath = null)
    {
        if (!file_exists($savePath) && false === mkdir($savePath)) {
            throw new \RuntimeException('Cannot create the given directory.');
        }

        if (!is_dir($savePath)) {
            throw new \InvalidArgumentException('The given path must be a directory.');
        }

        if (!is_writable($savePath)) {
            throw new \InvalidArgumentException('The given path must be writable.');
        }

        $this->savePath = $savePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionInformation($sessionId)
    {
        if ($filename = $this->getSessionFilename($sessionId)) {
            return $this->fileToSessionInfo($filename);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionInformations($username, $includeExpiredSessions = false)
    {
        $result = array();

        foreach (glob($this->getUsernameFilePattern($username)) as $filename) {
            $sessionInfo = $this->fileToSessionInfo($filename);
            if ($includeExpiredSessions || !$sessionInfo->isExpired()) {
                $result[] = $sessionInfo;
            }
        }

        usort(
            $result,
            function (SessionInformation $a, SessionInformation $b) {
                return $a->getLastUsed() == $b->getLastUsed() ? 0 : $a->getLastUsed()>$b->getLastUsed() ? -1 : 1;
            }
        );

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function saveSessionInformation(SessionInformation $sessionInformation)
    {
        $filename = $this->getFilePath($sessionInformation);
        file_put_contents($filename, serialize($sessionInformation));
        /*
         * Set the file mtime to last used
         */
        touch($filename, $sessionInformation->getLastUsed());
    }

    /**
     * {@inheritdoc}
     */
    public function removeSessionInformation($sessionId)
    {
        if ($filename = $this->getSessionFilename($sessionId)) {
            unlink($filename);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collectGarbage($maxLifetime)
    {
        $now = time();
        foreach (glob($this->getSessionFilePattern('*')) as $filename) {
            /*
             * Check the file mtime like the native file-based session handler
             * @see http://php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime
             */
            if ($now - filemtime($filename) > $maxLifetime) {
                $sessionInfo = $this->fileToSessionInfo($filename);
                if ($now - $sessionInfo->getLastUsed() > $maxLifetime) {
                    unlink($filename);
                } else {
                    /*
                     * If the file mtime is expired, but the session info is not expired, fix file mtime
                     */
                    touch($filename, $sessionInfo->getLastUsed());
                }
            }
        }
    }

    private function getSessionFilename($sessionId)
    {
        $filenames = glob($this->getSessionFilePattern($sessionId));
        if (count($filenames) > 1) {
            throw new \UnexpectedValueException(sprintf("'%s' files found for session ID '%s'.", count($filenames), $sessionId));
        }

        return count($filenames) == 0 ? null : reset($filenames);
    }

    private function getFilePath(SessionInformation $sessionInformation)
    {
        return $this->savePath.'/'.$sessionInformation->getSessionId().'~'.$sessionInformation->getUsername().'.sessinfo';
    }

    private function getSessionFilePattern($sessionId)
    {
        return $this->savePath.'/'.$sessionId.'~*.sessinfo';
    }

    private function getUsernameFilePattern($username)
    {
        return $this->savePath.'/*~'.$username.'.sessinfo';
    }

    private function fileToSessionInfo($filename)
    {
        return unserialize(file_get_contents($filename));
    }
}
