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
            'nomarkup' => array(
                'begin'     => '<tt>',
                'end'       => '</tt>',
            ),
            'list' => array(
                'begin'     => '<li>',
                'end'       => '</li>',
            ),
            'link' => array(
                'begin'     => '<a>',
                'end'       => '</a>',
            ),
            'hr' => array(
                'begin'     => '<hr />',
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
            'table' => array(
                'begin' => '<table><tr><td>',
                'end'   => '</td></tr></table>',
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
            'headings' => array(
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

    private $_lml        = array();
    private $_codeblocks = array();
    private $_nomarkups  = array();
    private $categories  = array();


    
    public function transform($args)
    { 
        
        extract($args);
        if(empty($modname) or empty($text)) {
            return $text;
        }
        $this->loadEditorSettings($modname);
        
                
        PageUtil::addVar('stylesheet', "modules/LuMicuLa/style/transform.css"); 
        $this->_codeblocks = array();
        $this->_nomarkups  = array();
        $this->categories  = array();
        
        
        
        
        $message = ' ' . $text; // pad it with a space so we can distinguish 
        // between FALSE and matching the 1st char (index 0).
        // This is important; bbencode_quote(), bbencode_list(), and 
        // bbencode_code() all depend on it.
        unset($text);
        
        
        $message = $this->extractCategories($message, $modname);
        
        $message = $this->transform_quotes($message);
        $message = $this->replace($message, $modname);   
        $message = $this->transform_smilies($message);       
        
        
        $message = str_replace('<br>', '<br />', $message);
        $message = str_replace("<br />\n", "\n", $message);
        $message = str_replace("\n", "<br />\n", $message);
        $message = str_replace("LINKREPLACEMENT", "//", $message);
        
        $message = $this->transform_code_post($message);
        $message = $this->transform_nomarkup_post($message);
        $message = $this->imageViewer($message);      
        
 
        $message .= $this->categoriesBox();
        
        
        // Remove our padding from the string..
        $message = substr($message, 1);
        return $message;

    }
    
    
    
    public function extractCategories($message, $modname)
    {
        
        $editor_settings = $this->_lml[$modname];
        $lml = $editor_settings['language'];
        return ModUtil::apiFunc($this->name, $lml, 'extractCategories', $message );
        
    }
    
    
    public function categoryCallback($things)
    {
        $things = explode(' ', $things[1]);
        $category = $things[0];
        $title  = str_replace('_', ' ', $category);
        
        $url = ModUtil::url(
            'Wikula',
            'user',
            'category',
            array('category' => $category)
        );
        ModUtil::apiFunc('LumiCuLa', 'Transform', 'addCategory', '<a href="'.$url.'">'.$title.'</a>');
        
    }

    
    public function addCategory($category)
    {
        $this->categories[] = $category;
    }
    
    
    
    private function categoriesBox()
    {   
        
        if( count($this->categories) > 0) {
            if( count($this->categories) == 1 ) {
                $categories = $this->__('Category');
            } else {
                $categories = $this->__('Categories');
            }
            $categories = '<div class="wikula_categories">'.$categories.': '.implode(', ', $this->categories).'</div>';
        } else {
            $categories = '';
        }
        return $categories;
            
    }
    
    
    public function loadEditorSettings($modname) {
        
        // get the light markup language of the current module
        if(!is_array($this->_lml) or !array_key_exists($modname, $this->_lml)) {
            $editor_settings = Doctrine_Core::getTable('LuMicuLa_Model_LuMicuLa')->find($modname);
            $editor_settings = $editor_settings->toArray();
            $this->_lml[$modname] = $editor_settings;
        }
    }
    
    
    protected function replace($message, $modname)
    {
        

        $editor_settings = $this->_lml[$modname];
        if(count($editor_settings) == 0) {
            return $message;
        }
        $lml = $editor_settings['language'];
        
        
        $replaces = $this->replaces();
        // transform other elements (bold, italic, , ...)
        $elements = ModUtil::apiFunc($this->name, 'user', 'elements', $editor_settings);
        
        
        $message = $this->replace2($message, $elements, $replaces, $lml);
        
        return $message;
        
    }
    
    
    protected function replace2($message, $elements, $replaces, $lml)
    {
        foreach($elements as $tagID => $tagData) {
            if(!array_key_exists($tagID, $replaces)) {
                continue;
            }
            //print_r($tagID);
            
            $replaceData = $replaces[$tagID];

            $func = null; $subitems = null;
            extract($tagData);// begin, end, func

            
            if(!is_null($func) and $func) {
                $message = $this->transform_func($message, $tagID, $tagData, $lml);  
            } else if (!is_null($subitems) ) {
                foreach($subitems as $key => $value) {
                    $message = $this->replace2($message, $subitems, $replaceData, $lml);
                }
            } else {
                if(substr_count($begin, 'VALUE') > 0) {
                    $message = $this->transform_multi($message,$tagID, $tagData, $replaceData);
                }
                
                $begin = preg_quote($begin,'/');
                $begin = str_replace("BOL", "^", $begin);
                $end   = preg_quote($end,  '/');
                $message = preg_replace(
                    "/".$begin."(.*?)".$end."/si",
                    $replaceData['begin']."\\1".$replaceData['end'],
                    $message
                );
            }
        }
        return $message;
    }
    
    
    
    
    protected function transform_func($message, $tag, $tagData, $lml)
    {     
        $func = '';
        extract($tagData);
        $func    = $tagData['func'];
        $pattern = 'si';
        if(!empty($regexp) and $regexp) {
            $pattern .= 'm';
        } else {
            $begin = preg_quote($begin,'/');
            $end   = preg_quote($end,'/');
        }
        
        if($func == 1) {
            $expression = "#".$begin."(.*?)".$end."#".$pattern;
        } else {
            $func = preg_quote($func,'/');
            $func = str_replace('VALUE', "(.*?)", $func);
            $func = str_replace('\=', "\=?", $func);
            $expression = "#".$func."#".$pattern;
        }
        
        if (isset($gfunc) and $gfunc) {
            $message = preg_replace_callback(
                $expression,
                array($this, $tag.'_callback'),
                $message
            );
        } else {

            $message = preg_replace_callback(
                $expression,
                array('LuMicuLa_Api_'.$lml, $tag.'_callback'),
                $message
            );
        }
        
        return $message;
    }
    
    private $_current_replace;

    protected function transform_multi($message, $tag, $tagData, $replace)
    {    
        
        $replace = $replace['begin'].'VALUE'.$replace['end'];
        
        extract($tagData);
        $pattern = 'si';
        if(!empty($regexp) and $regexp) {
            $pattern .= 'm';
        } else {
            $begin = preg_quote($begin,'/');
            $end   = preg_quote($end,'/');
        }  
        
        $expression = "#".$begin."(.*?)".$end."#".$pattern;
        $expression = str_replace('VALUE', '(.*?)', $expression);
        $this->_current_replace = $replace;  
        
        
        $message = preg_replace_callback(
            $expression,
            array($this, 'multiparameter_callback'),
            $message
        );

        return $message;
    }
    
    
    protected function multiparameter_callback($matches)
    {        

        unset($matches[0]);
        $replace = $this->_current_replace;

        foreach($matches as $match) {
            $replace = preg_replace('/VALUE/', $match, $replace, 1);
        }
        
   
        return $replace;
    } 
    
    public function transform_code($code)
    {
        $c = count($this->_codeblocks);
        $this->_codeblocks[$c] = $code;
        return 'CODEBLOCK'.$c;
    }
    
    public static function code_callback($matches)
    {
        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_code', $matches[1]);
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
    
        
    public function transform_nomarkup($nomarkup)
    {
        $c = count($this->_nomarkups);
        $this->_nomarkups[$c] = $nomarkup;
        return 'NOMARKUP'.$c;

    }
    
        
    
    protected function transform_nomarkup_post($message)
    {
        for($i = count($this->_nomarkups)-1; $i > -1 ; $i--) {
            $nomarkup = $this->_nomarkups[$i];
            $args = array(
                'text' => $nomarkup
            );
            $message = str_replace('NOMARKUP'.$i, $nomarkup, $message);
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
            $message = str_replace($tag, '<img src="modules/LuMicuLa/images/smilies/'.$icon.'" title="'.$tag.'" alt="'.$tag.'" />', $message);
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
                   '<img src="'.$src.'" width="250" />'.
                   '</a>';
        }        
        return '<a href="'.$src.'" rel="imageviewer">'.
               '<img src="'.$src.'" title="'.$title.'" alt="'.$title.'" width="250" />'.
               '</a>';
    }

    public function transform_link($link)
    {
        
        if(empty($link['title'])) {
            $link['title'] = null;
        }
        
        $url = $link['url'];
        $title = $link['title'];
        $mailto = substr($url, 0, 6) == 'mailto';
        
        
        $pos = strpos($url, '://');
        if( $pos === false and !$mailto) {
            return $this->transform_page($url, $title);
        }
        
                
        $url = str_replace("//", "LINKREPLACEMENT", $url);
        if(is_null($title) ) {
            if($mailto) {
                $title = substr($url, 7);
            } else {
                $title = $url;
                $title = ModUtil::apiFunc($this->name, 'transform', 'minimize_displayurl', $title);
            }
        }
        return '<a href="'.DataUtil::formatForDisplay($url).'">'.DataUtil::formatForDisplay($title).'</a>';
        
    }
    
     public static function link_callback($matches)
    {        
        $link = array();
        
        if (empty($matches[1])) {
            $link['url']   = $matches[2];
            $link['title'] = $matches[2];
        } else {
            $link['url']   = $matches[1];
            $link['title'] = $matches[2];
        }

        return ModUtil::apiFunc('LuMicuLa', 'transform', 'transform_link', $link);
    }

    protected function transform_page($tag, $title)
    {
        
        $pos = strpos($tag, ':');
        if($pos === false) {
            $tag = 'wiki:'.$tag;
        }

        list($module, $id) = explode(':', $tag);
        
        if(is_null($title)) {
            $title = $id;
        }
        
        switch ($module) {
        case 'post':
            $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array(
                'topic' => $id
            ));
            break;
        case 'topic':
            $url = ModUtil::url('Dizkus', 'user', 'viewforum', array(
                'forum' => $id
            ));
            break;
        case 'task':
            $url = ModUtil::url('Tasks', 'user', 'view', array(
                'id' => $id
            ));
            break;
         case 'wiki':
            if( !ModUtil::apiFunc('Wikula', 'user', 'PageExists', array('tag' => $id)) )
            {
                $url = ModUtil::url('Wikula', 'user', 'edit', array('tag' => $id) );
                $url   = str_replace("//", "LINKREPLACEMENT", $url);
                return DataUtil::formatForDisplay($title).'<a href="'.DataUtil::formatForDisplay($url).'">?</a>';
            }
            $url = ModUtil::url('Wikula', 'user', 'show', array('tag' => $id) );
            break;
        }
        $url   = str_replace("//", "LINKREPLACEMENT", $url);
        return '<a href="'.DataUtil::formatForDisplay($url).'">'.DataUtil::formatForDisplay($title).'</a>';

    }
    //DataUtil::formatForDisplay
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
                $text = '<pre class="brush: js">'.$text.'</pre>';
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
    
    public function getLanguage($modname)
    {
        $this->loadEditorSettings($modname);
        $editor_settings = $this->_lml[$modname];
        if(count($editor_settings) == 0) {
            return false;
        }
        return $editor_settings['language'];
    
    }
    
    
}