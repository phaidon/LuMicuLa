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

class LuMicuLa_Language_Parser
{
    
    private $replaces = array(
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
            ),
            'monospace' => array(
                'begin' => '<tt>',
                'end'   => '</tt>',
            ),
            'blockquote' => array(
                'begin' => '<blockquote>',
                'end'   => '</blockquote>',
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
    
    
    
    // ---------------------------------------------------------------------- \\
    // --- init parse ------------------------------------------------------- \\
    // ---------------------------------------------------------------------- \\
    
    
    private $text = '';
    private $categories = array();
    private $nomarkups = array();
    private $languages;
    private $codeblocks = array();

    
    
    public function setText($text) {
        
        $this->text = $text;
        
    }
    
    
        
    private $elements;
    private $l;

    
    
    public function setModname($modname) {        
        
        // get the light markup language of the current module
        if(!is_array($this->languages) or !array_key_exists($modname, $this->languages)) {
            $em = ServiceUtil::getService('doctrine.entitymanager');
            $editor_settings = $em->find('LuMicuLa_Entity_LuMicuLa', $modname);
            $editor_settings = $editor_settings->toArray();
            $this->languages[$modname] = $editor_settings;
        }

        $editor_settings = $this->languages[$modname];                
        $language = $editor_settings['language'];   
        $className = "LuMicuLa_Language_".$language;    
        $this->l = new $className;
        $this->elements = $this->l->elements();
        
    }
    
    
    public function parse() {
                
        
        
        $this->codeblocks = array();
        $this->categories = array();
        $this->nomarkups  = array();
        $this->urls       = array();
        
        
        PageUtil::addVar('stylesheet', "modules/LuMicuLa/style/transform.css");
        
        $this->text = ' '.$this->text;
        // pad it with a space so we can distinguish 
        // between FALSE and matching the 1st char (index 0).
        // This is important; bbencode_quote(), bbencode_list(), and 
        // bbencode_code() all depend on it.
        
        $this->extractCategories();
        $this->transformAbandonedLinks();
        //$this->extractUrls();
        $this->replace($this->elements);
        $this->transformSmilies();
        
        
        
        $this->text = str_replace("</blockquote><blockquote>", "\n", $this->text);
        $this->transform_list_post();
        $this->text = str_replace('<br>', '<br />', $this->text);
        $this->text = str_replace("<br />\n", "\n", $this->text);
        $this->text = str_replace("\n", "<br />\n", $this->text);
        
        
        // post transform
        $this->categoriesBox();
        $this->imageViewer(); 
        $this->transform_code_post();
        $this->transform_nomarkup_post();
        $this->transform_urls_post();
        
        // Remove our padding from the string..
        $this->text = substr($this->text, 1);
        return '<div class="lumiculaParser">'.$this->text.'</div>';
    }
    
    

    
    
    // ---------------------------------------------------------------------- \\
    // --- replace ---------------------------------------------------------- \\
    // ---------------------------------------------------------------------- \\
    
    
    
    private function replace($elements) {
        
        
        foreach($elements as $tagID => $tagData) {
            
            
            if(!array_key_exists($tagID, $this->replaces) || $tagID == 'categoty' || isset($tagData['noreplace'])) {
                continue;
            }
            $replaceData = $this->replaces[$tagID];
            
            
            
            if($tagID == 'code' || $tagID == 'nomarkup') {
                $tagData['begin']   = preg_quote($tagData['begin'],'/');
                $tagData['begin']   = str_replace("BOL", "^", $tagData['begin']);
                $tagData['end']     = preg_quote($tagData['end'],  '/');
                $pattern = "/".$tagData['begin']."(.*?)".$tagData['end']."/si";

                
                $this->text = preg_replace_callback(
                    $pattern,
                    array($this, $tagID.'_callback'),
                    $this->text
                );
                continue;
            }
            
            
            if(isset($tagData['func']) and $tagData['func']) {
                $this->replaceByFunc($tagID, $tagData);  
            } else if (isset($tagData['subitems']) ) {
                $this->replace($tagData['subitems']);
            } else {
                
                if(substr_count($tagData['begin'], 'VALUE') > 0) {
                    $this->transform_multi($this->text,$tagID, $tagData, $replaceData);
                }
                
                if (!isset($tagData['pattern'])) {
                    $tagData['begin']   = preg_quote($tagData['begin'],'/');
                    $tagData['begin']   = str_replace("BOL", "^", $tagData['begin']);
                    $tagData['end']     = preg_quote($tagData['end'],  '/');
                    $tagData['pattern'] = "/".$tagData['begin']."(.*?)".$tagData['end']."/si";
                }
                            
                $this->text = preg_replace(
                    $tagData['pattern'],
                    $replaceData['begin']."\\1".$replaceData['end'],
                    $this->text
                );
                
            }
            
            
            if (isset($tagData['alternatives']) ) {
                foreach($tagData['alternatives'] as $value) {
                    $this->replace(array($tagID => $value));
                }
            }
            
        }
    }
    
    
    private $currentTag;
    
    private function replaceByFunc($tag, $tagData)
    {        
        
        if(!isset($tagData['pattern'])) {
            $tagData['begin']   = preg_quote($tagData['begin'],'/');
            $tagData['begin']   = str_replace("BOL", "^", $tagData['begin']);
            $tagData['end']     = preg_quote($tagData['end'],  '/');
            $tagData['pattern'] = "/".$tagData['begin']."(.*?)".$tagData['end']."/si";
        }


        $this->currentTag = $tag;
        
        
        $this->text = preg_replace_callback(
            $tagData['pattern'],
            array($this, 'func_callback'),
            $this->text
        );
        
    }
    
    
    public function func_callback($matches)
    {  
         $functionName = $this->currentTag.'_callback';
         return $this->l->$functionName($matches);
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
        $expression = str_replace('VALUE', '([a-zA-Z0-9|\-]*?)', $expression);
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
    
    
    
    
    // ---------------------------------------------------------------------- \\
    // --- links ------------------------------------------------------------ \\
    // ---------------------------------------------------------------------- \\
    
    
     public function transformAbandonedLinks() {
        
        $this->text = preg_replace(
            "#([[:space:]](http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s[[:space:]])#ie",
            "' <a href=\"$1\" class=\"externalLink\">$3</a>$4 '",
            $this->text
        );
    }
    
    
    public function url_callback($matches)
    {
        $c = count($this->urls);
        $this->urls[$c] = $matches[1];
        return 'EXTRACTEDURL'.$c;
    }
    
    
    public function extractUrls() {
    
        
        $this->text = preg_replace_callback(
            "#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#si",
            array($this, 'url_callback'),
            $this->text
        );
    }
    
    
    public function transform_urls_post()
    {
        $protected = $this->l->protected;
        
        
        for($i = count($protected)-1; $i > -1 ; $i--) {
            $p = $protected[$i];
            $this->text = str_replace('PROTECTED'.$i, $p, $this->text);
        }
    }    
    
    
    
    // ---------------------------------------------------------------------- \\
    // --- images ----------------------------------------------------------- \\
    // ---------------------------------------------------------------------- \\
    
        
    public function imageViewer() {
        if( ModUtil::getVar('LuMicuLa', 'imageViewer') ) {
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
    }

    // ---------------------------------------------------------------------- \\
    // --- list & blockquote ------------------------------------------------ \\
    // ---------------------------------------------------------------------- \\
    
    public function transform_list_post()
    {
        $this->text = str_replace("\n<li>", "\n<ul><li>", $this->text);
        $this->text = str_replace("</li>\n", "</li></ul>\n", $this->text);
    }
    
    
    // ---------------------------------------------------------------------- \\
    // --- code & nomarkup -------------------------------------------------- \\
    // ---------------------------------------------------------------------- \\
    
    
    
    public function code_callback($matches)
    {
        $c = count($this->codeblocks);
        $this->codeblocks[$c] = $matches[1];
        return 'CODEBLOCK'.$c;
    }
    
    
    public function transform_code_post()
    {
        for($i = 0; $i < count($this->codeblocks); $i++) {
            $code = $this->codeblocks[$i];
            $code = $this->highlight($code);
            $this->text = str_replace('CODEBLOCK'.$i, $code, $this->text);
        }
    }
    
    
    public function highlight($text, $language = 'php')
    {
        if(empty($text)) {
            return '';
        }
        $highlighter = ModUtil::getVar('LuMicuLa', 'syntaxHighlighter');
           
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
    

    
        
    public function nomarkup_callback($matches)
    {
        $c = count($this->nomarkups);
        $this->nomarkups[$c] = $matches[1];
        return 'NOMARKUP'.$c;

    }
    
        
    
    public function transform_nomarkup_post()
    {
        for($i = count($this->nomarkups)-1; $i > -1 ; $i--) {
            $nomarkup = $this->nomarkups[$i];
            $this->text = str_replace('NOMARKUP'.$i, $nomarkup, $this->text);
        }
    }    
    
    
    
    // ---------------------------------------------------------------------- \\
    // --- categories ------------------------------------------------------- \\
    // ---------------------------------------------------------------------- \\
    
    public function extractCategories()
    {
        
        if (!array_key_exists('category', $this->elements)) {
            return;
        } 
        
        $begin = preg_quote($this->elements['category']['begin'],'/');
        $begin = str_replace("BOL", "^", $begin);
        $end   = preg_quote($this->elements['category']['end'],  '/');
        
        $this->text = preg_replace_callback(
            "#\n".$begin."([a-zA-Z0-9]*+)".$end."#si",
            array($this, 'categoryCallback'),
            $this->text
        );
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
        $this->categories[] = '<a href="'.$url.'">'.$title.'</a>';
        
    }
    
    
    private function categoriesBox()
    {   
        $this->categories = array_unique($this->categories);
        asort($this->categories);
        if( count($this->categories) > 0) {
            if( count($this->categories) == 1 ) {
                $categories = __('Category');
            } else {
                $categories = __('Categories');
            }
            $categories = '<div class="wikula_categories">'.$categories.': '.implode(', ', $this->categories).'</div>';
        } else {
            $categories = '';
        }
        $this->text .= $categories;
    }
    
    
    
    // ---------------------------------------------------------------------- \\
    // --- smilies ---------------------------------------------------------- \\
    // ---------------------------------------------------------------------- \\
    
    
    protected function transformSmilies()
    {
        $this->text = str_replace('font-size:x', 'font-sizeLMLCOLONx', $this->text);
        $alternative_smilies = ModUtil::apiFunc('LuMicuLa', 'Smilies', 'alternative_smilies');
        $smilies = ModUtil::apiFunc('LuMicuLa', 'Smilies', 'smilies');
        $smilies = array_merge($smilies, $alternative_smilies);
        foreach($smilies as $tag => $icon) {
            $img = '<img src="'.System::getBaseUrl().'/modules/LuMicuLa/images/smilies/'.$icon.'" title="'.$tag.'" alt="'.$tag.'" />';
            $this->text = str_replace($tag, $img, $this->text);
        }
        $this->text = str_replace('LMLCOLON', ':', $this->text);
    }
    
    
    
    
    // ---------------------------------------------------------------------- \\
    // --- getPageLinks & getPageCategories --------------------------------- \\
    // ---------------------------------------------------------------------- \\

    public function getPageLinks() {    
        if (!array_key_exists('page', $this->elements)) {
            return array();
        } 
        
        $begin = preg_quote($this->elements['page']['begin'],'/');
        $begin = str_replace("BOL", "^", $begin);
        $end   = preg_quote($this->elements['page']['end'],  '/');
                        
        
        
        $links = array();
        $pagelinks = array();
        preg_match_all("/".$begin."(.*?)".$end."/", $this->text, $links);
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

    
    
    
    public function getPageCategories() {
        
          
        if (!array_key_exists('category', $this->elements)) {
            return array();
        } 
        
        $begin = preg_quote($this->elements['category']['begin'],'/');
        $begin = str_replace("BOL", "^", $begin);
        $end   = preg_quote($this->elements['category']['end'],  '/');
        
        
        
        $categories = array();
        preg_match_all("/".$begin."([a-zA-Z0-9]*+)".$end."/", $this->text, $categories);
        $categories = $categories[1];
        
        foreach($categories as $key => $value) {
            $value = explode(' ', $value);
            $value = $value[0];
            $categories[$key] = $value;
        }
        return array_unique($categories);
    }
    
    
}