{pageaddvar name='javascript' value='jquery'}
{pageaddvar name="javascript" value="modules/LuMicuLa/javascript/SCEditor/jquery.sceditor.min.js"}
{pageaddvar name="stylesheet" value="modules/LuMicuLa/javascript/SCEditor/jquery.sceditor.default.min.css"}
{pageaddvar name="stylesheet" value="modules/LuMicuLa/javascript/SCEditor/themes/default.min.css"}


<script>
    jQuery(document).ready(function() {
        var initEditor = function() {
            jQuery("#{{$textfieldname}}").sceditorBBCodePlugin({
                style: Zikula.Config.baseURL+"modules/LuMicuLa/javascript/SCEditor/jquery.sceditor.default.min.css",
                emoticonsRoot: Zikula.Config.baseURL+"modules/LuMicuLa/javascript/SCEditor/"
            });
        };

        initEditor();
    });
</script>