<?php
/**
 * @@name		SecurityPro statistics and reports
 * @@author		Luis Gomes 
 * @@version 	2018-05-02

 * Revisions:
 * 
 */

class CSecStat
{
	public  $tsite;
	private $tyear;
	private $ttype;
	private $tsdtm;
	private $tedtm;

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
	 * set class CSubscription mod
	 */	
	function getHtml(&$mod, $completeLayout=true)
	{
		$oMain=$this->oMain;
		$ent='secstat';

		if ($mod =='dash_'.$ent)
		{
			//$this->readFromDb();
			$title.=$oMain->translate('show_'.$ent).' '.$this->tid;
			//$html=$this->form('show_'.$ent);
			//$html=$this->showData();
			$html='<table width=100%><tr><td>'.$this->chartVisits().'</td><td>'
					.$this->chartLoads().'</td></tr><tr><td>'.$this->chartGates().'</td><td>'.$this->chartOccurs().'</td><tr></table>'
					;
		}
		
		if ($mod =='showlist_'.$ent)
		{ 
			$title.=$oMain->translate('showlist_'.$ent).' '.$this->tid;
			$html=$this->showList();
		}	
		
		if ($mod =='xlsv_'.$ent) {$html=$this->xlsV();}
		if ($mod =='xlsl_'.$ent) {$html=$this->xlsL();}
		if ($mod =='xlsg_'.$ent) {$html=$this->xlsG();}
		if ($mod =='xlso_'.$ent) {$html=$this->xlsO();}

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
            //$x->title($title);
            //format toolbar
            //$this->toolbar($mod,$x->toolbar);     
			//use menuTree create a tree in layout
            //$x->add($this->menuTree($mod));
            $x->add($this->form().$this->oMain->efaHR().$html);
            return $x->html();          
        }
   
	/**
	 * set class toolbar
	 */	        
        protected function toolbar($mod,$maintoolbar)
        {
            $oMain=$this->oMain;
            if($mod=='show_subscription')
            {
                $maintoolbar->add('edit_subscription')->link($oMain->BaseLink('','edit_subscription','','&tid='.$this->tid))->title($oMain->translate('edit_subscription'))->tooltip($oMain->translate('edit_subscription'))->efaCIcon('edit.png');                          
            }
        }
            
	private function form()
	{
		$oMain = $this->oMain;

		return '<table width=100%><tr><td width=180>'.$this->formStat().'</td><td width=1><b>|</b></td><td>'.$this->formList().'</td></tr></table>';
	}
		
