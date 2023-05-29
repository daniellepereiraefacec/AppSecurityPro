<?php
/**
 * @@name	<table description resume>
 * @@author	Pedro Brandão
 * @@version 	07-11-2012 13:56:44
 *
 * Revisions:
 * 2015-06-08	PBrandao    removed validate('float',true) from campo valor @crulevar form
 * 2015-06-08	PSousa\PSilva	R1412_00015: correct problem of button delete condition is not visible
 * 2015-05-12	Paulo Sousa		R1412_00015: use string instead of string_trim in field vartype and vartypedest
 */


class CRule
{
	var $product;    /**  */
	var $tprod; /*prod code*/
	var $variant;
	var $vartype;
	var $tdesc;
	var $codigo;    /**  */
	var $numregra;    /**  */
	var $nivel;    /**  */
	var $tipo_regra;    /**  */
	var $descricao;    /**  */
	var $quantidade;    /**  */
	var $utilizador;    /**  */
	var $utilizadordesc;
	var $data;    /**  */
	var $ordem;    /**  */
	var $var_pergunta;    /**  */
	var $valor_defeito;    /**  */
	var $estado_regra;    /**  */
	var $var_global;    /**  */
	var $instante;    /**  */
	var $dc;    /**  */
	var $visible_op;    /** identify if the rule is visible in proposal mode (1- visible 0-not visible)  */
	var $remarks;    /**  */
	var $unitextdesc;
	var $lang;
	var $codigodest;
	
	var $productdest;
	Var $vartypedest;
	
	var $copyall;

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
	function getHtml(&$mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='rule'; 
	//var_dump($_REQUEST);	
		$idpopup=$ent.'Popup';
		$idtgrid=$ent.'TGrid';
		
		
		if ($mod =='setorder_'.$ent)
		{
			$tstatus=$this->storeIntoDB('setorder', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			$html.='<script>
				_urlContent2DivLoader("'.$this->oMain->baselink('sigipweb', '_reftreeprod').'&product='.$this->product.'&variant='.$this->codigo.'&ppajax=1", "menu");
				</script>';

		}
		
		if ($mod =='copydest_'.$ent)
		{
			
			$toupdt = $this->oMain->GetFromArray('toUpdt', $_REQUEST, 'int');

			$codigodest=$this->codigodest;
			
			$errors = array();
			$msgOk = '';

			foreach($toupdt as $k=>$v)
				{
				$this->numregra=(int) $k;
			//var_dump($this->numregra);
				$this->readFromDb();
				$this->codigodest=$codigodest;
				$tstatus=$this->storeIntoDB('copy', $tdesc);
				if($tstatus!='0') 
					$errors[] = $this->codigo.' - '.$k.' - '.$tdesc;
				else 
					$msgOk = $tdesc;
				}
				
			if(count($errors)>0) 
			{
				$error = implode('<br>', $errors);
				$oMain->stdShowResult(-1, $error);
			} 
			else 
				$oMain->stdShowResult(0, $msgOk);
			
			if($oMain->operation=='product')
				$mod='list_prodrule';
			else
				$mod='list_'.$ent;

		}
		
		if ($mod =='multisel_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			
			$html=$this->getSelection();
			if($html=='')
			{
				if($oMain->operation=='product')
					$mod='list_prodrule';
				else
					$mod='list_'.$ent;
			}
			else
			{
				$toupdt = $this->oMain->GetFromArray('toUpdt', $_REQUEST, 'int');
				//var_dump($toupdt);
				$numregras='';
				foreach($toupdt as $k=>$v)
				{
					if($numregras=='')
						$numregras="'$k'";
					else
						$numregras.=",'$k'";
				}
				//var_dump($numregra);
				
				if($oMain->operation=='product')
				{	
					$o = new CprodRule($oMain);
					$o->product=$this->product;
					$o->instante=$this->instante;
					$lista = $o->showlist($numregras);
				}
				else
				{
					$lista = $this->showlist($numregras);	
				}
				$spl = new _splitter('');
				$spl->orientation('v');
				$spl->add('form')->content($html)->dim('0');
				$spl->add('sep')->content(' ')->dim('5px');
				$spl->add('list')->content($lista)->dim('100%');
				$html = $spl->html();
			}

		}
				
		
		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$html=$oMain->refresh($idtgrid);
		}

		if ($mod =='insert_'.$ent)
		{
			
			if($this->ordem==0)
				$tstatus=$this->storeIntoDB('insert', $tdesc);
			else
				$tstatus=$this->storeIntoDB('insertintermed', $tdesc);
			
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
				$html.='<script>
					_urlContent2DivLoader("'.$this->oMain->baselink('sigipweb', '_reftreeprod').'&product='.$this->product.'&variant='.$this->codigo.'&ppajax=1", "menu");
					</script>';
			}
			else 
				$mod='xnew_'.$ent;
		}

