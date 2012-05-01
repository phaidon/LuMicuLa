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

class LuMicuLa_Api_Markdown extends Zikula_AbstractApi 
{
    
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
            ),
            'img' => array(
                'begin' => '[img]',
                'inner' => 'http://www.example.com/image.png',
                'end'   => '[/img]',
            ),
           'page' => array(
                'begin' => '[url]',
                'inner' => $this->__('Page'),
                'end'   => '[/url]',
            ),*/
            'link' => array(
                'begin' => '['.$this->__('Title').'](',
                'inner' => $this->__('http://www.example.com'),
                'end'   => ')',
                'pattern' => '/\[([^\[]*)\]\((.*?)\)/si',
                'callback' => 'link2'

            ),
            /*'list' => array(
                'begin' => '[list] [*]',
                'end'   => '[/list]',
                'func'  => '[list]VALUE[/list]',
            ),*/
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
            /*'underline' => array(
                'begin' => '[u]',
                'end'   => '[/u]',
            ),
            'strikethrough' => array(
                'begin' => '[s]',
                'end'   => '[/s]',
            ),*/
            'hr' => array(
                'begin' => '* * *',
                'inner' => '',
                'end'   => '',
            ),
            /*'mark' => array(
                'begin' => '[mark]',
                'end'   => '[/mark]',
            ),
            'center' => array(
                'begin' => '[center]',
                'end'   => '[/center]',
            ),
            'size' => array(
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
            ),
            'subscript'   => array(
                'begin' => '[sub]',
                'end'   => '[/sub]',
            ),
            'superscript'=> array(
                'begin' => '[sup]',
                'end'   => '[/sup]',
            ),*/
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
            'blockquote' => array(
                'begin' => ">",
                'end'   => "\n",
                'pattern' => '/^>(.*?)\n/mi',
                
            ),            
            /*'monospace' => array(
                'begin' => '[monospace]',
                'end'   => '[/monospace]',
            ),
            'key' => array(
                'begin' => "[key]",
                'end'   => "[/key]",
            ),*/
        );
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