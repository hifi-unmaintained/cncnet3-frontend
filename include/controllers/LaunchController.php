<?php

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
        $stmt = $this->db->query( $this->db->select()->from('players')->join('room_players', $this->db->quoteInto('room_players.room_id = ? AND room_players.player_id = players.id', $this->session->room_id)) );
        $ips = array();
        while ($row = $stmt->fetch()) {
            if ($row['id'] != $this->session->player_id)
                $ips[] = $row['ip'];
        }

        $this->view->uri = $room['game'].'://'.implode(',', $ips);
    }

    public function closeAction()
    {
        unset($this->session->room_id);
    }
}