		if ($mod =='update_'.$ent)
		{	
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				//$html=$oMain->closeandrefresh($idpopup, $idtgrid);
				$mod='show_'.$ent;
			}
			else
				$mod='xedit_'.$ent;
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			
			if($mod =='edit_'.$ent)
			{	
				$this->readFromDb(); 
			}
			$html=$this->dashboard('edit');

		}
		
		
		if ($mod =='editorder_'.$ent)
		{
			
			$this->readFromDb(); 

			
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('setorder_rule').' - '.$this->codigo;
				$content=$this->formOrder();
				$footer='';
				$menu='';
				
				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->formOrder();
			}
		}
		

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{

			if(_request('ppajax')!='')
			{
					$title=$oMain->translate('new_rule').' - '.$this->codigo;
					$content=$this->form('insert_'.$ent,'xnew_'.$ent);
					$footer='';
					$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
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

		if ($mod =='show_'.$ent)
		{
			$this->readFromDB();
			$html=$this->dashboard('show');
		}
		
		$idpopup='vccomdescPopup';
		$idtgrid='vccomdescTGrid';
		
		if ($mod =='delcomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('deleteunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$html=$oMain->refresh($idtgrid);
			$html.='<script>
					_urlContent2DivLoader("'.$this->oMain->baselink('sigipweb', '_reftreeprod').'&product='.$this->product.'&variant='.$this->variant.'&ppajax=1", "menu");
					</script>';
		}
		
		if ($mod =='insertcomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insertunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
				$html.='<script>
					_urlContent2DivLoader("'.$this->oMain->baselink('sigipweb', '_reftreeprod').'&product='.$this->product.'&variant='.$this->variant.'&ppajax=1", "menu");
					</script>';
			}
			else 
				$mod='xnewcomdesc_'.$ent;

		}
		
		if ($mod =='updatecomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('updateunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
				$html.='<script>
					_urlContent2DivLoader("'.$this->oMain->baselink('sigipweb', '_reftreeprod').'&product='.$this->product.'&variant='.$this->variant.'&ppajax=1", "menu");
					</script>';
			}
			else
				$mod='xeditcomdesc_'.$ent;
		}
		
		if ($mod =='editcomdesc_'.$ent || $mod =='xeditcomdesc_'.$ent)
		{
			
			
			if($mod =='editcomdesc_'.$ent)
				$this->readfromdbComDesc();
			
			
			if(_request('ppajax')!='')
				{			
				//$id='vccomdescPopup';
				$title=$oMain->translate('editcomdesc').' - '.$this->codigo.' | '.$this->numregra;
				$content=$this->formComDesc('updatecomdesc_'.$ent,'xeditcomdesc_'.$ent);
				$menu=$oMain->translate('comdesc_rule');
				$html=$oMain->popupOpen($idpopup, $title, $content, '', $menu);
				}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('updatecompdesc_'.$ent,'xeditcompdesc_'.$ent);
			}


		}
		
		if ($mod =='newcomdesc_'.$ent or $mod =='xnewcomdesc_'.$ent)
		{
			
			if(_request('ppajax')!='')
				{
				
				//$id='vccomdescPopup';
				$title=$oMain->translate('comdesc').' - '.$this->codigo.' | '.$this->numregra;
				$content=$this->formComDesc('insertcomdesc_'.$ent,'xnewcomdesc_'.$ent);
				$menu=$oMain->translate('comdesc_rule');
				$html=$oMain->popupOpen($idpopup, $title, $content, '', $menu);
				}
				else 
				{
					$oMain->subtitle=$oMain->translate('show_'.$ent);
					$html=$this->form('insert_'.$ent,'xnew_'.$ent);
				}
		}
		
		if ($mod =='listcomdesc_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showListComDesc($oMain->operation);
		}
		
		

		return($html);
	}
	
	
	function getSelection()
	{
		$oMain=$this->oMain;
		//$toupdt = Array();
    	$toupdt = $this->oMain->GetFromArray('toUpdt', $_REQUEST, 'int');
		$operation=$oMain->GetFromArray('globalAction',$_REQUEST,'int');
//print_r($_REQUEST); die();
//var_dump($_REQUEST); die();
		if(empty($toupdt))
		{
			$oMain->message('warning','',$oMain->translate('nochk'),20);
			return('');
		}
		else
		{	
			$this->product=$oMain->GetFromArray('product',$_REQUEST,'string_trim');
			$this->codigo=$oMain->GetFromArray('codigo',$_REQUEST,'string_trim');
			$this->variant=$oMain->GetFromArray('variant',$_REQUEST,'string_trim');
			
			if($operation==1)
				$this->estado_regra='Produção';
			else if ($operation==2)
				$this->estado_regra='Protótipo';
			else if ($operation==3)
				$this->estado_regra='Obsoleto';
			else if ($operation==4)
				$this->visible_op='1';
			else if ($operation==5)
				$this->visible_op='0';
			else if ($operation==6)
				$this->instante='I';
			else if ($operation==7)
				$this->instante='F';

			
			$errors = array();
			$msgOk = '';
			
			switch($operation)
				{
				case 1:
				case 2:
				case 3:
					foreach($toupdt as $k=>$v)
						{
						$this->numregra=(int) $k;	
						$tstatus=$this->storeIntoDB('setstatus', $tdesc);
						if($tstatus!='0') 
							$errors[] = $this->codigo.' - '.$k.' - '.$tdesc;
						else 
							$msgOk = $tdesc;
						}
					break;
				case 4:
				case 5:
					foreach($toupdt as $k=>$v)
						{
						$this->numregra=(int) $k;	
						$tstatus=$this->storeIntoDB('setvisible', $tdesc);
						if($tstatus!='0') 
							$errors[] = $this->codigo.' - '.$k.' - '.$tdesc;
						else 
							$msgOk = $tdesc;
						}
					break;
				case 6:
				case 7:
					foreach($toupdt as $k=>$v)
						{
						$this->numregra=(int) $k;	
						$tstatus=$this->storeIntoDB('setinstant', $tdesc);
						if($tstatus!='0') 
							$errors[] = $this->codigo.' - '.$k.' - '.$tdesc;
						else 
							$msgOk = $tdesc;
						}
					
					break;
				case 8:
					$html=$this->formCopy();
					return ($html);
				}
				
			if(count($errors)>0) 
			{
				$error = implode('<br>', $errors);
				$oMain->stdShowResult(-1, $error);
			} 
			else 
				$oMain->stdShowResult(0, $msgOk);
			
		}
		
	return '';
	}
	
	 /**
	  * read class capplications atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->product=$oMain->GetFromArray('product',$_REQUEST,'string_trim');
		$this->tprod=$oMain->GetFromArray('tprod',$_REQUEST,'int');
		$this->variant=$oMain->GetFromArray('variant',$_REQUEST,'string_trim');
		$this->vartype=$oMain->GetFromArray('vartype',$_REQUEST,'string');
		$this->tdesc=$oMain->GetFromArray('tdesc',$_REQUEST,'string_trim');
		
		$this->codigo=$oMain->GetFromArray('codigo',$_REQUEST,'string_trim');
		$this->numregra=$oMain->GetFromArray('numregra',$_REQUEST,'int');
		$this->nivel=$oMain->GetFromArray('nivel',$_REQUEST,'string_trim');
		$this->tipo_regra=$oMain->GetFromArray('tipo_regra',$_REQUEST,'string_trim');
		$this->descricao=$oMain->GetFromArray('descricao',$_REQUEST,'string_trim');
		$this->quantidade=$oMain->GetFromArray('quantidade',$_REQUEST,'int');
		$this->utilizador=$oMain->GetFromArray('utilizador',$_REQUEST,'string_trim');
		$this->data=$oMain->GetFromArray('data',$_REQUEST,'date');
		$this->ordem=$oMain->GetFromArray('ordem',$_REQUEST,'int');
		$this->var_pergunta=$oMain->GetFromArray('var_pergunta',$_REQUEST,'string_trim');
		$this->valor_defeito=$oMain->GetFromArray('valor_defeito',$_REQUEST,'string_trim');
		$this->estado_regra=$oMain->GetFromArray('estado_regra',$_REQUEST,'string_trim');
		$this->var_global=$oMain->GetFromArray('var_global',$_REQUEST,'string_trim');
		$this->instante=$oMain->GetFromArray('instante',$_REQUEST,'string_trim');
		$this->dc=$oMain->GetFromArray('dc',$_REQUEST,'string_trim');
		$this->visible_op=$oMain->GetFromArray('visible_op',$_REQUEST,'string_trim');
		$this->remarks=$oMain->GetFromArray('remarks',$_REQUEST,'string_trim');
		$this->unitextdesc=$oMain->GetFromArray('unitextdesc',$_REQUEST,'string_trim');
		$this->lang=$oMain->GetFromArray('lang',$_REQUEST,'string_trim');
		
		$this->codigodest=$oMain->GetFromArray('codigodest',$_REQUEST,'string_trim');
		
		$this->productdest=$oMain->GetFromArray('productdest',$_REQUEST,'string_trim');
		$this->vartypedest=$oMain->GetFromArray('vartypedest',$_REQUEST,'string');
		
		$this->copyall=$oMain->GetFromArray('copyall',$_REQUEST,'int');
		
	
	}
	
	
	function form($mod='show_rule', $modChange='')
	{
		$oMain=$this->oMain;
		
		$operation='';
		$formName = 'formrule';
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(2);
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		if($mod=='insert_rule')
			$form->ajax(true)->ajaxDiv('script');
		else
			$form->ajax(true)->ajaxDiv('main');
		
		if($mod=='show_rule') 
			$form->readOnly(true);
		
		$read=FALSE;
		if($mod=='insert_rule')
		{
			$this->estado_regra='Protótipo';
			$this->nivel='Variante';
			$this->quantidade=1;
			$read=TRUE;
		}
		
		$form->hidden('product', $this->product);
		$form->hidden('codigo', $this->codigo);
		$form->hidden('nivel', $this->nivel);
		$form->hidden('dc', $this->dc);
		$form->hidden('instante', $this->instante);
		$form->hidden('visible_op', $this->visible_op);
		$form->hidden('quantidade', $this->quantidade);
		$form->hidden('numregra', $this->numregra);
		
		
		
		
//		if($mod<>'insert_rule')
//		{
//			$form->elementAdd(new _htmlInput('numregra', $oMain->translate('numregra'), $this->numregra))->readonly(TRUE)->required(true);
//			$form->elementAdd(new _htmlInput('ordem', $oMain->translate('ordem'), $this->ordem))->readonly(TRUE)->required(true);
//		}
		
		$sql="SELECT regra as codigo, regra FROM bdsigip.dbo.tbQE_Tipos_Regra where Variante='s' order by Ordem asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$select=$form->elementAdd(new _htmlSelect('tipo_regra', $oMain->translate('type'), $this->tipo_regra, $temp))->blank(true)->required(true);
		$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
			FROM dbo.tbcodes C  
			WHERE (codetype = 'sigipwebrulstat' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('estado_regra', $oMain->translate('tstatus'), $this->estado_regra, $temp))->blank(true)->required(true);
		
		
		if($this->tipo_regra=='Pergunta ao Utilizador (Sim/Não)?')
		{
			$sql="SELECT Nome, Nome as tdesc  FROM bdsigip.dbo.tbQE_VariaveisDecisao Where Pergunta='S' order by Nome";
			$temp = $oMain->qSQL()->getDI($sql);
			$select=$form->elementAdd(new _htmlSelect('var_pergunta', $oMain->translate('var_pergunta'), $this->var_pergunta, $temp),null,2)->blank(true)->required(true);
			$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
			
			
			if(($mod=='insert_rule' or $mod=='update_rule') and $this->var_pergunta<>'')
			{
				$sql="SELECT fonte, atributo FROM bdsigip.dbo.tbQE_VariaveisDecisao where Nome='$this->var_pergunta'";
				$rs = $oMain->querySQL($sql);

				$fonte=$rs[0]['fonte'];
				$atributo=$rs[0]['atributo'];
			}
			
		
			if($this->var_pergunta=='Personalizar')
				$sql="SELECT Valor as Codigo, Valor as Descricao
					FROM bdsigip.dbo.TBQE_Valores_Pergunta
					Where Codigo='$this->codigo' and NumRegra='$this->numregra' 
					order by Valor";
			else
//				$sql="SELECT Valor as Codigo, Valor AS Descricao
//					from bdsigip.dbo.tbQE_ValoresCampos 
//					where Campo='$atributo'
//					order by Ordem asc";
				$sql="exec bdsigip.dbo.spsigfeaturevalues '$oMain->sid', 'list', '$this->var_pergunta', '$this->product', '$this->codigo', '$this->numregra'";


			$temp = $oMain->qSQL()->keyDesc('Descricao')->getDI($sql);
			$form->elementAdd(new _htmlSelect('valor_defeito', $oMain->translate('valor_defeito'), $this->valor_defeito, $temp))->blank(true);
			
			
		}
		

		if($this->tipo_regra=='Pergunta ao Utilizador (Sim/Não)?' OR $this->tipo_regra=='globalart')
		{
			$sql="SELECT Nome, Nome as tdesc  FROM  bdsigip.dbo.tbQE_VariaveisDecisao Where [Global]='S' order by Nome";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('var_global', $oMain->translate('var_global'), $this->var_global, $temp))->blank(true);
		}

		
		$form->elementAdd(new _htmlInput('descricao', $oMain->translate('descricao'), $this->descricao),null,2)->required(true);
		$form->elementAdd(new _htmlInput('remarks', $oMain->translate('remarks'), $this->remarks),null,2);
		
		if($mod=='insert_rule')
		{
			$form->elementAdd(new _htmlFieldsGroup('t1', $this->oMain->translate('intermedia')));

			$form->elementAdd(new _htmlInput('ordem', $oMain->translate('ordem'), $this->ordem));
		}
		else
		{
			$form->hidden('ordem', $this->ordem);
		}
		
//		if($mod=='show_rule')
//		{
//			$form->elementAdd(new _htmlInput('utilizador', $oMain->translate('utilizador'), $this->utilizadordesc));
//			$form->elementAdd(new _htmlInput('data', $oMain->translate('data'), $oMain->formatdate($this->data)));
//		}

		
		if($mod=='insert_rule')
		{
			$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
			//$button->confirm($oMain->translate('confassociate'));
		
			return $form->html().$button->html();
		}
		else
			return $form->html();
	}
	
	function formOrder()
	{
		$oMain=$this->oMain;
		
		$operation=$oMain->operation;
		$mod='setorder_rule';
		$formName = 'formrule'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(1);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		
		$form->hidden('product', $this->product);
//		$form->hidden('numregra', $this->numregra);

		$form->elementAdd(new _htmlInput('codigo', $oMain->translate('codigo'), $this->codigo))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('numregra', $oMain->translate('numregra'), $this->numregra))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('descricao', $oMain->translate('descricao'), $this->descricao))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('ordem', $oMain->translate('ordem'), $this->ordem))->required(true);

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		$button->confirm($oMain->translate('conf'));

		return $form->html().$button->html();
		
	}
	
	
	function formComDesc($mod='showcomdesc_rule', $modChange='')
	{
		$oMain=$this->oMain;
//var_dump($_REQUEST);
		$operation=$oMain->operation;
		$formName = 'formcomdescrule'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(2);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		//$form->hidden('id', $this->id);
		$form->hidden('codigo', $this->codigo);
		$form->hidden('numregra', $this->numregra);
		$form->hidden('variant', $this->variant);
		$form->hidden('product', $this->product);
//var_dump ($this->lang);		
		$sql="SELECT RTRIM(Codigo), Descricao FROM dbo.tbIdiomas ORDER BY Descricao asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('lang', $oMain->translate('idioma'), $this->lang, $temp))->blank(true)->required(true);
		
		$form->elementAdd(new _htmlTextarea('unitextdesc', $oMain->translate('unitextdesc'), $this->unitextdesc),null,2)->required(true)->maxlength(1000);

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		//$button->confirm($oMain->translate('confassociate'));
		
		return $form->html().$button->html();
	}
	
	function formCopy()
	{
		$oMain=$this->oMain;
//var_dump($_REQUEST);
		//$operation='';
		$formName = 'frmcopyrule';
		$modChange='multisel_rule';
		$form = $oMain->_stdForm($formName, 'copydest_rule', $oMain->operation);
		$form->cols(1);
		$form->ajax(true)->ajaxDiv('main');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		$form->hidden('codigo', $this->codigo);
		$form->hidden('globalAction', $oMain->GetFromArray('globalAction',$_REQUEST,'int'));
		
		$toupdt = $this->oMain->GetFromArray('toUpdt', $_REQUEST, 'int');		
		foreach($toupdt as $k=>$v)
			$form->hidden('toUpdt['.$k.']', $v);
		
		$form->hidden('product', $this->product);
		
		
		
		if($oMain->operation=='product')
		{
			
			$form->hidden('instante', $this->instante);
			
			$this->codigodest=$this->product;
			
			$sql="SELECT Produto, Produto AS tdesc FROM BDSIGIP.dbo.tbQE_Produtos WHERE EstadoProd='1' ORDER BY Produto";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('codigodest', $oMain->translate('product'), $this->codigodest, $temp))->blank(true)->required(true);
		}
		else
		{	
			$form->hidden('variant', $this->variant);
			$form->hidden('vartype', $this->vartype);
			
			if($this->productdest=='')
				$this->productdest=$this->product;
			
			$sql="SELECT Produto, Produto AS tdesc FROM BDSIGIP.dbo.tbQE_Produtos WHERE EstadoProd='1' ORDER BY Produto";
			$temp = $oMain->qSQL()->getDI($sql);
			$select=$form->elementAdd(new _htmlSelect('productdest', $oMain->translate('product'), $this->productdest, $temp))->blank(true)->required(true);
			$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
			
			$sql="SELECT tipo, tipo as tdesc FROM BDSIGIP.DBO.tbQE_Tipos WHERE Produto='$this->productdest' Order by tdesc";
			$temp = $oMain->qSQL()->getDI($sql);
			$select=$form->elementAdd(new _htmlSelect('vartypedest', $oMain->translate('vartype'), $this->vartypedest, $temp))->blank(true)->required(true);
			$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
			
			$sql="SELECT Desenho,(Descricao+' | '+Desenho) as descricao FROM bdsigip.dbo.tbQE_Variantes where produto='$this->productdest' and TipoVar='$this->vartypedest'";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('codigodest', $oMain->translate('variant'), $this->codigodest, $temp))->blank(true)->required(true);
		}
		
		
		$form->elementAdd(new _htmlCheck('copyall', $oMain->translate('copyall'), 1, 1))->label('')->noLayout(true);
		
		
		$button=(_button('', $oMain->translate('copy'),  _iconSrc('blueprints'), '', '_formSubmit("'.$formName.'");'));
		$button->confirm($oMain->translate('confirm'));
		
		return $form->html().$button->html();
	}
	
	
	function dashboard($operation)
	{
		$oMain=$this->oMain;
		
//var_dump($operation); die;	
		
		if($operation=='show')
		{
			$mod='show_rule';
			$mod2='';
		}
		if($operation=='edit')
		{
			$mod='update_rule';
			$mod2='xedit_rule';
		}

		$ocond = new CRuleCond($oMain);
		$ocond->numregra=$this->numregra;
		$ocond->codigo=$this->codigo;
		$ocond->product=$this->product;
		
		$conds=$ocond->showList();
		
		$equips='';
		if($this->tipo_regra=='Seleccionar equipamento a partir de variáveis' OR $this->tipo_regra=='Obrigatório seleccionar equipamentos' OR $this->tipo_regra=='Excluir equipamento a partir de variáveis')
		{
			$oequip = new CRulEquip($oMain);
			$oequip->numregra=$this->numregra;
			$oequip->codigo=$this->codigo;
			$equips=$oequip->showList();
		}
		
		$vars='';
		if($this->tipo_regra=='Atribuir valores a variáveis da variante' OR $this->tipo_regra=='globalart')
		{
			$ovar = new CRuleVar($oMain);
			$ovar->numregra=$this->numregra;
			$ovar->codigo=$this->codigo;
			//$ovar->variant=$this->codigo;
			$vars=$ovar->showList($this->tipo_regra);
		}
		
		$tasks='';
		if($this->tipo_regra=='Criar Tarefas para a solução')
		{
			$otask = new CRuleTask($oMain);
			$otask->numregra=$this->numregra;
			$otask->codigo=$this->codigo;
			$tasks=$otask->showList();
		}
		
		$rulequest='';
		if(trim($this->tipo_regra)=='Pergunta ao Utilizador (Sim/Não)?')
		{
			$sql="SELECT var_pergunta FROM bdsigip.dbo.tbqe_regras WHERE codigo='$this->codigo' and numregra='$this->numregra'";
			$rs = $oMain->querySQL($sql);
			
			$varpergunta=$rs[0]['var_pergunta'];
			
			if(trim($varpergunta)=='Personalizar')
			{
				$orulequest = new CRuleQuestion($oMain);
				$orulequest->numregra=$this->numregra;
				$orulequest->codigo=$this->codigo;
				$rulequest=$orulequest->showList();
			}
		}
		
		
		$spltop= new _splitter('');
		$spltop->orientation('v');
		$spltop->add('t21', $this->form($mod,$mod2), '100%', false, true);
		//$spltop->add('t22', $splbottom->html(), '100%', false, true);
		
		$onCLickback = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'list_rule', '', '&product='.$this->product.'&variant='.$this->variant.'&ppajax=1')."', 'main');";
		$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_rule', '', 'codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&variant='.$this->variant.'&vartype='.$this->vartype.'&ppajax=1')."', 'main');";		
		$onCLickcdesc = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'listcomdesc_rule', '', 'codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&variant='.urlencode($this->variant).'&vartype='.$this->vartype.'&product='.urlencode($this->product).'&ppajax=1&operation=dashboard')."', 'main');";

		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$menuObj->addItem('x10', $oMain->translate('operations'), _iconSrc('efa_edit'), '', '');
			$menuObj->item('x10')->addItem('x11', $oMain->translate('edit'), _iconSrc('efa_edit'), '', $onCLick);
			$menuObj->item('x10')->addItem('x12', $oMain->translate('comdesc'), 'img/translate_s.png', '', $onCLickcdesc);
		$menuObj->addItem('x15', $oMain->translate('list_rule'), 'img/rules_s.png', '', $onCLickback);
		
		$window = new _window('');
		$dashtitle=$oMain->translate('ruledashboard');
		$window->icon('img/efasst01_x.png');
		//$window->title($dashtitle.' - '.$this->codigo.' | Num:'.$this->numregra.' - Ord:'.$this->ordem.' | '.$this->descricao);
		$window->title($dashtitle.' - '.$this->codigo.' | Num:'.$this->numregra.' - Ord:'.$this->ordem.' | '.$oMain->formatdate($this->data).' - '.$this->utilizadordesc);
		$window->content($spltop->html());
		$window->menu($menuObj->html());
		if($operation=='edit')
		{
			$button=_button('', $oMain->translate('save'),  _iconSrc('save'), '', '_formSubmit("formrule"); ')->html();
			$button2=_button('', $oMain->translate('cancel'),  _iconSrc('cancel'), '', '_urlContent2DivLoader("'.$this->oMain->baseLink('sigipweb', 'show_rule').'&product='.$this->product.'&tprod='.$this->tprod.'&vartype='.$this->vartype.'&variant='.$this->variant.'&codigo='.$this->codigo.'&numregra='.$this->numregra.'&ppajax=1", "main"); ')->html();
			$window->footer($button.' '.$button2);
		}
		
		if($equips<>'')
			$optable=$equips;
		if($vars<>'')
			$optable=$vars;
		if($tasks<>'')
			$optable=$tasks;
		if($rulequest<>'')
			$optable=$rulequest;
		
		
		$splbottom= new _splitter('');
		$splbottom->orientation('h');
		$splbottom->add('t10', $conds, '50%');
		$splbottom->add('t11', ' ', '10px');
		$splbottom->add('t12', $optable, '50%');
		
//		if($this->tipo_regra=='Obrigatório seleccionar equipamentos' OR $this->tipo_regra=='Atribuir valores a variáveis da variante' OR $this->tipo_regra=='Seleccionar equipamento a partir de variáveis')
//			$y="10%";
//		else
//			$y="25%";
		
		$splmain= new _splitter('');
		$splmain->orientation('v');
		$splmain->add('t20', $window->html(), '');
		$splmain->add('t21', $splbottom->html(), '50%');
		
		
		return $splmain->html();
	}
	
	
	function showList($selection='')
	{
		$oMain=$this->oMain;
		
		if($selection<>'')
			$cond="WHERE (REG.Codigo = '$this->variant') AND NumRegra IN ($selection)";
		else
			$cond="WHERE (REG.Codigo = '$this->variant')";
		
		if($oMain->operation=='excel')
			$sql="SELECT REG.Codigo, REG.NumRegra, REG.Nivel, REG.Tipo_Regra, REG.Descricao, REG.Quantidade, REG.Utilizador, REG.Data, REG.Ordem
				, REG.Var_Pergunta, REG.Valor_Defeito, REG.Estado_Regra, REG.Var_Global, REG.DC, REG.visible_op, REG.remarks 
				,(SELECT TOP 1 username FROM tbusers WHERE userid=REG.utilizador) AS Username
				, bdsigip.dbo.qe_translate_unitext(REG.DC,'$oMain->l') as dcdesc
			FROM bdsigip.dbo.tbQE_Regras REG
			$cond
			ORDER BY REG.Ordem ASC";
		else
			$sql="SELECT '' AS img, '' AS light, REG.Codigo, REG.NumRegra, REG.Nivel, REG.Tipo_Regra, REG.Descricao, REG.Quantidade, REG.Utilizador, REG.Data, REG.Ordem
				, REG.Var_Pergunta, REG.Valor_Defeito, REG.Estado_Regra, REG.Var_Global, REG.Instante, REG.DC, REG.visible_op, REG.remarks 
				,(SELECT TOP 1 username FROM tbusers WHERE userid=REG.utilizador) AS Username, '' AS toperations, '' as toUpdt
				, bdsigip.dbo.qe_translate_unitext(REG.DC,'$oMain->l') as dcdesc
			FROM bdsigip.dbo.tbQE_Regras REG
			$cond
			ORDER BY REG.Ordem ASC";
//var_dump($sql); die;
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		
		if($oMain->operation=='excel')
		{
			require_once('cexpExcell.php');
			$excel = new expToExcell();
			$excel->conn($oMain->consql)->sql($sql)->name('ListVarRules')->maxrows(200000)->download();
		}
//		$ArrayRst=array();
//		$elementos=0;
		for ($r = 0; $r < $rc; $r++)
		{
			$tipo=$rs[$r]['Tipo_Regra'];
			$visible=$rs[$r]['visible_op'];
			$estado=$rs[$r]['Estado_Regra'];
			$username=$rs[$r]['Username'];
			$descricao=$rs[$r]['Descricao'];
			$dcdesc=$rs[$r]['dcdesc'];
			$dc=$rs[$r]['DC'];
			$user=$rs[$r]['Username'].' - '.$rs[$r]['Utilizador'];
//print $dc;		
			if($dc=='' OR $dcdesc=='Translation unavailable: ')
				$rs[$r]['dcdesc']=$descricao;
			
			//$rs[$r]['Username']= $username.' - '.$rs[$r]['Utilizador'];
			$rs[$r]['Username']= '<p title="'.$user.'">'.$username;
			//<p title="Free Web tutorials">W3Schools.com</p>
			
			$onCLickshow = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'show_rule', '', 'product='.$this->product.'&vartype='.$this->vartype.'&variant='.$this->variant.'&codigo='.$rs[$r]['Codigo'].'&numregra='.$rs[$r]['NumRegra'].'&ppajax=1')."', 'main');";
			$linkshow = '<a href="#" onclick="'.$onCLickshow.'" title="'.$rs[$r]['dcdesc'].'">'.$rs[$r]['dcdesc'].'</a>';
			
			//$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['dcdesc']=$linkshow;

			
			if(trim($tipo)=='Pergunta ao Utilizador (Sim/Não)?')
				$rs[$r]['img']='<img src="img/rules_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Seleccionar equipamento a partir de variáveis')
				$rs[$r]['img']='<img src="img/rulev_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Atribuir valores a variáveis da variante')
				$rs[$r]['img']='<img src="img/ruleav_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Criar Tarefas para a solução')
				$rs[$r]['img']='<img src="img/rulecreate_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Obrigatório seleccionar equipamentos')
				$rs[$r]['img']='<img src="img/rulequip_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='globalart')
				$rs[$r]['img']='<img src="img/rules_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Excluir equipamento a partir de variáveis')
				$rs[$r]['img']='<img src="img/ruleex_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Seleccionar esquema unifilar')
				$rs[$r]['img']='<img src="img/rules_s.png" title="'.$oMain->translate($tipo).'">';

			
//			if(trim($visible)==1)
//			{
//				$rs[$r]['light']='<img src="img/procvis_s.png" title="'.$oMain->translate('propvisib').'">';
//			}
//			else
//			{
//				$rs[$r]['light']='<img src="img/procnotvis_s.png" title="'.$oMain->translate('propinvisib').'">';
//			}
			
			
			if(trim($estado)=='Produção')
			{
				if(trim($visible)==1)
					$rs[$r]['Estado_Regra']='<img src="img/ruleprod_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procvis_s.png" title="'.$oMain->translate('propvisib').'">';
				else
					$rs[$r]['Estado_Regra']='<img src="img/ruleprod_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procnotvis_s.png" title="'.$oMain->translate('propinvisib').'">';
			}
			elseif(trim($estado)=='Protótipo')
			{
				if(trim($visible)==1)
					$rs[$r]['Estado_Regra']='<img src="img/mandatory_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procvis_s.png" title="'.$oMain->translate('propvisib').'">';
				else
					$rs[$r]['Estado_Regra']='<img src="img/mandatory_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procnotvis_s.png" title="'.$oMain->translate('propinvisib').'">';
			}
			else
			{
				if(trim($visible)==1)
					$rs[$r]['Estado_Regra']='<img src="img/ruleobs_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procvis_s.png" title="'.$oMain->translate('propvisib').'">';
				else
					$rs[$r]['Estado_Regra']='<img src="img/ruleobs_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procnotvis_s.png" title="'.$oMain->translate('propinvisib').'">';
			}
			
			
			$onCLickcdesc = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'listcomdesc_rule', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['NumRegra'].'&variant='.urlencode($this->variant).'&vartype='.$this->vartype.'&product='.urlencode($this->product).'&ppajax=1')."', 'main');";
			$linkcomdesc = '<a href="#" onclick="'.$onCLickcdesc.'" title="'.$oMain->translate('comdesc').'"><img src="img/translate_s.png"></a>';
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_rule', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['NumRegra'].'&variant='.urlencode($this->variant).'&vartype='.$this->vartype.'&ppajax=1')."', 'main');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'" title="'.$oMain->translate('edit').'"><img src="img/edit_s.png"></a>';
			
			$onCLickorder = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'editorder_rule', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['NumRegra'].'&variant='.urlencode($this->variant).'&vartype='.$this->vartype.'&product='.urlencode($this->product).'&ppajax=1')."', 'script');";
			$linkorder = '<a href="#" onclick="'.$onCLickorder.'" title="'.$oMain->translate('editorder').'"><img src="img/order_s.png"></a>';
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'del_rule', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['NumRegra'].'&variant='.urlencode($this->variant).'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['toperations']=$linkorder.' '.$linkcomdesc.' '.$linkedit.' '.$linkdel;
		}
		
		if($selection=='')
		{
			$menuObj = new _menuMaker('');
			$menuObj->create('mlevel');
			$menuObj->menu->cBackground(false);

			$linknewvarrule=$oMain->baseLink('sigipweb', 'new_rule', '', 'product='.$this->product.'&codigo='.$this->variant.'&vartype='.$this->vartype, '', '');
			$linknewvarrule.= '&ppajax=1';

			$menuObj->addItem('x10', $oMain->translate('new_rule'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$linknewvarrule.'", "script");');
		}

//var_dump ($rs);
		$table = $oMain->_stdTGrid('ruleTGrid');
		$table->title($oMain->translate('list_varrule').' | '.$oMain->translate('product').' - '.$this->product.' -> '.$oMain->translate('vartype').' - '.$this->vartype.' -> '.$oMain->translate('variant').' - '.$this->variant);
		if($selection=='')
			$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'list_rule', '', '&product='.$this->product.'&variant='.$this->variant));
//		$table->lineOnClick('_urlContent2DivLoader("'.$this->oMain->baseLink('sigipweb', 'show_rule').'&product='.$this->product.'&variant='.$this->variant.'&codigo=§§Codigo§§&numregra=§§NumRegra§§&ppajax=1", "main");');
		$table->border(0);
		$table->vals($rs);
		$table->exportExcellLink($oMain->baseLink('sigipweb', 'list_rule', '', '&product='.$this->product.'&variant='.$this->variant.'&operation=excel'));
		$table->exportExcell(true);
		
		if($selection<>'')
		{
			$table->searchable(false);
			$table->showFixedFooter(false);
		}
		
		if($selection=='')
		{
			$op1=$oMain->translate('prod');
			$op2=$oMain->translate('prot');
			$op3=$oMain->translate('obsolete');
			$op4=$oMain->translate('visprop');
			$op5=$oMain->translate('notvisprop');
			$op6=$oMain->translate('copyrule');
			//$op8=$oMain->translate('copyruleotherprod');

			$table->showGlobalActions(true);
			$table->globalActions(array(1=>$op1, 2=>$op2, 3=>$op3, 4=>$op4, 5=>$op5, 8=>$op6));
			$title=$oMain->translate('save');
			$table->globalActionsText($title);

			$table->uid('§§NumRegra§§');
			$table->form->hidden('mod', 'multisel_rule');
			$table->form->hidden('product', $this->product);
			$table->form->hidden('codigo', $this->variant);
			$table->form->hidden('variant', $this->variant);
			$table->form->hidden('vartype', $this->vartype);
	//		var_dump($rs);
	//		$table->searchable(false);
	//		$table->showFixedFooter(false);

			$table->column('toUpdt')->title('')->width('2.0em')->editable(true)->editType('checkbox')->searchable(false)->sortable(FALSE);
		}
		$table->column('img')->title('!')->width('2.0em')->searchable(false)->sortable(false);
		$table->column('Ordem')->title($oMain->translate('order'))->width('3.0em');
		$table->column('NumRegra')->title($oMain->translate('numregra'))->width('3.0em');
		//$table->column('light')->title('!')->width('2.0em')->searchable(false);
		$table->column('dcdesc')->title($oMain->translate('desc'))->width('42.0em');
		$table->column('Username')->title($oMain->translate('modifiedby'))->width('7.0em');
		$table->column('Estado_Regra')->title($oMain->translate('tstatus'))->width('3.0em')->searchable(false);
		if($selection=='')
			$table->column('toperations')->title('!')->width('5.0em')->searchable(false)->sortable(false);

		return $table->html();
	}
	
	
	function showListComDesc($operation)
	{
		$oMain=$this->oMain;
		$sql="[bdsigip].[dbo].[spsigrule] '$oMain->sid', 'listunitext', '$this->codigo', '$this->numregra'";
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		$ArrayRst=array();
		$elementos=0;
		for ($r = 0; $r < $rc; $r++)
		{	
			$onCLick1 = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'editcomdesc_rule', '', 'codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&lang='.$rs[$r]['Idioma'].'&variant='.urlencode($this->variant).'&product='.urlencode($this->product).'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick1.'" title="'.$oMain->translate('edit').'"><img src="img/edit_s.png"></a>';
			
			$onCLickdel = "if(confirm('".$oMain->translate('delcomdesc_rule')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'delcomdesc_rule', '', 'codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&lang='.$rs[$r]['Idioma'].'&variant='.urlencode($this->variant).'&product='.urlencode($this->product).'&ppajax=1')."', 'script');";
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
		
		$linknewcomdesc=$oMain->baseLink('sigipweb', 'newcomdesc_rule', '', 'codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&product='.urlencode($this->product).'&variant='.urlencode($this->variant), '', '');
		$linknewcomdesc.= '&ppajax=1';
		$linknewcomdesc = '_urlContent2DivLoader("'.$linknewcomdesc.'", "script")';
		
		$mod='list_rule';
		$instante='';
		if($operation=='dashboard')
			$mod='show_rule';
		if($operation=='prodrule')
		{
			$mod='show_prodrule';
			//$instante='&instante='.$this->instante;
		}
		
		$onCLickback = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', $mod, '', 'codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&product='.$this->product.'&variant='.urlencode($this->variant).'&instante='.$this->instante.'&ppajax=1')."', 'main');";

		$menuObj->addItem('x10', $oMain->translate($mod), 'img/back_s.png', '',$onCLickback);
		$menuObj->addItem('x20', $oMain->translate('new_comdesc'), 'img/new_s.png', '', $linknewcomdesc);

		$table = $oMain->_stdTGrid('vccomdescTGrid');
		$table->title($oMain->translate('list_comdesc').' | '.$oMain->translate('rule').' - '.$this->codigo.' - '.$this->numregra.' '.$this->descricao);
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'listcomdesc_rule', '', 'codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&product='.urlencode($this->product).'&variant='.urlencode($this->variant)));
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
	
		$sql="SELECT codigo,numregra,nivel,tipo_regra,descricao,quantidade,utilizador,data,ordem,var_pergunta,valor_defeito,estado_regra,var_global,instante
			,dc,visible_op,remarks, dbo.efa_username(Utilizador) as utilizadordesc
			FROM bdsigip.dbo.tbqe_regras
			WHERE codigo='$this->codigo' and numregra='$this->numregra'";		

		return($sql);
	}
	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->codigo=$rst['codigo'];
			$this->numregra=$rst['numregra'];
			$this->nivel=$rst['nivel'];
			$this->tipo_regra=$rst['tipo_regra'];
			$this->descricao=$rst['descricao'];
			$this->quantidade=$rst['quantidade'];
			$this->utilizador=$rst['utilizador'];
			$this->utilizadordesc=$rst['utilizadordesc'];
			$this->data=$rst['data'];
			$this->ordem=$rst['ordem'];
			$this->var_pergunta=$rst['var_pergunta'];
			$this->valor_defeito=$rst['valor_defeito'];
			$this->estado_regra=$rst['estado_regra'];
			$this->var_global=$rst['var_global'];
			$this->instante=$rst['instante'];
			$this->dc=$rst['dc'];
			$this->visible_op=$rst['visible_op'];
			$this->remarks=$rst['remarks'];
			
		}
		return $rc;
	}
	
	function readfromdbComDesc()
	{
		$oMain = $this->oMain;
		$sql="[bdsigip].[dbo].[spsigrule] '$oMain->sid', 'getunitext', '$this->codigo', '$this->numregra', '', '', '', '', '', '', '', '', '', '', '', '', '', '$this->lang'";
		$rs = $oMain->querySQL($sql);

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
		$sid=$oMain->sid;
		
		If($operation=='insert' AND $this->lang=='')
			$this->lang=$oMain->l;
		
		$sql="bdsigip.[dbo].[spsigrule] '$sid','$operation'
		,'$this->codigo'
		,'$this->numregra'
		,'$this->nivel'
		,'$this->tipo_regra'
		,'$this->descricao'
		,'$this->quantidade'
		,'$this->ordem'
		,'$this->var_pergunta'
		,'$this->valor_defeito'
		,'$this->estado_regra'
		,'$this->var_global'
		,'$this->instante'
		,'$this->visible_op'
		,'$this->remarks'
		,'$this->unitextdesc'
		,'$this->lang'
		,'$this->codigodest'
		,'$this->copyall'
		";
//var_dump($sql); die;	
		$rs = $oMain->querySQL($sql);
		
//var_dump($rs);
		$tstatus=$rs[0]['Erro'];
		$tdesc=$rs[0]['Descricao'];
		
		if($tstatus=='0' and $tdesc=='')
			$tdesc=$oMain->translate('sucess', $oMain->l, '@GERAL@');			

		return($tstatus);
	}
	
}//End of CRule



class CprodRule extends CRule
{
	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='prodrule'; 
		
		$idpopup=$ent.'Popup';
		$idtgrid=$ent.'TGrid';
		
		
		if ($mod =='setorder_'.$ent)
		{
			$tstatus=$this->storeIntoDB('setorder', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			$html.='<script>
				_urlContent2DivLoader("'.$this->oMain->baselink('sigipweb', '_reftreeprod').'&product='.$this->product.'&instante='.$this->instante.'&ppajax=1", "menu");
				</script>';

		}
		
		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$html=$oMain->refresh($idtgrid);
			
		}

		if ($mod =='insert_'.$ent)
		{
			if($this->ordem==0)
				$tstatus=$this->storeIntoDB('insert', $tdesc);
			else
				$tstatus=$this->storeIntoDB('insertintermed', $tdesc);
			
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='xnew_'.$ent;

		}

		if ($mod =='update_'.$ent)
		{	
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$mod='show_'.$ent;
			}
			else
				$mod='xedit_'.$ent;
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			
			if($mod =='edit_'.$ent)
			{	
				$this->readFromDb(); 

			}
			$html=$this->dashboard('edit');

		}
		
		if ($mod =='editorder_'.$ent)
		{

			$this->readFromDb(); 

			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('setorder_prodrule').' - '.$this->codigo.' '.$this->instante;
				$content=$this->formOrder();
				$footer='';
				$menu='';
				
				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->formOrder();
			}
		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{

			if(_request('ppajax')!='')
			{
					$title=$oMain->translate('new_rule').' - '.$this->codigo.' ('.$oMain->translate($this->instante).')';
					$content=$this->form('insert_'.$ent,'xnew_'.$ent);
					$menu='rule';
					$footer='';
					$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
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
		

		if ($mod =='show_'.$ent)
		{
			$this->readFromDB();
			$html=$this->dashboard('show');
		}

		return($html);
	}
	
	
	function showList($selection='')
	{
		$oMain=$this->oMain;
		
		if($selection<>'')
			$cond="WHERE (Codigo = '$this->product') AND Instante='$this->instante' AND NumRegra IN ($selection)";
		else
			$cond="WHERE (Codigo = '$this->product') AND Instante='$this->instante'";
		
		if($oMain->operation=='excel')
			$sql="SELECT Codigo, NumRegra, Nivel, Tipo_Regra, Descricao, Quantidade, Utilizador, Data, Ordem, Var_Pergunta, Valor_Defeito, Estado_Regra
					, Var_Global, Instante, DC, visible_op, remarks,
					(SELECT TOP 1 username FROM tbusers WHERE userid=bdsigip.dbo.tbQE_Regras.utilizador) AS Username
					, bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l') as dcdesc
				FROM bdsigip.dbo.tbQE_Regras  
				$cond
				ORDER BY Ordem ASC";
		else
			$sql="SELECT '' as toUpdt, '' as img, '' as light, Codigo, NumRegra, Nivel, Tipo_Regra, Descricao, Quantidade, Utilizador, Data, Ordem, Var_Pergunta, Valor_Defeito, Estado_Regra
					, Var_Global, Instante, DC, visible_op, remarks,
					(SELECT TOP 1 username FROM tbusers WHERE userid=bdsigip.dbo.tbQE_Regras.utilizador) AS Username, '' as toperations
					, bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l') as dcdesc
				FROM bdsigip.dbo.tbQE_Regras  
				$cond
				ORDER BY Ordem ASC";
//var_dump($sql); die;
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		if($oMain->operation=='excel')
		{
			require_once('cexpExcell.php');
			$excel = new expToExcell();
			$excel->conn($oMain->consql)->sql($sql)->name('ListProdRules'.$this->instante)->maxrows(200000)->download();
		}
//		$ArrayRst=array();
//		$elementos=0;
		for ($r = 0; $r < $rc; $r++)
		{
			$tipo=$rs[$r]['Tipo_Regra'];
			$visible=$rs[$r]['visible_op'];
			$estado=$rs[$r]['Estado_Regra'];
			$username=$rs[$r]['Username'];
			$descricao=$rs[$r]['Descricao'];
			$dcdesc=$rs[$r]['dcdesc'];
			$dc=$rs[$r]['DC'];
			$user=$rs[$r]['Username'].' - '.$rs[$r]['Utilizador'];
//print $dc;		
			if($dc=='' OR $dcdesc=='Translation unavailable: ')
				$rs[$r]['dcdesc']=$descricao;
			
			//$rs[$r]['Username']= $username;
			
			$rs[$r]['Username']= '<p title="'.$user.'">'.$username;
			
			//$rs[$r]['Descricao']=$oMain->stdImglink('show_rule', '','','product='.$this->product.'&vartype='.$this->vartype.'&codigo='.$rs[$r]['Codigo'].'&numregra='.$rs[$r]['NumRegra'],'',$descricao,'', $oMain->translate('show_rule'));
			
//			$onCLickshow = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'show_prodrule', '', 'product='.$this->product.'&vartype='.$this->vartype.'&variant='.$this->variant.'&codigo='.$rs[$r]['Codigo'].'&numregra='.$rs[$r]['NumRegra'].'&instante='.$this->instante.'&ppajax=1')."', 'main');";
//			$linkshow = '<a href="#" onclick="'.$onCLickshow.'">'.$rs[$r]['dcdesc'].'</a>';
			
			$onCLickshow = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'show_prodrule', '', 'product='.$this->product.'&vartype='.$this->vartype.'&variant='.$this->variant.'&codigo='.$rs[$r]['Codigo'].'&numregra='.$rs[$r]['NumRegra'].'&instante='.$this->instante.'&ppajax=1')."', 'main');";
			$linkshow = '<a href="#" onclick="'.$onCLickshow.'" title="'.$rs[$r]['dcdesc'].'">'.$rs[$r]['dcdesc'].'</a>';
			
			$rs[$r]['dcdesc']=$linkshow;
			
			if(trim($tipo)=='Pergunta ao Utilizador (Sim/Não)?')
				$rs[$r]['img']='<img src="img/rules_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Seleccionar equipamento a partir de variáveis')
				$rs[$r]['img']='<img src="img/rulev_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Atribuir valores a variáveis da variante')
				$rs[$r]['img']='<img src="img/ruleav_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Criar Tarefas para a solução')
				$rs[$r]['img']='<img src="img/rulecreate_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Obrigatório seleccionar equipamentos')
				$rs[$r]['img']='<img src="img/rulequip_s.png" title="'.$oMain->translate($tipo).'">';
			if(trim($tipo)=='Seleccionar esquema unifilar')
				$rs[$r]['img']='<img src="img/rules_s.png" title="'.$oMain->translate($tipo).'">';
			
			
			
			if(trim($estado)=='Produção')
			{
				if(trim($visible)==1)
					$rs[$r]['Estado_Regra']='<img src="img/ruleprod_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procvis_s.png" title="'.$oMain->translate('propvisib').'">';
				else
					$rs[$r]['Estado_Regra']='<img src="img/ruleprod_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procnotvis_s.png" title="'.$oMain->translate('propinvisib').'">';
			}
			elseif(trim($estado)=='Protótipo')
			{
				if(trim($visible)==1)
					$rs[$r]['Estado_Regra']='<img src="img/mandatory_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procvis_s.png" title="'.$oMain->translate('propvisib').'">';
				else
					$rs[$r]['Estado_Regra']='<img src="img/mandatory_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procnotvis_s.png" title="'.$oMain->translate('propinvisib').'">';
			}
			else
			{
				if(trim($visible)==1)
					$rs[$r]['Estado_Regra']='<img src="img/ruleobs_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procvis_s.png" title="'.$oMain->translate('propvisib').'">';
				else
					$rs[$r]['Estado_Regra']='<img src="img/ruleobs_s.png" title="'.$oMain->translate($estado).'"> <img src="img/procnotvis_s.png" title="'.$oMain->translate('propinvisib').'">';
			}
			
			//$linkcondit=$oMain->stdImglink('list_rulecond', '','','product='.$this->product.'&vartype='.$this->vartype.'&codigo='.$rs[$r]['Codigo'].'&numregra='.$rs[$r]['NumRegra'],'img/list_s.png','','', $oMain->translate('list_rulecond'));
			//$linkcomdesc=$oMain->stdImglink('listcomdesc_rule', '','','codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['NumRegra'],'img/translate_s.png','','', $oMain->translate('listcomdesc_valcamp'));
			$onCLickorder = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'editorder_prodrule', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['NumRegra'].'&variant='.urlencode($this->variant).'&vartype='.$this->vartype.'&product='.urlencode($this->product).'&instante='.$this->instante.'&ppajax=1')."', 'script');";
			$linkorder = '<a href="#" onclick="'.$onCLickorder.'" title="'.$oMain->translate('editorder').'"><img src="img/order_s.png"></a>';
			
			
			$onCLickcdesc = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'listcomdesc_rule', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['NumRegra'].'&variant='.urlencode($this->variant).'&vartype='.$this->vartype.'&product='.$this->product.'&instante='.$this->instante.'&ppajax=1&operation=prodrule')."', 'main');";
			$linkcomdesc = '<a href="#" onclick="'.$onCLickcdesc.'" title="'.$oMain->translate('comdesc').'"><img src="img/translate_s.png"></a>';
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_prodrule', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['NumRegra'].'&variant='.urlencode($this->variant).'&vartype='.$this->vartype.'&product='.$this->product.'&ppajax=1')."', 'main');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'" title="'.$oMain->translate('edit').'"><img src="img/edit_s.png"></a>';
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'del_prodrule', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['NumRegra'].'&variant='.urlencode($this->variant).'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['toperations']=$linkorder.' '.$linkcomdesc.' '.$linkedit.' '.$linkdel;
		}
		
		if($selection=='')
		{
			$menuObj = new _menuMaker('');
			$menuObj->create('mlevel');
			$menuObj->menu->cBackground(false);

			$linknewprule=$oMain->baseLink('sigipweb', 'new_prodrule', '', 'product='.$this->product.'&codigo='.$this->product.'&vartype='.$this->vartype.'&instante='.$this->instante, '', '');
			$linknewprule.= '&ppajax=1';
			$menuObj->addItem('x10', $oMain->translate('new_prodrule'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$linknewprule.'", "script");');
		}


//var_dump ($rs);
		$table = $oMain->_stdTGrid('prodruleTGrid');
		$table->title($oMain->translate('prodrules'.$this->instante).' '.$this->product);
		if($selection=='')
			$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'list_prodrule', '', '&product='.$this->product.'&instante='.$this->instante));
//		$table->lineOnClick('_urlContent2DivLoader("'.$this->oMain->baseLink('sigipweb', 'show_rule').'&product='.$this->product.'&variant='.$this->variant.'&codigo=§§Codigo§§&numregra=§§NumRegra§§&ppajax=1", "main");');
		$table->border(0);
		$table->vals($rs);
		$table->exportExcellLink($oMain->baseLink('sigipweb', 'list_prodrule', '', '&product='.$this->product.'&instante='.$this->instante.'&operation=excel'));
		$table->exportExcell(true);
		
		if($selection<>'')
		{
			$table->searchable(false);
			$table->showFixedFooter(false);
		}
		
		if($selection=='')
		{
			$table->uid('§§NumRegra§§');
			$table->form->hidden('mod', 'multisel_rule');
			$table->form->hidden('product', $this->product);
			$table->form->hidden('codigo', $this->product);
			$table->form->hidden('instante', $this->instante);
			$table->form->hidden('operation', 'product');
			//$table->form->hidden('variant', $this->variant);

			$table->showGlobalActions(true);
			$op1=$oMain->translate('prod');
			$op2=$oMain->translate('prot');
			$op3=$oMain->translate('obsolete');
			$op4=$oMain->translate('visprop');
			$op5=$oMain->translate('notvisprop');
			$op6=$oMain->translate('setrulestart');
			$op7=$oMain->translate('setruleend');
			$op8=$oMain->translate('copyrule');
			
			
//			$op1=$oMain->translate('prod');
//			$op2=$oMain->translate('prot');
//			$op3=$oMain->translate('obsolete');
//			$op4=$oMain->translate('visprop');
//			$op5=$oMain->translate('notvisprop');
//			$op6=$oMain->translate('copyrule');
			
			$table->globalActions(array(1=>$op1, 2=>$op2, 3=>$op3, 4=>$op4, 5=>$op5, 6=>$op6, 7=>$op7, 8=>$op8));
			$title=$oMain->translate('save');
			$table->globalActionsText($title);

			$table->column('toUpdt')->title('')->width('2.0em')->editable(true)->editType('checkbox')->searchable(false)->sortable(FALSE);
		}
		$table->column('img')->title('!')->width('2.0em')->searchable(false)->sortable(false);
		$table->column('Ordem')->title($oMain->translate('order'))->width('3.0em');
		$table->column('NumRegra')->title($oMain->translate('numregra'))->width('3.0em');
		//$table->column('light')->title('!')->width('2.0em')->searchable(false);
		$table->column('dcdesc')->title($oMain->translate('desc'))->width('42.0em');
		$table->column('Username')->title($oMain->translate('modifiedby'))->width('7.0em');
		$table->column('Estado_Regra')->title($oMain->translate('tstatus'))->width('3.0em')->searchable(false);
		if($selection=='')
			$table->column('toperations')->title('!')->width('5.0em')->searchable(false)->sortable(false);

		return $table->html();
	}
	

	
	function dashboard($operation)
	{
		$oMain=$this->oMain;
		
//var_dump($operation);		
		
		if($operation=='show')
		{
			$mod='show_prodrule';
			$mod2='';
		}
		if($operation=='edit')
		{
			$mod='update_prodrule';
			$mod2='xedit_prodrule';
		}

		$ocond = new CRuleCond($oMain);
		$ocond->numregra=$this->numregra;
		$ocond->codigo=$this->codigo;
		
		$conds=$ocond->showList();
		
		$equips='';
		if($this->tipo_regra=='Seleccionar equipamento a partir de variáveis' OR $this->tipo_regra=='Obrigatório seleccionar equipamentos')
		{
			$oequip = new CRulEquip($oMain);
			$oequip->numregra=$this->numregra;
			$oequip->codigo=$this->codigo;
			//$oequip->product=$this->product;
			$oMain->operation='product';
			$equips=$oequip->showList();
		}
		
		$vars='';
		if($this->tipo_regra=='Atribuir valores a variáveis da variante' OR $this->tipo_regra=='globalart')
		{
			$ovar = new CRuleVar($oMain);
			$ovar->numregra=$this->numregra;
			$ovar->codigo=$this->codigo;
			$oMain->operation='product';
			$vars=$ovar->showList();
		}
		
		$tasks='';
		if($this->tipo_regra=='Criar Tarefas para a solução')
		{
			$otask = new CRuleTask($oMain);
			$otask->numregra=$this->numregra;
			$otask->codigo=$this->codigo;
			$tasks=$otask->showList();
		}
		
		$rulequest='';
		if(trim($this->tipo_regra)=='Pergunta ao Utilizador (Sim/Não)?')
		{
			$sql="SELECT var_pergunta FROM bdsigip.dbo.tbqe_regras WHERE codigo='$this->codigo' and numregra='$this->numregra'";
			$rs = $oMain->querySQL($sql);
			
			$varpergunta=$rs[0]['var_pergunta'];
			
			if(trim($varpergunta)=='Personalizar')
			{
				$orulequest = new CRuleQuestion($oMain);
				$orulequest->numregra=$this->numregra;
				$orulequest->codigo=$this->codigo;
				$oMain->operation='product';
				$rulequest=$orulequest->showList();
			}
		}
		
		
		$spltop= new _splitter('');
		$spltop->orientation('v');
		$spltop->add('t21', $this->form($mod,$mod2), '100%', false, true);

		$onCLickback = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'list_prodrule', '', '&product='.$this->product.'&instante='.$this->instante.'&ppajax=1')."', 'main');";
		$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_prodrule', '', 'codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&instante='.$this->instante.'&ppajax=1')."', 'main');";
		$onCLickcdesc = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'listcomdesc_rule', '', 'codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&instante='.$this->instante.'&product='.urlencode($this->product).'&ppajax=1&operation=prodrule')."', 'main');";
//print $this->product;		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$menuObj->addItem('x10', $oMain->translate('operations'), _iconSrc('efa_edit'), '', '');
			$menuObj->item('x10')->addItem('x11', $oMain->translate('edit'), _iconSrc('efa_edit'), '', $onCLick);
			$menuObj->item('x10')->addItem('x12', $oMain->translate('comdesc'), 'img/translate_s.png', '', $onCLickcdesc);
		$menuObj->addItem('x15', $oMain->translate('list_rule'), 'img/rules_s.png', '', $onCLickback);
		
		$window = new _window('');
		$dashtitle=$oMain->translate('ruledashboardprod').' ('.$this->instante.')';
		$window->icon('img/efasst01_x.png');
		//$window->title($dashtitle.' - '.$this->codigo.' | '.$this->descricao);
		$window->title($dashtitle.' - '.$this->codigo.' | Num:'.$this->numregra.' - Ord:'.$this->ordem.' | '.$oMain->formatdate($this->data).' - '.$this->utilizadordesc);
		$window->content($spltop->html());
		$window->menu($menuObj->html());
		if($operation=='edit')
		{
			$button=_button('', $oMain->translate('save'),  _iconSrc('save'), '', '_formSubmit("formprodrule"); ')->html();
			$button2=_button('', $oMain->translate('cancel'),  _iconSrc('cancel'), '', '_urlContent2DivLoader("'.$this->oMain->baseLink('sigipweb', 'show_prodrule').'&codigo='.$this->codigo.'&numregra='.$this->numregra.'&instante='.$this->instante.'&ppajax=1", "main"); ')->html();
			$window->footer($button.' '.$button2);
		}
		
		if($equips<>'')
			$optable=$equips;
		if($vars<>'')
			$optable=$vars;
		if($tasks<>'')
			$optable=$tasks;
		if($rulequest<>'')
			$optable=$rulequest;
		
		
		$splbottom= new _splitter('');
		$splbottom->orientation('h');
		$splbottom->add('t10', $conds, '50%');
		$splbottom->add('t11', ' ', '10px');
		$splbottom->add('t12', $optable, '50%');
		
		//print $this->tipo_regra;
//		if($this->tipo_regra=='Obrigatório seleccionar equipamentos' OR $this->tipo_regra=='Atribuir valores a variáveis da variante' OR $this->tipo_regra=='Seleccionar equipamento a partir de variáveis')
//			$y="10%";
//		else
//			$y="25%";
		//print $y;
				
		$splmain= new _splitter('');
		$splmain->orientation('v');
		$splmain->add('t20', $window->html(), '');
		$splmain->add('t21', $splbottom->html(), '50%');
		

		return $splmain->html();
	}
	
	
	function form($mod='show_prodrule', $modChange='')
	{
		$oMain=$this->oMain;
		
		$operation='';
		//$mod='xxxx_vartype';
		$formName = 'formprodrule';
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(2);
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		if($mod=='insert_prodrule')
			$form->ajax(true)->ajaxDiv('script');
		else
			$form->ajax(true)->ajaxDiv('main');
		
		
		if($mod=='show_prodrule') 
			$form->readOnly(true);
		
		$read=FALSE;
		if($mod=='insert_prodrule')
		{
			$this->estado_regra='Protótipo';
			$this->nivel='Produto';
			$this->quantidade=1;
			$read=TRUE;
		}
		
		$form->hidden('codigo', $this->codigo);
		$form->hidden('product', $this->codigo);
		$form->hidden('nivel', $this->nivel);
		$form->hidden('dc', $this->dc);
		$form->hidden('instante', $this->instante);
		$form->hidden('visible_op', $this->visible_op);
		$form->hidden('quantidade', $this->quantidade);
		$form->hidden('numregra', $this->numregra);
		//$form->hidden('ordem', $this->ordem);
		
		
//		if($mod<>'insert_prodrule')
//		{
//			$form->elementAdd(new _htmlInput('numregra', $oMain->translate('numregra'), $this->numregra))->readonly(TRUE)->required(true);;
//			$form->elementAdd(new _htmlInput('ordem', $oMain->translate('ordem'), $this->ordem))->readonly(TRUE)->required(true);;
//		}
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
			FROM dbo.tbcodes C  
			WHERE (codetype = 'sigipwebrulstat' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('estado_regra', $oMain->translate('tstatus'), $this->estado_regra, $temp))->blank(true)->required(true);;
		
		
		//$form->elementAdd(new _htmlInput('tipo_regra', $oMain->translate('tipo_regra'), $this->tipo_regra));
		$sql="SELECT regra as codigo, regra FROM bdsigip.dbo.tbQE_Tipos_Regra where Produto='s' order by Ordem asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$select=$form->elementAdd(new _htmlSelect('tipo_regra', $oMain->translate('type'), $this->tipo_regra, $temp))->blank(true)->required(true);;
		$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
		
		$read=TRUE;
		if($this->tipo_regra=='Pergunta ao Utilizador (Sim/Não)?')
			$read=FALSE;
		if($this->tipo_regra=='Pergunta ao Utilizador (Sim/Não)?')
		{
			//$form->elementAdd(new _htmlInput('var_pergunta', $oMain->translate('var_pergunta'), $this->var_pergunta));
			$sql="SELECT Nome, Nome as tdesc  FROM bdsigip.dbo.tbQE_VariaveisDecisao Where Pergunta='S' order by Nome";
			$temp = $oMain->qSQL()->getDI($sql);
			$select=$form->elementAdd(new _htmlSelect('var_pergunta', $oMain->translate('var_pergunta'), $this->var_pergunta, $temp))->blank(true)->readonly($read)->required(true);;
			$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());

			if($mod=='insert_prodrule' and $this->var_pergunta<>'')
			{
				$sql="SELECT fonte, atributo FROM bdsigip.dbo.tbQE_VariaveisDecisao where Nome='$this->var_pergunta'";
				$rs = $oMain->querySQL($sql);

				$fonte=$rs[0]['fonte'];
				$atributo=$rs[0]['atributo'];
			}

			if($fonte=='CampoAlf' OR $fonte=='CampoNum' OR $fonte=='')
			{
				//$form->elementAdd(new _htmlInput('valor_defeito', $oMain->translate('valor_defeito'), $this->valor_defeito));
//				$sql="SELECT Valor as Codigo, Valor AS Descricao, Valor
//					from bdsigip.dbo.tbQE_ValoresCampos 
//					where Campo='$this->var_pergunta'
//					order by Ordem asc";
				$sql="exec bdsigip.dbo.spsigfeaturevalues '$oMain->sid', 'list', '$this->var_pergunta', '$this->product', '$this->codigo', '$this->numregra'";
				$temp = $oMain->qSQL()->keyDesc('Descricao')->getDI($sql);
				$form->elementAdd(new _htmlSelect('valor_defeito', $oMain->translate('valor_defeito'), $this->valor_defeito, $temp))->blank(true)->readonly($read);
			}
		
		}
		//$form->elementAdd(new _htmlInput('var_global', $oMain->translate('var_global'), $this->var_global));
		
		if($this->tipo_regra=='Pergunta ao Utilizador (Sim/Não)?' OR $this->tipo_regra=='globalart')
		{
			$sql="SELECT Nome, Nome as tdesc  FROM  bdsigip.dbo.tbQE_VariaveisDecisao Where GlobalProduto='S' order by Nome";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('var_global', $oMain->translate('var_global'), $this->var_global, $temp))->blank(true)->readonly($read);
		}
		
		$form->elementAdd(new _htmlInput('descricao', $oMain->translate('descricao'), $this->descricao),null,2)->required(true);;
		$form->elementAdd(new _htmlInput('remarks', $oMain->translate('remarks'), $this->remarks),null,2);
		
		if($mod=='insert_prodrule')
		{
			$form->elementAdd(new _htmlFieldsGroup('t1', $this->oMain->translate('intermedia')));
			$form->elementAdd(new _htmlInput('ordem', $oMain->translate('ordem'), $this->ordem));
		}
		else
		{
			$form->hidden('ordem', $this->ordem);
		}
		
		if($mod=='show_prodrule')
		{
			$form->elementAdd(new _htmlInput('utilizador', $oMain->translate('utilizador'), $this->utilizadordesc),null,2);
			$form->elementAdd(new _htmlInput('data', $oMain->translate('data'), $oMain->formatdate($this->data)),null,2);
		}
		
		if($mod=='insert_prodrule')
		{
			$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
			//$button->confirm($oMain->translate('confassociate'));
		
			return $form->html().$button->html();
		}
		else
			return $form->html();
	}
	
	function formOrder()
	{
		$oMain=$this->oMain;
		
		$operation=$oMain->operation;
		$mod='setorder_prodrule';
		$formName = 'formprodrule'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(1);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		
		$form->hidden('product', $this->product);
		$form->hidden('instante', $this->instante);

		$form->elementAdd(new _htmlInput('codigo', $oMain->translate('codigo'), $this->codigo))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('numregra', $oMain->translate('numregra'), $this->numregra))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('descricao', $oMain->translate('descricao'), $this->descricao))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('ordem', $oMain->translate('ordem'), $this->ordem))->required(true);

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		$button->confirm($oMain->translate('conf'));

		return $form->html().$button->html();
		
	}
	
	
} //End CprodRule


class CRuleCond
{
	var $codigo;    /**  */
	var $numregra;    /**  */
	var $numcondicao;    /**  */
	var $variavel;    /**  */
	var $valor;    /**  */
	var $operador;    /**  */
	var $op_ligacao;    /**  */
	var $valorvar;    /**  */
	var $opvalorvar;    /**  */
	var $valornum;    /**  */
	var $opvalornum;    /**  */
	
	var $nivel;
	var	$product;
	var $variant;
	var $newnumcondicao;
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class Crulecond mod
	 */	
	function getHtml(&$mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='rulecond'; 
		
		$idpopup=$ent.'Popup';
		$idtgrid=$ent.'TGrid';
	
		if ($mod =='multisel_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			
			$this->getSelection();

			$html='<script>
					_urlContent2DivLoader("'.$this->oMain->baselink('sigipweb', '_reftreeprod').'&product='.$this->product.'&variant='.$this->variant.'&ppajax=1", "menu");
					</script>';
			$mod='show_rule';
			
		}

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$html=$oMain->refresh($idtgrid);
		}
		
		if ($mod =='editorder_'.$ent)
		{
			
			$this->readFromDb(); 

			
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('setorder_rulecond').' - '.$this->codigo.' '.$this->numregra;
				$content=$this->formOrder();
				$footer='';
				$menu='';
				
				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->formOrder();
			}
		}
		
		if ($mod =='setorder_'.$ent)
		{
			$tstatus=$this->storeIntoDB('updateorder', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='list_'.$ent;
		}

		if ($mod =='insert_'.$ent)
		{
			if($this->numcondicao<>0)
				$tstatus=$this->storeIntoDB('insert_intermediate', $tdesc);
			else
				$tstatus=$this->storeIntoDB('insert', $tdesc);
			
			$oMain->stdShowResult($tstatus, $tdesc);

			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='xnew_'.$ent;
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xedit_'.$ent;
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			if($mod =='edit_'.$ent)
				$this->readFromDB();
			
			
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('edit_rulecond').' - '.$this->codigo.' - '.$this->numregra.' - '.$this->numcondicao;
				$content=$this->form('update_'.$ent,'xedit_'.$ent);
				$menu='rulecond';

				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
				
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('update_'.$ent,'xedit_'.$ent);
			}

		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('new_rulecond').' - '.$this->codigo.' - '.$this->numregra;
				$content=$this->form('insert_'.$ent,'xnew_'.$ent);
				$menu=$oMain->translate('rulecond');
				$footer='';
				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
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

		if ($mod =='show_'.$ent)
		{
			$this->readFromDb();
			$oMain->subtitle=$oMain->translate('show_'.$ent).' '.$Codigo;
			$html=$this->form('show_'.$ent);
		}

		return($html);
	}
	
	 /**
	  * read class Crulecond atributes from request
	  */	
	function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->codigo=$oMain->GetFromArray('codigo',$_REQUEST,'string_trim');
		$this->numregra=$oMain->GetFromArray('numregra',$_REQUEST,'int');
		$this->numcondicao=$oMain->GetFromArray('numcondicao',$_REQUEST,'int');
		$this->variavel=$oMain->GetFromArray('variavel',$_REQUEST,'string_trim');
		$this->valor=$oMain->GetFromArray('valor',$_REQUEST,'string');
		$this->operador=$oMain->GetFromArray('operador',$_REQUEST,'string_trim');
		$this->op_ligacao=$oMain->GetFromArray('op_ligacao',$_REQUEST,'string_trim');
		$this->valorvar=$oMain->GetFromArray('valorvar',$_REQUEST,'string_trim');
		$this->opvalorvar=$oMain->GetFromArray('opvalorvar',$_REQUEST,'string_trim');
		$this->valornum=$oMain->GetFromArray('valornum',$_REQUEST,'string_trim');
		$this->opvalornum=$oMain->GetFromArray('opvalornum',$_REQUEST,'string_trim');
		
		$this->nivel=$oMain->GetFromArray('nivel',$_REQUEST,'string_trim');
		$this->product=$oMain->GetFromArray('product',$_REQUEST,'string_trim');
		$this->variant=$oMain->GetFromArray('variant',$_REQUEST,'string_trim');
		
		$this->newnumcondicao=$oMain->GetFromArray('newnumcondicao',$_REQUEST,'int');
		

	}
	/**
	 * class Crulecond form
	 */	
	function form($mod='show_rulecond',$modChange='')
	{

		$oMain=$this->oMain;
		
//print $this->product.' product';		
		$operation='';
		//$mod='xxxx_vartype';
		$formName = 'formrulecond'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(2);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		$form->hidden('product', $this->product);
		
		
		$form->elementAdd(new _htmlInput('codigo', $oMain->translate('codigo'), $this->codigo))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('numregra', $oMain->translate('numregra'), $this->numregra))->readonly(TRUE);
		
		if($mod=='insert_rulecond')
		{	
			//get nivel from rule: Product or Variant
			$this->nivel=$oMain->getNivel($this->codigo, $this->numregra);
//var_dump($this->nivel); die;
		}
		else
		{
			$form->elementAdd(new _htmlInput('numcondicao', $oMain->translate('numcondicao'), $this->numcondicao))->readonly(TRUE);
		}
		
	
		
		$sql="SELECT nome,
					tdesc=CASE WHEN
					DC IS NULL THEN nome
					WHEN
					bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')='Translation unavailable: ' THEN nome
					ELSE
					(bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')+' - '+nome)
					END
				FROM bdsigip.dbo.tbQE_VariaveisDecisao
				Where Decisao='S' 
				UNION
				SELECT 'Pergunta '+cast(NumRegra as varchar(10)) as nome,'Pergunta '++cast(NumRegra as varchar(10)) as tdesc   FROM  bdsigip.dbo.tbQE_Regras  where Tipo_Regra='Pergunta ao Utilizador (Sim/Não)?' and Codigo='$this->codigo' 
				Order by tdesc asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$select=$form->elementAdd(new _htmlSelect('variavel', $oMain->translate('variavel'), $this->variavel, $temp))->blank(true);
		$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
		
		if($this->variavel<>'')
		{
			//get fonte value: Numeric or Alfannumeric
			$fonte=$oMain->getFonte($this->variavel);
			$atributo=$oMain->getAtributo($this->variavel);
			
			//get tipo dados
			$tipodados=$oMain->getTipoDados($this->variavel);
		}
//print $fonte.' '.$atributo.' '.$tipodados;		
		$block=TRUE;
		if($fonte=='CampoNum')
		{
			$block=FALSE;
		}
		
		$alfoper='';
		if($tipodados<>'N')
			$alfoper="AND obs='Alfa'";
		
		//print $fonte;
		//print $this->operador;
		
		$operator=$this->operador;
		if($this->operador=='<' or $this->operador=='<=' or $this->operador=='=' or $this->operador=='<>' or $this->operador=='>' or $this->operador=='>=')
			$operator=$oMain->getNumOperator($this->operador);
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
			FROM dbo.tbcodes C  
			WHERE (codetype = 'sigipweboper' and tstatus='A' $alfoper) ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('operador', $oMain->translate('operador'), $operator, $temp))->blank(true);
		
