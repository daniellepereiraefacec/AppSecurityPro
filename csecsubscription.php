<?php
/**
 * @@name		Occurrence subscription
 * @@author		Luis Gomes 
 * @@version 	24/04/2018 14:17:29

 * Revisions:
 * 
 */

class CSubscription
{
	var $tid;
	var $toccurid;
	var $tuserid;
	var $tgpid;
	var $tnotifydate;    // notify date
	var $tmodifiedby;
	var $tmodifdate;
	
	var $toccur;
	

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
		$ent='subscription'; 

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$mod='show_secoccur'; return '';
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$mod='show_secoccur'; return '';
		}

//		if ($mod =='update_'.$ent)
//		{
//			$tstatus=$this->storeIntoDB('update', $tdesc);
//			$oMain->stdShowResult($tstatus, $tdesc);
//			if($tstatus==0)	{ $mod='show_'.$ent;}
//			else			{ $mod='xedit_'.$ent;} // user retry
//		}

//		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
//		{
//			if($mod =='edit_'.$ent)
//			{	$this->readFromDb(); }
//			$title=$oMain->translate('edit_'.$ent).' '.$this->tid;
//			$html=$this->form('update_'.$ent,'xedit_'.$ent);
//
//		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$title =$oMain->translate('show_secoccur').' '.
					$oMain->stdImglink('show_secoccur', '','',"toccurid=$this->toccurid",'',$this->toccurid,'', $oMain->translate('linktoccurid')).' ('.$this->toccur.') - ';
			$title.=$oMain->translate('new_'.$ent);
			$html=$this->form('insert_'.$ent,'xnew_'.$ent);
		}
              
