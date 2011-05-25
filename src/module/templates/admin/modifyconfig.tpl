{include file="admin/header.tpl"}

<div class="z-adminpageicon">{icon type="config" size="large"}</div>

<h2>{gt text="Settings"}</h2>





{form cssClass="z-form"}
{formvalidationsummary}

<fieldset>
    <div class="z-formrow">
        {formlabel for="syntaxHighlighter" __text="Syntax highlighting"}
        {formdropdownlist id="syntaxHighlighter" items=$syntaxHighlighters}
    </div>

    <div class="z-formrow">
        {formlabel for="imageViewer" __text="Javascript pop-ups for images"}
        {formcheckbox id='imageViewer'}
    </div>

</fieldset>

<div class="z-formbuttons z-buttons">
    {formbutton class="z-bt-ok" commandName="save" __text="Save"}
    {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
</div>

{/form}

{modgetinfo info=all}
<p class="z-center"><a href="http://code.zikula.org/LuMicuLa/" title="{gt text="Visit the LuMicuLa project site"}">{$modinfo.displayname} {$modinfo.version}</a></p>
</div>