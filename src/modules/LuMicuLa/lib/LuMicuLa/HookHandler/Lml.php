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

class LuMicuLa_HookHandler_Lml extends Zikula_Hook_AbstractHandler
{

    /**
     * Filter hook for view.
     *
     * Subject is the object being viewed that we're attaching to.
     * args[id] is the id of the object.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_FilterHook $hook
     *
     * @return void
     */
    public static function filter(Zikula_FilterHook $hook)
    {
        $text = $hook->getData();

        if ($hook->getCaller() == 'WikulaSaver') {

            $data = array();
            $args = array(
                'text' => $text,
                'modname' => 'Wikula'
            );
            $data['links']      = ModUtil::apiFunc('LuMicuLa', 'Transform', 'getPageLinks', $args);
            $data['categories'] = ModUtil::apiFunc('LuMicuLa', 'Transform', 'getPageCategories', $args);
            $text = $data;
        } else {
            $text = ModUtil::apiFunc('LuMicuLa', 'transform', 'transform', array(
                'text'   => $text,
                'modname' => $hook->getCaller())
            );

        }
                
        $hook->setData($text);
    }
}