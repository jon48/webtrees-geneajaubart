var edit_window_specs="width=650,height=600,left=175,top=100,resizable=1,scrollbars=1";var indx_window_specs="width=600,height=500,left=200,top=150,resizable=1,scrollbars=1";var help_window_specs="width=500,height=400,left=250,top=200,resizable=1,scrollbars=1";var find_window_specs="width=550,height=600,left=250,top=150,resizable=1,scrollbars=1";var mesg_window_specs="width=500,height=600,left=250,top=100,resizable=1,scrollbars=1";var chan_window_specs="width=500,height=600,left=250,top=100,resizable=1,scrollbars=1";var mord_window_specs="width=500,height=600,left=250,top=100,resizable=1,scrollbars=1";var assist_window_specs="width=900,height=800,left=70,top=70,resizable=1,scrollbars=1";var gmap_window_specs="width=600,height=620,left=200,top=150,resizable=1,scrollbars=1";var fam_nav_specs="width=300,height=600,left=817,top=150,resizable=1,scrollbars=1";function helpDialog(b,a){url="help_text.php?help="+b+"&mod="+a;dialog=jQuery("<div></div>").load(url+" .helpcontent").dialog({modal:true,width:500,closeText:""});jQuery(".ui-widget-overlay").on("click",function(){jQuery("div:ui-dialog:visible").dialog("close")});jQuery(".ui-dialog-title").load(url+" .helpheader");return false}function modalDialog(a,b){dialog=jQuery('<div title="'+b+'"></div>').load(a).dialog({modal:true,width:700,closeText:"",close:function(c,d){$(this).remove()}});jQuery(".ui-widget-overlay").on("click",function(){jQuery("div:ui-dialog:visible").dialog("close")});return false}function modalNotes(a,b){dialog=jQuery('<div title="'+b+'"></div>').html(a).dialog({modal:true,width:500,closeText:"",close:function(c,d){$(this).remove()}});jQuery(".ui-widget-overlay").on("click",function(){jQuery("div:ui-dialog:visible").dialog("close")});return false}function modalDialogSubmitAjax(a){jQuery.ajax({type:"POST",url:jQuery(a).attr("action"),data:jQuery(a).serialize(),success:function(b){window.location.reload()}});return false}function closePopupAndReloadParent(a){if(parent.opener){if(a==null||a==""){parent.opener.location.reload()}else{parent.opener.location=a}}window.close()}var msX=0;var msY=0;function MM_showHideLayers(){var c,e,a,d,b=MM_showHideLayers.arguments;for(c=0;c<(b.length-3);c+=4){if((d=document.getElementById(b[c]))!=null){if(d.style){div=d;d=d.style}a=b[c+2];if(a=="toggle"){if(d.visibility.indexOf("hid")!=-1){a="show"}else{a="hide"}}a=(a=="show")?"visible":(a=="hide")?"hidden":a;d.visibility=a;if(b[c+1]=="followmouse"){pobj=document.getElementById(b[c+3]);if(pobj!=null){if(pobj.style.top!="auto"&&b[c+3]!="relatives"){d.top=5+msY-parseInt(pobj.style.top)+"px";if(textDirection=="ltr"){d.left=5+msX-parseInt(pobj.style.left)+"px"}if(textDirection=="rtl"){d.right=5+msX-parseInt(pobj.style.right)+"px"}}else{d.top="auto";pagewidth=document.documentElement.offsetWidth+document.documentElement.scrollLeft;if(textDirection=="rtl"){pagewidth-=document.documentElement.scrollLeft}if(msX>pagewidth-160){msX=msX-150-pobj.offsetLeft}contentdiv=document.getElementById("content");msX=msX-contentdiv.offsetLeft;if(textDirection=="ltr"){d.left=(5+msX)+"px"}d.zIndex=1000}}else{if(WT_SCRIPT_NAME.indexOf("fanchart")>0){d.top=(msY-20)+"px";d.left=(msX-20)+"px"}else{if(WT_SCRIPT_NAME.indexOf("index.php")==-1){Xadjust=document.getElementById("content").offsetLeft;d.left=(5+(msX-Xadjust))+"px";d.top="auto"}else{Xadjust=document.getElementById("content").offsetLeft;d.top=(msY-50)+"px";d.left=(10+(msX-Xadjust))+"px"}}d.zIndex=1000}}}}}var show=false;function togglechildrenbox(a){if(!a){a=""}else{a="."+a}if(show){MM_showHideLayers("childbox"+a," ","hide"," ");show=false}else{MM_showHideLayers("childbox"+a," ","show"," ");show=true}return false}var lastfamilybox="";var popupopen=0;function show_family_box(b,a){popupopen=1;lastfamilybox=b;if(a=="relatives"){MM_showHideLayers("I"+b+"links","followmouse","show",""+a)}else{famlinks=document.getElementById("I"+b+"links");divbox=document.getElementById("out-"+b);parentbox=document.getElementById("box"+b);if(famlinks&&divbox&&parentbox){famlinks.style.top="0px";if(textDirection=="ltr"){famleft=parseInt(divbox.style.width)+15}else{famleft=0}if(isNaN(famleft)){famleft=0;famlinks.style.top=parentbox.offsetTop+"px"}pagewidth=document.documentElement.offsetWidth+document.documentElement.scrollLeft;if(textDirection=="rtl"){pagewidth-=document.documentElement.scrollLeft}if(famleft+parseInt(parentbox.style.left)>pagewidth-100){famleft=25}famlinks.style.left=famleft+"px";if(WT_SCRIPT_NAME.indexOf("index.php")!=-1){famlinks.style.left="100%"}MM_showHideLayers("I"+b+"links"," ","show",""+a);return}MM_showHideLayers("I"+b+"links","followmouse","show",""+a)}}function toggle_family_box(b,a){if(popupopen==1){MM_showHideLayers("I"+lastfamilybox+"links"," ","hide",""+a);popupopen=0}if(b==lastfamilybox){lastfamilybox="";return}popupopen=1;lastfamilybox=b;if(a=="relatives"){MM_showHideLayers("I"+b+"links","followmouse","show",""+a)}else{famlinks=document.getElementById("I"+b+"links");divbox=document.getElementById("out-"+b);parentbox=document.getElementById("box"+b);if(!parentbox){parentbox=document.getElementById(a+".0")}if(famlinks&&divbox&&parentbox){divWidth=parseInt(divbox.style.width);linkWidth=parseInt(famlinks.style.width);parentWidth=parseInt(parentbox.style.width);famlinks.style.top="3px";famleft=divWidth+8;if(textDirection=="rtl"){famleft-=(divWidth+linkWidth+5);if(browserType!="mozilla"){famleft-=11}}pagewidth=document.documentElement.offsetWidth+document.documentElement.scrollLeft;if(famleft+parseInt(parentbox.style.left)>pagewidth-100){famleft=25}famlinks.style.left=famleft+"px";if(WT_SCRIPT_NAME.indexOf("index.php")!=-1){famlinks.style.left="100%"}MM_showHideLayers("I"+b+"links"," ","show",""+a)}else{MM_showHideLayers("I"+b+"links","followmouse","show",""+a)}}}function hide_family_box(a){MM_showHideLayers("I"+a+"links","","hide","");popupopen=0;lastfamilybox=""}var timeouts=new Array();function family_box_timeout(a){tout=setTimeout("hide_family_box('"+a+"')",2500);timeouts[a]=tout}function clear_family_box_timeout(a){clearTimeout(timeouts[a])}function expand_layer(a){if(jQuery("#"+a+"_img").hasClass("icon-plus")){jQuery("#"+a+"_img").removeClass("icon-plus").addClass("icon-minus");jQuery("#"+a).show("fast")}else{jQuery("#"+a+"_img").removeClass("icon-minus").addClass("icon-plus");jQuery("#"+a).hide("fast")}return false}function getMouseXY(a){if(IE){msX=event.clientX+document.documentElement.scrollLeft;msY=event.clientY+document.documentElement.scrollTop}else{msX=a.pageX;msY=a.pageY}return true}function edit_interface(e,b,d){var c=b||edit_window_specs;var a="edit_interface.php?"+jQuery.param(e)+"&accesstime="+accesstime+"&ged="+WT_GEDCOM;window.open(a,"_blank",c)}function edit_record(b,a){edit_interface({action:"edit",pid:b,linenum:a});return false}function edit_raw(a){edit_interface({action:"editraw",pid:a});return false}function edit_note(a){edit_interface({action:"editnote",pid:a,linenum:1});return false}function edit_source(a){edit_interface({action:"editsource",pid:a,linenum:1});return false}function add_record(c,d){var b=document.getElementById(d);if(b){var a=b.options[b.selectedIndex].value;if(a=="OBJE"){window.open("addmedia.php?action=showmediaform&linkid="+c+"&ged="+WT_GEDCOM,"_blank",edit_window_specs)}else{edit_interface({action:"add",pid:c,fact:a})}}return false}function addClipboardRecord(c,d){var b=document.getElementById(d);if(b){var a=b.options[b.selectedIndex].value;edit_interface({action:"paste",pid:c,fact:a.substr(10)})}return false}function reorder_media(a){edit_interface({action:"reorder_media",pid:a},mord_window_specs);return false}function add_new_record(a,b){edit_interface({action:"add",pid:a,fact:b});return false}function addnewchild(b,a){edit_interface({action:"addchild",gender:a,famid:b});return false}function addnewspouse(b,a){edit_interface({action:"addspouse",famid:b,famtag:a});return false}function addopfchild(a,b){edit_interface({action:"addopfchild",pid:a,gender:b});return false}function addspouse(b,a){edit_interface({action:"addspouse",pid:b,famtag:a,famid:"new"});return false}function linkspouse(b,a){edit_interface({action:"linkspouse",pid:b,famtag:a,famid:"new"});return false}function add_famc(a){edit_interface({action:"addfamlink",pid:a,famtag:"CHIL"});return false}function add_fams(b,a){edit_interface({action:"addfamlink",pid:b,famtag:a});return false}function edit_name(b,a){edit_interface({action:"editname",pid:b,linenum:a});return false}function add_name(a){edit_interface({action:"addname",pid:a});return false}function addnewparent(b,a){edit_interface({action:"addnewparent",pid:b,famtag:a,famid:"new"});return false}function addnewparentfamily(b,a,c){edit_interface({action:"addnewparent",pid:b,famtag:a,famid:c});return false}function delete_fact(b,a,d,c){if(confirm(c)){edit_interface({action:"delete",pid:b,linenum:a,mediaid:d})}return false}function reorder_children(a){edit_interface({action:"reorder_children",pid:a});return false}function reorder_families(a){edit_interface({action:"reorder_fams",pid:a});return false}function reply(b,a){window.open("message.php?to="+b+"&subject="+a+"&ged="+WT_GEDCOM,"_blank",mesg_window_specs);return false}function delete_message(a){window.open("message.php?action=delete&id="+a,"_blank&ged="+WT_GEDCOM,mesg_window_specs);return false}function change_family_members(a){edit_interface({action:"changefamily",famid:a});return false}function addnewsource(a){pastefield=a;edit_interface({action:"addnewsource",pid:"newsour"},null,a);return false}function addnewrepository(a){pastefield=a;edit_interface({action:"addnewrepository",pid:"newrepo"},null,a);return false}function addnewnote(a){pastefield=a;edit_interface({action:"addnewnote",noteid:"newnote"},null,a);return false}function addnewnote_assisted(b,a){pastefield=b;edit_interface({action:"addnewnote_assisted",noteid:"newnote",pid:a},assist_window_specs,b);return false}function addmedia_links(c,b,a){pastefield=c;insertRowToTable(b,a);return false}function valid_date(datefield){var months=new Array("JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC");var datestr=datefield.value;var datearr=datestr.split("(");var datephrase="";if(datearr.length>1){datestr=datearr[0];datephrase=datearr[1]}datestr=datestr.toUpperCase();datestr=datestr.replace(/\s+/," ");datestr=datestr.replace(/(^\s)|(\s$)/,"");datestr=datestr.replace(/(\d)([A-Z])/,"$1 $2");datestr=datestr.replace(/([A-Z])(\d)/,"$1 $2");if(datestr.match(/^Q ([1-4]) (\d\d\d\d)$/)){datestr="BET "+months[RegExp.$1*3-3]+" "+RegExp.$2+" AND "+months[RegExp.$1*3-1]+" "+RegExp.$2}var qsearch=/^([^\d]*)(\d+)[^\d](\d+)[^\d](\d+)$/i;if(qsearch.exec(datestr)){var f0=RegExp.$1;var f1=parseInt(RegExp.$2,10);var f2=parseInt(RegExp.$3,10);var f3=parseInt(RegExp.$4,10);var f4=RegExp.$5;var dmy="DMY";if(typeof(locale_date_format)!="undefined"){if(locale_date_format=="MDY"||locale_date_format=="YMD"){dmy=locale_date_format}}var yyyy=new Date().getUTCFullYear();var yy=yyyy%100;var cc=yyyy-yy;if(dmy=="DMY"&&f1<=31&&f2<=12||f1>13&&f1<=31&&f2<=12&&f3>31){datestr=f0+f1+" "+months[f2-1]+" "+(f3>=100?f3:(f3<=yy?f3+cc:f3+cc-100))}else{if(dmy=="MDY"&&f1<=12&&f2<=31||f2>13&&f2<=31&&f1<=12&&f3>31){datestr=f0+f2+" "+months[f1-1]+" "+(f3>=100?f3:(f3<=yy?f3+cc:f3+cc-100))}else{if(dmy=="YMD"&&f2<=12&&f3<=31||f3>13&&f3<=31&&f2<=12&&f1>31){datestr=f0+f3+" "+months[f2-1]+" "+(f1>=100?f1:(f1<=yy?f1+cc:f1+cc-100))}}}}datestr=datestr.replace(/^[>]([\w ]+)$/,"AFT $1");datestr=datestr.replace(/^[<]([\w ]+)$/,"BEF $1");datestr=datestr.replace(/^([\w ]+)[-]$/,"FROM $1");datestr=datestr.replace(/^[-]([\w ]+)$/,"TO $1");datestr=datestr.replace(/^[~]([\w ]+)$/,"ABT $1");datestr=datestr.replace(/^[*]([\w ]+)$/,"EST $1");datestr=datestr.replace(/^[#]([\w ]+)$/,"CAL $1");datestr=datestr.replace(/^([\w ]+) ?- ?([\w ]+)$/,"BET $1 AND $2");datestr=datestr.replace(/^([\w ]+) ?~ ?([\w ]+)$/,"FROM $1 TO $2");if(datestr.match(/^=([\d ()\/+*-]+)$/)){datestr=eval(RegExp.$1)}datestr=datestr.replace(/(JAN)(?:UARY)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(FEB)(?:RUARY)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(MAR)(?:CH)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(APR)(?:IL)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(MAY) (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(JUN)(?:E)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(JUL)(?:Y)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(AUG)(?:UST)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(SEP)(?:TEMBER)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(OCT)(?:OBER)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(NOV)(?:EMBER)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(DEC)(?:EMBER)? (\d\d?)[, ]+(\d\d\d\d)/,"$2 $1 $3");datestr=datestr.replace(/(^| )(\d [A-Z]{3,5} \d{4})/,"$10$2");if(datephrase!=""){datestr=datestr+" ("+datephrase}datefield.value=datestr}var oldheight=0;var oldwidth=0;var oldz=0;var oldleft=0;var big=0;var oldboxid="";var oldimgw=0;var oldimgh=0;var oldimgw1=0;var oldimgh1=0;var diff=0;var oldfont=0;var oldname=0;var oldthumbdisp=0;var repositioned=0;var oldiconsdislpay=0;var rv=null;function expandbox(b,c){if(big==1){if(clength>0){fontdef.style.display="none"}restorebox(oldboxid,c);if(b==oldboxid){return true}}jQuery(document).ready(function(){clength=jQuery(".compact_view").length});url=window.location.toString();divbox=document.getElementById("out-"+b);inbox=document.getElementById("inout-"+b);inbox2=document.getElementById("inout2-"+b);parentbox=document.getElementById("box"+b);if(!parentbox){parentbox=divbox}gender=document.getElementById("box-"+b+"-gender");thumb1=document.getElementById("box-"+b+"-thumb");famlinks=document.getElementById("I"+b+"links");icons=document.getElementById("icons-"+b);iconz=document.getElementById("iconz-"+b);if(divbox){if(icons){oldiconsdislpay=icons.style.display;icons.style.display="block"}if(jQuery(iconz).hasClass("icon-zoomin")){jQuery(iconz).removeClass("icon-zoomin").addClass("icon-zoomout")}else{jQuery(iconz).removeClass("icon-zoomout").addClass("icon-zoomin")}oldboxid=b;big=1;oldheight=divbox.style.height;oldwidth=divbox.style.width;oldz=parentbox.style.zIndex;if(url.indexOf("descendancy.php")==-1){parentbox.style.zIndex="100"}if(c!=2){divbox.style.width="300px";diff=300-parseInt(oldwidth);if(famlinks){famleft=parseInt(famlinks.style.left);famlinks.style.left=(famleft+diff)+"px"}}divleft=parseInt(parentbox.style.left);if(textDirection=="rtl"){divleft=parseInt(parentbox.style.right)}oldleft=divleft;divleft=divleft-diff;repositioned=0;if(divleft<0){repositioned=1;divleft=0}divbox.style.height="auto";if(inbox){inbox.style.display="block";if(inbox.innerHTML.indexOf("LOADING")>0){var a=b.split(".")[0];var d=createXMLHttp();d.open("get","expand_view.php?pid="+a,true);d.onreadystatechange=function(){if(d.readyState==4){inbox.innerHTML=d.responseText}};d.send(null)}}else{inbox.style.display="none"}if(inbox2){inbox2.style.display="none"}fontdef=document.getElementById("fontdef-"+b);if(fontdef){oldfont=fontdef.className;fontdef.className="detailsZoom";fontdef.style.display="block"}namedef=document.getElementById("namedef-"+b);if(namedef){oldname=namedef.className;namedef.className="nameZoom"}addnamedef=document.getElementById("addnamedef-"+b);if(addnamedef){oldaddname=addnamedef.className;addnamedef.className="nameZoom"}if(thumb1){oldthumbdisp=thumb1.style.display;thumb1.style.display="block";oldimgw=thumb1.offsetWidth;oldimgh=thumb1.offsetHeight;if(oldimgw){thumb1.style.width=(oldimgw*2)+"px"}if(oldimgh){thumb1.style.height=(oldimgh*2)+"px"}}if(gender){oldimgw1=gender.offsetWidth;oldimgh1=gender.offsetHeight;if(oldimgw1){gender.style.width="15px"}if(oldimgh1){gender.style.height="15px"}}}return true}function createXMLHttp(){if(typeof XMLHttpRequest!="undefined"){return new XMLHttpRequest()}else{if(window.ActiveXObject){var b=["MSXML2.XmlHttp.5.0","MSXML2.XmlHttp.4.0","MSXML2.XmlHttp.3.0","MSXML2.XmlHttp","Microsoft.XmlHttp"];for(var a=0;a<b.length;a++){try{var d=new ActiveXObject(b[a]);return d}catch(c){}}}}throw new Error("XMLHttp object could not be created.")}function restorebox(a,b){divbox=document.getElementById("out-"+a);inbox=document.getElementById("inout-"+a);inbox2=document.getElementById("inout2-"+a);parentbox=document.getElementById("box"+a);if(!parentbox){parentbox=divbox}thumb1=document.getElementById("box-"+a+"-thumb");icons=document.getElementById("icons-"+a);iconz=document.getElementById("iconz-"+a);if(divbox){if(icons){icons.style.display=oldiconsdislpay}if(jQuery(iconz).hasClass("icon-zoomin")){jQuery(iconz).removeClass("icon-zoomin").addClass("icon-zoomout")}else{jQuery(iconz).removeClass("icon-zoomout").addClass("icon-zoomin")}big=0;if(gender){oldimgw1=oldimgw1+"px";oldimgh1=oldimgh1+"px";gender.style.width=oldimgw1;gender.style.height=oldimgh1}if(thumb1){oldimgw=oldimgw+"px";oldimgh=oldimgh+"px";thumb1.style.width=oldimgw;thumb1.style.height=oldimgh;thumb1.style.display=oldthumbdisp}divbox.style.height=oldheight;divbox.style.width=oldwidth;if(parentbox){parentbox.style.zIndex=oldz}if(inbox){inbox.style.display="none"}if(inbox2){inbox2.style.display="block"}fontdef=document.getElementById("fontdef-"+a);if(fontdef){fontdef.className=oldfont}namedef=document.getElementById("namedef-"+a);if(namedef){namedef.className=oldname}addnamedef=document.getElementById("addnamedef-"+a);if(addnamedef){addnamedef.className=oldaddname}}return true}var menutimeouts=new Array();function show_submenu(f,h,e){var a=document.body.scrollWidth+document.documentElement.scrollLeft;var l=document.getElementById(f);if(l&&l.style){if(document.all){a=document.body.offsetWidth}else{a=document.body.scrollWidth+document.documentElement.scrollLeft-55;if(textDirection=="rtl"){k=l.offsetLeft+l.offsetWidth+10}}var b=0;var n=l.childNodes.length;for(var m=0;m<n;m++){var d=l.childNodes[m];if(d.offsetWidth>b+5){b=d.offsetWidth}}if(l.offsetWidth<b){l.style.width=b+"px"}if(e=="down"){var g=document.getElementById(h);if(g){l.style.left=g.style.left;var k=l.offsetLeft+l.offsetWidth+10;if(k>a){var o=a-l.offsetWidth;l.style.left=o+"px"}}}if(e=="right"){var g=document.getElementById(h);if(g){if(textDirection=="ltr"){var c=g.offsetLeft+g.offsetWidth-40;var k=c+l.offsetWidth+10;if(k>a){l.style.right=g.offsetLeft+"px"}else{l.style.left=c+"px"}}else{l.style.left=(g.offsetLeft-l.offsetWidth)+"px"}l.style.top=g.offsetTop+"px"}}if(l.offsetLeft<0){l.style.left="0px"}if(l.offsetHeight>500){l.style.height="400px";l.style.overflow="auto"}l.style.visibility="visible"}clearTimeout(menutimeouts[f]);menutimeouts[f]=null}function hide_submenu(a){if(menutimeouts[a]!=null){element=document.getElementById(a);if(element&&element.style){element.style.visibility="hidden"}clearTimeout(menutimeouts[a]);menutimeouts[a]=null}}function timeout_submenu(a){if(menutimeouts[a]==null){tout=setTimeout("hide_submenu('"+a+"')",100);menutimeouts[a]=tout}}function focusHandler(a){var b=a?a:window.event;if(!b){return}if(b.target){pastefield=b.target}else{if(b.srcElement){pastefield=b.srcElement}}}function loadHandler(){var b,a;for(b=0;b<document.forms.length;b++){for(a=0;a<document.forms[b].elements.length;a++){if(document.forms[b].elements[a].type=="text"){if(document.forms[b].elements[a].onfocus==null){document.forms[b].elements[a].onfocus=focusHandler}}}}}var IE=document.all?true:false;if(!IE){document.captureEvents(Event.MOUSEMOVE|Event.KEYDOWN|Event.KEYUP)}document.onmousemove=getMouseXY;function toggleStatus(b){var a=document.getElementById(b);a.disabled=!(a.disabled)}function statusDisable(b){var a=document.getElementById(b);a.checked=false;a.disabled=true}function statusEnable(b){var a=document.getElementById(b);a.disabled=false}function statusChecked(b){var a=document.getElementById(b);a.checked=true}var monthLabels=new Array();monthLabels[1]="January";monthLabels[2]="February";monthLabels[3]="March";monthLabels[4]="April";monthLabels[5]="May";monthLabels[6]="June";monthLabels[7]="July";monthLabels[8]="August";monthLabels[9]="September";monthLabels[10]="October";monthLabels[11]="November";monthLabels[12]="December";var monthShort=new Array();monthShort[1]="JAN";monthShort[2]="FEB";monthShort[3]="MAR";monthShort[4]="APR";monthShort[5]="MAY";monthShort[6]="JUN";monthShort[7]="JUL";monthShort[8]="AUG";monthShort[9]="SEP";monthShort[10]="OCT";monthShort[11]="NOV";monthShort[12]="DEC";var daysOfWeek=new Array();daysOfWeek[0]="S";daysOfWeek[1]="M";daysOfWeek[2]="T";daysOfWeek[3]="W";daysOfWeek[4]="T";daysOfWeek[5]="F";daysOfWeek[6]="S";var weekStart=0;function cal_setMonthNames(d,k,n,b,h,e,g,l,m,f,a,c){monthLabels[1]=d;monthLabels[2]=k;monthLabels[3]=n;monthLabels[4]=b;monthLabels[5]=h;monthLabels[6]=e;monthLabels[7]=g;monthLabels[8]=l;monthLabels[9]=m;monthLabels[10]=f;monthLabels[11]=a;monthLabels[12]=c}function cal_setDayHeaders(d,e,f,g,b,a,c){daysOfWeek[0]=d;daysOfWeek[1]=e;daysOfWeek[2]=f;daysOfWeek[3]=g;daysOfWeek[4]=b;daysOfWeek[5]=a;daysOfWeek[6]=c}function cal_setWeekStart(a){if(a>=0&&a<7){weekStart=a}}function cal_toggleDate(f,e){var b=document.getElementById(f);if(!b){return false}if(b.style.visibility=="visible"){b.style.visibility="hidden";return false}if(b.style.visibility=="show"){b.style.visibility="hide";return false}var c=document.getElementById(e);if(!c){return false}var d=/((\d+ (JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC) )?\d+)/;if(d.exec(c.value)){var a=new Date(RegExp.$1)}else{var a=new Date()}b.innerHTML=cal_generateSelectorContent(e,f,a);if(b.style.visibility=="hidden"){b.style.visibility="visible";return false}if(b.style.visibility=="hide"){b.style.visibility="show";return false}return false}function cal_generateSelectorContent(g,f,c){var e='<table border="1"><tr>';e+='<td><select name="'+g+'_daySelect" id="'+g+'_daySelect" onchange="return cal_updateCalendar(\''+g+"', '"+f+"');\">";for(i=1;i<32;i++){e+='<option value="'+i+'"';if(c.getUTCDate()==i){e+=' selected="selected"'}e+=">"+i+"</option>"}e+="</select></td>";e+='<td><select name="'+g+'_monSelect" id="'+g+'_monSelect" onchange="return cal_updateCalendar(\''+g+"', '"+f+"');\">";for(i=1;i<13;i++){e+='<option value="'+i+'"';if(c.getUTCMonth()+1==i){e+=' selected="selected"'}e+=">"+monthLabels[i]+"</option>"}e+="</select></td>";e+='<td><input type="text" name="'+g+'_yearInput" id="'+g+'_yearInput" size="5" value="'+c.getUTCFullYear()+'" onchange="return cal_updateCalendar(\''+g+"', '"+f+"');\" /></td></tr>";e+='<tr><td colspan="3">';e+='<table width="100%">';e+="<tr>";j=weekStart;for(i=0;i<7;i++){e+="<td ";e+='class="descriptionbox"';e+=">";e+=daysOfWeek[j];e+="</td>";j++;if(j>6){j=0}}e+="</tr>";var d=new Date(c.getUTCFullYear(),c.getUTCMonth(),1);var b=d.getUTCDay();b=b-weekStart;var a=(1000*60*60*24);d=d.getTime()-(b*a)+(a/2);d=new Date(d);for(j=0;j<6;j++){e+="<tr>";for(i=0;i<7;i++){e+="<td ";if(d.getUTCMonth()==c.getUTCMonth()){if(d.getUTCDate()==c.getUTCDate()){e+='class="descriptionbox"'}else{e+='class="optionbox"'}}else{e+='style="background-color:#EAEAEA; border: solid #AAAAAA 1px;"'}e+='><a href="#" onclick="return cal_dateClicked(\''+g+"', '"+f+"', "+d.getUTCFullYear()+", "+d.getUTCMonth()+", "+d.getUTCDate()+');">';e+=d.getUTCDate();e+="</a></td>";datemilli=d.getTime()+a;d=new Date(datemilli)}e+="</tr>"}e+="</table>";e+="</td></tr>";e+="</table>";return e}function cal_setDateField(e,c,d,a){var b=document.getElementById(e);if(!b){return false}if(a<10){a="0"+a}b.value=a+" "+monthShort[d+1]+" "+c;return false}function cal_updateCalendar(h,g){var a=document.getElementById(h+"_daySelect");if(!a){return false}var b=document.getElementById(h+"_monSelect");if(!b){return false}var e=document.getElementById(h+"_yearInput");if(!e){return false}var f=parseInt(b.options[b.selectedIndex].value);f=f-1;var d=new Date(e.value,f,a.options[a.selectedIndex].value);if(!d){alert("Date error "+d)}cal_setDateField(h,d.getUTCFullYear(),d.getUTCMonth(),d.getUTCDate());var c=document.getElementById(g);if(!c){alert("no dateDiv "+g);return false}c.innerHTML=cal_generateSelectorContent(h,g,d);return false}function cal_dateClicked(e,d,b,c,a){cal_setDateField(e,b,c,a);cal_toggleDate(d,e);return false}function findIndi(c,a,b){b=(typeof b==="undefined")?WT_GEDCOM:b;pastefield=c;nameElement=a;window.open("find.php?type=indi&ged="+b,"_blank",find_window_specs);return false}function findPlace(b,a){a=(typeof a==="undefined")?WT_GEDCOM:a;pastefield=b;window.open("find.php?type=place&ged="+a,"_blank",find_window_specs);return false}function findFamily(b,a){a=(typeof a==="undefined")?WT_GEDCOM:a;pastefield=b;window.open("find.php?type=fam&ged="+a,"_blank",find_window_specs);return false}function findMedia(b,c,a){a=(typeof a==="undefined")?WT_GEDCOM:a;pastefield=b;if(!c){c="0all"}window.open("find.php?type=media&choose="+c+"&ged="+a,"_blank",find_window_specs);return false}function findSource(c,a,b){b=(typeof b==="undefined")?WT_GEDCOM:b;pastefield=c;nameElement=a;window.open("find.php?type=source&ged="+b,"_blank",find_window_specs);return false}function findnote(c,b,a){a=(typeof a==="undefined")?WT_GEDCOM:a;pastefield=c;nameElement=b;window.open("find.php?type=note&ged="+a,"_blank",find_window_specs);return false}function findRepository(b,a){a=(typeof a==="undefined")?WT_GEDCOM:a;pastefield=b;window.open("find.php?type=repo&ged="+a,"_blank",find_window_specs);return false}function findSpecialChar(a){pastefield=a;window.open("find.php?type=specialchar","_blank",find_window_specs);return false}function findFact(b,a){a=(typeof a==="undefined")?WT_GEDCOM:a;pastefield=b;tags=b.value;window.open("find.php?type=facts&tags="+tags+"&ged="+a,"_blank",find_window_specs);return false}function ilinkitem(b,a,c){c=(typeof c==="undefined")?WT_GEDCOM:c;window.open("inverselink.php?mediaid="+b+"&linkto="+a+"&ged="+c,"_blank",find_window_specs);return false}function message(d,c,a,b){window.open("message.php?to="+d+"&method="+c+"&url="+a+"&subject="+b,"_blank",mesg_window_specs);return false}function include_css(a){var b=document.getElementsByTagName("head")[0];var c=document.createElement("link");c.setAttribute("rel","stylesheet");c.setAttribute("type","text/css");c.setAttribute("href",a);b.appendChild(c)}function include_js(b){var a=document.getElementsByTagName("head")[0];var c=document.createElement("script");c.setAttribute("type","text/javascript");c.setAttribute("src",b);a.appendChild(c)}function findPosX(a){var b=0;if(a.offsetParent){while(1){b+=a.offsetLeft;if(!a.offsetParent){break}a=a.offsetParent}}else{if(a.x){b+=a.x}}return b}function findPosY(b){var a=0;if(b.offsetParent){while(1){if(b.style.position=="relative"){break}a+=b.offsetTop;if(!b.offsetParent){break}b=b.offsetParent}}else{if(b.y){a+=b.y}}return a}function activate_colorbox(a){jQuery.extend(jQuery.colorbox.settings,{fixed:true,current:"",previous:textDirection=="ltr"?"◀":"▶",next:textDirection=="ltr"?"▶":"◀",slideshowStart:"○",slideshowStop:"●",close:"×",});if(a){jQuery.extend(jQuery.colorbox.settings,a)}jQuery("body").on("click","a.gallery",function(b){jQuery("a[type^=image].gallery").colorbox({photo:true,maxWidth:"95%",maxHeight:"95%",rel:"gallery",slideshow:true,slideshowAuto:false,onComplete:function(){jQuery(".cboxPhoto").wheelzoom();jQuery(".cboxPhoto img").on("click",function(c){c.preventDefault()})}})})};