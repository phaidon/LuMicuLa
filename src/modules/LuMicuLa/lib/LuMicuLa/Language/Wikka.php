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

class LuMicuLa_Language_Wikka extends LuMicuLa_Language_Common
{

    public function __construct()
    {
        $this->protect = true;
    }
    
    /**
     * Wikka elements
     *
     * @return list of Wikka elements
     */    
    public function elements()
    {
        return array(
        
            'nomarkup' => array(
                'begin' => '""',
                'end'   => '""',
                'func'  => true,               
            ),
            'link' => array(
                'begin' => '[[',
                'inner' => $this->__('http://www.example.com').' '.$this->__('Url Title'),
                'end'   => ']]',
                'func'  => true,
            ),
            'img' => array(
                'begin' => '{{image ',
                'inner' => 'url=&quot;http://www.example.com/image.png&quot;',
                'end'   => '}}',
                'func'  => true,
            ),
            'page' => array(
                'begin' => '[[',
                'inner' => $this->__('Page').' '.$this->__('Page Title'),
                'end'   => ']]',
            ),
            'bold' => array(
                'begin' => '**',
                'end'   => '**',
            ),

            'box' => array(
                'begin' => "<<",
                'end'   => '<<',
            ),
            'center' => array(
                'begin' => '@@',
                'end'   => '@@',
            ),
            'code' => array(
                'begin' => '%%',
                'end'   => '%%',
                'func'  => true,
            ),
            'color' => array(
                'begin' => '{{color c="blue" text="',
                'end'   => '"}}',
                'pattern' => '/\{\{color (.*?)\}\}/si',
                'func'  => true
            ),
            'clear' => array(
                'begin' => '::c::',
                'end'   => '',
            ),
            'headings'  => array(
                'subitems' => array(
                    'h1' => array(
                        'begin' => '======',
                        'end'   => '======',
                    ),
                    'h2' => array(
                        'begin' => '=====',
                        'end'   => '=====',
                    ),
                    'h3' => array(
                        'begin' => '====',
                        'end'   => '====',
                    ),
                    'h4' => array(
                        'begin' => '===',
                        'end'   => '===',
                    ),
                    'h5' => array(
                        'begin' => '==',
                        'end'   => '==',
                    ),

                )
            ),
            'hr' => array(
                'begin' => '----',
                'inner' => '',
                'end'   => '',
            ),
            'list' => array(
                'begin' => '~-',
                'end'   => "\n",
            ),
            'indent' => array(
                'begin' => '~',
                'end'   => "\n",
            ),
            'italic' => array(
                'begin' => '//',
                'end'   => '//',
            ),
            'key' => array(
                'begin' => "#%",
                'end'   => "#%",
            ),         
            'mark' => array(
                'begin' => "''",
                'end'   => "''",
            ),
            'monospace' => array(
                'begin' => "##",
                'end'   => "##",
            ),
            'strikethrough' => array(
                'begin' => '++',
                'end'   => '++',
            ),
            'subscript'   => array(
                'begin'      => ',,',
                'end'        =>  ',,',
            ),
            'superscript' => array(
                'begin'      => '^^',
                'end'        =>  '^^',
            ),
            'table' => array(
                'begin'      => '{{table columns="3" cellpadding="1" cells="',
                'inner'      => 'BIG;GREEN;FROGS;yes;yes;no;no;no;###',
                'end'        =>  '"}}',
                'pattern' => '/\{\{table (.*?)\}\}/si',
                'func'       => true,
                'alternatives' => array(
                    array(
                        'begin' => '#|',
                        'end'   => '|#',
                        'func'  => true
                    ),
                 ),
            ),
            'underline' => array(
                'begin' => '__',
                'end'   => '__',
            ),
            'category' => array(
                'begin' => 'Category',
                'end'   => '',
            ),
        );
    }
 
    public function img_callback($matches)
    {        
        $attributes = $this->getAtrributes($matches[1]);
        return $this->image($attributes['url']);
    }
    


 
    public function link_callback($matches)
    {
        $array = explode(" ", $matches[1]);
        $url = $array[0];
        unset($array[0]);
 
        if(count($array) > 0) {
            $title = implode(' ', $array);
        } else {
            $title = $url;
        }
                
        return $this->link($url, $title);
    }
    
    public static function list_callback($matches)
    {    
        
        $prevLevel = 0;
        $result = '';
        $array0 = explode("\n", '  * '.$matches[1]);
        
        foreach($array0 as $value) {
            $array1 = explode("*", $value);
            $level  = strlen($array1[0])/2;
            $value  = substr($array1[1], 1); 
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
     
    public function color_callback($matches)
    {           
        $attributes = $this->getAtrributes($matches[1]);
        if(empty($attributes['text'])) {
            return '';
        }
        
        $color = '';
        if(!empty($attributes['fg'])) {
            $color = 'color:'.$attributes['fg'].';';
        } else if(!empty($attributes['c'])) {
            $color = 'color:'.$attributes['c'].';';
        } else if(!empty($attributes['hex'])) {
            $color = 'color:'.$attributes['hex'].';';
        }
        $bgcolor = '';
        if(!empty($attributes['bg'])) {
            $bgcolor = 'background-color:'.$attributes['bg'];
        }
        return '<span style="'.$color.$bgcolor.'">'.$attributes['text'].'</span>';
    }
    
    public function table_callback($matches)
    {
        if (substr($matches[0], 0, 2) == '{{') {
        
            $attributes = $this->getAtrributes($matches[1]);

            $settings = '';
            if(!empty($attributes['cellpadding'])) {
                $settings = ' cellpadding="'.$attributes['cellpadding'].'"';
            }
            if(!empty($attributes['border'])) {
                $settings = ' border="'.$attributes['border'].'"';
            }

            $inner = '';
            $cells = explode(';',$attributes['cells']);
            $columns = $attributes['columns'];


            $j = 0;
            for($i = 1; $i <= $columns; $i++) {
                $inner .= '<tr>';
                for($j = $j; $j < $i*count($cells)/$columns; $j++) {
                    if($cells[$j] == '###') {
                        $cells[$j] = '';
                    }

                    $inner .= '<td>'.$cells[$j].'</td>';
                }
                $inner .= '</tr>';
            }        
            return '<table'.$settings.'>'.$inner.'</table>';
        } else {
            if (substr($matches[0], 0, 3) == '#||') {
                $border = 1;
            } else {
                $border = 0;
            }
            
            $rows = explode("\n", $matches[1]);
            $inner = '';
            for($i = 1; $i < count($rows)-1; $i++) {
                $inner .= '<tr>';
                
                $row = str_replace('||', '', $rows[$i]);
                $cells = explode("|", $row);
                for($j = 0; $j < count($cells); $j++) {
                    $inner .= '<td>'.$cells[$j].'</td>';
                }
                $inner .= '</tr>';
            }      
            return '<table border="'.$border.'">'.$inner.'</table>';    
        }
    }
    
    public function getAtrributes($match) {
        $result = array();
        $array  = explode(' ', $match);
        foreach($array as $value) {
            list($key, $value) = explode('=', $value);
            $result[$key] = str_replace('"', '', $value);
        }
        return $result;
    }
     
    
}