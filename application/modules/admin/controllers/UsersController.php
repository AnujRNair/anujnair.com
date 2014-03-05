<?php

class Admin_UsersController extends PageController {

    public function indexAction() {
        $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
        $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
        $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
        $this->view->users = Factory_User::getAllUsers();
    }

    public function editAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'userId'        => $this->getRequest()->getParam('id', 0),
                'username'      => $this->getRequest()->getParam('username', null),
                'firstName'     => $this->getRequest()->getParam('firstName', null),
                'lastName'      => $this->getRequest()->getParam('lastName', null),
                'email'         => $this->getRequest()->getParam('email', null),
                'website'       => $this->getRequest()->getParam('website', null),
                'regIp'         => $this->getRequest()->getParam('regIp', null),
                'regDate'       => $this->getRequest()->getParam('regDate', null),
                'lastLoginIp'   => $this->getRequest()->getParam('lastLoginIp', null),
                'lastLoginDate' => $this->getRequest()->getParam('lastLoginDate', null),
                'active'        => ($this->getRequest()->getParam('active', null) == 'on' ? 1 : 0),
                'validated'     => ($this->getRequest()->getParam('validated', null) == 'on' ? 1 : 0)
            );
            $filters = array(
                '*' => array(
                    'StripTags',
                    'StringTrim'
                ),
                'userId' => 'Digits',
                'username' => array(
                    new Zend_Filter_Alnum(false)
                ),
                'firstName' => array(
                    new Zend_Filter_Alpha(false)
                ),
                'lastName' => array(
                    new Zend_Filter_Alpha(false)
                ),
                'active' => 'Digits',
                'validated' => 'Digits'
            );
            $validators = array(
                'userId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Edit User Error : User ID must be greater than 0'
                    )
                ),
                'username' => array(
                    'presence' => 'required',
                    new Zend_Validate_Alnum(),
                    'messages' => array(
                        'Edit User Error : Username must be Alpha Numeric'
                    )
                ),
                'firstName' => array(
                    'presence' => 'required',
                    new Zend_Validate_Alpha(),
                    'messages' => array(
                        'Edit User Error : First name must be Alpha'
                    )
                ),
                'lastName' => array(
                    'presence' => 'required',
                    new Zend_Validate_Alpha(),
                    'messages' => array(
                        'Edit User Error : Last Name must be Alpha'
                    )
                ),
                'email' => array(
                    'presence' => 'required',
                    new Zend_Validate_EmailAddress(),
                    'messages' => array(
                        'Edit User Error : Email Address must be a valid Email'
                    )
                ),
                'website' => array(
                    'presence' => 'optional',
                    'allowEmpty' => true
                ),
                'regIp' => array(
                    'presence' => 'required',
                    new Zend_Validate_Regex(array('pattern' => '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/')),
                    'messages' => array(
                        'Edit User Error : Registration IP must be a valid IP address'
                    )
                ),
                'regDate' => array(
                    'presence' => 'required',
                    new Zend_Validate_Date(array('format' => 'YYYY-MM-dd HH:mm:ss')),
                    'messages' => array(
                        'Edit User Error : Registration Date must be a valid Date'
                    )
                ),
                'lastLoginIp' => array(
                    'presence' => 'required',
                    new Zend_Validate_Regex(array('pattern' => '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/')),
                    'messages' => array(
                        'Edit User Error : Last Login IP must be a valid IP address'
                    )
                ),
                'lastLoginDate' => array(
                    'presence' => 'required',
                    new Zend_Validate_Date(array('format' => 'YYYY-MM-dd HH:mm:ss')),
                    'messages' => array(
                        'Edit User Error : Last Login Date must be a valid Date'
                    )
                ),
                'active' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit User Error : Active must be true or false'
                    )
                ),
                'validated' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit User Error : Validated must be true or false'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_User::editUser(
                    $input->userId,
                    $input->username,
                    $input->firstName,
                    $input->lastName,
                    $input->email,
                    $input->website,
                    $input->regIp,
                    $input->regDate,
                    $input->lastLoginIp,
                    $input->lastLoginDate,
                    $input->active,
                    $input->validated
                );
                if ($result) {
                    $this->view->successes = array('User has been updated');
                } else {
                    $this->view->errors = array('Error updating user');
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
            'userId' => $this->getRequest()->getParam('id', 0)
        );
        $filters = array(
            'userId' => 'Digits'
        );
        $validators = array(
            'userId' => array(
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
            $this->view->user = Factory_User::getUserByID($input->userId, 0);
            if (!$this->view->user) {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Find User Error: Could not find user');
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "users", 'admin', array());
            }
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "users", 'admin', array());
        }
    }

    public function changestateAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $params = array(
            'userId' => $this->getRequest()->getParam('id', 0),
            'method' => $this->getRequest()->getParam('method', null)
        );
        $filters = array(
            '*' => array(
                'StringTrim',
                'StripTags'
            ),
            'userId' => 'Digits'
        );
        $validators = array(
            'userId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find User Error : User ID must be greater than 0'
                )
            ),
            'method' => array(
                'presence' => 'required',
                new Zend_Validate_InArray(array('add', 'delete')),
                'messages' => array(
                    'Find User Error : Method needs to be Add or Delete'
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
                $result = Factory_User::setUserActiveStatus($input->userId, 0);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('User has been deleted');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error deleting user');
                }
            } elseif ($input->method == 'add') {
                $result = Factory_User::setUserActiveStatus($input->userId, 1);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('User has been added');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error adding user');
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "users", 'admin', array());
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "users", 'admin', array());
        }
    }

}