//print $this->product.' product';			
		$form->elementAdd(new _htmlInput('valor', $oMain->translate('valor'), $this->valor));
		//$sql="SELECT valor, valor as tdesc From BDSIGIP.dbo.tbQE_ValoresCampos  where campo='$atributo' order by campo asc ,Ordem asc";
		$sql="exec bdsigip.dbo.spsigfeaturevalues '$oMain->sid', 'list', '$this->variavel', '$this->product', '$this->codigo', '$this->numregra'";
		//print $sql;
		$temp = $oMain->qSQL()->keyDesc('Descricao')->getDI($sql);
		$form->elementAdd(new _htmlSelect('valor', $oMain->translate('valor'), $this->valor, $temp))->blank(true);
		
		//$form->elementAdd(new _htmlInput('operador', $oMain->translate('operador'), $this->operador));
		if($mod=='insert_rulecond')
			$this->op_ligacao='E';
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
			FROM dbo.tbcodes C  
			WHERE (codetype = 'sigipwebandor' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('op_ligacao', $oMain->translate('op_ligacao'), $this->op_ligacao, $temp))->blank(true);
		
		
		if($fonte=='CampoNum')
		{
			$form->elementAdd(new _htmlFieldsGroup('t1', $this->oMain->translate('extra')));
			//$form->elementAdd(new _htmlInput('op_ligacao', $oMain->translate('op_ligacao'), $this->op_ligacao));

			//$form->elementAdd(new _htmlInput('opvalorvar', $oMain->translate('opvalorvar'), $this->opvalorvar));
			$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
				FROM dbo.tbcodes C  
				WHERE codeid IN ('+','-','*') AND (codetype = 'sigipwebcalc' and tstatus='A') ORDER BY C.torder asc";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('opvalorvar', $oMain->translate('opvalorvar'), $this->opvalorvar, $temp))->blank(true)->readonly($block);

			//$form->elementAdd(new _htmlInput('valorvar', $oMain->translate('valorvar'), $this->valorvar));
			$sql="SELECT nome, nome as tdesc  FROM  bdsigip.dbo.tbQE_VariaveisDecisao Where Fonte='CampoNum' and Decisao='S' order by Nome";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('valorvar', $oMain->translate('valorvar'), $this->valorvar, $temp))->blank(true)->readonly($block);


			//$form->elementAdd(new _htmlInput('opvalornum', $oMain->translate('opvalornum'), $this->opvalornum));
			$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
				FROM dbo.tbcodes C  
				WHERE codeid IN ('+','-','*') AND (codetype = 'sigipwebcalc' and tstatus='A') ORDER BY C.torder asc";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('opvalornum', $oMain->translate('opvalornum'), $this->opvalornum, $temp))->blank(true)->readonly($block);

			$form->elementAdd(new _htmlInput('valornum', $oMain->translate('valornum'), $this->valornum))->readonly($block);
		}
		
		if($mod=='insert_rulecond')
		{
			$form->elementAdd(new _htmlFieldsGroup('t2', $this->oMain->translate('intermedia')));

			$form->elementAdd(new _htmlInput('numcondicao', $oMain->translate('numcondicao'), $this->numcondicao));
		}

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		$button->confirm($oMain->translate('conf'));
		
		return $form->html().$button->html();
	}
	
	function formOrder()
	{
		$oMain=$this->oMain;
		
		$operation=$oMain->operation;
		$mod='setorder_rulecond';
		$formName = 'formruleordcond'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(1);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		
		$form->hidden('product', $this->product);
		$form->hidden('numcondicao', $this->numcondicao);

		$form->elementAdd(new _htmlInput('codigo', $oMain->translate('codigo'), $this->codigo))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('numregra', $oMain->translate('numregra'), $this->numregra))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('variavel', $oMain->translate('variavel'), $this->variavel))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('newnumcondicao', $oMain->translate('newnumcondicao'), $this->newnumcondicao))->required(true);

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		$button->confirm($oMain->translate('conf'));

		return $form->html().$button->html();
		
	}

	/**
	 * save class Crulecond record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$modifnum=0;// not being used
		
		$operador=$this->operador;
		if($this->operador<>'<' and $this->operador<>'<=' and $this->operador<>'=' and $this->operador<>'<>' and $this->operador<>'>' and $this->operador<>'>=')
			$operador=$oMain->getAlphaOperator($this->operador);
		
		$sql="bdsigip.[dbo].[spsigrulecond] '$sid','$operation','$modifnum'
		,'$this->codigo'
		,'$this->numregra'
		,'$this->numcondicao'
		,'$this->variavel'
		,'$this->valor'
		,'$operador'
		,'$this->op_ligacao'
		,'$this->valorvar'
		,'$this->opvalorvar'
		,'$this->valornum'
		,'$this->opvalornum'
		,'$this->newnumcondicao'
		";
			
			
		$rs = $oMain->querySQL($sql);
//var_dump($rs);
		$tstatus=$rs[0]['Erro'];
		$tdesc=$rs[0]['Descricao'];
		
		if($tstatus=='0' and $tdesc=='')
			$tdesc=$oMain->translate('sucess', $oMain->l, '@GERAL@');			

		return($tstatus);
	}
	

	
	/**
	 * query to get class Crulecond record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT COND.codigo,COND.numregra,COND.numcondicao,COND.variavel,COND.valor,COND.operador,COND.op_ligacao,COND.valorvar,COND.opvalorvar,COND.valornum,COND.opvalornum
				,RE.nivel
			FROM bdsigip.dbo.tbQE_Regras_Condicoes AS COND inner join
			bdsigip.dbo.tbQE_Regras as RE on COND.codigo=RE.codigo and COND.numregra=RE.numregra
			WHERE COND.codigo='$this->codigo' and COND.numregra='$this->numregra' and COND.numcondicao='$this->numcondicao'";		

		return($sql);
	}
	
	function getSelection()
	{
		$oMain=$this->oMain;
		$toupdt = Array();
    	$toupdt = $this->oMain->GetFromArray('toUpdt', $_REQUEST, 'int');

		if(empty($toupdt))
		{
			$oMain->message('warning','',$oMain->translate('nochk'),20);
			return('');
		}
		else
		{	
			$this->product=$oMain->GetFromArray('product',$_REQUEST,'string_trim');
			$this->codigo=$oMain->GetFromArray('codigo',$_REQUEST,'string_trim');
			$this->numregra=$oMain->GetFromArray('numregra',$_REQUEST,'int');
			
			$errors = array();
			$msgOk = '';
			
			foreach($toupdt as $k=>$v)
			{
				$this->numcondicao=(int) $k;
				
				$this->readFromDb();
				
				$tstatus=$this->storeIntoDB('insert', $tdesc);
				
				
				
				if($tstatus!='0') 
					$errors[] = $this->codigo.' - '.$this->numregra.' - '.$k.' : '.$tdesc;
				else 
					$msgOk = $tdesc;
				//$oMain->stdShowResult($tstatus, $tdesc);
			}
			if(count($errors)>0) 
			{
				$error = implode('<br>', $errors);
				$oMain->stdShowResult(-1, $error);
			} 
			else 
				$oMain->stdShowResult(0, $msgOk);
			
		}
		
	}
	/**
	 * set class Crulecond atributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->codigo=$rst['codigo'];
			$this->numregra=$rst['numregra'];
			$this->numcondicao=$rst['numcondicao'];
			$this->variavel=$rst['variavel'];
			$this->valor=$rst['valor'];
			$this->operador=$rst['operador'];
			$this->op_ligacao=$rst['op_ligacao'];
			$this->valorvar=$rst['valorvar'];
			$this->opvalorvar=$rst['opvalorvar'];
			$this->valornum=$rst['valornum'];
			$this->opvalornum=$rst['opvalornum'];
			
			$this->nivel=$rst['nivel'];
		}
		
		return $rc;
	}
	
	 /**
	  * advanced Search query to database
	  */
	
	function showList()
	{
		$oMain=$this->oMain;
		
		$sql="SELECT '' as toUpdt,RCOND.codigo,RCOND.numregra,RCOND.numcondicao,RCOND.variavel,RCOND.valor,RCOND.operador,RCOND.op_ligacao,RCOND.valorvar,RCOND.opvalorvar
				,RCOND.valornum,RCOND.opvalornum,  ISNULL(V.produto,RCOND.codigo) AS produto, '' as toperations
			FROM bdsigip.dbo.tbQE_Regras_Condicoes AS RCOND INNER JOIN
            bdsigip.dbo.tbQE_Regras AS R ON R.Codigo = RCOND.Codigo AND R.NumRegra = RCOND.NumRegra LEFT OUTER JOIN
			bdsigip.dbo.tbQE_Variantes AS V ON  V.Desenho = R.Codigo
			WHERE RCOND.codigo='$this->codigo' AND RCOND.numregra='$this->numregra'";
		
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		//$ArrayRst=array();
		for ($r = 0; $r < $rc; $r++)
		{		
			$variavel=$rs[$r]['variavel'];
			$valor=$rs[$r]['valor'];
			
			//$operador=$rs[$r]['operador'];
			
			$rs[$r]['valor']= '<p title="'.$valor.'">'.$valor;

			
			$onCLickorder = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'editorder_rulecond', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&numcondicao='.$rs[$r]['numcondicao'].'&ppajax=1')."', 'script');";
			$linkorder = '<a href="#" onclick="'.$onCLickorder.'" title="'.$oMain->translate('editorder').'"><img src="img/order_s.png"></a>';
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_rulecond', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&numcondicao='.$rs[$r]['numcondicao'].'&product='.$this->product.'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'"><img src="img/edit_s.png"></a>';
			$link = '<a href="#" onclick="'.$onCLick.'" title="'.$variavel.'">'.$variavel.'</a>';
			$rs[$r]['variavel']=$link;
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'del_rulecond', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&numcondicao='.$rs[$r]['numcondicao'].'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['toperations']=$linkorder.' '.$linkedit.' '.$linkdel;

		}
		
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$newrulecond=$oMain->baseLink('sigipweb', 'new_rulecond', '', 'codigo='.$this->codigo.'&numregra='.$this->numregra.'&product='.$this->product, '', '');
		$newrulecond.= '&ppajax=1';
		
		$menuObj->addItem('x10', $oMain->translate('new_rulecond'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$newrulecond.'", "script");');


//var_dump ($rs);
		$image="<img src='img/condition_s.png'>";
		$table = $oMain->_stdTGrid('rulecondTGrid');
		//$table->titleHide(true);
		$table->title($image.' '.$oMain->translate('list_rulecond'));
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'list_rulecond', '', '&codigo='.$this->codigo.'&numregra='.$this->numregra));
//		$table->lineOnClick('_urlContent2DivLoader("'.$this->oMain->baseLink('sigipweb', 'show_rule').'&product='.$this->product.'&variant='.$this->variant.'&codigo=§§Codigo§§&numregra=§§NumRegra§§&ppajax=1", "main");');
		$table->border(0);
		$table->vals($rs);
