<?php

class Admin_PortfolioController extends PageController {

    public function indexAction() {
        $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
        $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
        $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
        $this->view->sites = Factory_Portfolio::getAllSites(1, 1000, true);
    }

    public function addAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'siteName'     => $this->getRequest()->getParam('siteName', null),
                'abstract'     => $this->getRequest()->getParam('abstract', null),
                'contents'     => $this->getRequest()->getParam('contents', null),
                'image'     => $this->getRequest()->getParam('image', null),
                'link'         => $this->getRequest()->getParam('link', null),
                'featured'     => ($this->getRequest()->getParam('featured', null) == 'on' ? 1 : 0),
                'deleted'     => ($this->getRequest()->getParam('deleted', null) == 'on' ? 1 : 0)
            );
            $filters = array(
                '*' => array(
                    'StringTrim',
                    new Zend_Filter_HtmlEntities(array('quotestyle' => ENT_NOQUOTES))
                ),
                'featured' => 'Digits',
                'deleted' => 'Digits'
            );
            $validators = array(
                'siteName' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : Username must be Alpha Numeric'
                    )
                ),
                'abstract' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : First name must be Alpha'
                    )
                ),
                'contents' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : Last Name must be Alpha'
                    )
                ),
                'image' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : Email Address must be a valid Email'
                    )
                ),
                'link' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : Registration IP must be a valid IP address'
                    )
                ),
                'featured' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Site Error : Featured must be true or false'
                    )
                ),
                'deleted' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Site Error : Deleted must be true or false'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_Portfolio::addSite(
                    $input->siteName,
                    $input->getUnescaped("abstract"),
                    $input->getUnescaped("contents"),
                    $input->image,
                    $input->link,
                    $input->featured,
                    $input->deleted
                );
                if ($result != false) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Site has been added');
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "portfolio", 'admin', array('id' => $result));
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error updating site');
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple("add", "portfolio", 'admin', array());
                }
            } else {
                foreach($input->getMessages() as $message) {
                    foreach($message as $error) {
                        $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                    }
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("add", "portfolio", 'admin', array());
            }
        }
        $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
        $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
        $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
    }

    public function editAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'siteId'         => $this->getRequest()->getParam('id', 0),
                'siteName'         => $this->getRequest()->getParam('siteName', null),
                'abstract'         => $this->getRequest()->getParam('abstract', null),
                'contents'         => $this->getRequest()->getParam('contents', null),
                'image'         => $this->getRequest()->getParam('image', null),
                'link'             => $this->getRequest()->getParam('link', null),
                'creationDate'     => $this->getRequest()->getParam('creationDate', null),
                'featured'         => ($this->getRequest()->getParam('featured', null) == 'on' ? 1 : 0),
                'deleted'         => ($this->getRequest()->getParam('deleted', null) == 'on' ? 1 : 0)
            );
            $filters = array(
                '*' => array(
                    'StringTrim',
                    new Zend_Filter_HtmlEntities(array('quotestyle' => ENT_NOQUOTES))
                ),
                'siteId' => 'Digits',
                'featured' => 'Digits',
                'deleted' => 'Digits'
            );
            $validators = array(
                'siteId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Edit Site Error : Site ID must be greater than 0'
                    )
                ),
                'siteName' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : Username must be Alpha Numeric'
                    )
                ),
                'abstract' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : First name must be Alpha'
                    )
                ),
                'contents' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : Last Name must be Alpha'
                    )
                ),
                'image' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : Email Address must be a valid Email'
                    )
                ),
                'link' => array(
                    'presence' => 'required',
                    'messages' => array(
                        'Edit Site Error : Registration IP must be a valid IP address'
                    )
                ),
                'creationDate' => array(
                    'presence' => 'required',
                    new Zend_Validate_Date(array('format' => 'YYYY-MM-dd HH:mm:ss')),
                    'messages' => array(
                        'Edit Site Error : Creation Date must be a valid Datetime'
                    )
                ),
                'featured' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Site Error : Featured must be true or false'
                    )
                ),
                'deleted' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Site Error : Deleted must be true or false'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_Portfolio::editSite(
                    $input->siteId,
                    $input->siteName,
                    $input->getUnescaped("abstract"),
                    $input->getUnescaped("contents"),
                    $input->image,
                    $input->link,
                    $input->creationDate,
                    $input->featured,
                    $input->deleted
                );
                if ($result != false) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Site has been updated');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error updating site');
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "portfolio", 'admin', array('id' => $input->siteId));
            } else {
                foreach($input->getMessages() as $message) {
                    foreach($message as $error) {
                        $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                    }
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "portfolio", 'admin', array('id' => $params['siteId']));
            }
        }
        $params = array(
            'siteId' => $this->getRequest()->getParam('id', 0)
        );
        $filters = array(
            'siteId' => 'Digits'
        );
        $validators = array(
            'siteId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find User Error : User ID must be greater than 0'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            $this->view->site = Factory_Portfolio::getSiteByID($input->siteId, true, 0);
            if (!$this->view->site) {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Find Site Error: Could not find site');
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "portfolio", 'admin', array());
            } else {
                $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
                $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
                $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
                $this->view->allTags = Factory_Tag::getAllTags(false, 0);
                $this->view->unusedTags = Factory_Tag::getUsusedSiteTags($input->siteId);
            }
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "portfolio", 'admin', array());
        }
    }

    public function changestateAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $params = array(
            'siteId' => $this->getRequest()->getParam('id', 0),
            'method' => $this->getRequest()->getParam('method', null)
        );
        $filters = array(
            '*' => array(
                'StringTrim',
                'StripTags'
            ),
            'siteId' => 'Digits'
        );
        $validators = array(
            'siteId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Site Error : Site ID must be greater than 0'
                )
            ),
            'method' => array(
                'presence' => 'required',
                new Zend_Validate_InArray(array('add', 'delete')),
                'messages' => array(
                    'Find Site Error : Method needs to be Add or Delete'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            if ($input->method == 'delete') {
                $result = Factory_Portfolio::setSiteDeletedStatus($input->siteId, 1);
                if ($result != false) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Site has been deleted');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error deleting site');
                }
            } elseif ($input->method == 'add') {
                $result = Factory_Portfolio::setSiteDeletedStatus($input->siteId, 0);
                if ($result != false) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Site has been added');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error adding site');
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "portfolio", 'admin', array());
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "portfolio", 'admin', array());
        }
    }

    public function addtagAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $params = array(
            'siteId' => $this->getRequest()->getParam('id', 0),
            'tagName' => $this->getRequest()->getParam('newTagText', 0)
        );
        $filters = array(
            '*' => array(
                'StripTags',
                'StringTrim'
            ),
            'siteId' => 'Digits',
            'tagName' => new Zend_Filter_Alnum(true)
        );
        $validators = array(
            'siteId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Add Tag Error : Site ID must be greater than 0'
                )
            ),
            'tagName' => array(
                'presence' => 'required',
                new Zend_Validate_Alnum(true),
                new Zend_Validate_StringLength(array('min' => 0, 'max' => 31)),
                new Zend_Validate_Db_NoRecordExists(
                    array(
                        'adapter' => Factory_Model::getDb(),
                        'table' => 'tb_tag',
                        'field' => 'tag_name',
                        'exclude' => array(
                            'field' => 'is_deleted',
                            'value' => 1
                        )
                    )
                ),
                'messages' => array(
                    'Add Tag Error : Tag Name must be alphanumeric and between 1 and 30 chars.'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            $result = Factory_Tag::insertTag($input->tagName);
            if ($result && $result > 0) {
                $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Tag has been added');
            } else {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error adding tag');
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "portfolio", 'admin', array('id' => $input->siteId));
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "portfolio", 'admin', array('id' => $params['siteId']));
        }
    }

    public function removetagAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $params = array(
            'siteId' => $this->getRequest()->getParam('id', 0),
            'removeTagId' => $this->getRequest()->getParam('removeTagId', 0)
        );
        $filters = array(
            '*' => array(
                'StripTags',
                'StringTrim'
            ),
            'siteId' => 'Digits',
            'removeTagId' => 'Digits'
        );
        $validators = array(
            'siteId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Remove Tag Error : Site ID must be greater than 0'
                )
            ),
            'removeTagId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Remove Tag Error : Tag ID must be greater than 0'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            $result = Factory_Tag::setTagDeletedStatus($input->removeTagId, 1);
            if ($result && $result > 0) {
                $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Tag has been removed');
            } else {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error removing tag');
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "portfolio", 'admin', array('id' => $input->siteId));
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "portfolio", 'admin', array('id' => $params['siteId']));
        }
    }

    public function assigntagAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isPost()) {
            $params = array(
                'siteId' => $this->getRequest()->getParam('siteId', 0),
                'tagId' => $this->getRequest()->getParam('tagId', 0),
                'adding' => $this->getRequest()->getParam('adding', null)
            );
            $filters = array(
                '*' => array(
                    'StripTags',
                    'StringTrim'
                ),
                'siteId' => 'Digits',
                'tagId' => 'Digits',
                'adding' => 'Digits'
            );
            $validators = array(
                'siteId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Assign Tag Error : Site ID must be greater than 0'
                    )
                ),
                'tagId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Assign Tag Error : Tag ID must be greater than 0'
                    )
                ),
                'adding' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Assign Tag Error : Adding must be 0 or 1'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                if ($input->adding == 1) {
                    $result = Factory_Tag::assignSiteTagMap($input->siteId, $input->tagId);
                    if ($result == true) {
                        die(Zend_Json::encode(array(
                            'status' => 'success',
                            'message' => 'Tag Map added successfully.'
                        )));
                    } else {
                        die(Zend_Json::encode(array(
                            'status' => 'fail',
                            'message' => 'Could not assign Tag Map.'
                        )));
                    }
                } else {
                    $result = Factory_Tag::setSiteTagMapDeletedStatus($input->siteId, $input->tagId, 1);
                    if ($result == true) {
                        die(Zend_Json::encode(array(
                            'status' => 'success',
                            'message' => 'Tag Map removed successfully.'
                        )));
                    } else {
                        die(Zend_Json::encode(array(
                            'status' => 'fail',
                            'message' => 'Could not remove Tag Map.'
                        )));
                    }

                }
            } else {
                die(Zend_Json::encode(array(
                    'status' => 'fail',
                    'message' => 'Invalid Site, Tag ID or Adding Method'
                )));
            }
        }
    }

}