<?php

class RoomController extends CnCNet_Controller_Action
{
    public function preDispatch()
    {
        if (!$this->session->player_id)
            return $this->_forward('init', 'index');

        if ($this->session->room_id < 1)
            return $this->_forward('index', 'rooms');

        /* if we are not in the game, don't let us in */
        if (count($this->db->fetchAll( $this->db->select()->from('room_players')->where('room_id = ?', $this->session->room_id)->where('player_id = ?', $this->session->player_id) )) == 0) {
            unset($this->session->room_id);
            return $this->_forward('index', 'rooms');
        }

        if ($this->session->room_id) {
            $this->view->room = Zend_Registry::get('room');
            $this->view->refresh = 2;

            if ($this->view->room['started']) {
                unset($this->view->room);
                return $this->_forward('index', 'launch');
            }
        }
    }

    protected function updatePlayers()
    {
        /* update players if kicked or ready state changed */
        if ($this->session->room_id) {
            $this->ready = true;
            $this->view->room['players'] = array();
            $stmt = $this->db->query( $this->db->select()->from('players')->join('room_players', $this->db->quoteInto('room_players.room_id = ? AND room_players.player_id = players.id', $this->session->room_id)) );
            while ($row = $stmt->fetch()) {
                $this->view->room['players'][] = array(
                    'id'        => $row['id'],
                    'nickname'  => $row['nickname'],
                    'ready'     => (bool)$row['ready'],
                    'owner'     => $row['id'] == $this->view->room['player_id']
                );
                if (!$row['ready'])
                    $this->ready = false;
            }
        }
    }

    public function postDispatch()
    {
        if (isset($this->view->room))
            $this->updatePlayers();
    }

    public function indexAction()
    {
    }

    public function startAction()
    {
        $this->updatePlayers();
        if ($this->view->room['player_id'] == $this->session->player_id && $this->ready) {
            $this->db->update('rooms', array('started' => date('Y-m-d H:i:s')), $this->db->quoteInto('id = ?', $this->session->room_id));
            unset($this->view->room);
            unset($this->view->refresh);
            $room = Zend_Registry::get('room');
            $room['started'] = true;
            Zend_Registry::set('room', $room);
            $this->_forward('index', 'launch');
        }
    }

    public function readyAction()
    {
        $this->db->update('room_players', array('ready' => new Zend_Db_Expr('NOT ready')), sprintf('room_id = %d AND player_id = %d', $this->session->room_id, $this->session->player_id));
    }

    public function kickAction()
    {
        if ($this->view->room['player_id'] == $this->session->player_id && $this->_getParam('player') != $this->session->player_id) {
            $this->db->delete('room_players', sprintf('room_id = %d AND player_id = %d', $this->session->room_id, $this->_getParam('player')));
        }
    }

    public function leaveAction()
    {
        $this->db->delete('room_players', sprintf('room_id = %d AND player_id = %d', $this->session->room_id, $this->session->player_id));
        unset($this->session->room_id);
        unset($this->view->room);
        $this->_forward('index', 'rooms');
    }
}
