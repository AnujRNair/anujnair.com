<?php

class Social_Facebook extends Social_Model {

    protected $_socialId = 1;

    public function redirectUrl() {
        $view = $this->getView();
        return "http://www.facebook.com/sharer.php?u=http://"
            . $this->getBaseUrl() . $view->url(array('id' => $this->_blog->blogId, 'title' => $view->urltitle($this->_blog->title)), 'blogArticle', true)
            . "&t=" . urlencode($this->_blog->title . " - AnujNair.com");
    }

}