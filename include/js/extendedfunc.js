/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 HackTVT Project
 copyright(C) 2016 Alpatech mediaware - www.alpatech.it
 license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 Gnujiko 10.1 is free software released under GNU/GPL license
 developed by D. L. Alessandro (alessandro@alpatech.it)
 
 #DATE: 05-03-2016
 #PACKAGE: gnujiko-base
 #DESCRIPTION: Extended JavaScript functions
 #VERSION: 2.17beta
 #CHANGELOG: 05-03-2016 : Aggiunta funzione br2nl
			 28-12-2015 : Aggiunta funzione array_to_xml.
			 03-10-2015 : Aggiunto accento sx su funzione E_QUOT.
			 27-01-2015 : Aggiunta funzione nl2br.
			 17-12-2014 : Aggiunta funzione xml_purify.
			 16-04-2014 : Aggiunto F su date.printf (fulltext month)
			 26-02-2014 : Aggiunta funzione nl2br
			 14-02-2014 : Aggiunta funzione roundup che arrotonda alla cifra decimale desiderata.
			 10-02-2014 : Aggiunta funzione striptags
			 24-12-2013 : Aggiunto separatore (.) punto sulle date, su funzione strdatetime_to_iso.
			 19-12-2013 : Modificata funzione implode.
			 20-10-2013 : Ho messo il linguaggio italiano su funzione printf. Da fare multi-linguaggio.
			 18-06-2013 : Aggiunto funzione Math.arctan (restituisce l'arcotangente in gradi anzichè radianti)
			 23-04-2013 : Aggiunto millisecondi alla funzione printf.
			 25-01-2013 : Bug fix in setFromISO.
			 09-07-2012 : Aggiunto funzioni PrevMonth e NextMonth su oggetto Date.
			 11-04-2012 : Aggiunta funzione parseTime().
			 01-04-2012 : Aggiunta funzione is_numeric.
			 21-02-2012 : Aggiunta funzione parseCurrency.
			 16-01-2012 Rimosso funzione i18n che andava in conflitto su alcune applicazioni.
			 27-07-2010 Aggiunte 2 funzioni sugli array
			 05-02-2011 Bug fix into html_entity_decode added some special characters
						Added new function real_htmlspecialchars
 #TODO: Ricreare funzione i18n che dia la possibilità di caricare i file di lingua da javascript.
 #TODO: Fare il multilingua nella funzione date.printf
 
*/

/* DATE */
Date.prototype.PrevDate = function(a, _retNew)
{
 var ret = _retNew ? new Date(this.getTime()) : this;
 if (a != null)
  ret.setDate(this.getDate()-a);
 else
  ret.setDate(this.getDate()-1);
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
Date.prototype.NextDate = function(a, _retNew)
{
 var ret = _retNew ? new Date(this.getTime()) : this;
 if (a != null)
  ret.setDate(this.getDate()+a);
 else
  ret.setDate(this.getDate()+1);
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
Date.prototype.Midnight = function()
{
 this.setHours(0); this.setMinutes(0); this.setSeconds(0); this.setMilliseconds(0);
}
//-------------------------------------------------------------------------------------------------------------------//
Date.prototype.PrevMonth = function(num)
{
 if(!num) num = 1;
 if((this.getMonth()-num) < 0)
 {
  this.setFullYear(this.getFullYear()-1);
  this.setMonth(12-this.getMonth()-num);
 }
 else
  this.setMonth(this.getMonth()-num);
}
//-------------------------------------------------------------------------------------------------------------------//
Date.prototype.NextMonth = function(num)
{
 if(!num) num = 1;
 if((this.getMonth()+num) > 11)
 {
  this.setFullYear(this.getFullYear()+1);
  this.setMonth(num - (12-this.getMonth()));
 }
 else
  this.setMonth(this.getMonth()+num);
}
//-------------------------------------------------------------------------------------------------------------------//
Date.prototype.printf = function(fmt) //--- print a formatted date time ---//
{
 var ret = "";
 /* TODO: multilanguage */

 /* ENGLISH */
 //var days = new Array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
 //var months = new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

 /* ITALIAN */
 var days = new Array('Dom','Lun','Mar','Mer','Gio','Ven','Sab');
 var months = new Array('Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic');
 var fullmonths = new Array('Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');

 for(var c=0; c < fmt.length; c++)
 {
  switch(fmt.charAt(c))
  {
   case 'd' : { //--- Day of the month, 2 digits with leading zeros (01 to 31) ---//
		 if(parseInt(this.getDate()) < 10)
		  ret+= "0";
		 ret+=this.getDate();
		};break;
   case 'D' : { //--- A textual representation of a day, three letters (Mon through Sun) ---//
		 ret+= days[this.getDay()];
		};break;
   case 'm' : { //--- Numeric representation of a month, with leading zeros (01 through 12) ---//
		 if( (parseInt(this.getMonth())+1) < 10)
		  ret+= "0";
		 ret+= (parseInt(this.getMonth())+1);
		};break;
   case 'M' : { //--- A textual representation of a month, three letters (Jan to Dec) ---//
		 ret+= months[this.getMonth()];
		};break;
   case 'F' : { //--- A textual representation of a month, fulltext (January to December) ---//
		 ret+= fullmonths[this.getMonth()];
		};break;
   case 'y' : { //--- A two digit representation of a year (Examples: 99 or 03) ---//
		 ret+= this.getYear();
		};break;
   case 'Y' : { //--- A full numeric representation of a year, 4 digits (Examples: 1999 or 2003) ---//
		 ret+= this.getFullYear();
		};break;
   case 'H' : { //--- 24-hour format of an hour with leading zeros (00 through 23) ---//
		 if(parseInt(this.getHours()) < 10)
		  ret+= "0";
		 ret+= this.getHours();
		};break;
   case 'i' : { //--- Minutes with leading zeros (00 to 59) ---//
		 if(parseInt(this.getMinutes()) < 10)
		  ret+= "0";
		 ret+= this.getMinutes();
		};break;
   case 's' : { //--- Seconds with leading zeros (00 to 59) ---//
		 if(parseInt(this.getSeconds()) < 10)
		  ret+= "0";
		 ret+= this.getSeconds();
		};break;
   case 'u' : { //--- Milliseconds ---//
		 ret+= this.getMilliseconds();
		};break;
   default: ret+= fmt.charAt(c);
  }
 }
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
Date.prototype.setFromISO = function(isoStr)
{
 if(!isoStr)
  return false;
 var date = null;
 var time = null;
 var x = isoStr.split(" ");
 if(x[1])
 {
  date = x[0];
  time = x[1];
 }
 else
  date = isoStr;
 // set date //
 if(date)
 {
  var dx = date.split("-");
  this.setTime(Date.parse(dx[1]+","+dx[2]+","+dx[0]));
 }
 // set time //
 if(time)
 {
  var tx = time.split(":");
  this.setHours(tx[0]);
  this.setMinutes(tx[1]);
  if(tx[2])
   this.setSeconds(tx[2]);
 }
 return this;
}
//-------------------------------------------------------------------------------------------------------------------//
function timelength_to_str(seconds)
{
 if(!seconds)
  return "0:00";
 var h = Math.floor(seconds/3600);
 var m = Math.floor((seconds-(h*3600))/60);
 return h+":"+(m<10 ? "0"+m : m);
}
//-------------------------------------------------------------------------------------------------------------------//
function parse_timelength(tl)
{
 if(!tl)
  return 0;

 if(tl.indexOf(":")<0)
  tl = tl+":00";

 var tl = tl.split(":");
 if(!isNaN(parseFloat(tl[0])) && !isNaN(parseFloat(tl[1])))
  return ((parseFloat(tl[0])*60)+parseFloat(tl[1]))*60; // return seconds
 return 0;
}
//-------------------------------------------------------------------------------------------------------------------//
/* STRINGS */
String.prototype.E_QUOT = function()
{
 var t = this.replace(/\'/g,'&rsquo;');
 t = t.replace(/‘/g,'&lsquo;');
 t = t.replace(/’/g,'&rsquo;');
 t = t.replace(/`/g,'&lsquo;');
 return (t = t.replace(/\"/g,'&quot;'));
}
//-------------------------------------------------------------------------------------------------------------------//
String.prototype.ltrim = function()
{
 for(var c=0; c < this.length; c++)
 {
  if(this.charAt(c) != " ")
   return this.slice(c);
 }
 return "";
}
//-------------------------------------------------------------------------------------------------------------------//
String.prototype.rtrim = function()
{
 for(var c=(this.length-1); c > -1; c--)
 {
  if(this.charAt(c) != " ")
   return this.slice(0,c+1);
 }
 return "";
}
//-------------------------------------------------------------------------------------------------------------------//
String.prototype.trim = function(removeChar)
{
 var t = this.ltrim();
 if(removeChar)
 {
  t = t.replace(/\'/, "");
  t = t.replace(/\"/, "");
 }
 return t.rtrim();
}
//-------------------------------------------------------------------------------------------------------------------//
String.prototype.ucfirst = function()
{
 if(this.length == 0)
  return this;
 var t = this.charAt(0);
 t = t.toUpperCase();
 return t+this.substr(1);
}
//-------------------------------------------------------------------------------------------------------------------//
String.prototype.striptags = function(bool)
{
 var ret = this;
 if(bool)
  ret = ret.replace(/<br\/>/ig,"\n");
 return ret.replace(/(<([^>]+)>)/ig,"");
}
//-------------------------------------------------------------------------------------------------------------------//
String.prototype.nl2br = function(breakTag)
{
 var ret = this;
 if(!breakTag) breakTag = "<br/>";
 return ret.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
//-------------------------------------------------------------------------------------------------------------------//

/* ETC */
//-------------------------------------------------------------------------------------------------------------------//
Array.prototype.indexOf = function(obj)
{
 for(var c=0; c < this.length; c++)
 {
  if(this[c] == obj)
   return c;
 } 
 return -1;
}
//-------------------------------------------------------------------------------------------------------------------//
function array_keys(arr)
{
 var ret = new Array();
 for(k in arr)
  ret.push(k);
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function array_key_get(arr,idx)
{
 var k = array_keys(arr);
 return k[idx];
}
//-------------------------------------------------------------------------------------------------------------------//
function array_to_xml(arr, root)
{
 if(!root)
  var root = "xml";
 if((typeof(root) == "number") || isFinite(root))
  var root = "item";
 
 var ret = "<"+root;
 var keys = array_keys(arr);
 var sub = new Array();
 for(var c=0; c < keys.length; c++)
 {
  var key = keys[c];
  var value = arr[key];
  switch(typeof(value))
  {
   case 'number' : ret+= " "+key+"=\""+value+"\""; break;
   case 'string' : ret+= " "+key+"=\""+xml_purify(value)+"\""; break;
   case 'boolean' : ret+= " "+key+"=\""+(value ? '1' : '0')+"\""; break;
   case 'object' : sub.push(key); break;
  }
 }
 
 if(sub.length)
 {
  ret+= ">";
  for(var c=0; c < sub.length; c++)
  {
   var key = sub[c];
   ret+= array_to_xml(arr[key], key);
  }
  ret+= "</"+root+">";
 }
 else
  ret+= "/>";

 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function get_html_translation_table (table, quote_style) 
{
    // Returns the internal translation table used by htmlspecialchars and htmlentities  
    // 
    // version: 1004.2314
    // discuss at: http://phpjs.org/functions/get_html_translation_table    // +   original by: Philip Peterson
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: noname
    // +   bugfixed by: Alex
    // +   bugfixed by: Marco    // +   bugfixed by: madipta
    // +   improved by: KELAN
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Frank Forte    // +   bugfixed by: T.Wild
    // +      input by: Ratheous
    // %          note: It has been decided that we're not going to add global
    // %          note: dependencies to php.js, meaning the constants are not
    // %          note: real constants, but strings instead. Integers are also supported if someone    // %          note: chooses to create the constants themselves.
    // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
    // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
    
    var entities = {}, hash_map = {}, decimal = 0, symbol = '';    var constMappingTable = {}, constMappingQuoteStyle = {};
    var useTable = {}, useQuoteStyle = {};
    
    // Translate arguments
    constMappingTable[0]      = 'HTML_SPECIALCHARS';    constMappingTable[1]      = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';
     useTable       = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
    useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';
 
    if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
        throw new Error("Table: "+useTable+' not supported');        // return false;
    }
 
    entities['38'] = '&amp;';
    if (useTable === 'HTML_ENTITIES') {        entities['160'] = '&nbsp;';
        entities['161'] = '&iexcl;';
        entities['162'] = '&cent;';
        entities['163'] = '&pound;';
        entities['164'] = '&curren;';        entities['165'] = '&yen;';
        entities['166'] = '&brvbar;';
        entities['167'] = '&sect;';
        entities['168'] = '&uml;';
        entities['169'] = '&copy;';        entities['170'] = '&ordf;';
        entities['171'] = '&laquo;';
        entities['172'] = '&not;';
        entities['173'] = '&shy;';
        entities['174'] = '&reg;';        entities['175'] = '&macr;';
        entities['176'] = '&deg;';
        entities['177'] = '&plusmn;';
        entities['178'] = '&sup2;';
        entities['179'] = '&sup3;';        entities['180'] = '&acute;';
        entities['181'] = '&micro;';
        entities['182'] = '&para;';
        entities['183'] = '&middot;';
        entities['184'] = '&cedil;';        entities['185'] = '&sup1;';
        entities['186'] = '&ordm;';
        entities['187'] = '&raquo;';
        entities['188'] = '&frac14;';
        entities['189'] = '&frac12;';        entities['190'] = '&frac34;';
        entities['191'] = '&iquest;';
        entities['192'] = '&Agrave;';
        entities['193'] = '&Aacute;';
        entities['194'] = '&Acirc;';        entities['195'] = '&Atilde;';
        entities['196'] = '&Auml;';
        entities['197'] = '&Aring;';
        entities['198'] = '&AElig;';
        entities['199'] = '&Ccedil;';        entities['200'] = '&Egrave;';
        entities['201'] = '&Eacute;';
        entities['202'] = '&Ecirc;';
        entities['203'] = '&Euml;';
        entities['204'] = '&Igrave;';        entities['205'] = '&Iacute;';
        entities['206'] = '&Icirc;';
        entities['207'] = '&Iuml;';
        entities['208'] = '&ETH;';
        entities['209'] = '&Ntilde;';        entities['210'] = '&Ograve;';
        entities['211'] = '&Oacute;';
        entities['212'] = '&Ocirc;';
        entities['213'] = '&Otilde;';
        entities['214'] = '&Ouml;';        entities['215'] = '&times;';
        entities['216'] = '&Oslash;';
        entities['217'] = '&Ugrave;';
        entities['218'] = '&Uacute;';
        entities['219'] = '&Ucirc;';        entities['220'] = '&Uuml;';
        entities['221'] = '&Yacute;';
        entities['222'] = '&THORN;';
        entities['223'] = '&szlig;';
        entities['224'] = '&agrave;';        entities['225'] = '&aacute;';
        entities['226'] = '&acirc;';
        entities['227'] = '&atilde;';
        entities['228'] = '&auml;';
        entities['229'] = '&aring;';        entities['230'] = '&aelig;';
        entities['231'] = '&ccedil;';
        entities['232'] = '&egrave;';
        entities['233'] = '&eacute;';
        entities['234'] = '&ecirc;';        entities['235'] = '&euml;';
        entities['236'] = '&igrave;';
        entities['237'] = '&iacute;';
        entities['238'] = '&icirc;';
        entities['239'] = '&iuml;';        entities['240'] = '&eth;';
        entities['241'] = '&ntilde;';
        entities['242'] = '&ograve;';
        entities['243'] = '&oacute;';
        entities['244'] = '&ocirc;';        entities['245'] = '&otilde;';
        entities['246'] = '&ouml;';
        entities['247'] = '&divide;';
        entities['248'] = '&oslash;';
        entities['249'] = '&ugrave;';        entities['250'] = '&uacute;';
        entities['251'] = '&ucirc;';
        entities['252'] = '&uuml;';
        entities['253'] = '&yacute;';
        entities['254'] = '&thorn;';        entities['255'] = '&yuml;';
		entities['8364'] = '&euro;';
		/* added by Alpatech */
		entities['8216'] = '&lsquo;';
		entities['8217'] = '&rsquo;';
		entities['8220'] = '&ldquo;';
		entities['8221'] = '&rdquo;';
    }
 
    if (useQuoteStyle !== 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';    }
    if (useQuoteStyle === 'ENT_QUOTES') {
        entities['39'] = '&#39;';
    }
    entities['60'] = '&lt;';    entities['62'] = '&gt;';
 
 
    // ascii decimals to real symbols
    for (decimal in entities) {        symbol = String.fromCharCode(decimal);
        hash_map[symbol] = entities[decimal];
    }
    
    return hash_map;
}

function htmlentities (string, quote_style) 
{
    // Convert all applicable characters to HTML entities  
    // 
    // version: 1004.2314
    // discuss at: http://phpjs.org/functions/htmlentities    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: nobbler
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // -    depends on: get_html_translation_table
    // *     example 1: htmlentities('Kevin & van Zonneveld');    // *     returns 1: 'Kevin &amp; van Zonneveld'
    // *     example 2: htmlentities("foo'bar","ENT_QUOTES");
    // *     returns 2: 'foo&#039;bar'
    var hash_map = {}, symbol = '', tmp_str = '', entity = '';
    tmp_str = string.toString();    
    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }
    hash_map["'"] = '&lsquo;';    for (symbol in hash_map) {
        entity = hash_map[symbol];
        tmp_str = tmp_str.split(symbol).join(entity);
    }
 return tmp_str;
}

function html_entity_decode (string, quote_style) {
    // http://kevin.vanzonneveld.net
    // +   original by: john (http://www.jd-tech.net)
    // +      input by: ger
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: marc andreu
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Ratheous
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Nick Kolosov (http://sammy.ru)
    // +   bugfixed by: Fox

    var hash_map = {}, symbol = '', tmp_str = '', entity = '';
    tmp_str = string.toString();
    
    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }

    // fix &amp; problem
    // http://phpjs.org/functions/get_html_translation_table:416#comment_97660
    delete(hash_map['&']);
    hash_map['&'] = '&amp;';

    for (symbol in hash_map) {
        entity = hash_map[symbol];
        tmp_str = tmp_str.split(entity).join(symbol);
    }
    tmp_str = tmp_str.split('&#039;').join("'");
    
    return tmp_str;
}

function real_htmlspecialchars (string, quote_style, charset, double_encode) {
    // http://kevin.vanzonneveld.net
    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Nathan
    // +   bugfixed by: Arno
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +      input by: felix
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // %        note 1: charset argument not supported
    // *     example 1: htmlspecialchars("<a href='test'>Test</a>", 'ENT_QUOTES');
    // *     returns 1: '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
    // *     example 2: htmlspecialchars("ab\"c'd", ['ENT_NOQUOTES', 'ENT_QUOTES']);
    // *     returns 2: 'ab"c&#039;d'
    // *     example 3: htmlspecialchars("my "&entity;" is still here", null, null, false);
    // *     returns 3: 'my &quot;&entity;&quot; is still here'

    var optTemp = 0, i = 0, noquotes= false;
    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
    }
    string = string.toString();
    if (double_encode !== false) { // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');

    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE' : 1,
        'ENT_HTML_QUOTE_DOUBLE' : 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE' : 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i=0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes) {
        string = string.replace(/"/g, '&quot;');
    }

    return string;
}

function htmlspecialchars(s)
{
 if(!s)
  return "";
 s = s.replace(/&/g,"%26");
 s = s.replace(/\"/g,"%22");
 s = s.replace(/\'/g,"%27");
 s = s.replace(/\‘/g,"%27");
 s = s.replace(/\’/g,"%27");
 s = s.replace(/</g,"%3C");
 s = s.replace(/>/g,"%3E");
 s = s.replace(/\+/g,"%2B");
 s = s.replace(/\$/g,"%24");
 s = s.replace(/\(/g,"%28");
 s = s.replace(/\)/g,"%29");
 s = s.replace(/=/g,"%3D");
 s = s.replace(/\n/g,"%0D%0A");
 //s = s.replace(/%/g,"%26#38");//
 s = s.replace(/à/g,"%26agrave;");
 s = s.replace(/á/g,"%26aacute;");
 s = s.replace(/é/g,"%26eacute;");
 s = s.replace(/è/g,"%26egrave;");
 s = s.replace(/í/g,"%26iacute;");
 s = s.replace(/ì/g,"%26igrave;");
 s = s.replace(/ó/g,"%26oacute;");
 s = s.replace(/ò/g,"%26ograve;");
 s = s.replace(/ú/g,"%26uacute;");
 s = s.replace(/ù/g,"%26ugrave;");
 s = s.replace(/€/g,"%26euro;");
 s = s.replace(/°/g,"%26deg;");
 return s;
}
//-------------------------------------------------------------------------------------------------------------------//
function formatNumber(num, dec)
{
 var n = num ? num : 0;
 if(!dec)
  return Math.round(n);

 n *= Math.pow(10,dec);
 n = Math.round(n);
 n /= Math.pow(10,dec);
 var s = n.toString();
 var x = s.split(".");
 if(x.length > 1)
 {
  for(var c=x[1].length; c < dec; c++)
   s+="0";
 }
 else
 {
  s+= ".";
  for(var c=0; c < dec; c++)
   s+="0";
 }
 return s;
}
//-------------------------------------------------------------------------------------------------------------------//
function formatCurrency(num, dec)
{
 if(!dec) dec=2;
 var n = num ? num : 0;
 n *= Math.pow(10,dec);
 n = Math.round(n);
 if(n)
  n /= Math.pow(10,dec);
 var s = n.toString();
 s = s.replace('.',',');
 var x = s.split(",");
 if(x.length > 1)
 {
  for(var c=x[1].length; c < dec; c++)
   s+="0";
 }
 else
 {
  s+= ",";
  for(var c=0; c < dec; c++)
   s+="0";
 }
 var x = s.split(",");
 if(x[0].length > 3)
 {
  var str = x[0];
  var sp = str.length-3;
  while(sp > 0)
  {
   x[0] = x[0].substr(0,sp)+"."+x[0].substr(sp);
   sp-= 3;
  }
  return x[0]+","+x[1];
 }
 return s;
}
//-------------------------------------------------------------------------------------------------------------------//
function parseCurrency(str)
{
 if(!str) return 0;
 if(typeof(str) == "number")
  str = str.toString();
 if(!str)
  str="0";

 str = str.replace(',','.');
 if(str.indexOf(".") > 0)
 {
  var x = str.split('.');
  if(x.length > 0)
  {
   str = "";
   for(var c=0; c < (x.length-1); c++)
    str+= x[c];
   str+= "."+x[c];
  }
 }
 return parseFloat(str);
}
//-------------------------------------------------------------------------------------------------------------------//
function parseTime(str)
{
 var hours = 0;
 var minutes = 0;
 var seconds = 0;

 var x = str.split(":");
 if(x[0] != "")
  hours = parseFloat(x[0]);
 if(x[1] && (x[1] != ""))
  minutes = parseFloat(x[1]);
 if(x[2] && (x[2] != ""))
  seconds = parseFloat(x[2]);

 return {h:hours, m:minutes, s:seconds, hh:(hours < 10 ? "0"+hours : hours), mm:(minutes < 10 ? "0"+minutes : minutes), ss:(seconds < 10 ? "0"+seconds : seconds)}
}
//-------------------------------------------------------------------------------------------------------------------//
function strdatetime_to_iso(str)
{
 str = str.ltrim();
 str = str.rtrim();

 /* DETECT LANGUAGE */
 if (navigator.appName == 'Netscape')
  var language = navigator.language;
 else
  var language = navigator.browserLanguage

 var d = new Date();

 var dS = "0000-00-00";
 var tS = "00:00";
 var x = str.split(" ");
 if(x[1]) /* parse time */
 {
  var xt = x[1].split(":");
  tS = xt[0]+(xt[1] ? ":"+xt[1] : ":00")
  if(xt[2]) tS+= ":"+xt[2];
 }
 if(x[0]) /* parse date */
 {
  var sign = "/";
  if(x[0].indexOf('-') > -1)
   sign = "-";
  else if(x[0].indexOf('.') > -1)
   sign = ".";
  var xd = x[0].split(sign);
  if(xd.length < 2)
   return false;
  if(language.indexOf('en') > -1)
  {
   var month = xd[0].ltrim();
   var day = xd[1].ltrim();
   var year = xd[2] ? xd[2] : d.getFullYear();
  }
  else
  {
   var month = xd[1].ltrim();
   var day = xd[0].trim();
   var year = xd[2] ? xd[2] : d.getFullYear();
  }
  /* Detect if a valid date */
  if(month > 12)
   return false;
  if(day > 31)
   return false;
  dS = year+"-"+month+"-"+day;
 }

 return dS+" "+tS;
}
//-------------------------------------------------------------------------------------------------------------------//
function gshSecureString(str)
{
 /*var s = htmlentities(str);
 s = s.replace(/&lt;/g, "<");
 s = s.replace(/&gt;/g, ">");
 s = s.replace(/&quot;/g, '"');
 s = s.replace(/&amp;/g, "&");
 s = s.replace(/\r\n/g, "<br/>");*/
 return str;
}
//-------------------------------------------------------------------------------------------------------------------//
function implode(sep,arr,len)
{
 if(!sep || !arr)
  return;
 var ret = "";
 if(!len)
  var len = arr.length;
 for(var c=0; c < len; c++)
  ret+= (c>0 ? sep : "")+arr[c];
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//
function is_numeric (mixed_var) {
    // Returns true if value is a number or a numeric string  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/is_numeric    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: David
    // +   improved by: taith
    // +   bugfixed by: Tim de Koning
    // +   bugfixed by: WebDevHobo (http://webdevhobo.blogspot.com/)    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: is_numeric(186.31);
    // *     returns 1: true
    // *     example 2: is_numeric('Kevin van Zonneveld');
    // *     returns 2: false    // *     example 3: is_numeric('+186.31e2');
    // *     returns 3: true
    // *     example 4: is_numeric('');
    // *     returns 4: false
    // *     example 4: is_numeric([]);    // *     returns 4: false
    return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
}
//-------------------------------------------------------------------------------------------------------------------//
Math.arctan = function(n)
{
 /* Ritorna l'arcotangente in gradi anzichè radianti */
 return (180/Math.PI) * Math.atan(n);
}
//-------------------------------------------------------------------------------------------------------------------//
Math.rad2deg = function(n)
{
 return n * (180/Math.PI);
}
//-------------------------------------------------------------------------------------------------------------------//
Math.deg2rad = function(n)
{
 return n * (Math.PI/180);
}
//-------------------------------------------------------------------------------------------------------------------//
function roundup(num,dec)
{
 if(typeof(num) == "string")
  num = parseFloat(num);
 if(!dec)
  dec = 2;
 if(!num)
  return 0;
 var div = Math.pow(10,dec);
 return parseFloat((Math.round(num*div)/div).toFixed(dec));
}
//-------------------------------------------------------------------------------------------------------------------//
function nl2br (str, is_xhtml) 
{
  // From: http://phpjs.org/functions
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Philip Peterson
  // +   improved by: Onno Marsman
  // +   improved by: Atli Þór
  // +   bugfixed by: Onno Marsman
  // +      input by: Brett Zamir (http://brett-zamir.me)
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // +   improved by: Maximusya
  // *     example 1: nl2br('Kevin\nvan\nZonneveld');
  // *     returns 1: 'Kevin<br />\nvan<br />\nZonneveld'
  // *     example 2: nl2br("\nOne\nTwo\n\nThree\n", false);
  // *     returns 2: '<br>\nOne<br>\nTwo<br>\n<br>\nThree<br>\n'
  // *     example 3: nl2br("\nOne\nTwo\n\nThree\n", true);
  // *     returns 3: '<br />\nOne<br />\nTwo<br />\n<br />\nThree<br />\n'
  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
//-------------------------------------------------------------------------------------------------------------------//
function br2nl(str)
{
 if(!str) return str;
 str = str.replace(/<br\/>/g, '\n');
 return str;
}
//-------------------------------------------------------------------------------------------------------------------//
function xml_purify(string)
{
 var ret = string.replace(/&/g, '&amp;');
 return ret;
}
//-------------------------------------------------------------------------------------------------------------------//

