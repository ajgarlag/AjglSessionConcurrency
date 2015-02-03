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
 * Stores session registry information to a DB using PDO
 *
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class PdoSessionRegistryStorage implements SessionRegistryStorageInterface
{
    /**
     * @var \PDO|null PDO instance or null when not connected yet
     */
    private $pdo;

    /**
     * @var string|null|false DSN string or null for session.save_path or false when lazy connection disabled
     */
    private $dsn = false;

    /**
     * @var string Database driver
     */
    private $driver;

    /**
     * @var string Table name
     */
    private $table = 'sessions_registry';

    /**
     * @var string Column for session id
     */
    private $idCol = 'sess_id';

    /**
     * @var string Column for session username
     */
    private $usernameCol = 'sess_username';

    /**
     * @var string Column for last used timestamp
     */
    private $lastUsedCol = 'sess_last_used';

    /**
     * @var string Column for expired timestamp
     */
    private $expiredCol = 'sess_expired';

    /**
     * @var string Username when lazy-connect
     */
    private $username = '';

    /**
     * @var string Password when lazy-connect
     */
    private $password = '';

    /**
     * @var array Connection options when lazy-connect
     */
    private $connectionOptions = array();

    /**
     * Constructor.
     *
     * You can either pass an existing database connection as PDO instance or
     * pass a DSN string that will be used to lazy-connect to the database
     * when the session is actually used.
     *
     * List of available options:
     *  * db_table: The name of the table [default: sessions_registry]
     *  * db_id_col: The column where to store the session id [default: sess_id]
     *  * db_username_col: The column where to store the session username [default: sess_username]
     *  * db_last_used_col: The column where to store the last used timestamp [default: sess_last_used]
     *  * db_expired_col: The column where to store the expired timestamp [default: sess_expired]
     *  * db_username: The username when lazy-connect [default: '']
     *  * db_password: The password when lazy-connect [default: '']
     *  * db_connection_options: An array of driver-specific connection options [default: array()]
     *
     * @param \PDO|string $pdoOrDsn A \PDO instance or DSN string
     * @param array       $options  An associative array of options
     *
     * @throws \InvalidArgumentException When PDO error mode is not PDO::ERRMODE_EXCEPTION
     */
    public function __construct($pdoOrDsn, array $options = array())
    {
        if ($pdoOrDsn instanceof \PDO) {
            if (\PDO::ERRMODE_EXCEPTION !== $pdoOrDsn->getAttribute(\PDO::ATTR_ERRMODE)) {
                throw new \InvalidArgumentException(sprintf('"%s" requires PDO error mode attribute be set to throw Exceptions (i.e. $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION))', __CLASS__));
            }

            $this->pdo = $pdoOrDsn;
            $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } else {
            $this->dsn = $pdoOrDsn;
        }

        $this->table = isset($options['db_table']) ? $options['db_table'] : $this->table;
        $this->idCol = isset($options['db_id_col']) ? $options['db_id_col'] : $this->idCol;
        $this->usernameCol = isset($options['db_username_col']) ? $options['db_username_col'] : $this->usernameCol;
        $this->lastUsedCol = isset($options['db_last_used_col']) ? $options['db_last_used_col'] : $this->lastUsedCol;
        $this->expiredCol = isset($options['db_expired_col']) ? $options['db_expired_col'] : $this->expiredCol;
        $this->username = isset($options['db_username']) ? $options['db_username'] : $this->username;
        $this->password = isset($options['db_password']) ? $options['db_password'] : $this->password;
        $this->connectionOptions = isset($options['db_connection_options']) ? $options['db_connection_options'] : $this->connectionOptions;
    }

    /**
     * Creates the table to store sessions which can be called once for setup.
     *
     * Session ID is saved in a column of maximum length 128 because that is enough even
     * for a 512 bit configured session.hash_function like Whirlpool. Session username is
     * saved in a varchar of maximun length 256 because any valid email should fit into it.
     * One could also use a larger column if one was sure the data dfits into it.
     *
     * @throws \RuntimeException When the table cannot be created
     * @throws \DomainException  When an unsupported PDO driver is used
     */
    public function createTable()
    {
        // connect if we are not yet
        $this->getConnection();

        switch ($this->driver) {
            case 'mysql':
                // We use varbinary for the ID column because it prevents unwanted conversions:
                // - character set conversions between server and client
                // - trailing space removal
                // - case-insensitivity
                // - language processing like é == e
                $sql = "CREATE TABLE $this->table ($this->idCol VARBINARY(128) NOT NULL PRIMARY KEY, $this->usernameCol VARBINARY(256) NOT NULL, $this->lastUsedCol INTEGER UNSIGNED NOT NULL, $this->expiredCol INTEGER UNSIGNED) COLLATE utf8_bin, ENGINE = InnoDB";
                break;
            case 'sqlite':
                $sql = "CREATE TABLE $this->table ($this->idCol TEXT NOT NULL PRIMARY KEY, $this->usernameCol TEXT NOT NULL, $this->lastUsedCol INTEGER NOT NULL, $this->expiredCol INTEGER)";
                break;
            case 'pgsql':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR(128) NOT NULL PRIMARY KEY, $this->usernameCol VARCHAR(256) NOT NULL, $this->lastUsedCol INTEGER NOT NULL, $this->expiredCol INTEGER)";
                break;
            case 'oci':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR2(128) NOT NULL PRIMARY KEY, $this->usernameCol VARCHAR(256) NOT NULL, $this->lastUsedCol INTEGER NOT NULL, $this->expiredCol INTEGER)";
                break;
            case 'sqlsrv':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR(128) NOT NULL PRIMARY KEY, $this->usernameCol VARCHAR(256) NOT NULL, $this->lastUsedCol INTEGER NOT NULL, $this->expiredCol INTEGER)";
                break;
            default:
                throw new \DomainException(sprintf('Creating the session table is currently not implemented for PDO driver "%s".', $this->driver));
        }

        try {
            $this->getConnection()->exec($sql);
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to create sessions info table: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collectGarbage($maxLifetime)
    {
        // delete the session records that have expired
        $sql = "DELETE FROM $this->table WHERE $this->lastUsedCol < :time";

        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':time', time() - $maxLifetime, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to delete expired sessions info: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionInformation($sessionId)
    {
        $sql = "SELECT $this->usernameCol, $this->lastUsedCol, $this->expiredCol FROM $this->table WHERE $this->idCol = :id";

        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->execute();

            // We use fetchAll instead of fetchColumn to make sure the DB cursor gets closed
            $sessionRows = $stmt->fetchAll(\PDO::FETCH_NUM);

            if ($sessionRows) {
                return new SessionInformation($sessionId, $sessionRows[0][0], $sessionRows[0][1], $sessionRows[0][2]);
            }
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to read the session info: %s', $e->getMessage()), 0, $e);
        }
    }

    public function getSessionInformations($username, $includeExpiredSessions = false)
    {
        $includeExpiredSessionsCondition = $includeExpiredSessions ? '' : "AND $this->expiredCol IS NULL OR $this->expiredCol > ".time();
        $sql = "SELECT $this->idCol, $this->lastUsedCol, $this->expiredCol FROM $this->table WHERE $this->usernameCol = :username $includeExpiredSessionsCondition ORDER BY $this->lastUsedCol DESC";

        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindParam(':username', $username, \PDO::PARAM_STR);
            $stmt->execute();

            $sessionInformations = array();
            // We use fetchAll instead of fetchColumn to make sure the DB cursor gets closed
            $sessionRows = $stmt->fetchAll(\PDO::FETCH_NUM);

            foreach ($sessionRows as $sessionRow) {
                $sessionInformations[] = new SessionInformation($sessionRow[0], $username, $sessionRow[1], $sessionRow[2]);
            }

            return $sessionInformations;
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to read the session info: %s', $e->getMessage()), 0, $e);
        }
    }

    public function removeSessionInformation($sessionId)
    {
        // delete the record associated with this id
        $sql = "DELETE FROM $this->table WHERE $this->idCol = :id";

        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to delete a session info: %s', $e->getMessage()), 0, $e);
        }
    }

    public function saveSessionInformation(SessionInformation $sessionInformation)
    {
        try {
            // We use a single MERGE SQL query when supported by the database.
            $mergeSql = $this->getMergeSql();

            if (null !== $mergeSql) {
                $mergeStmt = $this->getConnection()->prepare($mergeSql);
                $mergeStmt->bindValue(':id', $sessionInformation->getSessionId(), \PDO::PARAM_STR);
                $mergeStmt->bindValue(':username', $sessionInformation->getUsername(), \PDO::PARAM_STR);
                $mergeStmt->bindValue(':last_used', $sessionInformation->getLastUsed(), \PDO::PARAM_INT);
                $mergeStmt->bindValue(':expired', $sessionInformation->getExpired(), \PDO::PARAM_INT);
                $mergeStmt->execute();

                return true;
            }

            $updateStmt = $this->getConnection()->prepare(
                "UPDATE $this->table SET $this->usernameCol = :username, $this->lastUsedCol = :last_used, $this->expiredCol = :expired WHERE $this->idCol = :id"
            );
            $updateStmt->bindValue(':id', $sessionInformation->getSessionId(), \PDO::PARAM_STR);
            $updateStmt->bindValue(':username', $sessionInformation->getUsername(), \PDO::PARAM_STR);
            $updateStmt->bindValue(':last_used', $sessionInformation->getLastUsed(), \PDO::PARAM_INT);
            $updateStmt->bindValue(':expired', $sessionInformation->getExpired(), \PDO::PARAM_INT);
            $updateStmt->execute();

            // When MERGE is not supported, like in Postgres, we have to use this approach that can result in
            // duplicate key errors when the same session is written simultaneously. We can just catch such an
            // error and re-execute the update. This is similar to a serializable transaction with retry logic
            // on serialization failures but without the overhead and without possible false positives due to
            // longer gap locking.
            if (!$updateStmt->rowCount()) {
                try {
                    $insertStmt = $this->getConnection()->prepare(
                        "INSERT INTO $this->table ($this->idCol, $this->usernameCol, $this->lastUsedCol, $this->expiredCol) VALUES (:id, :username, :last_used, :expired)"
                    );
                    $insertStmt->bindValue(':id', $sessionInformation->getSessionId(), \PDO::PARAM_STR);
                    $insertStmt->bindValue(':username', $sessionInformation->getUsername(), \PDO::PARAM_STR);
                    $insertStmt->bindValue(':last_used', $sessionInformation->getLastUsed(), \PDO::PARAM_INT);
                    $insertStmt->bindValue(':expired', $sessionInformation->getExpired(), \PDO::PARAM_INT);
                    $insertStmt->execute();
                } catch (\PDOException $e) {
                    // Handle integrity violation SQLSTATE 23000 (or a subclass like 23505 in Postgres) for duplicate keys
                    if (0 === strpos($e->getCode(), '23')) {
                        $updateStmt->execute();
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (\PDOException $e) {
            throw new \RuntimeException(sprintf('PDOException was thrown when trying to write the session info: %s', $e->getMessage()), 0, $e);
        }
    }

    /**
     * Returns a merge/upsert (i.e. insert or update) SQL query when supported by the database.
     *
     * @return string|null The SQL string or null when not supported
     */
    private function getMergeSql()
    {
        // connect if we are not yet
        $this->getConnection();

        switch ($this->driver) {
            case 'mysql':
                return "INSERT INTO $this->table ($this->idCol, $this->usernameCol, $this->lastUsedCol, $this->expiredCol) VALUES (:id, :username, :last_used, :expired) ".
                "ON DUPLICATE KEY UPDATE $this->usernameCol = VALUES($this->usernameCol), $this->lastUsedCol = VALUES($this->lastUsedCol), $this->expiredCol = VALUES($this->expiredCol)";
            case 'oci':
                // DUAL is Oracle specific dummy table
                return "MERGE INTO $this->table USING DUAL ON ($this->idCol = :id) ".
                "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->usernameCol, $this->lastUsedCol, $this->expiredCol) VALUES (:id, :username, :last_used, :expired) ".
                "WHEN MATCHED THEN UPDATE SET $this->usernameCol = :username, $this->lastUsedCol = :last_used, $this->expiredCol = :expired";
            case 'sqlsrv' === $this->driver && version_compare($this->getConnection()->getAttribute(\PDO::ATTR_SERVER_VERSION), '10', '>='):
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                return "MERGE INTO $this->table WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ($this->idCol = :id) ".
                "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->usernameCol, $this->lastUsedCol, $this->expiredCol) VALUES (:id, :username, :last_used, :expired) ".
                "WHEN MATCHED THEN UPDATE SET $this->usernameCol = :username, $this->lastUsedCol = :last_used, $this->expiredCol = :expired;";
            case 'sqlite':
                return "INSERT OR REPLACE INTO $this->table ($this->idCol, $this->usernameCol, $this->lastUsedCol, $this->expiredCol) VALUES (:id, :username, :last_used, :expired)";
        }
    }

    /**
     * Lazy-connects to the database.
     *
     * @param string $dsn DSN string
     */
    private function connect($dsn)
    {
        $this->pdo = new \PDO($dsn, $this->username, $this->password, $this->connectionOptions);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Return a PDO instance
     *
     * @return \PDO
     */
    protected function getConnection()
    {
        if (null === $this->pdo) {
            $this->connect($this->dsn);
        }

        return $this->pdo;
    }
}
