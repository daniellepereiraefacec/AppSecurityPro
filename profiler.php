<?php
/**
 *
 * Revisions:
 * 2018-02-01	Luis Gomes		GDPR - Removed personal information links
 * 2017-05-31	Luis Gomes		R1704_00007
 * 2017-05-30	Luis Gomes		R1705_00009 - GetRS2 -> dbQuery
 * 2015-08-28	Manuel Pimentel change baan companies query add year and month
 * 2015-06-30   PB corrected intraneto url on user contacts
 * 2015-01-12	Manuel Pimentel change baan companies query
 * 2014-12-05	Pedro Brandão Show informations from all groups managed by me in all comps
 * 2014-11-27	Pedro Brandão/LG	Minor changes
 * 2014-11-04	Pedro Brandão Profiler 2.0
 * 2013-10-23	Pedro Brandão add $ent='groupman';
 * 2013-10-11	Luis Cruz		UTF-8 version
 * 2013-05-31	Pedro Brandão	gsearch and search return no results if nothing found
 * 2013-03-22	Pedro Brandão	fix gsearch. Now bring the desc of groups
 */

 /**
  * Class CModule
  */
require_once('ccommonsql.php');
class CModule extends CMain
{
	var $gSearch;
	var $oMain;
    var $toExcel;		// Redirects output to  excel
	var $tfield;		//

	//Main Class
   function __construct()
   {
	   
	   
		parent:: __construct(); // use parent method to call parent class

		$this->gSearch	= $this->getFromArray('gsearch',$_REQUEST);
		if($this->getFromArray('toexcel')!=''){$this->toExcel='excel';} // SEND OUTPUT TO EXCEL
		$this->userid   = $this->GetFromArray('userid',$_REQUEST);

//		//compatibilidade para um link existente do antigo profiler
		if ($this->mod=='module')
		{
			$this->mod='listmod_access';
			$this->tmodule = $this->GetFromArray('moduleref',$_REQUEST);
		}
	}
	 /**
	  * main function
	  */
	function getHtml()
	{
		// Draws Standard toolbar
		$oMain=$this;
/*	
		$o=new CSearch($this);
		$form=$o->form();
		$a=array();
		$a[]=array('img/search.png',$oMain->translate('search'),$form,$oMain->translate('search'));
		$oMain->toolbarShowHide($a,'colour');	

		$this->toolbar_icon('img/user.png',$this->BaseLink('','show_users', '', 'userid='.$this->login), $this->translate('userdesc'),'','','',$this->translate('users'));
		$this->toolbar_icon('img/groups2.png',$this->BaseLink('','dash_applications','active'), $this->translate('groupsdesc'),'','','',$this->translate('groups'));
		$this->toolbar_icon('img/apps.png',$this->BaseLink('','list_tmodules','active',''), $this->translate('modulesdesc'),'','','',$this->translate('modules'));
		$this->toolbar_icon('img/org.png',$this->BaseLink('','organization_manage','',''), $this->translate('organizationdesc'),'','','',$this->translate('organization'));
		$this->toolbar_icon('img/info2.png',$this->BaseLink('','admin_netstat','',''), $this->translate('info'),'','','',$this->translate('netstat'));
				
		$this->toolbar_icon('img/user.png',$this->BaseLink('','show_users', '', 'userid='.$this->login), $this->translate('userdesc'),'','','',$this->translate('users'));
		$this->toolbar_icon('img/groups2.png',$this->BaseLink('','dash_applications','active'), $this->translate('groupsdesc'),'','','',$this->translate('groups'));
		$this->toolbar_icon('img/apps.png',$this->BaseLink('','list_tmodules','active',''), $this->translate('modulesdesc'),'','','',$this->translate('modules'));
		$this->toolbar_icon('img/org.png',$this->BaseLink('','organization_manage','',''), $this->translate('organizationdesc'),'','','',$this->translate('organization'));
		$this->toolbar_icon('img/info2.png',$this->BaseLink('','admin_netstat','',''), $this->translate('info'),'','','',$this->translate('netstat'));
*/

		$o=new CSearch($this);
		$form=$o->form();
		
		$a = $this->_hiddenBar->add('xpto1')->content($form);
		$b = $this->_hiddenBar->add('xpto2')->content('bbb');

		$toolbar = $this->_toolbar;
		$toolbar->add('search')->onclick($a->jsShowHide())->title($oMain->translate('search'))->tooltip($oMain->translate('search'))->icon('img/search.png');
		
		//$toolbar->add('users')->link($this->BaseLink('','show_users', '', 'userid='.$this->login))->title($this->translate('users'))->tooltip($this->translate('userdesc'))->icon('img/user.png');
		if($this->accesslevel>=8)
		{
			$toolbar->add('users')->title($this->translate('users'))->tooltip($this->translate('userdesc'))->icon('img/user.png');
			$usersSubs = $toolbar->item('users')->subMenuAdd();	
			$usersSubs->add('usersNew')->link($oMain->BaseLink('','new_users'))->title($oMain->translate('createuser'))->icon('img/new.png');
			$usersSubs->add('usersExt')->link($oMain->BaseLink('','newext_users'))->title($oMain->translate('createuserext'))->efaCicon('user_add.png');
			$usersSubs->add('synusers')->link($oMain->BaseLink('','synusers_netstat'))->title($oMain->translate('synusers'))->icon('img/synusers_s.png');
		}		
		
		$toolbar->add('groups')->link($this->BaseLink('','dash_applications','active'))->title($this->translate('groups'))->tooltip($this->translate('groupsdesc'))->icon('img/groups2.png');
			$groupsSubs = $toolbar->item('groups')->subMenuAdd();
			$groupsSubs->add('groupsSearch')->link($oMain->BaseLink('','search_groups'))->title($oMain->translate('search_groups'))->icon('img/search.png');
			$groupsSubs->add('appNew')->link($oMain->BaseLink('','new_applications'))->title($oMain->translate('createapp'))->icon('img/new.png');
			$groupsSubs->add('groupsNew')->link($oMain->BaseLink('','new_groups', '', 'company='.$oMain->comp))->title($oMain->translate('newgroup'))->icon('img/new_sphere.png');
			$groupsSubs->add('groupsCopy')->link($oMain->BaseLink('','copy_groups', '', 'company='.$oMain->comp))->title($oMain->translate('copygrp'))->icon('img/copy.png');
		$toolbar->add('modules')->link($this->BaseLink('','list_tmodules','active',''))->title($this->translate('modules'))->tooltip($this->translate('modulesdesc'))->icon('img/apps.png');
			$modulesSubs = $toolbar->item('modules')->subMenuAdd();
			$modulesSubs->add('list')->link($oMain->BaseLink('','list_tmodules','active'))->title($oMain->translate('list'))->icon('img/list.png');
			$modulesSubs->add('moduleNew')->link($oMain->BaseLink('','new_tmodules'))->title($oMain->translate('createmodule'))->icon('img/new.png');
			$modulesSubs->add('deploy')->link($oMain->BaseLink('','deploy_tmodules'))->title($oMain->translate('deploy_tmodules'))->icon('img/automation.png');
			$modulesSubs->add('topmodules')->link($oMain->BaseLink('','topmod_netstat'))->title($oMain->translate('topmodules'))->icon('img/fav_s.png');

		$toolbar->add('organization')->title($this->translate('organization'))->tooltip($this->translate('organizationdesc'))->icon('img/org.png');
			$orgaSubs = $toolbar->item('organization')->subMenuAdd();
			$orgaSubs->add('compsOrga')->link($oMain->BaseLink('','comps_proOrga'))->title($oMain->translate('companies'));
			$oSql = new CCommonSql($this);
			$rs2 = $this->dbQuerySQL($oSql->sqlGroupMembersId('efa', 'OPER', 'COMPOWNERS', '', $this->employee));
			if(count($rs2)==1)
				$orgaSubs->add('compsOrgaO')->link($oMain->BaseLink('','compsOwn_proOrga'))->title($oMain->translate('companiesOwners'));
			$orgaSubs->add('divsOrga')->link($oMain->BaseLink('','divs_proOrga'))->title($oMain->translate('divisions'));
			if(count($rs2)==1)
				$orgaSubs->add('divsOrgaO')->link($oMain->BaseLink('','divsOwn_proOrga'))->title($oMain->translate('divisionsOwners'));
		$toolbar->add('info')->link($this->BaseLink('','admin_netstat','',''))->title($this->translate('netstat'))->tooltip($this->translate('info'))->icon('img/info2.png');

		$toolbar->add('admin')->link($this->BaseLink('','profiler_admin','',''))->title($this->translate('admin'))->tooltip($this->translate('admin'))->icon('img/admin.png');
			$Subs = $toolbar->item('admin')->subMenuAdd();
			$Subs->add('compman')->link($oMain->BaseLink('compman',''))->title($oMain->translate('compman'))->icon('img/compman.png');
			$Subs->add('inactiveAccesses')->link($oMain->BaseLink('','xaccess_admin'))->title($oMain->translate('xaccess_admin'))->icon('img/list.png');
			$Subs->add('obsolete_netstat')->link($oMain->BaseLink('','obsolete_netstat'))->title($oMain->translate('obsolete_netstat'))->icon('img/user_obsolete.png');

		$dataArea=$this->dataArea($this);
		//return($this->stdPage($dataArea, "mod=$this->mod|op=$this->operation|userid=$this->userid|groupid=$this->groupid|"));
		return($this->stdPage($dataArea, ''));
	}
	 /**
	  * forward mod to classes
	  */
	
