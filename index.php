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

set_include_path(get_include_path() . PATH_SEPARATOR . 'include/');

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('CnCNet_');
unset($loader);

$db = Zend_Db::factory('Pdo_Sqlite', array('dbname' => 'db/cncnet.db'));
Zend_Registry::set('db', $db);
Zend_Db_Table::setDefaultAdapter($db);

$db->query('PRAGMA foreign_keys = ON');
unset($db);

$rest = new Zend_Rest_Server();
$rest->setClass('CnCNet_Rest');

$req = new Zend_Controller_Request_Http();
$parts = explode('/', substr($req->PATH_INFO, 1));

$request = array('method' => NULL);

if (count($parts) > 0 && strlen($parts[0]) > 0) {
    $request['method'] = array_shift($parts);
}

if (count($parts) > 0 && strlen($parts[0]) > 0) {
    foreach($parts as $i => $v) {
        $request['arg' . ($i+1)] = $v;
    }
} else if ($_POST) {
    $request = array_merge($_POST, $request);
}

unset($req);

$rest->handle($request);
