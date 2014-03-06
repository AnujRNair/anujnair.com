<?php

class ZendCustom_View_Helper_ParsePost extends Zend_View_Helper_Abstract {

    public function parsePost($string) {
        $bbCodeRegex = array(
            0 => '/\[code mode\=([\"\'])([full|long]+)\1 lang(?:uage)?\=([\"\'])(.*?)\3\](.*?)\[\/code\]/ims',
            1 => '/\[code mode\=([\"\'])([short|inline]+)\1 lang(?:uage)?\=([\"\'])(.*?)\3\](.*?)\[\/code\]/ims'
        );
        preg_match_all($bbCodeRegex[0], $string, $fullMatches);
        preg_match_all($bbCodeRegex[1], $string, $shortMatches);
        $bbFind = array(
            '/\[b\](.*?)\[\/b\]/ims',
            '/\[i\](.*?)\[\/i\]/ims',
            '/\[subheader\](.*?)\[\/subheader\]/ims',
            '/\[url\=([\"\'])(.*?)\1\](.*?)\[\/url\]/ims',
            '/\[img\](.*?)\[\/img\]/ims',
            '/\[list\](.*?)\[\/list\]/ims',
            '/\[\*\](.*?)\n/i'
        );
        $bbReplace = array(
            '<strong>\1</strong>',
            '<em>\1</em>',
            '<h4>\1</h4>',
            '<a href="\2">\3</a>',
            '<img src="\1" />',
            '<ul>\1</ul>',
            '<li>\1</li>'
        );
        $converted = nl2br(preg_replace($bbFind, $bbReplace, $string));
        for ($i = 0; $i < count($fullMatches[0]); $i++) {
            $converted = preg_replace($bbCodeRegex[0], '<pre class="syntax brush-' . $fullMatches[4][$i] . '">' . $fullMatches[5][$i] . '</pre>', $converted, 1);
        }
        for ($i = 0; $i < count($shortMatches[0]); $i++) {
            $converted = preg_replace($bbCodeRegex[1], '<code class="syntax brush-' . $shortMatches[4][$i] . '">' . $shortMatches[5][$i] . '</code>', $converted, 1);
        }
        return $converted;
    }

}