//		var_dump($rs);
		$table->searchable(false);
		//$table->showFixedFooter(false);
		
		$product=$rs[0]['produto'];
		$table->uid('§§numcondicao§§');
		$table->form->hidden('mod', 'multisel_rulecond');
		$table->form->hidden('product', $product);
		$table->form->hidden('codigo', $this->codigo);
		$table->form->hidden('numregra', $this->numregra);
		$table->form->hidden('variant', $this->codigo);

		
		$table->showGlobalActions(true);

		$title=$oMain->translate('copycond');
		$table->globalActionsText($title);
		
		
		$table->column('toUpdt')->title('')->width('2.0em')->editable(true)->editType('checkbox')->searchable(false)->sortable(FALSE);
		//$table->column('img')->title('!')->width('2.0em')->searchable(false)->sortable(false);
		$table->column('numcondicao')->title('!')->width('2.0em');
		$table->column('variavel')->title($oMain->translate('variavel'))->width('14.0em');
		$table->column('operador')->title('Op.')->width('3.0em');
		$table->column('valor')->title($oMain->translate('valor'))->width('14.0em');
		$table->column('op_ligacao')->title('Op2.')->width('3.0em');
//		$table->column('Estado_Regra')->title($oMain->translate('status'))->width('2.0em')->searchable(false);
		$table->column('toperations')->title('!')->width('6.0em')->searchable(false)->sortable(false);

		return $table->html();
	}	

}// Enf of Crulecond


