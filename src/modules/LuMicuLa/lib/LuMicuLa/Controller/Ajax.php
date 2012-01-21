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


class LuMicuLa_Controller_Ajax extends Zikula_AbstractController
{

    public function remove()
    {
        if(!SecurityUtil::checkPermission('LuMicuLa::', '::', ACCESS_DELETE) ){
            return;
        }
        $modname = FormUtil::getPassedValue('id', -1, 'GET');
        $d = Doctrine_Core::getTable('LuMicuLa_Model_LuMicuLa')->find($modname);
        $d->delete();
    }
   
}
