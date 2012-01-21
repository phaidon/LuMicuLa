{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Modify"}</h3>
</div>

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
    <legend>{gt text='Elements'}</legend>
    <table class="z-datatable">
        {assign var='i' value=0}
        {foreach from=$elements item='element' key='key'}
        {if $i == 0}
        <tr class="{cycle values="z-odd,z-even"}">
            {/if}
            <td>{formcheckbox id="$key"}</td>
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
            <td>{formcheckbox id='smilies'}</td>
            <td>{img modname='LuMicuLa' src='smiley.png' __title='Smilies'}</td>
            <td>{gt text='Smilies'}</td>
        </tr>
    </table>
    <a href="javascript:void(0);" id="lml_select_all">{gt text="Check all"}</a> / <a href="javascript:void(0);" id="lml_deselect_all">{gt text="Uncheck all"}</a>
</fieldset>

<div class="z-formbuttons z-buttons">
    {formbutton class="z-bt-ok" commandName="save" __text="Save"}
    {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
</div>

{/form}

{adminfooter}

<script type="text/javascript">
    $('lml_select_all').observe('click', function(e){
        Zikula.toggleInput('.z-form-checkbox', true);
        e.stop();
    });
    $('lml_deselect_all').observe('click', function(e){
        Zikula.toggleInput('.z-form-checkbox', false);
        e.stop();
    });
</script>