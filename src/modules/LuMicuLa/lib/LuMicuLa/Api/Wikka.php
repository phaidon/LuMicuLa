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

class LuMicuLa_Api_Wikka extends Zikula_AbstractApi 
{


    /**
    * Wikka elements
    *
    * @return list of Wikka elements
    */    
    
    public function elements()
    {
        return array(
        
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
                'begin' => '{{color ',
                'end'   => '}}',
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
            'img' => array(
                'begin' => '{{image ',
                'inner' => 'url=&quot;http://www.example.com/image.png&quot;',
                'end'   => '}}',
                'func'  => true,
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
            'link' => array(
                'begin' => '[[',
                'inner' => $this->__('http://www.example.com').' '.$this->__('Url Title'),
                'end'   => ']]',
                'func'  => true,
            ),
            'list' => array(
                'begin' => '^  \* ',
                'end'   => '\n$',
                'inner' => '',
                'func'  => true,
                'regexp'=> true // preg_quote
            ),              
            'mark' => array(
                'begin' => "''",
                'end'   => "''",
            ),
            'monospace' => array(
                'begin' => "##",
                'end'   => "##",
            ),
            'nomarkup' => array(
                'begin' => '""',
                'end'   => '""',
                'func'  => true,               
            ),
           'page' => array(
                'begin' => '[[',
                'inner' => $this->__('Page').' '.$this->__('Page Title'),
                'end'   => ']]',
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
                'begin'      => '{{table ',
                'end'        =>  '}}',
                'func'       => true,
            ),
            'underline' => array(
                'begin' => '__',
                'end'   => '__',
            ),   
        );
    }
 
    public static function img_callback($matches)
    {        
        $attributes = ModUtil::apiFunc('LuMicuLa', 'Wikka', 'getAtrributes', $matches[1]);
        $attributes['src'] = $attributes['url'];
                   
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_image', $attributes);
    }
    

    public static function code_callback($matches)
    {
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_code', $matches[1]);
    }
    
    
    public static function nomarkup_callback($matches)
    {
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_nomarkup', $matches[1]);
    }
 
 
    public static function link_callback($matches)
    {        
        
        $array = explode(" ", $matches[1]);
        $link = array();
        $link['url'] = $array[0];
        unset($array[0]);
 
        if(count($array) > 0) {
            $link['title'] = implode(' ', $array);
        }
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_link', $link);
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
     
    public static function color_callback($matches)
    {        
        $attributes = ModUtil::apiFunc('LuMicuLa', 'Wikka', 'getAtrributes', $matches[1]);
        extract($attributes);
        
        $color = '';
        if(!empty($fg)) {
            $color = 'color:'.$fg.';';
        } else if(!empty($c)) {
            $color = 'color:'.$c.';';
        } else if(!empty($hex)) {
            $color = 'color:'.$hex.';';
        }
        $bgcolor = '';
        if(!empty($bg)) {
            $bgcolor = 'background-color:'.$bg;
        }
        return '<span style="'.$color.$bgcolor.'">'.$text.'</span>';
    }
    
    public static function table_callback($matches)
    {        
        $attributes = ModUtil::apiFunc('LuMicuLa', 'Wikka', 'getAtrributes', $matches[1]);
        extract($attributes);
        

        if(empty($cellpadding)) {
            $cellpadding = '';
        } else {
            $cellpadding = ' cellpadding="'.$cellpadding.'"';
        }

        $inner = '';
        $cells = explode(';',$cells);
        // print_r($cells);

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
        return '<table'.$cellpadding.'>'.$inner.'</table>';
    }
    
    public function getAtrributes($match) {
        $result = array();
        $array  = explode('" ', substr($match,0,-1));
        foreach($array as $value) {
            list($key, $value) = explode('="', $value);
            $result[$key] = $value;
        }
        return $result;
    }
    
    
    public function extractCategories($message)
    {
        
        $message = preg_replace_callback(
            "#\n\[\[Category(.*?)\]\]#si",
            array('LuMicuLa_Api_Transform', 'categoryCallback'),
            $message
        );
        $message = preg_replace_callback(
            "#\nCategory([a-zA-Z0-9]*+)#si",
            array(LuMicuLa_Api_Transform, 'categoryCallback'),
            $message
        );
        return $message;
    }
    
    
    
    
     public function getPageCategories($text) {
        $categories = array();        
        preg_match_all("/\n\[\[Category(.*?)\]\]/", $text, $categories);
        $categories = $categories[1];
        $categories2 = array();        
        preg_match_all("/\nCategory([a-zA-Z0-9]*+)/", $text, $categories2);
        $categories2 = $categories2[1];
        $categories = array_merge($categories, $categories2);
        
        foreach($categories as $key => $value) {
            $value = explode(' ', $value);
            $value = $value[0];
            $categories[$key] = $value;
        }
        return array_unique($categories);
    }
    
    
    public function getPageLinks($text) {
        $links = array();
        $pagelinks = array();
        preg_match_all("/\[\[(.*?)\]\]/", $text, $links);
        $links = $links[1];
        foreach($links as $link) {
            $link = explode(' ', $link);
            // check if link is a hyperlink
            if( strstr($link[0], '://' ) or strstr($link[0], '@' ) ) {
                continue;
            }
            $pagelinks[] = $link[0];                 
        }
        return array_unique($pagelinks);
    }

    
}