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

class LuMicuLa_Handler_ModifyConfig extends Zikula_Form_AbstractHandler
{
    private $_modname;


    function initialize(Zikula_Form_View $view)
    {
        $view->caching = false;
        
        $view->assign($this->getVars());

        
        $syntaxHighlighters = array(
            array('value' => 'prettify',          'text' => 'Google-Code-Prettify'),
            array('value' => 'geshi',             'text' => 'GeSHi'),
            array('value' => 'syntaxhighlighter', 'text' => 'SyntaxHighlighter'),

        );
        $view->assign('syntaxHighlighters', $syntaxHighlighters);

        return true;
    }


    function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('LuMicuLa', 'admin', 'modifyconfig');
            return $view->redirect($url);
        }

        // Security check
        if (!SecurityUtil::checkPermission('LuMicuLa::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $ok = $view->isValid();
        $data = $view->getValues();

        $this->setVars($data);


        LogUtil::registerStatus($this->__('Done! Configuration has been updated'));

        return true;
    }
}