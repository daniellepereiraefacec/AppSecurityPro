<?php
/**
 * @@name	gates-cartoes
 * @@author	Luis Gomes 
 * @@version 	27-02-2017
 * Revisions:
 * 2018-05-17	Luis Gomes	I1804_01475

Cenários:
	Regista Entrada: Se Saída após até 8h - Agrupa esta saída com a entrada anterior 
	Regista Saida: Se Entrada até 8h - Agrupa esta entrada com a saída anterior
 
 */

class CSecGate
{
	var $tdaytype;	// 'W' Weekday, 'H' holiday, 'E' Weekend
	var $tgateid;    /** Registo de gatesa */
	var $tsite;    /** Arroteia, Maia, ... */
	var $tdatin;    /** Entrada */
	var $tdatout; 
	var $temployee;    /** gatesado */
	var $templname;    /** Nome gatesado */
	var $tdivi;    /** Empresa/Divisão */
	var $treason;    /** Motivo */
	var $treasondesc;
	var $tremarks;    /** Notas */
	var $tmodifiedby;    /** Modificado por */
	var $tmodifiedbydesc;
	var $tmodifdate;    /** Data de Modificação */
	

	/**
	 * constructor
	 */
	function  __construct($oMain,$readFromRequest=true)
	{
		$this->oMain=$oMain;
		$this->tdaytype=$this->getDayType();	// 'W' Weekday, 'H' holiday, 'E' Weekend
		
		if($readFromRequest==TRUE)  
			$this->readFromRequest();   
	}

