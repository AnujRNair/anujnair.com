<?php

class LinksController extends PageController {

    public function indexAction() {
        $this->view->links = Factory_Link::getAllLinks(1, 10, false);
        $this->view->featured = Factory_Link::getFeaturedLinks();
    }

}