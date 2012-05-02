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

class LuMicuLa_Language_Markdown extends LuMicuLa_Language_Common
{
    
    public function __construct()
    {
        $this->protect = true;
    }
    
    
    /**
     * Markdown elements
     *
     * @see http://en.wikipedia.org/wiki/Markdown
     * @return list of Markdown elements
     */ 
    
    
    public function elements() {
        
        // define elements
        return array(
            /*'code' => array(
                'begin' => '[code]',
                'end'   => '[/code]',
                'func'  => true,
                'gfunc' => true // general func
            ),*/
            'img' => array(
                'begin' => '!['.__('Title').'](',
                'inner' => 'http://www.example.com/image.png',
                'end'   => ')',
                'pattern' => '/\!\[([^\[]*)\]\((.*?)\)/si',
                'func' => true
            ),
           /*'page' => array(
                'begin' => '[url]',
                'inner' => $this->__('Page'),
                'end'   => '[/url]',
            ),*/
            'link' => array(
                'begin' => '['.__('Title').'](',
                'inner' => __('http://www.example.com'),
                'end'   => ')',
                'pattern' => '/\[([^\[]*)\]\((.*?)\)/si',
                'func' => true

            ),
            'bold' => array(
                'begin' => '**',
                'end'   => '**',
                'alternatives' => array(
                    array(
                        'begin' => '__',
                        'end'   => '__',
                    ),
                 ),
            ),
            'italic' => array(
                'begin' => '*',
                'end'   => '*',
                'alternatives' => array(
                    array(
                        'begin' => '_',
                        'end'   => '_',
                    ),
                 ),
            ),
            'strikethrough' => array(
                'begin' => '~~',
                'end'   => '~~',
            ),
            'hr' => array(
                'begin' => '* * *',
                'inner' => '',
                'end'   => '',
            ),
            'headings'  => array(
                'subitems' => array(
                    'h5' => array(
                        'begin' => '#####',
                        'end'   => "\n",
                    ),
                    'h4' => array(
                        'begin' => '#### ',
                        'end'   => "\n",
                    ),
                    'h3' => array(
                        'begin' => '### ',
                        'end'   => "\n",
                    ),
                    'h2' => array(
                        'begin' => '## ',
                        'end'   => "\n",
                    ),
                    'h1' => array(
                        'begin' => '# ',
                        'end'   => "\n",
                    ),
                 ),
             ),
            'indent' => array(
                'begin' => ">",
                'end'   => "\n",
                'pattern' => '/^>(.*?)\n/mi',
                
            ),
        );
    }
    
    
    public function replaceByFunc($tag, $text) {
            
        $elements = $this->elements();    
            
        if(!isset($elements[$tag]['pattern'])) {
            $pattern = "#".$elements[$tag]['begin']."(.*?)".$elements[$tag]['end']."#si";
        } else {
            $pattern = $elements[$tag]['pattern'];
        }
    
    
        return preg_replace_callback(
            $pattern,
            array($this, $tag.'_callback'),
            $text
        );
    }
    
    
    public function link_callback($matches)
    {        
        $url   = $matches[2];
        $title = $matches[1];
        return $this->link($url, $title);
        
    }
    
    
    public function img_callback($matches)
    {        
        $src   = $matches[2];
        $title = $matches[1];
        return $this->image($src, $title);
        
    }
    
    
    public static function list_callback($matches)
    {   
        $list = str_replace("\n", '', $matches[1]);
        $list = str_replace('<br>', '', $list);
        $list = explode("[*]", $list);
        
        $result = '';
        foreach($list as $li) {
            if($li != ' ' and !empty($li)) {
               $result .= '<li>'.$li.'</li>';
            }
        }
        return '<ul>'.$result.'</ul>';
    }
    
}