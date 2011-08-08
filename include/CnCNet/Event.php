<?php

/*
 * Copyright (c) 2011 John Sanderson <js@9point6.com>
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

class CnCNet_Event extends CnCNet_Db_Table_Abstract
{
    protected $_name = 'events';
    
    /**
     * Adds an event.
     *
     * @param string $type Type of event
     * @param int[] $players IDs of players to recieve event
     * @param int $room_id ID of room relevant to event
     * @param int $user_id ID of player relevant to event
     * @param string $param extra data.
     * @return boolean Success
     */
    public function add ( $type, $players, $room_id, $user_id = null, $param = null )
    {
        if ( is_array ( $param ) )
            $param = serialize ( $param );
    
        $new_event = array
        (
            'player_id' => 0,
            'type' => $type,
            'time' => date('Y-m-d H:i:s'),
            'room' => $room_id,
            'user' => $user_id,
            'param' => $param
        );
        
        foreach ( $players as $pid => $nickname )
        {
            $new_event['player_id'] = $pid;
            $e_id = $this->insert( $new_event );
        }
        
        return true;
    }
    
    public function get ( $player_id, $since = 0 )
    {
        $q = $this->select( 
            CnCNet_Db_Table_Abstract::SELECT_WITH_FROM_PART, 
            array( 'type', 'time', 'room', 'user', 'param' ) 
        )->where( 'player_id = ?', $player_id )->where( 'time > ?', $since );
        
        $return = $this->fetchAll( $q );
        $actual_return = array ( );
        
        foreach ( $return as $ret )
        {
            if ( strcmp ( $ret['type'], 'room' ) == 0 )
                $ret['param'] = unserialize( $ret['param'] );
                
            $actual_return[] = array
            (
                'type' => $ret['type'],
                'time' => $ret['time'],
                'room' => $ret['room'],
                'user' => $ret['user'],
                'param' => $ret['param']
            );
        }
        
        if ( empty( $actual_return ) )
            return array ( array ( 'type' => 'noevent' ) );
        
        return $actual_return;
    }
}
