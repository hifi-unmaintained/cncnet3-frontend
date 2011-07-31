<?php

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
