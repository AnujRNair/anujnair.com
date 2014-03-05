<?php

class Factory_Link extends Factory_Model {

    public static function getLinkById($linkId) {
        $sql = "select
                    pk_link_id as linkId,
                    title,
                    link,
                    description,
                    datetime_created as creationDate,
                    datetime_updated as updatedDate,
                    is_featured as featured,
                    is_deleted as deleted
                from tb_link
                where pk_link_id = :id";
        $params = array(
            'id' => $linkId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        return parent::fetch($sql, $params, $dataTypes, 3600, 'link_id_' . $linkId, false);
    }

    public static function getAllLinks($page, $noPerPage, $wantDeleted = false) {
        $sql = "select
                    l.pk_link_id as linkId
                from tb_link as l
                " . ($wantDeleted == false ? " where l.is_deleted = 0 " : "") . "
                order by l.pk_link_id desc";
        $linkIds = parent::fetch($sql, array(), array(), 3600, 'all_link_ids' . ($wantDeleted == false ? '_withoutdel' : ''), true);
        $links = array();
        for ($i = (($page-1) * $noPerPage); $i < (($page-1) * $noPerPage) + $noPerPage; $i++) {
            if (isset($linkIds[$i])) {
                $links[] = Factory_Link::getLinkById($linkIds[$i]->linkId);
            }
        }
        return $links;
    }

    public static function getFeaturedLinks() {
        $sql = "select
                    l.pk_link_id as linkId
                from tb_link as l
                where l.is_deleted = 0
                and l.is_featured = 1
                order by l.pk_link_id desc";
        $linkIds = parent::fetch($sql, array(), array(), 3600, 'featuredlinks', true);
        $links = array();
        foreach ($linkIds as $link) {
            $links[] = Factory_Link::getLinkById($link->linkId);
        }
        return $links;
    }

    public static function setLinkDeletedStatus($linkId, $deleted) {
        $sql = "update tb_link
                set
                    is_deleted = :deleted,
                    datetime_updated = :updatedDate
                where pk_link_id = :linkId;
                select row_count() as rows";
        $params = array(
            'linkId'      => $linkId,
            'updatedDate' => date('Y-m-d H:i:s'),
            'deleted'     => $deleted
        );
        $dataTypes = array(
            'linkId'      => Zend_Db::PARAM_INT,
            'updatedDate' => Zend_Db::PARAM_STR,
            'deleted'     => Zend_DB::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'link_id_' . $linkId,
                'all_link_ids_withoutdel',
                'featuredlinks'
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function addLink($title, $link, $description, $featured, $deleted) {
        $sql = "insert into tb_link (
                    title,
                    link,
                    description,
                    datetime_created,
                    datetime_updated,
                    is_featured,
                    is_deleted
                ) values (
                    :title,
                    :link,
                    :description,
                    :creationDate,
                    :updatedDate,
                    :featured,
                    :deleted
                )";
        $params = array(
            'title'        => $title,
            'link'         => $link,
            'description'  => $description,
            'creationDate' => date('Y-m-d H:i:s'),
            'updatedDate'  => date('Y-m-d H:i:s'),
            'featured'     => $featured,
            'deleted'      => $deleted
        );
        $dataTypes = array(
            'title'        => Zend_Db::PARAM_STR,
            'link'         => Zend_Db::PARAM_STR,
            'description'  => Zend_Db::PARAM_STR,
            'creationDate' => Zend_Db::PARAM_STR,
            'updatedDate'  => Zend_Db::PARAM_STR,
            'featured'     => Zend_Db::PARAM_BOOL,
            'deleted'      => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'all_link_ids',
                'all_link_ids_withoutdel',
                'featuredlinks'
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function editLink($linkId, $title, $link, $description, $creationDate, $featured, $deleted) {
        $sql = "update tb_link
                set
                    title = :title,
                    link = :link,
                    description = :description,
                    datetime_created = :creationDate,
                    datetime_updated = :updatedDate,
                    is_featured = :featured,
                    is_deleted = :deleted
                where pk_link_id = :linkId;
                select row_count() as rows";
        $params = array(
            'linkId'       => $linkId,
            'title'        => $title,
            'link'         => $link,
            'description'  => $description,
            'creationDate' => $creationDate,
            'updatedDate'  => date('Y-m-d H:i:s'),
            'featured'     => $featured,
            'deleted'      => $deleted
        );
        $dataTypes = array(
            'linkId'       => Zend_Db::PARAM_INT,
            'title'        => Zend_Db::PARAM_STR,
            'link'         => Zend_Db::PARAM_STR,
            'description'  => Zend_Db::PARAM_STR,
            'creationDate' => Zend_Db::PARAM_STR,
            'updatedDate'  => Zend_Db::PARAM_STR,
            'featured'     => Zend_Db::PARAM_BOOL,
            'deleted'      => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'link_id_' . $linkId,
                'all_link_ids_withoutdel',
                'featuredlinks'
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

}