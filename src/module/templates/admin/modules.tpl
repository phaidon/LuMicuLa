{ajaxheader ui=true}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Modules preferences"}</h3>
</div>


<p style="margin: 1em 0;">
    <a class="z-icon-es-new" href="{modurl modname=LuMicuLa type=admin func=modify}">{gt text='Add module'}</a>
</p>

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
                {foreach from=$mod.elements item='element'}
                {img modname='LuMicuLa' src=$element.icon title=$element.title|safetext}
                {/foreach}
            </td>
            <td class="z-right z-nowrap">
                <a href="{modurl modname=LuMicuLa func=modify type=admin id=$mod.modname}">{img modname='core' set='icons/extrasmall' src='xedit.png' alt='Edit'}</a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

{adminfooter}