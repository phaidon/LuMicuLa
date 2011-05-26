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
        
        $editor_settings = ModUtil::apiFunc($this->name, 'user', 'getModuleSettings', $modname);

        if(!$editor_settings['language']) {
            return '';
        }
        
        $e = ModUtil::apiFunc($this->name, 'user', 'elements', $editor_settings['language']);

        $items = array();
        $items[] = array(
            'icon'  => 'page.png',
            'title' => $this->__('Insert page link'),
            'begin' => '[[',
            'inner' => $this->__('PageName'),
            'end'   => ']]',
        );
        $items[] = array(
            'icon'  => 'link.png',
            'title' => $this->__('Insert web link'),
            'begin' => $e['url']['begin'],
            'inner' => $e['url']['inner'],
            'end'   => $e['url']['end'],
        );
            
        if(array_key_exists('italic', $editor_settings) and $editor_settings['italic']) {
            $items[] = array(
                'icon'     => 'italic.png',
                'title'    => $this->__('Italic text'),
                'begin'    => $e['italic']['begin'],
                'inner'    => $this->__('Italic text'),
                'end'      => $e['italic']['end'],
                'shortcut' => $this->__('i', 'test'),
            );
        }
        if(array_key_exists('bold', $editor_settings) and $editor_settings['bold']) {
            $items[] = array(
                'icon'     => 'bold.png',
                'title'    => $this->__('Bold text'),
                'begin'    => $e['bold']['begin'],
                'inner'    => $this->__('Bold text'),
                'end'      => $e['bold']['end'],
                'shortcut' => $this->__('b'),
            );
        }
        if(array_key_exists('underline', $editor_settings) and  $editor_settings['underline']) {
            $items[] = array(
                'icon'  => 'underline.png',
                'title' => $this->__('Underline'),
                'begin' => $e['underline']['begin'],
                'inner' => $this->__('Underline'),
                'end'   => $e['underline']['end'],
                'shortcut' => $this->__('u', 'test'),
            );
        }
        if(array_key_exists('strikethrough', $editor_settings) and $editor_settings['strikethrough']) {
            $items[] = array(
                'icon'  => 'strikethrough.png',
                'title' => $this->__('Strike through'),
                'begin' => $e['strikethrough']['begin'],
                'inner' => $this->__('Strike through'),
                'end'   => $e['strikethrough']['end'],
            );
        }
        if(array_key_exists('mark', $editor_settings) and $editor_settings['mark']) {
            $items[] = array(
                'icon'  => 'mark.png',
                'title' => $this->__('Mark'),
                'begin' => $e['mark']['begin'],
                'inner' => $this->__('Mark'),
                'end'   => $e['mark']['end'],
            );
        }
        if(array_key_exists('code', $editor_settings) and $editor_settings['code']) {
            $items[] = array(
                'icon'  => 'code.png',
                'title' => $this->__('Code'),
                'begin' => $e['code']['begin'],
                'inner' => $this->__('Code'),
                'end'   => $e['code']['end'],
            );
        }
        if(array_key_exists('headings', $editor_settings) and $editor_settings['headings']) {
            $headings = array(
                'h1' => $e['h1']['begin'],
                'h2' => $e['h2']['begin'],
                'h3' => $e['h3']['begin'],
                'h4' => $e['h4']['begin'],
                'h5' => $e['h5']['begin']
            );
        } else {
            $headings = null;
        }
        
        if(array_key_exists('smilies', $editor_settings) and $editor_settings['smilies']) {
            $smilies = ModUtil::apiFunc($this->name, 'user', 'smilies');
        } else {
            $smilies = null;
        }

        $this->view->assign('textfieldname', $textfieldname)
                   ->assign('smilies',       $smilies)
                   ->assign('items',         $items)
                   ->assign('headings',      $headings)
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
    public function filter(Zikula_FilterHook $hook)
    {
        
        $text = $hook->getData();
        $text = ModUtil::apiFunc('LuMicuLa', 'user', 'transform', array(
            'text'   => $text,
            'modname' => $hook->getCaller())
        );
        
        $hook->setData($text);
    }



}