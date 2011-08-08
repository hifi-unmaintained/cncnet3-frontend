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

class CnCNet_Rest
{
    ///////////////////////////////////////////////////////////////////////////
    // Users
    ///////////////////////////////////////////////////////////////////////////
    public function test()
    {
        return true;
    }
    
    /**
     * /cncnet/register/user123/paSsw0rd./email
     *
     * @param string $uname Username
     * @param string $pw Password
     * @param string $email Email Address
     * @return string Session Key
     */
    public function register ( $uname, $pw, $email ) 
    {
        $player = new CnCNet_Player( );
        
        $valid = true; // TODO: Validation
        
        if ( $valid )
        {  
            $p_id = $player->register
            ( 
                $uname, 
                $pw, 
                $email, 
                $_SERVER['REMOTE_ADDR']
            );
            
            if ( $p_id > 0 )
                return $this->login( $uname, $pw );
            $return['errors'][] = $this->error_code ( -4 );
        }
        
        $return['success'] = false;
        return $return;
    }
    
    /**
     * /cncnet/login/user123/paSsw0rd.
     *
     * @param string $uname Username
     * @param string $pw Password
     * @return string Session Key
     */
    public function login ( $uname, $pw ) 
    {
        $player = new CnCNet_Player( );
        
        $s_key = $player->login
        ( 
            $uname, 
            $pw, 
            $_SERVER['REMOTE_ADDR']
        );
        
        $this->join ( $s_key, 0 );

        if ( is_int ( $s_key ) && $s_key < 0 )
            return array( 'success' => false, 'errors' => array( $this->error_code ( $s_key ) ) );
        else
            return array( 'success' => true, 's_key' => $s_key );
    }
    
    /**
     * /cncnet/logout/GFsd4jf5-9lcld34f==
     *
     * @param string $s_key Session Key
     * @return boolean Success
     */
    public function logout ( $s_key ) 
    {
        if ( !$this->validate_session ( $s_key ) )
            return array( 'success' => false, 'errors' => array( $this->error_code ( -3 ) ) );
            
        $player = new CnCNet_Player( );
        $p_id = $player->get_id( $s_key );
        
        if ( $p_id < 0 )
            return array( 'success' => false, 'errors' => array( $this->error_code ( $p_id ) ) );
        
        return array ( 'success' => $player->logout( $p_id ) );
    }
    
    /**
     * /cncnet/change_pw/GFsd4jf5-9lcld34f==/w0rd.paSs/paSsw0rd.
     *
     * @param string $s_key Session Key
     * @param string $new_pw New Password
     * @param string $old_pw Old Password
     * @return boolean Success
     */
    public function change_pw ( $s_key, $new_pw, $old_pw ) 
    {
        if ( !$this->validate_session ( $s_key ) )
            return array ( 'success' => false, 'errors' => array ( $this->error_code ( -3 ) ) );
            
        $player = new CnCNet_Player( );
        $p_id = $player->get_id( $s_key );
        
        if ( $p_id < 0 )
            return array( 'success' => false, 'errors' => array( $this->error_code ( $p_id ) ) );
        
        $n_s_key = $player->change_pass ( $p_id, $old_pw, $new_pw, true );
            
        if ( is_int ( $n_s_key ) && $n_s_key < 0 )
            return array( 'success' => false, 'errors' => array( $this->error_code ( $n_s_key ) ) );
        else
            return array( 'success' => true, 'old_skey' => $s_key, 'new_skey' => $n_s_key );
    }
    
    ///////////////////////////////////////////////////////////////////////////
    // Chat
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * /cncnet/heartbeat/GFsd4jf5-9lcld34f==/
     *
     * @param string $s_key Session Key
     * @param int $since unix timestamp of last heartbeat
     * @return string[] Everything that's happened since last heartbeat
     */
    public function heartbeat ( $s_key, $since ) 
    {
        if ( !$this->validate_session ( $s_key ) )
            return array ( 'success' => false, 'errors' => array ( $this->error_code ( -3 ) ) );
            
        $event = new CnCNet_Event( );
        $player = new CnCNet_Player( );
        $p_id = $player->get_id( $s_key );
        
        return array
        (
            'info' => array
            (
                'skey' => $s_key,
                'since' => $since,
                'time' => date('Y-m-d H:i:s')
            ),
            'events' => $event->get( $p_id, $since )
        );
    } 
    