	function dataArea()
	{
		$html=$this->useDHTMLX(); 
		$mod=$this->mod;
		
		if($this->userid === '' || $this->userid === 0) $this->userid = $this->login;
		
		require_once 'cuser.php';
		require_once 'cgroup.php';
		require_once 'capplication.php';
		require_once 'cmodule.php';
		
		if ($mod =='') 
		{
			$mod = 'show_users';	
		}
		if ($mod =='' && 1==0) 
		{ 
			$this->subtitle=$this->translate('welcome').' '.$this->username;
			
			$o = new Cuser($this);
			$o->userid=$this->login;
			
			$o->readfromdb();
			$form=$o->form('update_users');
			
			$sql = $this->getSQLUserCompanies();		//print $sql;
			$rs = dbQuery($this->consql, $sql, $flds,1);
			//var_dump($rs);
			$sql = $this->getSQLCompanyLinks();		//print $sql;
			$rs = dbQuery($this->consql, $sql, $flds,1);
			
			$title=$this->Title($o->userid);
			$html.='<table width=100%><tr valign=top><td width=195 class=row1>'.$this->menuUser($o->userid).'</td>
			<td valign=top>'.$title.'<BR>'.$form.'</td></tr></table>';
			
			$html = $this->layoutUser($form);
		}
		
		if(mb_strstr('|gsearch|search|', '|'.$mod.'|'))
		{	
			$o=new CSearch($this);
			$html.=$o->getHtml($mod);	
		}
			
		$ent='admin';
		if(mb_strstr($mod.'|', "_$ent|"))
		{
			$o=new CProfilerAdmin($this);
			$html.=$o->getHtml($mod); 
		}					
			
		$ent='manage';
		if(mb_strstr($mod.'|', "_$ent|"))
		{
			$o=new CManage($this);
			$html.=$o->getHtml($mod); 
		}					
			
		$ent='file';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
			$o=new CTmodule($this);
			$html.=$o->getHtml($mod); 
		}
			
		
		$ent='userparam';
		if(mb_strstr($mod.'|', "_$ent|"))		
		{	
			$o=new Cuserparameter($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='users';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
			$o=new Cuser($this);
			$html.=$o->getHtml($mod); 
		}
		$ent='userspd';
		if(mb_strstr($mod.'|', "_$ent|"))
		{
			return "<BR><HR>A informação pessoal está disponivel no Portal do Utilizador.<BR>Personal information is available in the User Portal.<HR>";
	
			require_once 'cuserperssonaldata.php';
			$o=new CUserPD($this);
			$html.=$o->getHtml($mod); 
		}
		$ent='userstatit';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
			require_once 'cuserstatit.php';
			$o=new CUserStatit($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='access';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
			$o=new CAccess($this);
			$html.=$o->getHtml($mod); 
		}
		
		
		$ent='groupman';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
			$o=new CGroupMan ($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='groups';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
			$o=new CGroup ($this);
			$html.=$o->getHtml($mod);
		}
		
		$ent='members';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
		
			$o=new CMember ($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='applications';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
		
			$o=new CApplication ($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='tmodules';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
		
			$o=new CTmodule($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='netstat';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
			$o=new CNetStat($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='mmanagers';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
		
			$o=new CModuleMan ($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='modparam';
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
		
			$o=new CModuleParam($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='pageparam'; //Module Links Management
		if(mb_strstr($mod.'|', "_$ent|"))
		{	
		
			$o=new CPageParam($this);
			$html.=$o->getHtml($mod); 
		}
		
		$ent='proOrga'; //Module Links Management
		if(mb_strstr($mod.'|', "_$ent|"))
			{	
			require_once 'profilerOrga.php';
			$o=new CProfilerOrga($this);
			$html.=$o->getHtml($mod); 
			}
		
		return $html;
	}
	
	
	function Title($userid='', $text='')
	{
		if($userid<>'')
		{
			$sql="SELECT userid,company,employee,username FROM dbo.tbusers WHERE userid='$userid'";
			$rs=dbQuery($this->consql, $sql, $flds);
			$text=$rs[0]['employee'].' - '.$rs[0]['username'];
		}
		
		$title='<table width=100% border=0>
		<tr>
		<td width=100% nowrap height=25 valign=top>
			<table class="subtitbar" width=60%>
				<tr><td><img src="img/section_page.png"> <span class="font12B">'.$text.'</span></td></tr>
				<tr class="titlebar" ><td height="3.5px"></td></tr>
			</table>
		</td>
		</tr>
		</table>';
		
		return($title);
	}
	
	
	function getTuserid($userid)  // convert userid to tuserid 
	{	
		$sql="select employee from dbo.tbusers where userid='$userid'";
		$rs=dbQuery($this->consql, $sql, $flds);
		$tuserid=$rs[0]['employee'];
		
		return($tuserid);	
	}
	
	
	public function layoutUser($html)
		{	
		require_once('cuser.php');
		$oUser = new CUser($this);
		$oUser->userid = $this->userid;
		$oUser->readFromDB();

		$x2 = new efalayout($this);
		$x2->pattern('2U')->bgColor('#DCE3E9');		
		$x2->title($oUser->employee.' - '.$oUser->username);
		$x2->icon('img/section_page.png');
		$x2->efaCIcon('FatCow/user.png');
		$x2->add($this->layoutUserMenu($oUser))->width();
		$x2->add($this->layoutUserInfo($oUser))->width('120px');
		
			
		$x = new efalayout($this);
		$x->pattern('2U');
		$x->add($x2->html())->width(350)->paddingLeft(10);
		$x->add($html)->paddingLeft(10);
		return $x->html();
		}
	public function layoutUserInfo($oUser)
		{
		
		$seeefaphone = $this->translate('phone');
		$phone="<a href=\"http://intraapps.efacec.pt/rh/lista/?id=&dbg=0&niv=&por=".$oUser->employee."\" target=_blank title=\"$seeefaphone\">
				<img src=\"img/phone2.png\" border=0 title=\"$seeefaphone\"></a>";
        

		$sendemail=$this->translate('sendemail');
		$email="<a href=\"mailto://".$oUser->email."?tlf=1&por=".$oUser->employee."\" title=\"$sendemail\">
					<img src=\"img/email2.png\" border=0 title=\"$sendemail\"></a>";	
		$events=$this->stdImglink('events_users', '', '', 'userid='.$oUser->userid, 'img/log2.png', '', '', $this->translate('events'));
		
		$x = new efalayout($this->oMain);
		$x->pattern('2E');
		$x->add('<img src="'.$this->stdGetUserPicture($oUser->employee).'" width=100 border=0>')->padding(5);
		$x->add('<div>'.$phone.' '.$email.' '.$events.'</div>')->padding(5);
		return $x->html();
		}
	public function layoutUserMenu($oUser)
		{
		$isSelf = false;
		if($this->userid == $this->login) $isSelf = true;

		$sql = "exec [dbo].[spprofilerdadpessaccess]  '".$this->sid."', '".$this->userid."'";
		$dpAccess = dbQuery($this->consql, $sql, $flds);
		$dpAccess = $dpAccess[0];

		
		$partner = false;
		if(trim($oUser->partner)<>'') $partner = true;
		if(!$dpAccess['isEmployee']) $partner = true;
		
		$eLink = '&userid='.$oUser->userid;
		
		$menu = new efaSideMenu($this);
		if(!$partner) 			
			{
				$tMenu = $menu->add('syne')->icon('img/mypage_l.png')->title($this->translate('SynergyNet'));
			if($this->mod == "") $tMenu->selected(true);
			if(mb_strstr($this->mod.'|', "_users|")) $tMenu->selected(true);
			if(mb_strstr($this->mod.'|', "_userparam|")) $tMenu->selected(true);
			if(mb_strstr($this->mod.'|', "_access|")) $tMenu->selected(true);
			if(mb_strstr($this->mod.'|', "_members|")) $tMenu->selected(true);
			if(mb_strstr($this->mod.'|', "_manage|")) $tMenu->selected(true);
			}
			else $tMenu = $menu;
		

		
		$tMenu->add('deta')->icon('img/user_s.png')->link($this->baseLink('', 'show_users', '', $eLink))->title($this->translate('show_users'));
		$tMenu->add('parm')->icon('img/parameters_s.png')->link($this->baseLink('', 'list_userparam', '', $eLink))->title($this->translate('parameters'));
		$tMenu->add('acce')->icon('img/access_s.png')->link($this->baseLink('', 'listuser_access', '', $eLink))->title($this->translate('accesses'));
		$tMenu->add('memb')->icon('img/member_s.png')->link($this->baseLink('', 'listuser_members', '', $eLink))->title($this->translate('memberof'));
		$tMenu->add('comp')->icon('img/comps_s.png')->link($this->baseLink('', 'listcomp_users', '', $eLink))->title($this->translate('companies'));
		$tMenu->add('umod')->icon('img/apps_s.png')->link($this->baseLink('', 'modules_users', '', $eLink.'&employee='.$o->employee))->title($this->translate('usedmodules'));
		$tMenu->add('admi')->icon('img/admin2_s.png')->link($this->baseLink('', 'admin_users', '', $eLink))->title($this->translate('admin'));
		if($partner) $tMenu->add('dash')->icon('img/partners3_s.png')->link($this->baseLink('', 'dashboard_users', '', $eLink))->title($this->translate('dashboard'));
		$tMenu->add('mana')->icon('img/management_s.png')->link($this->baseLink('', 'show_manage', '', $eLink))->title($this->translate('manage'));
			

		if(!$partner)
			{
//			$tMenu = $menu->add('dadp')->efaCIcon('FatCow/report_user.png')->title($this->translate('Personal data'));
//			if(mb_strstr($this->mod.'|', "_userspd|") && $this->mod!='team_userspd') $tMenu->selected(true);
//		
//			$tMenu->add('situ')->efaCIcon('FatCow/user.png')->link($this->baseLink('', 'situation_userspd', '', $eLink))->title($this->translate('situation'));
//			if($dpAccess['isEmployee'])
//				{
//				if($isSelf || $dpAccess['NIBMOR']) $tMenu->add('addr')->efaCIcon('FatCow/cards_bind_address.png')->link($this->baseLink('', 'addresses_userspd', '', $eLink))->title($this->translate('adress'));
//				if($isSelf || $dpAccess['NIBMOR']) $tMenu->add('nib')->efaCIcon('FatCow/card_bank.png')->link($this->baseLink('', 'nibs_userspd', '', $eLink))->title($this->translate('nibs'));
//				if($isSelf) $tMenu->add('appr')->efaCIcon('FatCow/administrator.png')->link($this->baseLink('', 'approvers_userspd', '', $eLink))->title($this->translate('approvers'));					
//				if($isSelf) $tMenu->add('cruv')->efaCIcon('FatCow/user_student.png')->link($this->baseLink('', 'cv_userspd', '', $eLink))->title($this->translate('CV'));
//				//if($dpAccess['ASC'] || $dpAccess['ASC_LA']) $tMenu->add('alts')->efaCIcon('FatCow/user_edit.png')->link($this->baseLink('', 'altsitcol_userspd', '', $eLink))->title($this->translate('altsitcol'));
//				if($isSelf || (($dpAccess['FAM'] || $dpAccess['FAPMED'])) && $dpAccess['sup']) $tMenu->add('fiam')->efaCIcon('FatCow/medical_record.png')->link($this->baseLink('', 'fam_userspd', '', $eLink))->title($this->translate('fam'));
//				if($isSelf || $dpAccess['BENMED']) $tMenu->add('benm')->efaCIcon('FatCow/pill.png')->link($this->baseLink('', 'benmed_userspd', '', $eLink))->title($this->translate('benmed'));
//				if($isSelf) $tMenu->add('reca')->efaCIcon('FatCow/sallary_deferrais.png')->link($this->baseLink('', 'archrec_userspd', '', $eLink))->title($this->translate('reca'));
//				if($isSelf) $tMenu->add('formh')->efaCIcon('FatCow/user_student.png')->link($this->baseLink('', 'formation_userspd', '', $eLink))->title($this->translate('formHistory'));
//				}

			if($dpAccess["haveTeam"]) 
				{
				$tMenu = $menu->add('team')->efaCIcon('FatCow/group.png')->link($this->baseLink('', 'team_userspd', '', $eLink))->title($this->translate('team'));
				}
			
			
			if($isSelf || $dpAccess['sup'])
				{				
				$tMenu = $menu->add('sIT')->efaCIcon('FatCow/drive.png')->link($this->baseLink('', 'resume_userstatit', '', $eLink))->title($this->translate('StatIT'));
				if(mb_strstr($this->mod.'|', "_userstatit|") && !$this->GetFromArray('team', $_REQUEST, 'int')) $tMenu->selected(true);
				//$tMenu->add('sITassets')->efaCIcon('FatCow/computer.png')->link($this->baseLink('', 'assets_userstatit', '', $eLink))->title($this->translate('Assets'));
				$tMenu->add('sITlicssw')->efaCIcon('FatCow/cd.png')->link($this->baseLink('', 'licssw_userstatit', '', $eLink))->title($this->translate('SWextra'));
				$tMenu->add('sITvpni')->efaCIcon('FatCow/networking.png')->link($this->baseLink('', 'vpni_userstatit', '', $eLink))->title($this->translate('AcessoVPN'));
				$tMenu->add('sITinternet')->efaCIcon('FatCow/network_wireless.png')->link($this->baseLink('', 'internet_userstatit', '', $eLink))->title($this->translate('AcessoInternet'));
				$tMenu->add('sITbaan')->efaCIcon('FatCow/database.png')->link($this->baseLink('', 'baan_userstatit', '', $eLink))->title($this->translate('AcessoBaan'));
				if($dpAccess['empresas_PG']) $tMenu->add('sITowkc')->efaCIcon('FatCow/.png')->link($this->baseLink('', 'owkc_userstatit', '', $eLink))->title($this->translate('AcessoViewsBaan'));
				$tMenu->add('sITcrm')->efaCIcon('FatCow/database_yellow.png')->link($this->baseLink('', 'crm_userstatit', '', $eLink))->title($this->translate('AcessoCRM'));
				$tMenu->add('sITpext')->efaCIcon('FatCow/user_silhouette.png')->link($this->baseLink('', 'pext_userstatit', '', $eLink))->title($this->translate('PessoalExterno'));
				$tMenu->add('sITolap')->efaCIcon('FatCow/database_green.png')->link($this->baseLink('', 'olap_userstatit', '', $eLink))->title($this->translate('AcessoOLAP'));
				if($dpAccess['empresas_PG']) $tMenu->add('sIToper')->efaCIcon('FatCow/.png')->link($this->baseLink('', 'oper_userstatit', '', $eLink))->title($this->translate('AcessoOPER'));
				$tMenu->add('sITpaygest')->efaCIcon('FatCow/database_red.png')->link($this->baseLink('', 'paygest_userstatit', '', $eLink))->title($this->translate('AcessoPaygest'));
				if($isSelf) $tMenu->add('phon')->efaCIcon('FatCow/phone.png')->link($this->baseLink('', 'phones_userstatit', '', $eLink))->title($this->translate('phones'));
				$tMenu->add('sITtelemoveis')->efaCIcon('FatCow/phone.png')->link($this->baseLink('tlm', '', '', ''))->title($this->translate('Telemóveis'))->openInNewWindow(true);
				$tMenu->add('sITshares')->efaCIcon('FatCow/network_folder.png')->link($this->baseLink('', 'shares_userstatit', '', $eLink))->title($this->translate('DonoShares'));
				if($isSelf) $tMenu->add('lat')->efaCIcon('FatCow/location_pin.png')->link('http://intraapps.efacec.com/apps/lat/')->title($this->translate('lat'))->openInNewWindow(true);
				if($isSelf) $tMenu->add('aout')->efaCIcon('FatCow/email_setting.png')->link('http://intraapps.efacec.com/apps/outlooksign/')->title($this->translate('outlookSign'))->openInNewWindow(true);
				if($isSelf) $tMenu->add('shar')->efaCIcon('FatCow/network_folder.png')->link('http://intraapps.efacec.com/apps/shares2/')->title($this->translate('shares'))->openInNewWindow(true);
					
				if($dpAccess["haveTeam"]) 
					{				
					$tMenu = $menu->add('statitt')->efaCIcon('FatCow/drive_user.png')->link($this->baseLink('', 'resume_userstatit', '', $eLink.'&team=1'))->title($this->translate('StatITTeam'));
					if(mb_strstr($this->mod.'|', "_userstatit|") && $this->GetFromArray('team', $_REQUEST, 'int')) $tMenu->selected(true);
					//$tMenu->add('sITTassets')->efaCIcon('FatCow/computer.png')->link($this->baseLink('', 'assets_userstatit', '', $eLink.'&team=1'))->title($this->translate('Assets'));
					$tMenu->add('sITTlicssw')->efaCIcon('FatCow/cd.png')->link($this->baseLink('', 'licssw_userstatit', '', $eLink.'&team=1'))->title($this->translate('SWextra'));
					$tMenu->add('sITTvpni')->efaCIcon('FatCow/networking.png')->link($this->baseLink('', 'vpni_userstatit', '', $eLink.'&team=1'))->title($this->translate('AcessoVPN'));
					$tMenu->add('sITTinternet')->efaCIcon('FatCow/network_wireless.png')->link($this->baseLink('', 'internet_userstatit', '', $eLink.'&team=1'))->title($this->translate('AcessoInternet'));
					$tMenu->add('sITTbaan')->efaCIcon('FatCow/database.png')->link($this->baseLink('', 'baan_userstatit', '', $eLink.'&team=1'))->title($this->translate('AcessoBaan'));
					if($dpAccess['empresas_PG']) $tMenu->add('sITTowkc')->efaCIcon('FatCow/.png')->link($this->baseLink('', 'owkc_userstatit', '', $eLink.'&team=1'))->title($this->translate('AcessoViewsBaan'));
					$tMenu->add('sITTcrm')->efaCIcon('FatCow/database_yellow.png')->link($this->baseLink('', 'crm_userstatit', '', $eLink.'&team=1'))->title($this->translate('AcessoCRM'));
					$tMenu->add('sITtpext')->efaCIcon('FatCow/user_silhouette.png')->link($this->baseLink('', 'pext_userstatit', '', $eLink.'&team=1'))->title($this->translate('PessoalExterno'));
					$tMenu->add('sITTolap')->efaCIcon('FatCow/database_green.png')->link($this->baseLink('', 'olap_userstatit', '', $eLink.'&team=1'))->title($this->translate('AcessoOLAP'));
					if($dpAccess['empresas_PG']) $tMenu->add('sITToper')->efaCIcon('FatCow/.png')->link($this->baseLink('', 'oper_userstatit', '', $eLink.'&team=1'))->title($this->translate('AcessoOPER'));
					$tMenu->add('sITtpaygest')->efaCIcon('FatCow/database_red.png')->link($this->baseLink('', 'paygest_userstatit', '', $eLink.'&team=1'))->title($this->translate('AcessoPaygest'));
					$tMenu->add('sITttelemoveis')->efaCIcon('FatCow/phone.png')->link($this->baseLink('tlm', '', '', ''))->title($this->translate('Telemóveis'))->openInNewWindow(true);
					$tMenu->add('sITtshares')->efaCIcon('FatCow/network_folder.png')->link($this->baseLink('', 'shares_userstatit', '', $eLink.'&team=1'))->title($this->translate('DonoShares'));
					//$tMenu->add('statitgdoc')->efaCIcon('FatCow/.png')->link($this->baseLink('', 'gdoc_userstatit', '', $eLink.'&team=1'))->title($this->translate('AcessoGdoc'));
					}
				}
			}
		

			
		return $menu->html();
		}

	public function menuUser($userid)
		{
		return '';
		}
	function XXXmenuUser($userid)
	{
		
		require_once('cuser.php');
		$o=new CUser($this);
		$o->userid=$userid;
		$o->readFromDB();
		
		$param='&userid='.$userid;
		
		$label=$this->translate('show_users');
		if($this->mod=='' OR $this->mod=='show_users')
			$label='<b>'.$label.'</b>';		
		$user=$this->stdImglink('show_users', '', '', $param, 'img/user_s.png', $label, '', $this->translate('show_users'), '',$this->loading());
		
		$label=$this->translate('parameters');
		if($this->mod=='list_userparam' OR $this->mod=='del_userparam' OR $this->mod=='update_userparam' OR $this->mod=='insert_userparam')
			$label='<b>'.$label.'</b>';
		$parameters=$this->stdImglink('list_userparam', '', '', $param, 'img/parameters_s.png', $label, '', $this->translate('parameters'), '',$this->loading());
		
		$label=$this->translate('accesses');
		if($this->mod=='listuser_access' OR $this->mod=='listalluser_access' OR $this->mod=='updateuser_access' OR $this->mod=='insertuser_access' OR $this->mod=='deluser_access')
			$label='<b>'.$label.'</b>';
		$access=$this->stdImglink('listuser_access', '', '', $param, 'img/access_s.png', $label, '', $this->translate('accesses'), '',$this->loading());
		
		$label=$this->translate('memberof');
		if($this->mod=='listuser_members' OR $this->mod=='listalluser_members' OR $this->mod=='updateuser_members' OR $this->mod=='insert_members' OR $this->mod=='del_members')
			$label='<b>'.$label.'</b>';
		$groups=$this->stdImglink('listuser_members', '', '', $param, 'img/member_s.png', $label, '', $this->translate('memberof'), '',$this->loading());
		
		$label=$this->translate('companies');
		if($this->mod=='listcomp_users' OR $this->mod=='unsetcomp_users' OR $this->mod=='setcomp_users')
			$label='<b>'.$label.'</b>';
		$companies=$this->stdImglink('listcomp_users', '', '', $param, 'img/comps_s.png', $label, '', $this->translate('companies'), '',$this->loading());
		
		$label=$this->translate('usedmodules');
		if($this->mod=='modules_users')
			$label='<b>'.$label.'</b>';
		$modules=$this->stdImglink('modules_users', '', '', $param.'&employee='.$o->employee, 'img/apps_s.png', $label, '', $this->translate('usedmodules'), '',$this->loading());
		
		$label=$this->translate('admin');
		if($this->mod=='admin_users')
			$label='<b>'.$label.'</b>';
		$admin=$this->stdImglink('admin_users', '', '', $param, 'img/admin2_s.png', $label, '', $this->translate('admin'), '',$this->loading());
		
		$partner='';
		if(trim($o->partner)<>'')
			$partner=$this->stdImglink('dashboard_users', '', '', $param, 'img/partners3_s.png', $this->translate('dashboard'), '', $this->translate('dashboard'), '',$this->loading());
		
		$label=$this->translate('manage');
		if($this->mod=='show_security')
			$label='<b>'.$label.'</b>';
		$manage=$this->stdImglink('show_manage', '', '', $param, 'img/management_s.png', $label, '', $this->translate('manage'), '',$this->loading());
		
		$t1Img='<img src="img/search_s.png" title='.$this->translate('search').'>';
		$t20Img='<img src="img/info_s.png" title='.$this->translate('dataproj').'>';
		$bulletl20='<img src="img/bullet_black_s.png" height="5" width="5">';
		$bulletl20='';
		$tuserid=$this->getTuserid($userid);
		$picturehref=$this->stdGetUserPicture($tuserid);
		$picture="<img src=\"$picturehref\" width=100 border=0>";
		
		
		//entrepise phone
		$seeefaphone = $this->translate('phone');
		$phone="<a href=\"http://intraapps.efacec.pt/rh/lista/?id=&dbg=0&niv=&por=$tuserid\" target=_blank title=\"$seeefaphone\">
				<img src=\"img/phone2.png\" border=0 title=\"$seeefaphone\"></a>";
        

		$sendemail=$this->translate('sendemail');
		$email="<a href=\"mailto://$o->email?tlf=1&por=$o->erpuser\" target=_blank title=\"$sendemail\">
					<img src=\"img/email2.png\" border=0 title=\"$sendemail\"></a>";	
		$events=$this->stdImglink('events_users', '', '', 'userid='.$userid, 'img/log2.png', '', '', $this->translate('events'));

		$html="
		<table border=0>
		<tr>
			<td>$picture</td>
			<td width=30%>$phone<BR><BR>$email<BR><BR>$events</td>
		</tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr><td colspan=2>$bulletl20 $user</td></tr>
		<tr><td colspan=2>$bulletl20 $parameters</td></tr>
		<tr><td colspan=2>$bulletl20 $access</td></tr>
		<tr><td colspan=2>$bulletl20 $groups</td></tr>
		<tr><td colspan=2>$bulletl20 $companies</td></tr>
		<tr><td colspan=2>$bulletl20 $modules</td></tr>
		<tr><td colspan=2>$bulletl20 $admin</td></tr>
		<tr><td colspan=2>$bulletl20 $partner</td></tr>
		<tr><td colspan=2>$bulletl20 $manage</td></tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		</table>";			

		return($html);
	}
	
	function menuInfo()
	{
		
		$label=$this->translate('admin');
		if($this->mod=='admin_netstat')
			$label='<b>'.$label.'</b>';		
		$admin=$this->stdImglink('admin_netstat', '', '', '', 'img/user_comm_s.png', $label, '', $this->translate('admindesc'), '',$this->loading());
		
		$label=$this->translate('topmodules');
		if($this->mod=='topmod_netstat')
			$label='<b>'.$label.'</b>';
		$topmod=$this->stdImglink('topmod_netstat', '', '', '', 'img/fav_s.png', $label, '', $this->translate('topmodulesdesc'), '',$this->loading());
		
		$label=$this->translate('activesessions');
		if($this->mod=='active_netstat')
			$label='<b>'.$label.'</b>';
		$activeses=$this->stdImglink('active_netstat', '', '', '', 'img/usersession_s.png', $label, '', $this->translate('activesessionsdesc'), '',$this->loading());
		
		$label=$this->translate('obsoleteusers');
		if($this->mod=='obsolete_netstat')
			$label='<b>'.$label.'</b>';
		$obsolete=$this->stdImglink('obsolete_netstat', '', '', '', 'img/disable_s.png', $label, '', $this->translate('obsoleteusersdesc'), '',$this->loading());
		
		$label=$this->translate('synusers');
		if($this->mod=='synusers_netstat')
			$label='<b>'.$label.'</b>';
		$synusers=$this->stdImglink('synusers_netstat', '', '', '', 'img/synusers_s.png', $label, '', $this->translate('synusers'), '',$this->loading());
		
		$compman=$this->stdImglink('', 'compman', '', '', 'img/compman_s.png', $this->translate('compman'), '', $this->translate('compman'), '',$this->loading());

		
		if($this->mod=='admin_netstat')
			$picture='<img src=img\managers2.png width=100 border=0>';
		if($this->mod=='topmod_netstat')
			$picture='<img src=img\top.png width=100 border=0>';
		if($this->mod=='active_netstat')
			$picture='<img src=img\usersession.png width=100 border=0>';
		if($this->mod=='obsolete_netstat')
			$picture='<img src=img\obsoleteuser.png width=100 border=0>';
		if($this->mod=='synusers_netstat')
			$picture='<img src=img\synusers.png width=100 border=0>';
		
		
		$bulletl20='';

		$html="
		<table border=0>
		<tr>
			<td>$picture</td>
			<td width=30%></td>
		</tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr><td colspan=2>$bulletl20 $admin</td></tr>
		<tr><td colspan=2>$bulletl20 $topmod</td></tr>
		<tr><td colspan=2>$bulletl20 $activeses</td></tr>
		<tr><td colspan=2>$bulletl20 $obsolete</td></tr>
		<tr><td colspan=2>$bulletl20 $synusers</td></tr>
		<tr><td colspan=2>$bulletl20 $compman</td></tr>
		
		<tr><td colspan=2>&nbsp;</td></tr>
		</table>";			

		return($html);
	}
	
	function listAllComps($comp='')
	{
		$this->erpConnect('400');
		
		$company="a.t_comp=$comp";
		if($comp=='')
			$company="a.t_comp>0";
		
		$sql="select a.t_comp Numero,a.t_cpnm [Descrição],case when b.t_virt=1 then 'Virt' else '' end Virtual,
			case when b.t_comp <> a.t_comp then '' else 'Fin' end Financeira,
			case when charindex(right('0'+cast(a.t_comp as varchar(3)),3),t_valr)>0 then 'Hist' else '' end Historica
			,case when b.t_sigl is null then '' else t_sigl end Sigla
			, erplndb.dbo.ef_get_fin_comp (a.t_comp) fin
			, t_ccty pais
			from erplndb.dbo.tttaad100000 a
			left outer join erplndb.dbo.tefcom760400 b on erplndb.dbo.ef_get_fin_comp (a.t_comp)=b.t_comp
			,erplndb.dbo.tefcom925400
			where $company and t_tpid='COMPS' and t_codg='OLD' and a.t_comp<900";
//print $sql;		
		$sql="select a.t_comp Numero,a.t_cpnm [Descrição],case when b.t_virt=1 then 'Virt' else '' end Virtual,
			case when b.t_comp <> a.t_comp then '' else 'Fin' end Financeira,
			case when charindex(right('0'+cast(a.t_comp as varchar(3)),3),t_valr)>0 then 'Hist' else '' end Historica
			,case when b.t_sigl is null then '' else t_sigl end Sigla
			, erplndb.dbo.ef_get_fin_comp (a.t_comp) fin
			,case when charindex(right('0'+cast(a.t_comp as varchar(3)),3),t_valr)>0 then 'Portugal' else c.t_dsca end pais
			,year(t_trdt) Ano, month(t_trdt) Mes
			from erplndb.dbo.tttaad100000 a
			left outer join erplndb.dbo.tefcom760400 b on erplndb.dbo.ef_get_fin_comp (a.t_comp)=b.t_comp
			left outer join erplndb.dbo.ttcmcs010402 c on c.t_ccty=b.t_ccty
			left outer join erplndb.dbo.tefcom925400 on  t_tpid='COMPS' and t_codg='OLD'
			where $company and a.t_comp<900";
			



		$rs=dbQuery($this->conerp, $sql, $flds);
		$rc=count($rs);
		for ($r = 0; $r < $rc; $r++)
		{
			
			$virtual=$rs[$r]['Virtual'];
			if($virtual=='Virt')
			{		
				$rs[$r]['Virtual']='<img src="img\enable_s.png" title="Virtual">';
				if($this->operation=='EXCEL')
					$rs[$r]['Virtual']='X';
			}
			
			$finance=$rs[$r]['Financeira'];
			if($finance=='Fin')
			{
				$rs[$r]['Financeira']='<img src="img\enable_s.png" title="Financeira">';
				if($this->operation=='EXCEL')
					$rs[$r]['Financeira']='X';
			}
			
			$historic=$rs[$r]['Historica'];
			if($historic=='Hist')
			{
				$rs[$r]['Historica']='<img src="img\enable_s.png" title="Financeira">';
				if($this->operation=='EXCEL')
					$rs[$r]['Historica']='X';
			}
			
		}
		
		$oTable = new CTable(null, null, $rs);
		$oTable->SetSorting();
		$oTable->SetFixedHead (true,400);
		$oTable->addColumn($this->translate('company'), 'left', 'String');
		$oTable->addColumn($this->translate('description'), 'left', 'String');
		$oTable->addColumn('Virtual', 'center', 'String');
		$oTable->addColumn($this->translate('finance'), 'center', 'String');
		$oTable->addColumn($this->translate('hist'), 'center', 'String');
		$oTable->addColumn($this->translate('paygest'), 'left', 'String');
		$oTable->addColumn($this->translate('fincomp'), 'left', 'String');
		$oTable->addColumn($this->translate('country'), 'left', 'String');
		$oTable->addColumn($this->translate('ano'), 'center', 'String');
		$oTable->addColumn($this->translate('mes'), 'center', 'String');
		
		$export=$this->stdImglink('exportcomps_manage', '','EXCEL','','img/excel_s.png',$this->translate('export'));
        $sync=$this->stdImglink('synccomp_manage', '','','','sync_s.png',$this->translate('syncerp'),'','','',$this->loading());
		
		$html = $this->efaHR($this->translate('companies').' &nbsp; | &nbsp; '.$export.' | '.$sync);
		
		if($this->operation=='EXCEL')
			$oTable->setOutputToExcel(TRUE);
		
		$html.= $oTable->getHtmlCode ();
		
		return $html;
	}
	
	function listAllDivisions($comp='')
	{	
		$this->erpConnect('400');
		
		$company="t_cmpl='$comp'";
		if($comp=='')
			$company="t_cmpl<>''";
		
		$sql="SELECT t_cmpl, t_divi+' - '+t_desc,t_divi
		FROM intra.dbo.vwb_tefcom750400
		WHERE $company AND getdate()< t_dfim 
		ORDER BY t_dini desc";

		$oTable = new CTable($sql, $this->conerp, NULL);
		$oTable->SetSorting();
		$oTable->SetFixedHead (true,400);
		$oTable->addColumn($this->translate('company'),	'left',	'String');
		$oTable->addColumn($this->translate('division'), 'left', 'String');

		$export=$this->stdImglink('exportdivs_manage', '','EXCEL','','img/excel_s.png',$this->translate('export'));
		$html = $this->efaHR($this->translate('divisions').' &nbsp; | &nbsp; '.$export);
		
		
		if($this->operation=='EXCEL')
			$oTable->setOutputToExcel(TRUE);
		
		
		$html.= $oTable->getHtmlCode ();
		return $html;	
	}
	
	
	
	function organization($comp='')
	{
		$comps=$this->listAllComps($comp);
		$divs=$this->listAllDivisions($comp);
        
        
		
		$html.="<table border=0>
        <tr>
        <td>$comps</td>
		<td width=1%></td>
        <td>$divs</td>
        </tr>
		</table>";
		
		return ($html);
	}
    
    

}// end CManage

class CProfilerAdmin
{
	private $oMain;
	
	public $tuserid;

	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='admin'; 
				
		if ($mod =='delxuser_'.$ent)
		{		
            $tstatus=$this->storeIntoDB('delxuser', $tdesc);
            $oMain->stdShowResult($tstatus, $tdesc);
			$mod ='xaccess_'.$ent;			
		}
				
		if ($mod =='delallxu_'.$ent)
		{		
            $tstatus=$this->storeIntoDB('delallxusers', $tdesc);
            $oMain->stdShowResult($tstatus, $tdesc);
			$mod ='xaccess_'.$ent;			
		}
		
		if ($mod =='xaccess_'.$ent)
		{		
			$oMain->subtitle=$oMain->translate('show_'.$ent).' '.$company;
			
			$x = new efalayout($this);
			$x->title($oMain->translate('admin'))->icon('img/management_s.png');
									
			$x->add($this->showList());
			$html = $x->html();			
		}
	
		return $html;
	
	}

	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->tuserid=$oMain->GetFromArray('tuserid',$_REQUEST,'int');
	}

	protected function storeIntoDB($operation, &$tdesc)
	{
            $oMain = $this->oMain;
            $sid=$oMain->sid;
            $sql="[dbo].[spuseradmin] @sid='$sid',@sp_operation='$operation',@norecordset='0'
			,@tuserid='$this->tuserid'
            "; //var_dump($sql); die;		
            $rs=dbQuery($oMain->consql, $sql, $flds);
            $rst=$rs[0];
            if(isset($rst['tdesc']) && isset($rst['tstatus']))
            {
               $tdesc=$rst['tdesc'];
			   if($operation=='insert')
			   {
					$this->tseqline=$rst['tseqline'];
			   }
            }
            else
            {
                $rst['tstatus'] = -4999;
                $tdesc = 'unknown error';
            }
            return($rst['tstatus']);
	}
		
	protected function showList($op='')
	{
		$oMain=$this->oMain;

		$sql="SELECT DISTINCT UU.username, UU.employee AS tuserid, UU.company AS company, UU.modifdate AS modifdate, '' AS toper 
FROM dbo.tbusers AS UU INNER JOIN
dbo.tbmoduseracc AS AA ON UU.employee = AA.tuserid
WHERE (UU.tstatus = 'X')
ORDER BY  UU.username"; //print $sql;
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);

		foreach($rs as $k=>$v)
		{	//var_dump($rs);
			
			$rs[$k]['toper']=		$oMain->stdImglink('delxuser_admin', '','','&tuserid='.$rs[$k]['tuserid'],'img/delete_s.png','','','','Confirm / Confirma?');
			$rs[$k]['modifdate']=	$oMain->formatdate($rs[$k]['modifdate']);
			$rs[$k]['tuserid']=		$oMain->stdImglink('show_users', '','','&tuserid='.$rs[$k]['tuserid'],'',$rs[$k]['tuserid']);					
		}

		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		
		$oTable->height(220);  
		$oTable->autoExpandHeight(true);
		$oTable->widthUsePercent(true);
		$oTable->data($rs);
		$oTable->multilineRow(true);
		
		$oTable->searchable(false);
		$oTable->exportToExcel(false); $oTable->exportToPdf(false);

		$oTable->columnAdd('username');
		$oTable->columnAdd('tuserid');
		$oTable->columnAdd('company')->title($oMain->translate('company'))->width('5');
		$oTable->columnAdd('modifdate')->width('10');		
		$oTable->columnAdd('toper')->title('!')->width('5');

		$html=$oTable->html();
		$x = new efalayout($this);
		$this->toolbar($mod,$x->toolbar);            
		$x->add($html);
		
		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
		
		return($x->html());
	}	
	
	protected function toolbar($mod,$maintoolbar)
	{
		$oMain=$this->oMain;

		$maintoolbar->add('delallxu_admin')->link($oMain->BaseLink('','delallxu_admin','',''))
				->title($oMain->translate('delallxu_admin'))->tooltip($oMain->translate('explaindelallxu_admin'))
				->efaCIcon('delete.png')->linkConfirm($oMain->translate('confirmdelallxu_admin'));
	}
		
}

class CManage
{
	private $oMain;
	var $company;    /**  */
	var $application;    /**  */
	var $manager;    /**  */
	var $appdescr;    /**  */
	var $obs;    /**  */
	var $modifiedby;    /**  */
	var $modifdate;    /**  */
	var $tstatus;    /**  */
	
	var $copycomp; 
	var $apptocomp;
	var $copymembers;
	
	var $userid;

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class CApplications mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='manage'; 
		$company=$this->company;
        
        if($mod =='synccomp_'.$ent)
		{
			$this->syncCompanies();
            $mod ='organization_'.$ent;
		}
        

		if ($mod =='organization_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			
			$html=$oMain->Title('', 'Organization');
			$html.='<BR>'.$this->formFilterOrg();
	
			if($oMain->operation=='filter')
			{
				$html.=$oMain->organization($this->company);
			}
			else
				$html.=$oMain->organization();
			
			return($html);
		}
		
		if($mod =='exportcomps_'.$ent)
		{
			$oMain->listAllComps();
		}
		
		if($mod =='exportdivs_'.$ent)
		{
			$oMain->listAllDivisions();
		}
		
		
		if ($mod =='showall_'.$ent)
		{
			
			$oMain->subtitle=$oMain->translate('show_'.$ent).' '.$company;

//			$html= $this->showListModules();
//			$html.='<BR>';
			$html.=$this->showlistallGroups();
		}
	

		if ($mod =='show_'.$ent)
		{
			
			$oMain->subtitle=$oMain->translate('show_'.$ent).' '.$company;

			$html= $this->showListModules();
			$html.='<BR>';
			$html.=$this->showlistGroups();
			
			$x = new efalayout($this);
			$x->title($oMain->translate('manage'))->icon('img/management_s.png');
			$x->add($html);
			$html = $x->html();
			
			
		}
		
		
		return $this->oMain->layoutUser($html);
	
		$title=$oMain->Title($this->userid);
		$dashboard='<table width=100%><tr valign=top><td width=195 class=row1>'.$oMain->menuUser($this->userid).'</td>
		<td valign=top>'.$title.$html.'</td></tr></table>';

		return($dashboard);
		
	}
	
	function formFilterOrg()
	{
		$oMain=$this->oMain;
		$form_name='frm_search';
		$frmMod=CForm::MODE_EDIT;

		$aForm[] = new CFormText($oMain->translate('company'),'company', $this->company,3,'','','','CFormText::INPUT_STRING_CODE');
		
		$aForm[] = new CFormButton ('butsearch', $oMain->translate ('filter'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);

		$oForm = $oMain->std_form('organization_manage', 'filter',$form_name, 2, $frmMod,false,'40%');
		$oForm->addElementsCollection($aForm);
		$html.= $oForm->getHtmlCode() ;
		return($html);
	}
	
	 /**
	  * read class CApplications atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->company=$oMain->GetFromArray('company',$_REQUEST,'string_trim');
		$this->application=$oMain->GetFromArray('application',$_REQUEST,'string_trim');
		$this->manager=$oMain->GetFromArray('manager',$_REQUEST,'string_trim');
		$this->appdescr=$oMain->GetFromArray('appdescr',$_REQUEST,'string_trim');
		$this->obs=$oMain->GetFromArray('obs',$_REQUEST,'string_trim');
	//	$this->status=$oMain->GetFromArray('status',$_REQUEST,'string_trim');
		$this->modifiedby=$oMain->GetFromArray('modifiedby',$_REQUEST,'string_trim');
		$this->modifdate=$oMain->GetFromArray('modifdate',$_REQUEST,'date');
		$this->tstatus=$oMain->GetFromArray('tstatus',$_REQUEST,'string_trim');
		$this->copycomp=$oMain->GetFromArray('copycomp',$_REQUEST,'string_trim');
		$this->apptocomp=$oMain->GetFromArray('apptocomp',$_REQUEST,'string_trim');
		$this->copymembers=$oMain->GetFromArray('copymembers');
		
		
		$this->userid=$oMain->GetFromArray('userid',$_REQUEST,'string_trim');
		if($this->userid=='')
			$this->userid=$oMain->login;
	}
	

	function showListModules()
	{
		$oMain=$this->oMain;
		
		$title=$oMain->translate('mysoftware');
		$html.= $oMain->efaHR($title);
		
		$sql="SELECT PP.page, PP.pagedesc
				,(SELECT top 1 [tvalue] FROM [dbo].[tbmodparam] WHERE tmodule=PP.tmodule and tfield='application') as tsoftware
				,'' as lastmodif,PP.trevdate, '' as toper, dbo.efa_uidname(PP.treviewer) AS treviewerdesc
				,PP.unitext, PP.tstatus, PP.tmodule, PP.treviewer, DateDiff(day, PP.trevdate, getdate()) as ndays
			FROM dbo.tbpagemanagers MM INNER JOIN
				 dbo.tbpages PP ON MM.page = PP.page
			WHERE (MM.manager = '$this->userid') AND (MM.company = '$oMain->comp') AND (PP.tstatus = 'A')";	
//PRINT $sql;			
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		
		for ($r = 0; $r < $rc; $r++)
		{
			$tmodule=$rs[$r]['page'];
			$application=$rs[$r]['application'];
			$group_num=$rs[$r]['group_num'];

			$param='tmodule='.$tmodule.'&company='.$oMain->comp.'&userid='.$this->userid;
			

			$rs[$r]['page']=$oMain->stdImglink('show_tmodules', '', '',$param, '', $tmodule, '', $oMain->translate('show_tmodules'));
			$rs[$r]['trevdate']= $oMain->formatDate($rs[$r]['trevdate']).' ('.$rs[$r]['treviewerdesc'].')';
			
			
			if($rs[$r]['ndays']>180)
				$rs[$r]['toper'].=$oMain->stdImglink('review_tmodules', '', '',$param, 'img/red_s.png', '', '', $oMain->translate('explainreviewred'), $oMain->translate('confirmsetreview'));	
			elseif($rs[$r]['ndays']>150)
				$rs[$r]['toper'].=$oMain->stdImglink('review_tmodules', '', '',$param, 'img/grey_s.png', '', '', $oMain->translate('explainreviewgrey'), $oMain->translate('confirmsetreview'));	
			else
				$rs[$r]['toper'].=$oMain->stdImglink('review_tmodules', '', '',$param, 'img/green_s.png', '', '', $oMain->translate('explainreviewgreen'), $oMain->translate('confirmsetreview'));
			
			
			$rs[$r]['toper'].=' '.$oMain->stdImglink('review_tmodules', '', '',$param, 'img/checkblue_s.png','', '', $oMain->translate('explainsetreview'), $oMain->translate('confirmsetreview'));
		}

		$oTable = new CTable(null, null, $rs);
		$oTable->SetSorting();
		//$oTable->SetFixedHead (true,400);
		$oTable->addColumn($oMain->translate('module'), 'left', 'String');
		$oTable->addColumn($oMain->translate('description'), 'left', 'String');
		$oTable->addColumn('Software', 'left', 'String');
		$oTable->addColumn($oMain->translate('lastmodif'), 'left', 'String');
		$oTable->addColumn($oMain->translate('lastrev'), 'left', 'String');
		$oTable->addColumn('!', 'center');
						
		$html.= $oTable->getHtmlCode ();
		
		$title2=$oMain->translate('nosoftwaremanager');
		if($rc==0)
		{$html.= $title2;}
		
		return($html);
	}
	
	
	function showlistGroups()
	{
		$oMain=$this->oMain;
		
		$sql="SELECT '' as tgroup, '' as tdesc, dbo.efa_username(GG.manager) AS managerdesc,
		(SELECT count(tgpid) FROM [dbo].[tbgroupmember] WHERE tgpid=GG.tgpid) as tcount,GG.trevdate, '' as toper,
		DateDiff(day, GG.trevdate, getdate()) as ndays,
		GG.application, GG.groupid, GG.groupdesc, GG.tstatus, GG.tunitext, GG.tunitextrem, GG.tgpid, GG.treviewer, GG.deputy1, GG.deputy2, GG.manager, dbo.efa_uidname(GG.treviewer) AS treviewerdesc
		FROM  dbo.tbgroups GG
		WHERE (GG.company = '$oMain->comp') AND (GG.tstatus <>'X') AND (GG.manager = '$this->userid' OR GG.deputy1='$this->userid' OR GG.deputy2='$this->userid') 	
		UNION
		SELECT '' as tgroup, '' as tdesc, dbo.efa_username(GG.manager) AS managerdesc,
		(SELECT count(tgpid) FROM [dbo].[tbgroupmember] WHERE tgpid=GG.tgpid) as tcount,GG.trevdate, '' as toper,
		DateDiff(day, GG.trevdate, getdate()) as ndays,
		GG.application, GG.groupid, GG.groupdesc, GG.tstatus, GG.tunitext, GG.tunitextrem, GG.tgpid, GG.treviewer, GG.deputy1, GG.deputy2, GG.manager, dbo.efa_uidname(GG.treviewer) AS treviewerdesc
		FROM dbo.tbapplications AS AA INNER JOIN
			 dbo.tbgroups AS GG ON AA.company = GG.company AND AA.application = GG.application
		WHERE AA.company = '$oMain->comp' AND AA.manager = '$this->userid' AND GG.tstatus='A'	
		ORDER BY application, groupid";
		
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		
		for ($r = 0; $r < $rc; $r++)
		{
			$rst=$rs[$r];
			
			$managerdesc=$rst['managerdesc'];
			
			$paramlink='&tnodeid=APP_'.strtoupper($rst['application']).'&tnodeid='.$rst['tgpid'].'&tgpid='.$rst['tgpid'];
			$rs[$r]['tgroup']=$oMain->stdImglink('dash_applications', '', '',$paramlink, '', $rst['application'].'/'.$rst['groupid'], '', '-', '');
			$rs[$r]['tdesc']=$rst['groupdesc'];
			
			$rs[$r]['managerdesc']=$oMain->stdImglink('show_users', '', '','userid='.$rst['manager'], '', $managerdesc, '', '', '');
			
			$rs[$r]['trevdate']= $oMain->formatDate($rs[$r]['trevdate']).' ('.$rst['treviewerdesc'].')';
			
			$param='&userid='.$this->userid.'&tgpid='.$rst['tgpid'].'&company='.$oMain->comp;

			if($rst['ndays']>180)
				$rs[$r]['toper'].=$oMain->stdImglink('review_groups', '', '',$param, 'img/red_s.png', '', '', $oMain->translate('explainreviewred'), $oMain->translate('confirmsetreview'));	
			elseif($rst['ndays']>150)
				$rs[$r]['toper'].=$oMain->stdImglink('review_groups', '', '',$param, 'img/grey_s.png', '', '', $oMain->translate('explainreviewgrey'), $oMain->translate('confirmsetreview'));	
			else
				$rs[$r]['toper'].=$oMain->stdImglink('review_groups', '', '',$param, 'img/green_s.png', '', '', $oMain->translate('explainreviewgreen'), $oMain->translate('confirmsetreview'));	
				
			$rs[$r]['toper'].=' '.$oMain->stdImglink('review_groups', '', '',$param, 'img/checkblue_s.png', '', '', $oMain->translate('explainsetreview'), $oMain->translate('confirmsetreview'));	
		}

		$oTable = new CTable(null, null, $rs);
		$oTable->SetSorting();
		$oTable->SetFixedHead (false,400);
		$oTable->addColumn($oMain->translate('group'), 'left', 'String');
		$oTable->addColumn($oMain->translate('description'), 'left', 'String');
		$oTable->addColumn($oMain->translate('manager'), 'left', 'String');
		$oTable->addColumn($oMain->translate('members'), 'left', 'String');
		$oTable->addColumn($oMain->translate('lastrev'), 'left', 'String');
		$oTable->addColumn('!', 'center');
		
		
		
		$allcomps=$oMain->stdImglink('showall_manage', '', '', 'userid='.$this->userid, 'img/comps_s.png', $oMain->translate('allmygroups'), '', '', '', $oMain->loading());
		
		$title=$oMain->translate('mygroups').'&nbsp; | &nbsp; '.$allcomps;
		$html= $oMain->efaHR($title);
		
		$title2=$oMain->translate('nogroupmanager');
		if($rc==0)
			{$html.= $title2;}
			
		$html.= $oTable->getHtmlCode ();
		
		return($html);

	}
	
	
	function showlistallGroups()
	{
		$oMain=$this->oMain;
		
		$sql="SELECT GG.company, GG.groupid, GG.groupdesc, GG.manager, GG.deputy1, 
				GG.deputy2,
				(SELECT count(tgpid) FROM [dbo].[tbgroupmember] WHERE tgpid=GG.tgpid) as tcount,
				GG.trevdate, '' as toper, GG.tgpid, GG.application, GG.treviewer, GG.obs, DATEDIFF(day, GG.trevdate, GETDATE()) AS tdiff,
				dbo.efa_username(GG.manager) AS managerdesc, dbo.efa_username(GG.deputy1) AS deputy1desc, dbo.efa_username(GG.deputy2) AS deputy2desc, 
				dbo.efa_uidname(GG.treviewer) AS treviewerdesc
			FROM dbo.tbgroups AS GG INNER JOIN
			dbo.tbcompanies AS CO ON GG.company = CO.company
			WHERE (GG.manager = '$this->userid' OR GG.deputy1='$this->userid' OR GG.deputy2='$this->userid') AND (CO.tstatus = 'A') AND (NOT (CO.company IN ('AUT', 'PSH'))) AND (GG.tstatus = 'A')
			ORDER BY GG.company, GG.application, GG.groupid";
		
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		
		for ($r = 0; $r < $rc; $r++)
		{
			$rst=$rs[$r];
			
			$managerdesc=$rst['managerdesc'];
			$deputy1desc=$rst['deputy1desc'];
			$deputy2desc=$rst['deputy2desc'];
			$treviewerdesc=$rst['treviewerdesc'];
			$obs=$rst['obs'];
			
//			
			$paramlink='&tnodeid=APP_'.strtoupper($rst['application']).'&tnodeid='.$rst['tgpid'].'&tgpid='.$rst['tgpid'].'&comp='.$rst['company'];
			$rs[$r]['groupid']=$oMain->stdImglink('dash_applications', '', '',$paramlink, '', $rst['application'].'/'.$rst['groupid'], '', $obs, '');
			$rs[$r]['tdesc']=$rst['groupdesc'];
//			
			$rs[$r]['manager']=$oMain->stdImglink('show_users', '', '','userid='.$rst['manager'], '', $managerdesc, '', '', '');
			$rs[$r]['deputy1']=$oMain->stdImglink('show_users', '', '','userid='.$rst['deputy1'], '', $deputy1desc, '', '', '');
			$rs[$r]['deputy2']=$oMain->stdImglink('show_users', '', '','userid='.$rst['deputy2'], '', $deputy2desc, '', '', '');
			//$rs[$r]['treviewer']=$oMain->stdImglink('show_users', '', '','userid='.$rst['treviewer'], '', $treviewerdesc, '', '', '');
			
		
			$rs[$r]['trevdate']= $oMain->formatDate($rs[$r]['trevdate']).' ('.$rst['treviewerdesc'].')';
//			
			$param='&userid='.$this->userid.'&tgpid='.$rst['tgpid'].'&company='.$rst['company'].'&comp='.$rst['company'];
//
			if($rst['tdiff']>180)
				$rs[$r]['toper'].=$oMain->stdImglink('review_groups', '', 'ALL',$param, 'img/red_s.png', '', '', $oMain->translate('explainreviewred'), $oMain->translate('confirmsetreview'));	
			elseif($rst['tdiff']>150)
				$rs[$r]['toper'].=$oMain->stdImglink('review_groups', '', 'ALL',$param, 'img/grey_s.png', '', '', $oMain->translate('explainreviewgrey'), $oMain->translate('confirmsetreview'));	
			else
				$rs[$r]['toper'].=$oMain->stdImglink('review_groups', '', 'ALL',$param, 'img/green_s.png', '', '', $oMain->translate('explainreviewgreen'), $oMain->translate('confirmsetreview'));	
				
			$rs[$r]['toper'].=' '.$oMain->stdImglink('review_groups', '', 'ALL',$param, 'img/checkblue_s.png', '', '', $oMain->translate('explainsetreview'), $oMain->translate('confirmsetreview'));	
		}

		$oTable = new CTable(null, null, $rs);
		$oTable->SetSorting();
		$oTable->SetFixedHead (false,400);
		$oTable->addColumn($oMain->translate('company'), 'left', 'String');
		//$oTable->addColumn($oMain->translate('application'), 'left', 'String');
		$oTable->addColumn($oMain->translate('groupid'), 'left', 'String');
		$oTable->addColumn($oMain->translate('groupdesc'), 'left', 'String');
		//$oTable->addColumn($oMain->translate('obs'), 'left', 'String');
		$oTable->addColumn($oMain->translate('manager'), 'left', 'String');
		$oTable->addColumn($oMain->translate('deputy1'), 'left', 'String');
		$oTable->addColumn($oMain->translate('deputy2'), 'left', 'String');
		//$oTable->addColumn($oMain->translate('treviewer'), 'left', 'String');
		$oTable->addColumn($oMain->translate('members'), 'left', 'String');
		$oTable->addColumn($oMain->translate('lastrev'), 'left', 'String',10);
		//$oTable->addColumn($oMain->translate('tdiff'), 'left', 'String');
		$oTable->addColumn('!', 'center');
		
		
		
		$allcomps=$oMain->stdImglink('showall_manage', '', '', 'userid='.$this->userid, 'img/comps_s.png', $oMain->translate('allmygroups'), '', '', '', $oMain->loading());
		
		$title=$oMain->translate('mygroups').'&nbsp; | &nbsp; '.$allcomps;
		$html= $oMain->efaHR($title);
		
		$title2=$oMain->translate('nogroupmanager');
		if($rc==0)
			{$html.= $title2;}
			
		$html.= $oTable->getHtmlCode ();
		
		return($html);

	}
    
    function syncCompanies()
    {
        $oMain = $this->oMain;
        
        $sql="select C.company, C.tnama, E.t_cpnm
        from tbcompanies C
        inner join BAANLN10.erplndb.dbo.tttaad100000 E ON E.t_comp=C.company
        where C.tstatus='A' and C.company not in ('efa','ssm','psh','spd')";
        $rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
        $count=0;
        for ($r = 0; $r < $rc; $r++)
        {
            $comp=$rs[$r]['company'];
            $desc=$rs[$r]['tnama'];
            $descerp=$rs[$r]['t_cpnm'];

            if($desc!=$descerp)
            {
                $count++;
                $tstatus=$this->storeIntoDBCompanies($comp,$descerp,$tdesc);
            }
        }
        //print $count;
        if($count>0)
            $oMain->stdShowResult($tstatus, $tdesc);
        elseif($count===0)
            $oMain->SetInfo($oMain->translate('allsynced'));  
       
		
        //return '';
    }
    
    function storeIntoDBCompanies($company, $desc, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="[dbo].[spcompanies] '$sid','sync'
		,'$company'
		,''
		,'$desc'
		";
        //print $sql.'<HR>';
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rst=$rs[0];
		$tdesc=$rst['tdesc'];
		
		return($rst['tstatus']);
	}

}// Enf of CManage







 /**
  * classs used by gsearch and advanced search
  */
class CSearch
{

	public $oMain;
	
	var $userid;    /**  */
	var $company;    /**  */
	var $employee;    /**  */
	var $erpuser;    /**  */
	var $domainuser;    /**  */
	var $username;    /**  */
	var $fullname;    /**  */
	var $email;    /**  */
	var $emailerp;    /**  */
	var $emailalt;    /**  */
	var $status;    /**  */
	var $partner;    /**  */
	var $accesstype;    /**  */
	var $initials;    /**  */
	var $lang;    /**  */
	var $supervisor;    /**  */
	var $separator;    /**  */
	var $modifiedby;    /**  */
	var $modifdate;    /**  */
	var $tstatus;    /**  */
	var $supfunc;    /**  */
	var $gsearch;

	function  __construct($oMain)
	{
		$this->oMain=$oMain;
		$this->gsearch= $oMain->GetFromArray('gsearch',$_REQUEST,'string_trim');
	}

	 /**
	  * gsearch and search setup
	  */
	function getHtml($mod)
	{
		$this->readfromrequest();

		if($mod =='gsearch')	{$html.=$this->gsearch();}
		if($mod =='search')		{$html.=$this->form().$this->search();}

		return($html);
	}	
	 /**
	  * read class atributes from request
	  */
	function readFromRequest()
	{
		$oMain=$this->oMain;
		$this->userid=$oMain->GetFromArray('userid',$_REQUEST,'string_trim');
		$this->company=$oMain->GetFromArray('company',$_REQUEST,'string_trim');
		$this->employee=$oMain->GetFromArray('employee',$_REQUEST,'int');
		$this->erpuser=$oMain->GetFromArray('erpuser',$_REQUEST,'string_trim');
		$this->domainuser=$oMain->GetFromArray('domainuser',$_REQUEST,'string_trim');
		$this->username=$oMain->GetFromArray('username',$_REQUEST,'string_trim');
		$this->fullname=$oMain->GetFromArray('fullname',$_REQUEST,'string_trim');
		$this->email=$oMain->GetFromArray('email',$_REQUEST,'string_trim');
		$this->emailerp=$oMain->GetFromArray('emailerp',$_REQUEST,'string_trim');
		$this->emailalt=$oMain->GetFromArray('emailalt',$_REQUEST,'string_trim');
		$this->partner=$oMain->GetFromArray('partner',$_REQUEST,'string_trim');
		$this->accesstype=$oMain->GetFromArray('accesstype',$_REQUEST,'string_trim');
		$this->initials=$oMain->GetFromArray('initials',$_REQUEST,'string_trim');
		$this->lang=$oMain->GetFromArray('lang',$_REQUEST,'string_trim');
		$this->supervisor=$oMain->GetFromArray('supervisor',$_REQUEST,'string_trim');
		$this->separator=$oMain->GetFromArray('separator',$_REQUEST,'string_trim');
		$this->modifiedby=$oMain->GetFromArray('modifiedby',$_REQUEST,'string_trim');
		$this->modifdate=$oMain->GetFromArray('modifdate',$_REQUEST,'date');
		$this->tstatus=$oMain->GetFromArray('tstatus',$_REQUEST,'string_trim');
		$this->supfunc=$oMain->GetFromArray('supfunc',$_REQUEST,'string_trim');
		
	}
	 /**
	  * form for advanced search
	  */
	function form()
	{
		$oMain=$this->oMain;
		$form_name='frm_search';
		$frmMod=CForm::MODE_EDIT;

		//$html=$oMain->efaHR($oMain->translate('search'));
		//$aForm[] = new CFormTitle($oMain->translate('search'), 'tit'.$form_name);
		$aForm[] = new CFormText($oMain->translate('userid'),'userid', $this->userid,20,'','','','CFormText::INPUT_STRING_CODE');
		$aForm[] = new CFormText($oMain->translate('employee'),'employee', $this->employee,'','',false,'','CFormText::INPUT_INTEGER');
		$aForm[] = new CFormText($oMain->translate('username'),'username', $this->username,40,'',false,'','CFormText::INPUT_STRING_CODE');
		$aForm[] = new CFormText($oMain->translate('email'),'email', $this->email,50,'',false,'','CFormText::INPUT_STRING_CODE');
		
//		$aForm[] = new CFormText($oMain->translate('groupid'),'groupid', $this->groupid,50,'',false,'','CFormText::INPUT_STRING_CODE');
//		$aForm[] = new CFormText($oMain->translate('application'),'application', $this->application,50,'',false,'','CFormText::INPUT_STRING_CODE');
//		$aForm[] = new CFormText($oMain->translate('tmodule'),'tmodule', $this->tmodule,50,'',false,'','CFormText::INPUT_STRING_CODE');
		
		$aForm[] = new CFormButton	('butsearch', $oMain->translate ('Search'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);

		$oForm = $oMain->std_form('search', '',$form_name, 2, $frmMod,false,'80%');
		$oForm->addElementsCollection($aForm);
		$html.= $oForm->getHtmlCode() ;
		return($html);
	}
	

	function gsearch ()
{
	$oMain=$this->oMain;
	$gsearch=$this->gsearch;
	$isearch=(int)$this->gsearch; if($isearch==0) {$isearch=-987654321;}

	$sql="
	SELECT DISTINCT 'user' AS enttype, username as tdesc, userid AS tvalue, 
	'' AS tcomplement , '' AS tcomplement2 FROM dbo.tbusers 
	WHERE (userid = '$gsearch') OR (username LIKE '%$gsearch%') OR 
		(fullname LIKE '%$gsearch%') OR (email LIKE '%$gsearch%') OR 
		(emailerp LIKE '%$gsearch%') OR (employee = '$isearch')
UNION ALL
	SELECT DISTINCT 'module' AS enttype, '' as tdesc, page AS tvalue, '' AS tcomplement , '' AS tcomplement2 FROM dbo.tbpages WHERE (page LIKE '%$gsearch%') OR (pagedesc LIKE '%$gsearch%')
UNION ALL
	SELECT DISTINCT 'application' AS enttype, '' AS tdesc, application AS tvalue, '' AS tcomplement, '' AS tcomplement2 FROM dbo.tbapplications WHERE ((application LIKE '%$gsearch%') OR (appdescr LIKE '%$gsearch%')) AND (company = '$oMain->comp')
UNION ALL
	SELECT DISTINCT 'group' AS enttype, groupdesc AS tdesc, groupid AS tvalue, 
	application AS tcomplement, tgpid as tcomplement2 FROM dbo.tbgroups 
	WHERE ((groupid LIKE '%$gsearch%') OR (groupdesc LIKE '%$gsearch%') OR tgpid=$isearch) AND (company = '$oMain->comp')";
	//print $sql;
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);

	for ($j = 0; $j < $rc; $j++)
	{
		$rst=$rs[$j];
		$tvalue=$rst['tvalue'];
		$suf_title=$oMain->translate($rst['enttype']).' '.$rst['tvalue'];
		$tcomplement=$rst['tcomplement'];
		$tcomplement2=$rst['tcomplement2'];


		if($rst['enttype']=='user')
		{
			if($rc>1)
			{
				$title='Profiler: '.$suf_title;
				$actions='<a href="'.$oMain->BaseLink('profiler','show_users', '', 'user='.$tvalue.'&userid='.$tvalue ).'"><img src="img/user.png" height=25 border=0 title="'.$title.'"></a>';
			}
			else
			{
				$this->pageUndertitle=$oMain->translate('users');

				$oMain->userid = $rst['tvalue'];
				$o = new Cuser($oMain);
				$o->userid=$rst['tvalue'];
				return $o->getHtml('show_users');

				$o->readfromdb();
				$form=$o->form('update_users');

				$title=$oMain->Title($o->userid);

				$html='<table width=100%><tr height=550 valign=top><td width=195 class=row1>'.$oMain->menuUser($o->userid).'</td>
				<td valign=top>'.$title.'<BR>'.$form.'</td></tr></table>';
			}
		}
		elseif($rst['enttype']=='module')
		{

			if($rc>1)
			{
			$title='Profiler: '.$suf_title;
			$actions='<a href="'.$oMain->BaseLink('profiler','show_tmodules', '', 'tmodule='.$tvalue.'&moduleref='.$tvalue ).'"><img src="img/page.png"  height=25 border=0 title="'.$title.'"></a>';
			}
			else
			{
				$this->pageUndertitle=$oMain->translate('modules');
				$oModule = new CTmodule($oMain);
				$oModule->tmodule=$rst['tvalue']; // print $rst['tvalue'].':search';
				$oModule->readfromdb(); 		
				$oMain->subtitle=$oMain->translate('show_tmodules').' '.$oModule->tmodule;	
				$html=$oModule->form('update_tmodules');
				
				$title=$oMain->Title('', $rst['tvalue']);
				$dashboard='<table width=100%><tr valign=top><td width=195 class=row1>'.$oModule->menuModule($oModule->tmodule).'</td>
				<td valign=top>'.$title.'<BR>'.$html.'</td></tr></table>';
				
				return($dashboard);
			}
		}
		elseif($rst['enttype']=='application')
		{
			if($rc>1)
			{
				$title='Profiler: '.$suf_title;
				$tvalueUpper=strtoupper($tvalue);
				$actions='<a href="'.$oMain->BaseLink('profiler','dash_applications', '', 'application='.$tvalue.'&tnodeid=APP_'.$tvalueUpper.'&tappl='.$tvalueUpper ).'"><img src="img/clipboard.png" height=25 border=0 title="'.$title.'"></a>';
			}
			else
			{
				$this->pageUndertitle=$oMain->translate('applications');
				$oApplication = new CApplication($oMain);
				$oApplication->application=$rst['tvalue'];
				$oApplication->tnodeid='APP_'.strtoupper($rst['tvalue']);
				$oApplication->tappl=strtoupper($rst['tvalue']);
				$tnodeid='APP_'.strtoupper($rst['tvalue']);
				$tappl=strtoupper($rst['tvalue']);
				$html=$oApplication->dashboard($tnodeid, $tappl);
			}
		}
		elseif($rst['enttype']=='group')
		{
			if($rc>1)
			{
				$title='Profiler: '.$suf_title;
				$actions='<a href="'.$oMain->BaseLink('profiler','dash_applications', '', 'application='.$tvalue.'&tnodeid='.$tcomplement2.'&tappl='.strtoupper($tcomplement).'&tgpid='.$tcomplement2 ).'"><img src="img/clipboard.png" height=25 border=0 title="'.$title.'"></a>';
			}
			else
			{
				$this->pageUndertitle=$oMain->translate('groups');
				$oApplication = new CApplication($oMain);
				$tnodeid=$rst['tcomplement2'];
				$oMain->default_tab=2;
				$html=$oApplication->dashboard($tnodeid, $tappl);
			}
		}

		$array[]= array(
		'enttype'	=> $oMain->translate($rs[$j]['enttype']).': '.$rs[$j]['tvalue'],
		'tdesc'		=> $rs[$j]['tdesc'],
		'actions'	=> $actions);
	}

//print_r_html($array);
	if(count($array)>1)
	{
		$oTable = new CTable(null, null, $array);
		$oTable->addColumn($oMain->translate('enttype'), 'left', 'String');
		$oTable->addColumn($oMain->translate('description'), 'left', 'String');
		$oTable->addColumn($oMain->translate('actions'), 'left', 'String');

		$oTable->setSorting(true);
		$oTable->setFixedHead(true,390);
		$html .= $oTable->getHtmlCode();
	}
	else if(count($array)==0)
	{
		$html.='<BR>'.$oMain->translate('noresults');
	}

	return($html);
}

	
	function search()
	{
		$oMain=$this->oMain;

		$cond=""; $start=$cond;

		if($this->userid!='')	$cond.=" AND (userid = '$this->userid')";	
		if($this->employee!='') $cond.=" AND (employee = $this->employee)";		
		if($this->username!='') $cond.=" AND (username  COLLATE Latin1_general_CI_AI like '%$this->username%' COLLATE Latin1_general_CI_AI OR fullname COLLATE Latin1_general_CI_AI like '%$this->username%' COLLATE Latin1_general_CI_AI)";		
		if($this->email!='')	$cond.=" AND (email like '%$this->email%' )";	
	
		if($cond==$start) {return('');}

		$sql="SELECT userid,company,employee,username,fullname,email,partner, dbo.translate_code('global_status', tstatus, '$oMain->l') AS tstatusdesc
				,dbo.efa_username(supfunc) AS supfunc,tstatus 
			FROM dbo.tbusers WHERE 1=1 $cond ORDER BY userid asc";	
//print $sql;
		return($this->showList($sql));
	}
	 /**
	  * list records from search
	  */
	function showList($sql)
	{
		$oMain=$this->oMain;
		$conn = $oMain->consql;

		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		
		$ArrayRst=array();
		for ($r = 0; $r < $rc; $r++)
		{
			$rst=$rs[$r];

			$link_userid=$oMain->stdLink('show_users', '','&userid='.$rst['userid'],$rst['userid'],'', $oMain->translate('linkuserid'));

			$ArrayRst[$r]['userid']			= $link_userid;
			$ArrayRst[$r]['company']		= $rst['company'];
			$ArrayRst[$r]['employee']		= $rst['employee'];
			$ArrayRst[$r]['username']		= $rst['username'];
			$ArrayRst[$r]['fullname']		= $rst['fullname'];
			$ArrayRst[$r]['email']			= $rst['email'];
			$ArrayRst[$r]['partner']		= $rst['partner'];
			$ArrayRst[$r]['tstatus']		= $rst['tstatusdesc'];
			$ArrayRst[$r]['supfunc']		= $rst['supfunc'];

		}

		$oTable = new CTable(null, null, $ArrayRst);
		$oTable->SetSorting();
		$oTable->SetFixedHead (false,400);
		$oTable->addColumn($oMain->translate('userid'), 'left', 'String');
		$oTable->addColumn($oMain->translate('company'), 'left', 'String');
		$oTable->addColumn($oMain->translate('employee'), 'left', 'String');
		$oTable->addColumn($oMain->translate('username'), 'left', 'String');
		$oTable->addColumn($oMain->translate('fullname'), 'left', 'String');
		$oTable->addColumn($oMain->translate('email'), 'left', 'String');
		$oTable->addColumn($oMain->translate('partner'), 'left', 'String');
		$oTable->addColumn($oMain->translate('status'), 'left', 'String');
		$oTable->addColumn($oMain->translate('supervisor'), 'left', 'String');


		$html = $oMain->efaHR($oMain->translate('searchresults'));
		$html.= $oTable->getHtmlCode ();

		$txt=$oMain->translate('tasksearchresults');
		$txt = str_replace('$NUMBER$',	$rc,$txt);
		$oMain->subtitle=$txt;
		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
		if($rc==0)
			$html.='<BR>'.$oMain->translate('noresults');

		return($html);
	}
} // End CSearch




// Enf of profiler
?>
