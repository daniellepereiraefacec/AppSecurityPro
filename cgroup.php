<?php
/**
 * @@name	 	<description>
 * @@author	 	Generator 
 * @@version 	11-10-2012 10:02:50
 *
 * Revisions:
 * 2020-03-06   Danielle P.   TFS BUG 2270: fix load data on mod=review
 * 2016-06-13	Pedro Brandão Minor Changes
 * 2016-05-17	Pedro Brandão Minor Changes
 * 2015-02-27	Luis Gomes: SQL changed in list group members
 * 2015-01-30	Pedro Brandão Minor Changes
 * 2014-12-18	Pedro Brandão Minor Changes
 * 2014-12-05	Pedro Brandão Minor Changes
 * 2014-11-26	Pedro Brandão corrected export members to excel
 * 2014-11-20	Pedro Brandão Minor changes
 * 2014-11-04	Pedro Brandão Profiler 2.0
 * 2013-10-29	Pedro Brandao add tgpid to form and showlist in class cgroup
 * 2013-10-23	Pedro Brandao add class CGroupMan
 * 2013-08-12	Pedro Brandao	Updated compatibility to new table tbgroupmember
 * 2013-05-31	Pedro Brandão e Luis Cruz getapplbox() to correct the onchange when only one application exists in the company
 * 2013-03-04	Luis Cruz BDCOMUM removed
 */

 /**
  * Class CModule
  */

 /**
  * classs used by gsearch and advanced search
  */

require_once('ccommonsql.php');
/**
 * @@name	 	<description>
 * @@author	 	Generator 
 * @@version 	11-10-2012 10:02:50
 *
 * Revisions:
 */
class CGroup
{
	var $company;    /**  */
	var $application;    /**  */
	var $groupid;    /**  */
	var $manager;    /**  */
	var $managerdesc;
	var $groupdesc;    /**  */
	var $reference;    /**  */
	var $profile;    /**  */
	var $modifiedby;    /**  */
	var $modifdate;    /**  */
	var $obs;    /**  */
	var $tstatus;    /**  */
	var $deputy1;    /**  */
	var $deputy1desc;
	var $deputy2;    /**  */
	var $deputy2desc;
	var $tunitext;    /**  */
	var $tunitextrem;    /**  */
	var $tgpid;    /**  */
	var $copycomp;
	var $copyapplication;
	var $sourcetgpid;
	var $copymemb;
	var $tauto;
	
