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

class LuMicuLa_Language_Wikimedia extends LuMicuLa_Language_Common
{
    
    /**
     * Mediawiki elements
     *
     * @see http://www.mediawiki.org/wiki/Help:Formatting
     * @return list of Markdown elements
     */ 
    
    
    public function elements() {
        
        // define elements
        return array(
            'nomarkup' => array(
                'begin' => '<nomarkup>',
                'end'   => '</nomarkup>',
            ),
            'code' => array(
                'begin' => '<code>',
                'end'   => '</code>',
            ),
            /*'img' => array(
                'begin' => '[img]',
                'inner' => 'http://www.example.com/image.png',
                'end'   => '[/img]',
            ),*/
            'page' => array(
                'begin' => '[[',
                'inner' => __('Page'),
                'end'   => ']]',
                'func' => true
            ),
            'link' => array(
                'begin' => '[',
                'inner' => __('http://www.example.com').' '.__('Title'),
                'end'   => ']',
                'pattern' => '/\[(\S*?)[[:space:]](.*?)\]/si',
                'func' => true

            ),
            'list' => array(
                'begin' => '* ',
                'end'   => '',
                'pattern' => '/^\*(.*?)\n/mi',
            ),
            'bold' => array(
                'begin' => "'''",
                'end'   => "'''",
            ),
            'italic' => array(
                'begin' => "''",
                'end'   => "''",
            ),
            'underline' => array(
                'begin' => '{{underline|',
                'end'   => '}}',
            ),
            'strikethrough' => array(
                'begin' => '{{strikethrough|',
                'end'   => '}}',
            ),
            'hr' => array(
                'begin' => '----',
                'inner' => '',
                'end'   => '',
            ),
            'mark' => array(
                'begin' => '<mark>',
                'end'   => '</mark>',
            ),
            'center' => array(
                'begin' => '<center>',
                'end'   => '</center>',
            ),
            /*'size' => array(
                'begin' => '[size=VALUE]',
                'end'   => '[/size]',
            ),
            'table' => array(
                'begin' => '[table][tr][td]',
                'end'   => '[/td][/tr][/table]',
                'subitems' => array(
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
                'begin' => '[color=VALUE]',
                'end'   => '[/color]',
            ),
            'youtube' => array(
                'begin' => '[youtube]',
                'end'   => '[/youtube]',
            ),*/
            'subscript'   => array(
                'begin' => '<sub>',
                'end'   => '</sub>',
            ),
            'superscript'=> array(
                'begin' => '<sup>',
                'end'   => '</sup>',
            ),
            'headings'  => array(
                'subitems' => array(
                    'h5' => array(
                        'begin' => '===== ',
                        'end'   => '=====',
                    ),
                    'h4' => array(
                        'begin' => '====',
                        'end'   => '====',
                    ),
                    'h3' => array(
                        'begin' => '===',
                        'end'   => '===',
                    ),
                    'h2' => array(
                        'begin' => '==',
                        'end'   => '==',
                    ),
                    'h1' => array(
                        'begin' => '==',
                        'end'   => '==',
                    ),
                 ),
             ),
            'blockquote' => array(
                'begin' => '<blockquote>',
                'end'   => '</blockquote>',
            ),            
            'monospace' => array(
                'begin' => '<monospace>',
                'end'   => '</monospace>',
            ),
            'key' => array(
                'begin' => "<key>",
                'end'   => "</key>",
            ),
        );
    }
    
    
    public function replaceByFunc($tag, $pattern, $text) {
                      
        return preg_replace_callback(
            $pattern,
            array($this, $tag.'_callback'),
            $text
        );
    }
    
    public function page_callback($matches)
    {
        $args = array();
        // seperate title and url
        $list = explode("|", $matches[1]);
        // check if a title is set
        if(count($list) > 1) {
            $url   = $list[1];
        } else {
            $url   = $list[0];            
        }
        $title = $list[0];
        return $this->page($url, $title);
    }
    
    
    public function link_callback($matches)
    {                
        if(!isset($matches[2])) {
            $url   = $matches[1];
            $title = $matches[1];
        } else {
            $url   = $matches[1];
            $title = $matches[2];
        }
        
        return $this->link($url, $title);
        
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