/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2012 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 21-01-2012
 #PACKAGE: gserppagenav
 #DESCRIPTION: Gnujiko SERP page navigator
 #VERSION: 2.0
 #CHANGELOG:
 #TODO:
 
*/

function GSERPPageNav(_nItems, _rpp, _currentPage, _maxElms)
{
 this.autoupdate = true;
 this.ItemsCount = _nItems;
 this.ResultsPerPage = _rpp;
 this.MaxElms = _maxElms ? _maxElms : 10;

 this.O = document.createElement('DIV');
 this.O.className = 'GSERPPageNav';

 this.CurrentPage = _currentPage ? _currentPage : 0;

 this.init(this.CurrentPage);

 //--- EVENTS ---//
 this.OnChange = null;
}

GSERPPageNav.prototype.init = function(_currentPage)
{
 var oThis = this;
 this.O.innerHTML = "";
 var pages = Math.ceil(this.ItemsCount/this.ResultsPerPage);
 this.CurrentPage = _currentPage ? _currentPage : 0;
 if(pages < 2)
  return;
 
 var e,s;
 if(this.CurrentPage == 0) {s=0; e = (pages < this.MaxElms ? pages : this.MaxElms);}
 else
 {
  if(this.CurrentPage > Math.floor(this.MaxElms/2))
  {
   if((this.CurrentPage + Math.floor(this.MaxElms/2)) > pages)
   {
    e = pages;
	s = pages-this.MaxElms;
   }
   else
   {
    s = this.CurrentPage - Math.floor(this.MaxElms/2);
    e = s+this.MaxElms;
   }
  }
  else
  {
   s = 0;
   e = pages < this.MaxElms ? pages : this.MaxElms;
  }
 }

 // BACK BUTTON //
 if(this.CurrentPage > 0)
 {
  this.PrevBtn = document.createElement('A');
  this.PrevBtn.href = "#";
  this.PrevBtn.idx = this.CurrentPage-1;
  this.PrevBtn.onclick = function(){oThis._pageOnSelect(this);}
 }
 else
  this.PrevBtn = document.createElement('SPAN');
 this.PrevBtn.className='nextprev';
 this.PrevBtn.innerHTML = "&laquo;";
 this.O.appendChild(this.PrevBtn);

 // PAGE BUTTONS //
 this.Blocks = new Array;
 if(s < 0)
  s = 0;
 for(var c=s; c < e; c++)
 {
  var d = document.createElement('SPAN');
  d.innerHTML = c+1;
  d.idx = c;
  d.onclick = function(){oThis._pageOnSelect(this);};
  this.O.appendChild(d);
  this.Blocks.push(d);
  if(c == this.CurrentPage)
   d.className = 'current';
 }

 // FORWARD BUTTON //
 if(this.CurrentPage < (pages-1))
 {
  this.NextBtn = document.createElement('A');
  this.NextBtn.href = "#";
  this.NextBtn.idx = this.CurrentPage+1;
  this.NextBtn.onclick = function(){oThis._pageOnSelect(this);}
 }
 else
  this.NextBtn = document.createElement('SPAN');
 this.NextBtn.className='nextprev';
 this.NextBtn.innerHTML = "&raquo;";
 this.O.appendChild(this.NextBtn);
}

GSERPPageNav.prototype._pageOnSelect = function(b)
{
 this.CurrentPage = b.idx;
 if(this.autoupdate)
  this.Update(this.ItemsCount,this.ResultsPerPage,b.idx,this.MaxElms);
 if(this.OnChange)
  this.OnChange(this.CurrentPage, this.CurrentPage*this.ResultsPerPage, this.ResultsPerPage, this.ItemsCount);
}

GSERPPageNav.prototype.Update = function(_nItems, _rpp, _currentPage, _maxElms)
{
 this.ItemsCount = _nItems;
 this.ResultsPerPage = _rpp;
 this.MaxElms = _maxElms ? _maxElms : 10;
 this.init(_currentPage ? parseFloat(_currentPage) : 0);
}