	/**
	 * set class CSecGate mod
	 */	
	function getHtml($mod, $completeLayout=true)
	{
		$oMain=$this->oMain;
		$ent='secgate'; 

/* 		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)	{ $mod='';}
			else			{ $mod='show_'.$ent;} // user retry
		} */

		if ($mod =='checkout_'.$ent)
		{
			$tstatus=$this->storeIntoDB('checkout', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)	{ $mod='show_'.$ent;}
			else			{ $mod='xedit_'.$ent;} // user retry
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
			$title=$oMain->translate('edit_'.$ent).' '.$oMain->translate('tgateid').' '.$this->tgateid.' ('.$this->templname.')';
			$html=$this->form('update_'.$ent,'xedit_'.$ent);

		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			if($oMain->operation=='in')
			{$title=$oMain->translate('newin_'.$ent);}	//.' ['.$oMain->translate('daytype_'.$this->tdaytype).']'
			else
			{$title=$oMain->translate('newout_'.$ent);}	//.' ['.$oMain->translate('daytype_'.$this->tdaytype).']'
				
			$html=$this->form('insert_'.$ent,'xnew_'.$ent);
		}

		if ($mod =='formsearch_'.$ent or $mod =='dosearch_'.$ent)
		{
			$title=$oMain->translate('search_'.$ent).': '.$this->tsite;
			$html=$this->formSearch('dosearch_'.$ent).$this->showList();
		}
          		
		if ($mod =='list_'.$ent)
		{
			$title=$oMain->translate($mod).' '.$this->tgateid;
			$html=$this->showList();
		}
                
		if ($mod =='events_'.$ent)
		{			
			$this->readFromDb();
			$title=$oMain->translate('show_'.$ent).' '.
			$oMain->stdImglink('show_secgate', '','',"tgateid=$this->tgateid",'',$this->tgateid,'', $oMain->translate('linktgateid')).' ('.$this->templname.')';
			$html=$this->showEvents();
		}
		
		if ($mod =='show_'.$ent)
		{
			$this->readFromDb();
			$title=$oMain->translate('show_'.$ent).' '.
				$oMain->stdImglink('show_secgate', '','',"tgateid=$this->tgateid",'',$this->tgateid,'', $oMain->translate('linktgateid')).' ('.$this->templname.')';

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
	 * set class tree
	 */	
        protected function menuTree($mod)
        {
            $oMain=$this->oMain;
            require_once '../common/efastd.php';
            $html=$oMain->useDHTMLX();
            $ttree=$oMain->GetFromArray('ttree',$_REQUEST,'int');
            $arrparam[]=array(0 => 'tgateid',	1 => $this->tgateid);
            $arrparam[]=array(0 => 'ttree',	1 => $ttree);
            $html.=getHtmltreeStd('efa.php',$oMain->page,'show_secgatetree',$this->operation,$arrparam);
            return ($html);
        }    
	/**
	 * set class toolbar
	 */	        
        protected function toolbar($mod,$maintoolbar)
        {
            $oMain=$this->oMain;
            if($mod=='show_secgate')
            {
                $maintoolbar->add('edit_secgate')->link($oMain->BaseLink('','edit_secgate','','&tgateid='.$this->tgateid))->title($oMain->translate('edit_secgate'))->tooltip($oMain->translate('edit_secgate'))->efaCIcon('edit.png');                          
				$maintoolbar->add('events_secgate')->link($oMain->BaseLink('','events_secgate','','&tgateid='.$this->tgateid))->title($oMain->translate('events'))->tooltip($oMain->translate('events_tgateid'))->efaCIcon('events.png');                          
				
            }
        }
            
	 /**
	  * read class CSecGate attributes from request
	  */	
	protected function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->tgateid=$oMain->GetFromArray('tgateid',$_REQUEST,'int');
		$this->tsite=$oMain->GetFromArray('tsite',$_REQUEST,'string_trim');
		$this->tdatin=$oMain->GetFromArray('tdatin',$_REQUEST,'date');
		$this->tdatout=$oMain->GetFromArray('tdatout',$_REQUEST,'date');
		$this->temployee=$oMain->GetFromArray('temployee',$_REQUEST,'int');
		$this->templname=$oMain->GetFromArray('templname',$_REQUEST,'string_trim');
		$this->tdivi=$oMain->GetFromArray('tdivi',$_REQUEST,'string_trim');
		$this->treason=$oMain->GetFromArray('treason',$_REQUEST,'string_trim');
		$this->tremarks=$oMain->GetFromArray('tremarks',$_REQUEST,'string_trim');
		$this->tmodifiedby=$oMain->GetFromArray('tmodifiedby',$_REQUEST,'string_trim');
		$this->tmodifdate=$oMain->GetFromArray('tmodifdate',$_REQUEST,'string_trim');
		

	}
	
	private function OutOfTime()
	{	$oMain=$this->oMain;
		$sql="SELECT 
 (SELECT tvalue FROM dbo.tbmodparam WHERE (module = 'securitypro') AND (company = 'efa') AND (tfield = 'gen1') ) AS thini
,(SELECT tvalue FROM dbo.tbmodparam WHERE (module = 'securitypro') AND (company = 'efa') AND (tfield = 'gen2') ) AS thend";
		$rs=dbQuery($oMain->consql, $sql, $flds,3600);
		$thini=$rs[0]['thini'];
		$thend=$rs[0]['thend'];
		
		if($thini>=$thend)		{return FALSE;} // Não definido horario de entrada "normal"
		if(date('h')<$thini)	{return TRUE;} // entrada antes da hora
		if(date('h')>$thend)	{return TRUE;} // entrada antes da hora

		return FALSE;	
	}
	
	/**
	 * class CSecGate form
	 */	
	protected function form($mod='show_secgate',$modChange='')
	{

		$oMain=$this->oMain;
		$CSecGate_readonly=true;$new=false;
		$titleForm = ''; // To set form Title write set text here
		$html_form=$oMain->stdJsPopUpWin('400');
		$formName='frmCSecGate'; $operation='';$nCol=2;$width='100%';$ajax=false;
		$modCancel='show_secgate';

		$frmMod=CForm::MODE_EDIT;
		if($mod=='show_secgate')
			$frmMod=CForm::MODE_VIEW;

		if($mod=='insert_secgate')
		{
			$CSecGate_readonly=false;
			$new=true;	
			$this->tdatin=time(); //strtotime(date("Y-m-d",mktime (0,0,0,date("m"),date("d"),date("Y"))));
			$this->tdatout=time(); //strtotime(date("Y-m-d",mktime (0,0,0,date("m"),date("d"),date("Y"))));

			if(date("N">=6))			{$this->treason='W';}
			elseif ($this->OutOfTime()) {$this->treason='H';}
			else						{$this->treason='';}
			
		}

		$onChange="$formName.mod.value='$modChange';$formName.submit(); ".$oMain->loading();

		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$oForm->setWaitActionOnSubmit($oMain->loading());
		$aForm = array();
		//$oForm->setLabelWidthRatio(0.15);
		//general
		$aForm[] = new CFormText($oMain->translate('tgateid'),'tgateid', $this->tgateid,4,CForm::REQUIRED,true,'',CFormText::INPUT_INTEGER);
		
		$sql="SELECT tsite, tsite AS tdesc FROM dbo.tbsecconfig WHERE tstatus='A' ORDER BY tsite";
		$elem = new CFormSelect($oMain->translate('tsite'), 'tsite', $this->tsite, $this->tsite, $sql, $oMain->consql,'',' ',' ',CForm::REQUIRED);
		$aForm[] = $elem;
//		$aForm[] = new CFormText($oMain->translate('tsite'),'tsite', $this->tsite,20,'',false,'',CFormText::INPUT_STRING);
		
		if($oMain->operation!='in' && $oMain->operation!='out')
		{
			$aForm[] = new CFormEfaDate($oMain->translate('tdatin'), 'tdatin', $oMain->formatDate($this->tdatin,true),'',false, '', '', '', true);
			$aForm[] = new CFormEfaDate($oMain->translate('tdatout'),'tdatout', $oMain->formatDate($this->tdatout,true),'',false, '', '', '', true);
		}
		if($oMain->operation=='in')
		{
			$aForm[] = new CFormEfaDate($oMain->translate('tdatin'), 'tdatin', $oMain->formatDate($this->tdatin,true),'',false, '', '', '', true);
			$aForm[] = new CFormFree('','ff2');
		}
		if($oMain->operation=='out')
		{
			$aForm[] = new CFormFree('','ff2');
			$aForm[] = new CFormEfaDate($oMain->translate('tdatout'),'tdatout', $oMain->formatDate($this->tdatout,true),'',false, '', '', '', true);
		}
		
		$search_tuserid=$oMain->stdPopupwin('GETCCUSER',$formName,'temployee','tuseridName','temployee','tuseridName','efa','employee');	
		$tuserid = new CFormText($oMain->translate('temployee'),'temployee', $this->temployee,'','',false,'tuseridDist');
		$tuseridName = new CFormText($oMain->translate('temployee'), 'tuseridName', $this->templname, '','',false, 'tuseridNameDist', '', '', 70);
		$tuseridName->setExtraData($search_tuserid);
		$tuserid->addEvent('onchange="updateUsernameField(\'tuseridDist\', \'tuseridNameDist\');"');
		$elem = new CFormMultipleElement(array($tuserid, $tuseridName), 1);
		$aForm[] = $elem;

//		$aForm[] = new CFormText($oMain->translate('temployee'),'temployee', $this->temployee,4,'',false,'',CFormText::INPUT_INTEGER);
		$aForm[] = new CFormText($oMain->translate('tnama'),'templname', $this->templname,50,'',false,'',CFormText::INPUT_STRING);
		$aForm[] = new CFormText($oMain->translate('tdivi'),'tdivi', $this->tdivi,25,'',false,'',CFormText::INPUT_STRING);

		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS treasondesc  FROM dbo.tbcodes C   WHERE (codetype = 'SecPro_GateType' and tstatus='A' ) ORDER BY C.torder asc ";
		$aForm[] = new CFormSelect($oMain->translate('treason'), 'treason', $this->treason, $this->SecPro_GateTypedesc, $sql, $this->oMain->consql,'',' ',' ','',false);
//		$aForm[] = new CFormTextArea($oMain->translate('treason'), 'treason', $this->treason, 5,'',false);
//		$aForm[] = new CFormTextArea($oMain->translate('tremarks'), 'tremarks', $this->tremarks, 5,'',false);
		$atextForm = new CFormTextArea($oMain->translate('tremarks'),'tremarks', $this->tremarks,5,'',false); 
		$atextForm->setNumberOfColumns(2);
		$aForm[]=$atextForm;
		
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
		if(!$new) $titleForm = $oMain->translate('$mod').' - '.$oMain->translate('tgateid');

		//if no title returns form
		if($titleForm=='')  return $html_form;

		//if exists title sets layout title+form
		$x = new efalayout($this);
		$x->pattern('1C');
//		$x->title($titleForm);
		$x->add($html_form);
		return $x->html();

	}

	/**
	 * class CSecGate showData
	 */	
	protected function showData()
	{
		$oMain=$this->oMain;
		$data = array();
		$data[] = array($oMain->translate('tgateid'),	$this->tgateid);
		$data[] = array($oMain->translate('tsite'),		$this->tsite);
//		$data[] = array($oMain->translate('tdatin'), 	$oMain->formatDate($this->tdatin,true),2);
		$data[] = array($oMain->translate('tdatin'),	$oMain->formatDate($this->tdatin,true));
		$data[] = array($oMain->translate('tdatout'),	$oMain->formatDate($this->tdatout,true));
		$data[] = array($oMain->translate('temployee'), $this->temployee);
		$data[] = array($oMain->translate('tnama'),		$this->templname);
		$data[] = array($oMain->translate('tdivi'),		$this->tdivi,2);
		$data[] = array($oMain->translate('treason'),	$this->treasondesc,2);
		$data[] = array($oMain->translate('tremarks'),	$this->tremarks,2);
		$data[] = array($oMain->translate('tmodifiedby'), $this->tmodifiedbydesc);
		$data[] = array($oMain->translate('tmodifdate'),$oMain->formatDate($this->tmodifdate));
		if( ($this->tdatout < $this->tdatin) && ($this->tdatout>0 && $this->tdatin>0) )
		{$data[] = array('<font color=red>'.$oMain->translate('absence').'</font>',  '<font color=red>'.(($this->tdatin-$this->tdatout)/60).' '.'min.</font>',2);}
		if( ($this->tdatout > $this->tdatin) && ($this->tdatout>0 && $this->tdatin>0) )
		{$data[] = array('<font color=blue>'.$oMain->translate('presence').'</font>',  '<font color=blue>'.(($this->tdatout-$this->tdatin)/60).' '.'min.</font>',2);}
	
		$x = new efaDataDisplay($this->oMain);
		$x->cols(2);
		$x->labelWidth(30);
		$x->data($data);

		$user=' <img src='.$oMain->stdGetUserPicture($this->temployee).' title="'.$this->templname.'" height="165">';
		
		$sql="SELECT tindt, texdt, tremarks, tmodifiedby, dbo.efa_uidname(tmodifiedby) as tusername 
		FROM tbsecauth WHERE temployee=$this->temployee AND 
 ( '" . $oMain->formatDate($this->tdatin,true) . "' BETWEEN tindt AND texdt OR '" . $oMain->formatDate($this->tdatout,true) . "' BETWEEN tindt AND texdt ) ORDER BY tindt";	

		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('authorizations')." ($rc)");
	   //$oTable->dbClickLink($this->oMain->baseLink('', 'show_secgate', '', 'tgateid=§§tgateid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   $oTable->exportToExcel(false);  // if true enables icon to export data to excel
	   $oTable->exportToPdf(false);    // if true enables icon to export data to pdf
	   $oTable->searchable(false);    // if true enables icon to export data to pdf
	   
		$oTable->columnAdd('tindt')->type('datetime');
		$oTable->columnAdd('texdt')->type('datetime');
		$oTable->columnAdd('tmodifiedby')->type('int');
		$oTable->columnAdd('tusername');
		$oTable->columnAdd('tremarks');
				
		return '<table width=100%><TR><TD width=100>'.$user.'</TD><TD>'.$x->html().'</TD></TR></TABLE>'.$oTable->html();
	}	
	
