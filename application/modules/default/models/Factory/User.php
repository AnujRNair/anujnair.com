<?php

class Factory_User extends Factory_Model {

    // User functions

    public static function getUserById($userId) {
        // User data
        $sql = "select
                    pk_user_id as userId,
                    username,
                    firstname,
                    lastname,
                    email,
                    website,
                    reg_ip as regIp,
                    datetime_reg_date as regDate,
                    last_login_ip as lastLoginIp,
                    datetime_last_login as lastLoginDate,
                    validated,
                    active
                from tb_user
                where pk_user_id = :id";
        $params = array(
            'id' => $userId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $user = parent::fetch($sql, $params, $dataTypes, 3600, 'user_id_' . $userId, false);
        return $user;
    }

    public static function getAllUsers() {
        $sql = "select
                    pk_user_id as userId
                from tb_user
                order by pk_user_id desc";
        $userIds = parent::fetch($sql, array(), array(), 3600, 'all_user_ids', true);
        $users = array();
        foreach ($userIds as $user) {
            $users[] = Factory_User::getUserById($user->userId);
        }
        return $users;
    }

    public static function editUser($userId, $username, $firstname, $lastname, $email, $website, $regIp,
        $regDate, $loginIp, $loginDate, $validated, $active) {
        $sql = "update tb_user
                set
                    username = :username,
                    firstname = :firstname,
                    lastname = :lastname,
                    email = :email,
                    website = :website,
                    reg_ip = :regIp,
                    datetime_reg_date = :regDate,
                    last_login_ip = :loginIp,
                    datetime_last_login = :loginDate,
                    validated = :validated,
                    active = :active
                where pk_user_id = :userId;
                select row_count() as rows";
        $params = array(
            'userId'    => $userId,
            'username'  => $username,
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $email,
            'website'   => $website,
            'regIp'     => ip2long($regIp),
            'regDate'   => $regDate,
            'loginIp'   => ip2long($loginIp),
            'loginDate' => $loginDate,
            'validated' => $validated,
            'active'    => $active
        );
        $dataTypes = array(
            'userId'    => Zend_Db::PARAM_INT,
            'username'  => Zend_Db::PARAM_STR,
            'firstname' => Zend_Db::PARAM_STR,
            'lastname'  => Zend_Db::PARAM_STR,
            'email'     => Zend_Db::PARAM_STR,
            'website'   => Zend_Db::PARAM_STR,
            'regIp'     => Zend_Db::PARAM_INT,
            'regDate'   => Zend_Db::PARAM_STR,
            'loginIp'   => Zend_Db::PARAM_INT,
            'loginDate' => Zend_Db::PARAM_STR,
            'validated' => Zend_Db::PARAM_BOOL,
            'active'    => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'user_id_' . $userId
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function setUserActiveStatus($userId, $active) {
        $sql = "update tb_user
                set
                    active = :active
                where pk_user_id = :userId;
                select row_count() as rows";
        $params = array(
            'userId' => $userId,
            'active' => $active
        );
        $dataTypes = array(
            'userId' => Zend_Db::PARAM_INT,
            'active' => Zend_DB::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'user_id_' . $userId
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    // Random Poster functions

    public static function getRandomPosterById($randomPosterId) {
        // Random Poster data
        $sql = "select
                    pk_random_poster_id as randomPosterId,
                    name,
                    email,
                    website,
                    ip,
                    useragent,
                    datetime_created as creationDate,
                    is_deleted as deleted
                from tb_random_poster
                where pk_random_poster_id = :id";
        $params = array(
            'id' => $randomPosterId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $user = parent::fetch($sql, $params, $dataTypes, 3600, 'randomposter_id_' . $randomPosterId, false);
        return $user;
    }

    public static function getAllRandomPosters() {
        $sql = "select
                    pk_random_poster_id as randomPosterId
                from tb_random_poster
                order by pk_random_poster_id desc";
        $userIds = parent::fetch($sql, array(), array(), 3600, 'all_randomposter_ids', true);
        $users = array();
        foreach ($userIds as $user) {
            $users[] = Factory_User::getRandomPosterById($user->randomPosterId);
        }
        return $users;
    }

    public static function editRandomPoster($randomPosterId, $name, $email, $website, $ip, $useragent, $creationDate, $deleted) {
        $sql = "update tb_random_poster
                set
                    name = :name,
                    email = :email,
                    website = :website,
                    ip = :ip,
                    useragent = :useragent,
                    datetime_created = :creationDate,
                    is_deleted = :deleted
                where pk_random_poster_id = :randomPosterId;
                select row_count() as rows";
        $params = array(
            'randomPosterId' => $randomPosterId,
            'name'           => $name,
            'email'          => $email,
            'website'        => $website,
            'ip'             => $ip,
            'useragent'      => $useragent,
            'creationDate'   => $creationDate,
            'deleted'        => $deleted
        );
        $dataTypes = array(
            'randomPosterId' => Zend_Db::PARAM_INT,
            'name'           => Zend_Db::PARAM_STR,
            'email'          => Zend_Db::PARAM_STR,
            'website'        => Zend_Db::PARAM_STR,
            'ip'             => Zend_Db::PARAM_INT,
            'useragent'      => Zend_Db::PARAM_STR,
            'creationDate'   => Zend_Db::PARAM_STR,
            'deleted'        => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'randomposter_id_' . $randomPosterId
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function setRandomPosterActiveStatus($randomPosterId, $deleted) {
        $sql = "update tb_random_poster
                set
                    is_deleted = :deleted
                where pk_random_poster_id = :randomPosterId;
                select row_count() as rows";
        $params = array(
            'randomPosterId' => $randomPosterId,
            'deleted'        => $deleted
        );
        $dataTypes = array(
            'randomPosterId' => Zend_Db::PARAM_INT,
            'deleted'        => Zend_DB::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'randomposter_id_' . $randomPosterId
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    // General user functions

    public static function authenticate($username, $password) {
        $authAdapter = new Zend_Auth_Adapter_DbTable(parent::getDb());
        $authAdapter
            ->setTableName('tb_user')
            ->setIdentityColumn('username')
            ->setCredentialColumn('password')
            ->setCredentialTreatment('MD5(MD5(salt) + MD5(?)) AND validated = 1 AND active = 1');
        $authAdapter
            ->setIdentity($username)
            ->setCredential($password);
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);
        if ($result->isValid()) {
            $userInfo = $authAdapter->getResultRowObject(array('pk_user_id'), null);
            $authStorage = $auth->getStorage();
            $authStorage->write(Factory_User::getUserById($userInfo->pk_user_id));
            parent::getDb()->update(
                'tb_user',
                array(
                    'last_login_ip' => ip2long($_SERVER['REMOTE_ADDR']),
                    'datetime_last_login' => date("Y-m-d G:i:s")
                ),
                parent::getDb()->quoteInto('pk_user_id = ?', $userInfo->pk_user_id)
            );
            return true;
        } else {
            switch ($result->getCode()) {
                case 0:
                case -4:
                default:
                    return 'An unknown error occurred';
                case -1:
                    return 'Incorrect Username';
                case -2:
                    return 'Multiple Usernames Found. How did this happen!?';
                case -3:
                    return 'Incorrect Password';
            }
        }
    }

    private static function generatePassword($password, $salt) {
        return md5(md5($salt) . md5($password));
    }

}