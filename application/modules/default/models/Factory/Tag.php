<?php

class Factory_Tag extends Factory_Model {

    public static function getTagById($tagId) {
        // Tag data
        $sql = "select
                    t.pk_tag_id as tagId,
                    t.tag_name as tagName,
                    t.datetime_created as creationDate,
                    t.is_deleted as isDeleted
                from tb_tag as t
                where t.pk_tag_id = :id";
        $params = array(
            'id' => $tagId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        return parent::fetch($sql, $params, $dataTypes, 3600, 'tag_id_' . $tagId, false);
    }

    public static function getAllTags($wantDeleted = false) {
        $sql = "select
                    pk_tag_id as tagId
                from tb_tag
                " . ($wantDeleted == false ? " where is_deleted = 0 " : "") . "
                order by tag_name asc";
        $tagIds = parent::fetch($sql, array(), array(), 3600, 'all_tag_ids' . ($wantDeleted == false ? '_withoutdel' : ''), true);
        $tags = array();
        foreach ($tagIds as $tag) {
            $tags[] = Factory_Tag::getTagById($tag->tagId);
        }
        return $tags;
    }

    // Blog functions

    public static function getTagsByBlogId($blogId) {
        $sql = "select distinct
                    tm.fk_pk_tag_id as tagId
                from tb_tag_map as tm
                left join tb_tag as t
                on tm.fk_pk_tag_id = t.pk_tag_id
                where tm.fk_pk_blog_id = :id
                and tm.is_deleted = 0
                and t.is_deleted = 0";
        $params = array(
            'id' => $blogId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $tagIds = parent::fetch($sql, $params, $dataTypes, 3600, 'tagmap_tagsbyblog_id_' . $blogId, true);
        $tags = array();
        foreach ($tagIds as $tag) {
            $tags[] = Factory_Tag::getTagById($tag->tagId);
        }
        return $tags;
    }

    public static function assignBlogTagMap($blogId, $tagId) {
        $sql = "insert into tb_tag_map (
                    fk_pk_blog_id,
                    fk_pk_site_id,
                    fk_pk_tag_id,
                    is_deleted
                ) values (
                    :blogId,
                    :siteId,
                    :tagId,
                    :deleted
                )
                on duplicate key
                update
                    is_deleted = 0";
        $params = array(
            'blogId'  => $blogId,
            'siteId'  => null,
            'tagId'   => $tagId,
            'deleted' => 0
        );
        $dataTypes = array(
            'blogId'  => Zend_Db::PARAM_INT,
            'siteId'  => Zend_Db::PARAM_NULL,
            'tagId'   => Zend_Db::PARAM_INT,
            'deleted' => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        $cacheKeys = array(
            'unusedblogtags_id_' . $blogId,
            'tagmap_tagsbyblog_id_' . $blogId,
            'tagmap_blogsbytag_id_' . $tagId,
            'tagsummary'
        );
        parent::deleteFromCache($cacheKeys);
        return true;
    }

    public static function setBlogTagMapDeletedStatus($blogId, $tagId, $deleted) {
        $sql = "update tb_tag_map
                set
                    is_deleted = :deleted
                where fk_pk_blog_id = :blogId
                and fk_pk_tag_id = :tagId;
                select row_count() as rows";
        $params = array(
            'tagId'   => $tagId,
            'blogId'  => $blogId,
            'deleted' => $deleted
        );
        $dataTypes = array(
            'tagId'   => Zend_Db::PARAM_INT,
            'blogId'  => Zend_Db::PARAM_INT,
            'deleted' => Zend_DB::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'unusedblogtags_id_' . $blogId,
                'tagmap_tagsbyblog_id_' . $blogId,
                'tagmap_blogsbytag_id_' . $tagId,
                'tagsummary'
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function getUsusedBlogTags($blogId) {
        $sql = "select distinct
                    pk_tag_id as tagId
                from tb_tag as t
                where t.is_deleted = 0
                and pk_tag_id not in (
                    select
                        fk_pk_tag_id
                    from tb_tag_map tm
                    where tm.fk_pk_blog_id = :blogId
                    and tm.is_deleted = 0
                )
                order by t.tag_name asc";
        $params = array(
            'blogId' => $blogId
        );
        $dataTypes = array(
            'blogId' => Zend_Db::PARAM_INT
        );
        $tagIds = parent::fetch($sql, $params, $dataTypes, 3600, 'unusedblogtags_id_' . $blogId, true);
        $tags = array();
        foreach ($tagIds as $tag) {
            $tags[] = Factory_Tag::getTagById($tag->tagId);
        }
        return $tags;
    }

    // Site functions

    public static function getTagsBySiteId($siteId) {
        $sql = "select distinct
                    tm.fk_pk_tag_id as tagId
                from tb_tag_map as tm
                where fk_pk_site_id = :id
                and is_deleted = 0";
        $params = array(
            'id' => $siteId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $tagIds = parent::fetch($sql, $params, $dataTypes, 3600, 'tagmap_tagsbysite_id_' . $siteId, true);
        $tags = array();
        foreach ($tagIds as $tag) {
            $tags[] = Factory_Tag::getTagById($tag->tagId);
        }
        return $tags;
    }

    public static function assignSiteTagMap($siteId, $tagId) {
        $sql = "insert into tb_tag_map (
                    fk_pk_blog_id,
                    fk_pk_site_id,
                    fk_pk_tag_id,
                    is_deleted
                ) values (
                    :blogId,
                    :siteId,
                    :tagId,
                    :deleted
                )
                on duplicate key
                update
                    is_deleted = 0";
        $params = array(
            'blogId'  => null,
            'siteId'  => $siteId,
            'tagId'   => $tagId,
            'deleted' => 0
        );
        $dataTypes = array(
            'blogId'  => Zend_Db::PARAM_NULL,
            'siteId'  => Zend_Db::PARAM_INT,
            'tagId'   => Zend_Db::PARAM_INT,
            'deleted' => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        $cacheKeys = array(
            'tagmap_tagsbysite_id_' . $siteId,
            'unusedsitetags_id_' . $siteId
        );
        parent::deleteFromCache($cacheKeys);
        return true;
    }

    public static function setSiteTagMapDeletedStatus($siteId, $tagId, $deleted) {
        $sql = "update tb_tag_map
                set
                    is_deleted = :deleted
                where fk_pk_site_id = :siteId
                and fk_pk_tag_id = :tagId;
                select row_count() as rows";
        $params = array(
            'tagId'   => $tagId,
            'siteId'  => $siteId,
            'deleted' => $deleted
        );
        $dataTypes = array(
            'tagId'   => Zend_Db::PARAM_INT,
            'siteId'  => Zend_Db::PARAM_INT,
            'deleted' => Zend_DB::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'tagmap_tagsbysite_id_' . $siteId,
                'unusedsitetags_id_' . $siteId
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function getUsusedSiteTags($siteId) {
        $sql = "select distinct
                    pk_tag_id as tagId
                from tb_tag as t
                where t.is_deleted = 0
                and pk_tag_id not in (
                    select
                        fk_pk_tag_id
                    from tb_tag_map tm
                    where tm.fk_pk_site_id = :siteId
                    and tm.is_deleted = 0
                )
                order by t.tag_name asc";
        $params = array(
            'siteId' => $siteId
        );
        $dataTypes = array(
            'siteId' => Zend_Db::PARAM_INT
        );
        $tagIds = parent::fetch($sql, $params, $dataTypes, 3600, 'unusedsitetags_id_' . $siteId, true);
        $tags = array();
        foreach ($tagIds as $tag) {
            $tags[] = Factory_Tag::getTagById($tag->tagId);
        }
        return $tags;
    }

    // General tag

    public static function setTagDeletedStatus($tagId, $deleted) {
        $sql = "update tb_tag
                set
                    is_deleted = :deleted
                where pk_tag_id = :tagId;
                select row_count() as rows";
        $params = array(
            'tagId'       => $tagId,
            'deleted'     => $deleted
        );
        $dataTypes = array(
            'tagId'       => Zend_Db::PARAM_INT,
            'deleted'     => Zend_DB::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array_merge(array(
                'tag_id_' . $tagId,
                'all_tag_ids_withoutdel',
                'tagsummary'
            ), static::getMemcached()->getAllKeys('/(unusedsitetags_id_|tagmap_tagsbysite_id_|unusedblogtags_id_|tagmap_tagsbyblog_id_)/'));
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function insertTag($tagName) {
        $sql = "insert into tb_tag (
                    tag_name,
                    datetime_created,
                    is_deleted
                ) values (
                    :tagName,
                    :creationDate,
                    :deleted
                )
                on duplicate key
                update
                    is_deleted = :deleted";
        $params = array(
            'tagName'      => $tagName,
            'creationDate' => date('Y-m-d H:i:s'),
            'deleted'      => 0
        );
        $dataTypes = array(
            'tagName'      => Zend_Db::PARAM_STR,
            'creationDate' => Zend_Db::PARAM_STR,
            'deleted'      => Zend_Db::PARAM_INT
        );
        $result = parent::execute($sql, $params, $dataTypes);
        $cacheKeys = array_merge(array(
            'all_tag_ids',
            'all_tag_ids_withoutdel',
            'tagsummary'
        ), static::getMemcached()->getAllKeys('/(unusedsitetags_id_|tagmap_tagsbysite_id_|unusedblogtags_id_|tagmap_tagsbyblog_id_)/'));
        parent::deleteFromCache($cacheKeys);
        return true;
    }

    public static function getTagSummary() {
        $sql = "select distinct
                    pk_tag_id as tagId,
                    tag_name as tagName,
                    COUNT(pk_tag_id) as tagCount
                from tb_tag t
                left join tb_tag_map tm
                on tm.fk_pk_tag_id = t.pk_tag_id
                left join tb_blog b
                on tm.fk_pk_blog_id = b.pk_blog_id
                where t.is_deleted = 0
                and b.is_deleted = 0
                and tm.fk_pk_blog_id is not null
                and tm.is_deleted = 0
                group by
                    t.pk_tag_id,
                    t.tag_name
                order by t.tag_name asc";
        return parent::fetch($sql, array(), array(), 3600, 'tagsummary', true);
    }

}