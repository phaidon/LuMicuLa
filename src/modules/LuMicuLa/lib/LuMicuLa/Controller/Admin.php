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

class LuMicuLa_Controller_Admin extends Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * Run after construction.
     *
     * @return void
     */
    
    protected function postInitialize()
    {
        // Disable caching by default.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }
    
    
    public function main()
    {
        return $this->modules();
    }
    
       
    
    public function modifyconfig()
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('admin/modifyconfig.tpl', new LuMicuLa_Handler_ModifyConfig());
    }
    
    
    
    /**
     * Edit LuMicuLa module settings.
     *
     * Parameters passed via GET:
     * --------------------------
     * id     string    modname
     *
     * Parameters passed via POST:
     * ---------------------------
     * None.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access, or if the method of accessing this function is improper.
     */
    
    public function modify()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('admin/modify.tpl', new LuMicuLa_Handler_Modify());
    }

    public function modify2()
    {
        $modname = FormUtil::getPassedValue('id', null, "POST", FILTER_SANITIZE_STRING);

        return System::redirect(ModUtil::url($this->name, 'admin', "modify", array('id' => $modname)));
    }
    
    /**
     * Shows all modules, which are related to LuMicuLa.
     *
     * Parameters passed via GET:
     * --------------------------
     * None
     *
     * Parameters passed via POST:
     * ---------------------------
     * None.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access, or if the method of
     * accessing this function is improper.
     */
     public function modules()
     {
         if (!SecurityUtil::checkPermission('LuMicuLa::', '::', ACCESS_ADMIN)) {
             throw new Zikula_Exception_Forbidden();
         }
    
         $allElements = ModUtil::apiFunc($this->name, 'user', 'elements');
         $allModuleSettings = $this->entityManager->getRepository('LuMicuLa_Entity_LuMicuLa')->findAll();

         $allModules = ModUtil::getAllMods();
         foreach ($allModuleSettings as $moduleSetting) {
             $modname = $moduleSetting->getModname();
             unset($allModules[$modname]);
         }

         foreach ($allModules as $modname => $modinfo) {
             $subscriberHooks = HookUtil::getSubscriberAreasByOwner($modname);
             foreach ($subscriberHooks as $key => $value) {
                 $hook = explode('.', $value);
                 $hookType = $hook[2];
                 if ($hookType != 'filter_hooks') {
                     unset($subscriberHooks[$key]);
                 }
             }
             if (count($subscriberHooks) == 0) {
                 unset($allModules[$modname]);
             }
         }

         $this->view->assign('modules', $allModules);


        return $this->view->assign('mods', $allModuleSettings)
                          ->assign('elements', $allElements)
                          ->fetch('admin/modules.tpl');
     }


     public function deleteModuleSettings()
     {
         if (!SecurityUtil::checkPermission('LuMicuLa::', '::', ACCESS_ADMIN)) {
             throw new Zikula_Exception_Forbidden();
         }

         $url = ModUtil::url($this->name, 'admin', 'modules');
         $modname = FormUtil::getPassedValue('id', null, "GET", FILTER_SANITIZE_STRING);
         if (empty($modname)) {
             return System::redirect($url);
         }
         $moduleSettings = $this->entityManager->find('LuMicuLa_Entity_LuMicuLa', $modname);
         if (!$moduleSettings) {
             return System::redirect($url);
         }
         $this->entityManager->remove($moduleSettings);
         $this->entityManager->flush();

         return System::redirect($url);

     }


     public function tags()
     {
        if (!SecurityUtil::checkPermission('LuMicuLa::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        $tags = ModUtil::apiFunc($this->name, 'user', 'supportedTags');
        return $this->view->assign('tags', $tags)
                          ->fetch('admin/tags.tpl');
       
     }

}
