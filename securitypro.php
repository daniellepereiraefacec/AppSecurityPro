<?php
/**
 * @@name	Segurança - Visitas
 * @@author	Generator 
 * @@version 	10-02-2017 15:23:55
 * template version=2016-03-07
 * Revisions:
 * 
 */

 /**
  * Class CModule
  */
class CModule extends CMain
{
	var $gSearch;
	var $oMain;
	var $toExcel;		// Redirects output to  excel

	var $tsite;
	var $taccessV;		// Access level to Visits
	var $taccessL;		// Access level to Loads
	var $taccessG;		// Access level to gate (employee without card)
	var $taccessO;		// Access level to Occurrences
	
	//Main Class
	function __construct()
	{
		parent:: __construct(); // use parent method to call parent class
//		$this->tsite	= $this->getFromArray('tsite',$_REQUEST);
//		if($this->tsite=='') {$this->tsite=$this->getDefaultSite();}
		$this->gSearch	= $this->getFromArray('gsearch',$_REQUEST);
		if($this->getFromArray('toexcel')!=''){$this->toExcel='excel';} // SEND OUTPUT TO EXCEL

		$this->getSiteAccessLevel();
		
	}
	 /**
	  * main function
	  */
	function getHtml()
	{
		// Draws module toolbar
		$this->getToolbar();
		//gets contents from datarea
		$dataArea=$this->dataArea($this);
		//generate page
		return($this->stdPage($dataArea, ''));
	}
	 /**
	  * forward mod to classes
	  */
	private function  dataArea()
	{
		$html=''; $mod=$this->mod;

		if($mod=='') { $html=$this->dashboard(); }
		if($mod=='dashgate') { $html=$this->dashWIPGate(); }
		
		if($mod=='listsitedefs') { $html=$this->listsitedefs(); }
		if($mod=='setdefsite') 	 { $this->setDefaultSite(); $html=$this->listsitedefs();}
		
		
		//set module gsearch
		if(strstr('|gsearch|', '|'.$mod.'|'))
		{	$o=new CSearch($this);
			$html.=$o->getHtml($mod);	}


		$ent='secvisit';
		if(strstr($mod.'|', "_$ent|"))
		{	require_once 'csecvisit.php';
			$o=new CSecVisit($this);
			$html.=$o->getHtml($mod); 
			if($mod=='checkout_secvisit') {$html=$this->dashboard();}
		}

		$ent='secload';
		if(strstr($mod.'|', "_$ent|"))
		{	require_once 'csecload.php';
			$o=new CSecLoad($this);
			$html.=$o->getHtml($mod); 
			if($mod=='checkout_secload') {$html=$this->dashboard();}
		}

		$ent='secgate';
		if(strstr($mod.'|', "_$ent|"))
		{	require_once 'csecgate.php';
			$o=new CSecGate($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='subscription';
		if(strstr($mod.'|', "_$ent|"))
		{	require_once 'csecsubscription.php';
			$o=new CSubscription($this);
			$html=$o->getHtml($mod);
		}
		
		$ent='secauth';
		if(strstr($mod.'|', "_$ent|"))
		{	require_once 'csecauth.php';
			$o=new CSecAuth($this);
			$html=$o->getHtml($mod); 
		}
		
		$ent='secstat';
		if(strstr($mod.'|', "_$ent|"))
		{	require_once 'csecstat.php';
			$o=new CSecStat($this);
			$o->tsite=$this->tsite;
			$html=$o->getHtml($mod); 
		}

		$ent='secoccur';
		if(strstr($mod.'|', "_$ent|"))
		{	require_once 'csecoccur.php';
			$o=new CSecOccur($this);
			$html.=$o->getHtml($mod); 
		}
		
		return($html);
	}
	 /**
	  * get module main top toolbar
	  */	
        private function getToolbar()
        {
			$toolbar = $this->_toolbar;
			$toolbar->add('tsite')->link($this->BaseLink('','','',"tsite=$this->tsite"))->title($this->tsite)->tooltip($this->translate('home'))->efaCIcon('company.png');          
			$tbSite = $toolbar->item('tsite')->subMenuAdd();
			
			$sql="SELECT [tsite] FROM [dbo].[tbsecconfig] WHERE [tstatus]='A' ORDER BY tsite"; //print $sql; exit;
			$rs=dbQuery($this->consql, $sql, $flds, 3600);
			$rc=count($rs);		
			for ($r = 0; $r < $rc; $r++)
			{	$site=$rs[$r]['tsite'];
				$tbSite->add('site'.$site)->link($this->BaseLink('','','',"tsite=$site"))->title($site)->efaCicon('securitypro.png');
			}				
//			$tbSite->add('siteA')->link($this->BaseLink('','','','tsite=Arroteia'))->title('site Arroteia')->efaCicon('securitypro.png');
//			$tbSite->add('siteM')->link($this->BaseLink('','','','tsite=Maia'))->title('site Maia')->efaCicon('securitypro.png');
			
			$toolbar->add('search')->title($this->translate('search'))->tooltip($this->translate('search'))->efaCIcon('search.png');          
			$tbSearch = $toolbar->item('search')->subMenuAdd();
			$tbSearch->add('svisit')->link($this->BaseLink('','formsearch_secvisit','','tsite='.$this->tsite))->title($this->translate('formsearch_secvisit'))->efaCicon('search.png');
			$tbSearch->add('sload')->link($this->BaseLink('','formsearch_secload','','tsite='.  $this->tsite))->title($this->translate('formsearch_secload')) ->efaCicon('search.png');
			$tbSearch->add('sgate')->link($this->BaseLink('','formsearch_secgate','','tsite='.$this->tsite))->title($this->translate('formsearch_secgate'))->efaCicon('search.png');
			$tbSearch->add('soccur')->link($this->BaseLink('','formsearch_secoccur','','tsite='.$this->tsite))->title($this->translate('formsearch_secoccur'))->efaCicon('search.png');
			$tbSearch->add('sauth')->link($this->BaseLink('','formsearch_secauth','','tsite='.$this->tsite))->title($this->translate('formsearch_secauth'))->efaCicon('search.png');
			
			$toolbar->add('new')->link($this->BaseLink('','new_secvisit','',"tsite=$this->tsite"))->title($this->translate('new'))->tooltip($this->translate('new_secvisit'))->efaCIcon('new.png');          
			$tbNew = $toolbar->item('new')->subMenuAdd();
			
			$icon='newflat.png'; if($this->taccessV!='W') {$icon='cancel2.png';}
			$tbNew->add('snewv')->link($this->BaseLink('','new_secvisit','',"tsite=$this->tsite"))->title($this->translate('new_secvisit'))->efaCicon($icon);
			$icon='newflat.png'; if($this->taccessL!='W') {$icon='cancel2.png';}
			$tbNew->add('snewl')->link($this->BaseLink('','new_secload', '',"tsite=$this->tsite"))->title($this->translate('new_secload')) ->efaCicon($icon);
			$icon='newflat.png'; if($this->taccessG!='W') {$icon='cancel2.png';}
			$tbNew->add('snewgin')->link($this->BaseLink('','new_secgate','in',"tsite=$this->tsite"))->title($this->translate('newin_secgate'))->efaCicon($icon);
			$tbNew->add('snewgout')->link($this->BaseLink('','new_secgate','out',"tsite=$this->tsite"))->title($this->translate('newout_secgate'))->efaCicon($icon);
			$icon='newflat.png'; if($this->taccessO!='W') {$icon='cancel2.png';}
			$tbNew->add('snewo')->link($this->BaseLink('','new_secoccur','',"tsite=$this->tsite"))->title($this->translate('new_secoccur'))->efaCicon($icon);

			$toolbar->add('auth')->link($this->BaseLink('','new_secauth','',''))->title($this->translate('new_secauth'))->tooltip($this->translate('new_secauth'))->efaCIcon('calendar.png');          

			$toolbar->add('admin')->link($this->BaseLink('profiler','new_secvisit','',''))->title($this->translate('admin'))->tooltip($this->translate('admin'))->efaCIcon('admin.png');          
			$tbAdmin = $toolbar->item('admin')->subMenuAdd();
			$tbAdmin->add('stat')->link($this->BaseLink('','dash_secstat','',''))->title($this->translate('stats'))->tooltip($this->translate('stats'))->efaCIcon('report3.png');          
//			$tbAdmin->add('setdefsite')->link($this->BaseLink('','setdefsite','',''))->title($this->translate('setdefsite'))->tooltip($this->translate('setdefsite'))->efaCIcon('company.png');          
			$tbAdmin->add('listsitedefs')->link($this->BaseLink('','listsitedefs','',''))->title($this->translate('listsitedefs'))->tooltip($this->translate('listsitedefs'))->efaCIcon('deforigin.png');          
			$tbAdmin->add('Authoriz')->link($this->BaseLink('profiler','dash_applications','','&default_tab=2&comp=efa&tnodeid=1192688&tgpid=1192688'))->title($this->translate('manauthorizations'))->tooltip($this->translate('manauthorizations'))->efaCIcon('userinfo.png');          
			$tbAdmin->add('profiler')->link($this->BaseLink('profiler','','',''))->title($this->translate('profiler'))->tooltip($this->translate('profiler'))->efaCIcon('userinfo.png');          

			$toolbar->add('wipvisitload')->link($this->BaseLink('','','',''))->title($this->translate('checkoutvisload'))->tooltip($this->translate('checkoutvisload'))->icon('img/secwip.png');          
			$toolbar->add('wipsec')->link($this->BaseLink('','dashgate','',''))->title($this->translate('checkout_secgate'))->tooltip($this->translate('checkout_secgate'))->icon('img/secGatesWIP.png');          			
			
			return $toolbar->html();
        }        

	private function dashboard()
	{
		$x = new efalayout($this);
		$x->pattern('3U');  //use it to split screen in layouts
		$x->add($this->wipVisits())->padding(5);
		$x->add($this->wipLoads())->padding(5);
		$x->add($this->footer())->padding(5);
//		$x->add('<img src="img/prodwelcome.jpg">')->padding(30);
		return $x->html();
	}

	private function dashWIPGate()
	{
		$x = new efalayout($this);
		$x->pattern('2E');  //use it to split screen in layouts
		$x->add($this->wipGates())->padding(5);
		$x->add($this->footer())->padding(5);
		return $x->html();
	}
	
	private function listsitedefs()
	{
		$oMain=$this;
		
		$tsiteDef=$this->getDefaultSite();
		
		$sql="SELECT [tsite]
      ,(SELECT groupid FROM dbo.tbgroups WHERE tgpid=tgpidwrite) AS twrite
	  ,[tgpidwrite]
      ,(SELECT groupid FROM dbo.tbgroups WHERE tgpid=tgpidvisits) AS tvisits
      ,[tgpidvisits]
      ,(SELECT groupid FROM dbo.tbgroups WHERE tgpid=tgpidloads) AS tloads
      ,[tgpidloads]
      ,(SELECT groupid FROM dbo.tbgroups WHERE tgpid=tgpidgates) AS tgate
      ,[tgpidgates]
      ,[trefaidoccur]
      ,'' as toper FROM [dbo].[tbsecconfig] WHERE [tstatus]='A' ORDER BY tsite"; //print $sql; exit;
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$param='default_tab=2&tnodeid='.$rs[$r]['tgpidwrite'].'&tgpid='.$rs[$r]['tgpidwrite'];
			$rs[$r]['twrite']=$oMain->stdImglink('dash_applications', 'profiler','',$param, '', $rs[$r]['twrite'],'_blank');

			$param='default_tab=2&tnodeid='.$rs[$r]['tgpidvisits'].'&tgpid='.$rs[$r]['tgpidvisits'];
			$rs[$r]['tvisits']=$oMain->stdImglink('dash_applications', 'profiler','',$param, '', $rs[$r]['tvisits'],'_blank');	
			
			$param='default_tab=2&tnodeid='.$rs[$r]['tgpidloads'].'&tgpid='.$rs[$r]['tgpidloads'];
			$rs[$r]['tloads']=$oMain->stdImglink('dash_applications', 'profiler','',$param, '', $rs[$r]['tloads'],'_blank');

			$param='trefaid='.$rs[$r]['trefaidoccur'];
			$rs[$r]['trefaidoccur']=$oMain->stdImglink('shareplaceViewReferenceTree', 'shareplace','',$param, '', $rs[$r]['trefaidoccur'],'_blank');			

			$param='tsite='.$rs[$r]['tsite'];
			if($rs[$r]['tsite']== $tsiteDef)
			{$rs[$r]['toper']= $oMain->translate('isdefaultsite');}
			else
			{
				$rs[$r]['toper']= $oMain->stdImglink('setdefsite', '','',$param, 'company_s.png', $oMain->translate('setdefault'), '', '', 
				$oMain->translate('setdefsite'),$oMain->loading());								
			}
				
		}

		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->dbClickLink($this->baseLink('', 'show_secvisit', '', 'tvisitid=§§tvisitid§§'));
		$oTable->data($rs);
		$oTable->multilineRow(true);	//in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf
		$oTable->autoExpandHeight(true);
	   
		$oTable->columnAdd('tsite');
		$oTable->columnAdd('twrite');
		$oTable->columnAdd('tvisits');
		$oTable->columnAdd('tloads');
		$oTable->columnAdd('tgate');
		$oTable->columnAdd('trefaidoccur');
		$oTable->columnAdd('toper');
		
		$tab=$oMain->efaHR($oMain->translate('listsitedefs'));
		$tab.=$oTable->html();

		return $tab;		
	}

	private function wipGates()
	{
		$oMain=$this;
		$tab=$oMain->efaHR($oMain->translate('dashwipgate'));
		if($this->taccessV=='') {return $tab.$oMain->translate('noaccessareasite');}
		
		$yesterday=date("Y-m-d H:i:s", strtotime("yesterday"));
		$sql="SELECT '' as url, tgateid, tdatin, templname, temployee, templname, tdivi, treason, tremarks, '' as timg
			FROM dbo.tbsecgate WHERE (tsite = '$this->tsite') AND (tdatin > '$yesterday') AND (tdatout='1970-01-01 00:00:00')";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$param='tgateid='.$rs[$r]['tgateid'];
			
			$rs[$r]['tdatin']=$oMain->stdImglink('show_secgate', '','showstandards',$param, '', $oMain->formatDate($rs[$r]['tdatin'],true), '', '', '',$oMain->loading());			
			
			$rs[$r]['url'] = $oMain->stdImglink('checkout_secgate', '','',$param, 'approve3_m.png', '', '', '', 
			//$rs[$r]['tvisitid']= $oMain->stdImglink('checkout_secvisit', '','',$param, 'approve3_s.png', '', '', '', 
				$oMain->translate('checkout_secgate'),$oMain->loading()).' '.$rs[$r]['tgateid'];
			
			
			$rs[$r]['timg']='<link type="text/css" rel="StyleSheet" href="securitypro.css">
<div class="image">				
	<img class="photo" src='.$this->stdGetUserPicture($rs[$r]['temployee']).' height="35">
</div><div class="keepheight"> </div>'	;
		}

		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->dbClickLink($this->baseLink('', 'show_secgate', '', 'tgateid=§§tgateid§§'));
		$oTable->data($rs);
		$oTable->multilineRow(true);	//in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf
		$oTable->autoExpandHeight(true);
	   
		$oTable->columnAdd('tgateid')->hidded(true);
		$oTable->columnAdd('url')->width(10);
		$oTable->columnAdd('tdatin')->width(20);
		$oTable->columnAdd('timg')->width(5)->title('^')->align('center');
		$oTable->columnAdd('temployee')->width(10);
		$oTable->columnAdd('templname')->width(40);
		$oTable->columnAdd('tdivi')->width(10);

		$tab.=$oTable->html();
		return $tab;
	}
	