class CRulEquip
{
	var $codigo;    /**  */
	var $numregra;    /**  */
	var $desenho;    /**  */
	var $tipo;    /**  */
	var $data;    /**  */
	var $utilizador;    /**  */
	var $utilizadordesc; 
	var $ordem;    /**  */
	var $quantidade;    /**  */
	var $opvar;    /**  */
	var $variavel;    /**  */
	
	var $product;
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class Crulequip mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='rulequip'; 
		
		$idpopup=$ent.'Popup';
		$idtgrid=$ent.'TGrid';
		

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$html=$oMain->refresh($idtgrid);
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='xnew_'.$ent;
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xedit_'.$ent;
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			if($mod =='edit_'.$ent)
				$this->readFromDB();
			
			
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('edit_rulequip').' - '.$this->codigo.' - '.$this->numregra.' - '.$this->desenho;
				$content=$this->form('update_'.$ent,'xedit_'.$ent);
				$menu='rulequip';
				$tuserid=$oMain->getTuserid($this->utilizador);
				$photo="<img src=\"".$oMain->stdGetUserPicture($tuserid)."\" title=\"".$tuserid."\" height=24>";
				$photo="<img src=\"".$oMain->stdGetUserPicture($this->utilizador)."\" title=\"".$this->utilizador."\" height=24>";
				$footer=$photo.'<BR><b>'.$oMain->translate('modifiedby').' : '.$this->utilizador.' - '.$this->utilizadordesc.' | '.$oMain->translate('modifdate').' : '.$oMain->formatdate($this->data).'</b>';
				
