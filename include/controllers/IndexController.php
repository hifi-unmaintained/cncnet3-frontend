<?php

class IndexController extends CnCNet_Controller_Action
{
    public function indexAction()
    {
        $this->render('index');
    }

    public function initAction()
    {
        /* handle some JS routing here, this may be a bit wrong */

        if (isset($this->session->room_id) && $this->session->room_id == -1) {
            return $this->_forward('index', 'game');
        }

        if (isset($this->session->room_id) && $this->session->room_id > 0) {
            return $this->_forward('index', 'room');
        }

        if (isset($this->session->player_id)) {
            return $this->_forward('index', 'rooms');
        }

        return $this->_forward('index', 'player');
    }
}
