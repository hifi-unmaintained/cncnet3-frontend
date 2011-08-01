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

class LaunchController extends CnCNet_Controller_Action
{
    public function preDispatch()
    {
        if (!isset($this->session->room_id))
            return $this->_forward('index', 'rooms');

        $room = Zend_Registry::get('room');
        if (!$room['started'])
            return $this->_forward('index', 'room');
    }

    public function postDispatch()
    {
        if (!isset($this->session->room_id))
            $this->_forward('index', 'rooms');
    }

    public function indexAction()
    {
        $room = Zend_Registry::get('room');
        $player = Zend_Registry::get('player');
        $stmt = $this->db->query( $this->db->select()->from('players')->join('room_players', $this->db->quoteInto('room_players.room_id = ? AND room_players.player_id = players.id', $this->session->room_id)) );
        $ips = array();
        $lan_players = false;
        while ($row = $stmt->fetch()) {
            if ($row['id'] != $this->session->player_id) {
                if ($row['ip'] == $player['ip']) {
                    $lan_players = true;
                } else {
                    $ips[] = $row['ip'] . ($row['port'] != 8054 ? ':'.$row['port'] : '');
                }
            }
        }

        /* if we have people from the same address, enable LAN broadcasts for us */
        if ($lan_players) {
            $ips[] = '255.255.255.255';
        }

        $this->view->uri = $room['game'].'://'.implode(',', $ips);
    }

    public function closeAction()
    {
        unset($this->session->room_id);
    }
}
