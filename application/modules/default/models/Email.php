<?php

class Email {

    // The Internal Zend Mail Variable
    private $_mail = null;

    // Parameters to fill the template with
    private $_params = null;

    // Subject Line of the email
    private $_subject = null;

    // The Email Template from files
    private $_template = null;


    public function __construct($emailTemplate) {
        $templateFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . $emailTemplate . '.phtml';

        if (file_exists($templateFile)) {
            $this->_template = file_get_contents($templateFile);
        } else {
            throw new Exception("Template $emailTemplate could not be found");
        }

        $config = Zend_Registry::get('config');
        $this->_mail = new Zend_Mail();
        $this->_mail->setDefaultFrom($config->email->defaultemail, $config->email->defaultname);
        $this->_mail->setDefaultReplyTo($config->email->defaultemail, $config->email->defaultname);
        $this->_mail->addHeader('X-Mailer', 'PHP/' . phpversion());
    }

    public function send($userData = null, $subject = null, $params = null) {
        if ($userData != null) {
            if (!$this->setUserData($userData)) {
                return false;
            }
        }
        if ($subject != null) {
            $this->setSubject($subject);
        }
        if ($params != null) {
            $this->setParams($params);
        }
        if (count($this->_mail->getRecipients()) == 0) {
            return false;
        }

        $this->fillTemplate();

        try {
            $this->_mail->setSubject($this->_subject);
            $this->_mail->setBodyHtml($this->_template);
            $this->_mail->setMessageId();
            $this->_mail->setDate();
            $this->_mail->send();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function setUserData($userData) {
        if ($userData instanceof EmailUser) {
            $this->_mail->addTo($options->getEmail(), $options->getName());
        } elseif (is_array($userData)) {
            foreach ($userData as $method => $user) {
                if ($user instanceof EmailUser) {
                    $fixedMethod = 'add' . ucfirst($method);
                    $this->_mail->{$fixedMethod}($user->getEmail(), $user->getName());
                } elseif (is_array($user)) {
                    foreach ($user as $newMethod => $newUser) {
                        $fixedMethod = 'add' . ucfirst($newMethod);
                        $this->_mail->{$fixedMethod}($newUser->getEmail(), $newUser->getName());
                    }
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }

    public function setParams($params) {
        $this->_params = $params;
    }

    public function setSubject($subject) {
        $this->_subject = $subject;
    }

    protected function fillTemplate() {
        foreach ($this->_params as $key => $param) {
            $this->_template = str_replace('{' . $key . '}', $param, $this->_template);
            $this->_subject = str_replace('{' . $key . '}', $param, $this->_subject);
        }
    }

}

class EmailUser {

    private $_email = null;
    private $_name = null;

    public function __construct($email, $name) {
        $this->_email = $email;
        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }

    public function getEmail() {
        return $this->_email;
    }

}