	var $tmanager;
	var $tdeputy1;
	var $tdeputy2;

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
		$this->readFromRequest();
	}

	/**
	 * set class groups mod
	 */	
	function getHtml(&$mod)
	{
		$oMain=$this->oMain;
		$ent='groups'; 
		$company=$this->company;

		if ($mod =='tauto_'.$ent)
		{
			$oper='unsetauto';
			if($this->tauto!=0) {$oper='setauto';}
			
			$tstatus=$this->storeIntoDB($oper, $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$oMain->default_tab=1;
			$mod='dash_applications';		
		}		
		
		
		//copy group
		if ($mod =='insertcopy_'.$ent)
		{
			$this->readfromdb();
			
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$oapp=new CApplication($oMain);
			$html=$oapp->dashboard($this->tgpid);
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			//print $oMain->operation;
			if($tstatus==0)
			{
				$oapp=new CApplication($oMain);
				$html=$oapp->dashboard($this->tgpid);
			}
			else 
			{
				if($oMain->operation=='APP')
					$mod='edit_groups';
				else
					$mod='list_groups';
			}
			
		}

		if ($mod =='review_'.$ent)
		{
            $this->readfromdb();
			$tstatus=$this->storeIntoDB('review', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$o=new CManage($oMain);
			
			
			$html=$o->getHtml('show_manage');
			IF($oMain->operation=='ALL')
				$html=$o->getHtml('showall_manage');
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$oMain->default_tab=1;
			$mod='dash_applications';		
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			if($mod =='edit_'.$ent)
			{	
				$this->readFromDB(); $company=$this->company;}
				$oMain->subtitle=$oMain->translate($mod).' '.$company;
				
				$html=$this->groupdatatabs($this->application, $this->groupid);
			}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->form('insert_groups');
		}
		
		if ($mod =='newapp_'.$ent or $mod =='xnewapp_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->form('','APP');
		}
		
		if ($mod =='search_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$oMain->Title('', $oMain->translate($mod));
			$html.='<BR>';
			$html.=$this->formSearchGrp();
		}
		if ($mod =='copy_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$oMain->Title('', $oMain->translate($mod));
			$html.='<BR>';
			$html.=$this->formCopyGrp();
		}
		


		if ($mod =='events_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showListEvents();
		}
		
		//$oMain->toolbar_icon('img/new_sphere.png',$oMain->BaseLink('','new_groups', '', 'company='.$oMain->comp), $oMain->translate('newgroup'));
		$oMain->subtitle=$oMain->translate($mod);
		return($html);
	}
	
	 /**
	  * read class groups atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->company=$oMain->GetFromArray('company',$_REQUEST,'string_trim');
		$this->application=$oMain->GetFromArray('application',$_REQUEST,'string_trim');
		$this->groupid=$oMain->GetFromArray('groupid',$_REQUEST,'string_trim');
		$this->manager=$oMain->GetFromArray('manager',$_REQUEST,'string_trim');
		$this->groupdesc=$oMain->GetFromArray('groupdesc',$_REQUEST,'string_trim');
		$this->reference=$oMain->GetFromArray('reference',$_REQUEST,'string_trim');
		$this->profile=$oMain->GetFromArray('profile',$_REQUEST,'string_trim');
		$this->modifiedby=$oMain->GetFromArray('modifiedby',$_REQUEST,'string_trim');
		$this->modifdate=$oMain->GetFromArray('modifdate',$_REQUEST,'date');
		$this->obs=$oMain->GetFromArray('obs',$_REQUEST,'string_trim');
		$this->tstatus=$oMain->GetFromArray('tstatus',$_REQUEST,'string_trim');
		$this->deputy1=$oMain->GetFromArray('deputy1',$_REQUEST,'string_trim');
		$this->deputy2=$oMain->GetFromArray('deputy2',$_REQUEST,'string_trim');
		$this->tunitext=$oMain->GetFromArray('tunitext',$_REQUEST,'string_trim');
		$this->tunitextrem=$oMain->GetFromArray('tunitextrem',$_REQUEST,'string_trim');
		$this->tgpid=$oMain->GetFromArray('tgpid',$_REQUEST,'int');
		$this->copycomp=$oMain->GetFromArray('copycomp',$_REQUEST,'string_trim');
		$this->copyapplication=$oMain->GetFromArray('copyapplication',$_REQUEST,'string_trim');
		$this->sourcetgpid=$oMain->GetFromArray('sourcetgpid',$_REQUEST,'int');
		$this->copymemb=$oMain->GetFromArray('copymemb',$_REQUEST,'int');
		$this->tauto=$oMain->GetFromArray('tauto',$_REQUEST,'int');
		
		$this->tmanager=$oMain->GetFromArray('tmanager',$_REQUEST,'int');
		$this->tdeputy1=$oMain->GetFromArray('tdeputy1',$_REQUEST,'int');
		$this->tdeputy2=$oMain->GetFromArray('tdeputy2',$_REQUEST,'int');
		
		//print_r($_REQUEST);
	}

	
	function sqlGetFilter()
{
	$oMain=$this->oMain;
	$l=$oMain->l;
	$query='';
	
	if($this->application<>'') $query =" AND (application='$this->application')";
	if($this->groupid<>'')       {$query.=" AND groupid LIKE '%".$this->groupid."%' ";}
	
	$sql=
		$sql="SELECT groupid,application,groupdesc,profile,manager,reference,'' AS toperations,status,modifiedby,modifdate,obs,tstatus,deputy1,deputy2,tunitext,tunitextrem,tgpid,company
			FROM dbo.tbgroups
			WHERE company='$oMain->comp' $query";
	
	return($sql);
}
	private function switchAuto()
	{
		$oMain=$this->oMain;
		if($this->tauto==0)
		{
			$html=$oMain->translate('tautois0').' '.
			$oMain->stdImglink('tauto_groups', '', '',"tgpid=$this->tgpid&tnodeid=$this->tgpid&tauto=1",
					'refresh_s.png', '', '', $oMain->translate('explaintauto0'), $oMain->translate('confirmauto0'));			
		}
		else
		{
			$html='<font color=blue>'.$oMain->translate('tautois1').'</font> '.
			$oMain->stdImglink('tauto_groups', '', '',"tgpid=$this->tgpid&tnodeid=$this->tgpid&tauto=0",
					'refresh_s.png', '', '', $oMain->translate('explaintauto1'), $oMain->translate('confirmauto1'));			
		}
		return $html;
	}
	
	function form($mod='show_groups',$op='')
	{
		$oMain=$this->oMain;
//print $mod.' '.$op; 
		$html_form=$oMain->stdJsPopUpWin('400');
		$formName='frmgroups'; $operation='';$nCol=2;$width='100%';$ajax=false;
		$frmMod=CForm::MODE_EDIT;
		if($mod=='show_groups')
			$frmMod=CForm::MODE_VIEW;
		
		if($this->tmanager==0 or $this->tmanager==-1)	$this->tmanager='';
		if($this->tdeputy1==0 or $this->tdeputy1==-1)	$this->tdeputy1='';
		if($this->tdeputy2==0 or $this->tdeputy2==-1)	$this->tdeputy2='';
		
		If ($op=='APP')
			$oForm = $oMain->std_form('insert_groups', $op,$formName,$nCol,$frmMod,$ajax,$width);
		else
			$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		
		//print $mod;
		$aForm = array();
		
		if($mod=='insert_groups')
		{
			$groups_readonly=false;
			$this->tstatus='A';
		}
		else
			$groups_readonly=true;
		
		
		If ($op=='APP')  //Create new group from application details tabs
		{
			
			$aForm[] = new CFormHidden('tgpid',$this->tgpid);
			$aForm[] = new CFormHidden('tnodeid',$this->tgpid);
			$aForm[] = new CFormHidden('company',$oMain->comp);

			$aForm[] = new CFormText($oMain->translate('groupid'),'groupid', $this->groupid,20,CForm::REQUIRED,false,'',CFormText::INPUT_STRING_CODEI);			
			$aForm[] = new CFormText($oMain->translate('application'),'application', $this->application,5,'',true,'',CFormText::INPUT_STRING_CODEI);		

			$search_manager=$oMain->stdPopupwin('GETCCUSER',$formName,'tmanager','tmanagerdesc','tmanager','tmanagerdesc','','employee');	
			$field_manager = new CFormText($oMain->translate('manager'),'tmanager', $this->tmanager,'',CForm::REQUIRED,false,'',CFormText::INPUT_INTEGER);
			$field_manager_desc = new CFormText($oMain->translate('manager'), 'tmanagerdesc', $this->tmanagerdesc, '','',false, '', '', '', 70);
			if($frmMod==CForm::MODE_EDIT)
			   $field_manager->setExtraData($search_manager);
			$aForm[] = new CFormMultipleElement(array($field_manager, $field_manager_desc), 0);	
		
			
			$search_deputy1=$oMain->stdPopupwin('GETCCUSER',$formName,'tdeputy1','tdeputy1desc','tdeputy1','tdeputy1desc','','employee');	
			$field_deputy1 = new CFormText($oMain->translate('deputy1'),'tdeputy1', $this->tdeputy1,'','',false,'',CFormText::INPUT_INTEGER);
			$field_deputy1_desc = new CFormText($oMain->translate('deputy1'), 'tdeputy1desc', $this->tdeputy1desc, '','',false, '', '', '', 70);
			if($frmMod==CForm::MODE_EDIT)
			   $field_deputy1->setExtraData($search_deputy1);
			$aForm[] = new CFormMultipleElement(array($field_deputy1, $field_deputy1_desc), 0);	
			
			$search_deputy2=$oMain->stdPopupwin('GETCCUSER',$formName,'tdeputy2','tdeputy2desc','tdeputy2','tdeputy2desc','','employee');	
			$field_deputy2 = new CFormText($oMain->translate('deputy2'),'tdeputy2', $this->tdeputy2,'','',false,'',CFormText::INPUT_INTEGER);
			$field_deputy2_desc = new CFormText($oMain->translate('deputy2'), 'tdeputy2desc', $this->tdeputy2desc, '','',false, '', '', '', 70);
			if($frmMod==CForm::MODE_EDIT)
			   $field_deputy2->setExtraData($search_deputy2);
			$aForm[] = new CFormMultipleElement(array($field_deputy2, $field_deputy2_desc), 0);
			
			$aForm[] = new CFormText($oMain->translate('groupdesc'),'groupdesc', $this->groupdesc,30,'',false,'',CFormText::INPUT_STRING);
			$aForm[] = new CFormText($oMain->translate('reference'),'reference', $this->reference,10,'',false,'',CFormText::INPUT_STRING);
			$aForm[] = new CFormText($oMain->translate('profile'),'profile', $this->profile,20,'',false,'',CFormText::INPUT_STRING);
			
			$atextForm = new CFormTextArea($oMain->translate('obs'), 'obs', $this->obs, 4,'',false,CForm::RECOMMENDED);
			$atextForm->setNumberOfColumns(1);
			$aForm[]=$atextForm;

			//status listbox
			$this->tstatus='A';
			$sql="SELECT codeid, dbo.translate_optional(valunitext, '$oMain->l', codeid) AS codetxt FROM dbo.tbcodes WHERE (codetype = 'global_status')";
			$aForm[] = new CFormSelect($this->oMain->translate('tstatus'), 'tstatus', trim($this->tstatus), $this->tstatus, $sql, $oMain->consql, '', '', ' ', CForm::REQUIRED);
				
		}
		else
		{		
			$modifdate=$oMain->formatDate($this->modifdate);
			$info='<img src=img\info_s.png title="Modified by: '.$this->modifiedby.' @ '.$modifdate.'">';
			
			$setauto=$this->switchAuto();
						
			$paramcopy='&copycomp='.$this->company.'&copyapplication='.$this->application.'&sourcetgpid='.$this->tgpid;
			$events=$oMain->stdImglink('events_groups', '', '', '&tgpid='.$this->tgpid, '', $oMain->translate('events'), '', $oMain->translate('events'));
			$link=$oMain->stdImglink('dash_applications', '', '','&tgpid='.$this->tgpid.'&tnodeid='.$this->tgpid, '', $this->groupid, '', $oMain->translate('edit_groups'));
			$copygrp=$oMain->stdImglink('copy_groups', '', 'copygrp','&tgpid='.$this->tgpid.'&tnodeid='.$this->tgpid.$paramcopy, 'copy_s.png', $oMain->translate('copygrp'), '', $oMain->translate('copygrp'));
			
			if($mod=='insert_groups')
				$html_form.=$oMain->efaHR($oMain->translate('newgroup'));
			else
				$html_form.=$oMain->efaHR($oMain->translate('group').' - '.$link.
						' &nbsp; | &nbsp; '.$info.' &nbsp; | &nbsp; '.$events.' &nbsp; | &nbsp; '.$copygrp.' &nbsp; | &nbsp; '.$setauto);

			$aForm[] = new CFormHidden('tgpid',$this->tgpid);
			$aForm[] = new CFormHidden('tnodeid',$this->tgpid);
			
			if($mod=='insert_groups')
			{
				$aForm[] = new CFormText($oMain->translate('groupid'),'groupid', $this->groupid,20,CForm::REQUIRED,false,'',CFormText::INPUT_STRING_CODEI);
			}
			else
			{
				$aForm[] = new CFormText($oMain->translate('groupid'),'groupid', $this->groupid,20,CForm::REQUIRED,true,'',CFormText::INPUT_STRING_CODEI);
				$aForm[] = new CFormText($oMain->translate('tgpid'),'tgpid', $this->tgpid,20,'',true,'',CFormText::INPUT_STRING_CODEI);
			}

			//general
			$aForm[] = new CFormText($oMain->translate('company'),'company', $this->company,5,CForm::REQUIRED,true,'',CFormText::INPUT_STRING_CODE);
			
			if ($mod=='insert_groups')
			{
				//applications listbox
				$sql="SELECT application, application AS tdesc FROM dbo.tbapplications WHERE (company = '$oMain->comp')";
				$aForm[] = new CFormSelect($oMain->translate('application'), 'application', trim($this->application), trim($this->application), $sql, $oMain->consql, '', '', ' ', CForm::REQUIRED);
			}
			else
				$aForm[] = new CFormText($oMain->translate('application'),'application', $this->application,5,'',true,'',CFormText::INPUT_STRING_CODEI);

			
			$search_manager=$oMain->stdPopupwin('GETCCUSER',$formName,'tmanager','tmanagerdesc','tmanager','tmanagerdesc','','employee');	
			$field_manager = new CFormText($oMain->translate('manager'),'tmanager', $this->tmanager,'',CForm::REQUIRED,false,'',CFormText::INPUT_INTEGER);
			$field_manager_desc = new CFormText($oMain->translate('manager'), 'tmanagerdesc', $this->tmanagerdesc, '','',false, '', '', '', 70);
			if($frmMod==CForm::MODE_EDIT)
			   $field_manager->setExtraData($search_manager);
			$aForm[] = new CFormMultipleElement(array($field_manager, $field_manager_desc), 0);	

			
			$search_deputy1=$oMain->stdPopupwin('GETCCUSER',$formName,'tdeputy1','tdeputy1desc','tdeputy1','tdeputy1desc','','employee');	
			$field_deputy1 = new CFormText($oMain->translate('deputy1'),'tdeputy1', $this->tdeputy1,'','',false,'',CFormText::INPUT_INTEGER);
			$field_deputy1_desc = new CFormText($oMain->translate('deputy1'), 'tdeputy1desc', $this->tdeputy1desc, '','',false, '', '', '', 70);
			if($frmMod==CForm::MODE_EDIT)
			   $field_deputy1->setExtraData($search_deputy1);
			$aForm[] = new CFormMultipleElement(array($field_deputy1, $field_deputy1_desc), 0);	

			
			$search_deputy2=$oMain->stdPopupwin('GETCCUSER',$formName,'tdeputy2','tdeputy2desc','tdeputy2','tdeputy2desc','','employee');	
			$field_deputy2 = new CFormText($oMain->translate('deputy2'),'tdeputy2', $this->tdeputy2,'','',false,'',CFormText::INPUT_INTEGER);
			$field_deputy2_desc = new CFormText($oMain->translate('deputy2'), 'tdeputy2desc', $this->tdeputy2desc, '','',false, '', '', '', 70);
			if($frmMod==CForm::MODE_EDIT)
			   $field_deputy2->setExtraData($search_deputy2);
			$aForm[] = new CFormMultipleElement(array($field_deputy2, $field_deputy2_desc), 0);	

			$aForm[] = new CFormText($oMain->translate('groupdesc'),'groupdesc', $this->groupdesc,30,'',false,'',CFormText::INPUT_STRING);
			$aForm[] = new CFormText($oMain->translate('reference'),'reference', $this->reference,10,'',false,'',CFormText::INPUT_STRING);
			$aForm[] = new CFormText($oMain->translate('profile'),'profile', $this->profile,20,'',false,'',CFormText::INPUT_STRING);

			$atextForm = new CFormTextArea($oMain->translate('obs'), 'obs', $this->obs, 4,'',false,CForm::RECOMMENDED);
			$atextForm->setNumberOfColumns(1);
			$aForm[]=$atextForm;
			
			//status listbox
			$sql="SELECT codeid, dbo.translate_optional(valunitext, '$oMain->l', codeid) AS codetxt FROM dbo.tbcodes WHERE (codetype = 'global_status')";
			$aForm[] = new CFormSelect($this->oMain->translate('tstatus'), 'tstatus', trim($this->tstatus), $this->tstatus, $sql, $oMain->consql, '', '', ' ', CForm::REQUIRED);

		}
		//form buttons
		//$onSubmit="$formName.submit(); $oMain->loading;";
		$buttonSave = new CFormButton('save', $oMain->translate ('save'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);
		$aForm[]=$buttonSave;
		$oForm->addElementsCollection($aForm);
		$html_form.=$oForm->getHtmlCode();
		
		return $html_form;
	}
	
function formSearchGrp()
{
	$oMain=$this->oMain;
	
	$formName='searchgrp'; $operation='';$nCol=2;$width='100%';$ajax=false;
	$frmMod=CForm::MODE_EDIT;
	$oForm = $oMain->std_form('dash_applications', $operation,$formName,$nCol,$frmMod,$ajax,$width);
	$aForm = array();
	
	$aForm[] = new CFormTitle($oMain->translate('group'), 'tit'.$formName);

	//company listbox
	$sql="SELECT company, company + ' - ' + shortdesc AS shortdesc FROM dbo.tbcompanies WHERE (tstatus <> 'X')";
	$cformsel = new CFormSelect($this->oMain->translate('company'), 'copycomp', $this->copycomp, '', $sql, $oMain->consql, '', '',' ', CForm::REQUIRED);
	$cformsel->addEvent("onChange=\"searchgrp.mod.value='search_groups';  javascript:searchgrp.submit(); ".$oMain->loading()."\"");
	$aForm[]=$cformsel;
	
	//tgpid source listbox
	$sqlapp="SELECT tgpid, application+'|'+groupid+'|'+groupdesc AS tdesc,* from dbo.tbgroups WHERE company='$this->copycomp' ORDER BY tdesc";
	$cformsel = new CFormSelect($oMain->translate('group'), 'tgpid', $this->tgpid, '', $sqlapp, $oMain->consql, '', '', ' ', CForm::REQUIRED);
	$aForm[]=$cformsel;
	
	$buttonSave = new CFormButton('search', $oMain->translate ('search'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);
	$aForm[]=$buttonSave;

	//form buttons
	$oForm->addElementsCollection($aForm);
	$html_form.=$oForm->getHtmlCode();
	return $html_form;
	
}

function getNewGrps()
{
	$oMain=$this->oMain;
	
	$sqlsrc="SELECT tgpid, groupid+'|'+groupdesc AS tdesc, UPPER(groupid) as groupid from dbo.tbgroups WHERE company='$this->copycomp' and application='$this->application'";
	$rssrc=dbQuery($oMain->consql, $sqlsrc, $flds);
	
	
	$sqldest="SELECT tgpid, groupid+'|'+groupdesc AS tdesc, UPPER(groupid) as groupid from dbo.tbgroups WHERE company='$oMain->comp' and application='$this->application'";
	$rsdest=dbQuery($oMain->consql, $sqldest, $flds);
	
	foreach ($rssrc as $k => $v)
	{ 
		
		foreach ($rsdest as $k2 => $v2)
		{
			
			if($v['groupid']==$v2['groupid'])
			{
				unset ($rssrc[$k]);
			}
			
		}
	}
	
	return $rssrc;
}
	
function formCopyGrp()
{
	$oMain=$this->oMain;
	
	$formName='frm_copygrp'; $operation='';$nCol=3;$width='100%';$ajax=false;
	$frmMod=CForm::MODE_EDIT;
	$oForm = $oMain->std_form('insertcopy_groups', $operation,$formName,$nCol,$frmMod,$ajax,$width);
	$aForm = array();
	
	$aForm[] = new CFormTitle($oMain->translate('sourcegroup'), 'tit'.$formName);

	//company listbox
	$sql="SELECT company, company + ' - ' + shortdesc AS shortdesc FROM dbo.tbcompanies WHERE (tstatus <> 'X')";
	$cformsel = new CFormSelect($this->oMain->translate('copyfromcomp'), 'copycomp', $this->copycomp, '', $sql, $oMain->consql, '', '',' ', CForm::REQUIRED);
	$cformsel->addEvent("onChange=\"frm_copygrp.mod.value='copy_groups';  javascript:frm_copygrp.submit(); ".$oMain->loading()."\"");
	$aForm[]=$cformsel;
	
	//application existente on dest comp
	$sqlapp="SELECT distinct G1.[application], G2.[application] tdesc, G1.company, G2.company
		FROM dbo.tbapplications G1
		INNER JOIN dbo.tbapplications G2 ON G2.[application]=G1.[application]
		WHERE G2.company='$oMain->comp' and G1.company='$this->copycomp'
		ORDER BY tdesc";
	$cformsel = new CFormSelect($oMain->translate('application'), 'application', $this->application, '', $sqlapp, $oMain->consql, '', '', ' ', CForm::REQUIRED);
	$cformsel->addEvent("onChange=\"frm_copygrp.mod.value='copy_groups';  javascript:frm_copygrp.submit(); ".$oMain->loading()."\"");
	$aForm[]=$cformsel;
	
	//tgpid source listbox
//	$sqlapp="SELECT tgpid, application+'|'+groupid+'|'+groupdesc AS tdesc from dbo.tbgroups WHERE company='$this->copycomp' and application='$this->application' ORDER BY tdesc";
////	$sqlapp="SELECT tgpid, application+'|'+groupid+'|'+groupdesc AS tdesc, company from dbo.tbgroups WHERE company='$this->copycomp' and application='$this->application'
////			EXCEPT
////			SELECT tgpid, application+'|'+groupid+'|'+groupdesc AS tdesc, company from dbo.tbgroups WHERE company='$oMain->comp' and application='$this->application'
////			ORDER BY tdesc";
//	$cformsel = new CFormSelect($oMain->translate('tgpid'), 'tgpid', $this->tgpid, '', $sqlapp, $oMain->consql, '', '', ' ', CForm::REQUIRED);
//	$aForm[]=$cformsel;
	$array=[];
	if($this->copycomp!='' and $this->application!='')	$array=$this->getNewGrps();
		
	//var_dump($array); die;
	$cformsel = new CFormSelect($oMain->translate('tgpid'), 'tgpid', $this->tgpid, '', '', '', $array, '', ' ', CForm::REQUIRED);
	$aForm[]=$cformsel;

	$buttonSave = new CFormButton('insert', $oMain->translate ('copytothiscomp'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);
	$aForm[]=$buttonSave;

	//form buttons
	$oForm->addElementsCollection($aForm);
	$html_form.=$oForm->getHtmlCode();
	return $html_form;
	
}

	/**
	 * save class groups record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain=$this->oMain;
		$sid=$oMain->sid;
		if($operation=='setauto' || $operation=='unsetauto')
		{
			$sql="[dbo].[spgroup] '$sid','$operation',0,'$this->tgpid'";
		}
		elseif($operation=='insert' OR $operation=='update')
		{
			
			$sql="[dbo].[spgroup] @sid='$sid',@sp_operation='$operation',@norecordset=0
			,@tgpid=		'$this->tgpid'
			,@company=		'$oMain->comp'
			,@application=	'$this->application'
			,@groupid=		'$this->groupid'
			,@groupdesc=	'$this->groupdesc'
			,@reference=	'$this->reference'
			,@profile=		'$this->profile'
			,@obs=			'$this->obs'
			,@tstatus=		'$this->tstatus'
			,@tdesc=		'$this->tdesc'
			,@tremarks=		'$this->remarks'
			,@tmanager=		'$this->tmanager'
			,@tdeputy1=		'$this->tdeputy1'
			,@tdeputy2=		'$this->tdeputy2'
			";
		}
		else
		{	
			$sql="[dbo].[spgroups] '$sid','$operation','$this->company'
			,'$this->application'
			,'$this->groupid'
			,'$this->manager'
			,'$this->groupdesc'
			,'$this->reference'
			,'$this->profile'
			,'$this->obs'
			,'$this->tstatus'
			,'$this->deputy1'
			,'$this->deputy2'
			,'$this->tunitext'
			,'$this->tunitextrem'
			,'$this->tgpid'
			,'$this->copycomp'
			,'$this->copymemb'
			";
		}
		$rs=dbQuery($this->oMain->consql, $sql, $flds);	//print "<HR><HR><HR><HR><HR><HR>".$sql; exit;
		$rst=$rs[0];
		$tdesc=$rst['tdesc'];
		if($operation=='insert')
		{
			$this->tgpid=$rst['tgpid'];
		}
		return($rst['tstatus']);
	}
	/**
	 * query to get class groups record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
		$this->tgpid=(int)$this->tgpid;
		
		$sql='SELECT company,application,groupid,dbo.efa_username(manager) AS managerdesc,
			groupdesc,reference,profile,dbo.efa_username(modifiedby) AS modifiedby,modifdate,obs,tstatus
			,deputy1,deputy2,tunitext,tunitextrem,tgpid,manager, 
			dbo.efa_username(deputy1) AS deputy1desc, dbo.efa_username(deputy2) AS deputy2desc, CAST(tauto AS int) AS tauto
			, tmanager,tdeputy1,tdeputy2
			, dbo.efa_uidname(tmanager) AS tmanagerdesc
			, dbo.efa_uidname(tdeputy1) AS tdeputy1desc, dbo.efa_uidname(tdeputy2) AS tdeputy2desc
			FROM dbo.tbgroups ';
		if ($this->tgpid>0)
		{	$sql.="WHERE(tgpid=$this->tgpid)";}
		else
		{ $sql.="WHERE (company='$oMain->comp' and application='$this->application' and groupid='$this->groupid')";}
		
		//print $sql;
		return($sql);
	}
	
	/**
	 * set class groups atributes with data from database
	 */	
	function readfromdb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet(); //print "<HR><HR><HR><HR><HR>CGroup:<HR>$sql"; //debug_print_backtrace (0 ,3 );
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->company=$rst['company'];
			$this->application=$rst['application'];
			$this->groupid=$rst['groupid'];
			$this->manager=$rst['manager'];
			$this->managerdesc=$rst['managerdesc'];
			$this->groupdesc=$rst['groupdesc'];
			$this->reference=$rst['reference'];
			$this->profile=$rst['profile'];
			$this->modifiedby=$rst['modifiedby'];
			$this->modifdate=$rst['modifdate'];
			$this->obs=$rst['obs'];
			$this->tstatus=$rst['tstatus'];
			$this->deputy1=$rst['deputy1'];
			$this->deputy1desc=$rst['deputy1desc'];
			$this->deputy2=$rst['deputy2'];
			$this->deputy2desc=$rst['deputy2desc'];
			$this->tunitext=$rst['tunitext'];
			$this->tunitextrem=$rst['tunitextrem'];
			$this->tgpid=$rst['tgpid'];
			$this->tauto=$rst['tauto'];			
			$this->tmanager=$rst['tmanager'];
			$this->tdeputy1=$rst['tdeputy1'];
			$this->tdeputy2=$rst['tdeputy2'];
			$this->tmanagerdesc=$rst['tmanagerdesc'];
			$this->tdeputy1desc=$rst['tdeputy1desc'];
			$this->tdeputy2desc=$rst['tdeputy2desc'];
		}

		return $rc;
	}