    /**
     * /cncnet/send/GFsd4jf5-9lcld34f==/Hi Everyone
     * /cncnet/send/GFsd4jf5-9lcld34f==/Hi Everyone/23
     *
     * @param string $s_key Session Key
     * @param string $message Message to send
     * @param string $room Room to send to. Room 0 is always lobby and is default
     * @return boolean Success
     */
    public function send ( $s_key, $message, $room = 0 ) 
    {
        if ( !$this->validate_session ( $s_key ) )
            return array ( 'success' => false, 'errors' => array ( $this->error_code ( -3 ) ) );
            
        $r = new CnCNet_Room( );
        $player = new CnCNet_Player( );
        $event = new CnCNet_Event( );
        
        $p_id = $player->get_id( $s_key );
        $rooms = $r->lst( $room );
        
        $event->add( 
            'msg', 
            $rooms[$room]['users'],
            $room,
            $p_id,
            $message
        );
        
        // TODO: Process /[command]s
        
        return array ( 'success' => true );
    } 
    
    /**
     * /cncnet/join/GFsd4jf5-9lcld34f==/23
     *
     * @param string $s_key Session Key
     * @param string $room Room to join.
     * @param string $password Password to join room if needed
     * @return boolean Success
     */
    public function join ( $s_key, $room, $password = "" ) 
    {
        if ( !$this->validate_session ( $s_key ) )
            return array ( 'success' => false, 'errors' => array ( $this->error_code ( -3 ) ) );
            
        $r = new CnCNet_Room( );
        $player = new CnCNet_Player( );
        $event = new CnCNet_Event( );
        
        $p_id = $player->get_id( $s_key );
        $rooms = $r->lst( $room );
        
        if( $r->join( $room, $p_id, $password ) )
        {
            $event->add(
                'join',
                $rooms[$room]['users'],
                $room,
                $player->get_id( $s_key ),
                $player->get_name( $s_key )
            );
        
            return array( 'success' => true );
        }
        return array( 'success' => false, 'errors' => array ( $this->error_code ( -1 ) ) );
    }
    
    /**
     * /cncnet/leave/GFsd4jf5-9lcld34f==/23
     *
     * @param string $s_key Session Key
     * @param string $room Room to exit.
     * @return boolean Success
     */
    public function leave ( $s_key, $room ) 
    {
        if ( !$this->validate_session ( $s_key ) )
            return array ( 'success' => false, 'errors' => array ( $this->error_code ( -3 ) ) );
            
        $r = new CnCNet_Room( );
        $player = new CnCNet_Player( );
        $event = new CnCNet_Event( );
        
        $p_id = $player->get_id( $s_key );
        $rooms = $r->lst( $room );
        
        $r->leave( $room, $p_id );
        
        $event->add(
            'exit',
            $rooms[$room]['users'],
            $room,
            $player->get_id( $s_key ),
            $player->get_name( $s_key )
        );
        
        return array( 'success' => true );
    }
    
