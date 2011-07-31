<?php

abstract class CnCNet_Controller_Action extends Zend_Controller_Action
{
    public function init()
    {
        $this->db = Zend_Registry::get('db');
        $this->session = Zend_Registry::get('session');
        $this->_helper->viewRenderer->setRender('json', null, true);
        $this->view->view = strtolower(str_replace('Controller', '', get_class($this)));
        if (method_exists($this, '_init')) {
            $this->_init();
        }
    }
}