	private function wipVisits()
	{
		$oMain=$this;
		$tab=$oMain->efaHR($oMain->translate('tocheckoutvisit'));
		if($this->taccessV=='') {return $tab.$oMain->translate('noaccessareasite');}
		
		$sql="SELECT '' as url, tvisitid, tdatin, tnama, tentr, tregistration, temployee, templname, tdivi, tphone, treason, tremarks
			FROM dbo.tbsecvisit WHERE (tsite = '$this->tsite') AND (tdatin > tdatout)";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$param='tvisitid='.$rs[$r]['tvisitid'];
			
			$rs[$r]['tdatin']=$oMain->stdImglink('show_secvisit', '','showstandards',$param, '', $oMain->formatDate($rs[$r]['tdatin'],true), '', '', '',$oMain->loading());			
			
			$rs[$r]['url'] = $oMain->stdImglink('checkout_secvisit', '','',$param, 'approve3_s.png', '', '', '', 
			//$rs[$r]['tvisitid']= $oMain->stdImglink('checkout_secvisit', '','',$param, 'approve3_s.png', '', '', '', 
				$oMain->translate('checkout_secvisit'),$oMain->loading()).' '.$rs[$r]['tvisitid'];
		}

		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->dbClickLink($this->baseLink('', 'show_secvisit', '', 'tvisitid=§§tvisitid§§'));
		$oTable->data($rs);
		$oTable->multilineRow(true);	//in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf
		$oTable->autoExpandHeight(true);
	   
