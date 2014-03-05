<?php

class Social_Twitter extends Social_Model {

    protected $_socialId = 2;

    public function redirectUrl() {
        $view = $this->getView();
        $tagHash = "";
        foreach ($this->_blog->tags as $tag) {
            $tagHash .= ' #' . str_replace(' ', '', $tag->tagName);
        }
        return "http://twitter.com/intent/tweet?source=anujnaircom&text=" 
            . urlencode($this->_blog->title . " - http://" . $this->getBaseUrl() . "/blog/article/id/" . $this->_blog->blogId 
            . (strlen($tagHash) > 0 ? " -" . $tagHash : "")) 
            . "&url=" . $view->url(array('id' => $this->_blog->blogId, 'title' => $view->urltitle($this->_blog->title)), 'blogArticle', true);
    }

}