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

/**
 * Listeners class.
 */
class LuMicuLa_Listeners
{
    /**
     * Event listener for 'core.postinit' event.
     * 
     * @param Zikula_Event $event
     *
     * @return void
     */
    public static function coreinit(Zikula_Event $event)
    {
        $name = 'LuMicuLa';

        $modinfo1 = ModUtil::getInfoFromName($name);
        $version = new LuMicuLa_Version();
        $modinfo2 = $version->getMetaData();

        if ($modinfo1['version'] != $modinfo2['version']) {
            return;
        }

        $view = Zikula_View::getInstance('LuMicuLa');

        $modname = FormUtil::getPassedValue('module', null, "GET", FILTER_SANITIZE_STRING);

        if(empty($modname)) {
            return;
        }


        $em = ServiceUtil::getService('doctrine.entitymanager');
        $editorSettings = $em->find('LuMicuLa_Entity_LuMicuLa', $modname);

        if (!$editorSettings) {
            return false;
        }

        if (!$editorSettings->getAllFunctions()) {
            $func = FormUtil::getPassedValue('func', null, "GET", FILTER_SANITIZE_STRING);
            if (!in_array($func, $editorSettings->getFunctionNames())) {
                return false;
            }
        }

        $editorSettings = $editorSettings->toArray();


        ModUtil::apiFunc($name, 'user', 'getModuleSettings', ModUtil::getName());


        if (!$editorSettings['language']) {
            return false;
        }

        // elements
        $elements = ModUtil::apiFunc($name, 'user', 'elements', $editorSettings);
        $view->assign('elements', json_encode($elements));


        // smilies
        if (array_key_exists('smilies', $editorSettings) and $editorSettings['smilies']) {
            $smilies = ModUtil::apiFunc($name, 'smilies', 'smilies');
        } else {
            $smilies = '';
        }
        $view->assign('smilies', json_encode($smilies));

        // quote
        $view->assign('quote', 0);


        // textarray
        $view->assign('allTextAreas', $editorSettings['allTextAreas']);
        $view->assign('textAreaNames', $editorSettings['textAreaNames']);

        // editor template
        $sceditor = ModUtil::getVar($name, 'sceditor', false);
        $isSceditorLang = ($editorSettings['language'] == 'BBCode' || $editorSettings['language'] == 'Creole');
        if ($sceditor && $isSceditorLang) {
            $template = 'sceditor.tpl';
        } else {
            $template = 'editor.tpl';
        }
        $header = $view->fetch($template);

        PageUtil::addVar('footer', $header);

        return true;
    }
}