		$oTable->columnAdd('tvisitid')->hidded(true);
		$oTable->columnAdd('url')->width(12);
		$oTable->columnAdd('tdatin')->width(20);
//		$oTable->columnAdd('tdatin')->type('datetime');
		$oTable->columnAdd('tregistration')->width(14);
		$oTable->columnAdd('tnama')->width(28);
		$oTable->columnAdd('templname')->width(26);
//		$oTable->columnAdd('remarks');
//		$oTable->columnAdd('toper');
		$tab.=$oTable->html();
		return $tab;
	}
	
	private function wipLoads()
	{
		$oMain=$this;
		$tab=$oMain->efaHR($oMain->translate('tocheckoutload'));
		if($this->taccessV=='') {return $tab.$oMain->translate('noaccessareasite');}
		
		$sql="SELECT '' as url, [tloadid],[tsite],[tdatin],[tdatout],[tregistration],[tdeliveryto],[tentr],[ttype],[tdoctype],[tdocnumber],[tcontact],[tremarks]
			FROM dbo.tbsecload WHERE (tsite = '$this->tsite') AND (tdatin > tdatout)";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$param='tloadid='.$rs[$r]['tloadid'];
			
			$rs[$r]['tdatin']=$oMain->stdImglink('show_secload', '','showstandards',$param, '', $oMain->formatDate($rs[$r]['tdatin'],true), '', '', '',$oMain->loading());			
			
			$rs[$r]['url'] = $oMain->stdImglink('checkout_secload', '','',$param, 'approve3_s.png', '', '', '', 
				$oMain->translate('checkout_secload'),$oMain->loading()).' '.$rs[$r]['tloadid'];
		}

		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->dbClickLink($this->baseLink('', 'show_secvisit', '', 'tvisitid=§§tvisitid§§'));
		$oTable->data($rs);
		$oTable->multilineRow(true);	//in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf
		$oTable->autoExpandHeight(true);
	   
		$oTable->columnAdd('tloadid')->hidded(true);
		$oTable->columnAdd('url')->width(12);
		$oTable->columnAdd('tdatin')->width(18);
