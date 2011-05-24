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
    
    /**
    * BBCode elements
    *
    * @see http://www.bbcode.org/reference.php
    * @return list of BBCode elements
    */ 
        
    public function elements()
    {
        return array(
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
            'mark' => array(
                'begin' => '[mark]',
                'end'   => '[/mark]',
            ),
            'youtube' => array(
                'begin' => '[youtube]',
                'inner' => '',
                'end'   => '[/youtube]',
            ),
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
            'url' => array(
                'begin' => '[url]',
                'inner' => $this->__('http://www.example.com'),
                'end'   => '[/url]',
            ),
            'code' => array(
                'begin' => '[code]',
                'inner' => $this->__('Code'),
                'end'   => '[/code]',
            ),
        );
    }
    
    public function code() {
        return "\[code\](.*?)\[\/code\]";
    }
}