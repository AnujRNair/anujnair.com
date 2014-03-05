<?php

class Social_Google extends Social_Model {

    protected $_socialId = 3;

    public function redirectUrl() {
        $view = $this->getView();
        return "https://plus.google.com/share?url=http://" . $this->getBaseUrl()
            . $view->url(array('id' => $this->_blog->blogId, 'title' => $view->urltitle($this->_blog->title)), 'blogArticle', true);
    }

}