				$footer="<table style=width:25% border=0>
					<tr>
						<td rowspan=2 style=vertical-align:bottom>$photo</td>
						<td style=vertical-align:bottom>".$oMain->translate('modifiedby')." : ".$this->utilizadordesc."<BR>".$oMain->translate('modifdate')." : ".$oMain->formatdate($this->data)."</td>
					</table>";
				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
				
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('update_'.$ent,'xedit_'.$ent);
			}

		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			if(_request('ppajax')!='')
			{
					$title=$oMain->translate('new_rulequip').' - '.$this->codigo.' - '.$this->numregra;
					$content=$this->form('insert_'.$ent,'xnew_'.$ent);
					$menu=$oMain->translate('rulequip');
					$footer='';
					$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
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


		return($html);
	}
	
	 /**
	  * read class Crulequip atributes from request
	  */	
	function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->codigo=$oMain->GetFromArray('codigo',$_REQUEST,'string_trim');
		$this->numregra=$oMain->GetFromArray('numregra',$_REQUEST,'int');
		$this->desenho=$oMain->GetFromArray('desenho',$_REQUEST,'string_trim');
		$this->tipo=$oMain->GetFromArray('tipo',$_REQUEST,'string_trim');
		$this->data=$oMain->GetFromArray('data',$_REQUEST,'date');
		$this->utilizador=$oMain->GetFromArray('utilizador',$_REQUEST,'string_trim');
		$this->ordem=$oMain->GetFromArray('ordem',$_REQUEST,'int');
		$this->quantidade=$oMain->GetFromArray('quantidade',$_REQUEST,'int');
		$this->opvar=$oMain->GetFromArray('opvar',$_REQUEST,'string_trim');
		$this->variavel=$oMain->GetFromArray('variavel',$_REQUEST,'string_trim');
		$this->product=$oMain->GetFromArray('product',$_REQUEST,'string_trim');
	}
	/**
	 * class Crulequip form
	 */	
	function form($mod='show_rulequip',$modChange='')
	{

		$oMain=$this->oMain;
//print $oMain->operation;
		$operation=$oMain->operation;
		//$mod='xxxx_vartype';
		$formName = 'formrulequip'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(3);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		$form->hidden('product', $this->codigo);
		$form->hidden('tipo', $this->tipo);
		
		
		$form->elementAdd(new _htmlInput('codigo', $oMain->translate('codigo'), $this->codigo))->required(true);
		$form->elementAdd(new _htmlInput('numregra', $oMain->translate('numregra'), $this->numregra))->required(true);
		$form->elementAdd(new _htmlInput('ordem', $oMain->translate('ordem'), $this->ordem))->required(true);
		
		if($operation=='product')
		{
			$sql="SELECT desenho, (desenho+' - '+Descricao) as tdesc from bdsigip.dbo.tbQE_EquipPosto where Produto='$this->codigo' order by desenho";
			$temp = $oMain->qSQL()->getDI($sql);
			$select=$form->elementAdd(new _htmlSelect('desenho', $oMain->translate('desenho'), $this->desenho, $temp))->blank(true)->required(true);
			$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
			
			if($this->desenho<>'')
			{
				$sql="select Descricao from bdsigip.dbo.tbQE_EquipPosto where Desenho='$this->desenho'";
				$rs = $oMain->querySQL($sql);
				$desenhodesc=$rs[0]['Descricao'];
			}
		}
		else
		{
			$sql="SELECT DesenhoOp , DesenhoOp as tdesc from bdsigip.dbo.tbQE_VarianteOpcoes WHERE DesenhoVar = '$this->codigo'";
			$temp = $oMain->qSQL()->getDI($sql);
			$select=$form->elementAdd(new _htmlSelect('desenho', $oMain->translate('desenho'), $this->desenho, $temp))->blank(true)->required(true);
			$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());

			if($this->desenho<>'')
			{
				$sql="select Descricao from bdsigip.dbo.tbQE_Opcoes where Desenho='$this->desenho'";
				$rs = $oMain->querySQL($sql);
				$desenhodesc=$rs[0]['Descricao'];
			}
		}
		
		$form->elementAdd(new _htmlInput('desenhodesc', $oMain->translate('desc'), $desenhodesc),null,2);
		
		if($mod=='insert_rulequip')
			if($this->quantidade==0)
				$this->quantidade=1;

		$form->elementAdd(new _htmlFieldsGroup('t1', $this->oMain->translate('extra')));

		$sql="SELECT nome,
					tdesc=CASE WHEN
					DC IS NULL THEN nome
					WHEN
					bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')='Translation unavailable: ' THEN nome
					ELSE
					(bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')+' - '+nome)
					END
				FROM bdsigip.dbo.tbQE_VariaveisDecisao
				WHERE Fonte='CampoNum' and Decisao='S'
				Order by tdesc asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('variavel', $oMain->translate('variavel'), $this->variavel, $temp))->blank(true);
		
		$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
			FROM dbo.tbcodes C  
			WHERE codeid IN ('+','-','*') AND (codetype = 'sigipwebcalc' and tstatus='A') ORDER BY C.torder asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('opvar', $oMain->translate('opatrib'), $this->opvar, $temp))->blank(true);
		
		$form->elementAdd(new _htmlInput('quantidade', $oMain->translate('quantidade'), $this->quantidade))->required(true);
		

		if($mod<>'show_rule')
		{
			$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
			//$button->confirm($oMain->translate('confassociate'));
		
			return $form->html().$button->html();
		}
		else
			return $form->html();

	}

	/**
	 * save class Crulequip record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="bdsigip.[dbo].[spsigrulequip] '$sid','$operation','$this->codigo'
		,'$this->numregra'
		,'$this->desenho'
		,'$this->tipo'
		,'$this->ordem'
		,'$this->quantidade'
		,'$this->opvar'
		,'$this->variavel'
		";
		
		$rs = $oMain->querySQL($sql);
		
		$tstatus=$rs[0]['Erro'];
		$tdesc=$rs[0]['Descricao'];
		
		if($tstatus=='0' and $tdesc=='')
			$tdesc=$oMain->translate('sucess', $oMain->l, '@GERAL@');			

		return($tstatus);

	}
	/**
	 * query to get class Crulequip record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT codigo,num_regra,desenho,tipo,data,utilizador,ordem,quantidade,opvar,variavel
				,dbo.efa_username(Utilizador) as utilizadordesc
			FROM bdsigip.dbo.tbQE_CompRegras 
			WHERE Codigo='$this->codigo' and Num_Regra='$this->numregra' and Desenho='$this->desenho'";		

		return($sql);
	}
	/**
	 * set class Crulequip atributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->codigo=$rst['codigo'];
			$this->numregra=$rst['num_regra'];
			$this->desenho=$rst['desenho'];
			$this->tipo=$rst['tipo'];
			$this->data=$rst['data'];
			$this->utilizador=$rst['utilizador'];
			$this->utilizadordesc=$rst['utilizadordesc'];
			$this->ordem=$rst['ordem'];
			$this->quantidade=$rst['quantidade'];
			$this->opvar=$rst['opvar'];
			$this->variavel=$rst['variavel'];
			
		}
		return $rc;
	}
	
	 /**
	  * advanced Search query to database
	  */
	
	
	function showList()
	{
		$oMain=$this->oMain;
		
		$operation=$oMain->operation;

		
		if($operation=='product')
			$sql="SELECT  C.Codigo,C.Num_Regra, O.Descricao,C.Desenho,C.Tipo, C.Ordem,  C.Quantidade, Opvar=isnull(C.opvar,''), Variavel=isnull(C.Variavel,'')
					, '' as toperations
				FROM  bdsigip.dbo.tbQE_CompRegras C inner join 
				bdsigip.dbo.tbQE_EquipPosto O ON C.Desenho=O.Desenho and C.Codigo=O.Produto
				WHERE (Codigo='$this->codigo' and Num_Regra='$this->numregra')
				order by C.Ordem asc";
		else
			$sql="SELECT  C.Codigo,C.Num_Regra, O.Descricao,C.Desenho,C.Tipo, C.Ordem,  C.Quantidade, Opvar=isnull(C.opvar,''), Variavel=isnull(C.Variavel,'')
				, '' as toperations
			FROM  bdsigip.dbo.tbQE_CompRegras C inner join 
			bdsigip.dbo.tbQE_Opcoes O ON C.Desenho=O.Desenho
			WHERE (Codigo='$this->codigo' and Num_Regra='$this->numregra')
			order by C.Ordem asc";
			
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		
		for ($r = 0; $r < $rc; $r++)
		{
			$desenho=$rs[$r]['Desenho'];
			$desc=$rs[$r]['Descricao'];
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_rulequip', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['Num_Regra'].'&desenho='.$desenho.'&operation='.$operation.'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'"><img src="img/edit_s.png"></a>';
			$link = '<a href="#" onclick="'.$onCLick.'"title="'.$desenho.'">'.$desenho.'</a>';
			$rs[$r]['Desenho']=$link;
			
			
			$rs[$r]['Descricao']= '<p title="'.$desc.'">'.$desc;
			
			
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'del_rulequip', '', 'codigo='.urlencode($rs[$r]['Codigo']).'&numregra='.$rs[$r]['Num_Regra'].'&desenho='.$desenho.'&operation='.$operation.'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['toperations']=$linkedit.' '.$linkdel;

		}

		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$newrulecond=$oMain->baseLink('sigipweb', 'new_rulequip', '', 'codigo='.$this->codigo.'&numregra='.$this->numregra.'&product='.$this->codigo, '', '');
		$newrulecond.= '&operation='.$operation.'&ppajax=1';
		
		$menuObj->addItem('x10', $oMain->translate('new_rulequip'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$newrulecond.'", "script");');

		$image="<img src='img/components_s.png'>";		
		$table = $oMain->_stdTGrid('rulequipTGrid');
		$table->title($image.' '.$oMain->translate('list_rulequip'));
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'list_rulequip', '', '&codigo='.$this->codigo.'&numregra='.$this->numregra.'&operation='.$operation));
//		$table->lineOnClick('_urlContent2DivLoader("'.$this->oMain->baseLink('sigipweb', 'show_rule').'&product='.$this->product.'&variant='.$this->variant.'&codigo=§§Codigo§§&numregra=§§NumRegra§§&ppajax=1", "main");');
		$table->border(0);
		$table->vals($rs);
//		var_dump($rs);
		$table->searchable(false);
		$table->showFixedFooter(false);
		
		//$table->column('img')->title('!')->width('2.0em')->searchable(false)->sortable(false);
		$table->column('Ordem')->title($oMain->translate('Ordem'))->width('3.0em');
		$table->column('Desenho')->title($oMain->translate('Desenho'))->width('7.0em');
		$table->column('Descricao')->title($oMain->translate('Descricao'))->width('25.0em');
//		$table->column('op_ligacao')->title($oMain->translate('op_ligacao'))->width('5.0em');
//		$table->column('Estado_Regra')->title($oMain->translate('status'))->width('2.0em')->searchable(false);
		$table->column('toperations')->title('!')->width('4.0em')->searchable(false)->sortable(false);

		return $table->html();
		
	}	

}// Enf of Crulequip


class CRuleVar
{
	var $codigo;    /**  */
	var $numregra;    /**  */
	var $codregisto;    /**  */
	var $variavel;    /**  */
	var $valor;    /**  */
	var $opatrib;    /**  */
	var $valorvar;    /**  */
	var $opvalorvar;    /**  */
	var $valornum;    /**  */
	var $opvalornum;    /**  */
	
	var $variant;
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class Crulevar mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='rulevar';
		
