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

class LuMicuLa_Language_Creole extends LuMicuLa_Language_Common 
{
    
    
    public function __construct()
    {
        $this->protect = true;
    }
    
    
    /**
     * Creole elements
     *
     * @see http://www.wikicreole.org/
     * @return list of Creole elements
     */    
    
    public function elements()
    {
        return array(
            'code' => array(
                'begin' => '{{{',
                'end'   => '}}}',
                'func'  => true,
            ),
            'img' => array(
                'begin'     => '{{',
                'inner'     => 'http://www.example.com/image.png',
                'end'       => '}}',
                'func'      => true
            ),
            'youtube' => array(
                'begin'     => '<<youtube id=',
                'end'       => '>>',
            ),
            'indent' => array(
                'begin'     => ":",
                'end'       => "\n",
                'pattern'   => '/^\:(.*?)\n/mi',
                
            ), 
            'table' => array(
                'begin'     => "table",
                'end'       => "table",
                'pattern'   => '/\n\|(.*?)\|\n\n/si',
                'func'      => true
                
            ), 
            'hr' => array(
                'begin'     => '----',
                'end'       => '',
            ),
            'list' => array(
                'begin'     => '^\* ',
                'end'       => '\n$',
                'inner'     => '',
                'func'      => true,
                'regexp'    => true // preg_quote
            ),
            'link' => array(
                'begin'     => '[[',
                'inner'     => __('http://www.example.com').'|'.__('Url Title'),
                'end'       => ']]',
                'func'      => true,
            ),
            'page' => array(
                'begin'     => '[[',
                'inner'     => __('Page').'|'.__('Page Title'),
                'end'       => ']]',
                'noreplace' => true
            ),
            'bold' => array(
                'begin'     => '**',
                'end'       => '**',
            ),
            'italic' => array(
                'begin'     => '//',
                'end'       => '//',
            ),
            'underline' => array(
                'begin'     => '__',
                'end'       => '__',
            ),
            'strikethrough' => array(
                'begin'     => '--',
                'end'       => '--',
            ),
            'mark' => array(
                'begin'     => '++',
                'end'       => '++',
            ),
            'monospace' => array(
                'begin'     => "##",
                'end'       => "##",
            ),
            'key' => array(
                'begin'     => "#%",
                'end'       => "#%",
            ),
           'subscript'   => array(
                'begin'     => ',,',
                'end'       =>  ',,',
            ),
            'superscript'=> array(
                'begin'      => '^^',
                'end'        =>  '^^',
            ),
            'headings'  => array(
                'subitems' => array(
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
                    )
                )
            )
           

        );
    }
    

    
    
    
    public function img_callback($matches)
    {
        $array = explode("|", $matches[1]);
        $src   = $array[0];
        if (isset($array[1])) {
            $title = $array[1];
        } else {
            $title = $src;
        }
        return $this->image($src, $title);
    }
    
    
    public function list_callback($matches)
    {        
        $prevLevel = 0;
        $result = '';
        $array0 = explode("\n", '* '.$matches[1]);
        foreach($array0 as $value) {
            $array1 = explode(" ", $value);
            $level  = strlen($array1[0]);
            unset($array1[0]);
            $value  = implode(' ', $array1);
            if($level > $prevLevel) {
                $result .= '<ul>';
            } else if ($level < $prevLevel) {
                $result .= '</ul>';
            }
            $result .= '<li>'.$value.'</li>';
            $prevLevel = $level; 

        }
        $result = str_replace('</li><ul>', '<ul>', $result);
        $result = str_replace('</ul><li>', '</ul></li><li>', $result);  
        $array = array();
        for ($i = 1; $i <= $level; $i++) {
            $array[$i] = '</ul>'; 
        }
        $result .= implode('</li>',$array);
        return $result;
    }
    
    
    public function link_callback($matches)
    {        
        $array = explode("|", $matches[1]);
        $url = $array[0];
 
        if(count($array) > 1) {
            $title = $array[1];
        } else {
            $title = $url;
        }
        return $this->link($url, $title);
    }
    
    public function table_callback($matches)
    {        
        $rows = explode("\n", '|'.$matches[1].'|');
        $inner = "\n<table border=1>";
        foreach($rows as $row) {
            $inner .= '<tr>';
            $cells = explode("|", $row);
            for($i = 1; $i < count($cells)-1; $i++) {
                $cell = str_replace('\\', "\n", $cells[$i]);
                if (substr($cell, 0, 1) == '=') {
                    $inner .= '<th>'.substr($cell, 1).'</th>';
                } else {
                    $inner .= '<td>'.$cell.'</td>';
                }
            }
            $inner .= '</tr>';
        }
        
        $inner .= "</table>\n";
        return $inner;
    }
    
   
}