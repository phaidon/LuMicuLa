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

class LuMicuLa_Api_BBCode extends Zikula_AbstractApi 
{
    
    private $elements = array();
    
    
    public function __construct() {
        
        // define elements
        $this->elements = array(
            'code' => array(
                'begin' => '[code]',
                'end'   => '[/code]',
                'func'  => true,
                'gfunc' => true // general func
            ),
            'img' => array(
                'begin' => '[img]',
                'inner' => 'http://www.example.com/image.png',
                'end'   => '[/img]',
                'func'  => true,
            ),
           'page' => array(
                'begin' => '[url]',
                'inner' => $this->__('Page'),
                'end'   => '[/url]',
            ),
            'link' => array(
                'begin' => '[url]',
                'inner' => $this->__('http://www.example.com'),
                'end'   => '[/url]',
                'func'  => '[url=VALUE]VALUE[/url]',
                'gfunc' => true // general func
            ),
            'list' => array(
                'begin' => '[list] [*]',
                'end'   => '[/list]',
                'func'  => '[list]VALUE[/list]',
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
                'func'  => '[table]VALUE[/table]'
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
                'begin'      => '[sub]',
                'end'        =>  '[/sub]',
            ),
            'superscript'=> array(
                'begin'      => '[sup]',
                'end'        =>  '[/sup]',
            ),
            'headings'  => array(
                'subitems' => array(
                    'h5' => array(
                        'begin' => '[h5] ',
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
    
    /**
    * BBCode elements
    *
    * @see http://www.bbcode.org/reference.php
    * @return list of BBCode elements
    */ 
        
    public function elements()
    {
        return $this->elements;
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
    
    
    
    public static function table_callback($matches)
    {        
        $table = str_replace("\n",    '',      $matches[1]);
        $table = str_replace('<br>',  '',      $table);
        $table = str_replace('[tr]',  '<tr>',  $table);
        $table = str_replace('[/tr]', '</tr>', $table);
        $table = str_replace('[td]',  '<td>',  $table);
        $table = str_replace('[/td]', '<td>',  $table);
        return '<table>'.$table.'</table>';
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
   
    
    public function extractCategories($message)
    {
        // there are no categories in BBCode
        return $message;
    }
    
    
    public function getPageLinks($text = '') {
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
    
}