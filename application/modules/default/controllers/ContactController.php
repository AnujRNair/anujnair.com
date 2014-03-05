<?php

class ContactController extends PageController {

    public function postDispatch() {
        $captcha = substr(md5(uniqid(rand(), true)), 0, 4);
        $this->_session->captcha = $captcha;
        $this->view->captcha = Factory_Contact::createCaptcha(100, 85, $captcha);

        $secureForm = md5(microtime());
        $this->_session->secureForm = $secureForm;
        $this->view->secureForm = $secureForm;
    }

    public function indexAction() {
        if ($this->getRequest()->isPost()) {
            $data = array(
                'name'    => $this->getRequest()->getParam('name'),
                'email'   => $this->getRequest()->getParam('email'),
                'message' => $this->getRequest()->getParam('message'),
                'captcha' => $this->getRequest()->getParam('captcha')
            );
            $filters = array(
                '*' => array(
                    'StripTags',
                    'StringTrim'
                )
            );
            $validators = array(
                'name'    => array(
                    'presence' => 'required',
                    new Zend_Validate_Alpha(true),
                    new Zend_Validate_StringLength(array('min' => 2, 'max' => 50)),
                    'messages' => array(
                        'Your name may only include letters and spaces.'
                    )
                ),
                'email'   => array(
                    'presence' => 'required',
                    new Zend_Validate_EmailAddress(),
                    new Zend_Validate_StringLength(array('min' => 2, 'max' => 100)),
                    'messages' => array(
                        'You must submit a valid email address.'
                    )
                ),
                'message' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 5, 'max' => 1000)),
                    'messages' => array(
                        'You must submit a valid message.'
                    )
                ),
                'captcha' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 4, 'max' => 4, 'inclusive' => true)),
                    new Zend_Validate_Identical(array('token' => $this->_session->captcha, 'strict' => false)),
                    'messages' => array(
                        '(Error Code #0001) : An error occurred, please refresh the page and try again.'
                    )
                )
            );
            $input = new Zend_Filter_Input($filters, $validators, $data);
            if ($input->isValid()) {
                $config = Zend_Registry::get('config');
                $email = new Email('contact');
                $userData = array(
                    'to' => new EmailUser($config->email->defaultemail, $config->email->defaultname)
                );
                $subject = '"' . $input->name . '" wants to contact you';
                $params = array(
                    'name'    => $input->name,
                    'email'   => $input->email,
                    'message' => preg_replace("/\n/", "<br />", $input->message)
                );

                if ($email->send($userData, $subject, $params)) {
                    $this->getHelper('flashMessenger')->setNamespace('contactMessages')->addMessage('Message Sent');
                } else {
                    $this->getHelper('flashMessenger')->setNamespace('contactErrors')->addMessage('Error sending
                    mail');
                }
            } else {
                foreach ($input->getMessages() as $message) {
                    foreach ($message as $error) {
                        $this->getHelper('flashMessenger')->setNamespace('contactErrors')->addMessage($error);
                    }
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "contact", 'default', array());
        }
        $this->view->errors = $this->getHelper('flashMessenger')->setNamespace('contactErrors')->getMessages();
        $this->view->messages = $this->getHelper('flashMessenger')->setNamespace('contactMessages')->getMessages();
    }

}