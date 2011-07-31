<?php

class PlayerController extends CnCNet_Controller_Action
{
    public function _init()
    {
        $this->player = new CnCNet_Player();
    }

    public function indexAction()
    {
    }

    public function loginAction()
    {
        if ($this->session->player_id) {
            return $this->_forward('index', 'rooms');
        }

        $nickname = $this->_getParam('nickname');

        if (!$nickname || strlen($nickname) < 3) {
            $this->view->message = 'Nickname needs to be at least 3 characters.';
            return;
        }

        if (strlen($nickname) > 12) {
            $this->view->message = 'Oh come on!';
            return;
        }

        try {
            $player_id = $this->player->insert(array(
                'nickname'  => $nickname,
                'ip'        => $_SERVER['REMOTE_ADDR'],
                'port'      => 8054,
                'created'   => date('Y-m-d H:i:s'),
                'active'    => date('Y-m-d H:i:s')
            ));

            $this->session->player_id = $player_id;

            $this->_forward('index', 'rooms');
        } catch(Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }
}
