<?php
/*
 * 2016-05-17	Pedro Brandão Minor Changes
 * 2015-07-23	Pedro Brandão	Removed Includes from dhtmlxtree
 * 2015-04-06	Luis Cruz		R1504_00012 - remove sid from tree
 * 2013-12-118	Pedro Brandão	Minor Changes
 * 2013-11-04	Pedro Brandão	Profiler 2.0
 * 2013-12-17	Luis Gomes		Corrected copycompapp [copy memmbers]
 * 2013-10-29	Pedro Brandão	tgpid column in showlist form capplication
 * 2013-10-10	Luis Cruz		UTF-8 version
 * 2012-12-05	Pedro Brandão	New parameter for application events.
 */

require_once('ccommonsql.php');

class CApplication
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
	var $managerdesc;
	
	var $groupid;
	
	var $tnodeid;
	var $tappl;
	var $tgpid;

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
		$this->readFromRequest();
	}

	/**
	 * set class CApplications mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$ent='applications'; 
		$company=$this->company;

		
		if ($mod =='copycompapp_'.$ent)
		{
			$this->application=$this->apptocomp;
			$tstatus=$this->storeIntoDB('copyappcomp', $tdesc); //copy app
			//$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
				//copy group
				$oGroup = new CGroup($oMain);
				$oGroup->readfromrequest();
				$oGroup->application=$this->apptocomp;

				$tstatus=$oGroup->storeintodb('copyappcomp', $tdesc);
//print "ST: $tstatus|".$this->copymembers.'<HR>';
				if($tstatus==0 AND $this->copymembers=='TRUE')
				{
					//copy members
					$oMembers = new CMember($oMain);
					$oMembers->readfromrequest();
					$oMembers->application=$this->apptocomp;
// Do it for all groups					
					$sql="SELECT groupid FROM dbo.tbgroups WHERE (company = '$this->copycomp') AND (application = '$this->apptocomp')";
					$rs=getRS2($oMain->consql, $sql, $flds);
					$rc=count($rs);

					for ($r = 0; $r < $rc; $r++)
					{
						$oMembers->groupid =$rs[$r]['groupid'];
						$tstatus+=$oMembers->storeintodb('copyappcomp', $tdesc);
					}					
					
					$oMain->stdShowResult($tstatus, $tdesc);
					$mod='list_applications';
				}
				else
					$oMain->stdShowResult($tstatus, $tdesc);
					$mod='list_applications';
			}
			else
				$oMain->stdShowResult($tstatus, $tdesc);
				$mod='list_applications';
			
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$mod='list_applications';
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			$mod='dash_applications';
		}


		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->form('insert_applications');
		}
		

		
		if ($mod =='list_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod).' '.$company;
	
			if($oMain->operation=='copyapp')
			{
				$html=$this->copycompappform ();
				$html.=$this->showList();
			}
			else
				$html=$this->showList();	
		}
	

		if ($mod =='events_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showListEvents();
		}
		
		if ($mod =='dash_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			if($oMain->operation=='copymemb')
			{
				$oMain->default_tab=2;
			}
			
			$html=$this->dashboard();	
		}
		
		$oMain->toolbar_icon('img/new.png',$oMain->BaseLink('','new_applications'), $oMain->translate('createapp'),'','','',$oMain->translate('createapp'));

		if($oMain->accesslevel>=8)
		{
			$expand='';
			if($oMain->operation=='copyapp')
				$expand=TRUE;
			
			$b=array();
			$b[]=array('img/copy2.png',$oMain->translate('copyapp'),$this->copycompappform(),$oMain->translate('copyapp'),$expand);
			$oMain->toolbarShowHide($b);
		}
		
		$oMain->toolbar_icon('img/new_sphere.png',$oMain->BaseLink('','new_groups', '', 'company='.$oMain->comp), $oMain->translate('newgroup'),'','','',$oMain->translate('newgroup'));
		$oMain->toolbar_icon('img/copy.png',$oMain->BaseLink('','copy_groups', '', 'company='.$oMain->comp), $oMain->translate('copygrp'),'','','',$oMain->translate('copygrp'));
			
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
		
		$this->groupid=$oMain->GetFromArray('groupid',$_REQUEST,'string_trim');
		
		$this->tnodeid=$oMain->GetFromArray('tnodeid',$_REQUEST,'string_trim');
		$this->tappl=$oMain->GetFromArray('tappl',$_REQUEST,'string_trim');
		$this->tgpid=$oMain->GetFromArray('tgpid',$_REQUEST,'int');
	}
	
	function storeIntoDB($operation, &$tdesc)
	{
		$sid=$this->oMain->sid;
		$sql="[dbo].[spapplications]'$sid','$operation','$this->company'
		,'$this->application'
		,'$this->manager'
		,'$this->appdescr'
		,'$this->obs'
		,'$this->tstatus'
		,'$this->copycomp'
		";

//print $sql;
		$rs=getRS2($this->oMain->consql, $sql, $flds);
		$rst=$rs[0];
		$tdesc=$rst['tdesc'];
		return($rst['tstatus']);
	}

	
	function readfromdb()
	{
		$oMain = $this->oMain;
		$sql="SELECT company,application,dbo.efa_username(manager) AS managerdesc,appdescr,obs,dbo.efa_username(modifiedby) AS modifiedby,modifdate,tstatus,manager
			FROM dbo.tbapplications
			WHERE company='$oMain->comp' and application='$this->application'";	
		$rs=getRS2($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->company=$rst['company'];
			$this->application=$rst['application'];
			$this->manager=$rst['manager'];
			$this->managerdesc=$rst['managerdesc'];
			$this->appdescr=$rst['appdescr'];
			$this->obs=$rst['obs'];
	//		$this->status=$rst['status'];
			$this->modifiedby=$rst['modifiedby'];
			$this->modifdate=$rst['modifdate'];
			$this->tstatus=$rst['tstatus'];
			
		}
		return $rc;
	}

	function dashboard($tnodeid=0,$tappl='')
	{
		$oMain=$this->oMain;
		$lContent = new efalayout($this);
		if($tnodeid<>0)
			$tgpid=$tnodeid;
		else
			$tgpid=$this->tgpid; //$oMain->GetFromArray('tgpid',$_REQUEST,'int');

		if($tappl=='')
			$tappl=$oMain->GetFromArray('tappl',$_REQUEST, 'string_trim');
		
		$applic=	mb_substr($tappl, 0, 1);
		$applic2=	mb_substr($tappl, 1);
		$applic2=	strtolower($applic2);
		$application=$applic.$applic2;
		
		if($tappl<>'') {$lContent->title($application);}
	
		if($tgpid==0 and $tappl=='')
		{
			switch($oMain->operation)
			{
				case 'manage': $lContent->title($oMain->translate('appsman')); break;
				case 'inactive': $lContent->title($oMain->translate('disabledapps')); break;
				default: $lContent->title($oMain->translate('allactiveapp')); 
			}
			$lContent->toolbar->add('all')->link($this->oMain->BaseLink('', 'dash_applications', 'active'))->title($this->oMain->translate('allactiveapp'))->efaCIcon('FatCow/application.png');
			$lContent->toolbar->add('man')->link($this->oMain->BaseLink('', 'dash_applications', 'manage'))->title($this->oMain->translate('appsman'))->efaCIcon('FatCow/application_key.png');
			$lContent->toolbar->add('dis')->link($this->oMain->BaseLink('', 'dash_applications', 'inactive'))->title($this->oMain->translate('disabledapps'))->efaCIcon('FatCow/application_delete.png');
		}
				
		if($tgpid==0 && $tappl=="")
		{ 
			$right=$this->showList(); 		
		}
		else if ($tgpid>0)
		{ 			
			$ogrp=new CGroup($oMain);
			$ogrp->tgpid=$tgpid;		
			$right=$ogrp->groupdatatabs(); 
			$lContent->title($ogrp->company.'/'.$ogrp->application.'/'.$ogrp->groupid.' ['.$ogrp->groupdesc.']');	
		}
		else
		{ 
			$this->application=$tappl;
			$this->readFromDB(); 
			$company=$this->company;
			$oMain->subtitle=$oMain->translate('show_applications');
			$right=$this->form('update_applications', 'TREEVIEW');
		}

		$lContent->add($right)->padding(5);		

		$pMain = new efalayout($this);
		$pMain->pattern('2U');
		$pMain->add($this->appTree($tnodeid))->padding(5)->width(300);
		$pMain->add($lContent->html())->padding(5);
		return $pMain->html();		
	}		
	
	
	function showList()
	{
		$oMain=$this->oMain;
		$conn = $oMain->consql;
		
		/*
		if($this->tstatus=='A'||$this->tstatus=='')
			{$cond="APPL.tstatus<>'X'";}
		elseif ($this->tstatus=='X')				
			{$cond="APPL.tstatus='X'";}
		*/
		if($oMain->operation=='inactive') $cond="APPL.tstatus='X'";
			else $cond="APPL.tstatus<>'X'";
		
		if($oMain->operation=='manage')	
		$sql="
			SELECT APPL.application, USR.username, APPL.appdescr, APPL.obs AS remarks,
			(SELECT COUNT(groupid) FROM dbo.tbgroups WHERE (company = APPL.company) AND (application = APPL.application) AND (tstatus <> 'X')) AS group_num, 
			APPL.manager,APPL.tstatus
			FROM dbo.tbapplications AS APPL LEFT OUTER JOIN
				dbo.tbusers AS USR ON USR.userid = APPL.manager
			WHERE (APPL.company = '$oMain->comp') AND APPL.tstatus<>'X' AND APPL.manager='$oMain->login'";	
			
		else		
		$sql="
			SELECT APPL.application, USR.username, APPL.appdescr, APPL.obs AS remarks,
			(SELECT COUNT(groupid) FROM dbo.tbgroups WHERE (company = APPL.company) AND (application = APPL.application) AND (tstatus <> 'X')) AS group_num, 
			APPL.manager,APPL.tstatus
			FROM dbo.tbapplications AS APPL LEFT OUTER JOIN
				dbo.tbusers AS USR ON USR.userid = APPL.manager
			WHERE (APPL.company = '$oMain->comp') AND $cond";
