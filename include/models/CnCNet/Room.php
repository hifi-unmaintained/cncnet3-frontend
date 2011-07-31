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

class CnCNet_Room extends CnCNet_Db_Table_Abstract
{
    protected $_name = 'rooms';

    function create(array $game, array $player)
    {
        try {
            $room_id = $this->insert(array(
                'game_id'   => $game['id'],
                'title'     => "{$player['nickname']}'s Room",
                'player_id' => $player['id'],
                'max'       => 6,
                'created'   => date('Y-m-d H:i:s')
            ));

            $this->join($room_id, $player['id']);

            return $room_id;
        } catch (Exception $e) {
            if ($room_id) {
                $this->delete($this->getAdapter()->quoteInto('id = ?', $room_id));
            }
            return null;
        }
    }

    function join($room_id, $player_id)
    {
        $this->getAdapter()->insert('room_players', array(
            'room_id'   => $room_id,
            'player_id' => $player_id
        ));
    }
}