//		$oTable->columnAdd('tdatin')->type('datetime');
		$oTable->columnAdd('tregistration')->width(11);
		$oTable->columnAdd('tdeliveryto')->width(30);
		$oTable->columnAdd('tentr')->width(29);
//		$oTable->columnAdd('tdoctype');
//		$oTable->columnAdd('remarks');
//		$oTable->columnAdd('toper');
		$tab.=$oTable->html();
		return $tab;
	}
	
	private function footer()
	{	
		$oMain = $this;
		$username=$this->username;
		$userAccesses="$this->taccessV | $this->taccessL | $this->taccessG | $this->taccessO";
		$userData='<div class="chip"> '.$link.' <img class="photo" src='.$this->stdGetUserPicture($this->employee).
			' title="'.$username.' ('.$userAccesses.')'.'" height="65">'.$username.'</div>';
		if($this->mod=='dashgate')
		{$dash=$oMain->stdImglink('', '','',   $tparam,'img/secWIP.png',   '','', $oMain->translate('dashWIPMain'));}
		else
		{$dash=$oMain->stdImglink('dashgate', '','',   $tparam,'img/secGatesWIP.png',   '','', $oMain->translate('dashWIPGate'));}

		$space="";
		$tparam="tsite=$this->tsite";
		$links=$oMain->stdImglink('new_secload', '','',   $tparam,'img/secLoads.png',   '','', $oMain->translate('new_secload')).' &nbsp;'.
		$links=$oMain->stdImglink('new_secvisit','','',   $tparam,'img/secVisitor.png', '','', $oMain->translate('new_secvisit')).' &nbsp;'.
		$links=$oMain->stdImglink('new_secgate', '','in', $tparam,'img/secGatesIn.png', '','', $oMain->translate('newin_secgate')).' &nbsp;'.
		$links=$oMain->stdImglink('new_secgate', '','out',$tparam,'img/secGatesOut.png','','', $oMain->translate('newout_secgate')).' &nbsp;'.
		$links=$oMain->stdImglink('new_secoccur','','',   $tparam,'img/secOccur.png',   '','', $oMain->translate('new_secoccur'));
	
//		$html = $this->efaHR($this->translate('newoccur'));
		$html.="<table width=100%><tr><td>$userData</td><td>$dash</td><td align=right>$links</td></tr></table>";
		
		return $html;
	}

	private function getSiteAccessLevel()
	{
		$oMain = $this;
		$this->tsite = $this->getFromArray('tsite',$_REQUEST);
	
		if($this->tsite=='') 
		{
			$tvisitid = $this->getFromArray('tvisitid',$_REQUEST,'int');
			if($tvisitid>0)
			{
				$sql="SELECT tsite FROM [dbo].[tbsecvisit] WHERE tvisitid='$tvisitid'"; //print $sql; exit;
				$rs=dbQuery($oMain->consql, $sql, $flds);
				if(isset($rs[0]['tsite'])) {$tsite=$rs[0]['tsite'];}
			}
		
		}		
		if($this->tsite=='') {$this->tsite=$this->getDefaultSite();}
		if($this->tsite=='') {$this->tsite='Arroteia';}
		
		
		$sql="SELECT
			  (SELECT (dbo.secaccesstype('V', '$this->tsite',$oMain->employee))) as taccessv
			, (SELECT (dbo.secaccesstype('L', '$this->tsite',$oMain->employee))) as taccessl
			, (SELECT (dbo.secaccesstype('G', '$this->tsite',$oMain->employee))) as taccessg
			, (SELECT (dbo.secaccesstype('O', '$this->tsite',$oMain->employee))) as taccesso
			";
		$rs=dbQuery($oMain->consql, $sql, $flds); //print $sql; exit;
		$this->taccessV=rtrim($rs[0]['taccessv']);
		$this->taccessL=rtrim($rs[0]['taccessl']);
		$this->taccessG=rtrim($rs[0]['taccessg']);
		$this->taccessO=rtrim($rs[0]['taccesso']);

	}

	private function getDefaultSite()
	{
		$sql="SELECT t_value FROM tbuser_parameters WHERE userid='$this->login' AND parameter='SecurityProSite'"; //print $sql; exit;
		$rs=dbQuery($this->consql, $sql, $flds);
		if(isset($rs[0]['t_value'])) {return $rs[0]['t_value'];}
		return'';
	}

	private function setDefaultSite()
	{	
		$oMain=$this;
		$sql="[dbo].[spuserparameters] '$oMain->sid','set','$oMain->login','SecurityProSite','$this->tsite',''"; //print $sql; exit;
		$rs=dbQuery($oMain->consql, $sql, $flds);
		if(isset($rs[0]['tdesc']) && isset($rs[0]['tstatus']))
		{
		   $tdesc=$rs[0]['tdesc'];
		}
		else
		{
			$rs[0]['tstatus'] = -4999;
			$tdesc = 'unknown error';
		}
		
		return($rs[0]['tstatus']);
	}
	
}// end Cmodule

