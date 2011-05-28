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

class LuMicuLa_Api_Wakka extends Zikula_AbstractApi 
{

    /**
    * Wakka elements
    *
    * @return list of Wakka elements
    */    
    
    public function elements()
    {
        return array(
            'code' => array(
                'begin' => '%%',
                'end'   => '%%',
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
            'link' => array(
                'begin' => '[[',
                'inner' => $this->__('http://www.example.com').' '.$this->__('Url Title'),
                'end'   => ']]',
                'func'  => true,
            ),
            'hr' => array(
                'begin' => '----',
                'inner' => '',
                'end'   => '',
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
                'begin' => '++',
                'end'   => '++',
            ),
            'mark' => array(
                'begin' => "''",
                'end'   => "''",
            ),
            'monospace' => array(
                'begin' => "##",
                'end'   => "##",
            ),
            'key' => array(
                'begin' => "#%",
                'end'   => "#%",
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
            )
            
        );
    }
 
    public static function img_callback($matches)
    {
        $array0 = explode('" ', $matches[1]);
        $image = array();
        foreach($array0 as $value) {
            list($key, $value) = explode('="', $value);
            if($key == 'url') {
                $key = 'src';
            }
            $image[$key] = $value;
        }
                
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_image', $image);
    }
    

    public static function code_callback($matches)
    {
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_code', $matches[1]);
    }
 
    
 
    public static function link_callback($matches)
    {        
        $array = explode(" ", $matches[1]);
        $link['url'] = $array[0];
        unset($array[0]);
 
        if(count($array) > 0) {
            $link['title'] = implode(' ', $array);
        }
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_link', $link);
    }
    
    
    
}