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

class LuMicuLa_Api_Transform extends Zikula_AbstractApi 
{

    public function replaces()
    {
        return array(
            'code' => array(
                'begin'     => '<code>',
                'end'       => '</code>',
            ),
            'link' => array(
                'begin'     => '<a>',
                'end'       => '</a>',
            ),
            'hr' => array(
                'begin'     => '<hr>',
                'end'       => '',
            ),
            'img' => array(
                'begin'     => '<img src="..." title="..." alt="...">',
                'end'       => '',
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
            'monospace' => array(
                'begin' => '<tt>',
                'end'   => '</tt>',
            ),
            'key' => array(
                'begin' => '<kbd class="keys">',
                'end'   => '</kbd>',
            ),
            'headings' => array(
                'subitems' => array(
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
                )
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
    * @return wiki-formatted text
    */

    private $_lml;
    private $_codeblocks;

    
    public function transform($args)
    {
        
        $this->_codeblocks = array();
        
        extract($args);
        if(empty($modname)) {
            return $text;
        }
        PageUtil::addVar('stylesheet', "modules/LuMicuLa/style/transform.css");

        
        
        $message = $text;
        unset($text);
        
        
        // get the light markup language of the current module
        if(!is_array($this->_lml) or !array_key_exists($modname, $this->_lml)) {
            $editor_settings = Doctrine_Core::getTable('LuMicuLa_Model_LuMicuLa')->find($modname);
            if(!$editor_settings) {
                return $message;
            }
            $editor_settings = $editor_settings->toArray();
            $this->_lml[$modname] = $editor_settings;
        } else {
            $editor_settings = $this->_lml[$modname];
        }
                
        $lml = $editor_settings['language'];
        
        $message = ' ' . $message; // pad it with a space so we can distinguish 
        // between FALSE and matching the 1st char (index 0).
        // This is important; bbencode_quote(), bbencode_list(), and 
        // bbencode_code() all depend on it.
        
        
        $message                   = $this->transform_quotes($message);
             
        // transform other elements (bold, italic, , ...)
        $elements = ModUtil::apiFunc($this->name, 'user', 'elements', $editor_settings);        
                
        
        $replaces = $this->replaces();
        foreach($elements as $key => $e) {
            if(array_key_exists($key, $replaces)) {
                if(array_key_exists('func', $e) and $e['func']) {
                    $message = $this->transform_func($message, $key, $e, $lml);

                } else if (array_key_exists('subitems', $e) ) {
                    foreach($e['subitems'] as $key2 => $f) {
                        $replace = $replaces[$key]['subitems'][$key2];
                        $message = $this->replace($message, $f, $replace);
                    }
                } else {
                    $replace = $replaces[$key];
                    $message = $this->replace($message, $e, $replace);

                }
            }
        }
                
        
        $message = $this->transform_smilies($message);


      
        // Remove our padding from the string..
        $message = substr($message, 1);        
        $message = str_replace("<br />\n", "\n", $message);
        $message = str_replace("\n", "<br />\n", $message);
        
        $message = str_replace("LINKREPLACEMENT", "//", $message);
        
        
        
        
        
        $message = $this->transform_code_post($message);
        $message = $this->imageViewer($message);      
        
       
        
        return $message;

    }
    
    protected function replace($message, $e, $r ) {

        $e['begin'] = preg_quote($e['begin'],'/');
        $e['begin'] = str_replace("BOL", "^", $e['begin']);
        $e['end']   = preg_quote($e['end'],  '/');
        return preg_replace(
            "/".$e['begin']."(.*?)".$e['end']."/si",
            $r['begin']."\\1".$r['end'],
            $message
        );
        
    }
    
    
    
    protected function transform_func($message, $tag, $tagData, $lml)
    {
        extract($tagData);
        $begin = preg_quote($begin,'/');
        $end   = preg_quote($end,'/');
        
        $message = preg_replace_callback(
            "#".$begin."(.*?)".$end."#si",
            Array('LuMicuLa_Api_'.$lml, $tag.'_callback'),
            $message
        );
        return $message;
    }
    
    
    
    public function transform_code($code)
    {
        $c = count($this->_codeblocks);
        $this->_codeblocks[$c] = $code;
        return 'CODEBLOCK'.$c;

    }
    
    
    
    protected function transform_code_post($message)
    {
        for($i = 0; $i < count($this->_codeblocks); $i++) {
            $code = $this->_codeblocks[$i];
            $args = array(
                'text' => $code
            );
            $code = $this->highlight($args);
            $message = str_replace('CODEBLOCK'.$i, $code, $message);
        }
        return $message;
    }
    
    
    protected function transform_smilies($message)
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
        $src = str_replace("//", "LINKREPLACEMENT", $src);

       
        if(empty($title)) {
            return '<a href="'.$src.'" rel="imageviewer">'.
                   '<img src="'.$src.'" width="250">'.
                   '</a>';
        }        
        return '<a href="'.$src.'" rel="imageviewer">'.
               '<img src="'.$src.'" title="'.$title.'" alt="'.$title.'" width="250">'.
               '</a>';
    }

    public function transform_link($link)
    {    
        extract($link);
        
        $pos = strpos($url, '://');
        if( $pos === false) {
            $tag = $url;
            if(empty($title)) {
                $title = $tag;
            }
            $url = ModUtil::url('Wikula', 'user', 'main', array('tag' => $url) );
            if( !ModUtil::apiFunc('Wikula', 'user', 'PageExists', array('tag'=>$tag)) ) {
                $url   = str_replace("//", "LINKREPLACEMENT", $url);
                return "$tag<a href='$url'>?</a>";
            }
            return "<a href='$url'>$title</a>"; 
        }
        
        $url = str_replace("//", "LINKREPLACEMENT", $url);
        if(empty($title) ) {
            $title = $url;
            $title = ModUtil::apiFunc($this->name, 'transform', 'minimize_displayurl', $title);
        }
        
        return "<a href='$url'>$title</a>";      
        
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

    
    public function imageViewer($message) {
        if( $this->getVar('imageViewer') ) {
            PageUtil::addVar('javascript', "javascript/ajax/prototype.js");
            PageUtil::addVar('javascript', "javascript/helpers/Zikula.ImageViewer.js");
            $footer = '<script type="text/javascript">'.
                      'Zikula.ImageViewer.setup({'. 
                      '     caption: true,'.
                      '     modal: true,'.
                      '     langLabels: {'.
                      "         close: 'Close this box',". 
                      '     }'. 
                      '});'.
                      '</script>';
            PageUtil::addVar('footer', $footer);
        }
        return $message;
    }
    
    
}