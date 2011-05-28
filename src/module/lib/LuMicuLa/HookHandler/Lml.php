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
     * Zikula_View instance
     *
     * @var Zikula_View
     */
    private $view;

    /**
     * Post constructor hook.
     *
     * @return void
     */
    public function setup()
    {
        $this->view = Zikula_View::getInstance("LuMicuLa");
        $this->name = 'LuMicuLa';
    }

    /**
     * Display hook for view.
     *
     * Subject is the object being viewed that we're attaching to.
     * args[id] is the id of the object.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_Hook $hook
     *
     * @return void
     */
    public function ui_view(Zikula_DisplayHook $hook)
    {
        $modname = $hook->getCaller();
        $textfieldname = $hook->getId();
        
        if(empty($textfieldname)) {
           $textfieldname = 'textfield';
        }
        
        $editor_settings = Doctrine_Core::getTable('LuMicuLa_Model_LuMicuLa')
                           ->findOneBy('modname', $modname);
        $editor_settings = $editor_settings->toArray();
        
        
        ModUtil::apiFunc($this->name, 'user', 'getModuleSettings', $modname);
        

        if(!$editor_settings['language']) {
            return '';
        }
        
        $elements = ModUtil::apiFunc($this->name, 'user', 'elements', $editor_settings);        
        
        if(array_key_exists('smilies', $editor_settings) and $editor_settings['smilies']) {
            $smilies = ModUtil::apiFunc($this->name, 'smilies', 'smilies');
        } else {
            $smilies = null;
        }

        $this->view->assign('textfieldname', $textfieldname)
                   ->assign('smilies',       $smilies)
                   ->assign('items',         $elements)
                   ->assign('quote',         false);
        
        $response = new Zikula_Response_DisplayHook('provider_area.ui.lumicula.lml', $this->view, 'editor.tpl');
        $hook->setResponse($response);
    }

    /**
     * Filter hook for view
     *
     * Subject is the object being viewed that we're attaching to.
     * args[id] is the id of the object.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_Hook $hook
     *
     * @return void
     */
    public static function filter(Zikula_FilterHook $hook)
    {
        $text = $hook->getData();
        $text = ModUtil::apiFunc('LuMicuLa', 'transform', 'transform', array(
            'text'   => $text,
            'modname' => $hook->getCaller())
        );
        $hook->setData($text);
    }



}