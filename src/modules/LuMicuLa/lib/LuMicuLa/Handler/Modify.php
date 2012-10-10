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
    private $_moduleSettings;


    function initialize(Zikula_Form_View $view)
    {
        $view->caching = false;
        
        $modname = FormUtil::getPassedValue('id', null, "GET", FILTER_SANITIZE_STRING);

        if (empty($modname) || !ModUtil::available($modname)) {
            return LogUtil::registerArgsError();
        }


        $view->assign('templatetitle', $this->__('Modify module settings').': '.$modname);
        $view->assign('create', false);
        $this->_moduleSettings = $this->entityManager->find('LuMicuLa_Entity_LuMicuLa', $modname);
        if (!$this->_moduleSettings) {
            $this->_moduleSettings = new LuMicuLa_Entity_LuMicuLa();
            $this->_moduleSettings->setModname($modname);
        }


        $data = $this->_moduleSettings->toArray();
        $data['textAreaNames'] = implode(',', $data['textAreaNames']);
        $data['functionNames'] = implode(',', $data['functionNames']);
        $view->assign($data);


        $bindedHooks = HookUtil::getSubscriberAreasByOwner($modname);
        if (count($bindedHooks) == 0) {
            return LogUtil::registerError($this->__('The chosen module has no filters!'));
        }
        foreach ($bindedHooks as $key => $value) {
            $hook = explode('.', $value);
            $hookType = $hook[2];
            $binded = HookUtil::getBindingBetweenAreas($value, 'provider.lumicula.filter_hooks.lml');
            if ($hookType != 'filter_hooks' || !$binded) {
                unset($bindedHooks[$key]);
            }

        }
        $view->assign('bindedHooks', implode(', ', $bindedHooks));


        $lmls = array(
            array('value' => 'BBCode',    'text' => 'BBCode'),
            array('value' => 'Creole',    'text' => 'Creole'),
            array('value' => 'Markdown',  'text' => 'Markdown'),
            array('value' => 'Wikimedia', 'text' => 'Wikimedia'),
            array('value' => 'Wikka',     'text' => 'Wikka'),
        );
        $view->assign('lmls', $lmls);
        
        $elements = ModUtil::apiFunc($this->name, 'user', 'elements');

        $view->assign('allElements', $elements);

        return true;
    }


    function handleCommand(Zikula_Form_View $view, &$args)
    {
        $moduleSettings = $this->_moduleSettings;

        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('LuMicuLa', 'admin', 'main');
            return $view->redirect($url);
        }

        if ($args['commandName'] == 'delete') {
            $this->entityManager->remove($moduleSettings);
            $this->entityManager->flush();
            $url = ModUtil::url('LuMicuLa', 'admin', 'main');
            return $view->redirect($url);
        }

        // Security check
        if (!SecurityUtil::checkPermission('LuMicuLa::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $ok = $view->isValid();
        if (!$ok) {
            return false;
        }
        $data = $view->getValues();

        $data['textAreaNames'] = explode(',', $data['textAreaNames']);
        $data['functionNames'] = explode(',', $data['functionNames']);

        $moduleSettings->merge($data);
        $this->entityManager->persist($moduleSettings);
        $this->entityManager->flush();


        LogUtil::registerStatus($this->__('Done! Configuration has been updated'));

        return true;
    }
}