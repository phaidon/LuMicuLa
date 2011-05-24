{pageaddvar name='javascript' value='javascript/helpers/Zikula.UI.js'}
{include file="admin/header.tpl"}
<div class="z-adminpageicon">{icon type="view" size="large"}</div>
<h2>{gt text='Settings'}</h2>


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
            {if array_key_exists('bold', $mod) and $mod.bold}
            {img modname='LuMicuLa' src='bold.png' __title='Bold text'}
            {/if}

            {if array_key_exists('italic', $mod) and $mod.italic}
            {img modname='LuMicuLa' src='italic.png' __title='Italic'}
            {/if}

            {if array_key_exists('underline', $mod) and $mod.underline}
            {img modname='LuMicuLa' src='underline.png' __title='Underline'}
            {/if}

            {if array_key_exists('strikethrough', $mod) and $mod.strikethrough}
            {img modname='LuMicuLa' src='strikethrough.png' __title='Strikethrough'}
            {/if}

            {if array_key_exists('mark', $mod) and $mod.mark}
            {img modname='LuMicuLa' src='mark.png' __title='Mark'}
            {/if}

            {if array_key_exists('code', $mod) and $mod.code}
            {img modname='LuMicuLa' src='code.png' __title='Code'}
            {/if}

            {if array_key_exists('smilies', $mod) and $mod.smilies}
            {img modname='LuMicuLa' src='smiley.png' __title='Smilies'}
            {/if}

            {if array_key_exists('headings', $mod) and $mod.headings}
            {img modname='LuMicuLa' src='headings.png' __title='Headings'}
            {/if}

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