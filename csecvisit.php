<?php
/**
 * @@name	SecurityPro - Visitas
 * @@author	Luis Gomes
 * @@version 	10-02-2017 15:23:54
 *
 * Revisions:
 * 2018-05-17	Luis Gomes	I1804_01475
 * 2018-02-22	Luis Gomes	Novos campos de tbsecvisit: @tncompanion, @tcomp1, @tcomp2, @tcomp3	
 */

class CSecVisit
{
	var $taccessType;
	var $tvisitid;		/** Registo de visita */
	var $tsite;
	var $tdatin;		/** Entrada */
	var $tdatout;		/** Saida */
	var $tnama;			/** Nome do visitante */
	var $tentr;			/** Empresa */
	var $tregistration;	/** Matricula */
	var $temployee;		/** Visitado */
	var $templname;		/** Nome visitado */
	var $tdivi;			/** Empresa/Divisão */
	var $tphone;		/** Ext. tel. */
	var $treason;		/** Motivo */
	var $tremarks;		/** Notas */
	var $tmodifiedby;   /** Modificado por */
	var $tmodifiedbydesc;   /** Modificado por */
	var $tmodifdate;    /** Data de Modificação */

	var $tindt;
	var $texdt;
	var $tstatus;

	var $tncompanion; 
	var $tcomp1;
	var $tcomp2;
	var $tcomp3;
	
	/**
	 * constructor
	 */
	function  __construct($oMain,$readFromRequest=true)
	{
		$this->oMain=$oMain;
		$this->tsite	= $oMain->getFromArray('tsite',$_REQUEST);
		if($readFromRequest==TRUE)  
			$this->readFromRequest();   
	}

