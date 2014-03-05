<?php

class Factory_Blog extends Factory_Model {

    public static function getBlogById($blogId) {
        // Blog data
        $sql = "select
                    b.pk_blog_id as blogId,
                    b.title,
                    b.subtitle,
                    b.contents,
                    b.is_comment_disabled as isCommentDisabled,
                    b.datetime_created as creationDate,
                    b.datetime_updated as updatedDate,
                    b.is_deleted as deleted
                from tb_blog as b
                where b.pk_blog_id = :id";
        $params = array(
            'id' => $blogId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $blog = parent::fetch($sql, $params, $dataTypes, 3600, 'blog_id_' . $blogId, false);
        if (!empty($blog)) {
            // Tag data
            $blog->tags = Factory_Tag::getTagsByBlogId($blogId);
            // Comment data
            $blog->comments = Factory_Comment::getCommentsByBlogId($blogId);
        }
        return $blog;
    }

    public static function getAllBlogs($page, $noPerPage, $wantDeleted = false) {
        $sql = "select
                    b.pk_blog_id as blogId
                from tb_blog as b
                " . ($wantDeleted == false ? " where b.is_deleted = 0 " : "") . "
                order by b.pk_blog_id desc";
        $blogIds = parent::fetch($sql, array(), array(), 3600, 'all_blog_ids' . ($wantDeleted == false ? '_withoutdel' : ''), true);
        $blogs = array();
        for ($i = (($page-1) * $noPerPage); $i < (($page-1) * $noPerPage) + $noPerPage; $i++) {
            if (isset($blogIds[$i])) {
                $blogs[] = Factory_Blog::getBlogById($blogIds[$i]->blogId);
            }
        }
        return $blogs;
    }

    public static function getBlogsByTagId($tagId, $page, $noPerPage) {
        $sql = "select distinct
                    tm.fk_pk_blog_id as blogId
                from tb_tag as t
                left join tb_tag_map as tm
                on tm.fk_pk_tag_id = t.pk_tag_id
                left join tb_blog as b
                on tm.fk_pk_blog_id = b.pk_blog_id
                where t.is_deleted = 0
                and b.is_deleted = 0
                and b.pk_blog_id is not null
                and tm.fk_pk_tag_id = :id
                and tm.is_deleted = 0
                order by b.pk_blog_id desc";
        $params = array(
            'id' => $tagId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $blogIds = parent::fetch($sql, $params, $dataTypes, 3600, 'tagmap_blogsbytag_id_' . $tagId, true);
        $blogs = array();
        for ($i = (($page-1) * $noPerPage); $i < (($page-1) * $noPerPage) + $noPerPage; $i++) {
            if (isset($blogIds[$i])) {
                $blogs[] = Factory_Blog::getBlogById($blogIds[$i]->blogId);
            }
        }
        return $blogs;
    }

    public static function getArchive($limit, $group = true) {
        $sql = "select distinct
                    b.pk_blog_id as blogId,
                    b.title,
                    b.datetime_created as creationDate,
                    YEAR(b.datetime_created) as year,
                    MONTHNAME(b.datetime_created) as month
                from tb_blog as b
                where b.is_deleted = 0
                order by b.datetime_created desc";
        $archive = parent::fetch($sql, array(), array(), 3600, 'archive', true);
        $results = array_slice($archive, 0, ($limit > 0 ? $limit : null));
        if ($group) {
            $grouped = array();
            foreach ($results as $result) {
                $grouped[$result->month . ' ' . $result->year][] = $result;
            }
            return $grouped;
        }
        return $results;
    }

    public static function setBlogDeletedStatus($blogId, $deleted) {
        $sql = "update tb_blog
                set
                    is_deleted = :deleted
                where pk_blog_id = :blogId";
        $params = array(
            'blogId'  => $blogId,
            'deleted' => $deleted
        );
        $dataTypes = array(
            'blogId'  => Zend_Db::PARAM_INT,
            'deleted' => Zend_DB::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array_merge(array(
                'blog_id_' . $blogId,
                'all_blog_ids_withoutdel',
                'archive',
                'tagsummary'
            ), static::getMemcached()->getAllKeys('/tagmap_blogsbytag_id_/'));
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function addBlog($title, $subtitle, $contents, $disableComments, $deleted) {
        $sql = "insert into tb_blog (
                    title,
                    subtitle,
                    contents,
                    datetime_created,
                    datetime_updated,
                    is_comment_disabled,
                    is_deleted
                ) values (
                    :title,
                    :subtitle,
                    :contents,
                    :creationDate,
                    :updatedDate,
                    :disableComments,
                    :deleted
                )";
        $params = array(
            'title'           => $title,
            'subtitle'        => $subtitle,
            'contents'        => $contents,
            'creationDate'    => date('Y-m-d H:i:s'),
            'updatedDate'     => date('Y-m-d H:i:s'),
            'disableComments' => $disableComments,
            'deleted'         => $deleted
        );
        $dataTypes = array(
            'title'           => Zend_Db::PARAM_STR,
            'subtitle'        => Zend_Db::PARAM_STR,
            'contents'        => Zend_Db::PARAM_STR,
            'creationDate'    => Zend_Db::PARAM_STR,
            'updatedDate'     => Zend_Db::PARAM_STR,
            'disableComments' => Zend_Db::PARAM_INT,
            'deleted'         => Zend_Db::PARAM_INT
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'all_blog_ids',
                'all_blog_ids_withoutdel',
                'archive'
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function editBlog($blogId, $title, $subtitle, $contents, $creationDate, $disableComments, $deleted) {
        $sql = "update tb_blog
                set
                    title = :title,
                    subtitle = :subtitle,
                    contents = :contents,
                    datetime_created = :creationDate,
                    datetime_updated = :updatedDate,
                    is_comment_disabled = :disableComments,
                    is_deleted = :deleted
                where pk_blog_id = :blogId";
        $params = array(
            'blogId'          => $blogId,
            'title'           => $title,
            'subtitle'        => $subtitle,
            'contents'        => $contents,
            'creationDate'    => $creationDate,
            'updatedDate'     => date('Y-m-d H:i:s'),
            'disableComments' => $disableComments,
            'deleted'         => $deleted
        );
        $dataTypes = array(
            'blogId'          => Zend_Db::PARAM_INT,
            'title'           => Zend_Db::PARAM_STR,
            'subtitle'        => Zend_Db::PARAM_STR,
            'contents'        => Zend_Db::PARAM_STR,
            'creationDate'    => Zend_Db::PARAM_STR,
            'updatedDate'     => Zend_Db::PARAM_STR,
            'disableComments' => Zend_Db::PARAM_INT,
            'deleted'         => Zend_Db::PARAM_INT
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array_merge(array(
                'blog_id_' . $blogId,
                'all_blog_ids_withoutdel',
                'archive'
            ), static::getMemcached()->getAllKeys('/tagmap_blogsbytag_id_/'));
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

}