	/**
	 * save class CSecGate record into database
	 */	
	protected function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="[dbo].[spsecgate] @sid='$sid',@sp_operation='$operation',@norecordset='0',@tgateid='$this->tgateid'
		,@tsite='$this->tsite'
		,@itdatin='$this->tdatin'
		,@itdatout='$this->tdatout'
		,@temployee='$this->temployee'
		,@templname='$this->templname'
		,@tdivi='$this->tdivi'
		,@treason=N'$this->treason'
		,@tremarks=N'$this->tremarks'
		
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
		$this->tgateid=$rs[0]['tgateid'];
		return($rs[0]['tstatus']);
	}
	/**
	 * query to get class CSecGate record from database
	 */	
	protected function sqlGet()
	{
		$oMain = $this->oMain;

		$sql="SELECT  tgateid,tsite,tdatin,tdatout,temployee,templname,tdivi,treason,
			dbo.translate_code('SecPro_GateType',treason, '$oMain->l') AS treasondesc
			, tremarks,tmodifiedby,tmodifdate, dbo.efa_uidname(tmodifiedby) AS tmodifiedbydesc
		 FROM dbo.tbsecgate WITH (NOLOCK) WHERE 
		tgateid='$this->tgateid'";		

		return($sql);
	}
	/**
	 * set class CSecGate atributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$this->tgateid=$rs[0]['tgateid'];
			$this->tsite=$rs[0]['tsite'];
			$this->tdatin=$rs[0]['tdatin'];
			$this->tdatout=$rs[0]['tdatout'];
			$this->temployee=$rs[0]['temployee'];
			$this->templname=$rs[0]['templname'];
			$this->tdivi=$rs[0]['tdivi'];
			$this->treason=$rs[0]['treason'];
			$this->treasondesc=$rs[0]['treasondesc'];
			$this->tremarks=$rs[0]['tremarks'];
			$this->tmodifiedby=$rs[0]['tmodifiedby'];
			$this->tmodifiedbydesc=$rs[0]['tmodifiedbydesc'];
			$this->tmodifdate=$rs[0]['tmodifdate'];
			
		}
		return $rc;
	}
	        
	/**
	 * show list results
	 */		
	protected function showList()
	{
		$oMain=$this->oMain;
		
		$cond=" dbo.secaccesstype('G', '$this->tsite',$oMain->employee)<>'' AND (tsite = '".$this->tsite."') ";
		if($this->tgateid>0)	$cond.=" AND (tgateid = '$this->tgateid')";
		if($this->tindt>0)		$cond.=" AND (tdatin >= '".date('m/d/Y', $this->tindt)."')";
		if($this->texdt>0)		$cond.=" AND (tdatin <= '".date('m/d/Y', $this->texdt)." 23:59')";
		if($this->tnama!='')	$cond.=" AND (tnama LIKE '%$this->tnama%')";
		if($this->templname!='') $cond.=" AND (templname LIKE '%$this->templname%')";
		if($this->tdivi!='')	$cond.=" AND (tdivi LIKE '%$this->tdivi%')";
		
		$sql="SELECT TOP 5000 tgateid,tdatin,tdatout,temployee,templname,tdivi,treason,
			dbo.translate_code('SecPro_GateType',treason, '$oMain->l') AS treasondesc,tremarks,tmodifiedby,tmodifdate
		 FROM dbo.tbsecgate WITH (NOLOCK) WHERE $cond ORDER BY tgateid DESC";	

		 $rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$tparam='tgateid='.$rs[$r]['tgateid'];
			$rs[$r]['tgateid']=$oMain->stdImglink('show_secgate', '','',$tparam,'',$rs[$r]['tgateid']);			
			
//			$link_tgateid=$oMain->stdImglink('show_secgate', '','',$tparam,'',$rs[$r]['tgateid'],'', $oMain->translate('linktgateid'));

			$rs[$r]['templname']=$rs[$r]['templname'].' | '.$rs[$r]['temployee'];
//			$rs[$r]['toperations']= $oMain->stdImglink( 'edit_secgate', '','','&tgateid='.$rs[$r]['tgateid'], 'edit_s.png', $oMain->translate('edit'));
//			$rs[$r]['toperations'].= $oMain->stdImglink( 'del_secgate', '','','&tgateid='.$rs[$r]['tgateid'], 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_secgate', '', 'tgateid=§§tgateid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
		$oTable->columnAdd('tgateid')->type('int')->title($this->tsite);
		$oTable->columnAdd('tdatin')->type('datetime');
		$oTable->columnAdd('tdatout')->type('datetime');
//		$oTable->columnAdd('temployee')->type('int');
		$oTable->columnAdd('templname');
		$oTable->columnAdd('tdivi');
		$oTable->columnAdd('treasondesc');
		$oTable->columnAdd('tremarks');
		
	   $html=$oTable->html();                

		if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}	

	protected function showEvents()
	{
		$oMain=$this->oMain;
		$sql="SELECT TOP (5000) trefa AS tgateid, tuserid, tdate, tdeviceid, tremarks
FROM  dbo.tbeventcom WITH (NOLOCK) 
WHERE (tmodule = 'securitypro') AND (ttype = 'SECG') AND (trefa = '$this->tgateid')";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$link_tgateid=$oMain->stdImglink('show_secgate', '','',$tparam,'',$rs[$r]['tgateid'],'', $oMain->translate('linktgateid'));
					
			$rs[$r]['toperations']= $oMain->stdImglink( 'edit_secgate', '','','&tgateid='.$rs[$r]['tgateid'], 'edit_s.png', $oMain->translate('edit'));
			$rs[$r]['toperations'].= $oMain->stdImglink( 'del_secgate', '','','&tgateid='.$rs[$r]['tgateid'], 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_secgate', '', 'tgateid=§§tgateid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
		$oTable->columnAdd('tgateid')->type('int')->hidded(true);
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
		$aForm[] = new CFormText($oMain->translate('tgateid'),'tgateid', $this->tgateid,10,'',$CSecgate_readonly,'',CFormText::INPUT_INTEGER);
		$aForm[]  = new CFormEfaDate($oMain->translate('tindt'), 'tindt', $oMain->formatDate($this->tindt),'',false);
		$aForm[]  = new CFormEfaDate($oMain->translate('texdt'), 'texdt', $oMain->formatDate($this->texdt),'',false);
		$aForm[] = new CFormText($oMain->translate('tnama'),'tnama', $this->tnama,50,'',false,'',CFormText::INPUT_STRING);
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

	// 'W' Weekday, 'H' holiday, 'E' Weekend
	protected function getDayType()
	{
		$oMain=$this->oMain;
		$dat=date("Y-m-d"); 
		$sql="exec webrh.dbo.get_calendario '', '$dat', '$dat'";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		if($rs[$r]['tipo']=='FERIADO'){return'H';}
		if($rs[$r]['tipo']=='FIMDESEMANA'){return'E';}
		return 'W';
	}	
	
}// End of CSecGate
?>
