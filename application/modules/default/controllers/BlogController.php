<?php

class BlogController extends PageController {

    public function preDispatch() {
        parent::preDispatch();
        $this->view->headScript()->offsetSetFile(2, '/js/libs/jquery.syntax/jquery.syntax.min.js', 'text/javascript');
        $this->view->archive = Factory_Blog::getArchive(0);
        $this->view->tags = Factory_Tag::getTagSummary();
    }

    public function postDispatch() {
        $secureForm = md5(uniqid(rand(), true));
        $this->_session->secureForm = $secureForm;
        $this->view->secureForm = $secureForm;
    }

    public function indexAction() {
        $this->view->page = $this->getRequest()->getParam('page', 1);
        $this->view->blogs = Factory_Blog::getAllBlogs($this->view->page, 5, false);

        $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
        $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
        $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
    }

    public function articleAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'blogId'     => $this->getRequest()->getParam('blogId', null),
                'name'       => $this->getRequest()->getParam('name', null),
                'email'      => $this->getRequest()->getParam('email', null),
                'website'    => $this->getRequest()->getParam('website', null),
                'comment'    => $this->getRequest()->getParam('comment', null),
                'secureForm' => $this->getRequest()->getParam('secureForm', null)
            );
            $filters = array(
                '*' => array(
                    'StringTrim',
                    new Zend_Filter_HtmlEntities(array('quotestyle' => ENT_NOQUOTES))
                ),
                'blogId' => array(
                    'Digits'
                ),
                'secureForm' => array(
                    new Zend_Filter_PregReplace(array('match' => '/[^a-f0-9]/i', 'replace' => ''))
                )
            );
            $validators = array(
                'blogId' => array(
                    'presence' => 'required',
                    'Digits',
                    'allowEmpty' => false,
                    'messages' => array(
                        '(Error Code #0001) : An error occurred, please refresh the page and try again.'
                    )
                ),
                'name' => array(
                    'presence' => 'required',
                    new Zend_Validate_Regex('/^[a-z\s\']+$/i'),
                    new Zend_Validate_StringLength(array('min' => 2, 'max' => 50)),
                    'allowEmpty' => false,
                    'messages' => array(
                        'Your Name may only include letters and spaces.'
                    )
                ),
                'email' => array(
                    'presence' => 'required',
                    new Zend_Validate_EmailAddress(),
                    new Zend_Validate_StringLength(array('min' => 2, 'max' => 100)),
                    'allowEmpty' => false,
                    'messages' => array(
                        'You must submit a valid email address.'
                    )
                ),
                'website' => array(
                    'presence' => 'optional',
                    'allowEmpty' => true,
                    //new Zend_Validate_Hostname(),
                    new Zend_Validate_StringLength(array('min' => 5, 'max' => 100)),
                    'messages' => array(
                        'Please submit a valid website.'
                    )
                ),
                'comment' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 5, 'max' => 8000)),
                    'allowEmpty' => false,
                    'messages' => array(
                        'You must submit a valid comment.'
                    )
                ),
                'secureForm' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 31, 'max' => 33)),
                    new Zend_Validate_Identical(array('token' => $this->_session->secureForm, 'strict' => false)),
                    'messages' => array(
                        '(Error Code #0002) : An error occurred, please refresh the page and try again.'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_Comment::addRandomPosterComment(
                    $input->blogId,
                    $input->name,
                    $input->email,
                    $input->website,
                    $this->getRequest()->getClientIp(),
                    $this->_useragent->getUserAgent(),
                    $input->getUnescaped("comment")
                );
                if ($result !== false) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Comment has been successfully posted!');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('There was an error adding your comment, please try again');
                }
                $this->getHelper('Redirector')->setExit(true)->setGotoRoute(array(
                    'id' => $this->getRequest()->getParam('id', 0),
                    'title' => $this->getRequest()->getParam('title', null)
                ), 'blogArticle', true);
            } else {
                foreach($input->getMessages() as $message) {
                    foreach($message as $error) {
                        $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                    }
                }
                $this->getHelper('Redirector')->setExit(true)->setGotoRoute(array(
                    'id' => $this->getRequest()->getParam('id', 0),
                    'title' => $this->getRequest()->getParam('title', null)
                ), 'blogArticle', true);
            }
        }
        $id = $this->getRequest()->getParam('id', 0);
        $title = $this->getRequest()->getParam('title', null);
        if ((int)$id > 0) {
            $this->view->blog = Factory_Blog::getBlogById($id);
            if (empty($this->view->blog)) {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('You\'ve tried browsing to an invalid blog post, please try again.');
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'default', array());
            }
            if ($title != $this->view->urltitle($this->view->blog->title)) {
                $this->getResponse()->setRedirect(
                    $this->view->url(array(
                        'id' => $this->view->blog->blogId,
                        'title' => $this->view->urltitle($this->view->blog->title)
                    ), 'blogArticle', true),
                    301
                );
            }
            $this->view->comments = Factory_Comment::getCommentsByBlogId($id);
            $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
            $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
            $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
        } else {
            $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('You\'ve tried browsing to an invalid blog post, please try again.');
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'default', array());
        }
    }

    public function searchtagAction() {
        $this->_helper->viewRenderer('index');
        $this->view->selectedTagId = $this->getRequest()->getParam('id', null);
        if((int)$this->view->selectedTagId > 0) {
            $this->view->page = $this->getRequest()->getParam('page', 1);
            $this->view->blogs = Factory_Blog::getBlogsByTagId($this->view->selectedTagId, $this->view->page, 5);
            if (empty($this->view->blogs)) {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('No results found for that specific tag.');
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'default', array());
            }
        } else {
            $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('No results found for that specific tag.');
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'default', array());
        }
    }

}