{ajaxheader modname='LuMicuLa' filename='smilies.js' ui=true}
{pageaddvar name="javascript" value="modules/LuMicuLa/javascript/editor.js"}
{pageaddvar name="stylesheet" value="modules/LuMicuLa/style/editor.css"}

<div class="lumicula_editor_bar" id="lumicula_editor_bar">



    {foreach from=$items item="item"}
        {if !array_key_exists('subitems', $item)}
            {assign var='begin' value=$item.begin}
            {assign var='inner' value=$item.inner}
            {assign var='end'   value=$item.end}
            {assign var='title' value=$item.title}
            {gt assign='ctrl' text='ctrl'}

            {if array_key_exists('shortcut', $item)}
            {assign var='shortcut' value=$item.shortcut}
            <script type="text/javascript">
                new HotKey('{{$shortcut}}',function(event){
                    insertAtCursor({{$textfieldname}}, '{{$begin}}', '{{$inner}}', '{{$end}}');
                }); 
            </script>
            {assign var='title' value="$title ($ctrl+$shortcut)"}
            {/if}


            {img modname='LuMicuLa' src=$item.icon title=$title 
            onclick="insertAtCursor('$textfieldname', '$begin', '$inner', '$end');return false"}
        {else}
            <select style="height:22px;vertical-align:middle;margin-top:-15px" onchange="insertAtCursor2({$textfieldname}, this.value);this.selectedIndex=0;return false">
                <option value="0">{gt text='Heading'}</option>
                {foreach from=$item.subitems item='subitem' key='key'}
                <option value="{$subitem.begin},{$subitem.inner},{$subitem.end}">{$subitem.title}</option>
                {/foreach}
             </select>
        {/if}

    {/foreach}


    {if $quote}
        {img modname='LuMicuLa' src='quote.png' __title='Quote'
        onclick="insertAtCursor('$textfieldname', '[quote]', '', '[/quote]');return false"}
    {/if}

    {if count($smilies) > 0}
    {img modname='LuMicuLa' src='smiley.png' __title='Smiley'
    onclick="toggle_smileybox()"}
    {/if}

</div>

{if count($smilies) > 0}
<div id="lumicula_smiley_bar" class="lumicula_smiley_bar" style="display: none;">
{foreach from=$smilies item="icon" key="tag"}
    {img modname='LuMicuLa' src="smilies/$icon" title=$tag 
    onclick="insertAtCursor('$textfieldname', '$tag ', '', '');return false"}
{/foreach}
</div>
{/if}

<input type="hidden" id='lumicula_textfieldname' value='{$textfieldname}' />