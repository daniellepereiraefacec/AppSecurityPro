<?php
/**
 * @@name	SecurityPro  - Loads / Unloads
 * @@author	Luis Gomes
 * @@version 	20-02-2017 09:37:39
 *
 * Revisions:
 * 2018-05-17	Luis Gomes	I1804_01475
 * 2018-02-22	Luis Gomes	Novos campos de tbsecload: tnpassengers, tpass1, tpass2, tpass3
 */

class CSecLoad
{
	var $tloadid;
	var $tsite;    /** Arroteia, Maia, ... */
	var $tdatin;    /** Entrada */
	var $tdatout;    /** Saida */
	var $tregistration;    /** Matricula */
	var $tdeliveryto;
	var $tentr;    /** Empresa */
	var $ttype;    /** L Load; U Unload */
	var $tdoctype;
	var $tdocnumber;
	var $tcontact;
	var $tremarks;    /** Notas */
	var $tmodifiedby;    /** Modificado por */
	var $tmodifiedbydesc;
	var $tmodifdate;    /** Data de Modificação */
	var $tstatus;    /** A Active; X Canceled */
	var $ttypedesc;    /** L Load; U Unload */
	var $tdoctypedesc;
	var $tnpassengers;
	var $tpass1;
	var $tpass2;
	var $tpass3;
	var $tchekdoc;		//char(1)		Y -> Com AT
	var $tchecknumber;	//varchar(20)	N.º AT

	/**
	 * constructor
	 */
	function  __construct($oMain,$readFromRequest=true)
	{
		$this->oMain=$oMain;
		if($readFromRequest==TRUE)  
			$this->readFromRequest();   
	}

	/**
	 * set class CSecLoad mod
	 */	
	function getHtml($mod, $completeLayout=true)
	{
		$oMain=$this->oMain;
		$ent='secload'; 

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
			if($tstatus==0)	{ $mod='';}
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
			$title=$oMain->translate('edit_'.$ent).' '.$this->tloadid;
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
			$title=$oMain->translate($mod).' '.$this->tloadid;
			$html=$this->showList();
		}
                
		if ($mod =='events_'.$ent)
		{			
			$this->readFromDb();
			$title=$oMain->translate('show_'.$ent).' '.
			$oMain->stdImglink('show_secload', '','',"tloadid=$this->tloadid",'',$this->tloadid,'', $oMain->translate('linktloadid')).' ('.$this->tdeliveryto.')';
			$html=$this->showEvents();
		}
		
		if ($mod =='show_'.$ent)
		{
			$this->readFromDb();
			$title=$oMain->translate('show_'.$ent).' '.
			$oMain->stdImglink('show_secload', '','',"tloadid=$this->tloadid",'',$this->tloadid,'', $oMain->translate('linktloadid')).' ('.$this->tdeliveryto.')';
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
		if($mod=='show_secload')
		{
			$maintoolbar->add('edit_secload')->link($oMain->BaseLink('','edit_secload','','&tloadid='.$this->tloadid))->title($oMain->translate('edit_secload'))->tooltip($oMain->translate('edit_secload'))->efaCIcon('edit.png');                          
			if($this->tdatout<$this->tdatin)
			$maintoolbar->add('checkout_secload')->link($oMain->BaseLink('','checkout_secload','','&tloadid='.$this->tloadid))
				->title($oMain->translate('checkout'))->tooltip($oMain->translate('checkout'))->efaCIcon('approve3.png')->linkConfirm($oMain->translate('checkout_secload'));

				$maintoolbar->add('events_secload')->link($oMain->BaseLink('','events_secload','','&tloadid='.$this->tloadid))->title($oMain->translate('events'))->tooltip($oMain->translate('events_secload'))->efaCIcon('events.png');                          
//			$maintoolbar->add('del_secload')->link($oMain->BaseLink('','del_secload','','&tloadid='.$this->tloadid))
//				->title($oMain->translate('delete'))->tooltip($oMain->translate('delete'))->efaCIcon('delete.png')->linkConfirm($oMain->translate('del_secload'));
		}
	}
            
