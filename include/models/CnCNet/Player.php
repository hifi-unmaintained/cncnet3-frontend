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

class CnCNet_Player extends CnCNet_Db_Table_Abstract
{
    protected $_name = 'players';
    private $hashalgo = 'sha1';
    private $s_key_salt = 'derp';

    public function ping ( $id )
    {
        $row = $this->select()->where('id = ?', $id)->fetchRow();
        if ($row) {
            $row->active = date('Y-m-d H:i:s');
            $row->save();
            return true;
        }

        $this->logout($id);
        return false;
    }
    
    public function register ( $username, $password, $email, $ip, $port = 8054 )
    {
        $password_salt = hash ( $this->hashalgo, rand() . $username . time( ) );
        $password_hash = hash ( $this->hashalgo, $password . $password_salt );
        
        $new_user = array
        (
            'nickname' => $username,
            'ip' => $ip,
            'port' => $port,
            'created' => date( 'Y-m-d H:i:s' ),
            'active' => date( 'Y-m-d H:i:s' ),
            'pass_hash' => $password_hash,
            'pass_salt' => $password_salt,
            'email' => $email
        );
        
        $player_id = -1;
        $player_id = $this->insert( $new_user );
        return $player_id;
    }
    
    public function login ( $user, $password, $ip, $is_user_id = false, $port = 8054 )
    {
        $row = $this->select( )->where( $is_user_id ? 'id = ?' : 'nickname = ?', $user )->fetchRow( );
        if ( $row )
        {
            $pass_hash = hash ( $this->hashalgo, $password . $row->pass_salt );
            if ( strcmp ( $pass_hash, $row->pass_hash ) == 0 )
            {
                // correct password
                $session_key =  hash ( $this->hashalgo, $ip . $row->nickname . $pass_hash . $this->s_key_salt );
                
                $user_data = array 
                (
                    'active' => date( 'Y-m-d H:i:s' ),
                    'ip' => $ip,
                    'port' => $port,
                    'sesh_time' => date( 'Y-m-d H:i:s' ),
                    'sesh_key' => $session_key 
                );
                
                $this->update( $user_data, $this->getAdapter( )->quoteInto( 'id = ?', $row->id ) );
                
                return $session_key; // success - return session key
            }
            return -1; // error - incorrect password
        }
        return -2; // error - no user exists
    }
    
    public function change_pass ( $user, $oldpass, $newpass, $is_user_id = false )
    {
        $row = $this->select( )->where( $is_user_id ? 'id = ?' : 'nickname = ?', $user )->fetchRow( );
        if ( $row )
        {
            $pass_hash = hash ( $this->hashalgo, $oldpass . $row->pass_salt );
            if ( strcmp ( $pass_hash, $row->pass_hash ) == 0 )
            {
                // correct password
                $new_pass_salt = hash ( $this->hashalgo, rand() . $username . time( ) );
                $new_pass_hash = hash ( $this->hashalgo, $newpass . $new_pass_salt );
                
                $session_key =  hash ( $this->hashalgo, $row->ip . $row->nickname . $new_pass_hash . $this->s_key_salt );
                
                $user_data = array 
                (
                    'pass_hash' => $new_pass_hash,
                    'pass_salt' => $new_pass_salt,
                    'active' => date( 'Y-m-d H:i:s' ),
                    'sesh_time' => date( 'Y-m-d H:i:s' ),
                    'sesh_key' => $session_key 
                );
                
                $this->update( $user_data, $this->getAdapter( )->quoteInto( 'id = ?', $row->id ) );
                
                return $session_key; // success - return new session key
            }
            return -1; // error - incorrect password
        }
        return -2; // error - no user exists
    }
    
    public function validate_s_key ( $s_key, $ip )
    {
        $row = $this->select( )->where( 'sesh_key = ?', $s_key )->fetchRow( );
        if ( $row )
        {
            if ( strcmp ( $ip, $row->ip ) == 0 )
            {
                // TODO: Make this a bit better.
                
                return 1; // success
            }
            return -1; // error - incorrect password
        }
        return -2; // error - no user exists with that key
    }
    
    public function get_name ( $s_key )
    {
        $row = $this->select( )->where( 'sesh_key = ?', $s_key )->fetchRow( );
        if ( $row )
        {
            return $row->nickname; // success - return id
        }
        return -2; // error - no user exists with that key
    }
    
    public function get_id ( $s_key )
    {
        $row = $this->select( )->where( 'sesh_key = ?', $s_key )->fetchRow( );
        if ( $row )
        {
            return $row->id; // success - return id
        }
        return -2; // error - no user exists with that key
    }

    public function logout ( $id )
    {
        $this->update( array( 
            'logout' => date( 'Y-m-d H:i:s' ),
            'sesh_key' => null,
            'sesh_time' => null
        ), $this->getAdapter( )->quoteInto( 'id = ?', $id ) );
        
        // TODO: validate.
        return true;
    }
}
