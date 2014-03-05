<?php

class Factory_Portfolio extends Factory_Model {

    public static function getSitebyId($siteId) {
        $sql = "select
                    pk_site_id as siteId,
                    site_name as siteName,
                    abstract,
                    main_contents as contents,
                    image,
                    link,
                    is_featured as featured,
                    datetime_created as creationDate,
                    datetime_updated as updatedDate,
                    is_deleted as deleted
                from tb_site
                where pk_site_id = :id";
        $params = array(
            'id' => $siteId
        );
        $dataTypes = array(
            'id' => Zend_Db::PARAM_INT
        );
        $site = parent::fetch($sql, $params, $dataTypes, 3600, 'site_id_' . $siteId, false);
        if (!empty($site)) {
            // Tag data
            $site->tags = Factory_Tag::getTagsBySiteId($siteId);
        }
        return $site;
    }

    public static function getAllSites($page, $noPerPage, $wantDeleted = false) {
        $sql = "select
                    pk_site_id as siteId
                from tb_site
                " . ($wantDeleted == false ? " where is_deleted = 0 " : "") . "
                order by pk_site_id desc";
        $siteIds = parent::fetch($sql, array(), array(), 3600, 'all_site_ids' . ($wantDeleted == false ? '_withoutdel' : ''), true);
        $sites = array();
        for ($i = (($page-1) * $noPerPage); $i < (($page-1) * $noPerPage) + $noPerPage; $i++) {
            if (isset($siteIds[$i])) {
                $sites[] = Factory_Portfolio::getSitebyId($siteIds[$i]->siteId);
            }
        }
        return $sites;
    }

    public static function getFeaturedSites() {
        $sql = "select
                    pk_site_id as siteId
                from tb_site
                where is_deleted = 0
                and is_featured = 1
                order by pk_site_id desc";
        $siteIds = parent::fetch($sql, array(), array(), 3600, 'featuredsites', true);
        $sites = array();
        foreach ($siteIds as $site) {
            $sites[] = Factory_Portfolio::getSiteById($site->siteId);
        }
        return $sites;
    }

    public static function setSiteDeletedStatus($siteId, $deleted) {
        $sql = "update tb_site
                set
                    is_deleted = :deleted,
                    datetime_updated = :updatedDate
                where pk_site_id = :siteId;
                select row_count() as rows";
        $params = array(
            'siteId'      => $siteId,
            'updatedDate' => date('Y-m-d H:i:s'),
            'deleted'     => $deleted
        );
        $dataTypes = array(
            'siteId'      => Zend_Db::PARAM_INT,
            'updatedDate' => Zend_Db::PARAM_STR,
            'deleted'     => Zend_DB::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'site_id_' . $siteId,
                'all_site_ids_withoutdel',
                'featuredsites'
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

    public static function addSite($siteName, $abstract, $contents, $image, $link, $featured, $deleted) {
        $sql = "insert into tb_site (
                    site_name,
                    abstract,
                    main_contents,
                    image,
                    link,
                    is_featured,
                    datetime_created,
                    datetime_updated,
                    is_deleted
                ) values (
                    :siteName,
                    :abstract,
                    :contents,
                    :image,
                    :link,
                    :featured,
                    :creationDate,
                    :updatedDate,
                    :deleted
                )";
        $params = array(
            'siteName'     => $siteName,
            'abstract'     => $abstract,
            'contents'     => $contents,
            'image'        => $image,
            'link'         => $link,
            'featured'     => $featured,
            'creationDate' => date('Y-m-d H:i:s'),
            'updatedDate'  => date('Y-m-d H:i:s'),
            'deleted'      => $deleted
        );
        $dataTypes = array(
            'siteName'     => Zend_Db::PARAM_STR,
            'abstract'     => Zend_Db::PARAM_STR,
            'contents'     => Zend_Db::PARAM_STR,
            'image'        => Zend_Db::PARAM_STR,
            'link'         => Zend_Db::PARAM_STR,
            'featured'     => Zend_Db::PARAM_BOOL,
            'creationDate' => Zend_Db::PARAM_STR,
            'updatedDate'  => Zend_Db::PARAM_STR,
            'deleted'      => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'all_site_ids',
                'all_site_ids_withoutdel',
                'featuredsites'
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }
    
    public static function editSite($siteId, $siteName, $abstract, $contents, $image, $link, $creationDate, $featured, $deleted) {
        $sql = "update tb_site
                set
                    site_name = :siteName,
                    abstract = :abstract,
                    main_contents = :contents,
                    image = :image,
                    link = :link,
                    is_featured = :featured,
                    datetime_created = :creationDate,
                    datetime_updated = :updatedDate,
                    is_deleted = :deleted
                where pk_site_id = :siteId;
                select row_count() as rows";
        $params = array(
            'siteId'       => $siteId,
            'siteName'     => $siteName,
            'abstract'     => $abstract,
            'contents'     => $contents,
            'image'        => $image,
            'link'         => $link,
            'featured'     => $featured,
            'creationDate' => date('Y-m-d H:i:s'),
            'updatedDate'  => date('Y-m-d H:i:s'),
            'deleted'      => $deleted
        );
        $dataTypes = array(
            'siteId'       => Zend_Db::PARAM_INT,
            'siteName'     => Zend_Db::PARAM_STR,
            'abstract'     => Zend_Db::PARAM_STR,
            'contents'     => Zend_Db::PARAM_STR,
            'image'        => Zend_Db::PARAM_STR,
            'link'         => Zend_Db::PARAM_STR,
            'featured'     => Zend_Db::PARAM_BOOL,
            'creationDate' => Zend_Db::PARAM_STR,
            'updatedDate'  => Zend_Db::PARAM_STR,
            'deleted'      => Zend_Db::PARAM_BOOL
        );
        $result = parent::execute($sql, $params, $dataTypes);
        if ($result > 0) {
            $cacheKeys = array(
                'site_id_' . $siteId,
                'all_site_ids_withoutdel',
                'featuredsites'
            );
            parent::deleteFromCache($cacheKeys);
            return true;
        }
        return false;
    }

}