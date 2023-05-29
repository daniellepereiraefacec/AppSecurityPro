<?php
/**
 * @@name	Segurança-Autorizaçoes
 * @@author	Luis Gomes 
 * @@version 	31-03-2017 
 
 * 2018-05-17	Luis Gomes	I1804_01475
 * 2017-07-10	Luis Gomes	criteria in ShowList
 */
class CSecAuth
{
	var $tauthid;
	var $temployee;    /** Empregado */
	var $tbulk;			// Lista de empregados
	var $tuseridName;
	var $tnama;    /** Nome */
	var $tdivi;    /** Divisão/Empresa */
	var $tindt;    /** Data de início */
	var $texdt;    /** Data de validade */
	var $tstatus;    /** A - active; X Inactive */
	var $tremarks;    /**  */
	var $tmodifdate;    /** Data de Modificação */
	var $tmodifiedby;    /** Modificado por */
	var $tmodifiedbydesc;

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
	 * set class CSecAuth mod
	 */	
	function getHtml($mod, $completeLayout=true)
	{
		$oMain=$this->oMain;
		$ent='secauth'; 

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)	{ $mod='';}
			else			{ $mod='show_'.$ent;} // user retry
		}

		if ($mod =='insert_'.$ent)
		{
			if($this->tbulk=='' && $this->temployee>0)
			{
				$tstatus=$this->storeIntoDB('insert', $tdesc);
				$oMain->stdShowResult($tstatus, $tdesc);
				if($tstatus==0)	{ $mod='show_'.$ent;}
				else			{ $mod='xnew_'.$ent;} // user retry
			}
			else {$this->insertBulk(); $mod='formsearch_secauth';}
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
			$title=$oMain->translate('edit_'.$ent).' '.$this->temployee;
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
			$title=$oMain->translate($mod).' '.$this->temployee;
			$html=$this->showList();
		}

		if ($mod =='show_'.$ent)
		{
			$this->readFromDb();
			$title=$oMain->translate('show_'.$ent).' '.$this->temployee;
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
            if($mod=='show_secauth')
            {
                $maintoolbar->add('edit_secauth')->link($oMain->BaseLink('','edit_secauth','','&tauthid='.$this->tauthid))->title($oMain->translate('edit_secauth'))->tooltip($oMain->translate('edit_secauth'))->efaCIcon('edit.png');                          
            }
        }
            
	 /**
	  * read class CSecAuth attributes from request
	  */	
	protected function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->tauthid=$oMain->GetFromArray('tauthid',$_REQUEST,'int');
		$this->temployee=$oMain->GetFromArray('temployee',$_REQUEST,'int');
		$this->tbulk=$oMain->GetFromArray('tbulk',$_REQUEST,'string_trim');
		$this->tuseridName=$oMain->GetFromArray('tuseridName',$_REQUEST,'string_trim');
		$this->tnama=$oMain->GetFromArray('tnama',$_REQUEST,'string_trim');
		$this->tdivi=$oMain->GetFromArray('tdivi',$_REQUEST,'string_trim');
		$this->tindt=$oMain->GetFromArray('tindt',$_REQUEST,'date');
		$this->texdt=$oMain->GetFromArray('texdt',$_REQUEST,'date');
		$this->tstatus=$oMain->GetFromArray('tstatus',$_REQUEST,'string_trim');
		$this->tremarks=$oMain->GetFromArray('tremarks',$_REQUEST,'string_trim');
		$this->tmodifdate=$oMain->GetFromArray('tmodifdate',$_REQUEST,'string_trim');
		$this->tmodifiedby=$oMain->GetFromArray('tmodifiedby',$_REQUEST,'string_trim');
		
		$this->tExtComp=$oMain->GetFromArray('tExtComp',$_REQUEST,'string_trim');
		$this->tExtEmloyees=$oMain->GetFromArray('tExtEmloyees',$_REQUEST,'string_trim');
		$this->tWorkAuth=$oMain->GetFromArray('tWorkAuth',$_REQUEST,'string_trim');
		
		if($this->tnama=='') {$this->tnama=$this->tuseridName;}
	}
	/**
	 * class CSecAuth form
	 */	
	protected function form($mod='show_secauth',$modChange='')
	{
		$oMain=$this->oMain;
		$CSecAuth_readonly=true;$new=false;
		$titleForm = ''; // To set form Title write set text here
		$html_form=$oMain->stdJsPopUpWin('400');
		$formName='frmCSecAuth'; $operation='';$nCol=2;$width='100%';$ajax=false;
		$modCancel='show_secauth';

		$frmMod=CForm::MODE_EDIT;
		if($mod=='show_secauth')
			$frmMod=CForm::MODE_VIEW;

		if($mod=='insert_secauth')
		{
			$CSecAuth_readonly=false;
			$new=true;
		}

		$onChange="$formName.mod.value='$modChange';$formName.submit(); ".$oMain->loading();

		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$oForm->setWaitActionOnSubmit($oMain->loading());
		$aForm = array();
		//$oForm->setLabelWidthRatio(0.15);
		//general
		$aForm[] = new CFormHidden('tauthid',$this->tauthid);
		$search_tuserid=$oMain->stdPopupwin('GETCCUSER',$formName,'temployee','tuseridName','temployee','tuseridName','efa','employee');	
		$tuserid = new CFormText($oMain->translate('temployee'),'temployee', $this->temployee,'','',false,'tuseridDist');
		$tuseridName = new CFormText($oMain->translate('temployee'), 'tuseridName', $this->templname, '','',false, 'tuseridNameDist', '', '', 70);
		$tuseridName->setExtraData($search_tuserid);
		$tuserid->addEvent('onchange="updateUsernameField(\'tuseridDist\', \'tuseridNameDist\');"');
		$elem = new CFormMultipleElement(array($tuserid, $tuseridName), 1);
		$aForm[] = $elem;			
		
		$aForm[] = new CFormText($oMain->translate('tnama'),'tnama', $this->tnama,35,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tdivi'),'tdivi', $this->tdivi,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormFree('','sa1');
		
		$aForm[] = new CFormEfaDate($oMain->translate('tindt'), 'tindt', $oMain->formatDate($this->tindt,true),CForm::REQUIRED,false, '', '', '', true);
		$aForm[] = new CFormEfaDate($oMain->translate('texdt'), 'texdt', $oMain->formatDate($this->texdt,true),CForm::REQUIRED,false, '', '', '', true);
		
		$elem = new CFormTextArea($oMain->translate('tbulk'), 'tbulk', $this->tbulk, 5,'',false);
		$elem->setLabelHelp($oMain->translate ('explaintbulk'));	
		$aForm[] = $elem;
		$aForm[] = new CFormTextArea($oMain->translate('tremarks'), 'tremarks', $this->tremarks, 5,'',false);
		
		$elem = new CFormTextArea($oMain->translate('tExtComp'), 'tExtComp', $this->tExtComp, 5,'',false);
		$elem->setLabelHelp($oMain->translate ('explaintExtComp'));
		$aForm[] = $elem;
		$elem = new CFormTextArea($oMain->translate('tExtEmloyees'), 'tExtEmloyees', $this->tExtEmloyees, 5,'',false);
		$elem->setLabelHelp($oMain->translate ('explaintExtEmloyees'));
		$aForm[] = $elem;
		$elem = new CFormTextArea($oMain->translate('tWorkAuth'), 'tWorkAuth', $this->tWorkAuth, 5,'',false);
		$elem->setLabelHelp($oMain->translate ('explaintWorkAuth'));
		$aForm[] = $elem;
		
//		$aForm[] = new CFormText($oMain->translate('tstatus'),'tstatus', $this->tstatus,1,'',false,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormFree('','sa2');

//		$aForm[] = new CFormText($oMain->translate('tmodifdate'),'tmodifdate', $oMain->formatDate($this->tmodifdate),10,'',true,'',CFormText::INPUT_STRING);
//		$aForm[] = new CFormText($oMain->translate('tmodifiedby'),'tmodifiedby', $this->tmodifiedby,10,'',true,'',CFormText::INPUT_STRING);
		

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
		if(!$new) $titleForm = $oMain->translate('$mod').' - '.$oMain->translate('temployee');

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
	 * class CSecAuth showData
	 */	
	protected function showData()
	{
		$oMain=$this->oMain;
		$data = array();

		$data[] = array($oMain->translate('tauthid'), $this->tauthid);
		$data[] = array($oMain->translate('temployee'), $this->temployee);
		$data[] = array($oMain->translate('tnama'), $this->tnama);
		$data[] = array($oMain->translate('tdivi'), $this->tdivi,2);
		$data[] = array($oMain->translate('tindt'), $oMain->formatDate($this->tindt,true));
		$data[] = array($oMain->translate('texdt'), $oMain->formatDate($this->texdt,true));
		$data[] = array($oMain->translate('tremarks'), $this->tremarks,2);
		$data[] = array($oMain->translate('tExtComp'), $this->tExtComp,2);
		$data[] = array($oMain->translate('tExtEmloyees'), $this->tExtEmloyees,2);
		$data[] = array($oMain->translate('tWorkAuth'), $this->tWorkAuth,2);
		
//		$data[] = array($oMain->translate('tstatus'), $this->tstatus,2);
		$data[] = array($oMain->translate('tmodifiedby'), $this->tmodifiedbydesc." ($this->tmodifiedby)");
		$data[] = array($oMain->translate('tmodifdate'), $oMain->formatDate($this->tmodifdate,true));
		

		$x = new efaDataDisplay($this->oMain);
		$x->cols(2);
		$x->labelWidth(30);
		$x->data($data);

		return $x->html(); 
	}	
	

	private function insertBulk()
	{
		$oMain = $this->oMain;
		$iErr=0;
		$iOK=0;
		if($this->temployee>0)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			if($tstatus===0) {$iOK++;} else {$iErr++;}
			$oMain->stdShowResult($tstatus, $tdesc);
		}

		$tmp=str_replace(chr(13), '',  $this->tbulk);
		$tmp=str_replace(chr(10), ';', $tmp);
		$tmp=str_replace(' ', ';', $tmp);
		$aArray = explode(';', $tmp);
		foreach($aArray as $key=>$value)
		{
			$value=(int) $value;
			if($value>0)
			{
				$this->temployee=$value;
				$tstatus=$this->storeIntoDB('insert', $tdesc);
				if($tstatus===0 && $iOK==0 || $tstatus!==0 && $iErr==0)
				{$oMain->stdShowResult($tstatus, $tdesc);}
				if($tstatus===0) {$iOK++;} else {$iErr++;}
			}
		}		
		
		$this->tnama='';
		$this->temployee=0;
		if($iErr>0) {return -1;} // Erro
		return 0; // OK
	}
			
	protected function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="[dbo].[spsecauth] @sid='$sid',@sp_operation='$operation',@norecordset='0',@tauthid='$this->tauthid'
		,@temployee='$this->temployee'
		,@tnama='$this->tnama'
		,@tdivi='$this->tdivi'
		,@itindt='$this->tindt'
		,@itexdt='$this->texdt'
		,@tstatus='$this->tstatus'
		,@tremarks=N'$this->tremarks'	
		,@tExtComp=N'$this->tExtComp'
		,@tExtEmloyees=N'$this->tExtEmloyees'
		,@tWorkAuth	=N'$this->tWorkAuth'
		"; //print $sql; exit;
		$rs=dbQuery($oMain->consql, $sql, $flds);
		if(isset($rs[0]['tdesc']) && isset($rs[0]['tstatus']))
		{
		   $tdesc=$rs[0]['tdesc'];
		   $this->tauthid=$rs[0]['tauthid'];
		}
		else
		{
			$rs[0]['tstatus'] = -4999;
			$tdesc = 'unknown error';
		}
		return($rs[0]['tstatus']);
	}
	/**
	 * query to get class CSecAuth record from database
	 */	
	protected function sqlGet()
	{
		$oMain = $this->oMain;

		$sql="SELECT tauthid,temployee,tnama,tdivi,tindt,texdt,tstatus,tremarks,tmodifdate
			,tmodifiedby, dbo.efa_uidname(tmodifiedby) AS tmodifiedbydesc
			, tExtComp, tExtEmloyees, tWorkAuth
		 FROM dbo.tbsecauth WITH (NOLOCK) WHERE 
		tauthid='$this->tauthid'";		

		return($sql);
	}
	/**
	 * set class CSecAuth attributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$this->tauthid=$rs[0]['tauthid'];
			$this->temployee=$rs[0]['temployee'];
			$this->tnama=$rs[0]['tnama'];
			$this->tdivi=$rs[0]['tdivi'];
			$this->tindt=$rs[0]['tindt'];
			$this->texdt=$rs[0]['texdt'];
			$this->tstatus=$rs[0]['tstatus'];
			$this->tremarks=$rs[0]['tremarks'];
			$this->tmodifdate=$rs[0]['tmodifdate'];
			$this->tmodifiedby=$rs[0]['tmodifiedby'];
			$this->tmodifiedbydesc=$rs[0]['tmodifiedbydesc'];
			
			
			$this->tExtComp=$rs[0]['tExtComp'];
			$this->tExtEmloyees=$rs[0]['tExtEmloyees'];
			$this->tWorkAuth=$rs[0]['tWorkAuth'];
		}
		return $rc;
	}
	  
	/**
	 * show list results
	 */		
	protected function showList()
	{
		$oMain=$this->oMain;
		
		$cond=" 1=1 ";
		if($this->temployee>0)	$cond.=" AND (temployee = '$this->temployee')";
		if($this->tindt>0)		$cond.=" AND (tindt >= '".date('m/d/Y', $this->tindt)."')";
		if($this->texdt>0)		$cond.=" AND (tindt <= '".date('m/d/Y', $this->texdt)." 23:59')";
		if($this->tnama!='')	$cond.=" AND (tnama LIKE '%$this->tnama%')";
//		if($this->templname!='') $cond.=" AND (templname LIKE '%$this->templname%')";
		if($this->tdivi!='')	$cond.=" AND (tdivi LIKE '%$this->tdivi%')";
		if($this->tmodifiedby>0) $cond.=" AND (tmodifiedby = '$this->tmodifiedby')";		
		
		$sql="SELECT TOP 1000 tauthid,temployee,dbo.efa_username(temployee)tnama,tdivi,tindt,texdt,tstatus,tremarks,tmodifdate,tmodifiedby,
		[dbo].[erp_effective](getdate(),tindt,texdt ) as teffective
		 FROM dbo.tbsecauth WITH (NOLOCK) WHERE $cond ORDER BY tindt desc		 ";	
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$link=$oMain->stdImglink('show_secauth', '','',$tparam,'',$rs[$r]['tauthid'],'', $oMain->translate('linktemployee'));
			
			if($rs[$r]['teffective']==1)
			{ $rs[$r]['teffective']= $oMain->stdImglink( 'show_secauth', '','','&tauthid='.$rs[$r]['tauthid'], 'bulletgreen_s.png');}
			else
			{ $rs[$r]['teffective']= '';}
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_secauth', '', 'tauthid=§§tauthid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
	    $oTable->columnAdd('tauthid')->type('int')->hidded(true);
	    $oTable->columnAdd('temployee');//->type('int');
		$oTable->columnAdd('tnama');
		$oTable->columnAdd('tdivi')->align('center');
		$oTable->columnAdd('tindt')->type('datetime')->align('center');
		$oTable->columnAdd('texdt')->type('datetime')->align('center');
		$oTable->columnAdd('teffective')->align('center')->searchable(false)->width(3);
		$oTable->columnAdd('tremarks');
		$oTable->columnAdd('tmodifdate')->type('date')->align('center');
		$oTable->columnAdd('tmodifiedby');
		
	   $html=$oTable->html();                

		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}	
 
 	protected function formSearch($mod)
	{
		$oMain=$this->oMain;
		$frmMod=CForm::MODE_EDIT;

		$aForm[] = new CFormHidden('tsite',$this->tsite);
		$aForm[] = new CFormText($oMain->translate('temployee'),'temployee', $this->temployee,10,'',false,'',CFormText::INPUT_INTEGER);
		$aForm[] = new CFormEfaDate($oMain->translate('tindt'), 'tindt', $oMain->formatDate($this->tindt),'',false);
		$aForm[] = new CFormEfaDate($oMain->translate('texdt'), 'texdt', $oMain->formatDate($this->texdt),'',false);
		$aForm[] = new CFormText($oMain->translate('tnama'),'tnama', $this->tnama,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tdivi'),'tdivi', $this->tdivi,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('authorizer'),'tmodifiedby', $this->tmodifiedby,10,'',false,'',CFormText::INPUT_INTEGER);

		$aForm[] = new CFormButton('butsearch', $oMain->translate ('Search'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);

		$oForm = $oMain->std_form($mod, '','frm_search', 3, $frmMod);
		$oForm->addElementsCollection($aForm);
		$html.= $oForm->getHtmlCode();
		return($html);
	}
	        
}// End of CSecAuth

?>
