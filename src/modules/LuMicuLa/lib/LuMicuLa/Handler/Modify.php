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
    private $moduleSettings;


    function initialize(Zikula_Form_View $view)
    {
        $view->caching = false;
        
        $modname = FormUtil::getPassedValue('id', null, "GET", FILTER_SANITIZE_STRING);        
        if ($modname) {
            $view->assign('templatetitle', $this->__('Modify module settings').': '.$modname);
            $view->assign('create', false);
            $this->moduleSettings = $this->entityManager->find('LuMicuLa_Entity_LuMicuLa', $modname);
            if ($this->moduleSettings) {
                $view->assign($this->moduleSettings->toArray());
                $view->assign($this->moduleSettings->getElements());
                
            } else {
                return LogUtil::registerError($this->__f('Article with id %s not found', $id));
            }
        } else {
            $view->assign('templatetitle', $this->__('Create module settings'));
            $view->assign('create', true);
            $all_moduls = ModUtil::getAllMods();
            $lml_moduls = $this->entityManager->getRepository('LuMicuLa_Entity_LuMicuLa')->findAll();
            foreach($lml_moduls as $lml_module) {
                $lml_module = $lml_module->toArray();
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
            array('value' => 'BBCode',    'text' => 'BBCode'),
            array('value' => 'Creole',    'text' => 'Creole'),
            array('value' => 'Markdown',  'text' => 'Markdown'),
            array('value' => 'Wikimedia', 'text' => 'Wikimedia'),
            array('value' => 'Wikka',     'text' => 'Wikka'),
        );
        $view->assign('lmls', $lmls);
        
        
        $elements = ModUtil::apiFunc($this->name, 'user', 'elements');
        $view->assign('elements', $elements);

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
        if (!$this->moduleSettings) {
            $data['modname'] = $data0['modname'];
            unset($data0['modname']);
            $this->moduleSettings = new LuMicuLa_Entity_LuMicuLa();
        }
        
        $data['language'] = $data0['language'];
        unset($data0['language']);
        $data['smilies'] = $data0['smilies'];
        unset($data0['smilies']);
        $data['elements'] = $data0;        
        
        $this->moduleSettings->setAll($data);
        $this->entityManager->persist($this->moduleSettings);
        $this->entityManager->flush();


        LogUtil::registerStatus($this->__('Done! Configuration has been updated'));

        return true;
    }
}