<?php

class Admin_LinksController extends PageController {

    public function indexAction() {
        $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
        $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
        $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
        $this->view->links = Factory_Link::getAllLinks(1, 1000, true);
    }

    public function changestateAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $params = array(
            'linkId' => $this->getRequest()->getParam('id', 0),
            'method' => $this->getRequest()->getParam('method', null)
        );
        $filters = array(
            '*' => array(
                'StringTrim',
                'StripTags'
            ),
            'linkId' => 'Digits'
        );
        $validators = array(
            'linkId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Link Error : Link ID must be greater than 0'
                )
            ),
            'method' => array(
                'presence' => 'required',
                new Zend_Validate_InArray(array('add', 'delete')),
                'messages' => array(
                    'Find Link Error : Method needs to be Add or Delete'
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
                $result = Factory_Link::setLinkDeletedStatus($input->linkId, 1);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Link has been deleted');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error deleting link');
                }
            } elseif ($input->method == 'add') {
                $result = Factory_Link::setLinkDeletedStatus($input->linkId, 0);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Link has been added');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error adding link');
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "links", 'admin', array());
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "links", 'admin', array());
        }
    }

    public function editAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'linkId'         => $this->getRequest()->getParam('id', 0),
                'title'         => $this->getRequest()->getParam('linkTitle', null),
                'link'             => $this->getRequest()->getParam('linkLink', null),
                'description'     => $this->getRequest()->getParam('description', null),
                'creationDate'     => $this->getRequest()->getParam('creationDate', null),
                'featured'         => ($this->getRequest()->getParam('featured', null) == 'on' ? 1 : 0),
                'deleted'         => ($this->getRequest()->getParam('deleted', null) == 'on' ? 1 : 0)
            );
            $filters = array(
                '*' => array(
                    'StripTags',
                    'StringTrim'
                ),
                'linkId' => 'Digits',
                'featured' => 'Digits',
                'deleted' => 'Digits'
            );
            $validators = array(
                'linkId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Edit Link Error : Link ID must be greater than 0'
                    )
                ),
                'title' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 100)),
                    'messages' => array(
                        'Edit Link Error : Title must be between 0 and 100 chars.'
                    )
                ),
                'link' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 100)),
                    'messages' => array(
                        'Edit Link Error : Link must be between 0 and 100 chars.'
                    )
                ),
                'description' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 500)),
                    'messages' => array(
                        'Edit Link Error : Description must be between 0 and 100 chars.'
                    )
                ),
                'creationDate' => array(
                    'presence' => 'required',
                    new Zend_Validate_Date(array('format' => 'YYYY-MM-dd HH:mm:ss')),
                    'messages' => array(
                        'Edit Link Error : Creation Date must be a valid Datetime'
                    )
                ),
                'featured' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Link Error : Featured must be true or false'
                    )
                ),
                'deleted' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Link Error : Deleted must be true or false'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_Link::editLink(
                    $input->linkId,
                    $input->title,
                    $input->link,
                    $input->description,
                    $input->creationDate,
                    $input->featured,
                    $input->deleted
                );
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Link has been updated');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error updating link');
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "links", 'admin', array('id' => $input->linkId));
            } else {
                $errors = array();
                foreach($input->getMessages() as $message) {
                    foreach($message as $error) {
                        $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                    }
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "links", 'admin', array('id' => $params['linkId']));
            }
        }
        $params = array(
            'linkId' => $this->getRequest()->getParam('id', 0)
        );
        $filters = array(
            'linkId' => 'Digits'
        );
        $validators = array(
            'linkId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Link Error : Link ID must be greater than 0'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            $this->view->link = Factory_Link::getLinkById($input->linkId, 0);
            if (!$this->view->link) {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Find Link Error: Could not find link');
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "links", 'admin', array());
            } else {
                $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
                $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
                $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
            }
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "links", 'admin', array());
        }
    }

    public function addAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'title'         => $this->getRequest()->getParam('linkTitle', null),
                'link'             => $this->getRequest()->getParam('linkLink', null),
                'description'     => $this->getRequest()->getParam('description', null),
                'featured'         => ($this->getRequest()->getParam('featured', null) == 'on' ? 1 : 0),
                'deleted'         => ($this->getRequest()->getParam('deleted', null) == 'on' ? 1 : 0)
            );
            $filters = array(
                '*' => array(
                    'StripTags',
                    'StringTrim'
                ),
                'featured' => 'Digits',
                'deleted' => 'Digits'
            );
            $validators = array(
                'title' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 100)),
                    'messages' => array(
                        'Edit Link Error : Title must be between 0 and 100 chars.'
                    )
                ),
                'link' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 100)),
                    'messages' => array(
                        'Edit Link Error : Link must be between 0 and 100 chars.'
                    )
                ),
                'description' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 500)),
                    'messages' => array(
                        'Edit Link Error : Description must be between 0 and 100 chars.'
                    )
                ),
                'featured' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Link Error : Featured must be true or false'
                    )
                ),
                'deleted' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Link Error : Deleted must be true or false'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_Link::addLink(
                    $input->title,
                    $input->link,
                    $input->description,
                    $input->featured,
                    $input->deleted
                );
                if ($result !== false) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Link has been added');
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "links", 'admin', array());
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error adding link');
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple("add", "links", 'admin', array());
                }
            } else {
                $errors = array();
                foreach($input->getMessages() as $message) {
                    foreach($message as $error) {
                        $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                    }
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("add", "links", 'admin', array());
            }
        }
        $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
        $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
        $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
    }

}