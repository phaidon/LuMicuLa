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

class LuMicuLa_Api_Creole extends Zikula_AbstractApi 
{
    
    /**
    * Creole elements
    *
    * @see http://www.wikicreole.org/
    * @return list of Creole elements
    */    
    
    public function elements()
    {
        return array(
            'img' => array(
                'begin' => '{{',
                'inner' => 'http://www.example.com/image.png',
                'end'   => '}}',
                'func'  => true
            ),
            'youtube' => array(
                'begin' => '<<youtube id=',
                'inner' => '',
                'end'   => '>>',
            ),
            'hr' => array(
                'begin' => '----',
                'inner' => '',
                'end'   => '',
            ),
            'url' => array(
                'begin' => '[[',
                'inner' => $this->__('http://www.example.com').'|'.$this->__('Url Title'),
                'end'   => ']]',
            ),
            'bold' => array(
                'begin' => '**',
                'end'   => '**',
            ),
            'italic' => array(
                'begin' => '//',
                'end'   => '//',
            ),
            'underline' => array(
                'begin' => '__',
                'end'   => '__',
            ),
            'strikethrough' => array(
                'begin' => '--',
                'end'   => '--',
            ),
            'mark' => array(
                'begin' => '++',
                'end'   => '++',
            ),
            'code' => array(
                'begin' => '{{{',
                'end'   => '}}}',
            ),
            'h5' => array(
                'begin' => '======= ',
                'end'   => "\n",
            ),
            'h4' => array(
                'begin' => '====== ',
                'end'   => "\n",
            ),
            'h3' => array(
                'begin' => '==== ',
                'end'   => "\n",
            ),
            'h2' => array(
                'begin' => '=== ',
                'end'   => "\n",
            ),
            'h1' => array(
                'begin' => '== ',
                'end'   => "\n",
            ),

        );
    }
    
    public function img($message)
    {
        $message = preg_replace_callback(
            "#\{\{(.*?)\}\}#si",
            Array($this, 'image_callback'),
            $message
        );
        return $message;
    }
    
    
        
    protected function image_callback($matches)
    {
        $array = explode("|", $matches[1]);
        $image['src']   = $array[0];
        
        if(count($array) == 2) {
            $image['title'] = $array[1];
        } else {
            $image['title'] = null;
        }
        
        return ModUtil::apiFunc($this->name, 'user', 'transform_image', $image);
    }
    
    
    
    private $_codeblocks;

    
    public function preTransform($message)
    {
              
        $message = preg_replace_callback(
            '/^((\*|#)+) *(.*?)$/ms',
            Array($this, 'list_callback_0'),
            $message
        );
        $message = preg_replace_callback(
            "#\n\nli(.*?)>\n\n#si",
            Array($this, 'list_callback_1'),
            $message
        );
        
        
        $message = preg_replace_callback(
            "#\[\[(.*?)\]\]#si",
            Array($this, 'linktest_callback'),
            $message
        );
        
        
        
        /*$message = preg_replace_callback(
            "#<<(.*?)>>#si",
            Array($this, 'function_callback'),
            $message
        );*/
        
        
        return $message;

    }
    
    
    public function postTransform($message)
    {
        return str_replace('CREOLELINKREPLACEMENT', '//', $message);
    }
    
     public function list_callback_0($matches) {

        if(strlen($matches[1]) == 2) {
            $countDoubleStars = substr_count($matches[0], '**');
            $even = ($countDoubleStars & 1) ? false : true; // false = even, true = odd
            if( $even and $countDoubleStars > 0) {
                return $matches[0];
            }
        }

        if($matches[2] == "#") {
            $listtype = 'ol';
        } else {
            $listtype = 'ul';
        }
        $level = strlen($matches[1]);
        return "li>".$level.">".$matches[3].">";
    }
        
    public function list_callback_1($matches) {
        $formatedList = '';
        $list = explode("\n", $matches[0]);
        $lastlevel = 0;
        foreach($list as $l) {
            if(empty($l)) {
                continue;
            }
            $li = explode(">", $l);
            $level = $li[1];
            if( $level > $lastlevel ) {
                $formatedList .= '<ul>';
            } else if ( $level == $lastlevel ) {
                $formatedList .= '</li>';
            } else {
                $formatedList .= '</li></ul></li>';
            }
            $formatedList .= '<li>';
            $formatedList .= $li[2];
            $lastlevel = $level;
        }
        while($lastlevel > 0) {
            $formatedList .= '</li></ul>';
            $lastlevel = $lastlevel -1;
        }
        return $formatedList;
    }
    
    
    protected function function_callback($matches)
    {
       return ModUtil::apiFunc('Wikula', 'action', $matches[1]);
    }
    
    
    protected function linktest_callback($matches)
    {
        $array = explode("|", $matches[1]);
        $url   = $array[0];
        

        if(count($array) == 2) {
            $title = $array[1];
        } else {
            $title = $url;
            $title = ModUtil::apiFunc($this->name, 'user', 'minimize_displayurl', $title);
            $title = str_replace("//", "CREOLELINKREPLACEMENT", $title);
        }
        
        $pos = strpos($url, '://');
        if( $pos === false) {
            $tag = $url;
            $url = ModUtil::url('Wikula', 'user', 'main', array('tag' => $url) );
            if( !ModUtil::apiFunc('Wikula', 'user', 'PageExists', array('tag'=>$tag)) ) {
                $url   = str_replace("//", "CREOLELINKREPLACEMENT", $url);
                return "$title<a href='$url'>?</a>";
            }
        }
        
        $url   = str_replace("//", "CREOLELINKREPLACEMENT", $url);
        return "<a href='$url'>$title</a>";
    }
    
    
    public function code() {
        return "\{\{\{(.*?)\}\}\}";
    }
    
    
}