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
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access, or if the method of accessing this function is improper.
     */
    
     public function main()
     {
        if (!SecurityUtil::checkPermission('LuMicuLa::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }
        
        $all_module_settings = ModUtil::apiFunc($this->name, 'user', 'getAllModuleSettings');
        return $this->view->assign('mods', $all_module_settings)
                          ->fetch('admin/main.tpl');
     }

}