	 /**
	  * read class CSecLoad atributes from request
	  */	
	protected function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->tloadid			=$oMain->GetFromArray('tloadid',$_REQUEST,'int');
		$this->tsite			=$oMain->GetFromArray('tsite',$_REQUEST,'string_trim');
		$this->tdatin			=$oMain->GetFromArray('tdatin',$_REQUEST,'date');
		$this->tdatout			=$oMain->GetFromArray('tdatout',$_REQUEST,'date');
		$this->tregistration	=$oMain->GetFromArray('tregistration',$_REQUEST,'string_trim');
		$this->tdeliveryto		=$oMain->GetFromArray('tdeliveryto',$_REQUEST,'string_trim');
		$this->tentr			=$oMain->GetFromArray('tentr',$_REQUEST,'string_trim');
		$this->ttype			=$oMain->GetFromArray('ttype',$_REQUEST,'string_trim');
		$this->tdoctype			=$oMain->GetFromArray('tdoctype',$_REQUEST,'string_trim');
		$this->tdocnumber		=$oMain->GetFromArray('tdocnumber',$_REQUEST,'string_trim');
		$this->tcontact			=$oMain->GetFromArray('tcontact',$_REQUEST,'string_trim');
		$this->tremarks			=$oMain->GetFromArray('tremarks',$_REQUEST,'string_trim');
		$this->tmodifiedby		=$oMain->GetFromArray('tmodifiedby',$_REQUEST,'string_trim');
		$this->tmodifdate		=$oMain->GetFromArray('tmodifdate',$_REQUEST,'string_trim');

		$this->tnpassengers		=$oMain->GetFromArray('tnpassengers',$_REQUEST,'int');
		$this->tpass1			=$oMain->GetFromArray('tpass1',$_REQUEST,'string_trim');
		$this->tpass2			=$oMain->GetFromArray('tpass2',$_REQUEST,'string_trim');
		$this->tpass3			=$oMain->GetFromArray('tpass3',$_REQUEST,'string_trim');
		$this->tchekdoc			=$oMain->GetFromArray('tchekdoc',$_REQUEST,'string_trim');
		$this->tchecknumber		=$oMain->GetFromArray('tchecknumber',$_REQUEST,'string_trim');
		