		$idpopup=$ent.'Popup';
		$idtgrid=$ent.'TGrid';
		//$Codigo=$this->Codigo;

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$html=$oMain->refresh($idtgrid);
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='xnew_'.$ent;
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xedit_'.$ent;
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			if($mod =='edit_'.$ent)
				$this->readFromDB();
			
			
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('edit_rulevar').' - '.$this->codigo.' - '.$this->numregra.' - '.$this->numcondicao;
				$content=$this->form('update_'.$ent,'xedit_'.$ent);
				$menu='rulevar';

				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
				
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('update_'.$ent,'xedit_'.$ent);
			}

		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			if(_request('ppajax')!='')
			{
					$title=$oMain->translate('new_rulevar').' - '.$this->codigo.' - '.$this->numregra;
					$content=$this->form('insert_'.$ent,'xnew_'.$ent);
					$menu=$oMain->translate('rulevar');
					$footer='';
					$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
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


		return($html);
	}
	
	 /**
	  * read class Crulevar atributes from request
	  */	
	function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->codigo=$oMain->GetFromArray('codigo',$_REQUEST,'string_trim');
		$this->numregra=$oMain->GetFromArray('numregra',$_REQUEST,'int');
		$this->codregisto=$oMain->GetFromArray('codregisto',$_REQUEST,'int');
		$this->variavel=$oMain->GetFromArray('variavel',$_REQUEST,'string_trim');
		$this->valor=$oMain->GetFromArray('valor',$_REQUEST,'string_trim');
		$this->opatrib=$oMain->GetFromArray('opatrib',$_REQUEST,'string_trim');
		$this->valorvar=$oMain->GetFromArray('valorvar',$_REQUEST,'string_trim');
		$this->opvalorvar=$oMain->GetFromArray('opvalorvar',$_REQUEST,'string_trim');
		$this->valornum=$oMain->GetFromArray('valornum',$_REQUEST,'string_trim');
		$this->opvalornum=$oMain->GetFromArray('opvalornum',$_REQUEST,'string_trim');
		
		$this->variant=$oMain->GetFromArray('variant',$_REQUEST,'string_trim');
		

	}
	/**
	 * class Crulevar form
	 */	
	function form($mod='show_rulevar',$modChange='')
	{

		$oMain=$this->oMain;
		
		$globalart=$oMain->getRuleType($this->codigo,$this->numregra);
		
		$type=$oMain->getRuleEntity($this->codigo,$this->numregra);

		if($globalart)		
			$operation=$globalart;
		else
			$operation=$oMain->operation;
		
		//print $type.'a';
		
		$formName = 'formrulevar'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(3);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		$form->elementAdd(new _htmlInput('codigo', $oMain->translate('variant'), $this->codigo))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('numregra', $oMain->translate('numregra'), $this->numregra))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('codregisto', $oMain->translate('codigo'), $this->codregisto))->readonly(TRUE);
		
		
		if($globalart)
		{	
			$sql="SELECT nome,
						tdesc=CASE WHEN
						DC IS NULL THEN nome
						WHEN
						bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')='Translation unavailable: ' THEN nome
						ELSE
						(bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')+' - '+nome)
						END
					FROM bdsigip.dbo.tbQE_VariaveisDecisao
					WHERE Decisao='S'
					Order by tdesc asc";
		}
		elseif($type<>'') //variant Rule
		{
			$sql="SELECT nome,
					tdesc=CASE WHEN
					DC IS NULL THEN nome
					WHEN
					bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')='Translation unavailable: ' THEN nome
					ELSE
					(bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')+' - '+nome)
					END
				FROM bdsigip.dbo.tbQE_VariaveisDecisao
				WHERE [Global]='S'
				Order by tdesc asc";
		}
		else //product rule
		{
			$sql="SELECT nome,
					tdesc=CASE WHEN
					DC IS NULL THEN nome
					WHEN
					bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')='Translation unavailable: ' THEN nome
					ELSE
					(bdsigip.dbo.qe_translate_unitext(DC,'$oMain->l')+' - '+nome)
					END
				FROM bdsigip.dbo.tbQE_VariaveisDecisao
				WHERE [GlobalProduto]='S'
				Order by tdesc asc";
		}
		
		
		$temp = $oMain->qSQL()->getDI($sql);
		$select=$form->elementAdd(new _htmlSelect('variavel', $oMain->translate('variavel'), $this->variavel, $temp))->blank(true)->required(true);
		$select->onchange($form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
		
		if($this->variavel<>'')
		{
			//get fonte value: Numeric or Alfannumeric
			$fonte=$oMain->getFonte($this->variavel);
			$atributo=$oMain->getAtributo($this->variavel);
			
		}
		
//		print $fonte;
//		print $atributo;
		
		if($globalart)
		{
			$sql="SELECT codeid, BDCOMUM.dbo.translate_optional(valunitext, '$oMain->l', codeid) AS codetxt
				FROM BDCOMUM.dbo.tbcodes
				WHERE (codetype = 'sig_logoperator')";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('opatrib', $oMain->translate('opatrib'), $this->opatrib, $temp))->blank(true)->required(true);
		}
		else
		{
			if($fonte=='CampoNum')
			{
				$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
				FROM dbo.tbcodes C  
				WHERE codeid IN ('+','=') AND (codetype = 'sigipwebcalc' and tstatus='A') ORDER BY C.torder asc";
				$temp = $oMain->qSQL()->getDI($sql);
				$form->elementAdd(new _htmlSelect('opatrib', $oMain->translate('opatrib'), $this->opatrib, $temp))->blank(true)->required(true);
			}
			else
			{

				if($mod='insert_rulevar' and $this->opatrib=='')
					$this->opatrib='=';

				$form->elementAdd(new _htmlInput('opatrib', $oMain->translate('opatrib'), $this->opatrib))->required(true)->readonly(true);
			}
		}

		if($globalart)
		{
			$sql="SELECT  V.tfield, bdcomum.dbo.translate_unitext(V.unitext,'PT') AS tvalue
				FROM  bdcomum.dbo.tbfeatures V 
				WHERE (V.ttype = 'V') AND (V.tstatus = 'A') 
				ORDER BY bdcomum.dbo.translate_unitext(V.unitext,'$oMain->l') asc";
			$temp = $oMain->qSQL()->getDI($sql);
			$select=$form->elementAdd(new _htmlSelect('valor', $oMain->translate('featureglob'), $this->valor, $temp))->blank(true)->required(true);
			$select->onchange('_val("valorvar", "");'.$form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
		}
		elseif($atributo=='coordenadas')
		{
			$form->elementAdd(new _htmlInput('valor', $oMain->translate('valor'), $this->valor))->required(true); //->validate('float',true)
		}
		else
		{
			//$sql="SELECT valor, valor as tdesc From BDSIGIP.dbo.tbQE_ValoresCampos  where campo='$atributo' order by campo asc ,Ordem asc";
			$sql="exec bdsigip.dbo.spsigfeaturevalues '$oMain->sid', 'list', '$this->variavel', '$this->product', '$this->codigo', '$this->numregra'";
			$temp = $oMain->qSQL()->getDI($sql);
			$select=$form->elementAdd(new _htmlSelect('valor', $oMain->translate('valor'), $this->valor, $temp))->blank(true);
			if($this->valor!='' || $this->valorvar=='') $select->required(true);
			$select->onchange('_val("valorvar", "");'.$form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
		}
		
		//print $fonte;
		if($fonte=='CampoNum')
		{
			$form->elementAdd(new _htmlFieldsGroup('t1', $this->oMain->translate('extra')));
			
			$form->elementAdd(new _htmlHtml('h5', ''), null, 1)->noLayout(true);

			$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
				FROM dbo.tbcodes C  
				WHERE codeid IN ('+','-','*') AND (codetype = 'sigipwebcalc' and tstatus='A') ORDER BY C.torder asc";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('opvalorvar', $oMain->translate('opatrib'), $this->opvalorvar, $temp))->blank(true);

			$conditions='';
			if($oMain->operation=='product')
				$conditions="WHERE Fonte='CampoNum' and Decisao='S' and GlobalProduto='S' order by Nome";
			else
				$conditions="WHERE Fonte='CampoNum' and Decisao='S' order by Nome";
			
			$sql="SELECT nome, nome as tdesc FROM  bdsigip.dbo.tbQE_VariaveisDecisao WHERE Fonte='CampoNum' and Decisao='S' order by Nome";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('valorvar', $oMain->translate('valorvar'), $this->valorvar, $temp))->blank(true);

			$form->elementAdd(new _htmlHtml(''))->noLayout(true);

			$sql="SELECT codeid, dbo.translate_unitext(C.valunitext, '$oMain->l') AS tdsca  
				FROM dbo.tbcodes C  
				WHERE codeid IN ('+','-','*') AND (codetype = 'sigipwebcalc' and tstatus='A') ORDER BY C.torder asc";
			$temp = $oMain->qSQL()->getDI($sql);
			$form->elementAdd(new _htmlSelect('opvalornum', $oMain->translate('opatrib'), $this->opvalornum, $temp))->blank(true);

			$form->elementAdd(new _htmlInput('valornum', $oMain->translate('valornum'), $this->valornum));
		}
		else
		{
			$form->elementAdd(new _htmlHtml('h1', ''), null, 2)->noLayout(true);
			
			$sql="SELECT nome, nome as tdesc FROM  bdsigip.dbo.tbQE_VariaveisDecisao Where Decisao='S' order by Nome";
			$temp = $oMain->qSQL()->getDI($sql);
			$select=$form->elementAdd(new _htmlSelect('valorvar', $oMain->translate('valorvar'), $this->valorvar, $temp))->blank(true);
			$select->onchange('_val("valor", "");'.$form->jsChangeMod($modChange).$form->jsSubmitWithoutValidation());
			if($this->valorvar!='') 
				$select->required(true);
		}

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		$button->confirm($oMain->translate('conf'));
		
		return $form->html().$button->html();
	}

	/**
	 * save class Crulevar record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="bdsigip.[dbo].[spsigrulevar] '$sid','$operation'
		,'$this->codigo'
		,'$this->numregra'
		,'$this->codregisto'
		,'$this->variavel'
		,'$this->valor'
		,'$this->opatrib'
		,'$this->valorvar'
		,'$this->opvalorvar'
		,'$this->valornum'
		,'$this->opvalornum'
		";
		
		$rs = $oMain->querySQL($sql);
		
		$tstatus=$rs[0]['Erro'];
		$tdesc=$rs[0]['Descricao'];
		
		if($tstatus=='0' and $tdesc=='')
			$tdesc=$oMain->translate('sucess', $oMain->l, '@GERAL@');			

		return($tstatus);
		
	}
	/**
	 * query to get class Crulevar record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT VR.codigo,VR.numregra,VR.codregisto,VR.variavel,VR.valor,VR.opatrib,VR.valorvar,VR.opvalorvar,VR.valornum,VR.opvalornum
			FROM bdsigip.dbo.tbQE_VariaveisRegras VR 
			WHERE VR.Codigo='$this->codigo' and VR.NumRegra='$this->numregra' and VR.CodRegisto='$this->codregisto'";		

		return($sql);
	}
	

	/**
	 * set class Crulevar atributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=getRS2($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->codigo=$rst['codigo'];
			$this->numregra=$rst['numregra'];
			$this->codregisto=$rst['codregisto'];
			$this->variavel=$rst['variavel'];
			$this->valor=$rst['valor'];
			$this->opatrib=$rst['opatrib'];
			$this->valorvar=$rst['valorvar'];
			$this->opvalorvar=$rst['opvalorvar'];
			$this->valornum=$rst['valornum'];
			$this->opvalornum=$rst['opvalornum'];
		}
		return $rc;
	}
	
	 /**
	  * advanced Search query to database
	  */
	
	
	function showList()
	{
		$oMain=$this->oMain;
		
		$globalart=$oMain->getRuleType($this->codigo,$this->numregra);
		if($globalart)		
			$operation=$globalart;
		else
			$operation=$oMain->operation;
		
		if($globalart)
			$sql="SELECT codigo,numregra,codregisto,variavel,valor,opatrib,valorvar,opvalorvar,valornum,opvalornum
				,dbo.translate_code('sig_logoperator', opatrib, '$oMain->l') AS opatribdesc
				,(SELECT bdcomum.dbo.translate_unitext(V.unitext,'$oMain->l') FROM  bdcomum.dbo.tbfeatures V WHERE V.ttype = 'V' AND V.tstatus = 'A' and V.tfield=valor) as valordesc
				, '' as toperations
				FROM bdsigip.dbo.tbQE_VariaveisRegras 
				WHERE Codigo='$this->codigo' and NumRegra='$this->numregra'";
		else
			$sql="SELECT codigo,numregra,codregisto,variavel,valor,opatrib,valorvar,opvalorvar,valornum,opvalornum
			, '' as toperations
			FROM bdsigip.dbo.tbQE_VariaveisRegras 
			WHERE Codigo='$this->codigo' and NumRegra='$this->numregra'";
		
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		//$ArrayRst=array();
		for ($r = 0; $r < $rc; $r++)
		{	
			$variavel=$rs[$r]['variavel'];
			//$valor=$rs[$r]['valor'];
			
			
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_rulevar', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&codregisto='.$rs[$r]['codregisto'].'&ppajax=1&operation='.$operation)."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'"><img src="img/edit_s.png"></a>';
			$link = '<a href="#" onclick="'.$onCLick.'">'.$variavel.'</a>';
			
			$rs[$r]['variavel']=$link;
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'del_rulevar', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&numcondicao='.$rs[$r]['numcondicao'].'&codregisto='.$rs[$r]['codregisto'].'&ppajax=1&operation='.$operation)."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['toperations']=$linkedit.' '.$linkdel;

		}

		
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$newrulevar=$oMain->baseLink('sigipweb', 'new_rulevar', $type, 'codigo='.$this->codigo.'&numregra='.$this->numregra.'&operation='.$operation, '', '');
		$newrulevar.= '&ppajax=1';
		
		$menuObj->addItem('x10', $oMain->translate('new_rulevar'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$newrulevar.'", "script");');
//		$menuObj->addItem('x20', $oMain->translate('edit_variant'), _iconSrc('edit'), '', '_urlContent2DivLoader("'.$linkvariant.'", "script");');

//var_dump ($rs);
		$image="<img src='img/settings_s.png'>";
		$table = $oMain->_stdTGrid('rulevarTGrid');
		//$table->titleHide(true);
		$table->title($image.' '.$oMain->translate('list_rulevar').' ('.$operation.')');
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'list_rulevar', '', '&codigo='.$this->codigo.'&numregra='.$this->numregra.'&operation='.$operation));
//		$table->lineOnClick('_urlContent2DivLoader("'.$this->oMain->baseLink('sigipweb', 'show_rule').'&product='.$this->product.'&variant='.$this->variant.'&codigo=§§Codigo§§&numregra=§§NumRegra§§&ppajax=1", "main");');
		$table->border(0);
		$table->vals($rs);
//		var_dump($rs);
		$table->searchable(false);
		$table->showFixedFooter(false);
		
		if($globalart)
		{
			$table->column('variavel')->title($oMain->translate('variavelsig'))->width('20.0em');
			$table->column('opatribdesc')->title('Op.')->width('5.0em');
			$table->column('valordesc')->title($oMain->translate('featureglob'))->width('10.0em');
			$table->column('toperations')->title('!')->width('4.0em')->searchable(false)->sortable(false);
		}
		else
		{
			$table->column('variavel')->title($oMain->translate('variavel'))->width('20.0em');
			$table->column('opatrib')->title('Op.')->width('5.0em');
			$table->column('valor')->title($oMain->translate('valor'))->width('10.0em');
			$table->column('toperations')->title('!')->width('4.0em')->searchable(false)->sortable(false);
		}
		

		return $table->html();
	}	

}// Enf of CRuleVar


class CRuleTask
{
	var $codigo;    /**  */
	var $numregra;    /**  */
	var $tarefa;    /**  */
	var $grupo;    /**  */
	var $duracao;    /**  */
	var $prioridade;    /**  */
	var $obs;    /**  */
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class Cruletask mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='ruletask'; 
		
