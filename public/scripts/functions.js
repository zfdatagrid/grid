
function confirmDel(msg, url)
{

    if(confirm(msg))
    {
        window.location = url;
    }else{
        return false;
    }
}


function extractScript(texto){
    var ini = 0;
    while (ini!=-1){
        ini = texto.indexOf('<script', ini);
        if (ini >=0){
            ini = texto.indexOf('>', ini) + 1;
            var fim = texto.indexOf('</script>', ini);
            codigo = texto.substring(ini,fim);
            novo = document.createElement("script")
            novo.text = codigo;
            document.body.appendChild(novo);
        }
    }
}

/**

*/
function openAjax(ponto,url) {

    var xmlhttp;
    try
    {
        xmlhttp=new XMLHttpRequest();
    }
    catch (e)
    {
        try
        {
            xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e)
        {
            try
            {
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e)
            {
                alert("Your browser does not suppor AJAX!");
                return false;
            }
        }
    }
    xmlhttp.open("GET", URL_HOST+url,true);

    xmlhttp.onreadystatechange=function () {

        if (xmlhttp.readyState==4) {
            texto=xmlhttp.responseText;
            document.getElementById(ponto).innerHTML=texto;
            extractScript(texto);
        }else{

        }
    }
    xmlhttp.send(null);
}




function changeFilters(fields,url,usaAjax)
{
    var usaAjax = "2";
    var fieldsArray = fields.split(",");
    var filtro = new Array;

    for (var i = 0; i < fieldsArray.length -1; i++)
    {
        filtro[i] = '"'+fieldsArray[i]+'":"'+document.getElementById(fieldsArray[i]).value+'"';
    }

    filtro = "{"+filtro+"}";

    if(usaAjax=="1")
    {
        openAjax('grid',url+'/filters/'+filtro);
    }else{
        window.location=url+'/filters/'+filtro;
    }

}

/**
Derivated from phpmysqladmin
*/
function setpointer(theRow,id, theMarkColor)
{

    var tabela=null;
    var currentColor = null;
    var newColor= null;
    var domDetect=null;
    var theRowNum = null;
    var theDefaultColor = null;
    var thePointerColor = null;

    theRowNum = id;


    if (typeof(document.getElementsByTagName) != 'undefined') {
        tables = document.getElementsByTagName('table');
    }
    else if (typeof(tabela.cells) != 'undefined') {
        tables = document.tables;
    }
    else {
        return false;
    }

    for (var t = 0; t < tables.length; t++) {
        if (tables[t].getAttribute('name') == 'listagem') {
            oRows = tables[t].rows;
            var   len = oRows.length;
            for (i=2; i<oRows.length; i++) {
                var theCells = null;

                // 2. Gets the current row and exits if the browser can't get it
                if (typeof(document.getElementsByTagName) != 'undefined') {
                    theCells = oRows[i].getElementsByTagName('td');
                }
                else if (typeof(oRows[i].cells) != 'undefined') {
                    theCells = oRows[i].cells;
                }
                else {
                    return false;
                }

                var rowCellsCnt  = theCells.length;
                if (typeof(window.opera) == 'undefined'
                && typeof(theCells[0].getAttribute) != 'undefined') {
                    if (t==theRowNum+1) currentColor = theCells[0].getAttribute('bgcolor');
                    domDetect    = true;
                }
                // 3.2 ... with other browsers
                else {
                    currentColor = theCells[0].style.backgroundColor;
                    domDetect    = false;
                } // end 3

                if (t==theRowNum+1)
                {

                }

                var rowCellsCnt=theCells.length;

                var c=null;
                if (domDetect) {
                    for (c = 0; c < rowCellsCnt; c++) {
                        theCells[c].style.backgroundColor =  ((i!=theRowNum+1)?((i%2)? theDefaultColor: thePointerColor): theMarkColor);
                    } // end for
                }
                // 5.2 ... with other browsers
                else {
                    for (c = 0; c < rowCellsCnt; c++) {
                        theCells[c].style.backgroundColor = '';
                    }
                }

            }
        }
    } 

    return true;

}



function checkCheckbox(iNum) {
    var oEl = document.getElementById(iNum);
    oEl.checked = true;
    document.getElementById('inputId').value=oEl.value;

    alert('Select record with id '+oEl.value);
}
