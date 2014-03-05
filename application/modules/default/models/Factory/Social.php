<?php

class Factory_Social extends Factory_Model {

    public static function logSocialShare($socialId, $blogId, $ip) {
        $sql = "insert into tb_social_log (
                    fk_pk_social_id,
                    fk_pk_blog_id,
                    ip
                ) values (
                    :socialId,
                    :blogId,
                    :ip
                )";
        $params = array(
            'socialId' => $socialId,
            'blogId'   => $blogId,
            'ip'       => ip2long($ip)
        );
        $dataTypes = array(
            'socialId' => Zend_Db::PARAM_INT,
            'blogId'   => Zend_Db::PARAM_INT,
            'ip'       => Zend_Db::PARAM_INT,
        );
        $result = parent::execute($sql, $params, $dataTypes);
    }

}