    /**
     * /cncnet/create/GFsd4jf5-9lcld34f==/Irony's Room/1
     * /cncnet/create/GFsd4jf5-9lcld34f==/Irony's Room/1/true
     * /cncnet/create/GFsd4jf5-9lcld34f==/Irony's Room/1/true/6
     * /cncnet/create/GFsd4jf5-9lcld34f==/Irony's Room/1/true/6/paSsw0rd.
     *
     * @param string $s_key Session Key
     * @param string $name Name of room.
     * @param string $game Game ID. -1 is chat room.
     * @param string $late_start Are late start joins allowed?
     * @param string $players Number of players allowed to join. -1 = game maximum/infinite for chat.
     * @param string $password Room password. If empty string then room is open.
     * @return boolean Success
     */
    public function create ( $s_key, $name, $game = -1, $late_start = false, $players = -1, $password = "" ) 
    {
        if ( !$this->validate_session ( $s_key ) )
            return array ( 'success' => false, 'errors' => array ( $this->error_code ( -3 ) ) );
            
        $r = new CnCNet_Room( );
        $player = new CnCNet_Player( );
        $event = new CnCNet_Event( );
        
        $rooms = $r->lst(0);
        
        $game = array
        (
            'name' => $name,
            'game' => $game,
            'players' => $game == -1 ? $players : 6,
            'pass' => $password,
            'late' => $late_start
        );
        
        $plyr = array
        (
            'id' => $player->get_id( $s_key ),
            'nickname' => $player->get_name( $s_key )
        );
        $game['id'] = $r->create( $game, $plyr );
        $game['users'] = array ( $plyr['id'] => $plyr['nickname'] );
        
        $event->add(
            'room',
            $rooms[0]['users'],
            $game['id'],
            $player->get_id( $s_key ),
            $game
        );        
        
        return array 
        (
            'success' => true, 
            'room' => $game['id']
        );
    }
    
    /**
     * /cncnet/lst
     * /cncnet/lst/23
     *
     * @param string $room Room ID to list. if -1 then list all rooms.
     * @return string[] details of room(s)
     */
    public function lst ( $room = -1 ) 
    {
        $r = new CnCNet_Room( );
        
        return $r->lst ( $room );
    }
    
    ///////////////////////////////////////////////////////////////////////////
    // CnCNet
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * /cncnet/ready/GFsd4jf5-9lcld34f==/23
     *
     * @param string $s_key Session Key
     * @param string $room Room ID.
     * @return boolean Success
     */
    public function ready ( $s_key, $room )
    {
        if ( !$this->validate_session ( $s_key ) )
            return array ( 'success' => false, 'errors' => array ( $this->error_code ( -3 ) ) );

        $r = new CnCNet_Room( );
        $player = new CnCNet_Player( );
        $event = new CnCNet_Event( );
        
        $p_id = $player->get_id( $s_key );
        $rooms = $r->lst( $room );
        
        $event->add( 
            'ready', 
            $rooms[$room]['users'],
            $room,
            $p_id,
            "@" . base64_encode( $_SERVER['REMOTE_ADDR'] )
        );   
        
        return array ( 'success' => true );
    }
    
    /**
     * /cncnet/launch/GFsd4jf5-9lcld34f==/23
     *
     * @param string $s_key Session Key
     * @param string $room Room ID.
     * @return boolean Success
     */
    public function launch ( $s_key, $room )
    {
        if ( !$this->validate_session ( $s_key ) )
            return array ( 'success' => false, 'errors' => array ( $this->error_code ( -3 ) ) );
            
        $r = new CnCNet_Room( );
        $player = new CnCNet_Player( );
        $event = new CnCNet_Event( );
        
        $p_id = $player->get_id( $s_key );
        $rooms = $r->lst( $room );
        
        $event->add( 
            'launch', 
            $rooms[$room]['users'],
            $room,
            null,
            null // maybe change to that user's lauch url
        );   
        
        return array ( 'success' => true );
    }
    
    ///////////////////////////////////////////////////////////////////////////
    // Helper Functions
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * Validates Session Key. 
     *
     * @param string $s_key Session Key
     * @return boolean Success
     */
    private function validate_session ( $s_key )
    {
        $player = new CnCNet_Player( );
        
        // Maybe query database table to check if valid session key and comming from correct IP?
        $valid = $player->validate_s_key ( $s_key, $_SERVER['REMOTE_ADDR'] );
        
        return ( $valid == 1 ) ? true : false;
    }
    
    /**
     * Error code converter
     *
     * @param int $code Error Code
     * @return string Message
     */
    private function error_code ( $code )
    {
        switch ( $code )
        {
        case -1:
            return "Incorrect Password";
        case -2:
            return "User not found";
        case -3:
            return "Invalid Session";
        case -4:
            return "Failed to create user";
        default:
            return "Unknown Error";
        }
    }
}
