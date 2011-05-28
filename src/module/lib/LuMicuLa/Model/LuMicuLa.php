<?php

/**
 * Copyright LuMicuLa Team 2011
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package LuMicuLa
 * @link http://code.zikula.org/LuMicuLa
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


class LuMicuLa_Model_LuMicuLa extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('lumicula');
        $this->hasColumn('modname', 'string', 64, array(
            'unique'  => true,
            'primary' => true,
            'notnull' => true,
        ));
        $this->hasColumn('language', 'string', 16, array(
            'default' => null
        ));
        $this->hasColumn('elements', 'array', array(
            'default' => null
        ));
        $this->hasColumn('smilies', 'bool', array(
            'default' => false
        ));

    }

}