function areagroups()
{
	$oMain=$this->oMain;

	$ogroupman = new CGroupMan($oMain); 
	$ogroupman->tgpid=$this->tgpid;


	$a=$this->form('update_groups');
	$b=$ogroupman->showList();


	$html="<table border=0 width=99%>
	<tr>
		<td width=65% valign=top>$a</td>
	</tr>
	<tr>
		<td width=65% valign=top>$b</td>
	</tr>
	</table>
	";

	return $html;
}
	
function groupdatatabs()
{
	$oMain=$this->oMain;
	
	$defaulttab=$oMain->default_tab;
	
	$ogroupman = new CGroupMan($oMain); $ogroupman->readfromrequest();
	$this->readfromdb();
	$content=$this->areagroups();
	$tabs[1]=$array=array("title" => $oMain->translate('groupdata').$taskid,
					"xls"        => '',	"contents"   => $content);
	
	$o = new CMember($oMain); $o->readfromrequest();$o->readfromdb();
	$content = $o->showListGroupMembers($this->groupid, $this->application, $this->tgpid);
	$tabs[2]=$array=array("title" => $oMain->translate('showgroupmembers').$taskid,
					"xls"        => '',	"contents"   => $content);

	$o = new CAccess($oMain); $o->readfromrequest(); $o->readfromdb();
	$content = $o->showListGroupAccesses($this->groupid, $this->application, $this->tgpid);
	$tabs[3]=$array=array("title" => $oMain->translate('showgroupaccess').$taskid,
					"xls"        => '',	"contents"   => $content);

	$tabelements = $oMain->BuildTabElements($tabs[1], $tabs[2], $tabs[3],'', '', '', '', '', '');

	$html.= $oMain->build_tabs($tabelements, $defaulttab);

	return ($html);
}


