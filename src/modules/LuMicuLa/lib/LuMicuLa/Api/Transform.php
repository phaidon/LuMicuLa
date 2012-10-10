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

class LuMicuLa_Api_Transform extends Zikula_AbstractApi 
{
    

    
    
    
   /**
    * Wiki formater wrapper
    *
    * @param string $args['text'] text to wiki-format
    * @return wiki-formatted text
    */

    private $_lml        = array();
    private $elements;


    
    public function transform($args)
    { 
        if (empty($args['modname']) or empty($args['text'])) {
            return $args['text'];
        }

        $parser = new LuMicuLa_Language_Parser();
        $parser->setText($args['text']);
        $parser->setModname($args['modname']);
        return $parser->parse();

    }
    

    public function getPageLinks($args) {
        
        
        if (empty($args['text']) || empty($args['modname'])) {
            return array();
        }
        
               
        $parser = new LuMicuLa_Language_Parser();
        $parser->setText($args['text']);
        $parser->setModname($args['modname']);
        return $parser->getPageLinks();
  
    }
    
    
    public function getPageCategories($args) {
        
        if (empty($args['text']) || empty($args['modname'])) {
            return array();
        }
        
        $parser = new LuMicuLa_Language_Parser();
        $parser->setText($args['text']);
        $parser->setModname($args['modname']);
        return $parser->getPageCategories();

    }
    

    
    


    
    public function transform_quotes($message)
    {    
        return preg_replace_callback(
            "#\[quote(.*?)\[\/quote\]#si",
            Array($this, 'quote_callback'),
            $message
        );
    }
    

    
    //DataUtil::formatForDisplay
    protected function quote_callback($matches)
    {      
       if(substr($matches[1], 0, 1) == "]") {
           $quote = substr($matches[1], 1);
           $header = '';
       } else {
           $tmpArray = explode(']', $matches[1]);
           $quote = $tmpArray[1];
           $tmpArray = explode('=', $tmpArray[0]);
           $user = $tmpArray[1];
           $header = '<div class="lmlquoteheader">'.$user.' '.$this->__('wrote').':</div>';
       }
       
       return $header.'<blockquote class="lmlquotetext">'.$quote.'</blockquote>';
    }

}