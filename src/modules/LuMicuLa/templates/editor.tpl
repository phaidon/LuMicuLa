{pageaddvar name='javascript' value='prototype'}
{ajaxheader modname='LuMicuLa' filename='smilies.js' ui=true}
{pageaddvar name="javascript" value="modules/LuMicuLa/javascript/editor.js"}
{pageaddvar name="stylesheet" value="modules/LuMicuLa/style/editor.css"}

<script type="text/javascript">
    // move the bar before the textfied
    var editorOptions = {{$elements}};

    {{if $allTextAreas}}
    var textareas = document.getElementsByTagName('textarea');
    var i = textareas.length; while( i-- ) {
        new LuMicuLa(textareas[i], editorOptions, {{$smilies}}, {{$quote}} );
    }
    {{else}}
    {{foreach from=$textAreaNames item='textfieldname'}}
    var textarea = document.getElementById('{{$textfieldname}}');
    new LuMicuLa(textarea, editorOptions, {{$smilies}}, {{$quote}} );
    {{/foreach}}
    {{/if}}
</script>