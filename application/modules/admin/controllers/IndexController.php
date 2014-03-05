<?php

class Admin_IndexController extends PageController {

    public function indexAction() {
    }

    public function loginAction() {
        if ($this->getRequest()->isPost()) {
            $errors = array();
            $params = array(
                'username' => $this->getRequest()->getParam('username', null),
                'password' => $this->getRequest()->getParam('password', null)
            );
            $filters = array(
                '*' => array(
                    'StringTrim',
                    'StripTags'
                )
            );
            $validators = array(
                'username' => array(
                    'presence'   => 'required',
                    'allowEmpty' => false,
                    new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                    'messages'   => array(
                        'You must provide a valid Username.'
                    )
                ),
                'password' => array(
                    'presence'   => 'required',
                    'allowEmpty' => false,
                    new Zend_Validate_StringLength(array('min' => 3, 'max' => 100)),
                    'messages'   => array(
                        'You must provide a valid Password'
                    )
                )
            );
            $options = array(
                'missingMessage'  => 'You must provide a valid %field%.',
                'notEmptyMessage' => 'You must provide a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_User::authenticate($input->username, $input->password);
                if ($result === true) {
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple("index");
                } else {
                    $errors[] = $result;
                }
            } else {
                foreach ($input->getMessages() as $message) {
                    foreach ($message as $error) {
                        $errors[] = $error;
                    }
                }
            }
            $this->view->errors = $errors;
        }
    }

    public function logoutAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_auth->clearIdentity();
        $this->getHelper('Redirector')->setExit(true)->gotoSimple("login", "index", 'admin', array());
    }

}