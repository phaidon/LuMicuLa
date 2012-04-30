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
    
    
    public function setAll($data) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }
    
    
    

}