//print $sql;
		$rs=getRS2($oMain->consql, $sql, $flds);
		$rc=count($rs);
		
		for ($r = 0; $r < $rc; $r++)
		{

			$application=$rs[$r]['application'];
			$group_num=$rs[$r]['group_num'];

			$param='application='.$application.'&company='.$company.'&tappl='.$application.'&tnodeid=APP_'.strtoupper($application);
			
			$manager=$rs[$r]['manager'].' - '.$rs[$r]['managerdesc'];
			
			$rs[$r]['manager']=$manager;

			$rs[$r]['application']=$oMain->stdImglink('dash_applications', '', '',$param, '', $application, '', $oMain->translate('editapp'), '');
			$rs[$r]['group_num']=$oMain->stdImglink('dash_applications', '', '',$param.'&default_tab=2', '', $group_num, '', $oMain->translate('editgroups'), '','');

			//$rs[$r]['toperations']=$rs[$r]['toperations']=$oMain->stdImglink('edit_groups', '', '','tgpid='.$tgpid, 'img/edit_s.png', '', '', $oMain->translate('editgroups'), '');	
			//$rs[$r]['modifieddate']= $oMain->formatDate($rs[$r]['modifieddate']);

		}

//		$oTable = new CTable(null, null, $rs);
//		$oTable->SetSorting();
//		$oTable->SetFixedHead (false,400);
//		$oTable->addColumn($oMain->translate('application'), 'left', 'String');
//		$oTable->addColumn($oMain->translate('manager'), 'left', 'String');
//		$oTable->addColumn($oMain->translate('t_desc'), 'left', 'String');
//		$oTable->addColumn($oMain->translate('obs'), 'left', 'String');
//		$oTable->addColumn($oMain->translate('groupnumber'), 'left', 'String');
//		
//		$html.= $oMain->efaHR();
//		
//		$html.= $oTable->getHtmlCode ();
//
//		$txt=$oMain->translate('Applist').' '.$oMain->translate('fromcomp').' '.$oMain->comp;
//		$txt = str_replace('$NUMBER$',	$rc,$txt);
//		$oMain->subtitle=$txt;
		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		$x = new efaGrid($this->oMain);
		$x->height(490); 
		//$x->title($this->oMain->translate('Nums'));
		$x->exportToExcel(false);
		$x->exportToPDF(false);
		$x->data($rs);
		return $x->html();
	}
	
	
	function switchCompany()
	{
		$oMain=$this->oMain;
		
		$html='<table width=100%><TR>'.$oMain->stdForm('dash_applications','',"frmapplist");

		$html.='<TD><select name="tcompany" onchange="frmapplist.comp.value=frmapplist.tcompany.value; frmapplist.submit();">'
				. '<option  value="default" selected>'.$oMain->translate('company').' - '.$oMain->comp.'</option>';
		
		$sql="SELECT company, company +' '+ shortdesc AS tdesc FROM dbo.tbcompanies WHERE tstatus='A' Order by company";
		$rs=getRS2($oMain->consql, $sql, $flds);
		$rc=count($rs);
		for ($r = 0; $r < $rc; $r++)
		{
			$comp=$rs[$r]['company'];
			$text=$rs[$r]['tdesc'];
			$html.='<option value="'.$comp.'">'.$text.'</option>';		
		}	
		
		$html.='</select></td></form></tr></table>';

		return $html;
	}
	
	
	
	function appTree($tnodeid='')
	{
		$oMain=$this->oMain;
		
		$tlink='';
		if($tnodeid=='')
			$tnodeid=trim($oMain->GetFromArray('tnodeid',$_REQUEST));
		if($tnodeid<>'' AND $oMain->operation<>'root' AND $oMain->mod<>'show_docref')
			$tlink.='&tnodeid='.$tnodeid;

		//$params='comp='.$oMain->comp.'&sid='.$oMain->sid.'&l='.$oMain->l.$tlink;
		$params='comp='.$oMain->comp.$tlink;
		$xmlData='capplicationtree.php?page=profiler&mod='.$oMain->mod.'&'.$params; //print "$xmlData";
	
		/*
		$html="<table cellspacing=0 cellpadding=0 width=\"100%\"><tr><td nowrap>
					<a href=\"javascript:void(0);\" onclick=\"tree.openAllItems(0);\">".$oMain->translate('expand')."</a> |
					<a href=\"javascript:void(0);\" onclick=\"tree.closeAllItems(0); tree.openItem('dashboard');\">".$oMain->translate('collapse')."</a>
			</td></tr></table>";
		*/
		
		$html.='<table border="0" width="100%">
				<tr>
				<td valign="top" width=1% >';
		//tree_declare
		$html.='<form name="testtt" action="efrfqexttree.php">
				<div id="treeboxbox_tree2" style="width:300; height:520px;border :1px solid Silver; background-color:#f5f5f5;  overflow:auto;"></div>
				</form>';
		//getHtmltree
		$html.='
		<script type="text/javascript">
			tree = new dhtmlXTreeObject("treeboxbox_tree2","100%","100%",0);
			tree.setSkin(\'vista\');
			tree.setImagePath("./img/treeview/");
			tree.enableCheckBoxes(0);	
			tree.enableAutoTooltips(true);			
			tree.enableDistributedParsing(true); //loads items by sections that way the person does not need to wait for all to load
			tree.enableHighlighting(true);//NEW
			tree.loadXML("'.$xmlData.'");
					tree.attachEvent("onClick",function(id)
					{
						var id = tree.getSelectedItemId();
						var mod = tree.getUserData(id, "mod");
						var operation = tree.getUserData(id, "operation");
						var tnodeid = tree.getUserData(id, "tnodeid");
						var trefa = tree.getUserData(id, "trefa");
						var tgpid = tree.getUserData(id, "tgpid");
						var tappl = tree.getUserData(id, "tappl");
						var default_tab = tree.getUserData(id, "default_tab");

						var textralink="";
						var toperlink="";
						
						if(operation !== undefined)
						{
							toperlink = "&operation="+operation
						}

						if(default_tab !== undefined)
						{
							toperlink = "&default_tab="+default_tab
						}

						if(tnodeid !== undefined)
						{
							textralink+= "&tnodeid="+tnodeid
						}

						if(trefa !== undefined)
						{
							textralink+= "&trefid="+trefa
						}
						if(tgpid !== undefined)
						{
							textralink+= "&tgpid="+tgpid
						}
						if(tappl !== undefined)
						{
							textralink+= "&tappl="+tappl
						}
						
						window.location="efa.php?page=profiler&mod="+mod+toperlink+"&'.$params.'"+textralink,"treeboxbox_tree2";
					});
		</script>';

		$html.='</td>
				</tr></table>';
		//$html.='<div id="ajax1" style="width:100%;height:100%;border:0px" >';//height:100%;
		
		
		$pLeft = new efalayout($this);
		//$pLeft->pattern('2E');
		//$pLeft->add($this->switchCompany());
		$pLeft->title($this->switchCompany());
		$pLeft->toolbar->add('expand')->onclick('tree.openAllItems(0);')->title($this->oMain->translate('expand'))->efaCIcon('FatCow/bullet_toggle_plus.png');
		$pLeft->toolbar->add('contract')->onclick('tree.closeAllItems(0);')->title($this->oMain->translate('collapse'))->efaCIcon('FatCow/bullet_toggle_minus.png');
		

					
		$pLeft->add($html);		
		return $pLeft->html();
		
		return ($html);
	}
		
	
	
	function copycompappform()
{
	$oMain=$this->oMain;
	
	if($oMain->accesslevel>=0)
	{
		$this->readfromrequest();

		$loading="document.getElementById('pleaseWait').style.display='block'; return true;";

		//company listbox
		$sql="SELECT company, company + ' - ' + shortdesc AS shortdesc FROM dbo.tbcompanies WHERE (tstatus <> 'X') AND (company<>'$oMain->comp')";
		$company = new CFormSelect($this->oMain->translate('copyfromcomp'), 'copycomp', $this->copycomp, '', $sql, $oMain->consql, '', '', ' ', CForm::REQUIRED);
		$company->addEvent("onChange=\"frm_copycompapp.mod.value='dash_applications'; frm_copycompapp.mod.operation='copyapp'; javascript:frm_copycompapp.submit(); $loading\"");

		//applications listbox
		$sql="SELECT application, application AS tdesc FROM dbo.tbapplications WHERE (company = '$this->copycomp')";
		$app_listbox = new CFormSelect($this->oMain->translate('application'), 'apptocomp', '', '', $sql, $oMain->consql, '', '', ' ', CForm::REQUIRED);

		$formEdit = $oMain->std_form('copycompapp_applications', 'copyapp','frm_copycompapp',3,CForm::MODE_EDIT, false, '100%');
		$form_elem = array();
		$form_elem[] = $company;
		$form_elem[] = $app_listbox;
		$form_elem[] = new CFormCheckBox($oMain->translate('members'), 'copymembers', 'TRUE');
		$formEdit->addElementsCollection($form_elem);

		$button_new =  new CFormButton('button', $this->oMain->translate('copyapp'), CFormButton::TYPE_SUBMIT, '', CFormButton::LOCATION_FORM_RIGHT);
		$formEdit->addElement($button_new);

		$copyaccess_form = $formEdit->getHtmlCode();
		
		
		return $copyaccess_form;

	}
}
	

function applicationgroups($application)
{
	//print $application;
	
	$array=array();
	$oMain=$this->oMain;
	$sql="
	SELECT GRP.groupid, GRP.groupdesc, GRP.manager, USR.username, GRP.tstatus, GRP.company, GRP.profile
	FROM dbo.tbgroups AS GRP 
	LEFT OUTER JOIN dbo.tbusers AS USR ON USR.userid = GRP.manager
	WHERE (GRP.company = '$oMain->comp') AND (GRP.application = '$application')";
	//print $sql;
	
	$rs=getRS2($oMain->consql, $sql, $flds);
	$rc=count($rs);

	for ($j = 0; $j < $rc; $j++)
	{
		$rst=$rs[$j];
		$manager = $oMain->std_link($rst['manager'].' - '.$rst['username'], false, 'user', '', 'user='.$rst['manager'].'&userid='.$rst['manager'], '', '', $oMain->translate('seedetails'));

		$group = $oMain->std_link($rst['groupid'], false, 'group', '', 'groupapp='.$application.'&group='.$rst['groupid'].'&application='.$application.'&groupid='.$rst['groupid'].'&inactives='.$this->inactives, '', '', $oMain->translate('seedetails'));

		if(trim(mb_strtoupper($rst["tstatus"]))=="X")
		{
			//$group = "<a href=\"$oMain->PAGNAME?PAG=$oMain->PAG&SID=$oMain->SID&Mod=GROUP&company=$rst[company]&application=$application&groupid=$rst[groupid]&groupdesc=$rst[groupid]&inactives=ON\" title=\"".$oMain->translate("seedetails")."\"><font class=stock_negativo>$rst[groupid]</font></a>";
			$group = $oMain->std_link('<font class=tab_negative>'.$rst['groupid'].'</font>', false, 'group', '', 'groupapp='.$application.'&group='.$rst['groupid'].'&application='.$application.'&groupid='.$rst['groupid'].'&inactives='.$this->inactives, '', '', $oMain->translate('seedetails'));
			$manager = $oMain->std_link('<font class=tab_negative>'.$rst[manager].' - '.$rst[username].'</font>', false, 'user', '', 'user='.$rst[manager].'&userid='.$rst['manager'], '', '', $oMain->translate('seedetails'));
			$rst['groupdesc'] = "<font class=stock_negativo>$rst[groupdesc]</font>";
		}

		$array[]= array('groupid'	=> $group,
						'groupdesc'	=> $rst['groupdesc'],
						'profile'	=> $rst['profile'],
						'manager'	=> $manager);
	}

	$oTable = new CTable(null, null, $array);
	$oTable->addColumn($oMain->translate('group'), 'left', 'String');
	$oTable->addColumn($oMain->translate('description'), 'left', 'String');
	$oTable->addColumn($oMain->translate('profile'), 'left', 'String');
	$oTable->addColumn($oMain->translate('manager'), 'left', 'String');

	$oTable->setSorting(true);
	$oTable->setFixedHead(true,500);

	$html = $oTable->getHtmlCode();

	$content= array('title'		=> $oMain->translate('groups'),
					'xls'		=> '<input type=image src=img/blank_s.png>',
					'contents'	=> $html);

	return ($content);
}

function form($mod='show_applications', $op='')
{
	$oMain=$this->oMain;
	$oCC=new CCommonSql($oMain);
	$html_form=$oMain->stdJsPopUpWin('400');
	$formName='frmCApplications'; $operation='';$nCol=2;$width='100%';$ajax=false;
	$modCancel='show_applications';
	$modChange='x'.$mod;
	$frmMod=CForm::MODE_EDIT;
	if($mod=='show_applications')
		$frmMod=CForm::MODE_VIEW;

	$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
	$aForm = array();

	if($mod=='insert_applications')
		$CApplications_readonly=false;
	else
		$CApplications_readonly=true;


	$modifdate=$oMain->formatDate($this->modifdate);
	$events=$oMain->stdImglink('events_applications', '', '', 'application='.$this->application, '', $oMain->translate('events'), '', $oMain->translate('events'));
	$info='<img src=img\info_s.png title="Modified by: '.$this->modifiedby.' @ '.$modifdate.'">';
	$aForm[] = new CFormTitle($oMain->translate('application').' - '.$this->application.' &nbsp; | &nbsp; '.$events.' &nbsp; | &nbsp; '.$info, 'tit'.$formName);
	
//general
	if($mod=='insert_applications')
		$company=$oMain->comp;
	else
		$company=$this->company;

	$aForm[] = new CFormHidden('tnodeid','APP_'.strtoupper($this->application));
	$aForm[] = new CFormHidden('tappl',  strtoupper($this->application));

	if($mod=='insert_applications')
	{
		$aForm[] = new CFormText($oMain->translate('company'),'company', $company,5,CForm::REQUIRED,true,'',CFormText::INPUT_STRING_CODE);
		$aForm[] = new CFormText($oMain->translate('application'),'application', $this->application,15,CForm::REQUIRED,$CApplications_readonly,'',CFormText::INPUT_STRING_CODEI);
	}
	else
	{
		$aForm[] = new CFormHidden('company', $this->company);
		$aForm[] = new CFormHidden('application', $this->application);
	}
		

	$search_manager=$oMain->stdPopupwin('GETCCUSER',$formName,'manager','managerdesc','manager','managerdesc','','');	
	$field_manager = new CFormText($oMain->translate('manager'),'manager', $this->manager,'',CForm::REQUIRED,false);
	$field_manager_desc = new CFormText($oMain->translate('manager'), 'managerdesc', $this->managerdesc, '','',false, '', '', '', 70);
	if($frmMod==CForm::MODE_EDIT)
	   $field_manager->setExtraData($search_manager);
	$aForm[] = new CFormMultipleElement(array($field_manager, $field_manager_desc), 0);	

	$aForm[] = new CFormText($oMain->translate('t_desc'),'appdescr', $this->appdescr,30,'',false,'',CFormText::INPUT_STRING);

	$atextForm = new CFormTextArea($oMain->translate('obs'), 'obs', $this->obs, 5,CForm::RECOMMENDED);
	$atextForm->setNumberOfColumns(1);
	$aForm[]=$atextForm;

	if($mod=='insert_applications')
		$this->tstatus='A';
	$sql="SELECT codeid, dbo.translate_optional(valunitext, '$oMain->l', codeid) AS codetxt FROM dbo.tbcodes WHERE (codetype = 'global_status')";
	$aForm[] = new CFormSelect($this->oMain->translate('tstatus'), 'tstatus', trim($this->tstatus), $this->tstatus, $sql, $oMain->consql, '', '', ' ', CForm::REQUIRED);


//	if($mod!='insert_applications')
//	{
//		$aForm[] = new CFormText($oMain->translate('modifiedby'),'modifiedby', $this->modifiedby,20,'',true,'',CFormText::INPUT_STRING_CODE);
//		$aForm[] = new CFormText($oMain->translate('modifdate'),'modifdate', $oMain->formatDate($this->modifdate),4,'',true,'',CFormText::INPUT_DATE);
//	}

	//form buttons
	$onSubmit="$formName.submit(); $oMain->loading;";
	$buttonSave = new CFormButton('save', $oMain->translate ('save'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);
	$aForm[]=$buttonSave;
//		$buttonCancel = new CFormButton('cancel', $oMain->translate ('cancel'),CFormButton::TYPE_BUTTON,'',CFormButton::LOCATION_FORM_BOTTOM);
//		$buttonCancel->addEvent("onclick=\"$formName.mod.value='$modCancel';$onSubmit\"");
//		$aForm[]=$buttonCancel;
	$oForm->addElementsCollection($aForm);
	// $html_form.=$this->form_toolbar($mod);
	$html_form.=$oForm->getHtmlCode();

	IF($op=='TREEVIEW')
		return $html_form.$this->showListAppGroups($this->application);
	ELSE
		return $html_form;

}


function formFilterAppGrpxxx()
{
	$oMain=$this->oMain;
	
	$formEdit = $oMain->std_form('dash_applications', 'filter', $formName,1,CForm::MODE_EDIT);
	$aForm = array();
	$aForm[] = new CFormHidden('application', $this->application);
	$aForm[] = new CFormHidden('tnodeid', $this->tnodeid);
	$aForm[] = new CFormHidden('tappl', $this->tappl);
	
	
	$aForm[] = new CFormText($oMain->translate('groupid'), 'groupid', $this->groupid, 50);
	$formEdit->addElementsCollection($aForm);

	$button_new =  new CFormButton('button', $this->oMain->translate('search'), CFormButton::TYPE_SUBMIT, '', CFormButton::LOCATION_FORM_RIGHT);
	$formEdit->addElement($button_new);

	$html_form.= $formEdit->getHtmlCode();

	return $html_form;
}
	
function showListAppGroups($application)
{
	$oMain=$this->oMain;
	
	if($oMain->operation=='filter')
		$cond="and groupid like '%$this->groupid%'";
	
	$sql="SELECT groupid,tgpid,groupdesc,profile,dbo.efa_username(manager) AS manager,
		dbo.translate_code('global_status',dbo.tbgroups.tstatus, '$oMain->l') AS tstatusdesc
		,tstatus,'' AS toperations,modifiedby,modifdate,obs,deputy1,deputy2,tunitext,tunitextrem,company,reference,application 
		FROM dbo.tbgroups 
		WHERE company='$oMain->comp' AND application='$application' AND tstatus='A' $cond"; //  AND tstatus='A'
	//print $sql;
	
	$rs=getRS2($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		
		$application=$rs[$r]['application'];
		$groupid=$rs[$r]['groupid'];
		$tgpid=$rs[$r]['tgpid'];
		$company=$rs[$r]['company'];

		$param='application='.$application.'&company='.$company.'&groupid='.$groupid.'&tnodeid='.$tgpid.'&tgpid='.$tgpid;
		
		$rs[$r]['application']=$oMain->stdImglink('edit_applications', '', '',$param, '', $application, '', $oMain->translate('editapp'), '');
		$rs[$r]['groupid']=$oMain->stdImglink('dash_applications', '', '','tgpid='.$tgpid.'&application='.$application.'&groupid='.$groupid.'&tnodeid='.$tgpid.'&tgpid='.$tgpid, '', $groupid, '', $oMain->translate('editgroups'), '');		
	}
	
	$oTable = new efaGrid($oMain);
	$oTable->skin('dhx_web');
	$oTable->title($oMain->translate('appgroup')." ($rc)");
	//$oTable->dbClickLink($this->oMain->baseLink('', 'show_ruleanswer', '', 'tvarqid=§§tvarqid§§&tanswervar=§§tanswervar§§'));
	$tableheight=220;
	$oTable->height($tableheight);  
	$oTable->autoExpandHeight(true);
	$oTable->widthUsePercent(true);
	$oTable->data($rs);
	$oTable->multilineRow(true);

	//$oTable->searchable(false);
//	$oTable->exportToExcel(false);
//	$oTable->exportToPdf(false);
	
	$oTable->columnAdd('groupid')->width(15);
	$oTable->columnAdd('tgpid')->type('int')->width(10);
	$oTable->columnAdd('groupdesc')->width(30);
	$oTable->columnAdd('profile')->width(20);
	$oTable->columnAdd('manager')->width(20);
	//$oTable->columnAdd('tstatusdesc')->width(10)->title($oMain->translate('status'));
	
	$this->toolbar('list_applications',$oTable->toolbar);
	
	$html=$oTable->html();
					
//	$oTable = new CTable(null, null, $rs);
//	$oTable->SetSorting();
//	$oTable->SetFixedHead (true,400);
//
//	$oTable->addColumn($oMain->translate('groupid'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('tgpid'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('groupdesc'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('profile'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('manager'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('tstatus'), 'left', 'String');
		
//	$o = new CGroup($oMain);
//	$o->application=$this->application;
	
	//$link=$oMain->showHide($oMain->translate('creategroup'), $o->form('','APP'), 'img/new_s.png', 'img/new_s.png', 'img/new_s.png');
	//$filter=$oMain->showHide($oMain->translate('search'), $this->formFilterAppGrp(), 'img/search_s.png', 'img/search_s.png', 'img/search_s.png','','','class="rowpink"',' &nbsp; | &nbsp; '.$link);
	
	//$html = $oMain->efaHR($oMain->translate('appgroup').' - '.$this->application.' &nbsp; | &nbsp; '.$filter);
	//$html.= $oTable->getHtmlCode();
	//$html=$oTable->html();

	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}

protected function toolbar($mod,$maintoolbar)
	{
		$oMain=$this->oMain;
		if($mod=='list_applications')
		{
			$maintoolbar->add('newapp_groups')->link($oMain->BaseLink('','newapp_groups','','&application='.$this->application))->title($oMain->translate('newappgroups'))->tooltip($oMain->translate('expnewappgroups'))->efaCIcon('new.png');
		}
	}

function showListEvents()
{
	$oMain=$this->oMain;
	
	//$sql="select data, Utilizador, maquina, obs,referencia from tbeventos where referencia='$this->application' and aplicacao ='profiler' and tipoevento='app' order by data desc";
	
	$sql="SELECT tdate, dbo.efa_username(tuserid) as tuseriddesc, tdeviceid, ttype+' : '+tremarks, tuserid
			FROM [dbo].[tbeventcom] WHERE (tmodule ='profiler' OR tmodule='System') 
			AND trefa='$this->application' ORDER BY tdate DESC";
//print $sql;
	$rs=getRS2($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{	
		$rs[$r]['data']= $oMain->formatDate($rs[$r]['data']);			
	}
					
	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);

	$oTable->addColumn($oMain->translate('date'), 'left', 'String');
	$oTable->addColumn($oMain->translate('tmodifiedby'), 'left', 'String');
	$oTable->addColumn($oMain->translate('maquina'), 'left', 'String');
	$oTable->addColumn($oMain->translate('obs'), 'left', 'String');
	
	
	$html = $oMain->efaHR( $oMain->translate('userparam'));
	$html.= $oTable->getHtmlCode();
	
	If($rc==0) {return $oMain->translate('nosearchresults');}
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}

}// Enf of CApplications
?>
