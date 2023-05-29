<?PHP
/*
 * 2018-09-04   Paulo Sousa TFS TASK 2018: Sort company Links and hide channels menu if not exists channels
 * 2017-06-01	Luis Gomes	meta name="viewport" ...
 * 2017-03-30	Luis Cruz	R1703_00046 - add french language
 * 2016-03-22   Paulo Sousa new javascript method popupRequestAction
 * 2015-07-20	Luis Gomes	Login screen rebuild
 * 2015-07-03	LC\PS		R1403_00327: allow IE8 to intraefa module
 * 2014-11-14	Luis Cruz	R1411_00011 - checkDBError() to alert of an DB error and save it on a log
 * 2014-04-30	Luis Gomes	Internal/external site access validation
 * 2013-10-10	Luis Cruz	UTF-8 version
 * 2012-11-05	Luis Gomes	Enhanced display of ADODB errors
 * 2012-10-23	Luis Gomes	Links menu definition got from database
 * 2012-01-25	Paulo Sousa	define contant SHOW_MENUBAR
 * 2011-11-30	Luis Gomes	required_javascript() moved from efa.php to cinterface_**.php
 * 2011-10-03	Luis Gomes	efalogon() moved from efa.php to cinterface_**.php
 *
 */
 
header('X-UA-Compatible: IE=edge');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');

$GLOBALS['ADODB_COMCP'] = CP_UTF8;
//$GLOBALS['ADODB_QUIET'] = TRUE; //$GLOBALS['ADODB_WHISPER'] =	TRUE;	// LG 20121105 : ADODB errors control
define ('INTERNAL_SITE', TRUE);
define ('DEFAULT_Page', 'mypage');
define ('DEFAULT_COMP', '');
define ('FORCE_COMP','');		// Force company (Deny change company)


function getBrowser() 
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
	$trident = '';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE";
		$trident= strstr($u_agent, 'Trident');
		$trident=  mb_substr($trident, 0,11);
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    } 
	

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }

    // check if we have a number
    if ($version==null || $version=="") {$version="?";}

    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'trident'   => $trident,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
	
//	Trident/7.0	IE11
//	Trident/6.0	Internet Explorer 10
//	Trident/5.0	Internet Explorer 9
//	Trident/4.0	Internet Explorer 8
} 


class CInterface extends CInterfaceSTD
{
    var $user_device;   // 'PDA' || 'STANDARD'
	const SHOW_MENUBAR=TRUE;
	protected $toolbar2;	// Top menu bar (USer; links; companies; ...)
	var $showGSearch = true;
		
	function __construct()
	{
		require_once '../_efaC/_efaC.php';
		_efaC::phpIncludes();
			
		$this->_hiddenBar = new efaHiddenbar($this);
		$this->_toolbar = new efaToolbar($this);
		$this->_toolbar->skin('dhx_web');
	
		
		require_once('../common/efastd.php');

		  $this->user_device='STANDARD';
		  
		$page=efaSanitize($_REQUEST['page']);
		if($page<>'intraefa')
		{
			if((isset($this->version) && $this->version==2) || $page=='aoc' )
			{
				$html="<table border=0 width=100% cellspacing=10>
						<tr>
							<td><img src=img/atention.png> ".'<font size="4">An incompatible browser was detected</font>'."</td>
						</tr>
						<tr>
							<td>This apllication is only compatible with:</td>
						</tr>
						<tr>
							<td><ul><li>Internet Explorer 10.0 and above
							<li>Google Chrome 
							<li>Mozilla Firefox</ul></li>
							<p><B>Please contact Efacec IS Support, ext. 26717</ul></B></p>
							</td>
						</tr>
						</table>";	

				$ua=getBrowser();

				if(trim($ua['trident'])=='Trident/4.0' || (($page=='sigipweb' || $page=='aoc') && trim($ua['trident'])=='Trident/5.0') )  
					die($html);
			}
		}
	}