		$idpopup=$ent.'Popup';
		$idtgrid=$ent.'TGrid';

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$html=$oMain->refresh($idtgrid);
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='xnew_'.$ent;
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xedit_'.$ent;
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			if($mod =='edit_'.$ent)
				$this->readFromDb();
			
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('edit_ruletask').' - '.$this->codigo.' - '.$this->numregra.' - '.$this->tarefa;
				$content=$this->form('update_'.$ent,'xedit_'.$ent);
				$menu='ruletask';

				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
				
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('update_'.$ent,'xedit_'.$ent);
			}

		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('new_ruletask').' - '.$this->codigo.' - '.$this->numregra;
				$content=$this->form('insert_'.$ent,'xnew_'.$ent);
				$menu=$oMain->translate('ruletask');
				$footer='';
				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
			}
			else 
			{
				$oMain->subtitle=$oMain->translate('show_'.$ent);
				$html=$this->form('insert_'.$ent,'xnew_'.$ent);
			}
		}

		if ($mod =='list_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod).' '.$Codigo;
			$html=$this->showList();
		}


		return($html);
	}
	
	 /**
	  * read class Cruletask atributes from request
	  */	
	function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->codigo=$oMain->GetFromArray('codigo',$_REQUEST,'string_trim');
		$this->numregra=$oMain->GetFromArray('numregra',$_REQUEST,'int');
		$this->tarefa=$oMain->GetFromArray('tarefa',$_REQUEST,'string_trim');
		$this->grupo=$oMain->GetFromArray('grupo',$_REQUEST,'string_trim');
		$this->duracao=$oMain->GetFromArray('duracao',$_REQUEST,'int');
		$this->prioridade=$oMain->GetFromArray('prioridade',$_REQUEST,'string_trim');
		$this->obs=$oMain->GetFromArray('obs',$_REQUEST,'string_trim');
		

	}
	/**
	 * class Cruletask form
	 */	
	function form($mod='show_ruletask',$modChange='')
	{

		$oMain=$this->oMain;
		
		$operation='';
		//$mod='xxxx_vartype';
		$formName = 'formruletask'.rand();
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(2);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		$form->hidden('codigo', $this->codigo);
		$form->hidden('numregra', $this->numregra);
		
		
		$sql="SELECT codigo
			, Descricao = CASE WHEN
			uxdesc IS NULL THEN Descricao
			WHEN
			dbo.translate_unitext_exact(uxdesc, '$oMain->l')='' THEN Descricao
			ELSE
			(dbo.translate_unitext_exact(uxdesc, '$oMain->l')+' - ('+Descricao+')')
			END
			FROM bdsigip.dbo.[tbQE_Tarefas] 
			order by Descricao";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('tarefa', $oMain->translate('tarefa'), $this->tarefa, $temp))->blank(true)->required(true);
		
		$sql="SELECT groupid as Grupo,groupdesc as Descricao FROM tbgroups WHERE company='453' and  application='qe' and profile='tarefas' Order by Descricao";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('grupo', $oMain->translate('grupo'), $this->grupo, $temp))->blank(true)->required(true);
		
		
		$form->elementAdd(new _htmlInput('duracao', $oMain->translate('duracao'), $this->duracao))->required(true);
		
		$Array=array(1=>'1',2=>'2',3=>'3');
		$form->elementAdd(new _htmlSelect('prioridade', $oMain->translate('prioridade'), $this->prioridade, $Array))->blank(true)->required(true);

		$form->elementAdd(new _htmlTextarea('obs', $oMain->translate('obs'), $this->obs),null,3);
		

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		$button->confirm($oMain->translate('conf'));
		
		return $form->html().$button->html();
		
	}

	/**
	 * save class Cruletask record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$sql="bdsigip.[dbo].[spsigruletask] '$sid','$operation'
		,'$this->codigo'
		,'$this->numregra'
		,'$this->tarefa'
		,'$this->grupo'
		,'$this->duracao'
		,'$this->prioridade'
		,'$this->obs'
		";
		
		$rs = $oMain->querySQL($sql);
		
		$tstatus=$rs[0]['Erro'];
		$tdesc=$rs[0]['Descricao'];
		
		if($tstatus=='0' and $tdesc=='')
			$tdesc=$oMain->translate('sucess', $oMain->l, '@GERAL@');			

		return($tstatus);
	}
	/**
	 * query to get class Cruletask record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT Codigo,NumRegra,Tarefa,Grupo,Duracao,Prioridade,Obs 
			FROM bdsigip.dbo.tbQE_Regras_Tarefas 
			WHERE Codigo='$this->codigo' and NumRegra='$this->numregra' and Tarefa='$this->tarefa'";		

		return($sql);
	}
	/**
	 * set class Cruletask atributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->codigo=$rst['Codigo'];
			$this->numregra=$rst['NumRegra'];
			$this->tarefa=$rst['Tarefa'];
			$this->grupo=$rst['Grupo'];
			$this->duracao=$rst['Duracao'];
			$this->prioridade=$rst['Prioridade'];
			$this->obs=$rst['Obs'];
		}
		return $rc;
	}
	
	
	function showList()
	{
		$oMain=$this->oMain;

		$sql="select TR.codigo, TR.numregra, TR.tarefa, TR.grupo, TR.duracao, TR.prioridade, TR.obs, T.descricao
				,dbo.translate_unitext_exact(T.uxdesc, '$oMain->l') AS tdesc
			from bdsigip.dbo.tbQE_Regras_Tarefas TR INNER JOIN
			bdsigip.dbo.tbQE_Tarefas T
			ON T.codigo=TR.tarefa
			where (TR.codigo='$this->codigo' and TR.numregra='$this->numregra')";
		
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		$ArrayRst=array();
		for ($r = 0; $r < $rc; $r++)
		{
			
			$descricao=$rs[$r]['descricao'];
			$tdesc=$rs[$r]['tdesc'];
			
			if($tdesc=='')
				$rs[$r]['descricao']=$descricao;
			else
				$rs[$r]['descricao']=$tdesc.' - ('.$descricao.')';
			
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_ruletask', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&tarefa='.$rs[$r]['tarefa'].'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'"><img src="img/edit_s.png"></a>';
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'del_ruletask', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&tarefa='.$rs[$r]['tarefa'].'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['toperations']=$linkedit.' '.$linkdel;
			
		}
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$newrulecond=$oMain->baseLink('sigipweb', 'new_ruletask', '', 'codigo='.$this->codigo.'&numregra='.$this->numregra, '', '');
		$newrulecond.= '&ppajax=1';
		
		$menuObj->addItem('x10', $oMain->translate('new_ruletask'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$newrulecond.'", "script");');
//		$menuObj->addItem('x20', $oMain->translate('edit_variant'), _iconSrc('edit'), '', '_urlContent2DivLoader("'.$linkvariant.'", "script");');

//var_dump ($rs);
		$image="<img src='img/tarefas_s.png'>";
		$table = $oMain->_stdTGrid('ruletaskTGrid');
		//$table->titleHide(true);
		$table->title($image.' '.$oMain->translate('list_ruletask'));
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'list_ruletask', '', '&codigo='.$this->codigo.'&numregra='.$this->numregra));
//		$table->lineOnClick('_urlContent2DivLoader("'.$this->oMain->baseLink('sigipweb', 'show_rule').'&product='.$this->product.'&variant='.$this->variant.'&codigo=§§Codigo§§&numregra=§§NumRegra§§&ppajax=1", "main");');
		$table->border(0);
		$table->vals($rs);
//		var_dump($rs);
		$table->searchable(false);
		$table->showFixedFooter(false);
		
//		$table->column('img')->title('!')->width('2.0em')->searchable(false)->sortable(false);
		$table->column('descricao')->title($oMain->translate('descricao'))->width('20.0em');
		$table->column('toperations')->title('!')->width('4.0em')->searchable(false)->sortable(false);

		return $table->html();

	}	

}// Enf of Cruletask


class CRuleQuestion
{
	var $codigo;    /**  */
	var $numregra;    /**  */
	var $valor;    /**  */
	var $ordem;    /**  */
	var $dc;    /**  */
	var $valorantigo;
	
	var $desc;
	var $lang;
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class rulequestion mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='rulequestion'; 
		
		$idpopup=$ent.'Popup';
		$idtgrid=$ent.'TGrid';

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$html=$oMain->refresh($idtgrid);
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='xnew_'.$ent;
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			if($tstatus==0)
			{
				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else
				$mod='xedit_'.$ent;
		}

		if ($mod =='edit_'.$ent || $mod =='xedit_'.$ent)
		{
			if($mod =='edit_'.$ent)
				$this->readFromDb();
	
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('edit_rulequestion').' - '.$this->codigo.' - '.$this->numregra.' - '.$this->valor;
				$content=$this->form('update_'.$ent,'xedit_'.$ent);
				$menu='rulecond';
				$footer='';
				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
				
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('update_'.$ent,'xedit_'.$ent);
			}

		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			if(_request('ppajax')!='')
			{
				$title=$oMain->translate('new_rulequestion');
				$content=$this->form('insert_'.$ent,'xnew_'.$ent);
				$menu='rulequestion';
				$footer='';
				$html=$oMain->popupOpen($idpopup, $title, $content, $footer, $menu);
			}
			else 
			{
				$oMain->subtitle=$oMain->translate('show_'.$ent);
				$html=$this->form('insert_'.$ent,'xnew_'.$ent);
			}
		}

		if ($mod =='list_'.$ent)
		{
			//$oMain->subtitle=$oMain->translate($mod).' '.$Codigo;
			$html=$this->showList();
		}

		if ($mod =='show_'.$ent)
		{
			$this->readFromDb();
			$oMain->subtitle=$oMain->translate('show_'.$ent).' '.$Codigo;
			$html=$this->form('show_'.$ent);
		}
		
		
		
		
		if ($mod =='listcomdesc_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showListComDesc();
		}
		
		
		if ($mod =='delcomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('deleteunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$idtgrid='rqcomdescTGrid';
			$html=$oMain->refresh($idtgrid);

		}
		
		if ($mod =='insertcomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insertunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$idpopup='rqcomdescPopup';
				$idtgrid='rqcomdescTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);
			}
			else 
				$mod='xnewcomdesc_'.$ent;

		}
		
		if ($mod =='updatecomdesc_'.$ent)
		{
			$tstatus=$this->storeIntoDB('updateunitext', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			if($tstatus==0)
			{
				$idpopup='rqcomdescPopup';
				$idtgrid='rqcomdescTGrid';

				$html=$oMain->closeandrefresh($idpopup, $idtgrid);

			}
			else
				$mod='xeditcomdesc_'.$ent;
		}
		
		if ($mod =='editcomdesc_'.$ent || $mod =='xeditcomdesc_'.$ent)
		{
			
			
			if($mod =='editcomdesc_'.$ent)
				$this->readfromdbComDesc('getunitext');
			
			
			if(_request('ppajax')!='')
			{
				$id='rqcomdescPopup';
				$title=$oMain->translate('editcomdesc').' - '.$this->codigo.' | '.$this->numregra.' | '.$this->valor;
				$content=$this->formComDesc('updatecomdesc_'.$ent,'xeditcomdesc_'.$ent);
				$menu='comdesc_rulequestion';
				$html=$oMain->popupOpen($id, $title, $content, '', $menu);
			}
			else 
			{
				$oMain->subtitle=$oMain->translate($mod);
				$html=$this->form('updatecompdesc_'.$ent,'xeditcompdesc_'.$ent);
			}


		}
		
		if ($mod =='newcomdesc_'.$ent or $mod =='xnewcomdesc_'.$ent)
		{
			
			if(_request('ppajax')!='')
			{
				$id='rqcomdescPopup';
				$title=$oMain->translate('newcomdesc').' - '.$this->codigo.' | '.$this->numregra.' | '.$this->valor;
				$content=$this->formComDesc('insertcomdesc_'.$ent,'xnewcomdesc_'.$ent);
				$menu=$oMain->translate('rulequestion');
				$html=$oMain->popupOpen($id, $title, $content, '', $menu);
			}
			else 
			{
				$oMain->subtitle=$oMain->translate('show_'.$ent);
				$html=$this->form('insert_'.$ent,'xnew_'.$ent);
			}
		}

		return($html);
	}
	
	 /**
	  * read class rulequestion atributes from request
	  */	
	function readFromRequest()
	{
		$oMain = $this->oMain;
		$this->codigo=$oMain->GetFromArray('codigo',$_REQUEST,'string_trim');
		$this->numregra=$oMain->GetFromArray('numregra',$_REQUEST,'int');
		$this->valor=$oMain->GetFromArray('valor',$_REQUEST,'string_trim');
		$this->ordem=$oMain->GetFromArray('ordem',$_REQUEST,'int');
		$this->dc=$oMain->GetFromArray('dc',$_REQUEST,'string_trim');
		
		$this->desc=$oMain->GetFromArray('desc',$_REQUEST,'string_trim');
		$this->lang=$oMain->GetFromArray('lang',$_REQUEST,'string_trim');
		
		$this->valorantigo=$oMain->GetFromArray('valorantigo',$_REQUEST,'string_trim');
		

	}
	/**
	 * class rrulequestion form
	 */	
	
	function form($mod='show_rulequestion',$modChange='')
	{

		$oMain=$this->oMain;
		
		$operation=$oMain->operation;
		//$mod='xxxx_vartype';
		$formName = 'formrulequestion';
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(1);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		$form->hidden('ordem', $this->ordem);
		$form->hidden('desc', $this->valor);
		
		if($mod=='update_rulequestion')
		{
			$this->valorantigo=$this->valor;
			$form->hidden('valorantigo', $this->valorantigo);
		}
		
		
		
		$form->elementAdd(new _htmlInput('codigo', $oMain->translate('codigo'), $this->codigo))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('numregra', $oMain->translate('numregra'), $this->numregra))->readonly(TRUE);
		$form->elementAdd(new _htmlInput('valor', $oMain->translate('valor'), $this->valor))->required(true);

		$button=(_button('', $oMain->translate('save'), _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		$button->confirm($oMain->translate('conf'));
		
		return $form->html().$button->html();
		
	}
	

	function formComDesc($mod='showcomdesc_rulequestion', $modChange='')
	{
		$oMain=$this->oMain;
//var_dump($_REQUEST);
		$operation='';
		$formName = 'formcomdescrq';
		$form = $oMain->_stdForm($formName, $mod, $operation);
		$form->cols(2);
		$form->ajax(true)->ajaxDiv('script');
		
		$errormessage=$oMain->translate('errorfields');
		$form->errorMessage($errormessage);
		
		//$form->hidden('id', $this->id);
		$form->hidden('codigo', $this->codigo);
		$form->hidden('numregra', $this->numregra);
		$form->hidden('valor', $this->valor);
//var_dump ($this->lang);		
		$sql="SELECT RTRIM(Codigo), Descricao FROM dbo.tbIdiomas ORDER BY Descricao asc";
		$temp = $oMain->qSQL()->getDI($sql);
		$form->elementAdd(new _htmlSelect('lang', $oMain->translate('idioma'), $this->lang, $temp))->blank(true)->required(true);
		
		$form->elementAdd(new _htmlTextarea('desc', $oMain->translate('desc'), $this->desc),null,2)->required(true)->maxlength(1000);

		$button=(_button('', $oMain->translate('save'),  _iconSrc('save'), '', '_formSubmit("'.$formName.'");'));
		//$button->confirm($oMain->translate('confassociate'));
		
		return $form->html().$button->html();
	}

	/**
	 * save class rulequestion record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain = $this->oMain;
		$sid=$oMain->sid;
		$modifnum=0;// not being used
		
		if($this->lang=='')
			$this->lang=$oMain->l;
			
		$sql="bdsigip.[dbo].[spsigrulequestion] '$sid','$operation', '$modifnum'
		,'$this->codigo'
		,'$this->numregra'
		,'$this->valor'
		,'$this->valorantigo'
		,'$this->desc'
		,'$this->lang'
		";
		
		$rs = $oMain->querySQL($sql);
//var_dump($rs);
		$tstatus=$rs[0]['Erro'];
		$tdesc=$rs[0]['Descricao'];
		
		if($tstatus=='0' and $tdesc=='')
			$tdesc=$oMain->translate('sucess', $oMain->l, '@GERAL@');			

		return($tstatus);
	}
	/**
	 * query to get class rulequestion record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT  Codigo,NumRegra,Valor,Ordem,DC 
			FROM bdsigip.dbo.TBQE_Valores_Pergunta 
			WHERE Codigo='$this->codigo' and NumRegra='$this->numregra' and Valor='$this->valor'";		

		return($sql);
	}
	/**
	 * set class rulequestion atributes with data from database
	 */	
	function readFromDb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=getRS2($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->codigo=$rst['Codigo'];
			$this->numregra=$rst['NumRegra'];
			$this->valor=$rst['Valor'];
			$this->ordem=$rst['Ordem'];
			$this->dc=$rst['DC'];
			
		}
		return $rc;
	}
	
	
	function readfromdbComDesc()
	{
		$oMain = $this->oMain;

		$sql="[bdsigip].[dbo].[spsigrulequestion] '$oMain->sid', 'getunitext', '0', '$this->codigo', '$this->numregra', '$this->valor', '', '', '$this->lang'";
		$rs = $oMain->querySQL($sql);
//var_dump($rs);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->desc=$rst['Descricao'];
		}

		return $rc;
	}
	
	 /**
	  * advanced Search query to database
	  */

	function showList()
	{
		$oMain=$this->oMain;
		$sql="SELECT codigo, numregra, valor, ordem, dc
			FROM bdsigip.dbo.TBQE_Valores_Pergunta
			Where codigo='$this->codigo' and numregra='$this->numregra' 
			order by ordem asc";

		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		//$ArrayRst=array();
		for ($r = 0; $r < $rc; $r++)
		{		
			$variavel=$rs[$r]['variavel'];
			
			$valor=$rs[$r]['valor'];
//			
//			$rs[$r]['valor']= '<p title="'.$valor.'">'.$valor;
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_rulequestion', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&valor='.$valor.'&product='.$this->product.'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'"><img src="img/edit_s.png"></a>';
			$link = '<a href="#" onclick="'.$onCLick.'" title="'.$valor.'">'.$valor.'</a>';
			$rs[$r]['valor']=$link;
			
			
			$onCLickcdesc = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'listcomdesc_rulequestion', '', 'codigo='.$this->codigo.'&numregra='.$this->numregra.'&valor='.$valor.'&ppajax=1&operation='.$oMain->operation)."', 'main');";
			$linkcomdesc = '<a href="#" onclick="'.$onCLickcdesc.'" title="'.$oMain->translate('comdesc').'"><img src="img/translate_s.png"></a>';
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'edit_rulequestion', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&valor='.$valor.'&product='.$this->product.'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'"><img src="img/edit_s.png"></a>';
			$link = '<a href="#" onclick="'.$onCLick.'">'.$variavel.'</a>';
			$rs[$r]['variavel']=$link;
			
			$onCLickdel = "if(confirm('".$oMain->translate('delete')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'del_rulequestion', '', 'codigo='.urlencode($rs[$r]['codigo']).'&numregra='.$rs[$r]['numregra'].'&valor='.$valor.'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';
			
			$rs[$r]['toperations']=$linkcomdesc.' '.$linkedit.' '.$linkdel;

		}
		
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		$newrulecond=$oMain->baseLink('sigipweb', 'new_rulequestion', '', 'codigo='.$this->codigo.'&numregra='.$this->numregra.'&product='.$this->product, '', '');
		$newrulecond.= '&ppajax=1';
		
//		$linkvariant=$oMain->baseLink('sigipweb', 'show_variant', '', 'product='.$this->product.'&vartype='.$this->vartype.'&variant='.$this->variant, '', '');
//		$linkvariant.= '&ppajax=1';
		
		$menuObj->addItem('x10', $oMain->translate('new_rulequestion'), _iconSrc('add'), '', '_urlContent2DivLoader("'.$newrulecond.'", "script");');
//		$menuObj->addItem('x20', $oMain->translate('edit_variant'), _iconSrc('edit'), '', '_urlContent2DivLoader("'.$linkvariant.'", "script");');

//var_dump ($rs);
		$image="<img src='img/rulequest_s.png'>";
		$table = $oMain->_stdTGrid('rulequestionTGrid');
		//$table->titleHide(true);
		$table->title($image.' '.$oMain->translate('list_rulequestion'));
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'list_rulequestion', '', '&codigo='.$this->codigo.'&numregra='.$this->numregra));
//		$table->lineOnClick('_urlContent2DivLoader("'.$this->oMain->baseLink('sigipweb', 'show_rule').'&product='.$this->product.'&variant='.$this->variant.'&codigo=§§Codigo§§&numregra=§§NumRegra§§&ppajax=1", "main");');
		$table->border(0);
		$table->vals($rs);
//		var_dump($rs);
		$table->searchable(false);
		//$table->showFixedFooter(false);
		

		$table->column('valor')->title($oMain->translate('valor'))->width('25.0em');
		$table->column('toperations')->title('!')->width('4.0em')->searchable(false)->sortable(false);

		return $table->html();
		
	}
	
	
	function showListComDesc()
	{
		$oMain=$this->oMain;
		$sql="[bdsigip].[dbo].[spsigrulequestion] '$oMain->sid', 'listunitext', '0', '$this->codigo', '$this->numregra', '$this->valor'";
		$rs = $oMain->querySQL($sql);
		$rc=count($rs);
		$ArrayRst=array();
		$elementos=0;
		for ($r = 0; $r < $rc; $r++)
		{
			//$linkdel=$oMain->stdImglink('delcomdesc_valcamp', '','','campo='.$this->campo.'&valor='.$this->valor.'&lang='.$rs[$r]['Idioma'],'img/delete_s.png','','', $oMain->translate('del_comdesc'), $oMain->translate('del_comdesc'));
			
			$onCLick = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'editcomdesc_rulequestion', '', 'product='.urlencode($this->product).'&vartype='.urlencode($this->vartype).'&lang='.$rs[$r]['Idioma'].'&codigo='.$this->codigo.'&numregra='.$this->numregra.'&valor='.$this->valor.'&ppajax=1')."', 'script');";
			$linkedit = '<a href="#" onclick="'.$onCLick.'" title="'.$oMain->translate('edit').'"><img src="img/edit_s.png"></a>';
			
			$link = '<a href="#" onclick="'.$onCLick.'" title="'.$oMain->translate('edit').'">'.$rs[$r]['Descricao'].'</a>';
			
			$onCLickdel = "if(confirm('".$oMain->translate('delcomdesc_rulequestion')."')) _urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'delcomdesc_rulequestion', '', 'product='.urlencode($this->product).'&vartype='.urlencode($this->vartype).'&lang='.$rs[$r]['Idioma'].'&codigo='.$this->codigo.'&numregra='.$this->numregra.'&valor='.$this->valor.'&ppajax=1')."', 'script');";
			$linkdel = '<a href="#" onclick="'.$onCLickdel.'" title="'.$oMain->translate('delete').'"><img src="img/delete_s.png"></a>';

			$ArrayRst[$elementos]['img']='<img src="img/translate_s.png">';
			$ArrayRst[$elementos]['ID']=$rs[$r]['ID'];
			$ArrayRst[$elementos]['Idioma']=$rs[$r]['Idioma'];
			$ArrayRst[$elementos]['Descricao']=$link;
			$ArrayRst[$elementos]['NomeIdioma']=$rs[$r]['NomeIdioma'];
			$ArrayRst[$elementos]['toperations']=$linkedit.' '.$linkdel;

			$elementos++;
		}
		
		$menuObj = new _menuMaker('');
		$menuObj->create('mlevel');
		$menuObj->menu->cBackground(false);
		
		//$linkprod=$oMain->baseLink('products', 'show_products', '', 'tprod='.$this->tprod, '', '');
		
		$linknewcomdesc=$oMain->baseLink('sigipweb', 'newcomdesc_rulequestion', '', '&codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&valor='.$this->valor, '', '');
		$linknewcomdesc.= '&ppajax=1';
		$linknewcomdesc = '_urlContent2DivLoader("'.$linknewcomdesc.'", "script")';


		
		if($oMain->operation=='product')
		{
			$onCLickback = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'show_prodrule', '', '&product='.urlencode($this->product).'&codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&ppajax=1')."', 'main');";
			$tradrule=$oMain->translate('prodrule');
		}
		else
		{
			$onCLickback = "_urlContent2DivLoader('".$oMain->baseLink('sigipweb', 'show_rule', '', '&product='.urlencode($this->product).'&vartype='.urlencode($this->vartype).'&variant='.urlencode($this->variant).'&codigo='.urlencode($this->codigo).'&numregra='.$this->numregra.'&ppajax=1')."', 'main');";
			$tradrule=$oMain->translate('rule');
		}
		
		
		
		$menuObj->addItem('x10', $tradrule, 'img/back_s.png', '',$onCLickback);
		$menuObj->addItem('x20', $oMain->translate('new_comdesc'), 'img/new_s.png', '', $linknewcomdesc);

		$table = $oMain->_stdTGrid('rqcomdescTGrid');
		$image="<img src='img/translate_s.png'>";
		$table->title($image.' '.$oMain->translate('list_comdesc').' | '.$oMain->translate('rulequestion').' - '.$this->codigo.' | '.$this->numregra.' | '.$this->valor);
		$table->menu($menuObj->html());
		$table->updateLink($oMain->baseLink('sigipweb', 'listcomdesc_rulequestion', '', '&codigo='.$this->codigo.'&numregra='.$this->numregra.'&valor='.$this->valor));
		$table->border(0);
		$table->vals($ArrayRst);
//		$table->searchable(false);
//		$table->showFixedFooter(false);

		$table->column('img')->title('!')->width('2.0em')->searchable(false)->sortable(false);
		$table->column('Descricao')->title($oMain->translate('desc'))->width('35.0em');
		$table->column('NomeIdioma')->title($oMain->translate('idioma'))->width('15.0em');
		$table->column('toperations')->title('!')->width('3.0em')->searchable(false)->sortable(false);
		

		return $table->html();
	}

}// Enf of Crulequestion

?>