	/**
	 * set class CSecVisit mod
	 */	
	function getHtml($mod, $completeLayout=true)
	{
		$oMain=$this->oMain;
		$ent='secvisit'; 

		if ($mod =='checkout_'.$ent)
		{
			$tstatus=$this->storeIntoDB('checkout', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)	{ $mod='';}
			else			{ $mod='show_'.$ent;} // user retry
		}

/* 		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)	{ $mod='events_'.$ent;}
			else			{ $mod='show_'.$ent;} // user retry 			
		} */

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)	{ $mod='show_'.$ent;}
			else			{ $mod='xnew_'.$ent;} // user retry
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)	{ $mod='show_'.$ent;}
			else			{ $mod='xedit_'.$ent;} // user retry
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			if($mod =='edit_'.$ent)
			{	$this->readFromDb(); }
			$title=$oMain->translate('edit_'.$ent).' '.$this->tvisitid;
			$html=$this->form('update_'.$ent,'xedit_'.$ent);

		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$title=$oMain->translate('new_'.$ent);
			$html=$this->form('insert_'.$ent,'xnew_'.$ent);
		}

		if ($mod =='formsearch_'.$ent or $mod =='dosearch_'.$ent)
		{
			$title=$oMain->translate('search_'.$ent).': '.$this->tsite;
			$html=$this->formSearch('dosearch_'.$ent).$this->showList();
		}
                
		if ($mod =='list_'.$ent)
		{
			$title=$oMain->translate($mod).' '.$this->tvisitid;
			$html=$this->showList();
		}
                
		if ($mod =='events_'.$ent)
		{			
			$this->readFromDb();
			$title=$oMain->translate('show_'.$ent).' '.
			$oMain->stdImglink('show_secvisit', '','',"tvisitid=$this->tvisitid",'',$this->tvisitid,'', $oMain->translate('linktvisitid')).' ('.$this->tnama.')';
			$html=$this->showEvents();
		}

		if ($mod =='show_'.$ent)
		{
			$this->readFromDb();
			$title=$oMain->translate('show_'.$ent).' '.
			$oMain->stdImglink('show_secvisit', '','',"tvisitid=$this->tvisitid",'',$this->tvisitid,'', $oMain->translate('linktvisitid')).' ('.$this->tnama.')';
			//$html=$this->form('show_'.$ent);
			$html=$this->showData();
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
			//use pattern it to split screen in layouts
            //$x->pattern('2U');  
            $x->title($title);
            //format toolbar
            $this->toolbar($mod,$x->toolbar);     
			//use menuTree create a tree in layout
            //$x->add($this->menuTree($mod));
            $x->add($html);
            return $x->html();          
        }
        
	protected function toolbar($mod,$maintoolbar)
	{
		$oMain=$this->oMain;
		if($mod=='show_secvisit')
		{
			$maintoolbar->add('edit_secvisit')->link($oMain->BaseLink('','edit_secvisit','','&tvisitid='.$this->tvisitid))->title($oMain->translate('edit_secvisit'))->tooltip($oMain->translate('edit_secvisit'))->efaCIcon('edit.png');                          

			if($this->tdatout<$this->tdatin)
			$maintoolbar->add('checkout_secvisit')->link($oMain->BaseLink('','checkout_secvisit','','&tvisitid='.$this->tvisitid))
				->title($oMain->translate('checkout'))->tooltip($oMain->translate('checkout'))->efaCIcon('approve3.png')->linkConfirm($oMain->translate('checkout_secvisit'));

				$maintoolbar->add('events_secvisit')->link($oMain->BaseLink('','events_secvisit','','&tvisitid='.$this->tvisitid))->title($oMain->translate('events'))->tooltip($oMain->translate('events_secvisit'))->efaCIcon('events.png');                          
//			$maintoolbar->add('del_secvisit')->link($oMain->BaseLink('','del_secvisit','','&tvisitid='.$this->tvisitid))
//				->title($oMain->translate('delete'))->tooltip($oMain->translate('delete'))->efaCIcon('delete.png')->linkConfirm($oMain->translate('del_secvisit'));
		}
	}
            
	 /**
	  * read class CSecVisit atributes from request
	  */	
	protected function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->tvisitid=$oMain->GetFromArray('tvisitid',$_REQUEST,'int');
		$this->tsite=$oMain->GetFromArray('tsite',$_REQUEST,'string_trim');
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

		$this->tindt=$oMain->GetFromArray('tindt',$_REQUEST,'date');
		$this->texdt=$oMain->GetFromArray('texdt',$_REQUEST,'date');
		
		$this->tncompanion=$oMain->GetFromArray('tncompanion',$_REQUEST,'int');
		$this->tcomp1=$oMain->GetFromArray('tcomp1',$_REQUEST,'string_trim');
		$this->tcomp2=$oMain->GetFromArray('tcomp2',$_REQUEST,'string_trim');
		$this->tcomp3=$oMain->GetFromArray('tcomp3',$_REQUEST,'string_trim');
	}
	/**
	 * class CSecVisit form
	 */	
	protected function form($mod='show_secvisit',$modChange='')
	{
		$oMain=$this->oMain;
		$CSecVisit_readonly=true;$new=false;
		$titleForm = ''; // To set form Title write set text here
		$html_form=$oMain->stdJsPopUpWin('400');
		$formName='frmCSecVisit'; $operation='';$nCol=2;$width='100%';$ajax=false;
		$modCancel='show_secvisit';

		$frmMod=CForm::MODE_EDIT;
		if($mod=='show_secvisit')
			$frmMod=CForm::MODE_VIEW;

		if($mod=='insert_secvisit')
		{ 
			$CSecVisit_readonly=false;
			$new=true;
			$this->tdatin=time(); //strtotime(date("Y-m-d",mktime (0,0,0,date("m"),date("d"),date("Y"))));
		}

		$onChange="$formName.mod.value='$modChange';$formName.submit(); ".$oMain->loading();

		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$oForm->setWaitActionOnSubmit($oMain->loading());
		$aForm = array();
		//$oForm->setLabelWidthRatio(0.15);
		//general
		$aForm[] = new CFormText($oMain->translate('tvisitid'),'tvisitid', $this->tvisitid,10,CForm::REQUIRED,true,'',CFormText::INPUT_INTEGER);
		
		$sql="SELECT tsite, tsite AS tdesc FROM dbo.tbsecconfig WHERE tstatus='A' ORDER BY tsite";
		$elem = new CFormSelect($oMain->translate('tsite'), 'tsite', $this->tsite, $this->tsite, $sql, $oMain->consql,'',' ',' ',CForm::REQUIRED);
		$aForm[] = $elem;		
		
//		$aForm[] = new CFormText($oMain->translate('tsite'),'tsite', $this->tsite,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormEfaDate($oMain->translate('tdatin'), 'tdatin', $oMain->formatDate($this->tdatin, true),CForm::RECOMMENDED,false, '', '', '', true);
		
		$aForm[] = new CFormEfaDate($oMain->translate('tdatout'), 'tdatout', $oMain->formatDate($this->tdatout,true),'',false, '', '', '', true);
		$aForm[] = new CFormTitle($oMain->translate('visitor'),'t3');
		$elem = $aForm[] = new CFormText($oMain->translate('tnama'),'tnama', $this->tnama,50,CForm::REQUIRED,false,'',CFormText::INPUT_STRING);
		$elem->setNumberOfColumns(2);
		$aForm[] = new CFormText($oMain->translate('tentr'),'tentr', $this->tentr,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tregistration'),'tregistration', $this->tregistration,20,'',false,'',CFormText::INPUT_STRING);

		$aForm[] = new CFormText($oMain->translate('tncompanion'),'tncompanion', $this->tncompanion,4,CForm::RECOMMENDED,false,'',CFormText::INPUT_INTEGER);		
		$aForm[] = new CFormTextArea($oMain->translate('tcomp1'), 'tcomp1', $this->tcomp1, 3,'',false);
//		$aForm[] = new CFormText($oMain->translate('tcomp1'),'tcomp1', $this->tcomp1,50,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tcomp2'),'tcomp2', $this->tcomp2,50,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tcomp3'),'tcomp3', $this->tcomp3,50,'',false,'',CFormText::INPUT_STRING);				
		
		$aForm[] = new CFormTitle($oMain->translate('visited'),'t4');
		
		$search_tuserid=$oMain->stdPopupwin('GETCCUSER',$formName,'temployee','tuseridName','temployee','tuseridName','efa','employee');	
		$tuserid = new CFormText($oMain->translate('temployee'),'temployee', $this->temployee,'','',false,'tuseridDist');
		$tuseridName = new CFormText($oMain->translate('temployee'), 'tuseridName', $this->templname, '','',false, 'tuseridNameDist', '', '', 70);
		$tuseridName->setExtraData($search_tuserid);
		$tuserid->addEvent('onchange="updateUsernameField(\'tuseridDist\', \'tuseridNameDist\');"');
		$elem = new CFormMultipleElement(array($tuserid, $tuseridName), 1);
		$aForm[] = $elem;
		
//		$aForm[] = new CFormText($oMain->translate('temployee'),'temployee', $this->temployee,10,'',false,'',CFormText::INPUT_INTEGER);
		$aForm[] = new CFormText($oMain->translate('templname'),'templname', $this->templname,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tdivi'),'tdivi', $this->tdivi,25,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tphone'),'tphone', $this->tphone,25,'',false,'',CFormText::INPUT_STRING);
		//$aForm[] = new CFormFree();
		$elem = $aForm[] = new CFormText($oMain->translate('treason'),'treason', $this->treason,1024,'',false,'',CFormText::INPUT_STRING);
		$elem->setNumberOfColumns(2);
		$elem =$aForm[] = new CFormText($oMain->translate('tremarks'),'tremarks', $this->tremarks,1024,'',false,'',CFormText::INPUT_STRING);
		$elem->setNumberOfColumns(2);
//		$aForm[] = new CFormText($oMain->translate('tmodifiedby'),'tmodifiedby', $this->tmodifiedby,0,'',true,'',CFormText::INPUT_STRING);
//		$aForm[]  = new CFormEfaDate($oMain->translate('tmodifdate'), 'tmodifdate', $oMain->formatDate($this->tmodifdate),'',true);
		

		//form buttons
		$onSubmit="$formName.submit(); ".$oMain->loading().";";
		$buttonSave = new CFormButton('save', $oMain->translate ('save'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);
		$aForm[]=$buttonSave;
		$buttonCancel = new CFormButton('cancel', $oMain->translate ('cancel'),CFormButton::TYPE_BUTTON,'',CFormButton::LOCATION_FORM_BOTTOM);
		$buttonCancel->addEvent("onclick=\"$formName.mod.value='$modCancel';$onSubmit\"");
		$aForm[]=$buttonCancel;
		$oForm->addElementsCollection($aForm);
		
		$html_form.=$oForm->getHtmlCode();

		//if operation is new by defaut sets title
//		if(!$new) $titleForm = $oMain->translate('$mod').' - '.$oMain->translate('tvisitid');

		//if no title returns form
		if($titleForm=='')  return $html_form;

		//if exists title sets layout title+form
		$x = new efalayout($this);
		$x->pattern('1C');
		$x->title($titleForm);
		$x->add($html_form);
		return $x->html();
	}

	/**
	 * class CSecVisit showData
	 */	
	protected function showData()
	{
		$oMain=$this->oMain;
		$tcomps= str_replace(chr(13), '', $this->tcomp1);
		$tcomps= str_replace(chr(10), '|', $tcomps);
		$data = array();
		$data[] = array($oMain->translate('tvisitid'), $this->tvisitid);
		$data[] = array($oMain->translate('tsite'), $this->tsite);
		$data[] = array($oMain->translate('tdatin'), $oMain->formatDate($this->tdatin,true));
		$data[] = array($oMain->translate('tdatout'), $oMain->formatDate($this->tdatout,true));
		$data[] = array($oMain->translate('tnama'), $this->tnama);
		$data[] = array($oMain->translate('tentr'), $this->tentr);
		$data[] = array($oMain->translate('tregistration'), $this->tregistration);
		
		$data[] = array($oMain->translate('tncompanion'), $this->tncompanion);
		$data[] = array($oMain->translate('tcomp1'), $tcomps,2);
//		$data[] = array($oMain->translate('tcomp2'), $this->tcomp2);
//		$data[] = array($oMain->translate('tcomp3'), $this->tcomp3);
		
		$data[] = array($oMain->translate('temployee'), $this->temployee);
		$data[] = array($oMain->translate('templname'), $this->templname);
		$data[] = array($oMain->translate('tdivi'), $this->tdivi);
		$data[] = array($oMain->translate('tphone'), $this->tphone,2);
		$data[] = array($oMain->translate('treason'), $this->treason,2);
		$data[] = array($oMain->translate('tremarks'), $this->tremarks,2);
		$data[] = array($oMain->translate('tmodifiedby'), $this->tmodifiedbydesc);
		$data[] = array($oMain->translate('tmodifdate'), $oMain->formatDate($this->tmodifdate));

		$x = new efaDataDisplay($this->oMain);
		$x->cols(2);
		$x->labelWidth(30);
		$x->data($data);

		return $x->html(); 
	}	
	
	/**
	 * save class CSecVisit record into database
	 */	
	protected function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="[dbo].[spsecvisit] @sid='$sid',@sp_operation='$operation',@norecordset='0',@tvisitid='$this->tvisitid'
		,@tsite='$this->tsite'
		,@itdatin='$this->tdatin'
		,@itdatout='$this->tdatout'
		,@tnama='$this->tnama'
		,@tentr=N'$this->tentr'
		,@tregistration='$this->tregistration'
		,@temployee='$this->temployee'
		,@templname='$this->templname'
		,@tdivi='$this->tdivi'
		,@tphone='$this->tphone'
		,@treason=N'$this->treason'
		,@tremarks=N'$this->tremarks'
		,@tncompanion=N'$this->tncompanion'
		,@tcomp1=N'$this->tcomp1'
		,@tcomp2=N'$this->tcomp2'
		,@tcomp3=N'$this->tcomp3'			
		"; //print $sql; exit;
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
		$this->tvisitid=$rs[0]['tvisitid'];
		return($rs[0]['tstatus']);
	}

	/**
	 * set class CSecVisit atributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql="SELECT tvisitid,tsite,tdatin,tdatout,tnama,tentr,tregistration,temployee
			,templname,tdivi,tphone,treason,tremarks,tmodifiedby, dbo.efa_uidname(tmodifiedby) AS tmodifiedbydesc
			,tmodifdate, tncompanion, tcomp1, tcomp2, tcomp3			
		 FROM dbo.tbsecvisit WITH (NOLOCK) WHERE tvisitid='$this->tvisitid'"; //print $sql; exit;	
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{			
			$this->tvisitid=$rs[0]['tvisitid'];
			$this->tdatin=$rs[0]['tdatin'];
			$this->tsite=$rs[0]['tsite'];
			$this->tdatout=$rs[0]['tdatout'];
			$this->tnama=$rs[0]['tnama'];
			$this->tentr=$rs[0]['tentr'];
			$this->tregistration=$rs[0]['tregistration'];
			$this->temployee=$rs[0]['temployee'];
			$this->templname=$rs[0]['templname'];
			$this->tdivi=$rs[0]['tdivi'];
			$this->tphone=$rs[0]['tphone'];
			$this->treason=$rs[0]['treason'];
			$this->tremarks=$rs[0]['tremarks'];
			$this->tmodifiedby=$rs[0]['tmodifiedby'];
			$this->tmodifiedbydesc=$rs[0]['tmodifiedbydesc'];
			$this->tmodifdate=$rs[0]['tmodifdate'];

			$this->tncompanion=$rs[0]['tncompanion'];
			$this->tcomp1=$rs[0]['tcomp1'];
			$this->tcomp2=$rs[0]['tcomp2'];
			$this->tcomp3=$rs[0]['tcomp3'];			
		}
		return $rc;
	}
	
        
	/**
	 * show list results
	 */		
	protected function showList()
	{
		$oMain=$this->oMain;
		
		$cond=" dbo.secaccesstype('V', '$this->tsite',$oMain->employee)<>'' AND (tsite = '".$this->tsite."') ";
		if($this->tvisitid>0)	$cond.=" AND (tvisitid = '$this->tvisitid')";
		if($this->tindt>0)		$cond.=" AND (tdatin >= '".date('m/d/Y', $this->tindt)."')";
		if($this->texdt>0)		$cond.=" AND (tdatin <= '".date('m/d/Y', $this->texdt)." 23:59')";
		if($this->tnama!='')	$cond.=" AND (tnama LIKE '%$this->tnama%')";
		if($this->tentr!='')	$cond.=" AND (tentr LIKE '%$this->tentr%')";
		if($this->tregistration!='')	$cond.=" AND (tregistration LIKE '%$this->tregistration%')";
		if($this->templname!='') $cond.=" AND (templname LIKE '%$this->templname%')";
		if($this->tdivi!='')	$cond.=" AND (tdivi LIKE '%$this->tdivi%')";
				
		$sql="SELECT TOP 5000 tvisitid,tdatin,tdatout,tnama,tentr,tregistration,temployee,templname,tdivi,tphone,treason,tremarks,tmodifiedby,tmodifdate
		 FROM dbo.tbsecvisit WITH (NOLOCK) WHERE $cond ORDER BY tvisitid DESC"; //print $sql; exit;
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$tparam='tvisitid='.$rs[$r]['tvisitid'];
			$link_tvisitid=$oMain->stdImglink('show_secvisit', '','',$tparam,'',$rs[$r]['tvisitid'],'', $oMain->translate('linktvisitid'));
			
			$rs[$r]['templname']=$rs[$r]['templname'].' | '.$rs[$r]['temployee'];
//			$rs[$r]['toperations']= $oMain->stdImglink( 'edit_secvisit', '','','&tvisitid='.$rs[$r]['tvisitid'], 'edit_s.png', $oMain->translate('edit'));
//			$rs[$r]['toperations'].= $oMain->stdImglink( 'del_secvisit', '','','&tvisitid='.$rs[$r]['tvisitid'], 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
			$rs[$r]['tvisitid']=$oMain->stdImglink('show_secvisit', '','',$tparam,'',$rs[$r]['tvisitid']);
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_secvisit', '', 'tvisitid=§§tvisitid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
	   $oTable->columnAdd('tvisitid')->type('int')->title($this->tsite);
//		$oTable->columnAdd('tsite');
		$oTable->columnAdd('tdatin')->type('datetime');
		$oTable->columnAdd('tdatout')->type('datetime');
		$oTable->columnAdd('tnama');
		$oTable->columnAdd('tentr');
		$oTable->columnAdd('tregistration');
//		$oTable->columnAdd('temployee')->type('int');
		$oTable->columnAdd('templname');
		$oTable->columnAdd('tdivi');
		$oTable->columnAdd('tphone');
		$oTable->columnAdd('treason');
//		$oTable->columnAdd('tremarks');
//		$oTable->columnAdd('tmodifiedby');
//		$oTable->columnAdd('tmodifdate')->type('date');
		
	   $html=$oTable->html();                

		if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}	

	/**
	 * show events
	 */		
	protected function showEvents()
	{
		$oMain=$this->oMain;
		$sql="SELECT TOP (5000) trefa as tvisitid, tuserid, tdate, tdeviceid, tremarks
FROM  dbo.tbeventcom WITH (NOLOCK) 
WHERE (tmodule = 'securitypro') AND (ttype = 'SECV') AND (trefa = '$this->tvisitid')";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$link_tvisitid=$oMain->stdImglink('show_secvisit', '','',$tparam,'',$rs[$r]['tvisitid'],'', $oMain->translate('linktvisitid'));
					
			$rs[$r]['toperations']= $oMain->stdImglink( 'edit_secvisit', '','','&tvisitid='.$rs[$r]['tvisitid'], 'edit_s.png', $oMain->translate('edit'));
			$rs[$r]['toperations'].= $oMain->stdImglink( 'del_secvisit', '','','&tvisitid='.$rs[$r]['tvisitid'], 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_secvisit', '', 'tvisitid=§§tvisitid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
		$oTable->columnAdd('tvisitid')->type('int')->hidded(true);
		$oTable->columnAdd('tuserid')->type('int');
		$oTable->columnAdd('tdate')->type('datetime');
		$oTable->columnAdd('tremarks');
		
	   $html=$oTable->html();                

		if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}	
	
	
	protected function formSearch($mod)
	{
		$oMain=$this->oMain;
		$frmMod=CForm::MODE_EDIT;

		$aForm[] = new CFormHidden('tsite',$this->tsite);
		$aForm[] = new CFormText($oMain->translate('tvisitid'),'tvisitid', $this->tvisitid,10,'',$CSecVisit_readonly,'',CFormText::INPUT_INTEGER);
		$aForm[]  = new CFormEfaDate($oMain->translate('tindt'), 'tindt', $oMain->formatDate($this->tindt),'',false);
		$aForm[]  = new CFormEfaDate($oMain->translate('texdt'), 'texdt', $oMain->formatDate($this->texdt),'',false);
		$aForm[] = new CFormText($oMain->translate('tnama'),'tnama', $this->tnama,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tentr'),'tentr', $this->tentr,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tregistration'),'tregistration', $this->tregistration,20,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('temployee'),'temployee', $this->temployee,10,'',false,'',CFormText::INPUT_INTEGER);
		$aForm[] = new CFormText($oMain->translate('templname'),'templname', $this->templname,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tdivi'),'tdivi', $this->tdivi,25,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tphone'),'tphone', $this->tphone,25,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('treason'),'treason', $this->treason,10,'',false,'',CFormText::INPUT_STRING);
		

		$aForm[] = new CFormButton('butsearch', $oMain->translate ('Search'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);

		$oForm = $oMain->std_form($mod, '','frm_search', 3, $frmMod);
		$oForm->addElementsCollection($aForm);
		$html.= $oForm->getHtmlCode();
		return($html);
	}
	        
}// End of CSecVisit


?>
