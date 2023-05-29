<?php
/**
 * @@name	Security Occurrences
 * @@author	Luis Gomes 
 * @@version 	27-02-2017
 * Revisions:
  2018-05-17	Luis Gomes	I1804_01475
 * 
 */
class CSecOccur
{
	var $taccess;
	var $toccurid;    /** Registo de occura */
	var $toccur;    /** N.º da ocorrência (N.º do relatório) */
	var $tsite;    /** Arroteia, Maia, ... */
	var $tdatoccur;    /** Entrada */
	var $tuserid;    /** Vigilante */
	var $tusername;    /** Nome Vigilante */
	var $tprio;    /** Prioridade - Prioritário, Informativo, Manutenção */
	var $tpriodesc;
	var $tdesc;    /** Descrição da ocorrencia */
	var $tpart;    /** Participantes (encolvidsos) na ocorencia */
	var $tdep;    /** DEP Segurança das Instalações */
	var $twitness;    /** Testemunhas */
	var $tremarks;    /** Notas */
	var $tblid;
	var $tmodifiedby;    /** Modificado por */
	var $tmodifdate;    /** Data de Modificação */
	var $tmodifiedbydesc;
	var $tstatus;    /** A Active; C Closed; X Canceled */
	var $tstatusdesc;
	
	var $tdocid;
	var $tindt;
	var $texdt;

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
	 * set class CSecOccur mod
	 */	
	function getHtml($mod, $completeLayout=true)
	{
		$oMain=$this->oMain;
		$ent='secoccur';
		
		$this->taccess=$this->getAccess();
		
		if($this->taccess<1 && $mod!='formsearch_secoccur')
		{
			$oMain->stdShowResult(-1, 'Sem acesso / No access');
			return '';
		}
		
/* 		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)	{ $mod='';}
			else			{ $mod='show_'.$ent;} // user retry
		} */

 		if ($mod =='delfile_'.$ent)
		{
			$this->readFromDb();
			require_once 'shcfile.php';
			$tfileid=$oMain->GetFromArray('tfileid',$_REQUEST,'int');			
			$file = new CFile($this->tdocid, $this->trevorder, '', $tfileid, '', '', '', '', '', '','', '', '', '', '', '', '','','');
			
			$tstatus = $file->deleteExistingFromDB($oMain, $tdesc);			
			$oMain->stdShowResult(0, $tdesc);
			$mod='show_'.$ent;
		} 

 		if ($mod =='close_'.$ent)
		{
			$tstatus=$this->storeIntoDB('close', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$mod='show_'.$ent;
		} 

 		if ($mod =='cancel_'.$ent)
		{
			$tstatus=$this->storeIntoDB('cancel', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$mod='show_'.$ent;
		} 
		
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
			$title=$oMain->translate('edit_'.$ent).' '.$this->toccurid;
			$html=$this->form('update_'.$ent,'xedit_'.$ent);

		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$title=$oMain->translate('new_'.$ent).' ['.$this->tsite.']';
			$html=$this->form('insert_'.$ent,'xnew_'.$ent);
		}

		if ($mod =='formsearch_'.$ent or $mod =='dosearch_'.$ent)
		{
			$title=$oMain->translate('search_'.$ent).': '.$this->tsite;
			$html=$this->formSearch('dosearch_'.$ent).$this->showList();
		}
		
		if ($mod =='list_'.$ent)
		{
			$title=$oMain->translate($mod).' '.$this->toccurid;
			$html=$this->showList();
		}
                
		if ($mod =='events_'.$ent)
		{			
			$this->readFromDb();
			$title=$oMain->translate('show_'.$ent).' '.
			$oMain->stdImglink('show_secoccur', '','',"toccurid=$this->toccurid",'',$this->toccurid,'', $oMain->translate('linktoccurid')).' ('.$this->tnama.')';
			$html=$this->showEvents();
		}
		
		if ($mod =='show_'.$ent)
		{
			$this->readFromDb();
			$title=$oMain->translate('show_'.$ent).' '.
			$oMain->stdImglink('show_secoccur', '','',"toccurid=$this->toccurid",'',$this->toccurid,'', $oMain->translate('linktoccurid')).' ('.$this->toccur.')';
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
  
	/**
	 * set class toolbar
	 */	        
	protected function toolbar($mod,$maintoolbar)
	{
		$oMain=$this->oMain;
		if($mod=='show_secoccur')
		{
			if($this->tstatus=='A')
			{
				$maintoolbar->add('edit_secoccur')->link($oMain->BaseLink('','edit_secoccur','','&toccurid='.$this->toccurid))->title($oMain->translate('edit_secoccur'))->tooltip($oMain->translate('edit_secoccur'))->efaCIcon('edit.png');                          
				$maintoolbar->add('close_secoccur')->link($oMain->BaseLink('','close_secoccur','','&toccurid='.$this->toccurid))
					->title($oMain->translate('close'))->tooltip($oMain->translate('explainclose'))->efaCIcon('approve3.png')->linkConfirm($oMain->translate('close_secoccur'));
				$maintoolbar->add('cancel_secoccur')->link($oMain->BaseLink('','cancel_secoccur','','&toccurid='.$this->toccurid))
					->title($oMain->translate('cancel'))->tooltip($oMain->translate('explaincancel'))->efaCIcon('cancel3.png')->linkConfirm($oMain->translate('cancel_secoccur'));
			}
			$maintoolbar->add('events_secoccur')->link($oMain->BaseLink('','events_secoccur','','&toccurid='.$this->toccurid))->title($oMain->translate('events'))->tooltip($oMain->translate('events_secoccur'))->efaCIcon('events.png');                          

		}
	}
            
	 /**
	  * read class CSecOccur attributes from request
	  */	
	protected function readFromRequest()
	{
		$oMain = $this->oMain;
		if($oMain->mod!='formsearch_secoccur')
		{
			$this->toccurid=$oMain->GetFromArray('toccurid',$_REQUEST,'int');
		}
		$this->toccur=$oMain->GetFromArray('toccur',$_REQUEST,'string_trim');
		$this->tsite=$oMain->GetFromArray('tsite',$_REQUEST,'string_trim');
		$this->tdatoccur=$oMain->GetFromArray('tdatoccur',$_REQUEST,'date');
		$this->tuserid=$oMain->GetFromArray('tuserid',$_REQUEST,'int');
		$this->tprio=$oMain->GetFromArray('tprio',$_REQUEST,'string_trim');
		$this->tdesc=$oMain->GetFromArray('tdesc',$_REQUEST,'string_trim');
		$this->tpart=$oMain->GetFromArray('tpart',$_REQUEST,'string_trim');
		$this->tdep=$oMain->GetFromArray('tdep',$_REQUEST,'string_trim');
		$this->twitness=$oMain->GetFromArray('twitness',$_REQUEST,'string_trim');
		$this->tremarks=$oMain->GetFromArray('tremarks',$_REQUEST,'string_trim');
		$this->tmodifiedby=$oMain->GetFromArray('tmodifiedby',$_REQUEST,'string_trim');
		$this->tmodifdate=$oMain->GetFromArray('tmodifdate',$_REQUEST,'string_trim');
		$this->tstatus=$oMain->GetFromArray('tstatus',$_REQUEST,'string_trim');
		
		$this->tindt=$oMain->GetFromArray('tindt',$_REQUEST,'date');
		$this->texdt=$oMain->GetFromArray('texdt',$_REQUEST,'date');
	}
	

	/**
	 * class CSecOccur form
	 */	
	protected function form($mod='show_secoccur',$modChange='')
	{
		$oMain=$this->oMain;
		$CSecOccur_readonly=true;$new=false;
		$titleForm = ''; // To set form Title write set text here
		$html_form=$oMain->stdJsPopUpWin('400');
		$formName='frmCSecOccur'; $operation='';$nCol=2;$width='100%';$ajax=false;
		$modCancel='show_secoccur';

		$frmMod=CForm::MODE_EDIT;
		if($mod=='show_secoccur')
			$frmMod=CForm::MODE_VIEW;

		if($mod=='insert_secoccur')
		{
			$CSecOccur_readonly=false;
			$new=true;
			$this->tdatoccur=time();
		}

		$onChange="$formName.mod.value='$modChange';$formName.submit(); ".$oMain->loading();

		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$oForm->setWaitActionOnSubmit($oMain->loading());
		$aForm = array();
		//$oForm->setLabelWidthRatio(0.15);
		//general
		$aForm[] = new CFormHidden('toccurid',$this->toccurid);
//		$aForm[] = new CFormText($oMain->translate('toccurid'),'toccurid', $this->toccurid,4,CForm::REQUIRED,true,'',CFormText::INPUT_INTEGER);
		$aForm[] = new CFormText($oMain->translate('toccur'),'toccur', $this->toccur,10,'',true,'',CFormText::INPUT_STRING);

		$aForm[] = new CFormHidden('tsite',$this->tsite);
//		$sql="SELECT tsite, tsite AS tdesc FROM dbo.tbsecconfig WHERE tstatus='A' ORDER BY tsite";
//		$elem = new CFormSelect($oMain->translate('tsite'), 'tsite', $this->tsite, $this->tsite, $sql, $oMain->consql,'',' ',' ',CForm::REQUIRED);
//		$aForm[] = $elem;		
//		$aForm[] = new CFormText($oMain->translate('tsite'),'tsite', $this->tsite,20,'',false,'',CFormText::INPUT_STRING);

		$aForm[] = new CFormEfaDate($oMain->translate('tdatoccur'), 'tdatoccur', $oMain->formatDate($this->tdatoccur,true),'',false, '', '', '', true);		

		$sql_tuserid="SELECT GM.tuserid, dbo.efa_uidname(GM.tuserid) AS tusername
			FROM dbo.tbsecconfig AS SECO INNER JOIN
				 dbo.tbgroupmember AS GM ON SECO.tgpidwrite = GM.tgpid WHERE (SECO.tsite = '$this->tsite')";	
		$tuserid_field = new CFormSelect($oMain->translate('tuseridsec'), 'tuserid', $this->tuserid, $this->tusername, $sql_tuserid, $this->oMain->consql,'',' ',' ',CForm::REQUIRED,false);								
		$aForm[] =$tuserid_field;
//		$aForm[] = new CFormText($oMain->translate('tuserid'),'tuserid', $this->tuserid,4,'',false,'',CFormText::INPUT_INTEGER);

//		$aForm[] = new CFormText($oMain->translate('tprio'),'tprio', $this->tprio,1,'',false,'',CFormText::INPUT_STRING);
		$sql="SELECT codeid, dbo.translate_unitext(valunitext,'".$oMain->l."') AS tdesc FROM dbo.tbcodes WITH (nolock) WHERE codetype = 'SecPro_Priority' AND tstatus='A' ORDER BY tdesc";
		$aForm[] = new CFormSelect($oMain->translate('tprio'), 'tprio', $this->tprio, $this->ttpriodesc, $sql, $oMain->consql,'',' ',' ',CForm::REQUIRED);		
				
		$elem=$aForm[] = new CFormTextArea($oMain->translate('tdescoccur'), 'tdesc', $this->tdesc, 5,'',false);
		$elem->setNumberOfColumns(2);
		$aForm[] = new CFormTextArea($oMain->translate('twitness'),'twitness', $this->twitness,5,'',false);
		$aForm[] = new CFormText($oMain->translate('tdep'),'tdep', $this->tdep,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormTextArea($oMain->translate('tpart'), 'tpart', $this->tpart, 5,'',false);
		$aForm[] = new CFormTextArea($oMain->translate('tremarks'), 'tremarks', $this->tremarks, 5,'',false);
//		$aForm[] = new CFormText($oMain->translate('tmodifiedby'),'tmodifiedby', $this->tmodifiedby,10,'',true,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tmodifdate'),'tmodifdate', $oMain->formatDate($this->tmodifdate),10,'',true,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tstatus'),'tstatus', $this->tstatus,1,'',false,'',CFormText::INPUT_STRING);
		

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
		if(!$new) $titleForm = $oMain->translate('$mod').' - '.$oMain->translate('toccurid');

		//if no title returns form
		if($titleForm=='')  return $html_form;

		//if exists title sets layout title+form
		$x = new efalayout($this);
		$x->pattern('1C');
		$x->title($titleForm);
		$x->add($html_form);
		return $x->html();

	}

	private function oFiles()
	{
		$oMain=$this->oMain;
		//get files
		$sql = "SELECT '' as ticon, bd.tseqn, bd.tdocid, bd.trevorder, fvs.tfileid, fvs.docname, fvs.docdesc, fvs.tcreatedon, '' as toper
				FROM dbo.tbshbldoc AS bd INNER JOIN
					 dbo.vwshdocfiles as fvs on fvs.tdocid=bd.tdocid and fvs.trevorder=bd.trevorder
				WHERE bd.tblid='$this->tblid' order by fvs.docname";

		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{			
			$rs[$r]['ticon']= $oMain->stdImglink( 'shareplaceDownloadFile', 'shareplace','','&tfileid='.$rs[$r]['tfileid'], 'documents2_s.png', '','_blank');
			
			$param='toccurid='.$this->toccurid.'&tfileid='.$rs[$r]['tfileid'];
			$rs[$r]['toper'].= $oMain->stdImglink( 'delfile_secoccur', '','',$param, 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $label="";
	   if($this->tstatus=='A') {$label=$oMain->translate('insert');}
	   
	   
		$img=$oMain->stdImglink( 'show_docmain', 'shareplace','','&tdocid='.$this->tdocid, 'shareplace.png', '', '_blank');
		$lnk=$oMain->stdImglink( 'show_docmain', 'shareplace','','&tdocid='.$this->tdocid, '', $label, '_blank');
		$oTable->title($img.$oMain->translate('occurfiles')." ($rc) | ".$lnk);

	   $oTable->dbClickLink($this->oMain->baseLink('shareplace', 'shareplaceDownloadFile', '', 'tfileid=§§tfileid§§'));
	   //$oTable->autoExpandHeight(true); 
	   $oTable->height(180);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   $oTable->exportToExcel(false);  // if true enables icon to export data to excel
	   $oTable->exportToPdf(false);    // if true enables icon to export data to pdf
	   $oTable->searchable(false); //in case of large text fields shows all text
	   
	    $oTable->columnAdd('tfileid')->type('int')->hidded(true);
		$oTable->columnAdd('ticon')->align('center')->width(5);
		$oTable->columnAdd('docname')->width(35);
		$oTable->columnAdd('docdesc')->width(25);
		$oTable->columnAdd('tcreatedon')->type('date')->align('center')->width(20);
		if($this->tstatus=='A')
		{$oTable->columnAdd('toper')->align('center')->width(15);}
		
		return $oTable->html();
	}

	private function oSubs()
	{
		$oMain=$this->oMain;

		$sql = "SELECT [tid],[toccurid],[tuserid], dbo.efa_uidname(tuserid) AS tusername
			, [tgpid], (SELECT groupdesc FROM tbgroups WHERE tgpid=[dbo].[tbsecoccursub].tgpid) as tgpiddesc
			,[tnotifydate],[tmodifiedby],[tmodifdate], '' As tname, '' as toper
  FROM [dbo].[tbsecoccursub] with(nolock) WHERE toccurid=$this->toccurid order by tnotifydate";

		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			if($rs[$r]['tgpid']>0)
			{	$tgpid=$rs[$r]['tgpid'];
				$img=$oMain->stdImglink( 'dash_applications', 'profiler','operation=groupid',"tnodeid=$tgpid&tgpid=$tgpid", 'usergroup_s.png', '');
				$rs[$r]['tname']=$img.' '.$rs[$r]['tgpiddesc'];
			}
			else
			{	$img=$oMain->stdImglink( 'show_users', 'profiler','','employee=51', 'userflat_s.png', '');
				$rs[$r]['tname']=$img.' '.$rs[$r]['tusername'];
			}
			$param='toccurid='.$this->toccurid.'&tid='.$rs[$r]['tid'];
			$rs[$r]['toper'].= $oMain->stdImglink( 'del_subscription', '','',$param, 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$label="";
		if($this->tstatus=='A') {$label=$oMain->translate('insert');}
		$img=$oMain->stdImglink( 'new_subscription', '','','&toccurid='.$this->toccurid, 'userinfo.png', '', '');
		$lnk=$oMain->stdImglink( 'new_subscription', '','','&toccurid='.$this->toccurid, '', $label, '');
		$oTable->title($img.$oMain->translate('occursubs')." ($rc) | ".$lnk);	

		//$oTable->autoExpandHeight(true); 
		$oTable->height(180);               
		$oTable->data($rs);
		//$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
		$oTable->multilineRow(true); //in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf
		$oTable->searchable(false); //in case of large text fields shows all text

		$oTable->columnAdd('tid')->type('int')->hidded(true);

		$oTable->columnAdd('tname')			->width(60);
		$oTable->columnAdd('tnotifydate')	->width(25)		->align('center')	->type('date');
		$oTable->columnAdd('toper')			->width(15)		->align('center');		
		return $oTable->html();
	}

	protected function showData()
	{
		$oMain=$this->oMain;
		$data = array();
		$data[] = array($oMain->translate('toccurid'), $this->toccurid);
		$data[] = array($oMain->translate('toccur'), $this->toccur);
		$data[] = array($oMain->translate('tsite'), $this->tsite);
		$data[] = array($oMain->translate('tdatoccur'), $oMain->formatDate($this->tdatoccur,true));
		$data[] = array($oMain->translate('tuserid'), $this->tusername.' | '.$this->tuserid);
		$data[] = array($oMain->translate('tprio'), $this->tpriodesc);
		$data[] = array($oMain->translate('tdesc'), $this->tdesc,2);
		$data[] = array($oMain->translate('twitness'), $this->twitness);
		$data[] = array($oMain->translate('tdep'), $this->tdep);
		$data[] = array($oMain->translate('tpart'), $this->tpart);
		$data[] = array($oMain->translate('tremarks'), $this->tremarks);
		$data[] = array($oMain->translate('tmodifiedby'), $this->tmodifiedbydesc);
		$data[] = array($oMain->translate('tmodifdate'), $oMain->formatDate($this->tmodifdate));
		$data[] = array($oMain->translate('tstatus'), $this->tstatusdesc);

		$x = new efaDataDisplay($this->oMain);
		$x->cols(2);
		$x->labelWidth(30);
		$x->data($data);
	
		$xL = new efalayout($oMain);	
		//$xL->title($title);
		$xL->pattern('3T');
		//$this->toolbar($mod,$x->toolbar);
		$xL->add($x->html())->padding(5);
		$xL->add($this->oFiles())->padding(5);
		$xL->add($this->oSubs())->padding(5);
		return $xL->html();
	}	
	
	/**
	 * save class CSecOccur record into database
	 */	
	protected function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="[dbo].[spsecoccur] @sid='$sid',@sp_operation='$operation',@norecordset='0',@toccurid='$this->toccurid'
		,@toccur='$this->toccur'
		,@tsite='$this->tsite'
		,@itdatoccur='$this->tdatoccur'
		,@tuserid='$this->tuserid'
		,@tprio='$this->tprio'
		,@tdesc=N'$this->tdesc'
		,@tpart=N'$this->tpart'
		,@tdep='$this->tdep'
		,@twitness='$this->twitness'
		,@tremarks=N'$this->tremarks'
		,@tstatus='$this->tstatus'
		
		";
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
		if($rs[0]['tstatus']==0) {$this->toccurid=$rs[0]['toccurid'];}
		return($rs[0]['tstatus']);
	}

	private function getAccess()
	{
		$oMain = $this->oMain;
		if($this->toccurid==0){$this->taccess=1; return $this->taccess;}
		
		$this->taccess=0;
		$sql="	SELECT SECO.toccurid,
(SELECT COUNT(MM.tuserid) FROM dbo.tbgroupmember AS MM INNER JOIN dbo.tbsecconfig AS CC ON MM.tgpid = CC.tgpidwrite AND SECO.tsite = CC.tsite 
WHERE MM.tuserid=$oMain->employee) AS taccess
				
,(SELECT COUNT(MM.tuserid) AS E2
                               FROM            dbo.tbgroupmember AS MM 
                               WHERE        (MM.tuserid = $oMain->employee) AND (MM.tgpid  IN(SELECT tgpid from dbo.tbsecoccursub WHERE toccurid=SECO.toccurid AND tgpid>0))) AS taccessg
,

                             (SELECT        COUNT(MM.tuserid) AS Expr1
                               FROM            dbo.tbsecoccursub AS MM 
                               WHERE        (MM.tuserid = $oMain->employee) AND (MM.toccurid=SECO.toccurid )) AS taccessu
FROM dbo.tbsecoccur AS SECO WITH (NOLOCK) WHERE  (SECO.toccurid = '$this->toccurid')
				";	
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			if($rs[0]['taccess']>0 || $rs[0]['taccessg']>0 || $rs[0]['taccessu']>0)
			{$this->taccess=1;}
		}
		return $this->taccess;		
	}
	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql="SELECT SECO.toccurid, SECO.toccur, SECO.tsite, SECO.tdatoccur, SECO.tuserid
			, SECO.tprio, SECO.tdesc, SECO.tpart, SECO.tdep, SECO.twitness, SECO.tremarks, SECO.tblid, 
                     SECO.tmodifiedby, dbo.efa_uidname(SECO.tmodifiedby) AS tmodifiedbydesc, SECO.tmodifdate, SECO.tstatus, BLDOC.tdocid, 
					 dbo.translate_code('SecPro_Priority', SECO.tprio, '$oMain->l') AS tpriodesc,
					 dbo.translate_code('status_acx', SECO.tstatus, '$oMain->l') AS tstatusdesc,
					 dbo.efa_uidname(SECO.tuserid) as tusername
				FROM dbo.tbsecoccur AS SECO WITH (NOLOCK) INNER JOIN
                     dbo.tbshbldoc AS BLDOC ON SECO.tblid = BLDOC.tblid
			  WHERE  (SECO.toccurid = '$this->toccurid')";	
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$this->toccurid=$rs[0]['toccurid'];
			$this->toccur=$rs[0]['toccur'];
			$this->tsite=$rs[0]['tsite'];
			$this->tdatoccur=$rs[0]['tdatoccur'];
			$this->tuserid=$rs[0]['tuserid'];
			$this->tusername=$rs[0]['tusername'];
			$this->tprio=$rs[0]['tprio'];
			$this->tpriodesc=$rs[0]['tpriodesc'];
			$this->tdesc=$rs[0]['tdesc'];
			$this->tpart=$rs[0]['tpart'];
			$this->tdep=$rs[0]['tdep'];
			$this->twitness=$rs[0]['twitness'];
			$this->tremarks=$rs[0]['tremarks'];
			$this->tblid=$rs[0]['tblid'];
			$this->tmodifiedby=$rs[0]['tmodifiedby'];
			$this->tmodifiedbydesc=$rs[0]['tmodifiedbydesc'];
			$this->tmodifdate=$rs[0]['tmodifdate'];
			$this->tstatus=$rs[0]['tstatus'];
			$this->tstatusdesc=$rs[0]['tstatusdesc'];
			$this->tdocid=$rs[0]['tdocid'];
		}
		return $rc;
	}
	  
	/**
	 * show list results
	 */		
	protected function showList()
	{
		$oMain=$this->oMain;
		
		if($this->taccess<1 && $this->toccurid<1) {return'';}

		$cond=" dbo.secaccesstype('V', '$this->tsite',$oMain->employee)<>'' AND (tsite = '".$this->tsite."') ";
		if($this->toccurid>0)	$cond.=" AND (toccurid = '$this->toccurid')";
		if($this->tindt>0)		$cond.=" AND (tdatoccur >= '".date('m/d/Y', $this->tindt)."')";
		if($this->texdt>0)		$cond.=" AND (tdatoccur <= '".date('m/d/Y', $this->texdt)." 23:59')";
//		if($this->tnama!='')	$cond.=" AND (tnama LIKE '%$this->tnama%')";
//		if($this->tentr!='')	$cond.=" AND (tentr LIKE '%$this->tentr%')";
//		if($this->tregistration!='')	$cond.=" AND (tregistration LIKE '%$this->tregistration%')";
//		if($this->templname!='') $cond.=" AND (templname LIKE '%$this->templname%')";
//		if($this->tdivi!='')	$cond.=" AND (tdivi LIKE '%$this->tdivi%')";
				
		$sql="SELECT TOP 1000 toccurid,toccur,tsite,tdatoccur,tuserid,tprio,tdesc,tpart,tdep,twitness,tremarks,tmodifiedby,tmodifdate,tstatus,
		dbo.translate_code('SecPro_Priority', tprio, '$oMain->l') AS tpriodesc,
		dbo.translate_code('status_Acx', tstatus, '$oMain->l') AS tstatusdesc,
		dbo.efa_uidname(tuserid) AS tusername
		 FROM dbo.tbsecoccur WITH (NOLOCK) WHERE $cond ORDER BY toccurid DESC"; //print $sql; exit;

		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$tparam='toccurid='.$rs[$r]['toccurid'];
			$rs[$r]['toccurid']=$oMain->stdImglink('show_secoccur', '','',$tparam,'',$rs[$r]['toccurid']);			
			
			$rs[$r]['tuserid']=$rs[$r]['tusername'].' | '.$rs[$r]['tuserid'];

//			$link_toccurid=$oMain->stdImglink('show_secoccur', '','',$tparam,'',$rs[$r]['toccurid'],'', $oMain->translate('linktoccurid'));
//			$rs[$r]['toperations']= $oMain->stdImglink( 'edit_secoccur', '','','&toccurid='.$rs[$r]['toccurid'], 'edit_s.png', $oMain->translate('edit'));
//			$rs[$r]['toperations'].= $oMain->stdImglink( 'del_secoccur', '','','&toccurid='.$rs[$r]['toccurid'], 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_secoccur', '', 'toccurid=§§toccurid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
	   $oTable->columnAdd('toccurid')->type('int')->width(5);
		$oTable->columnAdd('toccur');
//		$oTable->columnAdd('tsite');
		$oTable->columnAdd('tdatoccur')->type('date');
		$oTable->columnAdd('tstatusdesc');
		$oTable->columnAdd('tuserid');
		$oTable->columnAdd('tpriodesc');
		$oTable->columnAdd('tdesc')->width(30);
		$oTable->columnAdd('tpart');
		$oTable->columnAdd('tdep');
		$oTable->columnAdd('twitness');
//		$oTable->columnAdd('tremarks');
//		$oTable->columnAdd('tmodifiedby');
//		$oTable->columnAdd('tmodifdate');
		
		//option to show footer with calculated fields (when use change this example)
/* 			$x = $oTable->addFooter();
		$x->title('tipackid', 'Total');
		$x->rowspan('tdsca', true);
		$x->rowspan('tfeatid', true);
		$x->rowspan('tfeatiddesc', true);
		$x->rowspan('tvalueiddesc', true);
		$x->rowspan('countitems', true);
		$x->calc('tcost', '§tcost§');
		$x->calc('tpric', '§tpric§');
		$x->calc('mgval', '§mgval§');
		$x->calc('mgperc', '§mgperc§')->decimals('mgperc', 2); */
		//end option to show footer with calculated fields
		
	   $html=$oTable->html();                

		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}	
        
	/**
	 * show Events
	 */		
	protected function showEvents()
	{
		$oMain=$this->oMain;
		$sql="SELECT TOP (5000) trefa as toccurid, tuserid, tdate, tdeviceid, tremarks
FROM  dbo.tbeventcom WITH (NOLOCK) 
WHERE (tmodule = 'securitypro') AND (ttype = 'SECO') AND (trefa = '$this->toccurid')";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$link_toccurid=$oMain->stdImglink('show_secoccur', '','',$tparam,'',$rs[$r]['toccurid'],'', $oMain->translate('linktoccurid'));
					
			$rs[$r]['toperations']= $oMain->stdImglink( 'edit_secoccur', '','','&toccurid='.$rs[$r]['toccurid'], 'edit_s.png', $oMain->translate('edit'));
			$rs[$r]['toperations'].= $oMain->stdImglink( 'del_secoccur', '','','&toccurid='.$rs[$r]['toccurid'], 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_secoccur', '', 'toccurid=§§toccurid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
		$oTable->columnAdd('toccurid')->type('int')->hidded(true);
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
		$aForm[] = new CFormText($oMain->translate('toccurid'),'toccurid', $this->toccurid,10,'',$CSecVisit_readonly,'',CFormText::INPUT_INTEGER);
		if($this->taccess>0)
		{
		$aForm[]  = new CFormEfaDate($oMain->translate('tindt'), 'tindt', $oMain->formatDate($this->tindt),'',false);
		$aForm[]  = new CFormEfaDate($oMain->translate('texdt'), 'texdt', $oMain->formatDate($this->texdt),'',false);
		}
		else
		{$mod='show_secoccur';}
//		$aForm[] = new CFormText($oMain->translate('tnama'),'tnama', $this->tnama,50,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tentr'),'tentr', $this->tentr,50,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tregistration'),'tregistration', $this->tregistration,20,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('templname'),'templname', $this->templname,50,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tdivi'),'tdivi', $this->tdivi,25,'',false,'',CFormText::INPUT_STRING);
		
		$aForm[] = new CFormButton('butsearch', $oMain->translate ('Search'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);

		$oForm = $oMain->std_form($mod, '','frm_search', 3, $frmMod);
		$oForm->addElementsCollection($aForm);
		$html.= $oForm->getHtmlCode();
		return($html);
	}	
}// End of CSecOccur

?>
