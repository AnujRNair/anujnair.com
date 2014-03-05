<?php

class PortfolioController extends PageController {

    public function indexAction() {
        $this->view->sites = Factory_Portfolio::getAllSites(1, 10, false);
        $this->view->latestBlogs = Factory_Blog::getArchive(6, false);
    }

    public function siteAction() {
        $id = $this->getRequest()->getParam('id', 0);
        $name = $this->getRequest()->getParam('name', null);
        if ((int)$id > 0) {
            $this->view->site = Factory_Portfolio::getSiteById($id, false, 0);
            if (!$this->view->site) {
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "portfolio", 'default', array());
            } elseif ($name != $this->view->urltitle($this->view->site->siteName)) {
                $this->getResponse()->setRedirect(
                    $this->view->url(array(
                        'id' => $this->view->site->siteId,
                        'name' => $this->view->urltitle($this->view->site->siteName)
                    ), 'site', true),
                    301);
            }
        } else {
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "portfolio", 'default', array());
        }
        $this->view->latestBlogs = Factory_Blog::getArchive(6, false);
    }

}