/**
  * classs used by gsearch and advanced search
  */
class CSearch
{

	public $oMain;
	var $tvisitid;    /** Registo de visita */
	var $tdatin;    /** Entrada */
	var $tdatout;    /** Saida */
	var $tnama;    /** Nome do visitante */
	var $tentr;    /** Empresa */
	var $tregistration;    /** Matricula */
	var $temployee;    /** Visitado */
	var $templname;    /** Nome visitado */
	var $tdivi;    /** Empresa/Divisão */
	var $tphone;    /** Ext. tel. */
	var $treason;    /** Motivo */
	var $tremarks;    /** Notas */
	var $tmodifiedby;    /** Modificado por */
	var $tmodifdate;    /** Data de Modificação */
	

	function  __construct($oMain,$readFromRequest=TRUE)
	{
		$this->oMain=$oMain;
		if($readFromRequest==TRUE)  
			$this->readFromRequest();
	}

	 /**
	  * gsearch and search setup
	  */
	function getHtml($mod, $completeLayout=true)
	{	$oMain=$this->oMain;
		if($oMain->GetFromArray('tmsgid',$_REQUEST,'int')>0)
		{	
			require_once 'csecoccur.php';
			$o=new CSecOccur($oMain);
			$o->toccurid=$oMain->GetFromArray('gsearch',$_REQUEST,'int');
			$html=$o->getHtml('show_secoccur'); 
		}
		else
		{
			if($mod =='gsearch'){$html.=$this->gsearch();}
			if($mod =='search')	{$html.=$this->form().$this->search();}
		}
		
		if($completeLayout) return $this->layout($mod, $title, $html);
					else return $html;            
	}	
        
