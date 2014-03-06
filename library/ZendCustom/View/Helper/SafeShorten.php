<?php

class ZendCustom_View_Helper_SafeShorten extends Zend_View_Helper_Abstract {

    public function safeShorten($input, $length, $encodingFallback = 'ISO-8859-1', $truncationIndicator = '...') {
        if (strlen($input) <= $length) {
            return $input;
        }

        if (!$encoding = mb_detect_encoding($input)) {
            $encoding = $encodingFallback; //detect encoding, or use fallback
        }
        $encoding = (mb_check_encoding($input, $encoding) ? $encoding : $encodingFallback); //validate encoding, or use fallback
        $htmlentityEncoding = (in_array($encoding, array(
            'ISO-8859-1',
            'ISO-8859-15',
            'UTF-8',
            'cp866',
            'cp1251',
            'cp1252',
            'KOI8-R',
            'BIG5',
            'GB2312',
            'BIG5-HKSCS',
            'Shift_JIS',
            'EUC-JP'
        )) ? $encoding : 'ISO-8859-1');
        $pregUtf8Modifier = ($encoding == 'UTF-8' ? 'u' : '');

        preg_match_all('#(</?\w+(?:(?:\s+\w+(?:\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)/?>)#i' . $pregUtf8Modifier, $input, $matches);
        if (empty($matches[1])) {
            return mb_substr($input, 0, $length, $encoding) . $truncationIndicator; //if there's no html we don't have to do all this fancy junk
        }

        $iSplit = preg_split('#</?\w+(?:(?:\s+\w+(?:\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)/?>#i' . $pregUtf8Modifier, $input);

        //do shorten
        $curCount = 0;
        $iSplitShortened = array();
        foreach ($iSplit as $i => $val) {
            $val = html_entity_decode($val, 0, $htmlentityEncoding);
            if (($curCount + mb_strlen($val, $encoding)) >= $length) {
                $iSplitShortened[$i] = mb_substr($val, 0, ($length - $curCount), $encoding) . $truncationIndicator;
                $iSplitShortened[$i] = htmlentities($iSplitShortened[$i], 0, $htmlentityEncoding);
                break;
            } else {
                $iSplitShortened[$i] = htmlentities($val, 0, $htmlentityEncoding);
                $curCount += mb_strlen($val, $encoding);
            }
        }

        //add back in full HTML
        $iHtmled = '';
        foreach ($iSplitShortened as $i => $txt) {
            if (isset($matches[1][$i-1])) {
                $iHtmled .= $matches[1][$i-1] . trim($txt);
            } else {
                $iHtmled .= trim($txt);
            }
        }

        //close open html tags
        $selfClosedTags = array('area', 'base', 'br', 'col', 'hr', 'img', 'input', 'link', 'meta', 'param'); //XHTML strict void elements
        preg_match_all('#<(/?\w+)(?:(?:\s+\w+(?:\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)/?>#i' . $pregUtf8Modifier, $iHtmled, $m);

        $tags = array();
        foreach ($m[1] as $v) {
            if (in_array($v, $selfClosedTags)) {
                continue;
            }
            if ($v[0] != '/') {
                if (isset($tags[$v])) {
                    $tags[$v] += 1;
                } else {
                    $tags[$v] = 1;
                }
            } else {
                $tags[mb_substr($v, 1, mb_strlen($v, $encoding), $encoding)] -= 1;
            }
        }
        $tags = array_reverse($tags); //$tags is in order found from left to right, reversing should nest tags properly...
        foreach ($tags as $tag => $unclosedCount) {
            if ($unclosedCount <= 0) {
                continue;
            }
            for ($i = 0; $i < $unclosedCount; $i++) {
                $iHtmled .= '</' . $tag . '>';
            }
        }
        return $iHtmled;
    }

}