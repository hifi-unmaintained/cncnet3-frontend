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

class RoomsController extends CnCNet_Controller_Action
{
    public function preDispatch()
    {
        if (!$this->session->player_id)
            return $this->_forward('init', 'index');
    }

    public function _init()
    {
        $this->room = new CnCNet_Room();
        $this->player = new CnCNet_Player();

        $this->view->rooms = array();
        $select = $this->room->select()
                             ->join('games', 'games.id = rooms.game_id', array('game' => 'games.protocol'))
                             ->join('players', 'players.id = rooms.player_id', '')
                             ->join('room_players', 'room_players.player_id = players.id AND room_players.room_id = rooms.id', '')
                             ->where('rooms.started IS NULL OR rooms.started > ?', date('Y-m-d H:i:s', strtotime('-5 minutes')))
                             ->where('players.active > ? OR rooms.started NOT NULL', date('Y-m-d H:i:s', strtotime('-30 seconds')));

        foreach ($select->fetchAll() as $row) {
            $data = $row->toArray();
            $data['players'] = array();

            $pselect = $this->player->select()
                                    ->join('room_players', '', array('ready'))
                                    ->where('room_players.player_id = players.id')
                                    ->where('room_players.room_id = ?', $data['id']);

            foreach ($pselect as $prow) {
                $parr = $prow->toArray();
                $pdata = array(
                    'id'        => $parr['id'],
                    'nickname'  => $parr['nickname'],
                    'ready'     => (bool)$parr['ready'],
                    'owner'     => $parr['id'] == $data['player_id']
                );
                $data['players'][] = $pdata;
            }

            $this->view->rooms[] = $data;
        }

        $this->view->refresh = 2;
    }

    public function indexAction()
    {
    }

    public function joinAction()
    {
        $row = $this->room->select()->where('id = ? AND rooms.started IS NULL', $this->_getParam('room'))->fetchRow();
        if ($row) {
            $room = $row->toArray();

            $pselect = $this->player->select()
                                    ->join('room_players', '', '')
                                    ->where('room_players.player_id = players.id')
                                    ->where('room_players.room_id = ?', $room['id']);

            if (count($pselect) < $room['max']) {
                try {
                    $this->room->join($room['id'], $this->session->player_id);
                    $this->session->room_id = $room['id'];
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
                }
            }
        }
    }

    public function newAction()
    {
        $this->session->room_id = -1;
        $this->_forward('init', 'index');
    }

    public function logoutAction()
    {
        unset($this->session->player_id);
        unset($this->session->room_id);
        unset($this->view->refresh);
        $this->_forward('init', 'index');
    }
}
