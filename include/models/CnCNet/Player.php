<?php

class CnCNet_Player extends CnCNet_Db_Table_Abstract
{
    protected $_name = 'players';

    public function ping($id)
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

    public function logout($id)
    {
        $this->update(array('logout' => date('Y-m-d H:i:s')), $this->getAdapter()->quoteInto('id = ?', $id));
    }
}
