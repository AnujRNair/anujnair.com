<?php

class Admin_CommentsController extends PageController {

    public function indexAction() {
        $params = array(
            'blogId' => $this->getRequest()->getParam('id', 0)
        );
        $filters = array(
            'blogId' => 'Digits'
        );
        $validators = array(
            'blogId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Blog Comment Error : Blog ID must be greater than 0'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            if ($this->getRequest()->isPost()) {
                $filters = array(
                    '*' => array(
                        'StringTrim',
                        new Zend_Filter_HtmlEntities(array('quotestyle' => ENT_NOQUOTES))
                    )
                );
                $validators = array(
                    'comment' => array(
                        'presence' => 'required',
                        new Zend_Validate_StringLength(array('min' => 5, 'max' => 8000)),
                        'allowEmpty' => false,
                        'messages' => array(
                            'Post Comment Error: You must submit a valid comment.'
                        )
                    )
                );
                $form = new Zend_Filter_Input($filters, $validators, $this->getRequest()->getParams(), $options);
                if ($form->isValid()) {
                    $result = Factory_Comment::addUserComment(
                        $input->blogId,
                        $this->view->userInfo->userId,
                        $form->getUnescaped("comment")
                    );
                    if ($result != false) {
                        $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Comment has been submitted!');
                        $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "comments", 'admin', array('id' => $input->blogId));
                    } else {
                        $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Post Comment Error: Error submitting comment');
                        $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "comments", 'admin', array('id' => $input->blogId));
                    }
                } else {
                    foreach($form->getMessages() as $message) {
                        foreach($message as $error) {
                            $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                        }
                    }
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "comments", 'admin', array('id' => $input->blogId));
                }
            }
            $this->view->comments = Factory_Comment::getCommentsByBlogId($input->blogId);
            $this->view->blog = Factory_Blog::getBlogByID($input->blogId);
            $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
            $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
            $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'admin', array());
        }
    }

    public function byuserAction() {
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
                    'Find User Comment Error : User ID must be greater than 0'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            $this->view->comments = Factory_Comment::getCommentsByUserId($input->userId);
            $this->view->userId = $input->userId;
            $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
            $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
            $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "users", 'admin', array());
        }
    }

    public function byrandomAction() {
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
                    'Find Random Poster Comment Error : Random Poster ID must be greater than 0'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            $this->view->comments = Factory_Comment::getCommentsByRandomPosterId($input->randomPosterId);
            $this->view->randomPosterId = $input->randomPosterId;
            $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
            $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
            $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
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
            'commentId' => $this->getRequest()->getParam('commentId', 0),
            'blogId' => $this->getRequest()->getParam('blogId'),
            'userId' => $this->getRequest()->getParam('userId'),
            'randomPosterId' => $this->getRequest()->getParam('randomPosterId'),
            'method' => $this->getRequest()->getParam('method', null)
        );
        $filters = array(
            '*' => array(
                'StringTrim',
                'StripTags'
            ),
            'commentId' => 'Digits',
            'blogId' => 'Digits',
            'userId' => 'Digits',
            'randomPosterId' => 'Digits'
        );
        $validators = array(
            'commentId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Blog Comment Error : Comment ID must be greater than 0'
                )
            ),
            'blogId' => array(
                'presence' => 'optional',
                'allowEmpty' => true,
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Blog Comment Error : Blog ID must be greater than 0'
                )
            ),
            'userId' => array(
                'presence' => 'optional',
                'allowEmpty' => true,
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find User Comment Error : User ID must be greater than 0'
                )
            ),
            'randomPosterId' => array(
                'presence' => 'optional',
                'allowEmpty' => true,
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Random Poster Comment Error : Random Poster ID must be greater than 0'
                )
            ),
            'method' => array(
                'presence' => 'required',
                new Zend_Validate_InArray(array('add', 'delete')),
                'messages' => array(
                    'Find Blog Comment Error : Method needs to be Add or Delete'
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
                $result = Factory_Comment::setCommentDeletedStatus($input->commentId, 1);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Comment has been deleted');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error deleting comment');
                }
            } elseif ($input->method == 'add') {
                $result = Factory_Comment::setCommentDeletedStatus($input->commentId, 0);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Comment has been added');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error adding comment');
                }
            }
            if ($input->blogId > 0) {
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "comments", 'admin', array('id' => $input->blogId));
            } elseif ($input->userId > 0) {
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("byuser", "comments", 'admin', array('id' => $input->userId));
            } elseif ($input->randomPosterId > 0) {
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("byrandom", "comments", 'admin', array('id' => $input->randomPosterId));
            }
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
        }
        $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'admin', array());
    }

    public function editAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'commentId'    => $this->getRequest()->getParam('commentId', 0),
                'blogId'       => $this->getRequest()->getParam('blogId', 0),
                'userId'       => $this->getRequest()->getParam('userId', null),
                'posterId'     => $this->getRequest()->getParam('posterId', null),
                'comment'      => $this->getRequest()->getParam('comment', null),
                'creationDate' => $this->getRequest()->getParam('creationDate', null),
                'deleted'      => ($this->getRequest()->getParam('deleted', null) == 'on' ? 1 : 0)
            );
            $filters = array(
                '*' => array(
                    'StringTrim',
                    new Zend_Filter_HtmlEntities(array('quotestyle' => ENT_NOQUOTES))
                ),
                'commentId' => 'Digits',
                'blogId' => 'Digits',
                'deleted' => 'Digits'
            );
            $validators = array(
                'commentId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Edit Comment Error : Comment ID must be greater than 0'
                    )
                ),
                'blogId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Edit Comment Error : Blog ID must be greater than 0'
                    )
                ),
                'userId' => array(
                    'presence' => 'optional',
                    'allowEmpty' => true,
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Edit Comment Error : User ID must be greater than 0'
                    )
                ),
                'posterId' => array(
                    'presence' => 'optional',
                    'allowEmpty' => true,
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Edit Comment Error : Poster ID must be greater than 0'
                    )
                ),
                'comment' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 8000)),
                    'messages' => array(
                        'Edit Comment Error : Comment must be Alpha Numeric'
                    )
                ),
                'creationDate' => array(
                    'presence' => 'required',
                    new Zend_Validate_Date(array('format' => 'YYYY-MM-dd HH:mm:ss')),
                    'messages' => array(
                        'Edit Comment Error : Creation Date must be a valid Datetime'
                    )
                ),
                'deleted' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Comment Error : Deleted must be true or false'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_Comment::editComment(
                    $input->commentId,
                    $input->blogId,
                    (empty($input->userId) ? null : $input->userId),
                    (empty($input->posterId) ? null : $input->posterId),
                    $input->getUnescaped("comment"),
                    $input->creationDate,
                    $input->deleted
                );
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Comment has been updated');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error updating comment');
                }
                if (($from = $this->getRequest()->getParam('from', null)) != null) {
                    $urlParts = explode('-', $from);
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple($urlParts[0], "comments", 'admin', array($urlParts[1] => $urlParts[2]));
                } else {
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "comments", 'admin', array('id' => $input->blogId));
                }
            } else {
                $errors = array();
                foreach($input->getMessages() as $message) {
                    foreach($message as $error) {
                        $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                    }
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "comments", 'admin', array('blogId' => $params['blogId'], 'commentId' => $params['commentId']));
            }
        }

        $params = array(
            'commentId' => $this->getRequest()->getParam('commentId', 0)
        );
        $filters = array(
            'commentId' => 'Digits'
        );
        $validators = array(
            'commentId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Comment Error : Comment ID must be greater than 0'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            $this->view->comment = Factory_Comment::getCommentById($input->commentId, true, 0);
            if (!$this->view->comment) {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Find Comment Error: Could not find comment');
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'admin', array());
            } else {
                $this->view->from = $this->getRequest()->getParam('from', '');
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
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'admin', array());
        }
    }

}