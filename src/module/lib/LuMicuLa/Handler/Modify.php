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

class LuMicuLa_Handler_Modify extends Zikula_Form_AbstractHandler
{
    private $_modname;


    function initialize(Zikula_Form_View $view)
    {
        $view->caching = false;
        
        $modname = FormUtil::getPassedValue('id', null, "GET", FILTER_SANITIZE_STRING);        
        if ($modname) {
            $view->assign('templatetitle', $this->__('Modify module settings').': '.$modname);
            $view->assign('create', false);
            $module_settings = ModUtil::apiFunc($this->name, 'user', 'getModuleSettings', $modname);
            if ($module_settings) {
                $this->_modname = $modname;
                $view->assign($module_settings);
                
            } else {
                return LogUtil::registerError($this->__f('Article with id %s not found', $id));
            }
        } else {
            $view->assign('templatetitle', $this->__('Create module settings'));
            $view->assign('create', true);
            $all_moduls = ModUtil::getAllMods();
            $lml_moduls = Doctrine_Core::getTable('LuMicuLa_Model_LuMicuLa')->findAll();
            $lml_moduls = $lml_moduls->toArray();
            foreach($lml_moduls as $lml_module) {
                $modame = $lml_module['modname'];
                if(array_key_exists($modame, $all_moduls)) {
                    unset($all_moduls[$modame]);
                }
            }
            $modules = array();
            foreach($all_moduls as $modname => $module) {
                $displayname = $module['displayname'];
                $modules[$displayname] = array(
                    'value' => $modname,
                    'text'  => $displayname
                );
            }
            sort($modules);
            $view->assign('modules', $modules);
            
        } 
        
        $lmls = array(
            array('value' => 'BBCode', 'text' => 'BBCode'),
            array('value' => 'Creole', 'text' => 'Creole'),
            array('value' => 'Wakka',  'text' => 'Wakka'),
        );
        $view->assign('lmls', $lmls);

        return true;
    }


    function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('LuMicuLa', 'admin', 'main');
            return $view->redirect($url);
        }

        // Security check
        if (!SecurityUtil::checkPermission('LuMicuLa::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $ok = $view->isValid();
        $data0 = $view->getValues();



        
        // switch between edit and create mode
        if ($this->_modname) {
            $d = Doctrine_Core::getTable('LuMicuLa_Model_LuMicuLa')->find($this->_modname);
        } else {
            $data['modname'] = $data0['modname'];
            unset($data0['modname']);
            $d = new LuMicuLa_Model_LuMicuLa();
        }
        
        $data['language'] = $data0['language'];
        unset($data0['language']);
        $data['elements'] = $data0;//serialize($data0);
        
        
        
        $d->merge($data);
        $d->save();


        LogUtil::registerStatus($this->__('Done! Configuration has been updated'));

        return true;
    }
}