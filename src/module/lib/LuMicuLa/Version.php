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


class LuMicuLa_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['description']    = __('A lightweight markup language editor');
        $meta['displayname']    = __('LuMicuLa');
        //!url must be different to displayname
        $meta['url']            = __('LuMicuLa');
        $meta['version']        = '0.1.0';
        $meta['author']         = 'Fabian Wuertz';
        $meta['contact']        = 'fabian.wuertz.org';
        // recommended and required modules
        $meta['dependencies'] = array();
        return $meta;
    }
}