<?php

class Social_Reddit extends Social_Model {

    protected $_socialId = 4;

    public function redirectUrl() {
        $view = $this->getView();
        return "http://www.reddit.com/submit?url=http://" . $this->getBaseUrl()
            . $view->url(array('id' => $this->_blog->blogId, 'title' => $view->urltitle($this->_blog->title)), 'blogArticle', true)
            . "&title=" . urlencode($this->_blog->title);
    }

}