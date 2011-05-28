{include file="admin/header.tpl"}

<div class="z-adminpageicon">{icon type="config" size="large"}</div>

<h2>{$templatetitle}</h2>


{form cssClass="z-form"}
{formvalidationsummary}

<fieldset>
    {if $create}
    <div class="z-formrow">
        {formlabel for="modname" __text="Module"}
        {formdropdownlist id="modname" items=$modules}
    </div>
    {/if}

    <div class="z-formrow">
        {formlabel for="language" __text="Lightweight markup language"}
        {formdropdownlist id="language" items=$lmls}
    </div>

</fieldset>


<fieldset>
    <legend>Elements</legend><br />
    <table cellpadding=5><tr>
        {foreach from=$elements item='element' key='key'}
        <td>{img modname='LuMicuLa' src=$element.icon' title=$element.title}</td>
        <td>{formcheckbox id="$key"}</td>
        {/foreach}


        <td>{img modname='LuMicuLa' src='smiley.png' __title='Smilies'}</td>
        <td>{formcheckbox id='smilies'}</td>

        <td>{img modname='LuMicuLa' src='headings.png' __title='Headings'}</td>
        <td>{formcheckbox id='headings'}</td>

  

    </tr></table>

</fieldset>

<div class="z-formbuttons z-buttons">
    {formbutton class="z-bt-ok" commandName="save" __text="Save"}
    {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
</div>

{/form}

{modgetinfo info=all}
<p class="z-center"><a href="http://code.zikula.org/LuMicuLa/" title="{gt text="Visit the LuMicuLa project site"}">{$modinfo.displayname} {$modinfo.version}</a></p>
</div>