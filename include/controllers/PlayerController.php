<?php

/*
 * Copyright (c) 2011 Toni Spets <toni.spets@iki.fi>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

class PlayerController extends CnCNet_Controller_Action
{
    public function _init()
    {
        $this->player = new CnCNet_Player();
        $this->view->nickname = '';
        $this->view->port = 8054;
    }

    public function indexAction()
    {
    }

    public function loginAction()
    {
        if ($this->session->player_id) {
            return $this->_forward('index', 'rooms');
        }

        $this->view->nickname = $this->_getParam('nickname');
        $this->view->port = $this->_getParam('port');

        if (!$this->view->nickname || strlen($this->view->nickname) < 3) {
            $this->view->message = 'Nickname needs to be at least 3 characters.';
            return;
        }

        if (strlen($this->view->nickname) > 12) {
            $this->view->message = 'Oh come on!';
            return;
        }

        if (!is_numeric($this->view->port) || $this->view->port < 1024 || $this->view->port > 65535) {
            $this->view->message = 'You know what a port is, right?';
            return;
        }

        try {
            $player_id = $this->player->insert(array(
                'nickname'  => $this->view->nickname,
                'ip'        => $_SERVER['REMOTE_ADDR'],
                'port'      => $this->view->port,
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
