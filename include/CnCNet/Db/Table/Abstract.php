<?php

abstract class CnCNet_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    public function _getName()
    {
        return $this->_name;
    }

    /**
     * Returns an instance of a CnCNet_Db_Table_Select object.
     *
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return CnCNet_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITH_FROM_PART)
    {
        require_once 'CnCNet/Db/Table/Select.php';
        $select = new CnCNet_Db_Table_Select($this);
        if ($withFromPart == self::SELECT_WITH_FROM_PART) {
            $select->from($this->info(self::NAME), Zend_Db_Table_Select::SQL_WILDCARD, $this->info(self::SCHEMA));
        }
        return $select;
    }
}
