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
            'page'          => array(
                'icon'         => 'page.png',
                'title'        => $this->__('Page link'),
            ),
            'link'          => array(
                'icon'         => 'link.png',
                'title'        => $this->__('Web link'),
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
                'title'        => $this->__('Horrizontal line'),
                'inner'        => ''
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
            'key'           => array(
                'icon'         => 'key.png',
                'title'        => $this->__('Key'),
                'inner'        => $this->__('CTR-C'),
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
            switch ($editor_settings['language']) {
                case 'Wakka':
                    $lmlElements = ModUtil::apiFunc($this->name, 'Wakka',  'elements');
                    break;
                case 'BBCode':
                    $lmlElements = ModUtil::apiFunc($this->name, 'BBCode', 'elements');
                    break;
                case 'BBCode':
                    $lmlElements = ModUtil::apiFunc($this->name, 'BBCode', 'elements');
                    break;
                case 'Creole':
                    $lmlElements = ModUtil::apiFunc($this->name, 'Creole', 'elements');
                    break;
                default:
                    return $elements;
            }
            
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
    
}