	/**
	 * set class layout
	 */	
	protected function layout($mod, $title, $html)
	{
		$x = new efalayout($this);
		$x->pattern('1C');
		$x->title($title);
		$x->add($html);
		return $x->html();          
	}        
        
	 /**
	  * read class attributes from request
	  */
	protected function readFromRequest()
	{
		$oMain=$this->oMain;
		$this->tvisitid=$oMain->GetFromArray('tvisitid',$_REQUEST,'int');
		$this->tdatin=$oMain->GetFromArray('tdatin',$_REQUEST,'date');
		$this->tdatout=$oMain->GetFromArray('tdatout',$_REQUEST,'date');
		$this->tnama=$oMain->GetFromArray('tnama',$_REQUEST,'string_trim');
		$this->tentr=$oMain->GetFromArray('tentr',$_REQUEST,'string_trim');
		$this->tregistration=$oMain->GetFromArray('tregistration',$_REQUEST,'string_trim');
		$this->temployee=$oMain->GetFromArray('temployee',$_REQUEST,'int');
		$this->templname=$oMain->GetFromArray('templname',$_REQUEST,'string_trim');
		$this->tdivi=$oMain->GetFromArray('tdivi',$_REQUEST,'string_trim');
		$this->tphone=$oMain->GetFromArray('tphone',$_REQUEST,'string_trim');
		$this->treason=$oMain->GetFromArray('treason',$_REQUEST,'string_trim');
		$this->tremarks=$oMain->GetFromArray('tremarks',$_REQUEST,'string_trim');
		$this->tmodifiedby=$oMain->GetFromArray('tmodifiedby',$_REQUEST,'string_trim');
		$this->tmodifdate=$oMain->GetFromArray('tmodifdate',$_REQUEST,'date');
		
	}
	 /**
	  * form for advanced search
	  */
	protected function form()
	{
		$oMain=$this->oMain;
		$frmMod=CForm::MODE_EDIT;

		$aForm[] = new CFormText($oMain->translate('tvisitid'),'tvisitid', $this->tvisitid,10,CForm::REQUIRED,$CSecVisit_readonly,'',CFormText::INPUT_INTEGER);
		$aForm[]  = new CFormDate($oMain->translate('tdatin'), 'tdatin', $oMain->formatDate($this->tdatin),'',false);
		$aForm[]  = new CFormDate($oMain->translate('tdatout'), 'tdatout', $oMain->formatDate($this->tdatout),'',false);
		$aForm[] = new CFormText($oMain->translate('tnama'),'tnama', $this->tnama,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tentr'),'tentr', $this->tentr,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tregistration'),'tregistration', $this->tregistration,20,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('temployee'),'temployee', $this->temployee,10,'',false,'',CFormText::INPUT_INTEGER);
		$aForm[] = new CFormText($oMain->translate('templname'),'templname', $this->templname,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tdivi'),'tdivi', $this->tdivi,25,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tphone'),'tphone', $this->tphone,25,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('treason'),'treason', $this->treason,10,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tremarks'),'tremarks', $this->tremarks,0,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tmodifiedby'),'tmodifiedby', $this->tmodifiedby,0,'',true,'',CFormText::INPUT_STRING);
		$aForm[]  = new CFormDate($oMain->translate('tmodifdate'), 'tmodifdate', $oMain->formatDate($this->tmodifdate),'',false);
		

		$aForm[] = new CFormButton('butsearch', $oMain->translate ('Search'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);

		$oForm = $oMain->std_form('search', '','frm_search', 3, $frmMod);
		$oForm->addElementsCollection($aForm);
		$html.= $oForm->getHtmlCode() ;
		return($html);
	}

	 /**
	  * Quick module Search query to database
	  */
	protected function gSearch ()
	{
		$oMain=$this->oMain;
		$search=(int)$oMain->gSearch;

		$cond.=" AND (tvisitid='$search')";	

		$sql="SELECT TOP 5000 tvisitid,tdatin,tdatout,tnama,tentr,tregistration,temployee,templname,tdivi,tphone,treason,tremarks,tmodifiedby,tmodifdate
		 FROM dbo.tbsecvisit WITH (NOLOCK) 
		  WHERE 1=1 $cond     ORDER BY tvisitid asc";	
		return($this->showList($sql));
	}
	 /**
	  * advanced Search query to database
	  */
	protected function search()
	{
		$oMain=$this->oMain;
		$cond=""; $start=$cond;

		if($this->tvisitid!='')  $cond.=" AND ( tvisitid like '%$this->tvisitid%' )";	
		

		if($cond==$start) {return('');}

		$sql="SELECT TOP 5000 tvisitid,tdatin,tdatout,tnama,tentr,tregistration,temployee,templname,tdivi,tphone,treason,tremarks,tmodifiedby,tmodifdate
		 FROM dbo.tbsecvisit WITH (NOLOCK) 
		  WHERE 1=1 $cond     ORDER BY tvisitid asc";	
		return($this->showList($sql));
	}
	 /**
	  * list records from search
	  */
	protected function showList($sql)
	{
		$oMain=$this->oMain;
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		for ($r = 0; $r < $rc; $r++)
		{
			$tparam='&tvisitid='.$rs[$r]['tvisitid'];
			$link_tvisitid=$oMain->stdImglink('show_secvisit', '','',$tparam,'',$rs[$r]['tvisitid'],'', $oMain->translate('linktvisitid'));
					
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_secvisit', '', 'tvisitid=§§tvisitid§§'));
	   //$oTable->height($tableheight);               
	   $oTable->data($rs);
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf		   
	   $oTable->columnAdd('tvisitid')->type('int');
		$oTable->columnAdd('tdatin')->type('date');
		$oTable->columnAdd('tdatout')->type('date');
		$oTable->columnAdd('tnama');
		$oTable->columnAdd('tentr');
		$oTable->columnAdd('tregistration');
		$oTable->columnAdd('temployee')->type('int');
		$oTable->columnAdd('templname');
		$oTable->columnAdd('tdivi');
		$oTable->columnAdd('tphone');
		$oTable->columnAdd('treason');
		$oTable->columnAdd('tremarks');
		$oTable->columnAdd('tmodifiedby');
		$oTable->columnAdd('tmodifdate')->type('date');
		
	   $html=$oTable->html();                
	   if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}
} // End CSearch


?>
