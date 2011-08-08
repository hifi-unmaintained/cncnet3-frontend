<?php

/*
 * Copyright (c) 2011 Toni Spets <toni.spets@iki.fi>
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

class CnCNet_Room extends CnCNet_Db_Table_Abstract
{
    protected $_name = 'rooms';
    private $hashalgo = 'sha1';

    public function create ( array $game, array $player )
    {
        try 
        {
            $pwhash = hash( $this->hashalgo, $game['pass'] );
            
            $_game = array
            (
                'game_id'   => $game['game'],
                'title'     => "{$player['nickname']}'s Room",
                'player_id' => $player['id'],
                'max'       => $game['players'],
                'created'   => date('Y-m-d H:i:s'),
                'latestart' => $game['late'],
                'password'  => $pwhash,
            );
            
            $room_id = $this->insert( $_game );
            

            $this->join( $room_id, $player['id'], 
                strcmp ( $game['pass'], "" ) == 0 ? "" : $pwhash );

            return $room_id;
        }
        catch (Exception $e) 
        {
            if ($room_id) 
            {
                $this->delete($this->getAdapter()->quoteInto('id = ?', $room_id));
            }
            return -999;
        }
    }

    public function join ( $room_id, $player_id, $password = "" )
    {
        $r = $this->select( )->where( 'id = ?', $room_id )->fetchRow( );
        if ( strcmp( $r['password'], hash( $this->hashalgo, $password ) ) == 0 
            || strcmp ( $r['password'], "" ) == 0 )
        {
            $this->getAdapter( )->insert( 'room_players', array
            (
                'room_id'   => $room_id,
                'player_id' => $player_id
            ) );
            
            return true;
        }
        return false;
    }
    
    public function leave ( $room_id, $player_id )
    {
        $where[] = $this->getAdapter()->quoteInto('player_id = ?', $player_id );
        $where[] = $this->getAdapter()->quoteInto('room_id = ?', $room_id );
        
        $this->getAdapter( )->delete ( 'room_players', $where );
        
        return true; // TODO: catch errors
    }
    
    public function lst ( $room = -1 )
    {
        // TODO: Reduce this to two queries as opposed to O(n) queries.
        
        $return = array( );
        
        $q = $this->select(
            CnCNet_Db_Table_Abstract::SELECT_WITH_FROM_PART, 
            array( 'id', 'title', 'game_id', 'max', 'password', 'latestart' )
        );
        if ( $room != -1 )
            $q->where( 'id = ?', $room );
            
        $rooms = $this->fetchAll( $q );
        foreach ( $rooms as $r )
        {
            $members = $this->getAdapter( )->select( )->from( 'room_players' );
                
            $members->where( 'room_id = ?', "" . $r['id'] );
            $members->join( 'players', 'players.id = room_players.player_id');
                
            $users = $this->getAdapter( )->fetchAll( $members );
            
            
            $u_out = array( );
            foreach ( $users as $u )
                $u_out[$u['player_id']] = $u['nickname'];
            
            $return[$r['id']] = array( 
                'id' => $r['id'],
                'name' => $r['title'],
                'game' => $r['game_id'],
                'players' => $r['max'],
                'pass' => !( strcmp( $r['password'], "" ) == 0 ),
                'late' => $r['latestart'],
                'users' => $u_out
            );
        }
        
        return $return;
    }
}
