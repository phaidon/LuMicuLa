{pageaddvar name="stylesheet" value="modules/LuMicuLa/style/transform.css"}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Supported tags"}</h3>
</div>

<table class="z-datatable">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th>{gt text='Tag'}</th>
            <th>{gt text='LML'}</th>
            <th>&nbsp;</th>
            <th>{gt text='Html'}</th>
            <th>{gt text='Preview'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$tags item="tag"}
        <tr class="{cycle values="z-odd,z-even"}">
            <td>{img modname='LuMicuLa' src=$tag.icon title=$tag.title|safetext}</td>
            <td class="z-nowrap">{$tag.title|safetext}</td>
            <td class="z-nowrap">
                {foreach from=$tag.lmls item='lml' key='key'}
                {$key|safehtml}<br />
                {/foreach}
            </td>
            <td>
                {foreach from=$tag.lmls item='lml'}
                {$lml|safehtml}<br />
                {/foreach}
            </td>
            <td>{$tag.html|safehtml}</td>
            <td class="z-nowrap">{$tag.preview|safehtml}</td>
        </tr>
        {/foreach}
    </tbody>
</table>

{adminfooter}