	private function formStat()
	{
		$oMain = $this->oMain;
		$formName='frmStat'; $operation='';$nCol=1;$width='100%';$ajax=false;
		$modCancel='show_secvisit';

		$frmMod=CForm::MODE_EDIT;

		$onChange="$formName.mod.value='$modChange';$formName.submit(); ".$oMain->loading();

		$oForm = $oMain->std_form('dash_secstat', $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$oForm->setWaitActionOnSubmit($oMain->loading());
		$aForm = array();
		$oForm->setLabelWidthRatio(0.05);
		//general
		//$aForm[] = new CFormText($oMain->translate('tyear'),'tyear', $this->tyear,10,CForm::REQUIRED,true,'',CFormText::INPUT_INTEGER);
		
		$y=array();
		for($i=2017; $i<=date("Y"); $i++)
		{
			$y[$i]['tcode']="$i";
			$y[$i]['tdesc']="$i";
		}
	
		$elem=$aForm[] = new CFormSelect($oMain->translate('statistics').':', 'tyear', $this->tyear, $this->tyear, '', '',$y,' ',' ','');
		$elem->addEvent("onChange=\" $formName.submit(); $oMain->loading\"");
		
		$oForm->addElementsCollection($aForm);
	
		return $oForm->getHtmlCode();
	}
	
	function formList($mod = 'showlist_secstat', $modChange = '') 
	{
		$oMain = $this->oMain;
		
		$formName = 'frmList';
		$operation = '';
		$nCol = 4;
		$width = '99%';
		$ajax = false;
		$frmMod = CForm::MODE_EDIT;
		$required = true;

		$onChange = "$formName.mod.value='$modChange';$formName.submit(); " . $oMain->loading() . ";";

		$oForm = $oMain->std_form($mod, $operation, $formName, $nCol, $frmMod, $ajax, $width);
		$aForm = array();

		$t=array();

		$t[0]['tcode']="V"; $t[0]['tdesc']=$oMain->translate('type_V');
		$t[1]['tcode']="L"; $t[1]['tdesc']=$oMain->translate('type_L');
		$t[2]['tcode']="G"; $t[2]['tdesc']=$oMain->translate('type_G');
		$t[3]['tcode']="O"; $t[3]['tdesc']=$oMain->translate('type_O');

		$aForm[] = new CFormSelect($oMain->translate('ttype'), 'ttype', $this->ttype, $this->ttype, '', '', $t, '', ' ', $required);

		$aForm[] = new CFormEfaDate($oMain->translate('tsdtm'), 'tsdtm', $oMain->formatDate($this->tsdtm), $required, false);
		$aForm[] = new CFormEfaDate($oMain->translate('tedtm'), 'tedtm', $oMain->formatDate($this->tedtm), $required, false);
		//form buttons
		$onSubmit = "$formName.submit(); $oMain->loading;";
		$buttonSave = new CFormButton('save', $oMain->translate('list'), CFormButton::TYPE_SUBMIT, '', CFormButton::LOCATION_FORM_RIGHT);
		$aForm[] = $buttonSave;

		$oForm->addElementsCollection($aForm);
		$html.=$oForm->getHtmlCode();

		return $html;
	}
	
	protected function readFromRequest()
	{
		$oMain = $this->oMain;
		$tsite=$oMain->GetFromArray('tsite',$_REQUEST,'string_trim');
		if($tsite!='')	{$this->tsite=$tsite;}
		$this->tyear		=$oMain->GetFromArray('tyear',$_REQUEST,'int');

		$this->ttype=$oMain->GetFromArray('ttype',$_REQUEST,'string_trim');	// V Visitors; L Loads; G Gates; O Occurrences
		$this->tsdtm=$oMain->GetFromArray('tsdtm',$_REQUEST,'date');
		$this->tedtm=$oMain->GetFromArray('tedtm',$_REQUEST,'date');
	}

	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql="SELECT top 10 * from tbusers";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
				$this->tid=$rs[0]['tid'];
			$this->toccurid=$rs[0]['toccurid'];

		}
		return $rc;
	}
	
	
	private function showList()
	{
		
		switch ($this->ttype) {
		case 'V': return $this->showListV();
		case 'L': return $this->showListL();
		case 'G': return $this->showListG();
		case 'O': return $this->showListO();
		default:	return 'Invalid type';			
		}
	}
	
	private function xlsV()
	{
		$oMain=$this->oMain;
		
		$this->readFromRequest()	;
		$tsdtm=$oMain->formatDate($this->tsdtm);
		$tedtm=$oMain->formatDate($this->tedtm);
		$sql="SELECT TOP 5000 tvisitid, tsite, tdatin, tdatout, tnama, tentr, 
			tregistration, temployee, templname, tdivi, tphone, treason, tremarks, tncompanion, tcomp1, tmodifiedby, tmodifdate 
			FROM dbo.tbsecvisit WHERE tsite='$this->tsite' AND tdatin BETWEEN '$tsdtm' AND '$tedtm'";

		//print "$this->tsdtm | $this->tedtm |$this->tsite | $sql";
		//var_dump($_REQUEST); exit;		
		//$rs=dbQuery($oMain->consql, $sql, $flds);
		//$rc=count($rs);

		require_once 'cexcel.php';
		$oMain=$this->oMain;
		$o = new CExcel();

		$o->setProperties('Luis Gomes','Luis Gomes','SecurityPro - Visitors',"From $tsdtm to $tedtm",'SynergyNet','SynergyNet','SynergyNet');
		$o->setActiveSheetIndex(0);
		$o->rename('Visitors');

		
//		$flds=array();
//		$rs=dbQuery($this->oMain->consql, $sql, $flds);	
//		$o->writeForm($rs[0], 'A', 1,3);
		
/*		
		$o->setCellValue('A1', $oMain->translate('baseline').': '.$this->tblid);
		$o->setCellValue('D1', $oMain->translate('tdesc')	.': '.$this->tdesc);
		$o->setCellValue('J1', $oMain->translate('tstatus')	.': '.$this->tstatusdesc);

		$o->setCellValue('A2', $oMain->translate('trefadest').': '.$this->trefaiddesc);
		$o->setCellValue('D2', $oMain->translate('tgroup')	 .': '.$this->tmnggroupdesc);
		$o->setCellValue('J2', $oMain->translate('ttype')	 .': '.$this->ttypedesc);
$o->setFillColor('A1:C1', 'FF808080');
		
//		$o->setCellValue('A3', $oMain->translate('message').': '.$this->tremarks);
		$o->setCellValue('A4', $oMain->translate('tuserid')	 .': '.$this->tcreatedbyname);
		$o->setCellValue('A5', $oMain->translate('date')	 .': '.$oMain->formatDate($this->tmodifdate,false));			
*/		

		$flds=array();
//		$rs=dbquery($oMain->consql, $sql, $flds);
		$rs=getrs2($this->oMain->consql, $sql, $flds); // Não substituir getrs2 por dbquery [parametro $flds]
		$rc=count($rs);
// var_dump($rs); exit;	//ob_end_clean();

		for($r = 0; $r < $rc; $r++)
		{	$rst=$rs[$r];
			$rs[$r]['tdatin'] =$oMain->formatDate($rst['tdatin'], TRUE);
			$rs[$r]['tdatout']=$oMain->formatDate($rst['tdatout'],TRUE);
			$rs[$r]['tmodifdate']=$oMain->formatDate($rst['tmodifdate'],False);
		}
		
		$o->writeRS($rs, $flds, 'A', 1);
		
		$o->setFillColor('A1:Q1', 'A5FF7F');
		
		$o->download('SecurityPro_V_'.$this->tsite);
		exit;
	}


	
	private function xlsL()
	{
		$oMain=$this->oMain;
		
		$this->readFromRequest()	;
		$tsdtm=$oMain->formatDate($this->tsdtm);
		$tedtm=$oMain->formatDate($this->tedtm);
		$sql="SELECT TOP 5000 tloadid, tsite, tdatin, tdatout, tregistration, 
			tdeliveryto, tentr, dbo.translate_code ('SecPro_LoadUnload', ttype, 'PT') AS ttypedesc,
			dbo.translate_code ('SecPro_DocType',	tdoctype, 'PT') AS tdoctypedesc, 
			tdocnumber, tchekdoc, tchecknumber, tcontact, tremarks,
			tnpassengers, tpass1, tpass2, tpass3, tmodifiedby, tmodifdate 
			FROM dbo.tbsecload WHERE tsite='$this->tsite' AND tdatin BETWEEN '$tsdtm' AND '$tedtm'";

		require_once 'cexcel.php';
		$oMain=$this->oMain;
		$o = new CExcel();

		$o->setProperties('Luis Gomes','Luis Gomes','SecurityPro - Loads',"From $tsdtm to $tedtm",'SynergyNet','SynergyNet','SynergyNet');
		$o->setActiveSheetIndex(0);
		$o->rename('Loads');

		$flds=array();
//		$rs=dbquery($oMain->consql, $sql, $flds);
		$rs=getrs2($this->oMain->consql, $sql, $flds); // Não substituir getrs2 por dbquery [parametro $flds]
		$rc=count($rs);
// var_dump($rs); exit;	//ob_end_clean();

		for($r = 0; $r < $rc; $r++)
		{	$rst=$rs[$r];
			$rs[$r]['tdatin'] =$oMain->formatDate($rst['tdatin'], TRUE);
			$rs[$r]['tdatout']=$oMain->formatDate($rst['tdatout'],TRUE);
			$rs[$r]['tmodifdate']=$oMain->formatDate($rst['tmodifdate'],False);
		}
		
		$o->writeRS($rs, $flds, 'A', 1);
		
		$o->setFillColor('A1:Q1', 'A5FF7F');
		
		$o->download('SecurityPro_L_'.$this->tsite);
		exit;
	}
	
	private function xlsG()
	{
		$oMain=$this->oMain;
		
		$this->readFromRequest()	;
		$tsdtm=$oMain->formatDate($this->tsdtm);
		$tedtm=$oMain->formatDate($this->tedtm);
		$sql="SELECT TOP 5000 tgateid, tsite, tdatin, tdatout, temployee, templname,
			tdivi, dbo.translate_code('SecPro_GateType',treason, 'PT') AS treason, tremarks, tmodifiedby, tmodifdate 
			FROM dbo.tbsecgate WHERE tsite='$this->tsite' AND tdatin BETWEEN '$tsdtm' AND '$tedtm'";

		require_once 'cexcel.php';
		$oMain=$this->oMain;
		$o = new CExcel();

		$o->setProperties('Luis Gomes','Luis Gomes','SecurityPro - Gates',"From $tsdtm to $tedtm",'SynergyNet','SynergyNet','SynergyNet');
		$o->setActiveSheetIndex(0);
		$o->rename('Gates');

		$flds=array();
//		$rs=dbquery($oMain->consql, $sql, $flds);
		$rs=getrs2($this->oMain->consql, $sql, $flds); // Não substituir getrs2 por dbquery [parametro $flds]
		$rc=count($rs);
// var_dump($rs); exit;	//ob_end_clean();

		for($r = 0; $r < $rc; $r++)
		{	$rst=$rs[$r];
			$rs[$r]['tdatin'] =$oMain->formatDate($rst['tdatin'], TRUE);
			$rs[$r]['tdatout']=$oMain->formatDate($rst['tdatout'],TRUE);
			$rs[$r]['tmodifdate']=$oMain->formatDate($rst['tmodifdate'],False);
		}
		
		$o->writeRS($rs, $flds, 'A', 1);
		
		$o->setFillColor('A1:Q1', 'A5FF7F');
		
		$o->download('SecurityPro_G_'.$this->tsite);
		exit;
	}
	
	
	private function xlsO()
	{
		$oMain=$this->oMain;
		
		$this->readFromRequest()	;
		$tsdtm=$oMain->formatDate($this->tsdtm);
		$tedtm=$oMain->formatDate($this->tedtm);
		$sql="SELECT TOP 5000 toccurid, tsite, tdatoccur, dbo.efa_uidname(tuserid) AS tuserid, tprio,
			tdesc, tpart, tdep, twitness, tremarks, tmodifiedby, tmodifdate 
			FROM dbo.tbsecoccur WHERE tsite='$this->tsite' AND tdatoccur BETWEEN '$tsdtm' AND '$tedtm'";

		require_once 'cexcel.php';
		$oMain=$this->oMain;
		$o = new CExcel();

		$o->setProperties('Luis Gomes','Luis Gomes','SecurityPro - Occurrences',"From $tsdtm to $tedtm",'SynergyNet','SynergyNet','SynergyNet');
		$o->setActiveSheetIndex(0);
		$o->rename('Occurrences');

		$flds=array();
//		$rs=dbquery($oMain->consql, $sql, $flds);
		$rs=getrs2($this->oMain->consql, $sql, $flds); // Não substituir getrs2 por dbquery [parametro $flds]
		$rc=count($rs);
// var_dump($rs); exit;	//ob_end_clean();

		for($r = 0; $r < $rc; $r++)
		{	$rst=$rs[$r];
			$rs[$r]['tdatoccur'] =$oMain->formatDate($rst['tdatoccur'], TRUE);
			$rs[$r]['tmodifdate']=$oMain->formatDate($rst['tmodifdate'],False);
		}
		
		$o->writeRS($rs, $flds, 'A', 1);
		
		$o->setFillColor('A1:Q1', 'A5FF7F');
		
		$o->download('SecurityPro_O_'.$this->tsite);
		exit;
	}

	private function showListV()
	{
		$oMain=$this->oMain;
		$tsdtm=$oMain->formatDate($this->tsdtm);
		$tedtm=$oMain->formatDate($this->tedtm);
		$sql="SELECT TOP 5000 tvisitid, tsite, tdatin, tdatout, tnama, tentr, 
			tregistration, temployee, templname, tdivi, tphone, treason, tremarks, tncompanion, tcomp1, tmodifiedby, tmodifdate 
			FROM dbo.tbsecvisit WHERE tsite='$this->tsite' AND tdatin BETWEEN '$tsdtm' AND '$tedtm'";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);

		$p="&tsdtm=$tsdtm&tedtm=$tedtm";
		$xls=$oMain->stdImglink('xlsv_secstat', '','',$p,'exportexcel.png','','_blank', $oMain->translate('excel'));

		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->title($oMain->translate('tasksearchresults')." ($rc) ".$xls);
		$oTable->dbClickLink($this->oMain->baseLink('', 'show_secvisit', '', 'tvisitid=§§tvisitid§§'));
		// $oTable->height($tableheight);               
		$oTable->data($rs);
		//$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
		$oTable->multilineRow(true); //in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf

		$oTable->columnAdd('tvisitid')->type('int')->hidded(true);
		$oTable->columnAdd('tsite')->hidded(true);
		$oTable->columnAdd('tdatin')->type('datetime');
		$oTable->columnAdd('tdatout')->type('datetime');
		$oTable->columnAdd('tnama');
		$oTable->columnAdd('tentr');
		$oTable->columnAdd('tregistration');
		$oTable->columnAdd('temployee')->type('int');
		$oTable->columnAdd('templname');
		$oTable->columnAdd('tdivi');
		$oTable->columnAdd('tphone');
		$oTable->columnAdd('treason');
		$oTable->columnAdd('tremarks');
		$oTable->columnAdd('tncompanion')->type('int');
		$oTable->columnAdd('tcomp1');
		$oTable->columnAdd('tmodifiedby')->hidded(true);
		$oTable->columnAdd('tmodifdate')->hidded(true);
		$html=$oTable->html();                

		if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}	
	private function showListL()
	{
		$oMain=$this->oMain;
		$tsdtm=$oMain->formatDate($this->tsdtm);
		$tedtm=$oMain->formatDate($this->tedtm);
		$sql="SELECT TOP 5000 tloadid, tsite, tdatin, tdatout, tregistration, 
			tdeliveryto, tentr, dbo.translate_code ('SecPro_LoadUnload', ttype, 'PT') AS ttypedesc,
			dbo.translate_code ('SecPro_DocType',	tdoctype, 'PT') AS tdoctypedesc, 
			tdocnumber, tchekdoc, tchecknumber, tcontact, tremarks,
			tnpassengers, tpass1, tpass2, tpass3, tmodifiedby, tmodifdate 
			FROM dbo.tbsecload WHERE tsite='$this->tsite' AND tdatin BETWEEN '$tsdtm' AND '$tedtm'";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);

		$p="&tsdtm=$tsdtm&tedtm=$tedtm";
		$xls=$oMain->stdImglink('xlsl_secstat', '','',$p,'exportexcel.png','','_blank', $oMain->translate('excel'));
		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->title($oMain->translate('tasksearchresults')." ($rc) ".$xls);
		$oTable->dbClickLink($this->oMain->baseLink('', 'show_secload', '', 'tloadid=§§tloadid§§'));
		// $oTable->height($tableheight);               
		$oTable->data($rs);
		//$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
		$oTable->multilineRow(true); //in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf

		$oTable->columnAdd('tloadid')		->type('int')->hidded(true);
		$oTable->columnAdd('tsite')			->hidded(true);
		$oTable->columnAdd('tdatin')		->type('datetime');
		$oTable->columnAdd('tdatout')		->type('datetime');
		$oTable->columnAdd('tregistration');
		$oTable->columnAdd('tdeliveryto');
		$oTable->columnAdd('tentr');
		$oTable->columnAdd('ttypedesc');
		$oTable->columnAdd('tdoctypedesc');
		$oTable->columnAdd('tdocnumber');
		$oTable->columnAdd('tchekdoc');
		$oTable->columnAdd('tchecknumber');
		$oTable->columnAdd('tcontact');
		$oTable->columnAdd('tremarks');
		$oTable->columnAdd('tnpassengers')	->type('int');
		$oTable->columnAdd('tpass1');
		$oTable->columnAdd('tpass2');
		$oTable->columnAdd('tpass3');
		$oTable->columnAdd('tmodifiedby')	->hidded(true);
		$oTable->columnAdd('tmodifdate')	->hidded(true);
		$html=$oTable->html();                

		if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);		
	}
	
	private function showListG()
	{
		$oMain=$this->oMain;
		$tsdtm=$oMain->formatDate($this->tsdtm);
		$tedtm=$oMain->formatDate($this->tedtm);
		$sql="SELECT TOP 5000 tgateid, tsite, tdatin, tdatout, temployee, templname,
			tdivi, dbo.translate_code('SecPro_GateType',treason, 'PT') AS treason, tremarks, tmodifiedby, tmodifdate 
			FROM dbo.tbsecgate WHERE tsite='$this->tsite' AND tdatin BETWEEN '$tsdtm' AND '$tedtm'";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);

		$p="&tsdtm=$tsdtm&tedtm=$tedtm";
		$xls=$oMain->stdImglink('xlsg_secstat', '','',$p,'exportexcel.png','','_blank', $oMain->translate('excel'));
		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->title($oMain->translate('tasksearchresults')." ($rc) ".$xls);
		$oTable->dbClickLink($this->oMain->baseLink('', 'show_secgate', '', 'tgateid=§§tgateid§§'));
		// $oTable->height($tableheight);               
		$oTable->data($rs);
		//$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
		$oTable->multilineRow(true); //in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf

		$oTable->columnAdd('tgateid')		->type('int')->hidded(true);
		$oTable->columnAdd('tsite')			->hidded(true);
		$oTable->columnAdd('tdatin')		->type('datetime');
		$oTable->columnAdd('tdatout')		->type('datetime');
		$oTable->columnAdd('temployee')		->type('int');
		$oTable->columnAdd('templname');
		$oTable->columnAdd('tdivi');
		$oTable->columnAdd('treason');
		$oTable->columnAdd('tremarks');
		$oTable->columnAdd('tmodifiedby')	->hidded(true);
		$oTable->columnAdd('tmodifdate')	->hidded(true);
		$html=$oTable->html();                

		if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);
	}	
	
	private function showListO()
	{
		$oMain=$this->oMain;
		$tsdtm=$oMain->formatDate($this->tsdtm);
		$tedtm=$oMain->formatDate($this->tedtm);
		$sql="SELECT TOP 5000 toccurid, tsite, tdatoccur, dbo.efa_uidname(tuserid) AS tuserid, tprio,
			tdesc, tpart, tdep, twitness, tremarks, tmodifiedby, tmodifdate 
			FROM dbo.tbsecoccur WHERE tsite='$this->tsite' AND tdatoccur BETWEEN '$tsdtm' AND '$tedtm'";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);

		$p="&tsdtm=$tsdtm&tedtm=$tedtm";
		$xls=$oMain->stdImglink('xlso_secstat', '','',$p,'exportexcel.png','','_blank', $oMain->translate('excel'));
		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->title($oMain->translate('tasksearchresults')." ($rc) ".$xls);
		$oTable->dbClickLink($this->oMain->baseLink('', 'show_secoccur', '', 'toccurid=§§toccurid§§'));
		// $oTable->height($tableheight);               
		$oTable->data($rs);
		//$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
		$oTable->multilineRow(true); //in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf

		$oTable->columnAdd('toccurid')		->type('int')->hidded(true);
		$oTable->columnAdd('tsite')			->hidded(true);
		$oTable->columnAdd('tdatoccur')		->type('datetime');
		$oTable->columnAdd('tuserid');
		$oTable->columnAdd('tprio');
		$oTable->columnAdd('tdesc');
		$oTable->columnAdd('tpart');
		$oTable->columnAdd('tdep');
		$oTable->columnAdd('twitness');
		$oTable->columnAdd('tremarks');
		$oTable->columnAdd('tmodifiedby')	->hidded(true);
		$oTable->columnAdd('tmodifdate')	->hidded(true);
		$html=$oTable->html();                

		if($rc>=5000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}

		return($html);		
	}
	
	function chartVisits()
	{
		$oMain = $this->oMain;
		require_once('cchart.php');

		if ($this->tyear<1970) {$this->tyear=date("Y");}
		$tyear1=$this->tyear+1;

		$sql="SELECT MONTH(tdatin) AS chartlabel, COUNT(tvisitid) AS chartval
FROM            dbo.tbsecvisit
WHERE        (tdatin BETWEEN '$this->tyear-01-01' AND '$tyear1-01-01')
GROUP BY MONTH(tdatin)
ORDER BY chartlabel";
		$rs = dbquery($oMain->consql, $sql, $flds);

		$o = new Cchart($oMain);
		$o->series($rs);
		//$o->type('donut');$o->width(600); $o->height(350); $o->pieinnertext(true); $o->xtitleWidth(270);
		//$o->type('line');$o->width(900); $o->height(300);
		$o->type('bar'); $o->width(600); $o->height(200);
		$o->xtitle("Visitantes por mês em $this->tyear");
	//	$o->title($oMain->translate('visitor'));
		$html=$o->html();
		
		return $html;
	}

	function chartLoads()
	{
		$oMain = $this->oMain;
		require_once('cchart.php');

		if ($this->tyear<1970) {$this->tyear=date("Y");}
		$tyear1=$this->tyear+1;

		$sql="SELECT MONTH(tdatin) AS chartlabel, COUNT(tloadid) AS chartval
FROM            dbo.tbsecload
WHERE        (tdatin BETWEEN '$this->tyear-01-01' AND '$tyear1-01-01')
GROUP BY MONTH(tdatin)
ORDER BY chartlabel";
		$rs = dbquery($oMain->consql, $sql, $flds);

		$o = new Cchart($oMain);
		$o->series($rs);
		$o->type('bar'); $o->width(600); $o->height(200);
		$o->xtitle("Cargas/descargas por mês em $this->tyear");
		$html=$o->html();
		
		return $html;
	}
	
	function chartGates()
	{
		$oMain = $this->oMain;
		require_once('cchart.php');

		if ($this->tyear<1970) {$this->tyear=date("Y");}
		$tyear1=$this->tyear+1;

		$sql="SELECT MONTH(tdatin) AS chartlabel, COUNT(tgateid) AS chartval
FROM            dbo.tbsecgate
WHERE        (tdatin BETWEEN '$this->tyear-01-01' AND '$tyear1-01-01')
GROUP BY MONTH(tdatin)
ORDER BY chartlabel";
		$rs = dbquery($oMain->consql, $sql, $flds);

		$o = new Cchart($oMain);
		$o->series($rs);
		$o->type('bar'); $o->width(600); $o->height(200);
		$o->xtitle("Entradas/Saídas por mês em $this->tyear");
		$html=$o->html();
		
		return $html;
	}
	
	function chartOccurs()
	{
		$oMain = $this->oMain;
		require_once('cchart.php');

		if ($this->tyear<1970) {$this->tyear=date("Y");}
		$tyear1=$this->tyear+1;

		$sql="SELECT MONTH(tdatoccur) AS chartlabel, COUNT(toccurid) AS chartval
FROM            dbo.tbsecoccur
WHERE        (tdatoccur BETWEEN '$this->tyear-01-01' AND '$tyear1-01-01')
GROUP BY MONTH(tdatoccur)
ORDER BY chartlabel";
		$rs = dbquery($oMain->consql, $sql, $flds);

		$o = new Cchart($oMain);
		$o->series($rs);
		$o->type('bar'); $o->width(600); $o->height(200);
		$o->xtitle("Ocorrências por mês em $this->tyear");
		$html=$o->html();
		
		return $html;
	}
	
	        
}// End of CSecStat
?>
