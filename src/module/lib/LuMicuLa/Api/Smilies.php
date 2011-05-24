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

class LuMicuLa_Api_Smilies extends Zikula_AbstractApi 
{

    public function smilies() { 
        return array(
            ':-)'        => 'happy.png',
            ';-)'        => 'wink.png',
            ':-('        => 'sad.png', 
            'O:-)'       => 'angel.png',
            ':-x'        => 'angry.png',
            ':arrow:'    => 'arrow.png',
            ':-D'        => 'biggrin.png',
            '§)'         => 'canny.png',
            ':-?'        => 'confused.png',
            '8-)'        => 'cool.png',
            ';-('        => 'cry.png',
            '8-o'        => 'eek.png',
            ']:-('       => 'evil.png',
            ':!:'        => 'exclaim.png',
            '<3'         => 'favorite.png',
            ':[]'        => 'grin.png',
            ':idea:'     => 'idea.png',
            ':lol: '     => 'neutral.png',
            ':-P'        => 'razz.png',
            ':-$'        => 'redface.gif',
            ':roll:'     => 'rolleyes.gif',
            ':->'        => 'smile.png',
            ':-o'        => 'surprised.png',
            ':thumbsup:' => 'thumbsup.png',
            ']:-)'       => 'twisted.png',
        );
    }
    
    public function alternative_smilies() { 
        return array(
            ':)'        => ':-)',
            ';)'        => ';-)',
            ':('        => ':-(', 
            'O:)'       => 'O:-)',
            ':x'        => ':-x',
            ':D'        => ':-D',
            ':?'        => ':-?',
            '8)'        => '8-)',
            ';('        => ';-(',
            '8o'        => '8-o',
            ']:('       => ']:-(',
            ':P'        => ':-P',
            ':$'        => ':-$',
            ':>'        => ':->',
            ':o'        => ':-o',
            ']:)'       => ']:-)',
        );
    }
   
}