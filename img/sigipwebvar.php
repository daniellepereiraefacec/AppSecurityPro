<?php

/**
 * @@name	<table description resume>
 * @@author	Pedro Brandão
 * @@version 	07-11-2012 13:56:44
 *
 * Revisions:
 * 2015-06-25	PBrandao - Copy Variants
 */

require_once('ccommonsql.php');

class CVarDecisao
{
	var $cond;
//	var $product;    /**  */
	var $nome;
	var $fonte;
	var $atributo;
	var $tipodados;
	var $decisao;
	var $pergunta;
	var $global; 
	var $descricao;
	var $condvariante;
	var $condregrasvar;
	var $condproduto;
	var $globalproduto;
	var $simbolo;
	var $tipo;
	var $param1;
	var $param2;
	var $param3;
	var $dc;
	var $et;
	
	var $lang;
	var $unitextdesc;
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class capplications mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='vardec'; 
//var_dump($_REQUEST); die();
//print 'mod: '.$mod;	
		
		$idpopup='vardecPopup';
		
		
		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$idtgrid='vardecTGrid';
			$html=$oMain->refresh($idtgrid);

		}
		
		if ($mod =='delcomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('deleteunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$idtgrid='vdcomdescTGrid';
			$html=$oMain->refresh($idtgrid);
	
		}
        
        if($mod =='copyinsert_'.$ent)
        {
            $mod='insert_'.$ent;
        }

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
            
			if($tstatus==0)
			{
				$idpopup='vardecPopup';
				$idtgrid='vardecTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='xnew_'.$ent;
				
		}
		
		if ($mod =='rename_'.$ent)
		{
			$tstatus=$this->storeIntoDB('rename', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
//				$this->desenhoop = $this->desenhoopnew;
				$html=$oMain->close($idpopup);
				$html.=$this->showList();
			}
			else 
			{
				$html.=$this->showList();
			}
		}
		
		
		if ($mod =='insertcomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insertunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
				{

				$idpopup='vardeccdescPopup';
				$idtgrid='vdcomdescTGrid';
				
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
				
				}
				else $mod='xnewcomdesc_'.$ent;

		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
				$idpopup='vardecPopup';
				$idtgrid='vardecTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xedit_'.$ent;
		}
        
        
		
		if ($mod =='updatecomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('updateunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
				$idpopup='vardeccdescPopup';
				$idtgrid='vdcomdescTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xeditcomdesc_'.$ent;
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			
			if($mod =='edit_'.$ent)
				$this->readFromDB();
			
			if(_request('ppajax')!='')
			{
				$id='vardecPopup';
				$title=$oMain->translate('edit_vardec').' - '.$this->nome;
				$content=$this->form('update_'.$ent,'xedit_'.$ent);
				$html=$oMain->popupOpen($id, $title, $content);
				
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('update_'.$ent,'xedit_'.$ent);
			}
			
		}
        
        
        if ($mod =='copy_'.$ent || $mod =='xcopy_'.$ent)
		{
			
			if($mod =='copy_'.$ent)
				$this->readFromDB();
			
			if(_request('ppajax')!='')
			{
				$id='vardecPopup';
				$title=$oMain->translate('copy_vardec').' - '.$this->nome;
				$content=$this->form('copyinsert_'.$ent,'xnew_'.$ent);
				$html=$oMain->popupOpen($id, $title, $content);
				
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('copyinsert_'.$ent,'xnew_'.$ent);
			}
			
		}
		
		
		if ($mod =='editcomdesc_'.$ent || $mod =='xeditcomdesc_'.$ent)
		{
			
			if($mod =='editcomdesc_'.$ent)
				$this->readfromdbComDesc();
			
			
			if(_request('ppajax')!='')
			{
				
				$id='vardeccdescPopup';
				$title=$oMain->translate('editcomdesc').' - '.$this->nome;
				$content=$this->formComDesc('updatecomdesc_'.$ent,'xeditcomdesc_'.$ent);
				$html=$oMain->popupOpen($id, $title, $content);
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('updatecompdesc_'.$ent,'xeditcompdesc_'.$ent);
			}

		}
		
		

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			
			if(_request('ppajax')!='')
				{
				
				$id='vardecPopup';
				$title=$oMain->translate('new_vardec');
				$content=$this->form('insert_'.$ent,'xnew_'.$ent);
				$html=$oMain->popupOpen($id, $title, $content);
				
				}
				else 
				{
					$oMain->subtitle=$oMain->translate('show_'.$ent);
					$html=$this->form('insert_'.$ent,'xnew_'.$ent);
				}
		}
		
		if ($mod =='newrename_'.$ent)
		{
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('rename').' '.$this->nome;	
				$content=$this->formRename('rename_vardec');
				$menu=$oMain->translate('vardec');
				$footer='';
				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
			}
			else 
			{
				$oMain->subtitle=$oMain->translate('show_'.$ent);
				$html=$this->form('insert_'.$ent,'xnew_'.$ent);
			}
		}
		
		
		if ($mod =='newcomdesc_'.$ent or $mod =='xnewcomdesc_'.$ent)
		{
			
			if(_request('ppajax')!='')
				{

				$id='vardeccdescPopup';
				$title=$oMain->translate('newcomdesc').' '.$this->nome;
				$menu=$oMain->translate('vardec');
				$content=$this->formComDesc('insertcomdesc_'.$ent,'xnewcomdesc_'.$ent);
				$html=$oMain->popupOpen($id, $title, $content,'',$menu);
				}
				else 
				{
					$oMain->subtitle=$oMain->translate('show_'.$ent);
					$html=$this->form('insert_'.$ent,'xnew_'.$ent);
				}
		}

		
		if ($mod =='list_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showList();
		}
		
		if ($mod =='listcodes_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showListCodes();
		}
		
		
		if ($mod =='listcomdesc_'.$ent)
		{
			
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showListComDesc();
		}
		


		return($html);
	}
	
	
	 /**
	  * read class capplications atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->cond = $oMain->GetFromArray('cond', $_REQUEST, 'int');
		
		$this->nome=$oMain->GetFromArray('nome',$_REQUEST,'string_trim');
		$this->fonte=$oMain->GetFromArray('fonte',$_REQUEST,'string_trim');
		$this->atributo=$oMain->GetFromArray('atributo',$_REQUEST,'string_trim');
		$this->tipodados=$oMain->GetFromArray('tipodados',$_REQUEST,'string_trim');
		$this->decisao=$oMain->GetFromArray('decisao',$_REQUEST,'string_trim');
		$this->pergunta=$oMain->GetFromArray('pergunta',$_REQUEST,'string_trim');
		$this->global=$oMain->GetFromArray('global',$_REQUEST,'string_trim');
		$this->descricao=$oMain->GetFromArray('descricao',$_REQUEST,'string_trim');
		$this->condvariante=$oMain->GetFromArray('condvariante',$_REQUEST,'string_trim');
		$this->condregrasvar=$oMain->GetFromArray('condregrasvar',$_REQUEST,'string_trim');
		$this->condproduto=$oMain->GetFromArray('condproduto',$_REQUEST,'string_trim');
		$this->globalproduto=$oMain->GetFromArray('globalproduto',$_REQUEST,'string_trim');
		$this->simbolo=$oMain->GetFromArray('simbolo',$_REQUEST,'string_trim');
		$this->tipo=$oMain->GetFromArray('tipo',$_REQUEST,'string_trim');
		$this->param1=$oMain->GetFromArray('param1',$_REQUEST,'string_trim');
		$this->param2=$oMain->GetFromArray('param2',$_REQUEST,'string_trim');
		$this->param3=$oMain->GetFromArray('param3',$_REQUEST,'string_trim');
		$this->dc=$oMain->GetFromArray('dc',$_REQUEST,'string_trim');
		$this->et=$oMain->GetFromArray('et',$_REQUEST,'string_trim');
		$this->unitextdesc=$oMain->GetFromArray('unitextdesc',$_REQUEST,'string_trim');
		$this->lang=$oMain->GetFromArray('lang',$_REQUEST,'string_trim');
	}
	
	
	function form($mod='show_vardec', $modChange='')
	{
		$oMain=$this->oMain;

		$operation='';
		$formName = 'formvardec'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(1);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
//		$form->hidden('companhia', $this->companhia);
		
		$form->elementAdd(new _htmlFieldsGroup('t1', $this->oMain->translate('identification')));	
		
		$form->elementAdd(new _htmlInput('nome', $oMain->translate('nome'), $this->nome))->required(true);
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebalfa' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('fonte', $oMain->translate('fonte'), $this->fonte, $temp))->blank(true)->required(true);
		
		$sql="select distinct campo, campo as tdesc FROM BDSIGIP.dbo.tbQE_ValoresCampos";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('atributo', $oMain->translate('atributo'), $this->atributo, $temp))->blank(true)->required(true);
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebyesno' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('pergunta', $oMain->translate('pergunta'), $this->pergunta, $temp))->blank(true)->required(true);

		$form->elementAdd(new _htmlInput('descricao', $oMain->translate('descricao'), $this->descricao))->required(true);
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebyesno' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('globalproduto', $oMain->translate('globalproduto'), $this->globalproduto, $temp))->blank(true)->required(true);
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebyesno' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('et', $oMain->translate('et'), $this->et, $temp))->blank(true);
		
		$form->elementAdd(new _htmlFieldsGroup('t2', $this->oMain->translate('simbpci')));	

		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebyesno' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$select=$form->elementAdd(new _htmlSelect('simbolo', $oMain->translate('simbolo'), $this->simbolo, $temp))->blank(true)->required(true);
		$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());

		if($mod=='insert_vardec')
		{
			$this->param1='N';
			$this->param2='N';
			$this->param3='N';
		}
		if($mod=='insert_vardec' && ($this->simbolo=='N' OR $this->simbolo==''))
		{
			$read=TRUE;
		}
		
		if($mod=='insert_vardec' && $this->simbolo=='S')
		{
			$read=FALSE;
			$this->param1='';
			$this->param2='';
			$this->param3='';
		}
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebtype' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('tipo', $oMain->translate('tipo'), $this->tipo, $temp))->blank(true)->readonly($read);
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebyesno' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('param1', $oMain->translate('extesq'), $this->param1, $temp))->blank(true)->readonly($read);
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebyesno' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('param2', $oMain->translate('extdir'), $this->param2, $temp))->blank(true)->readonly($read);
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebyesno' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('param3', $oMain->translate('int'), $this->param3, $temp))->blank(true)->readonly($read);

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		//$button->confirm($oMain->translate('confassociate'));
		
		return $form->html().$button->html();
	}
	
	
	function formComDesc($mod='showcomdesc_vardec', $modChange='')
	{
		$oMain=$this->oMain;
//var_dump($_REQUEST);
		$operation='';
		$formName = 'formcomdescvd'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(2);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		//$form->hidden('id', $this->id);
		$form->hidden('nome', $this->nome);
		$form->hidden('cond', $this->cond);
//var_dump ($this->lang);		
		$sql="SELECT RTRIM(Codigo), Descricao FROM dbo.tbIdiomas ORDER BY Descricao asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('lang', $oMain->translate('idioma'), $this->lang, $temp))->blank(true)->required(true);
		
		$form->elementAdd(new _htmlTextarea('unitextdesc', $oMain->translate('unitextdesc'), $this->unitextdesc),null,2)->required(true)->maxlength(1000);

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		//$button->confirm($oMain->translate('confassociate'));
		
		return $form->html().$button->html();
	}
	
	
	function formRename($mod)
	{
		$oMain=$this->oMain;
		
		$operation=$oMain->operation;
		$formName = 'formrenvardec';
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(1);
		$form->labelWidth(150);
		$form->ajax(true)->ajaxDiv('main');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		//desenho antigo
		$form->hidden('nome', $this->nome);
		$form->hidden('cond', $this->cond);

		$form->elementAdd(new _htmlInput('descricao', $oMain->translate('nomenovo'), $this->nome))->readonly(FALSE)->required(true);

		$button=(_button('', $oMain->translate('save'),  _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));

		$button->confirm($oMain->translate('conf'));

		return $form->html().$button->html();
		
	}
	
	
	function showList()
	{
		$oMain=$this->oMain;
		
		$sql="SELECT Nome, Fonte, Atributo, TipoDados, Decisao, Pergunta, [Global], Descricao, CondVariante, CondRegrasVar, CondProduto, GlobalProduto, simbolo, Tipo
		, Param1, Param2, Param3, DC, ET, '' as toperations, '' as img,
			tdesc=CASE WHEN
			DC IS NULL THEN nome
			WHEN
			BDSIGIP.dbo.qe_translate_unitext(DC,'$oMain->l')='Translation unavailable: ' THEN nome
			ELSE
			(BDSIGIP.dbo.qe_translate_unitext(DC,'$oMain->l')+' - ('+nome+')')
			END
		FROM BDSIGIP.dbo.tbQE_VariaveisDecisao";
		
		if($this->cond!=0)
			{
			switch($this->cond)	
				{
				//case 1: $sql .= " where Decisao='S' order by Nome"; break;
				case 2: $sql .= " where Global='S' and GlobalProduto='S' and Decisao='S' order by Nome"; break;
				case 3: $sql .= " Where Global='S' and GlobalProduto='N' and Decisao='S' order by Nome asc"; break;
				case 4: $sql .= " Where Simbolo='s'order by Nome asc"; break;
				case 5: $sql .= " Where Global='N' and Decisao='S' order by Nome"; break;
				//case 6: $this->showListCodes(); break;
				}
			}
		
//var_dump($sql); die;
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);

		for ($r = 0; $r < $rc; $r++)
		{
			$rules=$rs[$r]['Global'];
			$global=$rs[$r]['GlobalProduto'];
			$et=$rs[$r]['ET'];
			$nome=$rs[$r]['Nome'];
			$tdesc=$rs[$r]['tdesc'];
			
			if($tdesc<>'')
				$rs[$r]['Nome']=$tdesc;
			
			$rs[$r]['img']='<img src="img/features_s.png" title="'.$oMain->translate('vardec').'"">';
			
			if($rules=='S')
				$rs[$r]['Global']='<img src="img/enable_s.png" title='.$oMain->translate('rule').'">';
			else
				$rs[$r]['Global']='-';
			
			
			if($global=='S')
				$rs[$r]['GlobalProduto']='<img src="img/enable_s.png" title='.$oMain->translate('global').'">';
			else
				$rs[$r]['GlobalProduto']='-';
			
			if($et=='S')
				$rs[$r]['ET']='<img src="img/enable_s.png" title='.$oMain->translate('et').'">';
			else
				$rs[$r]['ET']='-';
			
            
            $onCLickcopy = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'copy_vardec', '', 'nome='.urlencode($nome).'&cond='.$this->cond.'&ppajax=1')."', 'script');";
			$linkcopy = '<a href="#" onclick="'.$onCLickcopy.'" title="'.$oMain->translate('copy').'"><img src="img/copy3_s.png"></a>';
			
			$onCLickren = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'newrename_vardec', '', 'nome='.urlencode($nome).'&cond='.$this->cond.'&ppajax=1')."', 'script');";
			$linkren = '<a href="#" onclick="'.$onCLickren.'" title="'.$oMain->translate('rename').'"><img src="img/rename_s.png"></a>';

			$linkcomdesc=$oMain->stdImglink('listcomdesc_vardec', '','','nome='.urlencode($nome).'&cond='.$this->cond,'img/translate_s.png','','', $oMain->translate('comdesc'));
			
			$onCLickedit = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_vardec', '', 'nome='.urlencode($nome).'&cond='.$this->cond.'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLickedit.'" title="'.$oMain->translate('edit').'"><img src="img/edit_s.png"></a>';
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'del_vardec', '', 'nome='.urlencode($nome).'&cond='.$this->cond.'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['toperations']=$linkcomdesc.' '.$linkren.' '.$linkedit.' '.$linkcopy.' '.$linkdel;
		}
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$linknewvardec=$oMain->baseLink('sigipweb', 'new_vardec', '', 'product='.$this->product, '', '');
		$linknewvardec.= '&ppajax=1';

		$menuObj->addItem('x10', $oMain->translate('new_vardec'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$linknewvardec.'", "script");');


		$table = $oMain->_stdTGrid('vardecTGrid');
		$trad=$oMain->translate('list_vardec');
		if($this->cond!=0)
		{
			if($this->cond==1)	$trad=$oMain->translate('variables');
			if($this->cond==2)	$trad=$oMain->translate('glovariables');
			if($this->cond==3)	$trad=$oMain->translate('varvariables');
			if($this->cond==4)	$trad=$oMain->translate('pcivariables');
			if($this->cond==5)	$trad=$oMain->translate('fixvariables');
			if($this->cond==6)	$trad=$oMain->translate('codvariables');
		}
		$table->title($trad);
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'list_vardec', '', 'cond='.$this->cond));
		$table->border(0);
		$table->vals($rs);
		
		
		$table->column('img')->title('!')->width('2.0em')->searchable(false);
		$table->column('Nome')->title($oMain->translate('nome'));
		$table->column('Global')->title($oMain->translate('rules'))->width('8.0em')->searchable(false);
		$table->column('GlobalProduto')->title($oMain->translate('glob'))->width('8.0em')->searchable(false);
		$table->column('ET')->title($oMain->translate('ett'))->width('8.0em')->searchable(false);
		$table->column('Atributo')->title($oMain->translate('atributo'));
		$table->column('toperations')->title('!')->width('9.0em')->searchable(false);
		

		return $table->html();
	}
	
	function showListCodes()
	{
		$oMain=$this->oMain;
		$sql="SELECT '' as img, codigo, nome, descrição FROM bdsigip.dbo.tbPP_PalavrasChave order by Codigo";
		
//var_dump($sql); die;
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
//		$ArrayRst=array();
//		$elementos=0;
		for ($r = 0; $r < $rc; $r++)
		{
			$rs[$r]['img']='<img src="img/features_s.png" title='.$oMain->translate('vardec').'">';
		}
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$linknewvardec=$oMain->baseLink('sigipweb', 'new_vardec', '', 'product='.$this->product, '', '');
		$linknewvardec.= '&ppajax=1';

		$menuObj->addItem('x10', $oMain->translate('new_vardec'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$linknewvardec.'", "script");');


		$table = $oMain->_stdTGrid('vardecTGrid');
		$trad=$oMain->translate('list_codvariables');

		$table->title($trad);
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'listcodes_vardec', '', ''));
		$table->border(0);
		$table->vals($rs);
		
		$table->column('img')->title('!')->width('2.0em')->searchable(false);
		$table->column('codigo')->title($oMain->translate('codigo'))->width('10.0em');
		$table->column('nome')->title($oMain->translate('nome'))->width('30.0em');

		return $table->html();
	}
	
	function showListComDesc()
	{
		$oMain=$this->oMain;
		$sql="[bdsigip].[dbo].[spsigfeatures] '$oMain->sid', 'listunitext', '0', '$this->nome'";
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		$ArrayRst=array();
		$elementos=0;
		for ($r = 0; $r < $rc; $r++)
		{

			$onCLickedit = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'editcomdesc_vardec', '', 'nome='.urlencode($this->nome).'&cond='.$this->cond.'&lang='.$rs[$r]['Idioma'].'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLickedit.'" title="'.$oMain->translate('editcomdesc_vardec').'"><img src="img/edit_s.png"></a>';
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'delcomdesc_vardec', '', 'nome='.urlencode($this->nome).'&cond='.$this->cond.'&lang='.$rs[$r]['Idioma'].'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';

			$ArrayRst[$elementos]['ID']=$rs[$r]['ID'];
			$ArrayRst[$elementos]['Idioma']=$rs[$r]['Idioma'];
			$ArrayRst[$elementos]['Descricao']=$rs[$r]['Descricao'];
			$ArrayRst[$elementos]['NomeIdioma']=$rs[$r]['NomeIdioma'];
			$ArrayRst[$elementos]['toperations']=$linkedit.' '.$linkdel;

			$elementos++;
		}
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$linknewcomdesc=$oMain->baseLink('sigipweb', 'newcomdesc_vardec', '', 'nome='.urlencode($this->nome), '', '');
		$linknewcomdesc.= '&ppajax=1';
		$linknewcomdesc = '_urlContent2DivLoader("'.$linknewcomdesc.'", "script")';
		
		$linkvardec=$oMain->baseLink('sigipweb', 'list_vardec', '', 'nome='.urlencode($this->nome).'&cond='.$this->cond, '', '');

		$menuObj->addItem('x10', $oMain->translate('vardec'), 'img/back_s.png', $linkvardec);
		$menuObj->addItem('x20', $oMain->translate('new_comdesc'), 'img/new_s.png', '', $linknewcomdesc);

		$table = $oMain->_stdTGrid('vdcomdescTGrid');
		$table->title($oMain->translate('list_comdesc').' | '.$oMain->translate('vardec').' - '.$this->nome);
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'listcomdesc_vardec', '', '&nome='.urlencode($this->nome).'&cond='.$this->cond));
		$table->border(0);
		$table->vals($ArrayRst);

		$table->column('Descricao')->title($oMain->translate('desc'))->width('35.0em');
		$table->column('NomeIdioma')->title($oMain->translate('idioma'))->width('15.0em');
		$table->column('toperations')->title('!')->width('3.0em')->searchable(false)->sortable(false);
		

		return $table->html();
	}
	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT nome, fonte, atributo, tipodados, decisao, pergunta, [global], descricao, condvariante, condregrasvar, condproduto, globalproduto, simbolo, tipo
		, param1, param2, param3, dc, et
		FROM BDSIGIP.dbo.tbQE_VariaveisDecisao
		WHERE Nome='$this->nome'";		
//var_dump($sql); die;
		return($sql);
	}
	
	function readfromdb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->nome=$rst['nome'];
			$this->fonte=$rst['fonte'];
			$this->atributo=$rst['atributo'];
			$this->tipodados=$rst['tipodados'];
			$this->decisao=$rst['decisao'];
			$this->pergunta=$rst['pergunta'];
			$this->global=$rst['global'];
			$this->descricao=$rst['descricao'];
			$this->condvariante=$rst['condvariante'];
			$this->condregrasvar=$rst['condregrasvar'];
			$this->condproduto=$rst['condproduto'];
			$this->globalproduto=$rst['globalproduto'];
			$this->simbolo=$rst['simbolo'];
			$this->tipo=$rst['tipo'];
			$this->param1=$rst['param1'];
			$this->param2=$rst['param2'];
			$this->param3=$rst['param3'];
			$this->dc=$rst['dc'];
			$this->et=$rst['et'];
			
		}
		return $rc;
	}
	
	function readfromdbComDesc()
	{
		$oMain = $this->oMain;
		$sql="[bdsigip].[dbo].[spsigfeatures] '$oMain->sid', 'getunitext', '0', '$this->nome', '', '', '', '', '', '', '', '', '', '', '', '', '$this->lang'";
		$rs = $oMain->querySQL($sql);
//var_dump($rs);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->unitextdesc=$rst['Descricao'];
		}
		
		//print $this->unitextdesc;
		
		return $rc;
	}
	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$this->oMain->sid;
		$modifnum=0;// not being used
		
		$sql="[bdsigip].[dbo].[spsigfeatures] '$sid','$operation'
		,'$modifnum'
		,'$this->nome'
		,'$this->descricao'
		,'$this->fonte'
		,'$this->atributo'
		,'$this->globalproduto'
		,'$this->et'
		,'$this->simbolo'
		,'$this->tipo'
		,'$this->param1'
		,'$this->param2'
		,'$this->param3'
		,'$this->pergunta'
		,'$this->unitextdesc'
		,'$this->lang'
		";
//var_dump($sql); //die();
		$rs = $oMain->querySQL($sql);
//var_dump($rs);
		$rst=$rs[0];
		$tdesc=$rst['Descricao'];
		return($rst['Erro']);

	}
	
	
}//End of CVarDecisao



class CValCampos
{
//	var $product;    /**  */

	var $campo;
	var $valor;
	var $tipo;
	var $ordem;
	var $dc;
	var $codigo;
	var $newvalue;
	var $unitextdesc; //commercial description
	var $lang;
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class capplications mod
	 */	
	function getHtml($mod)
	{
		//print (urldecode(urlencode("a+b+c"))); die;
		$oMain=$this->oMain;
		//$this->oMain->HTMLBlocks->html('menu', 'content', '');
		$this->readFromRequest();
		$ent='valcamp'; 

//var_dump($_REQUEST);
		
		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$idtgrid='valcampTGrid';
			$html=$oMain->refresh($idtgrid);
			$html.='<script>
					_urlContent2DivLoader("'.$this->oMain->baselink('sigipweb', '_reftreevar').'campo='.$this->campo.'&ppajax=1", "menu");
					</script>';
		}
		
		if ($mod =='delcomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('deleteunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$idtgrid='vccomdescTGrid';
			$html=$oMain->refresh($idtgrid);
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			if($tstatus==0)
			{
				$idpopup='valcampPopup';
				$idtgrid='valcampTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
				
				if($oMain->operation=='refreshtree')
				{				
					//refresh tree
					$html.='<script>
						_urlContent2DivLoader("'.$this->oMain->baselink('sigipweb', '_reftreevar').'campo='.$this->campo.'&ppajax=1", "menu");
						</script>';
				}
			}
			else 
				$mod='xnew_'.$ent;
		}
		
		if ($mod =='insertcomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insertunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$idpopup='vccomdescPopup';
				$idtgrid='vccomdescTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='xnewcomdesc_'.$ent;

		}
		
		
		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			if($tstatus==0)
			{
				$idpopup='valcampPopup';
				$idtgrid='valcampTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xedit_'.$ent;
		}
		
		if ($mod =='updatecomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('updateunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$idpopup='vccomdescPopup';
				$idtgrid='vccomdescTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xeditcomdesc_'.$ent;
		}
		

		if ($mod =='rename_'.$ent)
		{
			$tstatus=$this->storeIntoDB('rename', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$idpopup='valcampPopup';
				$idtgrid='valcampTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xeditvalue_'.$ent;
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			
			
			if($mod =='edit_'.$ent)
				$this->readFromDB();
			
			if(_request('ppajax')!='')
				{

				$id='valcampPopup';
				$title=$oMain->translate('edit_valcamp').' - '.$this->campo.' | '.$this->valor;
				$content=$this->form('update_'.$ent,'xedit_'.$ent);
				$menu=$oMain->translate('valcamp');
				$html=$oMain->popupOpen($id, $title, $content, '', $menu);
				}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('update_'.$ent,'xedit_'.$ent);
			}

		}
		
		if ($mod =='editvalue_'.$ent || $mod =='xeditvalue_'.$ent)
		{
			
			
			if($mod =='editvalue_'.$ent)
				$this->readFromDB();
			
			if(_request('ppajax')!='')
				{

				$id='valcampPopup';
				$title=$oMain->translate('editvalue_valcamp').' - '.$this->campo.' | '.$this->valor;
				$content=$this->form('rename_'.$ent,'xrename_'.$ent);
				$menu=$oMain->translate('valcamp');
				$html=$oMain->popupOpen($id, $title, $content, '', $menu);
				}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('update_'.$ent,'xedit_'.$ent);
			}


		}
		
		
		if ($mod =='editcomdesc_'.$ent || $mod =='xeditcomdesc_'.$ent)
		{
			
			
			if($mod =='editcomdesc_'.$ent)
				$this->readfromdbComDesc();
			
			
			if(_request('ppajax')!='')
				{

				$id='vccomdescPopup';
				$title=$oMain->translate('editcomdesc').' - '.$this->campo.' | '.$this->valor;
				$content=$this->formComDesc('updatecomdesc_'.$ent,'xeditcomdesc_'.$ent);
				$menu=$oMain->translate('valcamp');
				$html=$oMain->popupOpen($id, $title, $content, '', $menu);
				}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('updatecompdesc_'.$ent,'xeditcompdesc_'.$ent);
			}


		}
		
		

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			
			if(_request('ppajax')!='')
				{

				$id='valcampPopup';
				$title=$oMain->translate('new_valcamp');
				$content=$this->form('insert_'.$ent,'xnew_'.$ent);
				$menu=$oMain->translate('valcamp');
				$html=$oMain->popupOpen($id, $title, $content, '', $menu);
				}
				else 
				{
					$oMain->subtitle=$oMain->translate('show_'.$ent);
					$html=$this->form('insert_'.$ent,'xnew_'.$ent);
				}
		}
		
		if ($mod =='newcomdesc_'.$ent or $mod =='xnewcomdesc_'.$ent)
		{
			
			if(_request('ppajax')!='')
				{

				$id='vccomdescPopup';
				$title=$oMain->translate('newcomdesc').' - '.$this->campo.' | '.$this->valor;
				$content=$this->formComDesc('insertcomdesc_'.$ent,'xnewcomdesc_'.$ent);
				$menu=$oMain->translate('valcamp');
				$html=$oMain->popupOpen($id, $title, $content, '', $menu);
				}
				else 
				{
					$oMain->subtitle=$oMain->translate('show_'.$ent);
					$html=$this->form('insert_'.$ent,'xnew_'.$ent);
				}
		}

		
		if ($mod =='list_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showList();
		}
		
		if ($mod =='listcomdesc_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showListComDesc();
		}
		

		return($html);
	}
	
	
	 /**
	  * read class capplications atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->campo=$oMain->GetFromArray('campo',$_REQUEST,'string_trim');
		$this->valor=$oMain->GetFromArray('valor',$_REQUEST,'string_trim');
		$this->tipo=$oMain->GetFromArray('tipo',$_REQUEST,'string_trim');
		$this->ordem=$oMain->GetFromArray('ordem',$_REQUEST,'int');
		$this->dc=$oMain->GetFromArray('dc',$_REQUEST,'string_trim');
		$this->codigo=$oMain->GetFromArray('codigo',$_REQUEST,'string_trim');
		$this->newvalue=$oMain->GetFromArray('newvalue',$_REQUEST,'string_trim');
		$this->unitextdesc=$oMain->GetFromArray('unitextdesc',$_REQUEST,'string_trim');
		$this->lang=$oMain->GetFromArray('lang',$_REQUEST,'string_trim');

	}
	
	
	function form($mod='show_valcamp', $modChange='')
	{
		$oMain=$this->oMain;
		
		$operation='';
		if($this->campo=='')
			$operation='refreshtree';
		
		$formName = 'formvalcamp'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(1);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		$read=TRUE;
		$read2=FALSE;
		
		if($mod=='rename_valcamp')
		{
			$read=FALSE;
			$read2=TRUE;
			$form->hidden('campo', $this->campo);
			$form->hidden('tipo', $this->tipo);
			$form->hidden('valor', $this->valor);
		}
		
		if($this->campo<>'')
		{
			$sql="select distinct RTRIM(campo), campo as tdesc FROM BDSIGIP.dbo.tbQE_ValoresCampos";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('campo', $oMain->translate('campo'), $this->campo, $temp))->blank(true)->readonly($read2)->required(true);
		}
		else
		{
			$form->elementAdd(new _htmlInput('campo', $oMain->translate('campo'), $this->campo))->readonly(FALSE)->required(true);
		}
			
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  FROM dbo.tbcodes C   WHERE (codetype = 'sigipwebalfa2' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('tipo', $oMain->translate('tipo'), $this->tipo, $temp))->blank(true)->readonly($read2)->required(true);
		
		if($mod=='rename_valcamp')
		{
			$form->elementAdd(new _htmlInput('actualvalue', $oMain->translate('actualvalue'), $this->valor))->readonly($read2);
			$form->elementAdd(new _htmlInput('newvalue', $oMain->translate('newvalue'), $this->newvalue))->readonly($read);
		}
		elseif($mod=='insert_valcamp') 
			$form->elementAdd(new _htmlInput('valor', $oMain->translate('valor'), $this->valor))->readonly(FALSE)->required(true);
		else
			$form->elementAdd(new _htmlInput('valor', $oMain->translate('valor'), $this->valor))->readonly(TRUE)->required(true);
		
		
		$form->elementAdd(new _htmlInput('ordem', $oMain->translate('ordem'), $this->ordem))->readonly($read2);

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		//$button->confirm($oMain->translate('confassociate'));
		
		return $form->html().$button->html();
	}
	
	function formComDesc($mod='showcomdesc_valcamp', $modChange='')
	{
		$oMain=$this->oMain;
//var_dump($_REQUEST);
		$operation='';
		$formName = 'formcomdescvc'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(2);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		//$form->hidden('id', $this->id);
		$form->hidden('campo', $this->campo);
		$form->hidden('valor', $this->valor);
//var_dump ($this->lang);		
		$sql="SELECT RTRIM(Codigo), Descricao FROM dbo.tbIdiomas ORDER BY Descricao asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('lang', $oMain->translate('idioma'), $this->lang, $temp))->blank(true)->required(true);
		
		$form->elementAdd(new _htmlTextarea('unitextdesc', $oMain->translate('unitextdesc'), $this->unitextdesc),null,2)->required(true)->maxlength(1000);

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		//$button->confirm($oMain->translate('confassociate'));
		
		return $form->html().$button->html();
	}
	
	
	function showList()
	{
		$oMain=$this->oMain;
		$sql="SELECT campo, valor, tipo, ordem, dc, codigo, '' as img, '' as toperations
			, dbo.translate_code('sigipwebalfa2', tipo, '$oMain->l') as tipodesc
			FROM BDSIGIP.dbo.tbQE_ValoresCampos";
		
		if($this->campo!='')
			{
			$sql .= " where campo='".$this->campo."' ORDER BY ordem"; 
			}
		else
			$sql .= " ORDER BY campo, ordem"; 
		

		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		for ($r = 0; $r < $rc; $r++)
		{
			$rs[$r]['img']='<img src="img/edit3_s.png" title="'.$oMain->translate('valcamp').'"">';
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'editvalue_valcamp', '', 'campo='.urlencode($rs[$r]['campo']).'&valor='.urlencode($rs[$r]['valor']).'&ppajax=1')."', 'script');";
			$linkupdate = '<a href="#" onclick="'.$onCLick.'" title="'.$oMain->translate('editvalue').'"><img src="img/edit4_s.png"></a>';
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_valcamp', '', 'campo='.urlencode($rs[$r]['campo']).'&valor='.urlencode($rs[$r]['valor']).'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'" title="'.$oMain->translate('edit').'"><img src="img/edit_s.png"></a>';
			
			$linkcomdesc=$oMain->stdImglink('listcomdesc_valcamp', '','','campo='.urlencode($rs[$r]['campo']).'&valor='.urlencode($rs[$r]['valor']),'img/translate_s.png','','', $oMain->translate('comdesc'));
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'del_valcamp', '', 'campo='.urlencode($rs[$r]['campo']).'&valor='.urlencode($rs[$r]['valor']).'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['toperations']=$linkcomdesc.' '.$linkupdate.' '.$linkedit.' '.$linkdel;
		}
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
	
		$linknewvardec=$oMain->baseLink('sigipweb', 'new_valcamp', '', 'campo='.$this->campo, '', '');
		$linknewvardec.= '&ppajax=1';
		
		//$linklistcm=$oMain->baseLink('sigipweb', 'listcomdesc_valcamp', '', '&campo='.urlencode($this->campo).'&valor='.$this->valor, '', '');

		$menuObj->addItem('x20', $oMain->translate('new_valcamp'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$linknewvardec.'", "script");');
		//$menuObj->addItem('x30', $oMain->translate('list_comdesc'), _iconSrc('list'), $linklistcm);
		

//var_dump ($rs);
		$table = $oMain->_stdTGrid('valcampTGrid');
		$table->title($oMain->translate('vallist'));
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'list_valcamp', '', 'campo='.$this->campo));
		$table->border(0);
		$table->vals($rs);
		
//		$table->searchable(false);
//		$table->showFixedFooter(false);
		
		$table->column('img')->title('!')->width('2.0em')->searchable(false);
		$table->column('campo')->title($oMain->translate('campo'));
		$table->column('valor')->title($oMain->translate('valor'));
		$table->column('tipodesc')->title($oMain->translate('tipo'))->width('10.0em');
		$table->column('ordem')->title($oMain->translate('ordem'))->width('4.0em');
//		$table->column('dc')->title($oMain->translate('dc'));
//		$table->column('codigo')->title($oMain->translate('codigo'));
		$table->column('toperations')->title('!')->width('9.0em')->searchable(false);
		

		return $table->html();
	}
	
	function showListComDesc()
	{
		$oMain=$this->oMain;
		$sql="[bdsigip].[dbo].[spsigfieldvalues] '$oMain->sid', 'listunitext', '$this->campo', '$this->valor'";
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		$ArrayRst=array();
		$elementos=0;
		for ($r = 0; $r < $rc; $r++)
		{
			//$linkdel=$oMain->stdImglink('delcomdesc_valcamp', '','','campo='.$this->campo.'&valor='.$this->valor.'&lang='.$rs[$r]['Idioma'],'img/delete_s.png','','', $oMain->translate('del_comdesc'), $oMain->translate('del_comdesc'));
			
			$onCLick1 = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'editcomdesc_valcamp', '', 'campo='.urlencode($this->campo).'&valor='.urlencode($this->valor).'&lang='.$rs[$r]['Idioma'].'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick1.'" title="'.$oMain->translate('editcomdesc_valcamp').'"><img src="img/edit_s.png"></a>';
			
			$onCLickdel = "if(confirm('".$oMain->translate('delcomdesc_valcamp')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'delcomdesc_valcamp', '', 'campo='.urlencode($this->campo).'&valor='.urlencode($this->valor).'&lang='.$rs[$r]['Idioma'].'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delcomdesc_valcamp').'"><img src="img/delete_s.png"></a>';

			$ArrayRst[$elementos]['ID']=$rs[$r]['ID'];
			$ArrayRst[$elementos]['Idioma']=$rs[$r]['Idioma'];
			$ArrayRst[$elementos]['Descricao']=$rs[$r]['Descricao'];
			$ArrayRst[$elementos]['NomeIdioma']=$rs[$r]['NomeIdioma'];
			$ArrayRst[$elementos]['toperations']=$linkedit.' '.$linkdel;

			$elementos++;
		}
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		//$linkprod=$oMain->baseLink('products', 'show_products', '', 'tprod='.$this->tprod, '', '');
		
		$linknewcomdesc=$oMain->baseLink('sigipweb', 'newcomdesc_valcamp', '', 'campo='.urlencode($this->campo).'&valor='.urlencode($this->valor), '', '');
		$linknewcomdesc.= '&ppajax=1';
		$linknewcomdesc = '_urlContent2DivLoader("'.$linknewcomdesc.'", "script")';
		
		$linkvalcomp=$oMain->baseLink('sigipweb', 'list_valcamp', '', 'campo='.$this->campo, '', '');
		//$linkvartype.= '&ppajax=1';
		
		//$menuObj->addItem('x10', $oMain->translate('show_products'), _iconSrc('edit'), $linkprod);
		
		//'_urlContent2DivLoader("'.$linknewvartype.'", "script");'
		$menuObj->addItem('x10', $oMain->translate('valcamp'), 'img/back_s.png', $linkvalcomp);
		$menuObj->addItem('x20', $oMain->translate('new_comdesc'), 'img/new_s.png', '', $linknewcomdesc);

		$table = $oMain->_stdTGrid('vccomdescTGrid');
		$table->title($oMain->translate('list_comdesc').' | '.$oMain->translate('valcomp').' - '.$this->campo);
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'listcomdesc_valcamp', '', '&campo='.urlencode($this->campo).'&valor='.urlencode($this->valor)));
		$table->border(0);
		$table->vals($ArrayRst);

		$table->column('Descricao')->title($oMain->translate('desc'))->width('35.0em');
		$table->column('NomeIdioma')->title($oMain->translate('idioma'))->width('15.0em');
		$table->column('toperations')->title('!')->width('3.0em')->searchable(false)->sortable(false);
		

		return $table->html();
	}
	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT campo, valor, tipo, ordem, dc, codigo
			FROM BDSIGIP.dbo.tbQE_ValoresCampos
			WHERE campo='$this->campo' AND valor='$this->valor'";	
//var_dump($sql); die;
		return($sql);
	}
	
	function readfromdb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->campo=$rst['campo'];
			$this->valor=$rst['valor'];
			$this->tipo=$rst['tipo'];
			$this->ordem=$rst['ordem'];
			$this->dc=$rst['dc'];
			$this->codigo=$rst['codigo'];
		}
		return $rc;
	}
	
	
	function readfromdbComDesc()
	{
		$oMain = $this->oMain;
		$sql="[bdsigip].[dbo].[spsigfieldvalues] '$oMain->sid', 'getunitext', '$this->campo', '$this->valor', '', '', '', '', '', '', '$this->lang'";
		$rs = $oMain->querySQL($sql);
//var_dump($rs);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->unitextdesc=$rst['Descricao'];
		}

		return $rc;
	}
	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$this->oMain->sid;
		
		$sql="[bdsigip].[dbo].[spsigfieldvalues] '$sid','$operation'
		,'$this->campo'
		,'$this->valor'
		,'$this->tipo'
		,'$this->ordem'
		,'$this->dc'
		,'$this->codigo'
		,'$this->newvalue'
		,'$this->unitextdesc'
		,'$this->lang'
		";
//var_dump($sql); //die();
		$rs = $oMain->querySQL($sql);
//var_dump($rs);
		$rst=$rs[0];
		$tdesc=$rst['Descricao'];
		return($rst['Erro']);

	}
	
	
	
}//End of CValCampos

?>

