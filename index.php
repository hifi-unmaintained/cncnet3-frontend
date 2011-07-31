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

define('APP_ROOT', dirname(__FILE__));

set_include_path(get_include_path() . PATH_SEPARATOR . 'include/' . PATH_SEPARATOR . 'include/models/');

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('CnCNet_');
unset($loader);

$db = Zend_Db::factory('Pdo_Sqlite', array('dbname' => 'db/cncnet.db'));
Zend_Registry::set('db', $db);
Zend_Db_Table::setDefaultAdapter($db);

$db->query('PRAGMA foreign_keys = ON');

$session = new Zend_Session_Namespace('cncnet');

if ($session->player_id) {
    $table = new CnCNet_Player();
    $table->ping($session->player_id);

    $player = $table->select()->where('id = ?', $session->player_id)->fetchRow();
    if ($player) {
        Zend_Registry::set('player', $player->toArray());
    } else {
        unset($session->player_id);
    }
    unset($table);
    unset($player);
}

/* check if we are in any game room, if we are, make sure the room is set to registry! */
if ($session->room_id > 0) {
    $room = $db->fetchRow(
        $db->select()
           ->from('rooms')
           ->join('games', 'games.id = rooms.game_id', array('game' => 'protocol'))
           ->where('rooms.id = ?', $session->room_id)
    );
    if ($room) {
        Zend_Registry::set('room', $room);
    } else {
        unset($session->room_id);
    }
    unset($room);
}

Zend_Registry::set('session', $session);
unset($session);
unset($db);

Zend_Controller_Front::run(APP_ROOT.'/include/controllers');
