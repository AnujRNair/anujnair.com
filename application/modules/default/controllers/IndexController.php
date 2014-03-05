<?php

class IndexController extends PageController {

    public function indexAction() {
        $this->view->featuredSites = Factory_Portfolio::getFeaturedSites();
        $this->view->latestBlogs = Factory_Blog::getArchive(3, false);
    }

}