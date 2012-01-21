Event.observe(window, 'load', textarea_width_correction, false);  

function textarea_width_correction() {
    var textfieldname = $('lumicula_textfieldname').getValue();
    var ww = $(textfieldname).getWidth() - 10;
    var vv = ww - 2;
    $('lumicula_editor_bar').setStyle({
        width:ww + 'px'
    });
    $('lumicula_smiley_bar').setStyle({
        width:ww + 'px'
    });
    $(textfieldname).addClassName('lumicula');
    $(textfieldname).setStyle({
        width:vv + 'px',
        padding:'5px'
    });

}


function insertAtCursor2(textfieldname, tag) {
    var tags = tag.split(",");
    insertAtCursor(textfieldname, tags[0], tags[1], tags[2]);
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