<?php

class ZendCustom_Cache_Backend_MemcachedExtended extends Zend_Cache_Backend_Memcached {

    public function getExtendedStats() {
        $list = array();
        $allSlabs = $this->_memcache->getExtendedStats('slabs');
        $items = $this->_memcache->getExtendedStats('items');
        foreach($allSlabs as $server => $slabs) {
            foreach ($slabs as $slabId => $slabMeta) {
                $cdump = $this->_memcache->getExtendedStats('cachedump', (int)$slabId, 100000);
                foreach ($cdump as $server => $entries) {
                    if ($entries) {
                        foreach ($entries as $eName => $eData) {
                            $list[$eName] = array(
                                'key' => $eName,
                                'server' => $server,
                                'slabId' => $slabId,
                                'detail' => $eData,
                                'age' => $items[$server]['items'][$slabId]['age'],
                            );
                        }
                    }
                }
            }
        }
        ksort($list);
        return $list;
    }

    public function getAllKeys($regex = null) {
        $keys = array();
        $allSlabs = $this->_memcache->getExtendedStats('slabs');
        foreach($allSlabs as $server => $slabs) {
            foreach ($slabs as $slabId => $slabMeta) {
                $cdump = $this->_memcache->getExtendedStats('cachedump', (int)$slabId, 100000);
                foreach ($cdump as $server => $entries) {
                    if ($entries) {
                        foreach ($entries as $eName => $eData) {
                            $keys[] = $eName;
                        }
                    }
                }
            }
        }
        if ($regex == null) {
            return $keys;
        } else {
            return preg_grep($regex, $keys);
        }
    }

}