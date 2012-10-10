function LuMicuLa(textfield, editorOptions, smilies, quote) {


    if (textfield == null) {
        return;
    }

    var textfieldname = textfield.id;

    // container
    var container = document.createElement('div');
    container.style.marginBottom = "-5px";

    // main bar
    var bar = document.createElement('div');
    bar.className = "lumicula_editor_bar";
    for (var key in editorOptions) {
        var obj= editorOptions[key];
        if (obj['icon'] != null) {
            if (obj['subitems'] != null) {
                var select = document.createElement('select');
                select.style.height = "22px";
                select.style.marginTop = "-15px";
                select.style.verticalAlign = "middle";
                var onchange ="insertAtCursor2("+textfieldname+", this.value);this.selectedIndex=0;return false;";
                select.setAttribute('onchange', onchange);
                select.options[select.length] = new Option('Heading', 0);
                for (var i in obj['subitems']) {
                    var subobj = obj['subitems'][i];
                    select.options[select.length] = new Option(subobj['title'], subobj['begin']+','+subobj['inner']+','+subobj['end']);
                }
                bar.appendChild(select);
            } else {
                var img = document.createElement('img')
                console.log('Zikula.Config.baseURL');
                img.setAttribute('src', Zikula.Config.baseURL+'modules/LuMicuLa/images/'+obj['icon']);
                var onclick ="insertAtCursor("+textfieldname+", '"+obj['begin']+"', '"+obj['inner']+"', '"+obj['end']+"');return false";
                img.setAttribute('onclick', onclick);
                bar.appendChild(img);

                if (obj['shortcut'] != null) {
                    new HotKey(obj['shortcut'], function(event){
                        insertAtCursor(textfieldname, obj['begin'], obj['inner'], obj['end']);
                    });
                }
            }
        }
    }
    container.appendChild(bar);

    // smilies
    if (smilies != '') {
        var img = document.createElement('img');
        img.setAttribute('src', Zikula.Config.baseURL+'modules/LuMicuLa/images/smiley.png');
        img.setAttribute('onclick', 'toggle_smileybox()');
        bar.appendChild(img);

        var smiley = document.createElement('div');
        smiley.id = 'lumicula_smiley_bar';
        smiley.className = 'lumicula_smiley_bar';
        smiley.style.display = 'none';
        for (var key in smilies) {
            var obj = smilies[key];
            var src = Zikula.Config.baseURL+'modules/LuMicuLa/images/smilies/'+obj;
            var onclick ="insertAtCursor('body', '"+key+"', '', '');return false";
            smiley.innerHTML += '<img src="'+src+'" onclick="'+onclick+'"> ';
        }
        container.appendChild(smiley);
    }

    // quote
    if (quote != 0) {
        var img = document.createElement('img');
        img.setAttribute('src', Zikula.Config.baseURL+'modules/LuMicuLa/images/quote.png');
        img.setAttribute('onclick', "insertAtCursor('"+textfieldname+"', '[quote]', '', '[/quote]');return false;");
        bar.appendChild(img);
    }

    var width = $(textfieldname).getWidth()+10;
    container.style.width = width+'px';

    var parentDiv = textfield.parentNode;
    parentDiv.insertBefore(container, textfield);

    return true;
}


function insertAtCursor(textfieldname, startTag, middleTag, endTag) {

    var textfield = $(textfieldname);

    //IE support
    textfield.focus();
    /* für Internet Explorer */
    if(typeof document.selection != 'undefined') {
        /* Einfügen des Formatierungscodes */
        var range = document.selection.createRange();
        var insText = range.text;
        range.text = startTag + insText + endTag;
        /* Anpassen der Cursorposition */
        range = document.selection.createRange();
        if (insText.length === 0) {
            range.move('character', -endTag.length);
        } else {
            range.moveStart('character', startTag.length + insText.length + endTag.length);
        }
        range.select();
    }
    // Gecko based browsers
    else if (textfield.selectionStart || textfield.selectionStart == '0') {
        var startPos = textfield.selectionStart;
        var endPos   = textfield.selectionEnd;
        if(endPos-startPos !== 0) {
            middleTag = textfield.value.substring(startPos, endPos);
        }
        myValue = startTag + middleTag + endTag;


        textfield.value = textfield.value.substring(0, startPos) + myValue + textfield.value.substring(endPos, textfield.value.length);

        if(endPos-startPos === 0) {
            textfield.selectionStart = startPos+startTag.length;
            textfield.selectionEnd   = startPos+startTag.length+middleTag.length;
        } else {
            textfield.selectionStart = endPos+startTag.length+endTag.length;
            textfield.selectionEnd   = endPos+startTag.length+endTag.length;
        }
    } else {
        myValue = startTag + middleTag + endTag;
        textfield.value += myValue;
    }
}

function insertAtCursor2(textfieldname, tag) {
    var tags = tag.split(",");
    insertAtCursor(textfieldname, tags[0], tags[1], tags[2]);
}