//		if ($mod =='list_'.$ent)
//		{
//			$title=$oMain->translate($mod).' '.$this->tid;
//			$html=$this->showList();
//		}

		if ($mod =='show_'.$ent)
		{
			$this->readFromDb();
			$title.=$oMain->translate('show_'.$ent).' '.$this->tid;
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
            $arrparam[]=array(0 => 'tid',	1 => $this->tid);
            $arrparam[]=array(0 => 'ttree',	1 => $ttree);
            $html.=getHtmltreeStd('efa.php',$oMain->page,'show_subscriptiontree',$this->operation,$arrparam);
            return ($html);
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
            
	private function get_toccur()
	{
		$oMain = $this->oMain;
		$sql="SELECT toccur FROM dbo.tbsecoccur WHERE toccurid=$this->toccurid ";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		return$rs[0]['toccur'];
	}
	
	protected function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->tid			=$oMain->GetFromArray('tid',$_REQUEST,'int');
		$this->toccurid		=$oMain->GetFromArray('toccurid',$_REQUEST,'int');
		$this->tuserid		=$oMain->GetFromArray('tuserid',$_REQUEST,'int');
		$this->tgpid		=$oMain->GetFromArray('tgpid',$_REQUEST,'int');
		$this->tnotifydate	=$oMain->GetFromArray('tnotifydate',$_REQUEST,'date');
		$this->tmodifiedby	=$oMain->GetFromArray('tmodifiedby',$_REQUEST,'string_trim');
		$this->tmodifdate	=$oMain->GetFromArray('tmodifdate',$_REQUEST,'string_trim');
		$this->toccur		=$this->get_toccur();
		$tpreid				=$oMain->GetFromArray('tpreid',$_REQUEST,'string_trim');
		if(substr($tpreid, 0,1)=='G' ) {$this->tgpid  =(int)substr($tpreid, 1);}
		if(substr($tpreid, 0,1)=='U' ) {$this->tuserid=(int)substr($tpreid, 1);}

	}
	/**
	 * class CSubscription form
	 */	
	protected function form($mod='show_subscription',$modChange='')
	{

		$oMain=$this->oMain;

		$operation='';$nCol=2;$width='60%';$ajax=false;
		$modCancel='show_secoccur';

		$frmMod=CForm::MODE_EDIT;

		$onChange="$formName.mod.value='$modChange';$formName.submit(); ".$oMain->loading();

		$formName='frmCSubsGrpList';	//	*	*	*	*	*	*	*	*	*	*
		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$oForm->setWaitActionOnSubmit($oMain->loading());
		$aForm = array();
	
		$aForm[] = new CFormHidden('toccurid', $this->toccurid);

		$sql="SELECT tvalue FROM dbo.tbmodparam WHERE (module = 'securitypro') AND (company = 'efa') AND (tfield = 'gen3')";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$groups= explode(" ", $rs[0]['tvalue']);
	    reset($groups); $gcriteria='-2123123123';
		
        while(list($key, $val) = each($groups)) {$gcriteria.=','.(int)$val;}		
		
		$sql="SELECT tvalue FROM dbo.tbmodparam WHERE (module = 'securitypro') AND (company = 'efa') AND (tfield = 'gen4')";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$users= explode(" ", $rs[0]['tvalue']);
	    reset($users); $ucriteria='-2123123123';
		
        while(list($key, $val) = each($users)) {$ucriteria.=','.(int)$val;}		
				
		
		$sql="
SELECT 'G'+LTRIM(STR(tgpid)) As   tpreid, '[G] '+groupdesc AS tdesc FROM dbo.tbgroups WITH (nolock) WHERE (tgpid IN ($gcriteria)) 
UNION ALL		
SELECT 'U'+LTRIM(STR(employee)) As tpreid,'[U] '+username AS tdesc FROM dbo.tbusers  WITH (nolock) WHERE (employee IN ($ucriteria)) 
ORDER BY tdesc";
		$aForm[] = new CFormSelect($oMain->translate('tpreid'), 'tpreid', $this->tpreid, $this->ttpreiddesc, $sql, $oMain->consql,'',' ',' ',CForm::REQUIRED);		

		
		$aForm[]= new CFormButton('save', $oMain->translate ('save'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$oForm->addElementsCollection($aForm);
		$html='<br>'.$oMain->efaHR($oMain->translate ('subcribepregroup')).$oForm->getHtmlCode();
		
		
		
		$formName='frmCSubsUser';		//	*	*	*	*	*	*	*	*	*	*
		unset($aForm);
		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$oForm->setWaitActionOnSubmit($oMain->loading());
		$aForm = array();
	
		$aForm[] = new CFormHidden('toccurid', $this->toccurid);
//		$aForm[] = new CFormText($oMain->translate('tuserid'),'tuserid', $this->tuserid,4,'',false,'',CFormText::INPUT_INTEGER);

		$search_tresp=$oMain->stdPopupwin('GETCCUSER',$formName,'tuserid','tname_','tuserid','tname_','','employee');
		$field_tresp = new CFormText($oMain->translate('tuserid'),'tuserid', $this->tuserid,'',CForm::RECOMMENDED,false);
		$field_tresp_desc = new CFormText($oMain->translate('tuserid'), 'tname_', $this->tname_, '', '',false); //, '', '', '', 70
		if($frmMod==CForm::MODE_EDIT)
		   $field_tresp->setExtraData($search_tresp);
		$cformsel = $cformsel = $aForm[] = new CFormMultipleElement(array($field_tresp, $field_tresp_desc), 0);
//		$cformsel->setNumberOfColumns(2);		
		
		
		
		$aForm[]= new CFormButton('save', $oMain->translate ('save'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$oForm->addElementsCollection($aForm);
		$html.=$oMain->stdJsPopUpWin('400').'<br>'.$oMain->efaHR($oMain->translate ('subcribeuser')).$oForm->getHtmlCode().'<br>';

		
		$formName='frmCSubsGroup';		//	*	*	*	*	*	*	*	*	*	*
		unset($aForm);
		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$oForm->setWaitActionOnSubmit($oMain->loading());
		$aForm = array();
	
		$aForm[] = new CFormHidden('toccurid', $this->toccurid);
//		$aForm[] = new CFormText($oMain->translate('tgpid'),'tgpid', $this->tgpid,4,'',false,'',CFormText::INPUT_INTEGER);

		
		$field_tgpid = new CFormText($oMain->translate('tgpid'),'tgpid', $this->tgpid,6,CForm::RECOMMENDED,false);
		$field_tgpid_desc = new CFormText($oMain->translate('tgpid'), 'tgpiddesc', $this->tgpiddesc, '', '',$itemrelated_readonly, '', '', '', 70);
		if($frmMod==CForm::MODE_EDIT )
		{
		   $search_tgpid=$oMain->stdPopupwin('GETGROUP',$formName,'tgpid','tgpiddesc','tgpid','tgpiddesc',$oMain->comp,'release_active');	
		   $field_tgpid->setExtraData($search_tgpid);
		}
		$aForm[] = new CFormMultipleElement(array($field_tgpid, $field_tgpid_desc), 0);		
			
		
		$aForm[]= new CFormButton('save', $oMain->translate ('save'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$oForm->addElementsCollection($aForm);
		$html.=$oMain->efaHR($oMain->translate ('subcribegroup')).$oForm->getHtmlCode();		
		
		
		
		
//		$buttonCancel = new CFormButton('cancel', $oMain->translate ('cancel'),CFormButton::TYPE_BUTTON,'',CFormButton::LOCATION_FORM_BOTTOM);
//		$buttonCancel->addEvent("onclick=\"$formName.mod.value='$modCancel';$onSubmit\"");
//		$aForm[]=$buttonCancel;
		
		
		return $html;
	}

	/**
	 * class CSubscription showData
	 */	
	protected function showData()
	{
		$oMain=$this->oMain;
		$data = array();
		$data[] = array($oMain->translate('tid'), $this->tid);
		$data[] = array($oMain->translate('toccurid'), $this->toccurid);
		$data[] = array($oMain->translate('tuserid'), $this->tuserid);
		$data[] = array($oMain->translate('tgpid'), $this->tgpid);
		$data[] = array($oMain->translate('tnotifydate'), $this->tnotifydate);
		$data[] = array($oMain->translate('tmodifiedby'), $this->tmodifiedby);
		$data[] = array($oMain->translate('tmodifdate'), $this->tmodifdate);
		

		$x = new efaDataDisplay($this->oMain);
		$x->cols(2);
		$x->labelWidth(30);
		$x->data($data);

		return $x->html(); 


	}	
	
	/**
	 * save class CSubscription record into database
	 */	
	protected function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="[dbo].[spsecoccursub] @sid='$sid',@sp_operation='$operation',@norecordset='0',@tid='$this->tid'
		,@toccurid='$this->toccurid'
		,@tuserid='$this->tuserid'
		,@tgpid='$this->tgpid'
		,@itnotifydate='$this->tnotifydate'
		
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
		return($rs[0]['tstatus']);
	}
	/**
	 * query to get class CSubscription record from database
	 */	
	protected function sqlGet()
	{
		$oMain = $this->oMain;

		$sql="SELECT  tid,toccurid,tuserid,tgpid,tnotifydate,tmodifiedby,tmodifdate
		 FROM dbo.tbsecoccursub WITH (NOLOCK) WHERE 
		tid='$this->tid'";		

		return($sql);
	}
	/**
	 * set class CSubscription atributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
				$this->tid=$rs[0]['tid'];
			$this->toccurid=$rs[0]['toccurid'];
			$this->tuserid=$rs[0]['tuserid'];
			$this->tgpid=$rs[0]['tgpid'];
			$this->tnotifydate=$rs[0]['tnotifydate'];
			$this->tmodifiedby=$rs[0]['tmodifiedby'];
			$this->tmodifdate=$rs[0]['tmodifdate'];
			
		}
		return $rc;
	}
	
	 /**
	  * advanced Search query to database
	  */
	protected function sqlSearch()
	{
		$oMain=$this->oMain;
		$sql="SELECT  tid,toccurid,tuserid,tgpid,tnotifydate,tmodifiedby,tmodifdate
		 FROM dbo.tbsecoccursub WITH (NOLOCK) 
		 ";	
		return $sql;
	}
        
	/**
	 * show list results
	 */		
	protected function showList()
	{
		$oMain=$this->oMain;
		$sql=$this->sqlSearch();
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);		
		for ($r = 0; $r < $rc; $r++)
		{
			$link_tid=$oMain->stdImglink('show_subscription', '','',$tparam,'',$rs[$r]['tid'],'', $oMain->translate('linktid'));
					
			$rs[$r]['toperations']= $oMain->stdImglink( 'edit_subscription', '','','&tid='.$rs[$r]['tid'], 'edit_s.png', $oMain->translate('edit'));
			$rs[$r]['toperations'].= $oMain->stdImglink( 'del_subscription', '','','&tid='.$rs[$r]['tid'], 'delete_s.png', $oMain->translate('remove'), '', '', $oMain->translate('confirm_remove'),$oMain->loading());				
		}

	   $oTable = new efaGrid($oMain);
	   $oTable->skin('dhx_web');
	   $oTable->title($oMain->translate('tasksearchresults')." ($rc)");
	   $oTable->dbClickLink($this->oMain->baseLink('', 'show_subscription', '', 'tid=§§tid§§'));
	   // $oTable->height($tableheight);               
	   $oTable->data($rs);
	   //$oTable->liveUpdate(true); //if true stores the field value in database by row (only the field edited)
	   $oTable->multilineRow(true); //in case of large text fields shows all text
	   $oTable->widthUsePercent(true); //set percentage as unit to set with of columns
	   //$oTable->exportToExcel(true);  // if true enables icon to export data to excel
	   //$oTable->exportToPdf(true);    // if true enables icon to export data to pdf
	   
	   $oTable->columnAdd('tid')->type('int');
		$oTable->columnAdd('toccurid')->type('int');
		$oTable->columnAdd('tuserid')->type('int');
		$oTable->columnAdd('tgpid')->type('int');
		$oTable->columnAdd('tnotifydate')->type('date');
		$oTable->columnAdd('tmodifiedby');
		$oTable->columnAdd('tmodifdate');
		
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
        
	        
}// Enf of CSubscription
?>
