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

class CnCNet_Db_Table_Select extends Zend_Db_Table_Select implements Countable, Iterator
{
    /* override the default for our convenience */
    protected $_integrityCheck = false;

    protected $iterator_stmt;
    protected $iterator_pos;
    protected $iterator_cur;

    public function fetchRow()
    {
        $row = $this->_table->getAdapter()->fetchRow($this);

        if ($row) {
            $data = array(
                'table'     => $this->_table,
                'data'      => $row,
                'readOnly'  => $this->isReadOnly(),
                'stored'    => true
            );

            $rowClass = $this->_table->getRowClass();
            if (!class_exists($rowClass)) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($rowClass);
            }
            return new $rowClass($data);
        }
    }

    public function fetchAll()
    {
        $rows = $this->_table->getAdapter()->fetchAll($this);

        $data = array(
            'table'     => $this->_table,
            'data'      => $rows,
            'readOnly'  => $this->isReadOnly(),
            'rowClass'  => $this->_table->getRowClass(),
            'stored'    => true
        );

        $rowsetClass = $this->_table->getRowsetClass();
        if (!class_exists($rowsetClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowsetClass);
        }
        return new $rowsetClass($data);
    }

    public function count()
    {
        $select = clone $this;
        return $this->_table->getAdapter()->fetchOne($select->reset('columns')->columns(new Zend_Db_Expr('COUNT(*)')));
    }

    public function current()
    {
        $row = $this->iterator_cur;

        if ($row) {
            $data = array(
                'table'     => $this->_table,
                'data'      => $row,
                'readOnly'  => $this->isReadOnly(),
                'stored'    => true
            );

            $rowClass = $this->_table->getRowClass();
            if (!class_exists($rowClass)) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($rowClass);
            }
            return new $rowClass($data);
        }
    }

    public function key()
    {
        return $this->iterator_pos;
    }

    public function next()
    {
        $this->iterator_cur = $this->iterator_stmt->fetch();
        $this->iterator_pos++;
    }

    public function rewind()
    {
        $this->iterator_stmt = $this->_table->getAdapter()->query($this);
        $this->iterator_pos = 0;
        $this->iterator_cur = $this->iterator_stmt->fetch();
    }

    public function valid()
    {
        return ($this->iterator_cur !== false);
    }
}