function showListEvents()
{
	$oMain=$this->oMain;

	$sql="SELECT tdate, dbo.efa_username(dbo.efa_userid(tuserid)) as tuseriddesc, tdeviceid, ttype+' : '+tremarks, tuserid
			FROM [dbo].[tbeventcom] WHERE (tmodule ='profiler' OR tmodule='System') 
			AND trefa='$this->tgpid' ORDER BY tdate DESC";
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{	
		$rs[$r]['tdate']= $oMain->formatDate($rs[$r]['tdate']);			
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

}// Enf of groups


class CGroupMan
{
	var $tgpid;    /** Grupo a ser Gerido */
	var $tgpidman;    /** Grupo Gestor */
	var $tcreatedate;    /** Data de criação */
	var $tcreatedby;    /** Criado por */
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class CGroupMan mod
	 */	
	function getHtml(&$mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='groupman'; 
		$tgpid=$this->tgpid;

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$mod='dash_applications';
			//$mod='edit_groups';
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$mod='dash_applications';
			//$mod='edit_groups';
		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->form('insert_'.$ent,'xnew_'.$ent);
		}

		if ($mod =='list_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod).' '.$tgpid;
			$html=$this->showList();
		}

		if ($mod =='show_'.$ent)
		{
			$this->readFromDb();
			$oMain->subtitle=$oMain->translate('show_'.$ent).' '.$tgpid;
			$html=$this->form('show_'.$ent);
		}

		return($html);
	}
	
	 /**
	  * read class CGroupMan atributes from request
	  */	
	function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->tgpid=$oMain->GetFromArray('tgpid',$_REQUEST,'int');
		$this->tgpidman=$oMain->GetFromArray('tgpidman',$_REQUEST,'int');
		$this->tcreatedate=$oMain->GetFromArray('tcreatedate',$_REQUEST,'date');
		$this->tcreatedby=$oMain->GetFromArray('tcreatedby',$_REQUEST,'string_trim');
		

	}
	/**
	 * class CGroupMan form
	 */	
	function form($mod='show_groupman',$modChange='')
	{

		$oMain=$this->oMain;
		$oCC=new CCommonSql($oMain);
		$html_form=$oMain->stdJsPopUpWin('400');
		$formName='frmCGroupMan'; $operation='';$nCol=1;$width='100%';$ajax=false;
		$modCancel='show_groupman';
		
		$frmMod=CForm::MODE_EDIT;
		if($mod=='show_groupman')
			$frmMod=CForm::MODE_VIEW;

		if($mod=='insert_groupman')
			$CGroupMan_readonly=false;
		else
			$CGroupMan_readonly=true;
	
		$onChange="$formName.mod.value='$modChange';$formName.submit(); ".$oMain->loading().";";

		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$aForm = array();
				
		if($mod<>'insert_groupman')
			$aForm[] = new CFormTitle($oMain->translate('CGroupMan'), 'tit'.$formName);
		//general
		$aForm[] = new CFormHidden('tgpid', $this->tgpid);
		$aForm[] = new CFormHidden('tnodeid',$this->tgpid);
		
		$sql_tgpidman="SELECT tgpid, (company+' - '+application+' - '+groupid) as tdesc
						FROM dbo.tbgroups
						WHERE (company = '$oMain->comp') AND (tstatus = 'A')
						ORDER BY tdesc";
		$aForm[] = new CFormSelect($oMain->translate('tgpidman'), 'tgpidman', $this->tgpidman, $this->tgpidman, $sql_tgpidman, $this->oMain->consql,'',' ',' ','',false);
		
		if($mod<>'insert_groupman')
		{
			$aForm[]  = new CFormDate($oMain->translate('tcreatedate'), 'tcreatedate', $oMain->formatDate($this->tcreatedate),'',false);
			$aForm[] = new CFormText($oMain->translate('tcreatedby'),'tcreatedby', $this->tcreatedby,0,'',true,'',CFormText::INPUT_STRING);
		}

		//form buttons
		$onSubmit="$formName.submit(); $oMain->loading();";
		$buttonSave = new CFormButton('save', $oMain->translate ('save'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);
		$aForm[]=$buttonSave;
		$buttonCancel = new CFormButton('cancel', $oMain->translate ('cancel'),CFormButton::TYPE_BUTTON,'',CFormButton::LOCATION_FORM_BOTTOM);
		$buttonCancel->addEvent("onclick=\"$formName.mod.value='$modCancel';$onSubmit\"");
		$aForm[]=$buttonCancel;
		$oForm->addElementsCollection($aForm);
		// $html_form.=$this->form_toolbar($mod);
		$html_form.=$oForm->getHtmlCode();
		return $html_form;

	}

	/**
	 * save class CGroupMan record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="[dbo].[spgroupman] '$sid','$operation'
		,'$this->tgpid'
		,'$this->tgpidman'
		";
		
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rst=$rs[0];
		$tdesc=$rst['tdesc'];
		return($rst['tstatus']);
	}
	/**
	 * query to get class CGroupMan record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT tgpid,tgpidman,tcreatedate,tcreatedby FROM dbo.tbgroupman WHERE tgpid=$this->tgpid and tgpidman=$this->tgpidman";		

		return($sql);
	}
	
	
	function showList()
	{
		$oMain=$this->oMain;
		$sql="SELECT tgpid,tgpidman,tcreatedate,tcreatedby
				, dbo.efa_groupdesc(tgpidman) as groupdesc, dbo.efa_uidname(tcreatedby) AS tcreatedbydesc 
			FROM dbo.tbgroupman WHERE tgpid=$this->tgpid";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		$ArrayRst=array();
		for ($r = 0; $r < $rc; $r++)
		{
			$rst=$rs[$r];
			$param='&tgpid='.$rst['tgpid'].'&tgpidman='.$rst['tgpidman'].'&tnodeid='.$rst['tgpid'];
			
			$groupmanparam='&tgpid='.$rst['tgpidman'];
			
			$link_tgpidman=$oMain->stdImglink('edit_groups', '', '',$groupmanparam.'&default_tab=2', '', $rst['groupdesc'], '', $oMain->translate('edit_groups'));
			
					
			//$ArrayRst[$elementos]['tgpid']	= $link_tgpid;
			$ArrayRst[$elementos]['groupdesc']		= $link_tgpidman;
			$ArrayRst[$elementos]['tcreatedate']		= $oMain->formatdate($rst['tcreatedate']);
			$ArrayRst[$elementos]['tcreatedbydesc']		= $rst['tcreatedbydesc'];
		
			//$ArrayRst[$elementos]['toperations']= $oMain->ajaxImg( 'edit_groupman', '', 'img/edit_s.png', $oMain->translate('edit'), $param);
			$ArrayRst[$elementos]['toperations'] = $oMain->stdImglink('del_groupman', '', '',$param, 'img/delete_s.png', '', '', $oMain->translate('del_groupman'), $oMain->translate('del_groupman'),$oMain->loading());
			
			$elementos=$elementos+1;

		}


		$oTable = new CTable(null, null, $ArrayRst);
		$oTable->SetSorting();
		$oTable->SetFixedHead (true,400);
		//$oTable->addColumn($oMain->translate('tgpid'), 'left', 'String');
		$oTable->addColumn($oMain->translate('tgpidman'), 'left', 'String');
		$oTable->addColumn($oMain->translate('tcreatedate'), 'left', 'String');
		$oTable->addColumn($oMain->translate('tcreatedby'), 'left', 'String');
		$oTable->addColumn('!', 'center');
		

		$html = $oMain->efaHR($oMain->translate('list_groupman').' &nbsp; | &nbsp; '. $oMain->showHide($oMain->translate('New'), $this->form('insert_groupman'),0,'img/new_s.png','img/new_s.png'));
		$html.= $oTable->getHtmlCode ();

		
		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}
	
	/**
	 * set class CGroupMan atributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->tgpid=$rst['tgpid'];
			$this->tgpidman=$rst['tgpidman'];
			$this->tcreatedate=$rst['tcreatedate'];
			$this->tcreatedby=$rst['tcreatedby'];
			
		}
		return $rc;
	}

}// Enf of CGroupMan




class CMember
{
	var $company;    /**  */
	var $application;    /**  */
	var $groupid;    /**  */
	var $tgpid;  //var to get tgpid from tbgroups
	var $tuserid;    /**  */
	var $tmodifiedby;    /**  */
	var $tmodifdate;    /**  */
	var $copyuserid;    /**  */
	var $copycomp;
	var $copyapplication;
	
	var $userid;  //string userid
	var $sourcetgpid;
	
	
	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class members mod
	 */	
	function getHtml(&$mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='members';
		
		if ($mod =='copy_'.$ent)
		{
			$tstatus=$this->storeIntoDB('copymembers', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			$oMain->default_tab=2;
			$mod='dash_applications';
			return($html);
		}

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			$mod ='listuser_'.$ent;
		}
		
		if ($mod =='delbygroup_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
//print 11;			
			$oMain->default_tab=2;
			$mod='dash_applications';
			return($html);
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
	
			$mod ='listuser_'.$ent;
		}
		
		
		//insert user as memebr into a group
		if ($mod =='insertbygroup_'.$ent)
		{
			//print 111;
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$oMain->default_tab=2;
			$mod='dash_applications';
			
			return($html);
		}
		

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->form('insert_'.$ent);
		}

		if ($mod =='list_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod).' '.$company;
			$html=$this->showList();
		}
		
		if ($mod =='listuser_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			//$html.=$this->showListUserMember();
			
			
			$xNew = new efalayout($this);
			$xNew->subtitle($oMain->translate('new'));
			$xNew->add($this->formInsertuser());
			
			$cont = new efaHiddenBar($this->oMain);
			$cont->add('new')->content($xNew->html())->bar(false);
			$cont->add('user')->content($this->showListUserMember())->bar(false);
			$cont->add('all')->content($this->showListAllUsrGrp())->bar(false);
			$x = new efalayout($this->oMain);
			$x->title($this->oMain->translate($mod))->icon('img/member_s.png');
			$x->toolbar->add('new')->title($this->oMain->translate('new'))->onClick($cont->part('new')->jsShowHide());
			$x->toolbar->add('user')->title($oMain->translate('memberof').' - ('.$oMain->comp.') ')->onClick($cont->part('user')->jsShowHide());
			$x->toolbar->add('all')->title($this->oMain->translate('userallgrps'))->onClick($cont->part('all')->jsShowHide());
			$x->add($cont->html());
			$html = $x->html();
			if($oMain->operation=='onchange') $html .= '<script>'.$cont->part('new')->jsShowHide().'</script>';
				else $html .= '<script>'.$cont->part('user')->jsShowHide().'</script>';

		}
		
		if ($mod =='listalluser_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			
			$html.=$this->showListAllUsrGrp();
			

		}
		
		
		
		if ($mod =='show_'.$ent)
		{
			$this->readFromDB();
			$oMain->subtitle=$oMain->translate('show_'.$ent).' '.$company;
			
			$html=$this->form('show_'.$ent);
		}
		
		
		return $this->oMain->layoutUser($html);
		
		$title=$oMain->Title($this->userid);
		
		$dashboard='<table width=100%><tr valign=top><td width=195 class=row1>'.$oMain->menuUser($this->userid).'</td>
		<td valign=top>'.$title.'<BR>'.$html.'</td></tr></table>';

		return($dashboard);
	}
	
	 /**
	  * read class members atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
//		$this->company=$oMain->comp;
		$this->application=$oMain->GetFromArray('application',$_REQUEST,'string_trim');
		$this->groupid=$oMain->GetFromArray('groupid',$_REQUEST,'string_trim');
		$this->tgpid=$oMain->GetFromArray('tgpid',$_REQUEST,'int');
		
		$this->userid=$oMain->GetFromArray('userid',$_REQUEST,'string_trim');
		
		$this->tuserid=$oMain->GetFromArray('tuserid',$_REQUEST,'int');
		$this->tmodifiedby=$oMain->GetFromArray('tmodifiedby',$_REQUEST,'int');
		$this->tmodifdate=$oMain->GetFromArray('tmodifdate',$_REQUEST,'date');
		$this->copyuserid=$oMain->GetFromArray('copyuserid',$_REQUEST,'string_trim');
		$this->copycomp=$oMain->GetFromArray('copycomp',$_REQUEST,'string_trim');
		$this->copyapplication=$oMain->GetFromArray('copyapplication',$_REQUEST,'string_trim');
		$this->sourcetgpid=$oMain->GetFromArray('sourcetgpid',$_REQUEST,'int');
		
	}
	/**
	 * class members form
	 */	
	function formInsertUserbyGroup($tgpid) //insert user in group from grouptabs
	{
		$oMain=$this->oMain;
		
		$this->tgpid=$tgpid;
		
		$formName='frmmembers'; $operation='';$nCol=3;$width='100%';$ajax=false;
		
		$frmMod=CForm::MODE_EDIT;

		//$onChange="$formName.mod.value='$modChange';$formName.submit(); $oMain->loading;";

		$oForm = $oMain->std_form('insertbygroup_members', $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$aForm = array();

		$aForm[] = new CFormHidden('tgpid',$this->tgpid);
		$aForm[] = new CFormHidden('application',$this->application);
		$aForm[] = new CFormHidden('tnodeid',$this->tgpid);
		
		$search_tuserid=$oMain->stdPopupwin('GETCCUSER',$formName,'tuserid','tuseriddesc','tuserid','tuseriddesc','','employee');	
		$field_tuserid = new CFormText($oMain->translate('tuserid'),'tuserid', $this->tuserid,'',CForm::REQUIRED,false);
		$field_tuserid_desc = new CFormText($oMain->translate('tuserid'), 'tuseriddesc', $this->tuseriddesc, '','',false, '', '', '', 70);
		if($frmMod==CForm::MODE_EDIT)
		   $field_tuserid->setExtraData($search_tuserid);
		$aForm[] = new CFormMultipleElement(array($field_tuserid, $field_tuserid_desc), 0);	
		
		//form buttons
		$onSubmit="$formName.submit(); $oMain->loading;";
		$buttonSave = new CFormButton('insert', $oMain->translate ('insert'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$aForm[]=$buttonSave;	

		$oForm->addElementsCollection($aForm);
		$html_form.=$oForm->getHtmlCode();
		return $html_form;
	}
	
	function formInsertuser() //insert user in group from usertabs
	{
		$oMain=$this->oMain;
		$formName='frmmembers'; $operation='';$nCol=3;$width='100%';$ajax=false;

		$frmMod=CForm::MODE_EDIT;

		$oForm = $oMain->std_form('insert_members', $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$aForm = array();

		//$aForm[] = new CFormHidden('tgpid',$this->tgpid);
		$tuserid=$oMain->getTuserid($this->userid);
		$aForm[] = new CFormHidden('tuserid',$tuserid);
		$aForm[] = new CFormHidden('userid',$this->userid);

		$arrapplbox = $this->getAppLBox();
		$cformsel = new CFormSelect($oMain->translate('application'), 'application', $this->application, '', '', '', $arrapplbox, '', ' ', CForm::REQUIRED);
		$cformsel->addEvent("onChange=\"frmmembers.mod.value='listuser_members'; frmmembers.operation.value='onchange'; frmmembers.submit(); ".$oMain->loading()."\"");
		$aForm[]=$cformsel;
//print $this->application;
		$sql="SELECT tgpid, RTRIM(groupid) + ' - ' + groupdesc AS tdesc FROM dbo.tbgroups WHERE (company = '$oMain->comp') AND (application='$this->application')";
		$aForm[] = new CFormSelect($oMain->translate('groupid'), 'tgpid', $this->tgpid, '', $sql, $oMain->consql, '', '', '', CForm::REQUIRED);
//print $sql;
		//form buttons
		$onSubmit="$formName.submit(); $oMain->loading;";
		$buttonSave = new CFormButton('insert', $oMain->translate ('insert'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$aForm[]=$buttonSave;
		
		//form buttons
		$oForm->addElementsCollection($aForm);
		$html_form.=$oForm->getHtmlCode();
		return $html_form;
	}
	
	
	function formCopyMembers()
{
	$oMain=$this->oMain;
	
	$formName='frm_copymembers'; $operation='';$nCol=3;$width='100%';$ajax=false;
	$frmMod=CForm::MODE_EDIT;
	$oForm = $oMain->std_form('copy_members', $operation,$formName,$nCol,$frmMod,$ajax,$width);
	$aForm = array();
	
	
	$aForm[] = new CFormHidden('tgpid',$this->tgpid);
	$aForm[] = new CFormHidden('application',$this->application);
	$aForm[] = new CFormHidden('tnodeid',$this->tgpid);

	//company listbox
	$sql="SELECT company, company + ' - ' + shortdesc AS shortdesc FROM dbo.tbcompanies WHERE (tstatus <> 'X')";
	$cformsel = new CFormSelect($this->oMain->translate('copyfromcomp'), 'copycomp', $this->copycomp, '', $sql, $oMain->consql, '', '',' ', CForm::REQUIRED);
	$cformsel->addEvent("onChange=\"frm_copymembers.mod.value='dash_applications'; frm_copymembers.operation.value='copymemb'; frm_copymembers.application.value=''; javascript:frm_copymembers.submit(); $loading\"");
	$aForm[]=$cformsel;
	//applications listbox
	$sqlapp="SELECT application, application AS tdesc FROM dbo.tbapplications WHERE (company = '$this->copycomp')";
	$cformsel = new CFormSelect($oMain->translate('application'), 'copyapplication', $this->copyapplication, '', $sqlapp, $oMain->consql, '', '', ' ', CForm::REQUIRED);
	$cformsel->addEvent("onChange=\"frm_copymembers.mod.value='dash_applications'; frm_copymembers.operation.value='copymemb'; frm_copymembers.application.value=''; frm_copymembers.submit(); ".$oMain->loading()."\"");
	$aForm[]=$cformsel;
	
	$sqlgroup="SELECT tgpid, RTRIM(groupid) + ' - ' + groupdesc AS tdesc FROM dbo.tbgroups WHERE (company = '$this->copycomp') AND (application='$this->copyapplication')";
	$aForm[] = new CFormSelect($oMain->translate('groupid'), 'sourcetgpid', $this->sourcetgpid, '', $sqlgroup, $oMain->consql, '', '', ' ', CForm::REQUIRED);


	$buttonSave = new CFormButton('insert', $oMain->translate ('copy'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
	$aForm[]=$buttonSave;

	//form buttons
	$oForm->addElementsCollection($aForm);
	$html_form.=$oForm->getHtmlCode();
	return $html_form;
	
}
	
	function getAppLBox()
	{
		$oMain=$this->oMain;
		
		$sql="SELECT application, application AS tdesc FROM dbo.tbapplications WHERE (company = '$oMain->comp')";
		$rs=dbQuery($this->oMain->consql, $sql, $flds);
		$rc=count($rs);
		
		if($rc==1)
			$array[]= array('tappl' => '',
							'tdsca' => '');
		
		for ($j = 0; $j < $rc; $j++)
		{
			$rst=$rs[$j];

			$array[]= array('tappl' => $rst['application'],
							'tdsca' => $rst['tdesc']);
		}
		
		return($array);
	}
	/**
	 * save class members record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$sid=$this->oMain->sid;
		$sql="[dbo].[spgroupmember] '$sid','$operation'
		,'$this->tgpid'
		,'$this->tuserid'
		,'$this->application'
		,'$this->groupid'
		,'$this->copyuserid'
		,'$this->copycomp'
		,'$this->sourcetgpid'
		";		
//print $sql.'<HR>'; die();
		
		$rs=dbQuery($this->oMain->consql, $sql, $flds);
		$rst=$rs[0];
		$tdesc=$rst['tdesc'];
		return($rst['tstatus']);
	}
	/**
	 * query to get class members record from database
	 */	
	
	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT GRP.tgpid , MEMB.tuserid, GRP.application, GRP.groupid, GRP.company, MEMB.tmodifiedby, MEMB.tmodifdate
				FROM dbo.tbgroups AS GRP 
				INNER JOIN dbo.tbgroupmember AS MEMB ON GRP.tgpid = MEMB.tgpid
				WHERE MEMB.tgpid='$this->tgpid' and MEMB.tuserid='$this->tuserid'";
		
		//print $sql;
		
		return($sql);
	}
	/**
	 * set class members atributes with data from database
	 */	
	function readfromdb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->company=$rst['company'];
			$this->application=$rst['application'];
			$this->groupid=$rst['groupid'];
			$this->tuserid=$rst['tuserid'];
			$this->modifiedby=$rst['modifiedby'];
			$this->modifdate=$rst['modifdate'];
			$this->tgpid=$rst['tgpid'];
			
		}
		return $rc;
	}

function showListGroupMembers($groupid, $application, $tgpid)
{
	$oMain=$this->oMain;
	
	$sql="SELECT TOP 1000 usr.username, usr.email, mem.tmodifiedby, mem.tmodifdate, '' AS toperations, usr.userid, grp.application, grp.groupid, grp.tgpid, usr.employee
				,dbo.efa_uidname(mem.tmodifiedby) as tmodifiedbydesc
			FROM dbo.tbusers AS usr INNER JOIN
				dbo.tbgroupmember AS mem ON usr.employee = mem.tuserid LEFT OUTER JOIN
				dbo.tbgroups AS grp ON mem.tgpid = grp.tgpid
			WHERE  (grp.tgpid = $tgpid) AND (usr.employee <> 0)
			ORDER BY usr.fullname"; //(grp.company = '$oMain->comp') AND
	
	//print $sql;
	
	if($this->groupid=='') 
		$this->groupid=$groupid;
	if($this->application=='') 
		$this->application=$application;
	
	$ArrayRst=array();
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		
		$application=$rs[$r]['application'];
		$username=$rs[$r]['username'];
		$tgpid=$rs[$r]['tgpid'];
		$tuserid=$rs[$r]['employee'];
		$userid=$rs[$r]['userid'];
		$email=$rs[$r]['email'];
		$tmodifiedbydesc=$rs[$r]['tmodifiedbydesc'];
		
		$emailink = "<a href=mailto:$email>$email</a>";
		
		
//		$fields_form="<input type=\"hidden\" name=\"FIELDS[$rs[$r]][tgpid]\" id=\"tgpid$r\" value=\"".$rs[$r]['tgpid']."\"  >";
//		$temnophoto="<img src=\"".$oMain->stdGetUserPicture($rs[$r]['employee'])."\" title=\"".$rs[$r]['username']."\" height=25 onMouseOver=\"return wrapContent(event,'".$oMain->stdGetUserPicture($rs[$r]['employee'])."', 100, null, null, null, 0, 20)\">";
		$param='tuserid='.$tuserid.'&tgpid='.$tgpid.'&application='.$application.'&tnodeid='.$tgpid.'&tgpid='.$tgpid;
		$link=$oMain->stdImglink('show_users', '', '','userid='.$userid, '', $username, '', $oMain->translate('edit_users'), '');	
		
		$ArrayRst[$elementos]['userid']	= $rs[$r]['employee'];
		$ArrayRst[$elementos]['username']	= $temnophoto.' '.$link;
		$ArrayRst[$elementos]['email'] = $emailink;
		$ArrayRst[$elementos]['tmodifiedby']= $tmodifiedbydesc;
		$ArrayRst[$elementos]['tmodifdate']= $oMain->formatDate($rs[$r]['tmodifdate']);
		$ArrayRst[$elementos]['toperations']=$oMain->stdImglink('delbygroup_members', '', '',$param, 'img/delete_s.png', '', '', $oMain->translate('delmembers'), $oMain->translate('confremove'));
		
		$elementos=$elementos+1;
	}
	
	$oTable = new efaGrid($oMain);
	$oTable->skin('dhx_web');
	//$oTable->title($oMain->translate('appgroup')." ($rc)");
	//$oTable->dbClickLink($this->oMain->baseLink('', 'show_ruleanswer', '', 'tvarqid=§§tvarqid§§&tanswervar=§§tanswervar§§'));
	$tableheight=430;
	$oTable->height($tableheight);  
	$oTable->autoExpandHeight(true);
	$oTable->widthUsePercent(true);
	$oTable->data($ArrayRst);
	$oTable->multilineRow(true);

	//$oTable->searchable(false);
//	$oTable->exportToExcel(false);
//	$oTable->exportToPdf(false);
	
	$oTable->columnAdd('userid')->width(7);
	$oTable->columnAdd('username')->width(25);
	$oTable->columnAdd('email')->width(20);
	$oTable->columnAdd('tmodifiedby')->width(20);
	$oTable->columnAdd('tmodifdate')->width(15)->title($oMain->translate('modifdate'));
	$oTable->columnAdd('toperations')->width(5)->title('!')->searchable(FALSE);
					
//	$oTable = new CTable(null, null, $ArrayRst);
//	$oTable->SetSorting();
//	$oTable->SetFixedHead (true,400);
//	$oTable->addColumn($oMain->translate('name'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('email'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
//	$oTable->addColumn('!');
	

	$expand=0;
	if($oMain->operation=='copymemb')
		$expand=1;

	//$excel=$oMain->stdImglink('dash_applications', '', 'EXCEL','&tnodeid='.$this->tgpid.'&tgpid='.$this->tgpid.'&default_tab=2', 'excel_s.png', $oMain->translate('excel') , '', $oMain->translate('excel'));
	$copy=$oMain->showHide($oMain->translate('copymembers'), $this->formCopyMembers(), $expand, 'img/copy_s.png', 'img/copy_s.png','','','class="rowpink"', '  ');
	$add=$oMain->showHide($oMain->translate('insertgrpmember'), $this->formInsertUserbyGroup($tgpid), '', 'img/new_s.png', 'img/new_s.png', '','','class="rowpink"',' &nbsp; | &nbsp; '.$copy);

	$html.= $oMain->efaHR($oMain->translate('group').' &nbsp; | &nbsp; '.$this->groupid.' &nbsp; | &nbsp; '.$add);
	
	$html_table.= $oTable->html();
	
	
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords').' (1000)');}

	$html.=$html_table;



	return($html);
}

function showListGroupMembersXXXX($groupid, $application, $tgpid)
{
	$oMain=$this->oMain;
	
	$top='TOP 750';
	if($oMain->operation=='EXCEL')
		$top='';

	$sql="SELECT $top usr.username, usr.email, mem.tmodifiedby, mem.tmodifdate, '' AS toperations, usr.userid, grp.application, grp.groupid, grp.tgpid, usr.employee
				,dbo.efa_uidname(mem.tmodifiedby) as tmodifiedbydesc
			FROM dbo.tbusers AS usr INNER JOIN
				dbo.tbgroupmember AS mem ON usr.employee = mem.tuserid LEFT OUTER JOIN
				dbo.tbgroups AS grp ON mem.tgpid = grp.tgpid
			WHERE  (grp.tgpid = $tgpid) AND (usr.employee <> 0)
			ORDER BY usr.fullname"; //(grp.company = '$oMain->comp') AND
	
	//print $sql;
	
	if($this->groupid=='') 
		$this->groupid=$groupid;
	if($this->application=='') 
		$this->application=$application;
	
	$ArrayRst=array();
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		
		$application=$rs[$r]['application'];
		$username=$rs[$r]['username'];
		$tgpid=$rs[$r]['tgpid'];
		$tuserid=$rs[$r]['employee'];
		$userid=$rs[$r]['userid'];
		$email=$rs[$r]['email'];
		$tmodifiedbydesc=$rs[$r]['tmodifiedbydesc'];
		
		$emailink = "<a href=mailto:$email>$email</a>";
		
		if($oMain->operation<>'EXCEL')
		{
			$fields_form="<input type=\"hidden\" name=\"FIELDS[$rs[$r]][tgpid]\" id=\"tgpid$r\" value=\"".$rs[$r]['tgpid']."\"  >";
			//$temnophoto="<img src=\"http://intranet.efacec.pt/rh/foto.php?nmec=".$rs[$r]['employee']."\" title=\"".$rs[$r]['username']."\" height=25 onMouseOver=\"return wrapContent(event,'http://intranet.efacec.pt/rh/foto.php?nmec=".$rs[$r]['employee']."', 100, null, null, null, 0, 20)\">";
			$temnophoto="<img src=\"".$oMain->stdGetUserPicture($rs[$r]['employee'])."\" title=\"".$rs[$r]['username']."\" height=25 onMouseOver=\"return wrapContent(event,'".$oMain->stdGetUserPicture($rs[$r]['employee'])."', 100, null, null, null, 0, 20)\">";
			
			
			$param='tuserid='.$tuserid.'&tgpid='.$tgpid.'&application='.$application.'&tnodeid='.$tgpid.'&tgpid='.$tgpid;
			$link=$oMain->stdImglink('show_users', '', '','userid='.$userid, '', $username.' - '.$userid, '', $oMain->translate('edit_users'), '');	
		}
		
		if($oMain->operation=='EXCEL')
		{
			$ArrayRst[$elementos]['userid'] = $userid;
			$ArrayRst[$elementos]['employee'] = $tuserid;
			$ArrayRst[$elementos]['username']	= $username;
			$ArrayRst[$elementos]['email'] = $email;
		}
		else
		{
			$ArrayRst[$elementos]['username']	= $temnophoto.' '.$link;
			$ArrayRst[$elementos]['email'] = $emailink;
		}
		
		$ArrayRst[$elementos]['tmodifiedby']= $tmodifiedbydesc.' - '.$rs[$r]['tmodifiedby'];
		$ArrayRst[$elementos]['tmodifdate']= $oMain->formatDate($rs[$r]['tmodifdate']);
		
		if($oMain->operation<>'EXCEL')
			$ArrayRst[$elementos]['toperations']=$oMain->stdImglink('delbygroup_members', '', '',$param, 'img/delete_s.png', '', '', $oMain->translate('delmembers'), $oMain->translate('confremove'));
		
		
		$elementos=$elementos+1;
	}
					
	$oTable = new CTable(null, null, $ArrayRst);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);
	if($oMain->operation=='EXCEL')
	{
		$oTable->addColumn($oMain->translate('userid'), 'left', 'String');
		$oTable->addColumn($oMain->translate('employee'), 'left', 'int');
	}
	$oTable->addColumn($oMain->translate('name'), 'left', 'String');
	$oTable->addColumn($oMain->translate('email'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
	if($oMain->operation<>'EXCEL')
		$oTable->addColumn('!');
	
	if($oMain->operation<>'EXCEL')
	{
		$form_lines=$oMain->stdForm('', '', $form_name);

		$html="<script type=\"text/javascript\" src=\"AddIn/show.js\"></script>
				 <div id=\"dragDiv\" style=\"position:absolute; visibility:hidden;  left:0; top:0; z-index:1000\"></div>";
		
		$expand=0;
		if($oMain->operation=='copymemb')
			$expand=1;

		$excel=$oMain->stdImglink('dash_applications', '', 'EXCEL','&tnodeid='.$this->tgpid.'&tgpid='.$this->tgpid.'&default_tab=2', 'excel_s.png', $oMain->translate('excel') , '', $oMain->translate('excel'));
		$copy=$oMain->showHide($oMain->translate('copymembers'), $this->formCopyMembers(), $expand, 'img/copy_s.png', 'img/copy_s.png','','','class="rowpink"', ' &nbsp; | &nbsp; '.$excel);
		$add=$oMain->showHide($oMain->translate('insertgrpmember'), $this->formInsertUserbyGroup($tgpid), '', 'img/new_s.png', 'img/new_s.png', '','','class="rowpink"',' &nbsp; | &nbsp; '.$copy);
	
		$html.= $oMain->efaHR($oMain->translate('group').' &nbsp; | &nbsp; '.$this->groupid.' &nbsp; | &nbsp; '.$add);
	}
	

	if($oMain->operation=='EXCEL')
		$oTable->setOutputToExcel(TRUE);
	
	$html_table.= $oTable->getHtmlCode ();
	
	if($oMain->operation<>'EXCEL')
	{
		if($rc>=750) {$oMain->setwarning($oMain->translate('toomanyrecords').' (750)');}

		$html.="<table width=98%>$form_lines <tr><td>$html_table</td></tr></form></table>";
	}
	
	if($oMain->operation=='EXCEL')
		return ($html_table);
	else
		return($html);
}

function showListUserMember() // Show groups that tuserid is member of
{
	$oMain=$this->oMain;
//print 11;	
	$tuserid=$oMain->getTuserid($this->userid);
	
	$sql="SELECT GRP.application, GRP.groupid, GRP.profile, dbo.efa_uidname(MENB.tmodifiedby) AS tmodifiedby, MENB.tmodifdate, '' AS toperations, MENB.tuserid, MENB.tgpid
		,GRP.tgpid, GRP.company ,(SELECT userid from dbo.tbusers where employee=MENB.tuserid) as userid
		FROM dbo.tbgroupmember AS MENB 
		INNER JOIN dbo.tbgroups AS GRP ON MENB.tgpid = GRP.tgpid
		WHERE (MENB.tuserid = $tuserid) and GRP.company='$oMain->comp' AND GRP.tstatus='A'
		ORDER BY GRP.application, GRP.groupid";
	
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		
		$groupid=$rs[$r]['groupid'];
		$application=$rs[$r]['application'];
		$company=$rs[$r]['company'];
		$tgpid=$rs[$r]['tgpid'];
		$tuserid=$rs[$r]['tuserid'];
		$userid=$rs[$r]['userid'];

		$appparam='tnodeid=APP_'.strtoupper($application).'&tappl='.strtoupper($application).'&company='.$company;
		$grpparam='tnodeid='.$tgpid.'&company='.$company.'&tgpid='.$tgpid;
		$membersparam='tgpid='.$tgpid.'&tuserid='.$tuserid.'&userid='.$userid;

		$rs[$r]['application']=$oMain->stdImglink('dash_applications', '', '',$appparam, '', $application, '', $oMain->translate('editapp'), '');
		$rs[$r]['groupid']=$oMain->stdImglink('dash_applications', '', 'groupid',$grpparam, '', $groupid, '', $oMain->translate('editgroups'), '');
		$rs[$r]['tmodifdate']= $oMain->formatDate($rs[$r]['tmodifdate']);
		
		$rs[$r]['toperations']=$rs[$r]['toperations']=$oMain->stdImglink('del_members', '', '',$membersparam, 'img/delete_s.png', '', '', $oMain->translate('delmembersgroup'), $oMain->translate('confremove'));			
	}
	
	$oTable = new efaGrid($oMain);
	$oTable->skin('dhx_web');
	//$oTable->title($oMain->translate('appgroup')." ($rc)");
	//$oTable->dbClickLink($this->oMain->baseLink('', 'show_ruleanswer', '', 'tvarqid=§§tvarqid§§&tanswervar=§§tanswervar§§'));
	$tableheight=450;
	$oTable->height($tableheight);  
	$oTable->autoExpandHeight(true);
	$oTable->widthUsePercent(true);
	$oTable->data($rs);
	$oTable->multilineRow(true);

	//$oTable->searchable(false);
	$oTable->exportToExcel(false);
	$oTable->exportToPdf(false);
	
	$oTable->columnAdd('application')->width(20);
	$oTable->columnAdd('groupid')->width(20);
	$oTable->columnAdd('profile')->width(15);
	$oTable->columnAdd('modifiedby')->width(20);
	$oTable->columnAdd('modifdate')->width(10);
	$oTable->columnAdd('toperations')->width(10)->title('!');
	
	//$this->toolbar('list_applications',$oTable->toolbar);
	
	//$html=$oTable->html();
					
//	$oTable = new CTable(null, null, $rs);
//	$oTable->SetSorting();
//	$oTable->SetFixedHead (true,400);
//
//	$oTable->addColumn($oMain->translate('application'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('groupid'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('profile'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
//	$oTable->addColumn('!');
	
	
	$x = new efalayout($this);
	$x->subtitle($oMain->translate('memberof').' - ('.$oMain->comp.')');
	$x->add($oTable->html());
	return $x->html();
	
	$expand=0;
	if($oMain->operation=='onchange')
		$expand=1;
	//$new=$oMain->showHide($oMain->translate('new'), $this->formInsertuser(),$expand,'img/new_s.png','img/new_s.png', '', '', 'class="rowpink"');
	
	
	$allcomps=$oMain->stdImglink('listalluser_members', '', '', 'userid='.$this->userid, 'img/comps_s.png', $oMain->translate('userallgrps'), '', '', '', $oMain->loading());
	
	
	$html = $oMain->efaHR($oMain->translate('memberof').' - ('.$oMain->comp.') &nbsp; | &nbsp; '.$allcomps.' &nbsp; | &nbsp; '.$new);
	
	
	$html.= $oTable->getHtmlCode();
	
	
	//If($rc==0) {return $oMain->translate('nosearchresults');}
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}


function showListAllUsrGrp() // Show groups that userid is member of in all companies execpt omain comp
{
	$oMain=$this->oMain;
//print 111;	
	$tuserid=$oMain->getTuserid($this->userid);
	
	$sql="SELECT top 5000 GRP.company, GRP.application, GRP.groupid, GRP.profile, dbo.efa_uidname(MENB.tmodifiedby) AS tmodifiedby, MENB.tmodifdate, '' AS toperations, MENB.tuserid, MENB.tgpid
		,GRP.tgpid, GRP.company
		FROM dbo.tbgroupmember AS MENB 
		INNER JOIN dbo.tbgroups AS GRP ON MENB.tgpid = GRP.tgpid
		WHERE MENB.tuserid = $tuserid  AND GRP.tstatus='A' and GRP.company!='AUT'
		ORDER BY GRP.company,GRP.application, GRP.groupid";
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		$application=$rs[$r]['application'];
		$groupid=$rs[$r]['groupid'];
		$company=$rs[$r]['company'];
		$tgpid=$rs[$r]['tgpid'];
		
		$appparam='tnodeid=APP_'.strtoupper($application).'&tappl='.strtoupper($application).'&comp='.$company;
		$grpparam='tnodeid='.$tgpid.'&tgpid='.$tgpid.'&comp='.$company.'&tgpid='.$tgpid;
		
		$rs[$r]['application']=$oMain->stdImglink('dash_applications', '', '',$appparam, '', $application, '', $oMain->translate('editapp'), '');
		$rs[$r]['groupid']=$oMain->stdImglink('dash_applications', '', 'groupid',$grpparam, '', $groupid, '', $oMain->translate('editgroups'), '');
		
		$rs[$r]['tmodifdate']= $oMain->formatDate($rs[$r]['tmodifdate']);		
	}
	
	$oTable = new efaGrid($oMain);
	$oTable->skin('dhx_web');
	//$oTable->title($oMain->translate('appgroup')." ($rc)");
	//$oTable->dbClickLink($this->oMain->baseLink('', 'show_ruleanswer', '', 'tvarqid=§§tvarqid§§&tanswervar=§§tanswervar§§'));
	$tableheight=450;
	$oTable->height($tableheight);  
	$oTable->autoExpandHeight(true);
	$oTable->widthUsePercent(true);
	$oTable->data($rs);
	$oTable->multilineRow(true);

	//$oTable->searchable(false);
	$oTable->exportToExcel(false);
	$oTable->exportToPdf(false);
	
	$oTable->columnAdd('company')->width(10)->searchtype('select');
	$oTable->columnAdd('application')->width(20);
	$oTable->columnAdd('groupid')->width(20);
	$oTable->columnAdd('profile')->width(15);
	$oTable->columnAdd('modifiedby')->width(20);
	$oTable->columnAdd('modifdate')->width(10);
	//$oTable->columnAdd('toperations')->width(10)->title('!');
					
//	$oTable = new CTable(null, null, $rs);
//	$oTable->SetSorting();
//	$oTable->SetFixedHead (true,400);
//
//	$oTable->addColumn($oMain->translate('company'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('application'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('groupid'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('profile'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
	//$oTable->addColumn('!');
	
	
	$x = new efalayout($this);
	$x->subtitle($oMain->translate('userallgrps'));
	$x->add($oTable->html());
	return $x->html();
	
	$comp=$oMain->stdImglink('listuser_members', '', '', 'userid='.$this->userid, 'img/comps_s.png', $oMain->translate('memberof').' - ('.$oMain->comp.')', '', '', '', $oMain->loading());
	$html = $oMain->efaHR( $oMain->translate('userallgrps').' &nbsp; | &nbsp; '.$comp);
	
	$html.= $oTable->getHtmlCode();
	
	
	//If($rc==0) {return $oMain->translate('nosearchresults');}
	if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}
	

}// Enf of members
?>
