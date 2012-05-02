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

class LuMicuLa_Api_User extends Zikula_AbstractApi 
{
    
   /**
    * Wiki elements wrapper
    *
    * @return list of language elements
    */
    public function elements($editor_settings = null)
    {

        $elements = array(
            'code'          => array(
                'icon'         => 'code.png',
                'title'        => $this->__('Code'),
                'inner'        => $this->__('Code'),
            ),
            'nomarkup'      => array(
                'icon'         => 'code.png',
                'title'        => $this->__('No markup'),
                'inner'        => $this->__('No markup'),
            ),
            'table'           => array(
                'icon'         => 'table.png',
                'title'        => $this->__('Table'),
                'inner'        => $this->__('table data'),
            ),
            'page'          => array(
                'icon'         => 'page.png',
                'title'        => $this->__('Page link'),
            ),
            'link'          => array(
                'icon'         => 'link.png',
                'title'        => $this->__('Web link'),
            ),
            'list'            => array(
                'icon'         => 'list.png',
                'title'        => $this->__('List'),
                'inner'        => $this->__('list item'),
            ),
            'img'           => array(
                'icon'         => 'img.png',
                'title'        => $this->__('Image'),
                'preview'      => false,
            ),
            'youtube'       => array(
                'icon'         => 'youtube.png',
                'title'        => $this->__('YouTube video'),
                'inner'        => 'ID',
                'preview'      => false,
            ),
            'hr'            => array(
                'icon'         => 'hr.png',
                'title'        => $this->__('Horizontal line'),
                'inner'        => ''
            ),
            'box'            => array(
                'icon'         => 'box.png',
                'title'        => $this->__('box'),
                'inner'        => $this->__('boxed text'),
            ),
            'clear'            => array(
                'icon'         => 'box.png',
                'title'        => $this->__('clear'),
                'inner'        => '',
            ),
            'indent'         => array(
                'icon'         => 'indent.png',
                'title'        => $this->__('indent'),
                'inner'        => $this->__('text'),
            ),
            'italic'        => array(
                'icon'         => 'italic.png',
                'title'        => $this->__('italicized text'),
                'inner'        => $this->__('italicized text'),
                'shortcut'     => $this->__('i', 'test'),
            ),
            'bold'          => array(
                'icon'         => 'bold.png',
                'title'        => $this->__('bolded text'),
                'inner'        => $this->__('bolded text'),
                'shortcut'     => $this->__('b'),
            ),
            'underline'     => array(
                'icon'         => 'underline.png',
                'title'        => $this->__('underlined text'),
                'inner'        => $this->__('underlined text'),
                'shortcut'     => $this->__('u', 'test'),
            ),
            'strikethrough' => array(
                'icon'         => 'strikethrough.png',
                'title'        => $this->__('strikethrough text'),
                'inner'        => $this->__('strikethrough text'),
            ),
            'mark'          => array(
                'icon'         => 'mark.png',
                'title'        => $this->__('marked text'),
                'inner'        => $this->__('marked text'),
            ),
            'monospace'     => array(
                'icon'         => 'monospace.png',
                'title'        => $this->__('Monospace'),
                'inner'        => $this->__('Monospace'),
            ),
            'center'           => array(
                'icon'         => 'center.png',
                'title'        => $this->__('centered text'),
                'inner'        => $this->__('centered text'),
            ),
            'size'          => array(
                'icon'         => 'size.png',
                'title'        => $this->__('Font size'),
                'inner'        => $this->__('Large text'),
                'values'       => array(
                    0 => '12pt'
                )
            ),
            'color'         => array(
                'icon'         => 'color.png',
                'title'        => $this->__('Font color'),
                'inner'        => $this->__('colored text'),
                'values'       => array(
                    0 => 'red'
                )
            ),
            'key'           => array(
                'icon'         => 'key.png',
                'title'        => $this->__('Key'),
                'inner'        => $this->__('CTR-C'),
            ),
           'subscript'   => array(
                'icon'         => 'subscript.png',
                'title'        => $this->__('subscripted text'),
                'inner'        => $this->__('subscripted text'),
            ),
            'superscript'   => array(
                'icon'         => 'superscript.png',
                'title'        => $this->__('superscripted text'),
                'inner'        => $this->__('superscripted text'),
            ),
            'headings'      => array(
                'icon'         => 'headings.png',
                'title'        => $this->__('Headings'),
                'subitems'     => array(
                    'h1' => array(
                        'title' => $this->__('Heading').': '.$this->__('Level').' 1',
                        'inner' => $this->__('Heading')
                    ),
                    'h2' => array(
                        'title' => $this->__('Heading').': '.$this->__('Level').' 2',
                        'inner' => $this->__('Heading')
                    ),
                    'h3' => array(
                        'title' => $this->__('Heading').': '.$this->__('Level').' 3',
                        'inner' => $this->__('Heading')
                    ),
                    'h4' => array(
                        'title' => $this->__('Heading').': '.$this->__('Level').' 4',
                        'inner' => $this->__('Heading')
                    ),
                    'h5' => array(
                        'title'  => $this->__('Heading').': '.$this->__('Level').' 5',
                        'inner' => $this->__('Heading')
                    )
                )
            )
        );
                
        if(count($editor_settings) > 0) {
            
            $language = $editor_settings['language'];
            $lmlElements = $this->getElements($language);
            

            $elements0 = $editor_settings['elements'];
            foreach($elements0 as $element => $value) {
                if($value and array_key_exists($element, $elements) and array_key_exists($element, $lmlElements)) {
                    $elements0[$element] = array_merge_recursive(
                        $elements[$element],
                        $lmlElements[$element]
                    );
                } else {
                    unset($elements0[$element]);
                }
            }
            $elements = $elements0;
            
        }
        
        return $elements;
    }
    
    
   public function supportedTags()
   {    
        $replaces = $this->getReplaces();
        $tags     = $this->elements();
                
        foreach($tags as $key => $value) {

           $lmls = array('BBCode', 'Creole', 'Wikka', 'Wikimedia', 'Markdown');
            
           foreach($lmls as $lml) {
               $lmlElements = $this->getElements($lml); 
               if(array_key_exists($key, $lmlElements)) {
                   if(array_key_exists('subitems', $lmlElements[$key])) {
                      $tags[$key]['lmls'][$lml] = '<em>'.$this->__('Super tag').'</em>';
                   } else {
                       $inner = '';
                       if(array_key_exists('inner', $lmlElements[$key])) {
                           $inner = $lmlElements[$key]['inner'];
                       } else if (array_key_exists('inner', $value)) {
                           $inner = $value['inner'];
                       }
                       $lmltag = '';
                       if(array_key_exists('begin', $lmlElements[$key])) {
                           $lmltag = htmlentities($lmlElements[$key]['begin'].$inner.$lmlElements[$key]['end']);
                           $lmltag = str_replace('&quot;', '"', $lmltag);
                       }
                       $tags[$key]['lmls'][$lml] = $lmltag;
                   }
               } else {
                   $tags[$key]['lmls'][$lml] = '<em style="background-color:red">'.$this->__('Not available').'</em>';
               }
           }
           
            $html = '';
            if(array_key_exists($key, $replaces) && array_key_exists('begin', $replaces[$key]) ) {
                if (array_key_exists('inner', $value)) {
                   $inner = $value['inner'];
                } else {
                   $inner = '';
                }
                $html = $replaces[$key]['begin'].$inner.$replaces[$key]['end'];
            }
            $tags[$key]['html'] = htmlentities($html);
            if(array_key_exists('preview', $value) and !$value['preview']) {
                $tags[$key]['preview'] = '';
            } else {
                $tags[$key]['preview'] = $html;
            }

        }
        return $tags;
    }
    
    
    public function getElements($language) {
        $className = 'LuMicuLa_Language_'.$language;
        $languageClass = new $className;
        return $languageClass->elements();
    }
    
    
    public function getReplaces(){
        return array(
            'code' => array(
                'begin'     => '<code>',
                'end'       => '</code>',
            ),
            'nomarkup' => array(
                'begin'     => '<tt>',
                'end'       => '</tt>',
            ),
            'list' => array(
                'begin'     => '<li>',
                'end'       => '</li>',
            ),
            'link' => array(
                'begin'     => '<a href="',
                'end'       => '">VALUE</a>',
            ),
            'page' => null,
            'hr' => array(
                'begin'     => '<hr />',
                'end'       => '',
            ),
            'img' => array(
                'begin'     => '<img src="',
                'end'       => '">',
            ),
            'bold' => array(
                'begin' => '<strong>',
                'end'   => '</strong>',
            ),
            'italic' => array(
                'begin' => '<em>',
                'end'   => '</em>',
            ),
            'underline' => array(
                'begin' => '<u>',
                'end'   => '</u>',
            ),
            'strikethrough' => array(
                'begin' => '<del>',
                'end'   => '</del>',
            ),
            'mark' => array(
                'begin' => '<strong style="background-color:#ffee33;">',
                'end'   => '</strong>',
            ),
            'table' => array(
                'begin' => '<table border=1>',
                'end'   => '</table>',
            ),
            'tr' => array(
                'begin' => '<tr>',
                'end'   => '</tr>',
            ),
            'td' => array(
                'begin' => '<td>',
                'end'   => '</td>',
            ),
            'monospace' => array(
                'begin' => '<tt>',
                'end'   => '</tt>',
            ),
           'center' => array(
                'begin' => '<center>',
                'end'   => '</center>',
            ),
            'size'  => array(
                'begin' => '<span style="font-size:VALUE">',
                'end'   => '</span>',
             ),
            'color'  => array(
                'begin' => '<span style="color:VALUE">',
                'end'   => '</span>',
             ),            
            'key' => array(
                'begin' => '<kbd class="keys">',
                'end'   => '</kbd>',
            ),
            'box' => array(
                'begin' => '<div class="floatl">',
                'end'   => '</div>',
            ),
            'clear' => array(
                'begin' => '<div class="clear">&nbsp;</div>',
                'end'   => '',
            ),
            'indent' => array(
                'begin' => '<div class="indent">',
                'end'   => '</div>',
            ),
           'subscript'   => array(
                'begin'      => '<sub>',
                'end'        =>  '</sub>',
            ),
            'superscript'=> array(
                'begin'      => '<sup>',
                'end'        =>  '</sup>',
            ),
            'headings' => null,
            'h5' => array(
                'begin' => '<h6>',
                'end'   => '</h6>',
            ),
            'h4' => array(
                'begin' => '<h5>',
                'end'   => '</h5>',
            ),
            'h3' => array(
                'begin' => '<h4>',
                'end'   => '</h4>',
            ),
            'h2' => array(
                'begin' => '<h3>',
                'end'   => '</h3>',
            ),
            'h1' => array(
                'begin' => '<h2>',
                'end'   => '</h2>',
            ),
            'youtube' => array(
                'begin' => '<object width="640" height="390"><param name="allowScriptAccess" value="always"></param><embed src="https://www.youtube.com/v/',
                'end'   => '?version=3&autoplay=1" type="application/x-shockwave-flash" allowscriptaccess="always" width="640" height="390"></embed></object>'
            )
            // the iframe solution makes problem with the Dizkus edit/quote buttons
            /*'youtube' => array(
                'begin' => '<iframe class="youtube-player" type="text/html" width="640" height="385" src="http://www.youtube.com/embed/',
                'end'   => '" frameborder="0">'
            )*/
        );
        
    }
    
    
    
}