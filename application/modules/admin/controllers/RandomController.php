<?php

class Admin_RandomController extends PageController {

    public function indexAction() {
        $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
        $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
        $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
        $this->view->randomPosters = Factory_User::getAllRandomPosters(0);
    }

    public function editAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'randomPosterId' => $this->getRequest()->getParam('id', 0),
                'name'           => $this->getRequest()->getParam('name', null),
                'email'          => $this->getRequest()->getParam('email', null),
                'website'        => $this->getRequest()->getParam('website', null),
                'ip'             => $this->getRequest()->getParam('ip', null),
                'useragent'      => $this->getRequest()->getParam('useragent', null),
                'creationDate'   => $this->getRequest()->getParam('creationDate', null),
                'deleted'        => ($this->getRequest()->getParam('deleted', null) == 'on' ? 1 : 0)
            );
            $filters = array(
                '*' => array(
                    'StripTags',
                    'StringTrim'
                ),
                'randomPosterId' => 'Digits',
                'name' => array(
                    new Zend_Filter_Alnum(true),
                ),
                'deleted' => 'Digits'
            );
            $validators = array(
                'randomPosterId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Edit Random Poster Error : Random Poster ID must be greater than 0'
                    )
                ),
                'name' => array(
                    'presence' => 'required',
                    new Zend_Validate_Alnum(true),
                    'messages' => array(
                        'Edit Random Poster Error : Username must be Alpha Numeric'
                    )
                ),
                'email' => array(
                    'presence' => 'required',
                    new Zend_Validate_EmailAddress(),
                    'messages' => array(
                        'Edit Random Poster Error : Email Address must be a valid Email'
                    )
                ),
                'website' => array(
                    'presence' => 'optional',
                    'allowEmpty' => true,
                    'messages' => array(
                        'Edit Random Poster Error : Website not required'
                    )
                ),
                'ip' => array(
                    'presence' => 'required',
                    new Zend_Validate_Regex(array('pattern' => '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/')),
                    'messages' => array(
                        'Edit Random Poster Error : IP must be a valid IP address'
                    )
                ),
                'useragent' => array(
                    'presence' => 'optional',
                    'allowEmpty' => true,
                    'messages' => array(
                        'Edit Random Poster Error : Useragent not required'
                    )
                ),
                'creationDate' => array(
                    'presence' => 'required',
                    new Zend_Validate_Date(array('format' => 'YYYY-MM-dd HH:mm:ss')),
                    'messages' => array(
                        'Edit Random Poster Error : Creation Date must be a valid Date'
                    )
                ),
                'deleted' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Random Poster Error : Deleted must be true or false'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_User::editRandomPoster(
                    $input->randomPosterId,
                    $input->name,
                    $input->email,
                    $input->website,
                    ip2long($input->ip),
                    $input->useragent,
                    $input->creationDate,
                    $input->deleted
                );
                if ($result) {
                    $this->view->successes = array('Random Poster has been updated');
                } else {
                    $this->view->errors = array('Error updating Random Poster');
                }
            } else {
                $errors = array();
                foreach($input->getMessages() as $message) {
                    foreach($message as $error) {
                        $errors[] = $error;
                    }
                }
                $this->view->errors = $errors;
            }
        }
        $params = array(
            'randomPosterId' => $this->getRequest()->getParam('id', 0)
        );
        $filters = array(
            'randomPosterId' => 'Digits'
        );
        $validators = array(
            'randomPosterId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Random Poster Error : Random Poster ID must be greater than 0'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            $this->view->randomPoster = Factory_User::getRandomPosterByID($input->randomPosterId, 0);
            if (!$this->view->randomPoster) {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Find Random Poster Error: Could not find Random Poster');
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "random", 'admin', array());
            }
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "random", 'admin', array());
        }
    }

    public function changestateAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $params = array(
            'randomPosterId' => $this->getRequest()->getParam('id', 0),
            'method' => $this->getRequest()->getParam('method', null)
        );
        $filters = array(
            '*' => array(
                'StringTrim',
                'StripTags'
            ),
            'randomPosterId' => 'Digits'
        );
        $validators = array(
            'randomPosterId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Random Poster Error : Random Poster ID must be greater than 0'
                )
            ),
            'method' => array(
                'presence' => 'required',
                new Zend_Validate_InArray(array('add', 'delete')),
                'messages' => array(
                    'Find Random Poster Error : Method needs to be Add or Delete'
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
                $result = Factory_User::setRandomPosterActiveStatus($input->randomPosterId, 1);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Random Poster has been deleted');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error deleting Random Poster');
                }
            } elseif ($input->method == 'add') {
                $result = Factory_User::setRandomPosterActiveStatus($input->randomPosterId, 0);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Random Poster has been added');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error adding Random Poster');
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "random", 'admin', array());
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "random", 'admin', array());
        }
    }

}