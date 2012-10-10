{ajaxheader ui=true}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Modules preferences"}</h3>
</div>

<form class="z-form z-linear" style="margin-bottom:10px;" action="{modurl modname="LuMicuLa" type="admin" func="modify2"}" method="post">
    <div class="z-buttons">
        <select name="id">
            {foreach from=$modules item="module" key="modname"}
            <option value="{$modname}">{$module.displayname}</option>
            {/foreach}
        </select>
        {button src=button_ok.png set=icons/extrasmall __alt="Add module" __title="Add module" __text='Add module'}
    </div>
</form>


<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text='Modules'}</th>
            <th>{gt text='Language'}</th>
            <th>{gt text='Elements'}</th>
            <th class="z-right z-nowrap">{gt text='Actions'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$mods item="mod"}
        <tr class="{cycle values="z-odd,z-even"}">
            <td>{$mod.modname|safetext}</td>
            <td>{$mod.language|safetext}</td>
            <td>
                {foreach from=$mod.elements key='element' item='active'}
                    {if $active}
                        {img modname='LuMicuLa' src=$elements.$element.icon title=$elements.$element.title|safetext}
                    {/if}
                {/foreach}
            </td>
            <td class="z-right" width=40>
                <a href="{modurl modname='LuMicuLa' func='deleteModuleSettings' type='admin' id=$mod.modname}">{img modname='core' set='icons/extrasmall' src='14_layer_deletelayer.png' __alt='Remove' __title='Remove'}</a>
                <a href="{modurl modname='LuMicuLa' func='modify' type='admin' id=$mod.modname}">{img modname='core' set='icons/extrasmall' src='xedit.png' __alt='Edit' __title='Edit'}</a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

{adminfooter}