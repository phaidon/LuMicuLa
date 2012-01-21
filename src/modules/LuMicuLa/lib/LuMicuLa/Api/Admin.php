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

class LuMicuLa_Api_Admin extends Zikula_AbstractApi
{

    /**
    *  get available admin panel links
    *
    * @return array array of admin links
    */
    public function getlinks($args)
    {
        $links = array();
        if( SecurityUtil::checkPermission('LuMicuLa::', '::', ACCESS_ADMIN) ) {
            $links[] = array(
                'url'   => ModUtil::url($this->name, 'admin', 'modules'),
                'text'  => $this->__('Module preferences'),
                'class' => 'z-icon-es-view'
             );
            $links[] = array(
                'url'   => ModUtil::url($this->name, 'admin', 'tags'),
                'text'  => $this->__('Supported tags'),
                'class' => 'z-icon-es-help'
            );
            $links[] = array(
                'url'   => ModUtil::url($this->name, 'admin', 'modifyconfig'),
                'text'  => $this->__('Settings'),
                'class' => 'z-icon-es-config'
            );
        }
        return $links;
    }
}