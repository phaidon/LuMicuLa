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
            'code' => array(
                'begin' => '{{{',
                'end'   => '}}}',
                'func'  => true,
            ),
            'img' => array(
                'begin' => '{{',
                'inner' => 'http://www.example.com/image.png',
                'end'   => '}}',
                'func'  => true
            ),
            'youtube' => array(
                'begin' => '<<youtube id=',
                'end'   => '>>',
            ),
            'hr' => array(
                'begin' => '----',
                'end'   => '',
            ),
            'list' => array(
                'begin' => '^\* ',
                'end'   => '\n$',
                'inner' => '',
                'func'  => true,
                'regexp'=> true // preg_quote
            ),
           'page' => array(
                'begin' => '[[',
                'inner' => $this->__('Page').'|'.$this->__('Page Title'),
                'end'   => ']]',
            ),
            'link' => array(
                'begin' => '[[',
                'inner' => $this->__('http://www.example.com').'|'.$this->__('Url Title'),
                'end'   => ']]',
                'func'  => true,
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
            'monospace' => array(
                'begin' => "##",
                'end'   => "##",
            ),
            'key' => array(
                'begin' => "#%",
                'end'   => "#%",
            ),
           'subscript'   => array(
                'begin'      => ',,',
                'end'        =>  ',,',
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
    

    
    
    
    public static function img_callback($matches)
    {
        $array = explode("|", $matches[1]);
        $image['src']   = $array[0];
        
        if(count($array) == 2) {
            $image['title'] = $array[1];
        }
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_image', $image);
    }
    
    public static function code_callback($matches)
    {
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_code', $matches[1]);
    }
    
    
    public static function list_callback($matches)
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
    
    
    public static function link_callback($matches)
    {        
        $array = explode("|", $matches[1]);
        $link['url'] = $array[0];
 
        if(count($array) > 1) {
            $link['title'] = $array[1];
        }
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_link', $link);
    }
    
}