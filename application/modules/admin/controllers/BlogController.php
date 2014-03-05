<?php

class Admin_BlogController extends PageController {

    public function indexAction() {
        $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
        $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
        $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
        $this->view->blogs = Factory_Blog::getAllBlogs(1, 100, true);
    }

    public function changestateAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $params = array(
            'blogId' => $this->getRequest()->getParam('id', 0),
            'method' => $this->getRequest()->getParam('method', null)
        );
        $filters = array(
            '*' => array(
                'StringTrim',
                'StripTags'
            ),
            'blogId' => 'Digits'
        );
        $validators = array(
            'blogId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Find Blog Error : Blog ID must be greater than 0'
                )
            ),
            'method' => array(
                'presence' => 'required',
                new Zend_Validate_InArray(array('add', 'delete')),
                'messages' => array(
                    'Find Blog Error : Method needs to be Add or Delete'
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
                $result = Factory_Blog::setBlogDeletedStatus($input->blogId, 1);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Blog has been deleted');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error deleting blog');
                }
            } elseif ($input->method == 'add') {
                $result = Factory_Blog::setBlogDeletedStatus($input->blogId, 0);
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Blog has been added');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error adding blog');
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'admin', array());
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'admin', array());
        }
    }

    public function addAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'title'           => $this->getRequest()->getParam('blogTitle', null),
                'subtitle'        => $this->getRequest()->getParam('blogSubTitle', null),
                'contents'        => $this->getRequest()->getParam('contents', null),
                'disableComments' => ($this->getRequest()->getParam('disableComments', null) == 'on' ? 1 : 0),
                'deleted'         => ($this->getRequest()->getParam('deleted', null) == 'on' ? 1 : 0)
            );
            $filters = array(
                '*' => array(
                    'StringTrim',
                    new Zend_Filter_HtmlEntities(array('quotestyle' => ENT_NOQUOTES))
                ),
                'deleted' => 'Digits'
            );
            $validators = array(
                'title' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 100)),
                    'messages' => array(
                        'Add Blog Error : Title must be between 0 and 100 characters long'
                    )
                ),
                'subtitle' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 200)),
                    'messages' => array(
                        'Add Blog Error : SubTitle must be between 0 and 200 characters long'
                    )
                ),
                'contents' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 15000)),
                    'messages' => array(
                        'Add Blog Error : Contents must be between 0 and 15000 characters long'
                    )
                ),
                'disableComments' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Add Blog Error : Disable Comments must be true or false'
                    )
                ),
                'deleted' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Add Blog Error : Deleted must be true or false'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_Blog::addBlog(
                    $input->title,
                    $input->subtitle,
                    $input->getUnescaped("contents"),
                    $input->disableComments,
                    $input->deleted
                );
                if ($result != false) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Blog entry has been added');
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'admin', array());
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error adding blog entry');
                    $this->getHelper('Redirector')->setExit(true)->gotoSimple("add", "blog", 'admin', array());
                }
            } else {
                $errors = array();
                foreach($input->getMessages() as $message) {
                    foreach($message as $error) {
                        $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                    }
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("add", "blog", 'admin', array());
            }
        }
        $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
        $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
        $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
    }

    public function editAction() {
        if ($this->getRequest()->isPost()) {
            $params = array(
                'blogId'          => $this->getRequest()->getParam('id', 0),
                'title'           => $this->getRequest()->getParam('blogTitle', null),
                'subtitle'        => $this->getRequest()->getParam('blogSubTitle', null),
                'contents'        => $this->getRequest()->getParam('contents', null),
                'creationDate'    => $this->getRequest()->getParam('creationDate', null),
                'disableComments' => ($this->getRequest()->getParam('disableComments', null) == 'on' ? 1 : 0),
                'deleted'         => ($this->getRequest()->getParam('deleted', null) == 'on' ? 1 : 0)
            );
            $filters = array(
                '*' => array(
                    'StringTrim',
                    new Zend_Filter_HtmlEntities(array('quotestyle' => ENT_NOQUOTES))
                ),
                'blogId' => 'Digits',
                'disableComments' => 'Digits',
                'deleted' => 'Digits'
            );
            $validators = array(
                'blogId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Edit Blog Error : Blog ID must be greater than 0'
                    )
                ),
                'title' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 100)),
                    'messages' => array(
                        'Edit Blog Error : Title must be between 0 and 100 characters long'
                    )
                ),
                'subtitle' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 200)),
                    'messages' => array(
                        'Edit Blog Error : SubTitle must be between 0 and 200 characters long'
                    )
                ),
                'contents' => array(
                    'presence' => 'required',
                    new Zend_Validate_StringLength(array('min' => 0, 'max' => 15000)),
                    'messages' => array(
                        'Edit Blog Error : Contents must be between 0 and 15000 characters long'
                    )
                ),
                'creationDate' => array(
                    'presence' => 'required',
                    new Zend_Validate_Date(array('format' => 'YYYY-MM-dd HH:mm:ss')),
                    'messages' => array(
                        'Edit Blog Error : Creation Date must be a valid Datetime'
                    )
                ),
                'disableComments' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Blog Error : Disable Comments must be true or false'
                    )
                ),
                'deleted' => array(
                    'presence' => 'required',
                    new Zend_Validate_Between(array('min' => 0, 'max' => 1, 'inclusive' => true)),
                    'messages' => array(
                        'Edit Blog Error : Deleted must be true or false'
                    )
                )
            );
            $options = array(
                'missingMessage' => 'You must submit a valid %field%.',
                'notEmptyMessage' => 'You must submit a valid %field%.'
            );
            $input = new Zend_Filter_Input($filters, $validators, $params, $options);
            if ($input->isValid()) {
                $result = Factory_Blog::editBlog(
                    $input->blogId,
                    $input->title,
                    $input->subtitle,
                    $input->getUnescaped("contents"),
                    $input->creationDate,
                    $input->disableComments,
                    $input->deleted
                );
                if ($result) {
                    $this->getHelper('FlashMessenger')->setNamespace('successes')->addMessage('Blog has been updated');
                } else {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Error updating blog');
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "blog", 'admin', array('id' => $input->blogId));
            } else {
                $errors = array();
                foreach($input->getMessages() as $message) {
                    foreach($message as $error) {
                        $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                    }
                }
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "blog", 'admin', array('id' => $params['blogId']));
            }
        }
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
                    'Find Blog Error : Blog ID must be greater than 0'
                )
            )
        );
        $options = array(
            'missingMessage' => 'You must submit a valid %field%.',
            'notEmptyMessage' => 'You must submit a valid %field%.'
        );
        $input = new Zend_Filter_Input($filters, $validators, $params, $options);
        if ($input->isValid()) {
            $this->view->blog = Factory_Blog::getBlogByID($input->blogId, 0);
            if (!$this->view->blog) {
                $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage('Find Blog Error: Could not find blog');
                $this->getHelper('Redirector')->setExit(true)->gotoSimple("index", "blog", 'admin', array());
            } else {
                $this->view->warnings = $this->getHelper('FlashMessenger')->setNamespace('warnings')->getMessages();
                $this->view->successes = $this->getHelper('FlashMessenger')->setNamespace('successes')->getMessages();
                $this->view->errors = $this->getHelper('FlashMessenger')->setNamespace('errors')->getMessages();
                $this->view->allTags = Factory_Tag::getAllTags(false);
                $this->view->unusedTags = Factory_Tag::getUsusedBlogTags($input->blogId);
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

    public function addtagAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $params = array(
            'blogId' => $this->getRequest()->getParam('id', 0),
            'tagName' => $this->getRequest()->getParam('newTagText', 0)
        );
        $filters = array(
            '*' => array(
                'StripTags',
                'StringTrim'
            ),
            'blogId' => 'Digits',
            'tagName' => new Zend_Filter_Alnum(true)
        );
        $validators = array(
            'blogId' => array(
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
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "blog", 'admin', array('id' => $input->blogId));
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "blog", 'admin', array('id' => $params['blogId']));
        }
    }

    public function removetagAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $params = array(
            'blogId' => $this->getRequest()->getParam('id', 0),
            'removeTagId' => $this->getRequest()->getParam('removeTagId', 0)
        );
        $filters = array(
            '*' => array(
                'StripTags',
                'StringTrim'
            ),
            'blogId' => 'Digits',
            'removeTagId' => 'Digits'
        );
        $validators = array(
            'blogId' => array(
                'presence' => 'required',
                new Zend_Validate_GreaterThan(0),
                'messages' => array(
                    'Remove Tag Error : Blog ID must be greater than 0'
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
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "blog", 'admin', array('id' => $input->blogId));
        } else {
            foreach($input->getMessages() as $message) {
                foreach($message as $error) {
                    $this->getHelper('FlashMessenger')->setNamespace('errors')->addMessage($error);
                }
            }
            $this->getHelper('Redirector')->setExit(true)->gotoSimple("edit", "blog", 'admin', array('id' => $params['blogId']));
        }
    }

    public function assigntagAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isPost()) {
            $params = array(
                'blogId' => $this->getRequest()->getParam('blogId', 0),
                'tagId' => $this->getRequest()->getParam('tagId', 0),
                'adding' => $this->getRequest()->getParam('adding', null)
            );
            $filters = array(
                '*' => array(
                    'StripTags',
                    'StringTrim'
                ),
                'blogId' => 'Digits',
                'tagId' => 'Digits',
                'adding' => 'Digits'
            );
            $validators = array(
                'blogId' => array(
                    'presence' => 'required',
                    new Zend_Validate_GreaterThan(0),
                    'messages' => array(
                        'Assign Tag Error : Blog ID must be greater than 0'
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
                    $result = Factory_Tag::assignBlogTagMap($input->blogId, $input->tagId);
                    if ($result > 0) {
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
                    $result = Factory_Tag::setBlogTagMapDeletedStatus($input->blogId, $input->tagId, 1);
                    if ($result > 0) {
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
                    'message' => 'Invalid Blog, Tag ID or Adding Method'
                )));
            }
        }
    }

}