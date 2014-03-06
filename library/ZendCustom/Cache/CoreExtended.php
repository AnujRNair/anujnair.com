<?php

class ZendCustom_Cache_CoreExtended extends Zend_Cache_Core {

    public function stripId($id) {
        if (($id !== null) && isset($this->_options['cache_id_prefix'])) {
            if (strpos($id, $this->_options['cache_id_prefix']) === 0) {
                return substr($id, strlen($this->_options['cache_id_prefix']));
            }
        }
        return $id; // couldn't strip, just return the $id passed
    }

}