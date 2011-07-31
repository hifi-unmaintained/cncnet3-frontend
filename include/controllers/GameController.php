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

class GameController extends CnCNet_Controller_Action
{
    public function preDispatch()
    {
        if (!$this->session->player_id)
            return $this->_forward('init', 'index');

        if ($this->session->room_id != -1)
            return $this->_forward('index', 'rooms');
    }

    public function _init()
    {
        $this->game = new CnCNet_Game();
        $this->view->games = array();

        foreach ($this->game->select() as $row) {
            $this->view->games[] = $row->toArray();
        }
        $this->view->refresh = 5;
    }

    public function indexAction()
    {
    }

    public function selectAction()
    {
        $game_id = $this->_getParam('game_id');

        foreach ($this->view->games as $game) {
            if ($game['id'] == $game_id) {
                $player = Zend_Registry::get('player');

                try {
                    $room = new CnCNet_Room();
                    $this->session->room_id = $room->create($game, $player);
                    unset($this->view->games);
                    $room = $this->db->fetchRow(
                        $this->db->select()
                                 ->from('rooms')
                                 ->join('games', 'games.id = rooms.game_id', array('game' => 'protocol'))
                                 ->where('rooms.id = ?', $this->session->room_id)
                    );
                    Zend_Registry::set('room', $room);
                    return $this->_forward('index', 'room');
                } catch(Exception $e) {
                    $this->view->error = $e->getMessage();
                    return;
                }
            }
        }
    }

    public function cancelAction()
    {
        unset($this->session->room_id);
        $this->_forward('init', 'index');
    }
}
