<?php

class Factory_Comment extends Factory_Model {

    public static function getCommentById($commentId) {
        // Comment data
        $sql = "select
                    pk_comment_id as commentId,
                    fk_pk_blog_id as blogId,
                    fk_pk_user_id as userId,
                    fk_pk_random_poster_id as randomPosterId,
                    comment,
                    datetime_created as creationDate,
                    datetime_updated as updatedDate,
                    is_deleted as deleted
                from tb_comment
                where pk_comment_id = :id";
        $params = array(
            'id' => $commentId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $comment = parent::fetch($sql, $params, $dataTypes, 3600, 'comment_id_' . $commentId, false);
        if (!empty($comment)) {
            $comment->user = Factory_User::getUserById($comment->userId);
            $comment->randomPoster = Factory_User::getRandomPosterById($comment->randomPosterId);
        }
        return $comment;
    }

    public static function getCommentsByBlogId($blogId) {
        $sql = "select distinct
                    pk_comment_id as commentId
                from tb_comment
                where fk_pk_blog_id = :id
                order by pk_comment_id asc";
        $params = array(
            'id' => $blogId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $commentIds = parent::fetch($sql, $params, $dataTypes, 3600, 'commentsbyblog_id_' . $blogId, true);
        $comments = array();
        foreach ($commentIds as $comment) {
            $comments[] = Factory_Comment::getCommentById($comment->commentId);
        }
        return $comments;
    }

    public static function getCommentsByUserId($userId) {
        $sql = "select distinct
                    pk_comment_id as commentId
                from tb_comment
                where fk_pk_user_id = :id
                order by pk_comment_id desc";
        $params = array(
            'id' => $userId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $commentIds = parent::fetch($sql, $params, $dataTypes, 3600, 'commentsbyuser_id_' . $userId, true);
        $comments = array();
        foreach ($commentIds as $comment) {
            $comments[] = Factory_Comment::getCommentById($comment->commentId);
        }
        return $comments;
    }

    public static function getCommentsByRandomPosterId($randomPosterId) {
        $sql = "select distinct
                    pk_comment_id as commentId
                from tb_comment
                where fk_pk_random_poster_id = :id
                order by pk_comment_id desc";
        $params = array(
            'id' => $randomPosterId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $commentIds = parent::fetch($sql, $params, $dataTypes, 3600, 'commentsbyrandomposter_id_' . $randomPosterId, true);
        $comments = array();
        foreach ($commentIds as $comment) {
            $comments[] = Factory_Comment::getCommentById($comment->commentId);
        }
        return $comments;
    }

    public static function setCommentDeletedStatus($commentId, $deleted) {
        $sql = "update tb_comment
                set
                    is_deleted = :deleted
                where pk_comment_id = :commentId;
                select row_count() as rows";
        $params = array(
            'commentId' => $commentId,
            'deleted'   => $deleted
        );
        $dataTypes = array(
            'commentId' => Zend_Db::PARAM_INT,
            'deleted'   => Zend_DB::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array('comment_id_' . $commentId);
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function addUserComment($blogId, $userId, $comment) {
        $sql = "insert into tb_comment (
                    fk_pk_blog_id,
                    fk_pk_user_id,
                    fk_pk_random_poster_id,
                    comment,
                    datetime_created,
                    datetime_updated,
                    is_deleted
                ) values (
                    :blogId,
                    :userId,
                    :posterId,
                    :comment,
                    :creationDate,
                    :updatedDate,
                    :deleted
                )";
        $params = array(
            'blogId'       => $blogId,
            'userId'       => $userId,
            'posterId'     => null,
            'comment'      => $comment,
            'creationDate' => date('Y-m-d H:i:s'),
            'updatedDate'  => date('Y-m-d H:i:s'),
            'deleted'      => 0
        );
        $dataTypes = array(
            'blogId'       => Zend_Db::PARAM_INT,
            'userId'       => Zend_Db::PARAM_INT,
            'posterId'     => Zend_Db::PARAM_NULL,
            'comment'      => Zend_Db::PARAM_STR,
            'creationDate' => Zend_Db::PARAM_STR,
            'updatedDate'  => Zend_Db::PARAM_STR,
            'deleted'      => Zend_Db::PARAM_INT
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'commentsbyblog_id_' . $blogId,
                'commentsbyuser_id_' . $userId,
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function addRandomPosterComment($blogId, $name, $email, $website, $ip, $useragent, $comment) {
        // Find Poster
        $sql = "select
                    pk_random_poster_id as randomPosterId
                from tb_random_poster
                where name = :name
                and email = :email
                and website = :website
                and ip = :ip
                and useragent = :useragent
                and is_deleted = :deleted";
        $params = array(
            'name'      => $name,
            'email'     => $email,
            'website'   => $website,
            'ip'        => ip2long($ip),
            'useragent' => $useragent,
            'deleted'   => 0
        );
        $dataTypes = array(
            'name'      => Zend_Db::PARAM_STR,
            'email'     => Zend_Db::PARAM_STR,
            'website'   => Zend_Db::PARAM_STR,
            'ip'        => Zend_Db::PARAM_INT,
            'useragent' => Zend_Db::PARAM_STR,
            'deleted'   => Zend_Db::PARAM_INT
        );
        $poster = parent::fetch($sql, $params, $dataTypes, 0, null, false);
        if (!$poster || !isset($poster->randomPosterId)) {
            // No poster? Insert a new one
            $sql = "insert into tb_random_poster (
                        name,
                        email,
                        website,
                        ip,
                        useragent,
                        datetime_created,
                        is_deleted
                    ) values (
                        :name,
                        :email,
                        :website,
                        :ip,
                        :useragent,
                        :creationDate,
                        :deleted
                    )";
            $params = array(
                'name'         => $name,
                'email'        => $email,
                'website'      => $website,
                'ip'           => ip2long($ip),
                'useragent'    => $useragent,
                'creationDate' => date('Y-m-d H:i:s'),
                'deleted'      => 0
            );
            $dataTypes = array(
                'name'         => Zend_Db::PARAM_STR,
                'email'        => Zend_Db::PARAM_STR,
                'website'      => Zend_Db::PARAM_STR,
                'ip'           => Zend_Db::PARAM_INT,
                'useragent'    => Zend_Db::PARAM_STR,
                'creationDate' => Zend_Db::PARAM_STR,
                'deleted'      => Zend_Db::PARAM_INT
            );
            $inserted = parent::execute($sql, $params, $dataTypes);
            $posterId = $inserted;
        } else {
            $posterId = $poster->randomPosterId;
        }
        // Insert comment
        $sql = "insert into tb_comment (
                    fk_pk_blog_id,
                    fk_pk_user_id,
                    fk_pk_random_poster_id,
                    comment,
                    datetime_created,
                    datetime_updated,
                    is_deleted
                ) values (
                    :blogId,
                    :userId,
                    :posterId,
                    :comment,
                    :creationDate,
                    :updatedDate,
                    :deleted
                )";
        $params = array(
            'blogId'       => $blogId,
            'userId'       => null,
            'posterId'     => $posterId,
            'comment'      => $comment,
            'creationDate' => date('Y-m-d H:i:s'),
            'updatedDate'  => date('Y-m-d H:i:s'),
            'deleted'      => 0
        );
        $dataTypes = array(
            'blogId'       => Zend_Db::PARAM_INT,
            'userId'       => Zend_Db::PARAM_NULL,
            'posterId'     => Zend_Db::PARAM_INT,
            'comment'      => Zend_Db::PARAM_STR,
            'creationDate' => Zend_Db::PARAM_STR,
            'updatedDate'  => Zend_Db::PARAM_STR,
            'deleted'      => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'commentsbyblog_id_' . $blogId,
                'commentsbyrandomposter_id_' . $posterId,
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function editComment($commentId, $blogId, $userId, $posterId, $comment, $creationDate, $deleted) {
        $sql = "update tb_comment
                set
                    fk_pk_blog_id = :blogId,
                    fk_pk_user_id = :userId,
                    fk_pk_random_poster_id = :posterId,
                    comment = :comment,
                    datetime_created = :creationDate,
                    datetime_updated = :updatedDate,
                    is_deleted = :deleted
                where pk_comment_id = :commentId;
                select row_count() as rows";
        $params = array(
            'commentId'    => $commentId,
            'blogId'       => $blogId,
            'userId'       => $userId,
            'posterId'     => $posterId,
            'comment'      => $comment,
            'creationDate' => $creationDate,
            'updatedDate'  => date('Y-m-d H:i:s'),
            'deleted'      => $deleted
        );
        $dataTypes = array(
            'commentId'    => Zend_Db::PARAM_INT,
            'blogId'       => Zend_Db::PARAM_INT,
            'userId'       => Zend_Db::PARAM_INT,
            'posterId'     => Zend_Db::PARAM_INT,
            'comment'      => Zend_Db::PARAM_STR,
            'creationDate' => Zend_Db::PARAM_STR,
            'updatedDate'  => Zend_Db::PARAM_STR,
            'deleted'      => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array_merge(array(
                'comment_id_' . $commentId
            ), static::getMemcached()->getAllKeys('/(commentsbyblog_id_|commentsbyuser_id_|commentsbyrandomposter_id_)/'));
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

}