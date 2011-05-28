{pageaddvar name='javascript' value='javascript/helpers/Zikula.UI.js'}
{include file="admin/header.tpl"}
<div class="z-adminpageicon">{icon type="view" size="large"}</div>
<h2>{gt text='Supported tags'}</h2>

<table class="z-admintable">
    <thead>
    <tr>
        <th colspan='2'>Tag</th>
        <th colspan='2'>LML</th>
        <th>Html</th>
        <th>{gt text='Preview'}</th>
    </tr>
    <thead>
    <tbody>
{foreach from=$tags item="tag"}
    <tr class="{cycle values="z-odd,z-even"}">
        <td>{img modname='LuMicuLa' src=$tag.icon title=$tag.title}</td>
        <td nowrap>{$tag.title}</td>
        <td nowrap>
            {foreach from=$tag.lmls item='lml' key='key'}
            {$key}<br />
            {/foreach}
        </td>
        <td nowrap>
            {foreach from=$tag.lmls item='lml'}
            {$lml}<br />
            {/foreach}
        </td>
        <td>{$tag.html}</td>
        <td nowrap>{$tag.preview}</td>
    </tr>
{/foreach}
    </tbody>
</table>

{include file="admin/footer.tpl"}