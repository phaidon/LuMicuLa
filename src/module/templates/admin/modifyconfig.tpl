{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

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

{adminfooter}