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
        $meta['url']            = __('luMicuLa');
        $meta['version']        = '0.1.0';
        $meta['author']         = 'Fabian Wuertz';
        $meta['contact']        = 'fabian.wuertz.org';
        // recommended and required modules
        $meta['core_min'] = '1.3.0'; // requires minimum 1.3.0 - 1.3.99 
        $meta['core_max'] = '1.3.99';
        $meta['dependencies'] = array();
        $meta['capabilities'] = array(HookUtil::PROVIDER_CAPABLE => array('enabled' => true));

        return $meta;
    }
    
    protected function setupHookBundles()
    {
        $bundle = new Zikula_HookManager_ProviderBundle(
            $this->name,
            'provider.lumicula.filter_hooks.lml',
            'filter_hooks', __('LuMicuLa transform')
        );
        $bundle->addStaticHandler('filter', 'LuMicuLa_HookHandler_Lml', 'filter', 'lumicula.lml');
        $this->registerHookProviderBundle($bundle);    
    }
}
