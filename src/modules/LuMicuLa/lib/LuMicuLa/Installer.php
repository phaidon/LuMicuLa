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

class LuMicuLa_Installer extends Zikula_AbstractInstaller
{
    /**
    * initialise the template module
    * This function is only ever called once during the lifetime of a particular
    * module instance
    */
    public function install()
    {
         // create table
        try {
            DoctrineHelper::createSchema($this->entityManager, array(
                'LuMicuLa_Entity_LuMicuLa'
            ));
        } catch (Exception $e) {
            LogUtil::registerStatus($e->getMessage());
            return false;
        }

        $this->defaultdata();
        
        // create hook
        HookUtil::registerProviderBundles($this->version->getHookProviderBundles());

        // Initialisation successful
        return true;
    }
    
    
    /**
     * Provide default data.
     *
     * @return void
     */
    protected function defaultdata()
    {
        $i = new LuMicuLa_Entity_LuMicuLa();
        $data = array(
            'modname'  => 'Tasks',
            'language' => 'Creole',
            'elements' => array('bold' => true)
        );
        $i->setAll($data);
        $this->entityManager->persist($i);
        
        
        $i = new LuMicuLa_Entity_LuMicuLa();
        $data = array(
            'modname'  => 'Wikula',
            'language' => 'Wikka',
            'elements' => array('bold' => true)
        );
        $i->setAll($data);
        $this->entityManager->persist($i);
        
        $this->entityManager->flush();

        $this->setVar('syntaxHighlighters', 'syntaxhighlighter');
        $this->setVar('imageViewer', true);
        
    }

    /**
    * Upgrade the errors module from an old version
    *
    * This function must consider all the released versions of the module!
    * If the upgrade fails at some point, it returns the last upgraded version.
    *
    * @param        string   $oldVersion   version number string to upgrade from
    * @return       mixed    true on success, last valid version string or false if fails
    */
    public function upgrade($oldversion)
    {
        // Update successful
        return true;
    }

    /**
    * delete the errors module
    * This function is only ever called once during the lifetime of a particular
    * module instance
    */
    public function uninstall()
    {
         // drop tables
        DoctrineHelper::dropSchema($this->entityManager, array(
            'LuMicuLa_Entity_LuMicuLa'
        ));
        
        // Delete any module variables
        $this->delVars();
        HookUtil::unregisterProviderBundles($this->version->getHookProviderBundles());

        // Deletion successful
        return true;

    }
}

