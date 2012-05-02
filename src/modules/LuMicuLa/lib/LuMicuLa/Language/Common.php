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

class LuMicuLa_Language_Common extends Zikula_AbstractBase
{
    
    public $protected = array();
    public $protect = false;
    
    public function image($src, $title = '')
    {
        if ($this->protect) {
            $c = count($this->protected);
            $this->protected[$c] = $src;
            $src = 'PROTECTED'.$c;
            $this->protected[$c+1] = $title;
            $title = 'PROTECTED'.$c;
        }
        
        
        return '<a href="'.$src.'" rel="imageviewer">'.
               '<img src="'.$src.'" title="'.$title.'" alt="'.$title.'" width="250" />'.
               '</a>';
    }
    
    
    public function link($url, $title = null, $couldBeAPage = true)
    {    

        
        if (empty($title)) {
            $title = $url;
        } 
        
        $mailto = substr($url, 0, 6) == 'mailto';
        
        $pos = strpos($url, '://');
        if(substr($url, 0, 12) != 'EXTRACTEDURL' && $pos === false && !$mailto && $couldBeAPage) {
            return self::page($url, $title);
        }
        
        if(is_null($title) ) {
            if($mailto) {
                $title = substr($url, 7);
            } else {
                $title = $url;
                $title = self::minimize_displayurl($title);
            }
        }
        
        $url = DataUtil::formatForDisplay($url);
        $title = DataUtil::formatForDisplay($title);
        
        if ($this->protect) {
            $c = count($this->protected);
            $this->protected[$c] = $url;
            $url= 'PROTECTED'.$c;
            $c++;
            $this->protected[$c] = $title;
            $title = 'PROTECTED'.$c;
        }
        
        
        return '<a href="'.$url.'">'.$title.'</a>';
    }
    
    
    public function page($tag, $title)
    {
        
        
        $url = '';
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
            if (!ModUtil::apiFunc('Wikula', 'user', 'PageExists', $id))
            {
                $url = ModUtil::url('Wikula', 'user', 'edit', array('tag' => $id) );

                $url = DataUtil::formatForDisplay($url);
                $title = DataUtil::formatForDisplay($title);
                
                if ($this->protect) {
                    $c = count($this->protected);
                    $this->protected[$c] = $url;
                    $url= 'PROTECTED'.$c;
                    $c++;
                    $this->protected[$c] = $title;
                    $title = 'PROTECTED'.$c;
                }
                
                return $title.'<a href="'.$url.'">?</a>';
            }
            $url = ModUtil::url('Wikula', 'user', 'show', array('tag' => $id) );
            break;
        }
        

        
        
        $url = DataUtil::formatForDisplay($url);
        $title = DataUtil::formatForDisplay($title);
        
        
        if ($this->protect) {
            $c = count($this->protected);
            $this->protected[$c] = $url;
            $url= 'PROTECTED'.$c;
            $c++;
            $this->protected[$c] = $title;
            $title = 'PROTECTED'.$c;
        }
        
        
        return '<a href="'.$url.'">'.$title.'</a>';

    }
    
        
    /** minimize_displayurl
     *  helper function to cut down the displayed url to a maximum length
     *
     *
     */
    public function minimize_displayurl($displayurl)
    {
        // get the maximum size of the urls to show
        $maxsize = ModUtil::getVar('LuMicuLa', 'link_shrinksize', 50);
        if($maxsize<>0 && strlen($displayurl) > $maxsize) {
            $before = round($maxsize / 2);
            $after  = $maxsize - 1 - $before;
            $displayurl = substr($displayurl, 0, $before) . " ... " . substr($displayurl, strlen($displayurl) - $after, $after);
        }
        return $displayurl;
    }

    
}