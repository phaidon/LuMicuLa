{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{modgetinfo modname=$modname info='displayname'}</h3>
</div>

{modurl modname=$modname type="admin" func="hooks" assign="hookadmin"}

{form cssClass="z-form"}
{formvalidationsummary}

{if empty($bindedHooks)}
<p class="z-errormsg">
    {gt text="There is no filter hook binded. You can add a hook <a href='%s' target='blank'>here</a>!" tag1=$hookadmin}
</p>
{/if}

<fieldset>
    <div class="z-formrow">
        {formlabel for="language" __text="Lightweight markup language"}
        {formdropdownlist id="language" items=$lmls}
    </div>
</fieldset>


<fieldset>
    <legend>{gt text='Elements'}</legend>
    <table class="z-datatable">
        {assign var='i' value=0}
        {foreach from=$allElements item='element' key='key'}
        {if $i == 0}
        <tr class="{cycle values="z-odd,z-even"}">
            {/if}
            <td>{formcheckbox id="$key" cssClass="tags" group="elements"}</td>
            <td><label for="{$key}">{img modname='LuMicuLa' src=$element.icon title=$element.title}</label></td>
            <td class="z-nowrap"><label for="{$key}">{$element.title}</label></td>
            {assign var='i' value=$i+1}
            {if $i == 4}
        </tr>
        {assign var='i' value=0}
        {/if}
        {/foreach}

        {if $i == 0}
        <tr class="{cycle values="z-odd,z-even"}">
            {/if}
            <td>{formcheckbox id='smilies' cssClass="tags"}</td>
            <td>{img modname='LuMicuLa' src='smiley.png' __title='Smilies'}</td>
            <td>{gt text='Smilies'}</td>
        </tr>
    </table>
    <a href="javascript:void(0);" id="lml_select_all">{gt text="Check all"}</a> / <a href="javascript:void(0);" id="lml_deselect_all">{gt text="Uncheck all"}</a>
</fieldset>

<fieldset>
    <legend>{gt text='Editor'}</legend>
    <div class="z-formrow">
        {formlabel for="allTextAreas" __text="Use the editor in all textareas of the module"}
        {formcheckbox id='allTextAreas'}
    </div>
    <div class="z-formrow" id="textAreaNamesContainer">
        {formlabel for="textAreaNames" __text="Use the editor just for following textareas"}
        {formtextinput id='textAreaNames' maxLength=255}
        <em class="z-formnote z-sub">{gt text="(comma separated)"}</em>
    </div>
    <div class="z-formrow">
        {formlabel for="allFunctions" __text="Use the editor in all functions of the module"}
        {formcheckbox id='allFunctions'}
    </div>
    <div class="z-formrow" id="functionNamesContainer">
        {formlabel for="functionNames" __text="Use the editor just for following module functions"}
        {formtextinput id='functionNames' maxLength=255}
        <em class="z-formnote z-sub">{gt text="(comma separated)"}</em>
    </div>

</fieldset>

<fieldset>
    <legend>{gt text='Transformer'}</legend>
    <p class="z-informationmsg">
        {gt text="The transformer will transfrom all strings that are connected to the binded filter hooks. For example [i]italic[/i] could be transformed to &lt;i&gt;italic&lt;/i&gt;."}
    </p>
    <div class="z-formrow">
        {formlabel for="textAreaNames" __text="Binded filter hooks"}
        <div>
            {if empty($bindedHooks)}
            <span style="color:red">{gt text="None"}</span>
            {else}
            {$bindedHooks}
            {/if}
            <br />{gt text="<a href='%s' target='blank'>Configure binded hooks</a>" tag1=$hookadmin}
        </div>
</fieldset>

<div class="z-formbuttons z-buttons">
    {formbutton class="z-bt-ok" commandName="save" __text="Save"}
    {formbutton class="z-bt-delete" commandName="delete" __text="Delete"}
    {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
</div>

{/form}

{adminfooter}

<script type="text/javascript">
    $('lml_select_all').observe('click', function(e){
        Zikula.toggleInput('.tags', true);
        e.stop();
    });
    $('lml_deselect_all').observe('click', function(e){
        Zikula.toggleInput('.tags', false);
        e.stop();
    });

    $('allTextAreas').observe('click', function(e){
        $('textAreaNamesContainer').toggle();
    });

    Event.observe(window, 'load', function() {
        if ($('allTextAreas').checked) {
            $('textAreaNamesContainer').hide();
        }
    });

    $('allFunctions').observe('click', function(e){
        $('functionNamesContainer').toggle();
    });

    Event.observe(window, 'load', function() {
        if ($('allFunctions').checked) {
            $('functionNamesContainer').hide();
        }
    });
</script>