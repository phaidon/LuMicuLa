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
    public function elements($language = '')
    {
        
        switch ($language) {
            case 'Wakka':
                return ModUtil::apiFunc($this->name, 'Wakka',  'elements'); 
            case 'BBCode':
                return ModUtil::apiFunc($this->name, 'BBCode', 'elements'); 
            default:
                return ModUtil::apiFunc($this->name, 'Creole', 'elements'); 
        }
            
    }
    
    
    public function replaces()
    {
        return array(
            'hr' => array(
                'begin' => '<hr>',
                'end'   => '',
            ),
            'img' => array(
                'begin' => '<img src="',
                'end'   => '">',
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
                'begin' => '<u> ',
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
                'begin' => '<iframe class="youtube-player" type="text/html" width="640" height="385" src="http://www.youtube.com/embed/',
                'end'   => '" frameborder="0">'
            )
        );
    }

    
    
    /**
    * Wiki formater wrapper
    *
    * @param string $args['text'] text to wiki-format
    * @param string $args['method'] (optional) legacy Wakka state
    * @return wiki-formatted text
    */

    private $_lml;

    
    public function transform($args)
    {
        extract($args);
        if(empty($modname)) {
            return $text;
        }
        PageUtil::addVar('stylesheet', "modules/LuMicuLa/style/transform.css");

        
        // Wakka workaround
        if( $this->getVar('lml') == 'Wakka') {
            return ModUtil::apiFunc($this->name, 'wakka', 'wakka', $args); 
        }
        
        $message = $text;
        unset($text);
        
        
        // get the light markup language of the current module
        if(!is_array($this->_lml) or !array_key_exists($modname, $this->_lml)) {
            $editor_settings = Doctrine_Core::getTable('LuMicuLa_Model_LuMicuLa')->find($modname);
            if(!$editor_settings) {
                return $message;
            }
            $editor_settings = $editor_settings->toArray();
            $this->_lml[$modname] = $editor_settings['language'];
        }
        $lml = $this->_lml[$modname];
        
        
        $message = ' ' . $message; // pad it with a space so we can distinguish 
        // between FALSE and matching the 1st char (index 0).
        // This is important; bbencode_quote(), bbencode_list(), and 
        // bbencode_code() all depend on it.
        
        
        $message                   = $this->transform_quotes($message);
        list($message,$codeblocks) = $this->transform_codeblocks_pre($message);
        $message                   = ModUtil::apiFunc($this->name, 'creole', 'preTransform', $message); 
        $message                   = $this->transform_smilies($message);
             
        // transform other elements (bold, italic, , ...)
        $elements = $this->elements($lml);
        $replaces = $this->replaces();
        foreach($elements as $key => $e) {
            if(array_key_exists($key, $replaces) ) {          
                if(array_key_exists('func', $e) and $e['func']) {
                    $message = ModUtil::apiFunc($this->name, $lml, $key, $message); 
                } else {
                    $r = $replaces[$key];
                    $e['begin'] = preg_quote($e['begin'],'/');
                    $e[$key]['begin'] = str_replace("BOL", "^", $e['begin']);
                    $e['end']   = preg_quote($e['end'],  '/');
                    $message = preg_replace(
                        "/".$e['begin']."(.*?)".$e['end']."/si",
                        $r['begin']."\\1".$r['end'],
                        $message
                    );
                }
            }
        }
                

        $message = ModUtil::apiFunc($this->name, 'Creole', 'postTransform', $message); 
      
        // Remove our padding from the string..
        $message = substr($message, 1);        
        $message = str_replace("<br />\n", "\n", $message);
        $message = str_replace("\n", "<br />\n", $message);
        
        
        $message = $this->transform_codeblocks_post(array($message, $codeblocks));
       
        $message = $this->imageViewer($message);      
        
        return $message;

    }
    
    public function transform_codeblocks_pre($message)
    {
        $match = ModUtil::apiFunc($this->name, 'Creole',  'code');
        $count = preg_match_all("#".$match."#si", $message, $codeblocks);
        for($i = 0; $i < $count; $i++) {
            $str_to_match = "/" . preg_quote($codeblocks[0][$i], "/") . "/";
            $message = preg_replace($str_to_match, 'CODEBLOCK'.$i, $message, 1);
        }
        return array($message, $codeblocks);
    }
    
    
        
    public function transform_codeblocks_post($args)
    {
        $message    = $args[0];
        $codeblocks = $args[1];
        $count = count($codeblocks[0]);
        for($i = 0; $i < $count; $i++) {
            $code = $codeblocks[1][$i];
            $args = array(
                'text' => $code
            );
            $code = $this->highlight($args);
            $message = str_replace('CODEBLOCK'.$i, $code, $message);
        }
        return $message;
    }
    
    public function transform_smilies($message)
    {   
        $alternative_smilies = ModUtil::apiFunc($this->name, 'Smilies', 'alternative_smilies');
        foreach($alternative_smilies as $tag1 => $tag2) {
            $message = str_replace($tag1, $tag2, $message);
        }
        $smilies = ModUtil::apiFunc($this->name, 'Smilies', 'smilies');
        foreach($smilies as $tag => $icon) {
            $message = str_replace($tag, '<img src="modules/LuMicuLa/images/smilies/'.$icon.'" title="'.$tag.'" alt="'.$tag.'">', $message);
        }
        return $message;
    }
    
    public function transform_quotes($message)
    {    
        return preg_replace_callback(
            "#\[quote(.*?)\[\/quote\]#si",
            Array($this, 'quote_callback'),
            $message
        );
    }
    
    
    public function transform_image($image)
    {    
        extract($image);        
        $src = str_replace("//", "CREOLELINKREPLACEMENT", $src);
        
        if(is_null($title)) {
            return '<a href="'.$src.'" rel="imageviewer">'.
                   '<img src="'.$src.'" width="250">'.
                   '</a>';
        }        
        return '<a href="'.$src.'" rel="imageviewer>'.
               '<img src="'.$src.'" title="'.$title.'" alt="'.$title.'" width="250">'.
               '</a>';
    }

    
    
    

    
    
    
    protected function quote_callback($matches)
    {      
       if(substr($matches[1], 0, 1) == "]") {
           $quote = substr($matches[1], 1);
           $header = '';
       } else {
           $tmpArray = explode(']', $matches[1]);
           $quote = $tmpArray[1];
           $tmpArray = explode('=', $tmpArray[0]);
           $user = $tmpArray[1];
           $header = '<div class="lmlquoteheader">'.$user.' '.$this->__('wrote').':</div>';
       }
       
       return $header.'<blockquote class="lmlquotetext">'.$quote.'</blockquote>';
    }
    
    /** minimize_displayurl
    *  helper function to cut down the displayed url to a maximum length
    *
    *
    */
    public function minimize_displayurl($displayurl)
    {
        // get the maximum size of the urls to show
        $maxsize = $this->getVar('link_shrinksize', 50);
        if($maxsize<>0 && strlen($displayurl) > $maxsize) {
            $before = round($maxsize / 2);
            $after  = $maxsize - 1 - $before;
            $displayurl = substr($displayurl, 0, $before) . "&hellip;" . substr($displayurl, strlen($displayurl) - $after, $after);
        }
        return $displayurl;
    }


    public function highlight($args)
    {
        extract($args);
        if(empty($language)) {
            $language = 'php';
        }
        if(empty($text)) {
            return '';
        }
        $highlighter = $this->getVar('syntaxHighlighter');
           
        switch ($highlighter) {
            case 'geshi':
                include_once('modules/LuMicuLa/lib/vendor/geshi/geshi.php');                        
                $geshi = new GeSHi($text, $language);
                $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
                $geshi->set_header_type(GESHI_HEADER_PRE);
                $text = $geshi->parse_code(); 
                break;
            case 'prettify':
                $path = 'modules/LuMicuLa/lib/vendor/prettify/';
                PageUtil::addVar('javascript', $path.'prettify.js');
                PageUtil::addVar('stylesheet', $path.'prettify.css');
                PageUtil::addVar('header', '<script type="text/javascript">Event.observe(window, \'load\', prettyPrint);</script>');
                $text = str_replace("\n", '<br />', $text);
                $text = '<code class="prettyprint linenums:1">'.$text.'</code>';
                break;
            case 'syntaxhighlighter':
                $path = 'modules/LuMicuLa/lib/vendor/syntaxhighlighter/';
                PageUtil::addVar('javascript', $path.'scripts/shCore.js');
                PageUtil::addVar('javascript', $path.'scripts/shBrushJScript.js');
                PageUtil::addVar('stylesheet', $path.'styles/shCoreDefault.css');
                PageUtil::addVar('header', '<script type="text/javascript">SyntaxHighlighter.all()</script>');                
                $text = '<pre class="brush: js;">'.$text.'</pre>';
                break;
        }
        
        return $text;
    } 
    
    public function getAllModuleSettings()
    {    
    
        $all_module_settings = Doctrine_Core::getTable('LuMicuLa_Model_LuMicuLa')->findAll();
        $all_module_settings = $all_module_settings->toArray();
                
        foreach($all_module_settings as $key => $value) {
            $elements = $value['elements'];
            if(is_array($elements)) {
                $all_module_settings[$key] = array_merge($value, $elements);                
            }
            unset($all_module_settings[$key]['elements']);
        }
        
        return $all_module_settings;
    }
    
    public function getModuleSettings($modname)
    {    
        $module_settings = Doctrine_Core::getTable('LuMicuLa_Model_LuMicuLa')->findOneBy('modname', $modname);
        if($module_settings) {
            $module_settings = $module_settings->toArray();
            $elements = $module_settings['elements'];
            unset($module_settings['elements']);
            if(is_array($elements)) {
                $module_settings = array_merge($module_settings, $elements);
            }
            return $module_settings;
        }
            
        return false;
                
    }
    
    
    public function imageViewer($message) {
        if( $this->getVar('imageViewer') ) {
            PageUtil::addVar('javascript', "javascript/ajax/prototype.js");
            PageUtil::addVar('javascript', "javascript/helpers/Zikula.ImageViewer.js");
            $message .= '<script type="text/javascript">'.
                        'Zikula.ImageViewer.setup({'. 
                        '     modal: true,'.
                        '     langLabels: {'.
                        "         close: 'Close this box',". 
                        '     }'. 
                        '});'.
                        '</script>';
        }
        return $message;
    }
    
    
    
    
    
    
}