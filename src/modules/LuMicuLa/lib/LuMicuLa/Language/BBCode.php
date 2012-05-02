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

class LuMicuLa_Language_BBCode extends LuMicuLa_Language_Common
{
    
    public function __construct()
    {
        $this->protect = false;
    }
    
    
    /**
     * BBCode elements
     *
     * @see http://www.bbcode.org/reference.php
     * @return list of BBCode elements
     */ 
    
    
    public function elements() {
        
        // define elements
        return array(
            'code' => array(
                'begin' => '[code]',
                'end'   => '[/code]',
            ),
            'nomarkup' => array(
                'begin' => '[noparse]',
                'end'   => '[/noparse]',
            ),
            'img' => array(
                'begin' => '[img]',
                'inner' => 'http://www.example.com/image.png',
                'end'   => '[/img]',
            ),
            'page' => array(
                'begin' => '[page]',
                'inner' => __('Page'),
                'end'   => '[/page]',
            ),
            'link' => array(
                'begin' => '[url]',
                'inner' => __('http://www.example.com'),
                'end'   => '[/url]',
                'pattern' => '/\[url=?(.*?)\](.*?)\[\/url\]/mi',
                'func' => true
            ),
            'list' => array(
                'begin' => '[list] [*]',
                'end'   => '[/list]',
                'func'  => '[list]VALUE[/list]',
            ),
            'indent' => array(
                'begin' => '[indent]',
                'end'   => '[/indent]',
            ),
            'bold' => array(
                'begin' => '[b]',
                'end'   => '[/b]',
            ),
            'italic' => array(
                'begin' => '[i]',
                'end'   => '[/i]',
            ),
            'underline' => array(
                'begin' => '[u]',
                'end'   => '[/u]',
            ),
            'strikethrough' => array(
                'begin' => '[s]',
                'end'   => '[/s]',
            ),
            'hr' => array(
                'begin' => '[hr]',
                'inner' => '',
                'end'   => '',
            ),
            'mark' => array(
                'begin' => '[highlight]',
                'end'   => '[/highlight]',
            ),
            'center' => array(
                'begin' => '[center]',
                'end'   => '[/center]',
            ),
            'size' => array(
                'begin' => '[size=+1]',
                'end'   => '[/size]',
                'pattern' => '/\[size=(.*?)\](.*?)\[\/size]/mi',
                'func'  => true
            ),
            'table' => array(
                'begin' => '[table][tr][td]',
                'end'   => '[/td][/tr][/table]',
                'items' => array(
                    'table' => array(
                        'begin' => '[table]',
                        'end'   => '[/table]',
                    ),
                    'tr' => array(
                        'begin' => '[tr]',
                        'end'   => '[/tr]',
                    ),
                    'td' => array(
                        'begin' => '[td]',
                        'end'   => '[/td]',
                    ),
                 ),
                
            ),
            'color' => array(
                'begin' => '[color=red]', 
                'end'   => '[/color]',
                'pattern' => '/\[color=(.*?)\](.*?)\[\/color\]/mi',
                'func'  => true
            ),
            'youtube' => array(
                'begin' => '[youtube]',
                'end'   => '[/youtube]',
            ),
            'subscript'   => array(
                'begin' => '[sub]',
                'end'   => '[/sub]',
            ),
            'superscript'=> array(
                'begin' => '[sup]',
                'end'   => '[/sup]',
            ),
            'headings'  => array(
                'subitems' => array(
                    'h5' => array(
                        'begin' => '[h5]',
                        'end'   => '[/h5]',
                    ),
                    'h4' => array(
                        'begin' => '[h4]',
                        'end'   => '[/h4]',
                    ),
                    'h3' => array(
                        'begin' => '[h3]',
                        'end'   => '[/h3]',
                    ),
                    'h2' => array(
                        'begin' => '[h2]',
                        'end'   => '[/h2]',
                    ),
                    'h1' => array(
                        'begin' => '[h1]',
                        'end'   => '[/h1]',
                    ),
                 ),
             ),
            'monospace' => array(
                'begin' => '[monospace]',
                'end'   => '[/monospace]',
            ),
            'key' => array(
                'begin' => "[key]",
                'end'   => "[/key]",
            ),
        );
    }
    
    
    public function link_callback($matches)
    {               
        
        if(empty($matches[1])) {
            $url   = $matches[2];
            $title = $matches[2];
        } else {
            $url   = $matches[1];
            $title = $matches[2];
        }
        
        return $this->link($url, $title);
        
    }
    
    
    public function color_callback($matches)
    {   
        return $this->color($matches[1], $matches[2]);
    }
    
    
    public function size_callback($matches)
    {   
        return $this->size($matches[1], $matches[2]);
    }
    
    
    
    public function list_callback($matches)
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