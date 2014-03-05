<?php

class Factory_Model {

    protected static $_db = null;
    protected static $_memcached = null;

    private function __construct() {}

    public static function getDb() {
        if (!static::$_db) {
            try {
                static::$_db = Zend_Db::factory(Zend_Registry::get('config')->database);
                static::$_db->setFetchMode(Zend_Db::FETCH_OBJ);
                static::$_db->getConnection();
                Zend_Db_Table::setDefaultAdapter(static::$_db);
            } catch (Zend_Db_Adapter_Exception $e) {
                die('Failed login credential');
            } catch (Zend_Exception $e) {
                die('factory() failed to load the specified Adapter class');
            }
        }
        return static::$_db;
    }

    protected static function fetch($sql, array $params, array $dataTypes, $cacheTime, $cacheKey, $all = true) {
        // Check $params and $dataTypes match up
        $diff = array_diff(array_keys($params), array_keys($dataTypes));
        if (!empty($diff)) {
            die('Error in SQL - Array Keys for $params and $dataTypes do not match for SQL : <br />' . $sql);
        }
        // Check cache for existing query
        $memcached = Zend_Registry::get('cache');
        $cached = (!empty($cacheKey) && $cacheKey != null ? $memcached->load($cacheKey) : false);
        if ($cached === false || $cacheTime === 0) {
            // Prepare query, bind params, execute and return results
            $db = self::getDb();
            $stmt = new Zend_Db_Statement_Pdo($db, $sql);
            $stmt->setFetchMode(Zend_Db::FETCH_OBJ);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, $dataTypes[$key]);
            }
            $stmt->execute();
            $results = ($all == true ? $stmt->fetchAll() : $stmt->fetch());
            if (!empty($cacheKey) && $cacheKey != null) {
                $memcached->save($results, $cacheKey, array(), ($cacheTime === false ? 3600 : $cacheTime));
            }
            return $results;
        } else {
            return $cached;
        }
    }

    protected static function execute($sql, array $params, array $dataTypes) {
        // Check $params and $dataTypes match up
        $diff = array_diff(array_keys($params), array_keys($dataTypes));
        if (!empty($diff)) {
            die('Error in SQL - Array Keys for $params and $dataTypes do not match for SQL : <br />' . $sql);
        }
        $db = self::getDb();
        $stmt = new Zend_Db_Statement_Pdo($db, $sql);
        $stmt->setFetchMode(Zend_Db::FETCH_OBJ);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $dataTypes[$key]);
        }
        $stmt->execute();
        return ($db->lastInsertId() > 0 ? $db->lastInsertId() : $stmt->rowCount());
    }

    protected static function deleteFromCache($cacheKey) {
        $memcached = Zend_Registry::get('cache');
        if (is_array($cacheKey)) {
            foreach ($cacheKey as $key) {
                $memcached->remove($memcached->stripId($key));
            }
        } else {
            $memcached->remove($memcached->stripId($cacheKey));
        }
    }

    protected static function getMemcached() {
        if (!static::$_memcached) {
            static::$_memcached = Zend_Registry::get('cache')->getBackend();
        }
        return static::$_memcached;
    }

}