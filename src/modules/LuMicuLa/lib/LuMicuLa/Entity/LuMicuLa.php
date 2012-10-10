<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Events entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="lumicula")
 */
class LuMicuLa_Entity_LuMicuLa extends Zikula_EntityAccess
{
    /**
     * The following are annotations which define the fid field.
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=64, nullable="false")
     */
    private $modname;
    
    /**
     * The following are annotations which define the language field.
     *
     * @ORM\Column(type="string", length=16, nullable="true")
     */
    private $language = null;

    /**
     * The following are annotations which define the elements field.
     *
     * @ORM\Column(type="array", nullable="true")
     */
    private $elements = null;

    /**
     * The following are annotations which define the allTextAreas field.
     *
     * @ORM\Column(type="boolean")
     */
    private $allTextAreas = true;

    /**
     * The following are annotations which define the textAreaNames field.
     *
     * @ORM\Column(type="array")
     */
    private $textAreaNames = array();



    /**
     * The following are annotations which define the allTextAreas field.
     *
     * @ORM\Column(type="boolean")
     */
    private $allFunctions = true;

    /**
     * The following are annotations which define the textAreaNames field.
     *
     * @ORM\Column(type="array")
     */
    private $functionNames = array();
    

    /**
     * The following are annotations which define the smilies field.
     *
     * @ORM\Column(type="boolean")
     */
    private $smilies = false;
    
    public function getModname()
    {
        return $this->modname;
    } 
    
    public function getLanguage()
    {
        return $this->language;
    }
    
    public function getElements()
    {
        return $this->elements;
    }
    
    
    public function getSmilies()
    {
        return $this->smilies;
    }

    public function getAllTextAreas()
    {
        return $this->allTextAreas;
    }

    public function getTextAreaNames()
    {
        return $this->textAreaNames;
    }


    public function getAllFunctions()
    {
        return $this->allFunctions;
    }

    public function getFunctionNames()
    {
        return $this->functionNames;
    }


    public function setModname($modname)
    {
        return $this->modname = $modname;
    }

    public function setLanguage($language)
    {
        return $this->language = $language;
    }

    public function setElements($elements)
    {
        return $this->elements = $elements;
    }


    public function setSmilies($smilies)
    {
        return $this->smilies = $smilies;
    }

    public function setAllTextAreas($allTextAreas)
    {
        return $this->allTextAreas = $allTextAreas;
    }

    public function setTextAreaNames($textAreaNames)
    {
        return $this->textAreaNames = $textAreaNames;
    }


    public function setAllFunctions($allFunctions)
    {
        return $this->allFunctions = $allFunctions;
    }

    public function setFunctionNames($functionNames)
    {
        return $this->functionNames = $functionNames;
    }
    
    
    

}