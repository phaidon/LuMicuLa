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
             'url' => array(
                'begin' => '[[',
                'inner' => $this->__('http://www.example.com').' '.$this->__('Url Title'),
                'end'   => ']]',
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
            
            
        );
    }
    
    
    /**
    * htmlspecialchars to entities utility function
    *
    * @param string $args['text'] text to process
    * @return formatted text
    */
    public function htmlspecialchars_ent($args)
    {
        
        if (!isset($args['text']) || empty($args['text'])) {
            return '';
        }

        // Fixing for now the other args
        $quote_style = ENT_COMPAT;
        $charset     = 'UTF-8';

        // define patterns
        $alpha      = '[a-z]+';               # character entity reference
        $numdec     = '#[0-9]+';              # numeric character reference (decimal)
        $numhex     = '#x[0-9a-f]+';          # numeric character reference (hexadecimal)
        $terminator = ';|(?=($|[\n<]|&lt;))'; # semicolon; or end-of-string, newline or tag

        $entitystring   = $alpha.'|'.$numdec.'|'.$numhex;
        $escaped_entity = '&amp;('.$entitystring.')('.$terminator.')';

        // execute PHP built-in function, passing on optional parameters
        $output = htmlspecialchars($args['text'], $quote_style, $charset);

        // "repair" escaped entities
        // modifiers: s = across lines, i = case-insensitive
        $output = preg_replace('/'.$escaped_entity.'/si', '&$1;', $output);

        // return output
        return $output;
    }

    /**
    * Wakka formater
    *
    * @param string $args['text'] text to wiki-format
    * @param string $args['method'] (optional) legacy Wakka state
    * @return wiki-formatted text
    */
    public function wakka($args)
    {
        
        global $mapcounter;
        $mapcounter = 1;

        $args['text'] = str_replace("\r\n", "\n", $args['text']);

        // We'll see about that later
        $args['method'] = isset($args['method']) ? $args['method'] : FormUtil::getPassedValue('method');
        if (empty($args['method']) || $args['method'] == 'show') {
            $mindmap_pattern = '<map.*?<\/map>|';
        } else {
            $mindmap_pattern = '';
        }

        $linktracking = SessionUtil::getVar('linktracking');

        if ($linktracking) {
        ModUtil::apiFunc('Wikula', 'user', 'ClearLinkTable');
        }

        $args['text'] = preg_replace_callback(
            '/'.
            '%%.*?%%|'.                                                   // code
            "\/\*.*?\*\/[\s]*|".                                          // elided content (eliminates trailing ws)
            "``.*?``|".                                                   // elided content (preserves trailing ws)
            '"".*?""|'.                                                   // literal
            $mindmap_pattern.
            '\[\[[^\[]*?\]\]|\(\([^\(]*?\)\)|'.                           // forced link
            '-{4,}|-{3,}|'.                                               // forced linebreak and separator (hr)
            '\b[a-z]+:\/\/\S+|'.                                          // URL
            "\*\*|\'\'|\#\#|\#\%|@@|::c::|\>\>|\<\<|&pound;&pound;|&yen;&yen;|\+\+|__|<|>|\/\/|". // Wiki markup
            '======|=====|====|===|==|'.                                  // headings
            "(^|\n)([\t~]+|[ ]{2,})+(-(?!-)|&|\*(?!\*)|([0-9]+|[a-zA-Z]+)\))?|". // indents and lists
            "\|(?:[^\|])?\|(?:\(.*?\))?(?:\{[^\{\}]*?\})?(?:\n)?|".       // Simple Tables
            "\{\{.*?\}\}|".                                               // action
            "\b[A-ZÄÖÜ][A-Za-zÄÖÜßäöü]+[:](?![=_])\S*\b|".											# InterWiki link
            "\b([A-ZÄÖÜ]+[a-zßäöü]+[A-Z0-9ÄÖÜ][A-Za-z0-9ÄÖÜßäöü]*)\b|".								# CamelWords
            '\\&([#a-zA-Z0-9]+;)?|'.                                      // ampersands! Track single ampersands or any htmlentity-like (&...;)
            "\n".                                                         // new line
            '/ms',

            'LuMicuLa_Api_Wakka::wakka2callback',
            $args['text']
        );

        // close open tags
        $args['text'] .= $this->wakka2callback('closetags');

        $args['text'] = preg_replace_callback(
            '#('.
            '<h[1-6].*?>.*?</h[1-6]>'.
            // other elements to be treated go here
            ')#ms',

            'LuMicuLa_Api_Wakka::wakka3callback',
            $args['text']
        );

        // we're cutting the lasts <br />
        $args['text'] = preg_replace('/<br \/>$/', '', $args['text']);
    /*
        if ($linktracking && ($previous = SessionUtil::getVar('wikula_previous'))) {
            ModUtil::apiFunc('Wikula', 'user', 'WriteLinkTable',
                        array('tag' => $previous));
        }
    */
        return $args['text'];
    }

    /**
    * Callback transform Wikka function
    *
    * @param string $things match with the patterns defined
    * @see wakka
    * @return HTML transformation
    */
    public function wakka2callback($things)
    {
        
        $cr     = "\n";
        $thing  = $things[0];
        $result = '';
        $valid_filename = '';

        static $oldIndentLevel = 0;
        static $indentClosers = array();
        static $curIndentType;
        static $newIndentSpace= array();
        static $br = true;
        static $trigger_bold = 0;
        static $trigger_center = 0;
        static $trigger_colgroup = 0;
        static $trigger_deleted = 0;
        static $trigger_floatl = 0;
        static $trigger_floatr = 0;
        static $trigger_inserted = 0;
        static $trigger_italic = 0;
        static $trigger_keys = 0;
        static $trigger_l = array(-1, 0, 0, 0, 0, 0);
        static $trigger_monospace = 0;
        static $trigger_notes = 0;
        static $trigger_rowgroup = 0;
        static $trigger_strike = 0;
        static $trigger_table = 0;
        static $trigger_underline = 0;
        static $li = 0;
        static $output = '';
        static $invalid = '';

        if (!is_array($things) && $things == 'closetags') {
            $return = '';
            // close inline elements
            if ($trigger_keys % 2) { $return .= '</kbd>'; }
            if ($trigger_italic % 2) { $return .= '</em>'; }
            if ($trigger_monospace % 2) { $return .= '</tt>'; }
            if ($trigger_bold % 2) { $return .= '</strong>'; }
            if ($trigger_strike % 2) { $return .= '</span>'; }
            if ($trigger_notes % 2) { $return .= '</span>'; }
            if ($trigger_inserted % 2) { $return .= '</span>'; }
            if ($trigger_deleted % 2) { $return .= '</span>'; }
            if ($trigger_underline % 2) { $return .= '</span>'; }

            // close headings
            for ($i = 1; $i<=5; $i ++) {
                if ($trigger_l[$i] % 2) { $return .= "</h$i>"; }
            }

            // close indents
            $c = count($indentClosers);
            for ($i = 0; $i < $c; $i++) {
                $return .= array_pop($indentClosers);
            }

            // close tables
            // TODO check colgroup?
            if (3 < $trigger_table){ $return .=  '</caption>'; }
            elseif (2 < $trigger_table) { $return .=  '</th></tr>'; }
            elseif (1 < $trigger_table) { $return .=  '</td></tr>'; }
            if (2 < $trigger_rowgroup) { $return .=  '</tbody>'; }
            elseif (1 < $trigger_rowgroup) { $return .=  '</tfoot>'; }
            elseif (0 < $trigger_rowgroup) { $return .=  '</thead>'; }
            if (0 < $trigger_table) { $return .=  '</table>'; }

            // close block elements
            if ($trigger_floatl % 2) { $return .= '</div>'; }
            if ($trigger_floatr % 2) { $return .= '</div>'; }
            if ($trigger_center % 2) { $return .= '</div>'; }

            // reset the static vars
            $oldIndentLevel  = 0;
            $oldIndentLength = 0;
            $indentClosers = $newIndentSpace  = array();
            $trigger_bold = $trigger_center = $trigger_floatl = $trigger_floatr = $trigger_inserted = $trigger_deleted = $trigger_italic = $trigger_keys = $trigger_table = $trigger_rowgroup = $trigger_rowgroup = 0;
            $trigger_monospace = $trigger_notes = $trigger_strike = $trigger_underline = 0;
            $trigger_l = array(-1, 0, 0, 0, 0, 0);

            return $return;

        // Ignore the closing delimiter if there is nothing to close.
        } elseif (preg_match("/^\|\|\n$/", $thing, $matches) && $trigger_table == 1) {
            return '';

        // Simple tables
        // $matches[1] is element, $matches[2] is attributes, $matches[3] is styles and $matches[4] is linebreak
        } elseif (preg_match("/^\|([^\|])?\|(\(.*?\))?(\{.*?\})?(\n)?$/", $thing, $matches)) {
            for ($i = 1; $i < 5; $i++) {
                if (!isset($matches[$i])) $matches[$i] = '';
            }
            // Set up the variables that will aggregate the html markup
            $close_part = '';
            $open_part  = '';
            $linebreak_after_open = '';
            $selfclose = '';

            // $trigger_table == 0 means no table, 1 means in table but no cell, 2 is in datacell, 3 is in headercell, 4 is in caption.

            // If we have parsed the caption, close it, set trigger = 1 and return.
            if ($trigger_table == 4) {
                $trigger_table = 1;
                return '</caption>'.$cr;
            }

            // If we have parsed a cell - close it, go on to open new.
            if ($trigger_table == 3) {
                $close_part = '</th>';
            } elseif ($trigger_table == 2) {
                $close_part = '</td>';
            }

            // If no cell, or we want to open a table; then there is nothing to close
            elseif ($trigger_table == 1 || $matches[1] == '!') {
                $close_part = '';
            } else {
                //This is actually opening the table (i.e. nothing at all to close). Go on to open a cell.
                $trigger_table = 1;
                $close_part = '<table class="data">'.$cr;
            }

            // If we are in a cell and there is a linebreak - then it is end of row.
            if ( $trigger_table > 1 && $matches[4] == $cr) {
                $trigger_table = 1;
                return $close_part .= '</tr>'.$cr; //Can return here, it is closed-
            }

            // If we were in a colgroup and there is a linebreak, then it is the end.
            if ($trigger_colgroup == 1 && $matches[4] == $cr) {
                $trigger_colgroup = 0;
                return $close_part .= '</colgroup>'.$cr; //Can return here, it is closed-
            }

            // We want to start a new table, and most likely have attributes to parse.
            // TODO: Need to find out if class="data" should be auto added, and if so - put it in the attribute list to add up.
            if ($matches[1] == '!') {
                $trigger_table = 1;
                $open_part = '<table class="data"';
                $linebreak_after_open = $cr;
            // Open a caption.
            } elseif ($matches[1] == '?') {
                $trigger_table = 4;
                $open_part = '<caption';
            //Start a rowgroup.
            } elseif ($matches[1] == '#' || $matches[1] == '[' || $matches[1] == ']') {
                //If we're here, we want to close any open rowgroup.
                if (2 < $trigger_rowgroup) {
                    $close_part .= '</tbody>'.$cr;
                } elseif (1 < $trigger_rowgroup) {
                    $close_part .= '</tfoot>'.$cr;
                } elseif (0 < $trigger_rowgroup) {
                    $close_part .= '</thead>'.$cr;
                }

                // Then open the appropriate rowgroup.
                if ($matches[1] == '[' ) {
                    $open_part .= '<thead';
                    $trigger_rowgroup = 1;
                } elseif ($matches[1] == ']' ) {
                    $open_part .= '<tfoot';
                    $trigger_rowgroup = 2;
                } else {
                    $open_part .= '<tbody';
                    $trigger_rowgroup = 3;
                }
                $linebreak_after_open = $cr;

            // Here we want to add colgroup.
            } elseif ($matches[1] == '_') {
                // close any open colgroup
                if ($trigger_colgroup == 1) {
                    $close_part .= '</colgroup>'.$cr;
                }

                $trigger_colgroup = 1;
                $open_part .= '<colgroup';

            // And col elements
            } elseif ($matches[1] == '-') {
                $open_part .= '<col';
                $selfclose = ' /';
                if ($matches[4]) {
                    $linebreak_after_open = $cr;
                }

            //Ok, then it is cells.
            } else {
                $open_part = '';
                // Need a tbody if no other rowgroup open.
                if ($trigger_rowgroup == 0) {
                    $open_part .= '<tbody>'.$cr;
                    $trigger_rowgroup = 3;
                }

                // If no row, open a new one.
                if ($trigger_table == 1) {
                    $open_part .= '<tr>';
                }

                // Header cell.
                if ($matches[1] == '=') {
                    $trigger_table = 3;
                    $open_part .= '<th';
                //Datacell
                } else {
                    $trigger_table = 2;
                    $open_part .= '<td';
                }
            }

            // If attributes...
            if (preg_match("/\((.*)\)/", $matches[2], $attribs)) {
                // $hints = array('core' => 'core', 'i18n' => 'i18n');
                $hints = array();
                // allow / disallow different attribute keys. (ie. data/header cell only.
                if ($trigger_table == 2 || $trigger_table == 3) {
                    $hints['cell'] = 'cell';
                } else {
                    $hints['other_table'] = 'other_table';
                }
                $open_part .= parse_attributes($attribs[1], $hints);
            }

            // If styles, just make attribute of it and parse again.
            if (preg_match("/\{(.*)\}/", $matches[3], $attribs)) {
                $attribs = 's:'.$attribs[1];
                $open_part .= parse_attributes($attribs, array());
            }

            // the variable $selfclose is "/" if this is a <col/> element.
            $open_part .= $selfclose.'>';
            return $close_part . $open_part . $linebreak_after_open;

        // are in table, no cell - but not asked to open new: please close and parse again. ;)
        } else if ($trigger_table == 1) {
            $close_part = '';
            if (2 < $trigger_rowgroup) {
                $close_part .= '</tbody>'.$cr;
            } elseif (1 < $trigger_rowgroup) {
                $close_part .= '</tfoot>'.$cr;
            } elseif (0 < $trigger_rowgroup) {
                $close_part .= '</thead>'.$cr;
            }

            $close_part .= '</table>'.$cr;

            $trigger_table = $trigger_rowgroup = 0;

            // and remember to parse what we got.
            return $close_part.$this->wakka2callback($things);
        }

        // convert HTML thingies
        if ($thing == '<') {
            return '&lt;';

        } else if ($thing == '>') {
            return '&gt;';

        // float box left
        } else if ($thing == '<<') {
            return (++$trigger_floatl % 2 ? '<div class="floatl">'.$cr : $cr.'</div>'.$cr);

        // float box right
        } else if ($thing == '>>') {
            return (++$trigger_floatr % 2 ? '<div class="floatr">'.$cr : $cr.'</div>'.$cr);

        // clear floated box
        } else if ($thing == '::c::') {
            return ('<div class="clear">&nbsp;</div>'.$cr);

        // keyboard
        } else if ($thing == '#%') {
            return (++$trigger_keys % 2 ? '<kbd class="keys">' : '</kbd>');

        // bold
        } else if ($thing == '**') {
            return (++$trigger_bold % 2 ? '<strong>' : '</strong>');

        // italic
        } else if ($thing == '//') {
            return (++$trigger_italic % 2 ? '<em>' : '</em>');

        // underline
        } else if ($thing == '__') {
            return (++$trigger_underline % 2 ? '<span class="underline">' : '</span>');

        // monospace
        } else if ($thing == '##') {
            return (++$trigger_monospace % 2 ? '<tt>' : '</tt>');

        // notes
        } else if ($thing == "''") {
            return (++$trigger_notes % 2 ? '<span class="notes">' : '</span>');

        // strikethrough
        } else if ($thing == '++') {
            return (++$trigger_strike % 2 ? '<span class="strikethrough">' : '</span>');

        // additions
        } else if ($thing == '&pound;&pound;') {
            return (++$trigger_inserted % 2 ? '<span class="additions">' : '</span>');

        // deletions
        } else if ($thing == '&yen;&yen;') {
            return (++$trigger_deleted % 2 ? '<span class="deletions">' : '</span>');

        // center
        } else if ($thing == '@@') {
            return (++$trigger_center % 2 ? '<div class="center">'.$cr : $cr.'</div>'.$cr);

        // urls
        } else if (preg_match('/^([a-z]+:\/\/\S+?)([^[:alnum:]^\/])?$/', $thing, $matches)) {
            $url = $matches[1];
            if (preg_match('/^(.*)\.(gif|jpg|jpeg|png)/si', $url)) {
                return DataUtil::formatForDisplayHTML('<img src="'.$url.'" alt="image" />'.$matches[2]);
            } else {
                // Mind Mapping Mod
                if (preg_match('/^(.*)\.(mm)/si', $url)) {
                    return ModUtil::apiFunc('Wikula', 'user', 'Action',
                                        array('action' => 'mindmap',
                                            'url'    => $url));
                } else {
                    $matches[2] = (isset($matches[2]) ? $matches[2] : '');
                    return ModUtil::apiFunc('Wikula', 'user', 'Link',
                                        array('tag'    => $url,
                                            'method' => '',
                                            'text'   => $matches[2])).$matches[2];
                }
            }

        // header level 5
        } else if ($thing == '==') {
            $br = false;
            return (++$trigger_l[5] % 2 ? '<h5>' : '</h5>'.$cr);

        // header level 4
        } else if ($thing == '===') {
            $br = false;
            return (++$trigger_l[4] % 2 ? '<h4>' : '</h4>'.$cr);

        // header level 3
        } else if ($thing == '====') {
            $br = false;
            return (++$trigger_l[3] % 2 ? '<h3>' : '</h3>'.$cr);

        // header level 2
        } else if ($thing == '=====') {
            $br = false;
            return (++$trigger_l[2] % 2 ? '<h2>' : '</h2>'.$cr);

        // header level 1
        } else if ($thing == '======') {
            $br = false;
            return (++$trigger_l[1] % 2 ? '<h1>' : '</h1>'.$cr);

        // forced line breaks
        } else if ($thing == '---') {
            return '<br />';

        // escaped text
        } else if (preg_match('/^""(.*)""$/s', $thing, $matches)) {
            $ddquotes_policy = $this->getVar('double_doublequote_html', 'safe');
            $embedded = $matches[1];

            if ($ddquotes_policy == 'safe' || $ddquotes_policy == 'raw')
            {
                // get tags with id attributes
                // use backref to match both single and double quotes
                $patTagWithId = '((<[a-z][^>]*)((?<=\\s)id=("|\')(.*?)\\4)(.*?>))';
                // with PREG_SET_ORDER we get an array for each match: easy to use with list()!
                // we do the match case-insensitive so we catch uppercase HTML as well;
                // SafeHTML will treat this but 'raw' may end up with invalid code!
                $tags2 = preg_match_all('/'.$patTagWithId.'/i', $embedded, $matches2, PREG_SET_ORDER);
                // step through code, replacing tags with ids with tags with new ('repaired') ids
                $tmpembedded = $embedded;
                $newembedded = '';
                for ($i=0; $i < $tags2; $i++)
                {
                    // $attrid not needed, just for clarity
                    list( , $tag, $tagstart, $attrid, $quote, $id, $tagend) = $matches2[$i];
                    // split in two at matched tag
                    $parts = explode($tag, $tmpembedded, 2);
                    // replace if we got a new value
                    if ($id != ($newid = $this->makeId('embed', $id))) {
                        $tag = $tagstart.'id='.$quote.$newid.$quote.$tagend;
                    }
                    // append (replacement) tag to first part
                    $newembedded .= $parts[0].$tag;
                    // after tag: next bit to handle
                    $tmpembedded  = $parts[1];
                }
                // add last part
                $newembedded .= $tmpembedded;
            }

            switch ($ddquotes_policy)
            {
                case 'safe':
                    return DataUtil::formatForDisplayHTML($newembedded);
                case 'raw':
                    return $newembedded; // may still be invalid code - 'raw' will not be corrected!
                default:
                    return $this->htmlspecialchars_ent(array('text' => $embedded)); // display only
            }


        // Elided content (eliminates trailing ws)
        } elseif(preg_match("/^\/\*(.*?)\*\/[\s]*$/s", $thing, $matches)){
            return null;

        // Elided content (preserves trailing ws)
        } elseif(preg_match("/``(.*?)``/s", $thing, $matches)) {
            return null;

        // code text
        } else if (preg_match('/^%%(.*?)%%$/s', $thing, $matches)) {
            /*
            * Note: this routine is rewritten such that (new) language formatters
            * will automatically be found, whether they are GeSHi language config files
            * or "internal" Wikka formatters.
            * Path to GeSHi language files and Wikka formatters MUST be defined in config.
            * For line numbering (GeSHi only) a starting line can be specified after the language
            * code, separated by a ; e.g., %%(php;27)....%%.
            * Specifying >= 1 turns on line numbering if this is enabled in the configuration.
            */
            $code = $matches[1];
            // if configuration path isn't set, make sure we'll get an invalid path so we
            // don't match anything in the home directory
            $geshi_hi_path = 'modules/Wikula/pnincludes/geshi/geshi';
            $wikka_hi_path = 'modules/Wikula/pnincludes/formatters';
            // check if a language (and starting line) has been specified
            if (preg_match('/^'.PATTERN_OPEN_BRACKET.PATTERN_FORMATTER.PATTERN_LINE_NUMBER.PATTERN_FILENAME.PATTERN_CLOSE_BRACKET.PATTERN_CODE.'$/s', $code, $matches)) {
                $language = isset($matches[1]) ? $matches[1] : null;
                $start    = isset($matches[3]) ? $matches[3] : null;
                $filename = isset($matches[5]) ? $matches[5] : null;
                $invalid  = isset($matches[6]) ? $matches[6] : null;
                $code     = isset($matches[7]) ? $matches[7] : null;
            }
            // get rid of newlines at start and end (and preceding/following whitespace)
            // Note: unlike trim(), this preserves any tabs at the start of the first "real" line
            $code = preg_replace('/^\s*\n+|\n+\s*$/', '', $code);

            // check if GeSHi path is set and we have a GeSHi hilighter for this language
            $geshi_path = is_dir('modules/Wikula/pnincludes/geshi/');

            if (isset($language) && $geshi_path && file_exists("{$geshi_hi_path}/{$language}.php")) {
                // check if specified filename is valid and generate code block header
                if (isset($filename) && strlen($filename) > 0 && strlen($invalid) == 0) {
                    // TODO: use central regex library for filename validation
                    $valid_filename = $filename;
                    // create code block header
                    $output .= '<div class="code_header">';
                    // display filename and start line, if specified
                    $output .= $filename;
                    if (strlen($start) > 0) {
                        $output .= ' (line '.$start.')';
                    }
                    $output .= '</div>'.$cr;
                }
                // use GeSHi for hilighting
                $output = ModUtil::apiFunc('Wikula', 'user', 'GeSHi_Highlight',
                                    array('sourcecode' => $code,
                                            'language'   => $language,
                                            'start'      => $start));

            } elseif (isset($language) && isset($wikka_hi_path) && file_exists("{$wikka_hi_path}/{$language}.php")) {
                // check Wikka highlighter path is set and if we have an internal Wikka hilighter
                // use internal Wikka hilighter
                $output  = '<div class="code">'.$cr;
                include("{$wikka_hi_path}/{$language}.php");
                $output .= $code.$cr;
                $output .= '</div>'.$cr;

            } else {
                // no language defined or no formatter found: make default code block;
                $output = '<div class="code">'.$cr;
                $output .= '<pre>'.htmlspecialchars($code, ENT_QUOTES).'</pre>'.$cr;
                $output .= '</div>'.$cr;
            }

            // display grab button if option is set in the config file
            if ($this->getVar('grabcode_button', true)) {
                // build form
                $output .= '<form method="post" action="'.ModUtil::apiFunc('Wikula', 'user', 'grabcode').'">
                            <input type="submit" name="save" class="grabcode" value="'.__('Grab code').'" title="'.__('Grab code').'" />
                            <input type="hidden" name="filename" value="'.urlencode($valid_filename).'" />
                            <input type="hidden" name="code" value="'.urlencode(nl2br($code)).'" />
                            </form>';
            }

            return $output;

        // recognize forced links across lines
        // @@@ regex accepts NO non-whitespace before whitespace, surely not correct? [[  something]]
        } elseif (preg_match('/^\[\[(\S*)(\s+(.+))?\]\]$/s', $thing, $matches) || preg_match('/^\(\((\S*)(\s+(.+))?\)\)$/s', $thing, $matches)) {
            // forced links
            // \S : any character that is not a whitespace character
            // \s : any whitespace character
            // $matches[1] = url, $matches[3] = text
            // TODO: debug if needed
            if (isset($matches[1]) && !empty($matches[1])) {
                $result = '';
                $url = $matches[1];
                /*if ($url != ($url=(preg_replace('/@@|&pound;&pound;|\[\[/', '', $url)))) {
                    $result = '</span>';
                }*/
                $text = isset($matches[3]) ? $matches[3] : $url;
                //$text = preg_replace('/@@|&pound;&pound;|\[\[/', '', $text);
                return $result.ModUtil::apiFunc('Wikula', 'user', 'Link',
                                            array('tag'  => $url,
                                                'text' => $text));
            } else {
                return '';
            }

        // indented text
        } elseif (preg_match("/(^|\n)([\t~]+|[ ]{2,})+(-(?!-)|&|\*(?!\*)|([0-9]+|[a-zA-Z]+)\))?(\n|$)/s", $thing, $matches)) {
            // find out which indent type we want
            $newIndentType  = $matches[3];
            $newIndentLevel = (strpos($matches[2], ' ') === false) ? strlen($matches[2]) : strlen($matches[2])/2;

            // close indent or list element
            if ($li == 2 && $curIndentType != '.') $result .= '</li>';
            if ($li == 2) $result .= ($br ? '<br />'.$cr : $cr);
            $li = 0;

            // we definitely want no line break in this one.
            $br = false;

            if (empty($newIndentType)) {
                $newIndentType = '.';
                $li = 1;
                $br = true;
            } else {
                if (preg_match('`[0-9]`', $newIndentType[0])) { $newIndentType = '1'; }
                elseif (preg_match('`[IVX]`', $newIndentType[0])) { $newIndentType = 'I'; }
                elseif (preg_match('`[ivx]`', $newIndentType[0])) { $newIndentType = 'i'; }
                elseif (preg_match('`[A-Z]`', $newIndentType[0])) { $newIndentType = 'A'; }
                elseif (preg_match('`[a-z]`', $newIndentType[0])) { $newIndentType = 'a'; }

                $li = 1;
            }

            if ($newIndentLevel < $oldIndentLevel) {
                for (; $newIndentLevel < $oldIndentLevel; $oldIndentLevel--)
                {
                    $curIndentType = array_pop($indentClosers);
                    if ($oldIndentLevel > 1) {
                        $result .= str_repeat("\t", $oldIndentLevel -1);
                    }
                    if ($curIndentType == '.') {
                        $result .= '</div>';
                    } elseif ($curIndentType == '-' || $curIndentType == '&' || $curIndentType == '*') {
                        $result .= '</ul>';
                    } else {
                        $result .= '</ol>';
                    }
                    $result .= $cr;
                }
            }

            if ($oldIndentLevel == $newIndentLevel) {
                $curIndentType = array_pop($indentClosers);
                if ($newIndentType != $curIndentType)
                {
                    if ($oldIndentLevel > 1) {
                        $result .= str_repeat("\t", $oldIndentLevel -1);
                    }
                    if ($curIndentType == '.') {
                        $result .= '</div>';
                    } elseif ($curIndentType == '-' || $curIndentType == '&' || $curIndentType == '*') {
                        $result .= '</ul>';
                    } else {
                        $result .= '</ol>';
                    }
                    $oldIndentLevel = $newIndentLevel - 1;
                    $result .= $cr;

                } else {
                    array_push($indentClosers, $curIndentType);
                }
            }

            if ($newIndentLevel > $oldIndentLevel) {
                for (; $newIndentLevel > $oldIndentLevel; $oldIndentLevel++)
                {
                    $result .= str_repeat("\t", $oldIndentLevel);
                    if ($newIndentType == '.') {
                        $result .= '<div class="indent">';
                    } else if ($newIndentType == '-' || $newIndentType == '&' || $newIndentType == '*') {
                        $result .= '<ul';
                        if ($newIndentType == '&') {
                            $result .= ' class="thread"';
                        }
                        $result .= '>';
                    } else {
                        $result .= '<ol type="'.$newIndentType.'">';
                    }
                    $result .= $cr;
                    array_push($indentClosers, $newIndentType);
                }
            }

            $oldIndentLevel = $newIndentLevel;

            $result .= str_repeat("\t", $oldIndentLevel);
            if ($li == 1) {
                if ($newIndentType != '.') {
                    $result .= '<li>';
                }
                $li = 2;
            }

            $curIndentType = $newIndentType;
            return $result;

        // new lines
        } elseif ($thing == $cr) {
            // close lines in indents and list elements
            if ($li == 2) {
                if ($curIndentType != '.') {
                    $result .= '</li>';
                } else {
                    $result .= '<br/>';
                }
                $result .= $cr;
                $li = 0;
            }
            // if we got here, there was no tab in the next line; this means that we can close all open indents and lists.
            for (; 0 < $oldIndentLevel; $oldIndentLevel--)
            {
                $curIndentType = array_pop($indentClosers);
                if ($oldIndentLevel > 1) {
                    $result .= str_repeat("\t", $oldIndentLevel-1);
                }
                if ($curIndentType == '.') {
                    $result .= '</div>';
                } elseif ($curIndentType == '-' || $curIndentType == '&' || $curIndentType == '*') {
                    $result .= '</ul>';
                } else {
                    $result .= '</ol>';
                }
                $result .= $cr;
                $br = false;
            }
            $oldIndentLevel = 0;

            $result .= ($br ? '<br />'.$cr : $cr);
            $br = true;

            return $result;

        // Actions
        } else if (preg_match('/^\{\{(.*?)\}\}$/s', $thing, $matches)) {
            if (isset($matches[1]) && !empty($matches[1])) {
                return ModUtil::apiFunc('Wikula', 'user', 'Action',
                                    array('action' => $matches[1]));
            } else {
                return '{{}}';
            }

        // InterWiki links!
        } else if (preg_match("/^[A-Z���][A-Za-z�������]+[:]\S*$/s", $thing)) {
            return ModUtil::apiFunc('Wikula', 'user', 'Link',
                                array('tag' => $thing));

        // CamelWords unparsed
        //} else if (preg_match("/^\!{0,1}[A-Z���]+[a-z����]+[A-Z0-9���][A-Za-z0-9�������]*$/s", $thing)) {
        } else if (preg_match("/^\!?[A-Z0-9���]+[a-z����]+[A-Z0-9���][A-Za-z0-9�������]*$/s", $thing)) {
            if ($thing[0] == '!') {
                return DataUtil::formatForDisplay(substr($thing, 1));
            } else {
                return ModUtil::apiFunc('Wikula', 'user', 'Link', array('tag' => $thing));
            }

        // wiki links!
        } elseif (preg_match('/^[A-Z���]+[a-z����]+[A-Z0-9���][A-Za-z0-9�������]*$/s', $thing)) {
            return ModUtil::apiFunc('Wikula', 'user', 'Link',
                                array('tag'  => $thing));

        // separators
        } else if (preg_match('/-{4,}/', $thing, $matches)) {
            $br = false;

            return '<hr />'.$cr;

        // Removing this until it's been worked out
        } else if (preg_match('/^<map.*<\/map>$/s', $thing)) {
            // mind map xml
            $maptemp = $mapcounter;
            $mapcounter++;
            //return pnGetBaseUrl();
            //$mapurl =  ModUtil::url('Wikula', 'user', 'mindmap');
            //return ModUtil::apiFunc('Wikula', 'user', 'Action', array('action' => 'mindmap', 'url' => 'index.php?module=wikula&ampfunc=mindmap&amp;tag='.$tag.'&amp;mapcounter='.$maptemp));
            //return ModUtil::apiFunc('Wikula', 'user', 'Action', array('action' => 'mindmap', 'url' => $mapurl));
            //SessionUtil::setVar('wikula_map', base64_encode($thing));
            //return 'tada !';
            //return ModUtil::apiFunc('Wikula', 'user', 'Action', array('action' => 'mindmap', 'url' =>  ModUtil::url('Wikula', 'user', 'mindmap')));
            return 'Instant Map coming soon!';

        } elseif ($thing[0] == '&') {
            return $this->htmlspecialchars_ent(array('text' => $thing));
        }

        // if we reach this point, it must have been an accident.
        return $thing;
    }

    function parse_attributes($attribs, $hints)
    {
        
        // Sort different attributes / keys to use for different elements.
        static $attributes = array(
            'core' => array(
                            'c' => 'class',
                            'i' => 'id',
                            's' => 'style',
                            't' => 'title'
                        ),
            'i18n' => array(
                            'd' => 'dir',
                            'l' => 'xml:lang'
                        ),
            'cell' => array(
                            'a' => 'abbr',
                            'h' => 'headers',
                            'o' => 'scope',
                            'x' => 'colspan',
                            'y' => 'rowspan',
                            'z' => 'axis'
                        ),
            'other_table' => array(
                                'p' => 'span',
                                'u' => 'summary'
                                )
        );

        // adds in default hints ( core + i18n )
        $hints['core'] = 'core';
        $hints['i18n'] = 'i18n';

        $attribs = preg_split('/;(?=.:)/', $attribs);
        $return_value = '';

        foreach ($attribs as $attrib)
        {
            list ($key, $value) = explode(':', $attrib, 2);
            foreach ($hints as $hint)
            {
                $temp = $attributes[$hint];
                if ($temp) {
                    $a = $temp[$key];
                }
                if ($a) {
                    break;
                }
            }

            if (!$a) {
                //This attribute isn't allowed here / is wrong.
                // WARNING: JS vulnerability: two minus signs are not allowed in a comment, so we replace any occurence of them by underscore.
                // Consider the code ||(p--><font size=1px><a href=...<!--:blabla
                // When migrating to UTF-8, we could use str_replace('--', '−−', $key) to make things more pretty.
                // TODO garbled ... mdash
                $return_value .= '<!--Cannot find attribute for key "'.str_replace('--', '__', $key).'" from hints given.-->'."\n";    #i18n

            } else {
                // WARNING: JS vulnerability: use htmlspecialchars_ent to prevent JS attack!
                $return_value .= ' '.$a.'="'.$this->htmlspecialchars_ent(array('text' => $value)).'"';
            }
        }

        return $return_value;
    }

    /**
    * "Afterburner" formatting: extra handling of already-generated XHTML code.
    *
    * Ensure every heading has an id, either specified or generated. (May be
    * extended to generate section TOC data.)
    * If an id is already specified, that is used without any modification.
    * If no id is specified, it is generated on the basis of the heading context:
    * - any image tag is replaced by its alt text (if specified)
    * - all tags are stripped
    * - all characters that are not valid in an ID are stripped (except whitespace)
    * - the resulting string is then used by makedId() to generate an id out of it
    *
    * @access    private
    *
    * @param    array    $things    required: matches of the regex in the preg_replace_callback
    * @return    string    heading with an id attribute
    */
    function wakka3callback($things)
    {
        $thing = $things[1];

        // heading
        if (preg_match(PATTERN_MATCH_HEADINGS, $thing, $matches))
        {
            list($h_element, $h_tagname, $h_attribs, $h_heading) = $matches;

            if (preg_match(PATTERN_MATCH_ID_ATTRIBUTES, $h_attribs)) {
                // existing id attribute: nothing to do (assume already treated as embedded code)
                // @@@ we *may* want to gather ids and heading text for a TOC here ...
                // heading text should then get partly the same treatment as when we're creating ids:
                // at least replace images and strip tags - we can leave entities etc. alone - so we end up with
                // plain text-only
                // do this if we have a condition set to generate a TOC
                return $h_element;

            } else {
                // no id: we'll have to create one
                $headingtext = $this->CleanTextNode($h_heading);
                // now create id based on resulting heading text
                $h_id = $this->makeId(array('group' => 'hn', 'id' => $headingtext));

                // The text of a heading is now becoming a link to this heading, allowing an easy way to copy link to clipboard.
                // For this, we take the textNode child of a heading, and if it is not enclosed in <a...></a>, we enclose it in
                // $opening_anchor and $closing_anchor.
                $opening_anchor = '<a class="heading" href="#'.$h_id.'">';
                $closing_anchor = '</a>';
                $h_heading = preg_replace('@('.PATTERN_OPEN_A_ALONE. '|'.PATTERN_END_OF_STRING_ALONE.  ')@', $closing_anchor.'\\0', $h_heading);
                $h_heading = preg_replace('@('.PATTERN_CLOSE_A_ALONE.'|'.PATTERN_START_OF_STRING_ALONE.')@', '\\0'.$opening_anchor, $h_heading);

                // rebuild element, adding id
                return '<'.$h_tagname.$h_attribs.' id="'.$h_id.'">'.$h_heading.'</'.$h_tagname.'>';
            }
        }
        // other elements to be treated go here (tables, images, code sections...)
    }

    public function CleanTextNode($textvalue, $pattern_prohibited_chars = PATTERN_INVALID_ID_CHARS, $decode_html_entities = true)
    {
        
        $textvalue = trim($textvalue);
        // First find and replace any image having an alt attribute with its (trimmed) alt text
        // Image tags missing an alt attribute are not replaced.
        $textvalue = preg_replace(PATTERN_REPLACE_IMG_WITH_ALTTEXT, '\\2', $textvalue);
        // @@@ JW/2005-05-27 now first replace linebreaks <br/> and other whitespace with single spaces!!
        // Remove all other tags, including img tags that missed an alt attribute
        $textvalue = strip_tags($textvalue);
        // @@@ this all-text result is usable for a TOC!!!
        // Use this if we have a condition set to generate a TOC
        // END -- nodeToTextOnly

        if ($decode_html_entities) {
            if (function_exists('html_entity_decode')) {
                // replace entities that can be interpreted
                // use default charset ISO-8859-1 because other chars won't be valid for an ID anyway
                $textvalue = html_entity_decode($textvalue, ENT_NOQUOTES);
            }
            // remove any remaining entities (so we don't end up with strange words and numbers in the ID text)
            $textvalue = preg_replace('/&[#]?.+?;/','',$textvalue);
        }

        // finally remove non-ID characters (except whitespace which is handled by makeId())
        if ($pattern_prohibited_chars) {
            $textvalue = preg_replace($pattern_prohibited_chars, '', $textvalue);
        }

        return $textvalue;
    }

    /**
    * Build an element ID
    *
    * @param string $args['group'] group of the id to build
    * @return final id
    */
    public function makeId($args)
    {
        if (!isset($args['group'])) {
            return LogUtil::registerArgsError();
        }

        $group = $args['group'];
        $id    = (isset($args['id'])) ? $args['id'] : '';
        unset($args);

        // initializations
        static $aSeq = array(); // group sequences
        static $aIds = array(); // used ids

        // preparation for group
        // make sure group starts with a letter
        if (!preg_match('/^[A-Z-a-z]/',$group)) {
            $group = 'g'.$group;
        }

        if (!isset($aSeq[$group])) {
            $aSeq[$group] = 0;
        }

        if (!isset($aIds[$group])) {
            $aIds[$group] = array();
        }

        if ('embed' != $group) {
            // replace any whitespace sequence in $id with a single underscore
            $id = preg_replace('/\s+/','_',trim($id));
        }

        // validation (full for 'embed', characters only for other groups since we'll add a prefix)
        if ('embed' == $group) {
            // ref: http://www.w3.org/TR/html4/types.html#type-id
            $validId = preg_match('/^[A-Za-z][A-Za-z0-9_:.-]*$/',$id);
        } else {
            $validId = preg_match('/^[A-Za-z0-9_:.-]*$/',$id);
        }

        // build or generate id
        // ignore specified id if it is invalid or exists already
        if ('' == $id || !$validId || in_array($id, $aIds)) {
            // use group and id as basis for generated id
            $id = substr(md5($group.$id), 0, ID_LENGTH);
        }

        // add group prefix (unless embedded HTML)
        $idOut = ('embed' == $group) ? $id : $group.'_'.$id;

        if (in_array($id, $aIds[$group])) {
            // add suffiX to make ID unique
            $idOut .= '_'.++$aSeq[$group];
        }

        // result
        // keep track of both specified and generated ids (without suffix)
        $aIds[$group][] = $id;

        return $idOut;
    }
    
}