		$this->tindt			=$oMain->GetFromArray('tindt',$_REQUEST,'date');
		$this->texdt			=$oMain->GetFromArray('texdt',$_REQUEST,'date');
		
	}
	/**
	 * class CSecLoad form
	 */	
	protected function form($mod='show_secload',$modChange='')
	{
		$oMain=$this->oMain;
		$CSecLoad_readonly=true;$new=false;
		$titleForm = ''; // To set form Title write set text here
		$html_form=$oMain->stdJsPopUpWin('400');
		$formName='frmCSecLoad'; $operation='';$nCol=2;$width='100%';$ajax=false;
		$modCancel='show_secload';

		$frmMod=CForm::MODE_EDIT;
		if($mod=='show_secload')
			$frmMod=CForm::MODE_VIEW;

		if($mod=='insert_secload')
		{
			$CSecLoad_readonly=false;
			$new=true;
			$this->tdatin=time();
		}

		$onChange="$formName.mod.value='$modChange';$formName.submit(); ".$oMain->loading();

		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$oForm->setWaitActionOnSubmit($oMain->loading());
		$aForm = array();
		//$oForm->setLabelWidthRatio(0.15);
		//general
		$aForm[] = new CFormText($oMain->translate('tloadid'),'tloadid', $this->tloadid,4,CForm::REQUIRED,true,'',CFormText::INPUT_INTEGER);
		
		$sql="SELECT tsite, tsite AS tdesc FROM dbo.tbsecconfig WHERE tstatus='A' ORDER BY tsite";
		$elem = new CFormSelect($oMain->translate('tsite'), 'tsite', $this->tsite, $this->tsite, $sql, $oMain->consql,'',' ',' ',CForm::REQUIRED);
		$aForm[] = $elem;
		
		$aForm[] = new CFormEfaDate($oMain->translate('tdatin'),  'tdatin',  $oMain->formatDate($this->tdatin, true),CForm::RECOMMENDED,false, '', '', '', true);	
		$aForm[] = new CFormEfaDate($oMain->translate('tdatout'), 'tdatout', $oMain->formatDate($this->tdatout,true),'',false, '', '', '', true);
		
		$aForm[] = new CFormText($oMain->translate('tregistration'),'tregistration', $this->tregistration,20,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormTextArea($oMain->translate('tdeliveryto'), 'tdeliveryto', $this->tdeliveryto, 5,'',false);

		$aForm[] = new CFormText($oMain->translate('tnpassengers'),'tnpassengers', $this->tnpassengers,4,CForm::RECOMMENDED,false,'',CFormText::INPUT_INTEGER);		
		$aForm[] = new CFormText($oMain->translate('tpass1'),'tpass1', $this->tpass1,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tpass2'),'tpass2', $this->tpass2,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tpass3'),'tpass3', $this->tpass3,50,'',false,'',CFormText::INPUT_STRING);
		
		$aForm[] = new CFormText($oMain->translate('tentr'),'tentr', $this->tentr,50,'',false,'',CFormText::INPUT_STRING);
				
		$sql="SELECT codeid, dbo.translate_unitext(valunitext,'".$oMain->l."') AS tdesc FROM dbo.tbcodes WITH (nolock) WHERE codetype = 'SecPro_LoadUnload' AND tstatus='A' ORDER BY tdesc";
		$aForm[] = new CFormSelect($oMain->translate('ttype'), 'ttype', $this->ttype, $this->ttypedesc, $sql, $oMain->consql,'',' ',' ',CForm::REQUIRED);		
		
		$sql="SELECT codeid, dbo.translate_unitext(valunitext,'".$oMain->l."') AS tdesc FROM dbo.tbcodes WITH (nolock) WHERE codetype = 'SecPro_DocType' AND tstatus='A' ORDER BY tdesc";
		$aForm[] = new CFormSelect($oMain->translate('tdoctype'), 'tdoctype', $this->tdoctype, $this->tdoctypedesc, $sql, $oMain->consql,'',' ',' ',CForm::REQUIRED);		
		$aForm[] = new CFormText($oMain->translate('tdocnumber'),'tdocnumber', $this->tdocnumber,25,'',false,'',CFormText::INPUT_STRING);
	
		$checked = "";	if($this->tchekdoc=='Y'){$checked = "checked";}
		$aForm[] = new CFormCheckBox($oMain->translate('tchekdoc'),'tchekdoc','Y',$checked);
		$aForm[] = new CFormText($oMain->translate('tchecknumber'),'tchecknumber', $this->tchecknumber,20,'',false,'',CFormText::INPUT_STRING);

		$aForm[] = new CFormText($oMain->translate('tcontact'),'tcontact', $this->tcontact,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormTextArea($oMain->translate('tremarks'), 'tremarks', $this->tremarks, 5,'',false);
//		$aForm[] = new CFormText($oMain->translate('tmodifiedby'),'tmodifiedby', $this->tmodifiedby,10,'',true,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tmodifdate'),'tmodifdate', $oMain->formatDate($this->tmodifdate),10,'',true,'',CFormText::INPUT_STRING);
		

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
		//if(!$new) $titleForm = $oMain->translate('$mod').' - '.$oMain->translate('tloadid');

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
	 * class CSecLoad showData
	 */	
	protected function showData()
	{
		$oMain=$this->oMain;
		
		if($this->tchekdoc=='Y'){$checkimg='checked_s.png';} 
		else					{$checkimg='unchecked_s.png';}
		$checkimg='<img src="img/'.$checkimg.'" border="0">';
				
		$data = array();
		$data[] = array($oMain->translate('tloadid'), $this->tloadid);
		$data[] = array($oMain->translate('tsite'), $this->tsite);
		$data[] = array($oMain->translate('tdatin'), $oMain->formatDate($this->tdatin,true));
		$data[] = array($oMain->translate('tdatout'), $oMain->formatDate($this->tdatout,true));
		$data[] = array($oMain->translate('tregistration'), $this->tregistration);
		$data[] = array($oMain->translate('tdeliveryto'), $this->tdeliveryto);
		
		$data[] = array($oMain->translate('tnpassengers'), $this->tnpassengers);
		$data[] = array($oMain->translate('tpass1'), $this->tpass1);
		$data[] = array($oMain->translate('tpass2'), $this->tpass2);
		$data[] = array($oMain->translate('tpass3'), $this->tpass3);
		
		$data[] = array($oMain->translate('tentr'), $this->tentr);
		$data[] = array($oMain->translate('ttype'), $this->ttypedesc);
		$data[] = array($oMain->translate('tdoctype'), $this->tdoctypedesc);
		$data[] = array($oMain->translate('tdocnumber'), $this->tdocnumber);
		
		$data[] = array($oMain->translate('tchekdoc'), $checkimg);
		$data[] = array($oMain->translate('tchecknumber'), $this->tchecknumber);

		$data[] = array($oMain->translate('tcontact'), $this->tcontact);
		$data[] = array($oMain->translate('tremarks'), $this->tremarks);
		$data[] = array($oMain->translate('tmodifiedby'), $this->tmodifiedbydesc);
		$data[] = array($oMain->translate('tmodifdate'), $oMain->formatDate($this->tmodifdate,true));
		$data[] = array($oMain->translate('tstatus'), $this->tstatus);

		$x = new efaDataDisplay($this->oMain);
		$x->cols(2);
		$x->labelWidth(30);
		$x->data($data);

		return $x->html(); 
	}	
	
	/**
	 * save class CSecLoad record into database
	 */	
	protected function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="[dbo].[spsecload] @sid='$sid',@sp_operation='$operation',@norecordset='0',@tloadid='$this->tloadid'
		,@tsite='$this->tsite'
		,@itdatin='$this->tdatin'
		,@itdatout='$this->tdatout'
		,@tregistration='$this->tregistration'
		,@tdeliveryto=N'$this->tdeliveryto'
		,@tentr=N'$this->tentr'
		,@ttype='$this->ttype'
		,@tdoctype='$this->tdoctype'
		,@tdocnumber='$this->tdocnumber'
		,@tcontact='$this->tcontact'
		,@tremarks=N'$this->tremarks'
		,@tnpassengers=N'$this->tnpassengers'
		,@tpass1=N'$this->tpass1'
		,@tpass2=N'$this->tpass2'
		,@tpass3=N'$this->tpass3'
		,@tchekdoc=N'$this->tchekdoc'
		,@tchecknumber=N'$this->tchecknumber'
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
		$this->tloadid=$rs[0]['tloadid'];
		return($rs[0]['tstatus']);
	}

	/**
	 * set class CSecLoad attributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql="SELECT tloadid,tsite,tdatin,tdatout,tregistration,tdeliveryto,tentr,ttype,tdoctype,tdocnumber,
		tcontact,tremarks,tmodifiedby,tmodifdate,tstatus, dbo.efa_uidname(tmodifiedby) AS tmodifiedbydesc,
		dbo.translate_code ('SecPro_LoadUnload', ttype,    '".$oMain->l."') AS ttypedesc,
		dbo.translate_code ('SecPro_DocType',	 tdoctype, '".$oMain->l."') AS tdoctypedesc,
		tnpassengers, tpass1, tpass2, tpass3, tchekdoc, tchecknumber
		 FROM dbo.tbsecload WITH (NOLOCK) WHERE tloadid='$this->tloadid'";	
		$rs=dbQuery($oMain->consql, $sql, $flds); //print $sql; exit;
		$rc=count($rs);
		if($rc>0)
		{
			$this->tloadid=$rs[0]['tloadid'];
			$this->tsite=$rs[0]['tsite'];
			$this->tdatin=$rs[0]['tdatin'];
			$this->tdatout=$rs[0]['tdatout'];
			$this->tregistration=$rs[0]['tregistration'];
			$this->tdeliveryto=$rs[0]['tdeliveryto'];
			$this->tentr=$rs[0]['tentr'];
			$this->ttype=$rs[0]['ttype'];
			$this->tdoctype=$rs[0]['tdoctype'];
			$this->tdocnumber=$rs[0]['tdocnumber'];
			$this->tcontact=$rs[0]['tcontact'];
			$this->tremarks=$rs[0]['tremarks'];
			$this->tmodifiedby=$rs[0]['tmodifiedby'];
			$this->tmodifiedbydesc=$rs[0]['tmodifiedbydesc'];
			$this->tmodifdate=$rs[0]['tmodifdate'];
			$this->tstatus=$rs[0]['tstatus'];

			$this->tnpassengers=$rs[0]['tnpassengers'];
			$this->tpass1=$rs[0]['tpass1'];
			$this->tpass2=$rs[0]['tpass2'];
			$this->tpass3=$rs[0]['tpass3'];
			
			$this->tchekdoc=$rs[0]['tchekdoc'];
			$this->tchecknumber=$rs[0]['tchecknumber'];

			$this->ttypedesc=$rs[0]['ttypedesc'];
			$this->tdoctypedesc=$rs[0]['tdoctypedesc'];			
		}
		return $rc;
	}
        
	/**
	 * show list results
	 */		
	protected function showList()
	{
		$oMain=$this->oMain;
		$cond=" dbo.secaccesstype('L', '$this->tsite',$oMain->employee)<>'' AND (tsite = '".$this->tsite."') ";
		if($this->tloadid>0)	$cond.=" AND (tloadid = '$this->tloadid')";
		if($this->tindt>0)		$cond.=" AND (tdatin >= '".date('m/d/Y', $this->tindt)."')";
		if($this->texdt>0)		$cond.=" AND (tdatin <= '".date('m/d/Y', $this->texdt)." 23:59')";
		if($this->tnama!='')	$cond.=" AND (tnama LIKE '%$this->tnama%')";
		if($this->tentr!='')	$cond.=" AND (tentr LIKE '%$this->tentr%')";
		if($this->tregistration!='')	$cond.=" AND (tregistration LIKE '%$this->tregistration%')";
		if($this->templname!='') $cond.=" AND (templname LIKE '%$this->templname%')";
		if($this->tdivi!='')	$cond.=" AND (tdivi LIKE '%$this->tdivi%')";
		if($this->tdocnumber!='')	$cond.=" AND (tdocnumber LIKE '%$this->tdocnumber%')";
		if($this->tchecknumber!='')	$cond.=" AND (tchecknumber LIKE '%$this->tchecknumber%')";
				
		$sql="SELECT TOP 5000 tloadid,tdatin,tdatout,tregistration,tdeliveryto,tentr,ttype,tdoctype,		
		dbo.translate_code ('SecPro_LoadUnload', ttype,    '".$oMain->l."') AS ttypedesc,
		dbo.translate_code ('SecPro_DocType',	 tdoctype, '".$oMain->l."') AS tdoctypedesc,		
		tdocnumber,tcontact,tremarks,tmodifiedby,tmodifdate,tstatus, tchecknumber
		 FROM dbo.tbsecload WITH (NOLOCK) WHERE $cond ORDER BY tloadid DESC"; //print $sql; exit;
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$tparam='tloadid='.$rs[$r]['tloadid'];		
			$rs[$r]['tloadid']=$oMain->stdImglink('show_secload', '','',$tparam,'',$rs[$r]['tloadid']);			
//			$link_tloadid=$oMain->stdImglink('show_secload', '','',$tparam,'',$rs[$r]['tloadid'],'', $oMain->translate('linktloadid'));				
//			$rs[$r]['toperations']= $oMain->stdImglink( 'edit_secload', '','','&tloadid='.$rs[$r]['tloadid'], 'edit_s.png', $oMain->translate('edit'));
//			$rs[$r]['toperations'].= $oMain->stdImglink( 'del_secload', '','','&tloadid='.$rs[$r]['tloadid'], 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_secload', '', 'tloadid=§§tloadid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
	    $oTable->columnAdd('tloadid')->type('int')->title($this->tsite);
//		$oTable->columnAdd('tsite');
		$oTable->columnAdd('tdatin')->type('datetime');
		$oTable->columnAdd('tdatout')->type('datetime');
		$oTable->columnAdd('tregistration');
		$oTable->columnAdd('tdeliveryto');
		$oTable->columnAdd('tentr');
		$oTable->columnAdd('ttypedesc');
		$oTable->columnAdd('tdoctypedesc');
		$oTable->columnAdd('tdocnumber');
		$oTable->columnAdd('tchecknumber');
		$oTable->columnAdd('tcontact');
//		$oTable->columnAdd('tremarks');
//		$oTable->columnAdd('tmodifiedby');
//		$oTable->columnAdd('tmodifdate');
//		$oTable->columnAdd('tstatus');

	   $html=$oTable->html();                

		if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}	

	protected function showEvents()
	{
		$oMain=$this->oMain;
		$sql="SELECT TOP (5000) trefa AS tloadid, tuserid, tdate, tdeviceid, tremarks
FROM  dbo.tbeventcom WITH (NOLOCK) 
WHERE (tmodule = 'securitypro') AND (ttype = 'SECL') AND (trefa = '$this->tloadid')";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$link_tloadid=$oMain->stdImglink('show_secload', '','',$tparam,'',$rs[$r]['tloadid'],'', $oMain->translate('linktloadid'));
					
			$rs[$r]['toperations']= $oMain->stdImglink( 'edit_secload', '','','&tloadid='.$rs[$r]['tloadid'], 'edit_s.png', $oMain->translate('edit'));
			$rs[$r]['toperations'].= $oMain->stdImglink( 'del_secload', '','','&tloadid='.$rs[$r]['tloadid'], 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->title($oMain->translate('tasksearchresults')." ($rc)");
		$oTable->dbClickLink($this->oMain->baseLink('', 'show_secload', '', 'tloadid=§§tloadid§§'));
		// $oTable->height($tableheight);               
		$oTable->data($rs);
		//$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
		$oTable->multilineRow(true); //in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		//$oTable->exportToExcel(true);  // if true enables icon to export data to excel
		//$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
		$oTable->columnAdd('tloadid')->type('int')->hidded(true);
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
		$aForm[] = new CFormText($oMain->translate('tloadid'),'tloadid', $this->tloadid,10,'','','',CFormText::INPUT_INTEGER);
		$aForm[]  = new CFormEfaDate($oMain->translate('tindt'), 'tindt', $oMain->formatDate($this->tindt),'',false);
		$aForm[]  = new CFormEfaDate($oMain->translate('texdt'), 'texdt', $oMain->formatDate($this->texdt),'',false);
//		$aForm[] = new CFormText($oMain->translate('tnama'),'tnama', $this->tnama,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tentr'),'tentr', $this->tentr,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tregistration'),'tregistration', $this->tregistration,20,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('temployee'),'temployee', $this->temployee,10,'',false,'',CFormText::INPUT_INTEGER);
//		$aForm[] = new CFormText($oMain->translate('templname'),'templname', $this->templname,50,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tdivi'),'tdivi', $this->tdivi,25,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tphone'),'tphone', $this->tphone,25,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('treason'),'treason', $this->treason,10,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tdocnumber'),'tdocnumber', $this->tdocnumber,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tchecknumber'),'tchecknumber', $this->tchecknumber,50,'',false,'',CFormText::INPUT_STRING);

		$aForm[] = new CFormButton('butsearch', $oMain->translate ('Search'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);

		$oForm = $oMain->std_form($mod, '','frm_search', 3, $frmMod);
		$oForm->addElementsCollection($aForm);
		$html.= $oForm->getHtmlCode();
		return($html);
	}
	
}// End of CSecLoad

?>