    function pageHead($sTit='', $bodyExtra = '')
    {
		if($this->version==2) return '';
		if($sTit==''){ $sTit = $this->PagDesc;}
		
		

		$__js = _efaC::js($this->jqueryLastVersion);
		$__sJS = '';
		foreach($__js as $k=>$v)
			$__sJS .= '<script src="'.$v["url"].'" type="text/javascript"></script>';
		$__css = _efaC::css();
		$__sCSS = '';
		foreach($__css as $k=>$v)
			$__sCSS .= '<link href="'.$v["url"].'" rel="stylesheet" type="text/css" media="all">';
		return(
			"<!DOCTYPE html>
			<!--Autor:Efacec - Sistemas de Informaçao-->

			<HTML>
				<head>
					<META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">
					<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
					<meta http-equiv=\"Content-Language\" content=\"pt\">
					<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
					<meta name=\"viewport\" content=\"initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,width=device-width,height=device-height,target-densitydpi=device-dpi,user-scalable=yes\" />
					<title>$sTit</title>
					<LINK href=\"$this->cssfile\" rel=stylesheet>
					".$this->includehtml."
  					".$__sJS."
  					".$__sCSS."
				</head>
			<BODY  leftMargin=0 link=#975922 text=#000000 topMargin=0 vLink=#a26024 marginheight=\"0\" marginwidth=\"0\" $bodyExtra>
 			<A name=top></A>"
 			);
		
		
		
        return("<!--Autor:Efacec - Sistemas de Informaçao-->
<!--DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\"http://www.w3.org/TR/html4/loose.dtd\"-->		
<HTML>
<head>
<META HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">
<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
<meta http-equiv=\"Content-Language\" content=\"pt\">
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>$sTit</title>
  <LINK href=\"$this->cssfile\" rel=stylesheet>
  ".$this->includehtml."
</head>
<BODY  leftMargin=0 link=#975922 text=#000000 topMargin=0 vLink=#a26024 marginheight=\"0\" marginwidth=\"0\" $bodyExtra>
 <A name=top></A>");
    }


// returns javascript functions allways requeired
function required_javascript()
{
	$html='<script language="JavaScript">
var cssmenuids=["cssmenu1"] //Enter id(s) of CSS Horizontal UL menus, separated by commas
var csssubmenuoffset=-1 //Offset of submenus from main menu. Default is 0 pixels.

function createcssmenu2(){
for (var i=0; i<cssmenuids.length; i++){
  var ultags=document.getElementById(cssmenuids[i]).getElementsByTagName("ul")
    for (var t=0; t<ultags.length; t++)
	{
		ultags[t].style.top=ultags[t].parentNode.offsetHeight+csssubmenuoffset+"px"
    	var spanref=document.createElement("span")
		spanref.className="arrowdiv"
		spanref.innerHTML="&nbsp;&nbsp;&nbsp;&nbsp;"
		ultags[t].parentNode.getElementsByTagName("a")[0].appendChild(spanref)
		ultags[t].parentNode.onmouseover=function(t)
		{
			this.style.zIndex=100
			this.getElementsByTagName("ul")[0].style.visibility="visible"
			this.getElementsByTagName("ul")[0].style.zIndex=0
    	}
    	ultags[t].parentNode.onmouseout=function()
		{
					this.style.zIndex=0
					this.getElementsByTagName("ul")[0].style.visibility="hidden"
					this.getElementsByTagName("ul")[0].style.zIndex=100
    	}
    }
  }
}

if (window.addEventListener)
window.addEventListener("load", createcssmenu2, false)
else if (window.attachEvent)
window.attachEvent("onload", createcssmenu2)
</script>';

	//<script type=\"text/javascript\" src=\"amt_menu.js\"></script>


	//$html.=" // PSILVA
	$html="
<script language=\"JavaScript\">
function displaySubs(the_sub)
{   if (document.getElementById(the_sub).style.display==\"\")
	{   document.getElementById(the_sub).style.display = \"none\";
		return;
					}
	document.getElementById(the_sub).style.display = \"\";
}

function collapseSubs ()
{   var subs_array = new Array(\"div01\", \"div02\", \"div03\");  // Put the id's of your hidden divs in this array
	for (i=0;i<subs_array.length;i++)
	{        var my_sub = document.getElementById(subs_array[i]);
		my_sub.style.display = \"none\";
	}
}
function jsValida(campo, total, maiusculas)
{
    if(maiusculas == \"1\")
       { campo.value = campo.value.toUpperCase()  }
    char = eval(campo.value.length)
    left = eval(total - char)
    if (left <= eval(\"-1\"))
    {
        alert(\"Excedeu o n?mero de caracteres (m?ximo \" + total + \").\");
        var value = campo.value.substr(0,total);
        campo.value = value;
    }
}

function showhidediv(elementid)
{
    var eleid = document.getElementById(elementid);

    if(eleid.style.display == 'block')
    {
        eleid.style.display = 'none';
    }
    else
    {
        eleid.style.display = 'block';
    }
}
function popupRequestAction(formName,mod)
{
        var formObj = document.getElementsByName(formName);
        formObj = formObj[0];
        formObj.mod.value=mod;formObj.submit();
}
</script>";

	return($html);
}

function draw_menu()
{
		return '';
		
		
		
		
		
		
	// Retrieve all parameters. Changing company or language all parameters are preserved
	$Params = '';
	reset($_REQUEST);
	while (list ($key, $val) = each($_REQUEST))
	{		
		if ($key != 'comp' && $key != 'login' & $key != 'pwd' & $val != 'Logon' & $key != 'l')
		{ 
			if	(	(($key != 'mod') && ($key != 'csid')) || 
				!(strpos($val, 'show_')===FALSE) || 
				!(strpos($val, 'edit_')===FALSE) || 
				!(strpos($val, 'search')===FALSE) 
				) // Allow modes show_**** ; search; gsearch; 
			{	$Params .= "&$key=" . str_replace (' ', '%20', $val);}
		}
	}

	// Links
	siteMainParameters($this, $Params,$links, $company, $comp_div);

	// change language
	$Langs = array ('PT' => 'Português ', 'ES' => 'Castellano', 'IN' => 'English &nbsp; ');
	reset($Langs);
	$language = "";
	while (list ($key, $val) = each($Langs))
	{
		   if (trim($this->l) == $key)
			   $language = $val;
		   $lang_div .= "<li><a href=\"$this->pagename?$Params&l=$key\">$val</a></li><br>";
	}
	if(trim($language)=='')$language="Português";

	if(trim($this->username)<>'')
		$username="<li><b>$this->username</b>&nbsp;|&nbsp;</li>";
	else
		$username="";

	$site=$this->stdImgLink('','','','','img/synergynet_s.png');
	//The menu is builed from the bottom to the top of the html code
	$html.="
	<TABLE width=100% border=0>
<TR><TD>$site<B><font color=blue> SynergyNet</font></B></TD>
	<TD align=left>
	<div class=\"horizontalcssmenu\">
	<ul id=\"cssmenu1\">

	<li><a href=$this->pagename?page=".DEFAULT_Page."&mod=logout >" . $this->translate ('logout') . "</a>&nbsp;</li>
	<li><a href=\"#\">$language <SMALL>&#9660;</SMALL></a>&nbsp;|&nbsp;
		<ul>
		$lang_div
		</ul>
	</li>
";
	if($comp_div<>'')
	{
	$html.="
	<li><a href=\"#\">$company <SMALL>&#9660;</SMALL></a>
		<ul>
		$comp_div
		</ul>
	&nbsp;|&nbsp;
	</li>
";
	}
	if($links<>'')
	{
	$html.="
	<li><a href=\"#\">Links <SMALL>&#9660;</SMALL></a>
		<ul>
		$links
		</ul>
	&nbsp;|&nbsp;
	</li>
";
	}

	$html.="
	<li><a href=$this->pagename?page=help&CONTEXT=$this->page&accesslevel=$this->accesslevel target=_blank>" . $this->translate ('help') . "</a>&nbsp;|&nbsp;</li>
	$username
	</ul>
	</div>
	</TD></TR></TABLE>";
		return($html);
	}


	function PageHeaderMessages()
		{
		$messageHtml = '
			<style>
			.dhtmlx_message_area{width: 400px;}
			
			.dhtmlx_message_area{left:50%; margin-left:-200px;}
			.LEFTdhtmlx_message_area{left:0;}
			</style>
			';
		if(!isset($_REQUEST["donotclosemsg"]))
			$messageHtml .= '
				<script>
					for(var propertyName in dhtmlx.message.pull)
						$(dhtmlx.message.pull[propertyName]).hide();
				</script>';
		$this->checkDBError(); 
		if(trim($this->error)!='')
			{
			$x = new efaMessage($this);
			$x->type('error')->message(str_replace("\n", "", $this->error));
			$messageHtml .= $x->html();
			}		
			else if(trim($this->warning)!='')
				{
				$x = new efaMessage($this);
				$x->type('warning')->message(str_replace("\n", "", $this->warning));
				$messageHtml .= $x->html();
				}
		if(trim($this->info)!='')
			{
			$x = new efaMessage($this);
			$x->type('success')->message(str_replace("\n", "", $this->info));
			$messageHtml .= $x->html();
			}
		if(trim($this->news)!='')
			{
			$x = new efaMessage($this);
			$x->type('info')->message(str_replace("\n", "", $this->news))->expire(10);
			$messageHtml .= $x->html();
			}
		return $messageHtml;
		}
    // Draw the page header
	function PageHeader($title='', $subTitle='')
		{	
		if($title!='') $this->title = $title;
		if($subTitle!='') $this->subtitle = $subTitle;
		if($this->PagUnderTitle==$this->subtitle) $this->PagUnderTitle = '';
		$Params = '';
		reset($_REQUEST);
		while (list ($key, $val) = each($_REQUEST))
			{		
			if ($key != 'comp' && $key != 'login' & $key != 'pwd' & $val != 'Logon' & $key != 'l')
				{ 
				if	(	(($key != 'mod') && ($key != 'csid')) || 
				!(strpos($val, 'show_')===FALSE) || 
				!(strpos($val, 'edit_')===FALSE) || 
				!(strpos($val, 'search')===FALSE) 
				) // Allow modes show_**** ; search; gsearch; 
					{
					$Params .= "&$key=" . str_replace (' ', '%20', $val);
					}
				}
			}
			
		$maintit = $this->Translate($this->page);
		
		
		$messageHtml = $this->PageHeaderMessages();
			
		// Draw the global search input box
		$search = '';
		if($this->showGSearch)
			{
			$search .= '<div style="position: relative; background: #ffffff; border: 1px solid #c0c0c0; height: 20px;  margin-top: 4px; margin-right: 4px;">';
				$search .= '<form id="efaModSearch">';
				$search .= '<input type="hidden" name="l" value="'.$this->l.'"></input>';
				$search .= '<input type="hidden" name="comp" value="'.$this->comp.'"></input>';
				$search .= '<input type="hidden" name="mod" value="gsearch"></input>';
				$search .= '<input id="gsearchPage" type="hidden" name="page" value="'.$this->page.'"></input>';
				$search .= '<input id="gsearch"  autocomplete="on" style="border-color: transparent; font-size: 12px; outline: none; vertical-align: top;" name="gsearch"></input>';
				$search .= '<img src="https://synergynet.efacec.com/pure2.icons/icons/FatCow/icons/find.png" style="padding: 2px;height: 16px;" onclick="$(\'#efaModSearch\').submit();">';
				$search .= '</form>';			
			$search .= '</div>';
			}
		
		return ''
			.$messageHtml
			.'<div id="efaWindowWrapper" style="background: rgba(255, 255, 255, 0.5) url(img/logoA.gif) center center no-repeat; position: fixed; top: 0; left: 0; bottom: 0; right: 0; z-index: 100;"></div>'
			.'<div id="efaTop" style="background: #ffffff; position: fixed; top: 0; width: 100%; z-index: 100;">'
			
				.$this->_stdPageTopMenu($Params)
				.($this->_toolbar->itemsCount()>0?'<div style="background: #006387; border-bottom: 1px solid #AAAAAB;"><div style="float: left">'.$this->_toolbar->html().'</div><div style="float: right;">'.$search.'</div><div style="clear: both;"></div></div>':'')
				.$this->_hiddenBar->html()
			.'</div>'
			.'<div id="efaTopWrapper"></div>'
			
			.'<div style="z-index: 0;">'
				.$dataArea
			.'</div>'
			.'
			<script>
			$(
				function () 
					{
					$("#efaTopWrapper").height($("#efaTop").height());
					$(window).on("resize", function(){
						$("#efaTopWrapper").height($("#efaTop").height())
						});
					efaWindowWrapperHide();
					} 
			);
			</script>
			'
			;
		}

	function getSQLUserCompanies()
	{
		return "
SELECT CMPY.company , CMPY.company+' '+CMPY.shortdesc AS tdsca
FROM dbo.tbcompanies AS CMPY 
	INNER JOIN dbo.tbmembers AS GRPS ON CMPY.company = GRPS.company AND GRPS.application = 'Organization' AND GRPS.groupid = 'members' AND GRPS.userid = '".$this->login."'
ORDER BY CMPY.company";		
	}
		
	private function setToolbarCompanies($Params)
	{	
		$this->toolbar2->add('comps')->title('Companies');
		$compsSubs = $this->toolbar2->item('comps')->subMenuAdd();

		$rs = getRS2($this->consql, $this->getSQLUserCompanies(), $flds,3600);
		foreach($rs as $k=>$v)
		{
			$link = $this->pagename.'?comp='.$v["company"].$Params;
			$compsSubs->add('comp'.$k)->link($link)->title($v["tdsca"]);
			if($this->comp == $v["company"]) $this->toolbar2->item('comps')->title($v["tdsca"]);
		}
			
	}

	function getSQLUserLinks()
	{return "SELECT * FROM dbo.tbuser_links WHERE (userid='".$this->login."') ORDER BY seqn";}
	
// Set username in the toolbar		
	private function setToolbarUsername($Params)
	{
		$subUsername = null;
		if(trim($this->username)<>'')
		{
			$this->toolbar2->add('username')->title('<b>'.$this->username.'</b>')->tooltip($this->accesslevel);	
			$subUsername = $this->toolbar2->item('username')->subMenuAdd();
		}
		
		$rsUserLinks = getRS2($this->consql, $this->getSQLUserLinks(), $flds,3600);
		
		if(CInterface::SHOW_MENUBAR!==FALSE && $subUsername!==null && count($rsUserLinks)>0)
		{
			$subUsername->add('mylinks')->title($this->translate('mylinks'));
			$mylinksSubs = $subUsername->item('mylinks')->subMenuAdd();
			foreach($rsUserLinks as $k=>$v)
				$mylinksSubs->add('li'.$k)->link($v["link"])->title($v["label"]);
			$subUsername->add('sepmylinks')->type('separator');
		}
					
		if($subUsername!==null) 
		{
			$subUsername->add('idion')->title($this->translate('Idiom'));
			$idiomSubs = $subUsername->item('idion')->subMenuAdd();
			$Langs = array ('PT' => 'Português ', 'ES' => 'Castellano', 'IN' => 'English', 'FR' => 'Français');
			foreach($Langs as $k=>$v)
			{
				$idiomSubs->add('i'.$k)->link($this->pagename.'?'.$Params.'&l='.$k)->title($v);
			   	if(trim($this->l) == $k) $idiomSubs->item('i'.$k)->icon('img/next2_s.png');
			}
			$subUsername->add('idiomSpacer')->type('separator');
		}
		if($subUsername!==null) $subUsername->add('logout')->link($this->pagename.'?page='.DEFAULT_Page.'&mod=logout')->title($this->translate('logout'));			
		
	}

	
	function getSQLCompanyLinks()
	{
		return "
SELECT tvalue AS tlink, remarks AS tdsca, 2 as torder, tparam
FROM  dbo.tbcompparam
WHERE company = '".$this->comp."' AND tparam LIKE 'SYNERGY%'
UNION ALL
SELECT 'efa.php' AS tlink, 'Synergynet' AS tdsca, 0 as torder, '' as tparam
ORDER BY torder,tparam, tdsca";				
	}
	
	private function setToolbarCompanyLinks()
	{
		$this->toolbar2->add('links')->title($this->translate('links'));
		$linksSubs = $this->toolbar2->item('links')->subMenuAdd();
			
		$rs = getRS2($this->consql, $this->getSQLCompanyLinks(), $flds,3600);
	

		foreach($rs as $k=>$v)
		{						
			$link = str_replace('$SID$', $this->sid, $v['tlink']);
			$linksSubs->add('link'.$k)->link($link)->title($v["tdsca"]);
					
			if($v['torder']==0)
				$linksSubs->add('linkSpacer')->type('separator');
		}
	}

	function getSQLChannels()
	{
		return "
SELECT groupid, groupdesc, obs as remarks 
FROM dbo.tbgroups
WHERE (company = '$this->comp') AND (application = 'channels') AND (tstatus = 'A')
ORDER BY reference";		
	}
	
	private function setToolbarChannels()
	{


		if(CInterface::SHOW_MENUBAR!==FALSE)
		{
			
			$rs=getRS2($this->consql, $this->getSQLChannels(), $flds,3600);
            if(count($rs)>0)
            {
                $this->toolbar2->add(channels)->title($this->translate('channels'));
                $channelSubs = $this->toolbar2->item('channels')->subMenuAdd();                
            }
			foreach($rs as $k=>$v)
				$channelSubs->add('ch'.$k)->link($this->BaseLink('mypage','channels',$v['groupid']))->title($v["groupdesc"]);
		}			
	}
	
	
	function rateIt()
	{
	   $x = new efaPopup($this);
	   $x->height(400);
	   $html = $x->html();
	   
	   $page=$this->page;
	   $trate='';
					   
	   $sql="SELECT R.tratingmodule,R.trating1,R.trating2,R.trating3,R.trating4,R.trating5
									   , (SELECT page from dbo. tbpages WHERE tmodule=R.tratingmodule) as tpage
									   , (SELECT trating from dbo.tbswmodratdet WHERE tratingmodule=R.tratingmodule and tuserid='$this->employee') as trate
					   FROM dbo.tbswmodrat R WITH (NOLOCK) WHERE 
					   tratingmodule=(select tparentmod from tbpages where tmodule='$this->tmodule')";
	   //print $sql;
	   $rs=dbQuery($this->consql, $sql, $flds);
	   if(count($rs)>0)
	   {
					   $rat1=$rs[0]['trating1'];
					   $rat2=$rs[0]['trating2'];
					   $rat3=$rs[0]['trating3'];
					   $rat4=$rs[0]['trating4'];
					   $rat5=$rs[0]['trating5'];
					   $page=$rs[0]['tpage'];
					   $trate=$rs[0]['trate'];
	   }
	   
	   if($trate=='')      
	   { 
					   $alertMsg=$this->translate('notrated');
					   $rating .= '<img src="'.efaCImgPath('help3.png').'" height=14px; title="'.$alertMsg.'";>';
	   }
	   
	   $totRatings=$rat1+$rat2+$rat3+$rat4+$rat5;
	   if ($totRatings>0)            { $avgRating=(($rat5*5)+($rat4*4)+($rat3*3)+($rat2*2)+($rat1*1))/$totRatings; }
	   else $avgRating=0;
	   
	   $label=$this->translate('tmodule').' : '.$page.' - '.$this->translate('ratethisapp');
	   for($c=0; $c<floor($avgRating); $c++)
					   {
					   $rating .= '<img src="'.efaCImgPath('halfleft1.png').'" height=14px; title="'.$label.'"; >';
					   $rating .= '<img src="'.efaCImgPath('halfright1.png').'" height=14px; title="'.$label.'"; >';
					   }
	   if($avgRating*10%10>0)
					   {
					   $rating .= '<img src="'.efaCImgPath('halfleft1.png').'" height=14px; title="'.$label.'"; >';
					   $rating .= '<img src="'.efaCImgPath('halfright0.png').'" height=14px; title="'.$label.'"; >';
					   }
	   for($c=ceil($avgRating); $c<5; $c++)
					   {
					   $rating .= '<img src="'.efaCImgPath('halfleft0.png').'" height=14px; title="'.$label.'"; >';
					   $rating .= '<img src="'.efaCImgPath('halfright0.png').'" height=14px; title="'.$label.'"; >';
					   }

	   $serverName = $_SERVER['SERVER_NAME']; /* synergynet.efacec.pt */
	   $site=$serverName.'/intra';
	   
	   $tmodule=$this->tmodule;
	   //print $tmodule; die;
	   //print_r($this); die;

	   $html.=' <a onclick="'.$x->jsOpen('https://'.$site.'/efa.php?page=evaluateit&mod=showpopup_rating&tratingmodule='.$alert.$tmodule.'&sid='.$this->sid.'&pajax=2&popupid='.$x->id()).'">'.$rating.'</a>';

	   return($html);
	}

	function _stdPageTopMenu($Params)
	{
		
		$this->toolbar2 = new efaMenubar($this);		
		$this->toolbar2->skin('dhx_web');	//$toolbar2->skin('dhx_terrace');
		$this->setToolbarUsername($Params);
		$this->setToolbarCompanyLinks();
		$this->setToolbarChannels();
		$this->setToolbarCompanies($Params);			
		
		$this->toolbar2->add('help')->link($this->pagename.'?page=help&CONTEXT='.$this->page.'&accesslevel='.$this->accesslevel)->title($this->translate('help'))->openInNewWindow(true);
				
		
		//print ENVIRONMENT;
		$bg='';
		$syn='SynergyNet';
		$textcolor='';
		if(ENVIRONMENT=='DEV')
		{
			$bg='background: #E8FDF2;';
			$textcolor='style="color: #E43D42;"';
			$syn='SynergyDEV';
		}
		elseif(ENVIRONMENT=='TEST')
		{
			$bg='background: #E9F0FE;';
			$textcolor='style="color: #E43D42;"';
			$syn='SynergyTEST';
		}
		elseif(ENVIRONMENT=='BETA')
		{
			$bg='background: #FCECEC;';
			$textcolor='style="color: #E43D42;"';
			$syn='SynergyBETA';
		}
		
		$toolbar1Html = '';
		$toolbar1Html .= '<span style="padding-right: 10px; padding-top: 5px;"><a href="'.$this->baseLink($this->page).'" class="efaTopAppTitle">'.($this->title!=''?$this->title:$this->Translate($this->page)).'</a></span>';
		
		if(defined('ENVIRONMENT'))
			$toolbar1Html .= '<span style="padding-right: 10px; padding-top: 5px;"><a href="efa.php" class="efaTopSynergyTitle" '.$textcolor.' >'.$syn.'</a></span>';

// Draw the global search input box
		$search = '';
		$search .= '<div style="position: relative; background: #ffffff; border: 1px solid #c0c0c0; height: 20px;  margin-top: 4px;">';
			$search .= '<form id="efaGSearch">';
			$search .= '<input type="hidden" name="l" value="'.$this->l.'"></input>';
			$search .= '<input type="hidden" name="comp" value="'.$this->comp.'"></input>';
			$search .= '<input type="hidden" name="mod" value="gsearch"></input>';
			$search .= '<input id="gsearchPage" type="hidden" name="page" value="mypage"></input>';
			$search .= '<input id="gsearch"  autocomplete="on" style="border-color: transparent; font-size: 12px; outline: none; vertical-align: top;" name="gsearch"></input>';
			$search .= '<img src="https://synergynet.efacec.com/pure2.icons/icons/FatCow/icons/zoom.png" style="padding: 2px;height: 16px;" onclick="$(\'#efaGSearch\').submit();">';
			$search .= '</form>';
			
			//$search .= $searchOptions;
			
		$search .= '</div>';
		
		$tSub = $this->subtitle.' '.$this->PagUnderTitle;
		$subTitle= '<span style="padding-right: 10px; padding-left: 5px; padding-top: 5px;" class="efaTopSynergySubTitle">'.$tSub.'</span>';
		
		
		return '
			<div style="border-bottom: 1px solid #AAAAAB; padding-left: 4px; '.$bg.' padding-right: 4px; padding-top: 4px;">
				<div style="float: left;"><a href="'.$this->baseLink($this->page).'"><img src="img/'.$this->page.'_l.png" height=40 border=0></a></div>
				<div style="float: left; width: 10px; height: 10px;"></div>		
				<div style="float: left; padding-top: 0">'.$toolbar1Html.'<br>'.$subTitle.'</div>
				
				<div style="float: right;">'.$search.'</div>
				<div style="float: right; padding-top: 3px;">'.$this->toolbar2->html().'</div>
				<div style="float: right; padding-top: 9px; padding-right: 10px; cursor: pointer; cursor: hand;">'.$this->rateIt().'</div>
				<div style="clear: both;"></div>
			</div>
			';		
		}







































    
    

	/**
	 * Draw a standard separator line
	 * @param <String> $color Color of the line. Default: #ff9900
	 * @return <type>
	 */
	function efaHR($title='', $height=24, $class='form', $color='#ff9900', $width='100%', $lheight=2)
	{
		$tit='';
		
		//print $title;
		if($title!='')
		{
			$tit='<table class="'.$class.'" width="'.$width.'">
				<tr height='.$height.' valign=bottom>
					<td align="left"><B>'.$title.'</B></td>
				</tr></table>';	
		}
		
		$table='<table class="'.$class.'" width="'.$width.'">
					<tbody>
						<tr height='.$lheight.' valign=bottom bgColor="'.$color.'">
							<td></td>
						</tr>
					</tbody>
				</table>';

		return($tit.$table);
	}


// Logon screen

function efaLogon()
{
    $this->version=1;
    $LogonMessage = $this->Translate("logonmsg");

    $LG_USER="Login:";
    $LG_PWD="Password";

    $sHidden = '';

    reset($_REQUEST);
    while (list($key, $val) = each($_REQUEST))
    {
		if(mb_substr($key,0,2)!='__')
			if($key!='mod' || $val!='logout')
				$sHidden .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
    }

	$invalidLogin='';
	if($this->GetFromArray('mod',$_REQUEST,'string_trim','',30)!='logout' && mb_strlen($this->GetFromArray("pwd",$_REQUEST,'string','',32)) > 0)
	{ $invalidLogin='<tr><td align=center><font color=red>'.$this->translate('LG_ERR_1').'</font></td></tr>'; }
	
	//if($this->l==''){$this->l='PT';}
	
	$page=$this->page;
	if($this->page==''){$page=DEFAULT_Page;}
	
	$pt='Português';
	if($this->l=='PT') {$pt='<b>Português</b>';}
	$es='Español';
	if($this->l=='ES') {$es='<b>Español</b>';}
	$in='English';
	if($this->l=='IN') {$in='<b>English</b>';}
	
    $HTML=	
'
<!--Efacec - Information Systems-->
<!--DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN""http://www.w3.org/TR/html4/loose.dtd"-->		
<HTML>
<head>
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<meta http-equiv="Content-Language" content="pt">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>SynergyNet - Login</title>
<LINK href="login.css" rel=stylesheet>
</head>
<BODY>

<table width=100% border=0><tr>
<td no wrap><a href="efa.php"><img src="img/synergynet_s.png" border=0></a><B><font color=blue> SynergyNet</font></B></TD>
	<td width=60%>&nbsp;</td>
	<td nowrap>
	<span class="font10"><a href="efa.php?'.$param.'&l=PT">'.$pt.'</a> | <a href="efa.php?'.$param.'&l=ES">'.$es.'</a> | <a href="efa.php?'.$param.'&l=IN">'.$in.'</a></span>
	<span class="font10"> | <a href="efa.php?page=help&CONTEXT='.$page.'&l='.$this->l.'" target=_blank>'.$this->translate ('help').'</a> | </span>
	<span class="font10"><a href="efa.php?page=help&CONTEXT='.$page.'&l='.$this->l.'" target=_blank>'.$this->translate ('contacts').'</a></span>
	</td>
	</tr>
	<tr><td height=60></td></tr>
	</table>
'.
"
<br>
<table cellpadding=\"10\" align=center width=546 class=tablogin>
<FORM action=$this->pagename method=post name=\"logscreen\">
$sHidden
<tr>
<td height=50 class=row1 valign=middle colspan=3><p align=center style=\"font-family:verdana; font-size:17px\">".$this->translate("intuserpass")."</p></td>
</tr>
<td valign=top class=loginlabel>".$this->translate("utilizador")."<BR>
<input type=text class=loginfield name=login id=login value=\"\"></td>
</tr>

<td valign=top class=loginlabel>".$this->translate("password")."<BR>
<input type=password class=loginfield name=pwd type=password value=\"\"></td>
</tr>

<tr><td align=\"center\"><input type=submit value=\"".$this->translate("login")."\" class=loginbutton></td></tr>

$invalidLogin

<tr><td align=\"center\">".$this->translate("explainnewaccount")."</td></tr>
</form>
<script language=\"JavaScript\">document.logscreen.login.focus();</script></table>
<div align=\"center\"><img border=\"0\" src=\"img/loginbot.png\"></div>
</body></html>";

    print($HTML); exit;
    }

/*
	function efaLogon()
	{
		$this->version=1;
        $LogonMessage = $this->Translate("logonmsg");

        $LG_MSG="Efacec AMT";
        $LG_USER="Login:";
        $LG_PWD="Password";

        $sHidden = '';

        reset($_REQUEST);
        while (list($key, $val) = each($_REQUEST))
        {
			if(mb_substr($key,0,2)!='__')
				if($key!='mod' || $val!='logout')
					$sHidden .= "<input type=\"hidden\" name=\"$key\" value=\"$val\">\n";
        }

		$HTML=$this->pageHead();
		$HTML.=$this->required_javascript();
		$HTML.=$this->draw_menu();
		$HTML.=$this->warning;
		$HTML.="
<br>
<table align=center width=400 style=\"border-width: 1px; border-color: #465964; border-collapse: collapse;\" FRAME=BOX RULES=NONE>
<FORM action=$this->pagename method=post name=\"logscreen\">
$sHidden
<tr>
<td colspan=3><b>".$this->translate("intuserpass")."</b></td>
</tr><tr><td>&nbsp;</td></tr><tr>
<td valign=top>".$this->translate("utilizador")."<BR>
<input type=text class=lg_log name=login id=login value=\"\"></td>
</tr>
</tr><tr><td>&nbsp;</td></tr><tr>
<td valign=top>".$this->translate("password")."<BR>
<input type=password class=lg_log name=pwd type=password value=\"\"></td>
</tr>
</tr><tr><td>&nbsp;</td></tr><tr>
<tr><td><input type=submit value=\"".$this->translate("login")."\" class=button></td></tr>
</tr><tr><td>&nbsp;</td></tr><tr>
<tr><td class=row1>".$this->translate("explainnewaccount")."</td></tr>
</tr><tr><td>&nbsp;</td></tr><tr>
</form>";
$HTML.="<script language=\"JavaScript\">document.logscreen.login.focus();</script>";
$HTML .= "</body></html>";

	if($this->GetFromArray('ppajax',$_REQUEST,'string_trim')!='') return "<script language=\"JavaScript\">$(function () {_home();}); </script>";
	return($HTML);
	}
*/


}
// Define main site parameters for top menubar: List of links; List of companies
function siteMainParameters($oMain, $Params, &$links, &$company, &$comp_div)
{
	$links='';

	$sql="SELECT CMPY.company AS tfield1, CMPY.company+' '+CMPY.shortdesc AS tfield2, 1 as tfield3
FROM dbo.tbcompanies AS CMPY INNER JOIN
     dbo.tbmembers AS GRPS ON CMPY.company = GRPS.company AND GRPS.application = 'Organization' AND GRPS.groupid = 'members' AND GRPS.userid = '$oMain->login'
UNION ALL
SELECT  tvalue AS tfield1, remarks AS tfield2, 2 as tfield3
FROM dbo.tbcompparam
WHERE  (company = '$oMain->comp') AND (tparam LIKE 'SYNERGY%')
ORDER BY tfield3, tfield2";
	$Comps=Array();
	$rs=getRS2($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($j = 0; $j < $rc; $j++)
    {
		if($rs[$j]['tfield3']==1)
		{
			$i=$rs[$j]['tfield1'];
			$Comps[$i]= $rs[$j]['tfield2'];
		}
		if($rs[$j]['tfield3']==2)
		{
			$link=str_replace('$SID$', $oMain->sid, $rs[$j]['tfield1']);
			$links.= '<li><a href="'.$link.'">&nbsp;'.$rs[$j]['tfield2'].'</a></li><br>';			
		}
    }
	
	reset($Comps);
	$company = '';
	while (list ($key, $val) = each($Comps))
	{
		   if ($oMain->comp == $key)
			   $company = $val;
		   //$HTML .= "<OPTION " . $key . " value=$this->pagename?comp=$key$Params $Selected>" . $val . "</OPTION>";
		   $comp_div .= "<li><a href=\"$oMain->pagename?comp=$key$Params\">$val</a></li><br>";
	}
	if(trim($company)=='') $company='453 AMT';

	return(0);
}
?>