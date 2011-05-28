{pageaddvar name='javascript' value='javascript/helpers/Zikula.UI.js'}
{include file="admin/header.tpl"}
<div class="z-adminpageicon">{icon type="view" size="large"}</div>
<h2>{gt text='Modules preferences'}</h2>


<p><a style="margin:1em 0;" class="z-icon-es-new" href="{modurl modname=LuMicuLa type=admin func=modify}">
{gt text="Add module"}
</a></p>

<table class="z-admintable">
    <thead>
    <tr>
        <th>Modules</th>
        <th>Language</th>
        <th>Elements</th>
        <th>Actions</th>
    </tr>
    <thead>
    <tbody>
{foreach from=$mods item="mod"}
    <tr class="{cycle values="z-odd,z-even"}">
        <td>{$mod.modname}</td>
        <td>{$mod.language}</td>
        <td width=100%>
            {foreach from=$mod.elements item='element'}
            {img modname='LuMicuLa' src=$element.icon title=$element.title}
            {/foreach}
        </td>
        <td>
            {remove id=$mod.modname}
            <a href="{modurl modname=LuMicuLa func=modify type=admin id=$mod.modname}">
                {img modname=core set=icons/extrasmall src=xedit.png alt="Edit"}
            </a>
        </td>
    </tr>
{/foreach}
    </tbody>
</table>

{include file="admin/footer.tpl"}