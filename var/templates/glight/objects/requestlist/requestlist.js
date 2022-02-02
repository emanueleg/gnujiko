/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2014 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 11-01-2014
 #PACKAGE: glight-template
 #DESCRIPTION: GLight - Request List
 #VERSION: 2.0beta
 #CHANGELOG: 
 #TODO:
 
*/

var ACTIVE_GLREQLIST = null;

function GLRequestList(tb)
{
 this.TB = tb;
 this.TB.hinst = this;

 Template.initCollapseTable(tb);

 Template.initPopupMenu(document.getElementById(this.TB.id+"-footmenu"));
 Template.initPopupMessage(document.getElementById(this.TB.id+"-details"));
 Template.initEd(document.getElementById(this.TB.id+"-cat"), "catfind");
 Template.initBtn(document.getElementById(this.TB.id+"-btnselcat"), "catselect");
 Template.initEd(document.getElementById(this.TB.id+"-ctime"), "date");
 Template.initEd(document.getElementById(this.TB.id+"-mtime"), "date");

 ACTIVE_GLREQLIST = this;
}

GLRequestList.prototype.addNewRequest = function()
{
 var tb = this.TB;
 var r = tb.insertRow(tb.rows.length-1);
 r.className = "expanded";
 r.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"GLightDemo/img/task.png'/"+">";
 r.insertCell(-1).innerHTML = "<b>Digita un titolo</b>";
 r.insertCell(-1).innerHTML = "<span class='status-orange'>da analizzare</span>";
 r.insertCell(-1).innerHTML = "11.01.2013";
 r.insertCell(-1).innerHTML = "<img src='"+ABSOLUTE_URL+"GLightDemo/img/focus-off.png'/"+">";

 r.cells[0].className = "icon";
 r.cells[1].className = "title";
 r.cells[2].className = "status";
 r.cells[3].className = "time";
 r.cells[4].className = "icon";

 var rC = tb.insertRow(tb.rows.length-1);
 rC.className = "container";
 var cell = rC.insertCell(-1);
 cell.colSpan=5;
 
 var html = "";
 html+= "<span class='minioption' onclick='ACTIVE_GLREQLIST.showRequestDetails(this)'>dettagli <img src='"+ABSOLUTE_URL+"GLightDemo/img/options.png'/"+"></span>";
 html+= "<div class='contents'><textarea class='textarea' style='width:100%;height:200px'></textarea></div>";
 html+= "<div class='footer'>";
 html+= "<input type='button' class='button-blue' style='float:left' value='Salva'/"+">";
 html+= "<ul class='iconsmenu' style='float:left;margin-top:2px'>";
 html+= "<li><img src='"+ABSOLUTE_URL+"GLightDemo/img/type.png'/"+"></li>";
 html+= "<li class='separator'></li>";
 html+= "<li><img src='"+ABSOLUTE_URL+"GLightDemo/img/upload.png'/"+"></li>";
 html+= "</ul>";
 html+= "<ul class='iconsmenu' style='float:right;margin-top:2px'>";
 html+= "<li><img src='"+ABSOLUTE_URL+"GLightDemo/img/trash.png' title='Elimina'/"+"></li>";
 html+= "<li class='separator'></li>";
 html+= "<li><img src='"+ABSOLUTE_URL+"GLightDemo/img/dnarrow.png' title='Opzioni' onclick='ACTIVE_GLREQLIST.showMenuOpt(this)'/"+"></li>";
 html+= "</ul>";
 html+= "</div>";

 cell.innerHTML = html;

 Template.initCollapseTableRow(r,tb);
 r.expand();
}
//-------------------------------------------------------------------------------------------------------------------//
GLRequestList.prototype.showRequestDetails = function(btn)
{
 var details = document.getElementById(this.TB.id+"-details");
 details.show(btn,36);
}
//-------------------------------------------------------------------------------------------------------------------//
GLRequestList.prototype.showMenuOpt = function(btn)
{
 var menu = document.getElementById(this.TB.id+"-footmenu");
 menu.show(btn);
}
//-------------------------------------------------------------------------------------------------------------------//

