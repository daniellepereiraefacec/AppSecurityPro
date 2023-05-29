<?php

class CHelp
{
	var $tdesc;
	
	
	function  __construct($oMain,$context='')
	{
		$this->oMain=$oMain;
		if($context!='')	{ $this->CONTEXT=$context; }
		else	{ $this->CONTEXT = trim($oMain->getFromArray("CONTEXT",$_REQUEST)); }
	}

	/**
	 * set class CLineItems mod
	 */	
	function getHtml($mod, $completeLayout=true)
	{
		$oMain=$this->oMain;
		//$ent='lineitems';
		
		$sql="SELECT dbo.translate_unitext([unitext],'$oMain->l') as tdesc
			FROM dbo.tbpages AS PAGS 
			WHERE PAGS.page = '$this->CONTEXT'";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$this->tdesc=$rs[0]['tdesc'];
		
		if($mod=='asktraining')
		{
			$this->askTraining();
		}
		
		$html=$this->dashboard();
		

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
           // $this->toolbar($mod,$x->toolbar);     
			//use menuTree create a tree in layout
            //$x->add($this->menuTree($mod));
            $x->add($html);
            return $x->html();          
        }
	/**
	 * set class tree
	 */	
   
	/**
	 * set class toolbar
	 */	        
//        protected function toolbar($mod,$maintoolbar)
//        {
//            $oMain=$this->oMain;
//            if($mod=='show_lineitems')
//            {
//                $maintoolbar->add('edit_lineitems')->link($oMain->BaseLink('','edit_lineitems','','&companhia='.$this->companhia.'&linhamon='.$this->linhamon.'&titemvalue='.$this->titemvalue))->title($oMain->translate('edit_lineitems'))->tooltip($oMain->translate('edit_lineitems'))->efaCIcon('edit.png');                          
//            }
//        }
            
	 /**
	  * read class CLineItems atributes from request
	  */	
//	protected function readFromRequest()
//	{
//		$oMain = $this->oMain;
////		$this->companhia=$oMain->GetFromArray('companhia',$_REQUEST,'string_trim');
////		$this->linhamon=$oMain->GetFromArray('linhamon',$_REQUEST,'string_trim');
////		$this->titemvalue=$oMain->GetFromArray('titemvalue',$_REQUEST,'string_trim');
////		$this->tmodifiedby=$oMain->GetFromArray('tmodifiedby',$_REQUEST,'string_trim');
////		$this->tmodifdate=$oMain->GetFromArray('tmodifdate',$_REQUEST,'string_trim');
//		
//
//	}
	
	function dashboard()
	{
		$oMain=$this->oMain;
		
		$x = new efalayout($oMain);	
		$x->title($title);
		$x->pattern('4X');
		$x->add($this->moduleManagers())->padding(15);
		$x->add($this->moduleInfoHelp($email))->padding(15);
		$x->add($this->moduleDocuments($this->CONTEXT))->padding(15);
		$x->add($this->info($email))->padding(15);
		return $x->html();	
	}
	
	function moduleDocuments($refa)
	{
		$oMain=$this->oMain;
		$array=array();

		$sql="SELECT RFDOC.tcompany, RFDOC.tscope, RFDOC.ttype, RFDOC.trefid, RFDOC.tdocid, REVDOC.trevorder, RFDOC.tcreatedby, UPL.docname,
			dbo.efa_username(RFDOC.tcreatedby) AS creatname, RFDOC.tcreatedon, DOCS.tname, REVDOC.tfileid, REVDOC.tversion,
			ARPATH.tvalue AS tpath, FV.tcreatedon as tcreatedate
		FROM dbo.tbshrefdocs AS RFDOC INNER JOIN
			dbo.tbshdocs AS DOCS ON RFDOC.tdocid = DOCS.tdocid  INNER JOIN
			dbo.tbshdocfvs AS REVDOC ON RFDOC.tdocid = REVDOC.tdocid AND REVDOC.trevorder=(SELECT MAX(trevorder) FROM dbo.tbshdocfvs WHERE tdocid=REVDOC.tdocid) INNER JOIN
			dbo.tbshfvs AS FV ON REVDOC.tfileid = FV.tfileid INNER JOIN
			dbo.tbdocs_upload AS UPL ON FV.company = UPL.company AND FV.docnumber = UPL.docnumber LEFT OUTER JOIN
			dbo.tbshdocattr AS ARPATH ON (ARPATH.tdocid = RFDOC.tdocid) AND (tfield = 'PATH')
		WHERE (RFDOC.tcompany='efa') AND (RFDOC.tscope = 'profiler') AND (RFDOC.trefid = '$refa') ORDER BY FV.tcreatedon DESC";

		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);

		for ($j = 0; $j < $rc; $j++)
		{
			$rst=$rs[$j];

			$crtdate = $oMain->formatDate($rst['tcreatedate']);

			$doclink = "<a href=\"efa.php?page=shareplace&mod=shareplaceDownloadFile&comp=efa&tdocid=$rst[tdocid]&trevorder=$rst[trevorder]&tfileid=$rst[tfileid]&tversion=$rst[tversion]&l=$oMain->l&sid=$oMain->sid\" target=\"_blank\">
			$rst[docname]</a>";

			$img_ext = mb_substr($rst['docname'], mb_strrpos($rst['docname'], '.') + 1);
			$img = './img/shareplace/'.$img_ext.'_s.png';
			if (!file_exists($img))
				$img = './img/shareplace/unknown_s.png';

			$img_link = "<a href=\"efa.php?page=shareplace&mod=shareplaceDownloadFile&comp=efa&tdocid=$rst[tdocid]&trevorder=$rst[trevorder]&tfileid=$rst[tfileid]&tversion=$rst[tversion]&l=$oMain->l&sid=$oMain->sid\" target=\"_blank\">
			<img src=\"$img\" border=0 title=\"$rst[docname]\" alt=\"$rst[docname]\"></a>";

			$array[]= array('img'		=> $img_link,
							'tname'		=> $doclink,
							'crtdate'	=> $crtdate
							);
		}

		$oTable = new efaGrid($oMain);
		$oTable->skin('dhx_web');
		$oTable->skin('efaweb');
		$img=efaImg('shareplace_s.png','');
		$oTable->title($img.' '.$oMain->translate('moduledocuments'));
		$oTable->height(190);               
		$oTable->data($array);
		$oTable->multilineRow(true); //in case of large text fields shows all text
		$oTable->widthUsePercent(true); //set percentage as unit to set with of columns
		$oTable->autoExpandHeight(true);
		$oTable->searchable(false);
		$oTable->exportToExcel(false);  // if true enables icon to export data to excel
		$oTable->exportToPdf(false);    // if true enables icon to export data to pdf

		$oTable->columnAdd('img')->title('!')->width(5);
		$oTable->columnAdd('tname')->title($oMain->translate('name'))->width(75);
		$oTable->columnAdd('crtdate')->title($oMain->translate('creation'))->type('date')->width(20);

		$html.=$oTable->html();                

		return($html);

	
	}

	function moduleManagers()
	{
		$oMain=$this->oMain;
		
		$sql="
			SELECT USR.username, USR.email, USR.employee
			FROM dbo.tbpagemanagers AS PAGM LEFT OUTER JOIN
				 dbo.tbusers AS USR ON PAGM.manager = USR.userid
			WHERE USR.tstatus='A' and (PAGM.page = '$this->CONTEXT' AND PAGM.company='$oMain->comp')
				and USR.employee not in (
						SELECT tuserid
						FROM 
							dbo.tbgroups AS GRP 
							INNER JOIN dbo.tbgroupmember AS MEM ON GRP.tgpid = MEM.tgpid
						WHERE 
							GRP.company = 'efa' 
							AND ((GRP.application = 'Organization'  AND GRP.groupid = 'ADMIN_IS') OR (GRP.application = 'IS'  AND GRP.groupid = 'WEB'))
						)
		UNION
			SELECT USR.username, USR.email, USR.employee
			FROM dbo.tbaccesses AS PAGM INNER JOIN
				 dbo.tbusers AS USR ON PAGM.userid = USR.userid
			WHERE USR.tstatus='A' and (PAGM.company = '$oMain->comp') AND (PAGM.page = 'profiler') AND (PAGM.accesslevel = 8)
				and USR.employee not in (
						SELECT tuserid
						FROM 
							dbo.tbgroups AS GRP 
							INNER JOIN dbo.tbgroupmember AS MEM ON GRP.tgpid = MEM.tgpid
						WHERE 
							GRP.company = 'efa' 
							AND ((GRP.application = 'Organization'  AND GRP.groupid = 'ADMIN_IS') OR (GRP.application = 'IS'  AND GRP.groupid = 'WEB'))
						)
			
		";
//print $sql;
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);

		$tablechips="<span><table border=0 width=100%>";
		$subject="SynergyNet $comp: Please give access to user ".$oMain->login.' to the module '.$this->CONTEXT.' | Favor dar acesso ao utilizador '.$oMain->login.' ao módulo '.$this->CONTEXT;
		for ($r = 0; $r < $rc; $r++)
		{
			$username=$rs[$r]['username'];
			$userPhoto="<img src=\"".$oMain->stdGetUserPicture($rs[$r]['employee'])."\" height=30\" style=\"vertical-align:middle;\">";

			$rs[$r]['username']="$userPhoto $username";

			$email= $rs[$r]['email'];
			$img=efaImg('communicator_s.png',$oMain->translate('requestacccesstomodule'), '', 'link');
			$link="<a href=\"mailto:$email?subject=$subject\">$img</a>";

			$rs[$r]['contact']= $link;

			$user='<div class="chip"> '.$link.' <img class="photo" src='.$oMain->stdGetUserPicture($rs[$r]['employee']).' title="'.$username.'" height="65">'.$username.'</div>';
			
			if($r%2==0)
			{
				$tablechips.='<tr align=left><td>'.$user.'</td>';
			}
			else
			{
				$tablechips.='<td>'.$user.'</td></tr>';
			}
		}
		
		if($r%2<>0)	{	$tablechips.='</tr>';	}
		
		$tablechips.="</table></span>";

		$a = new efalayout($this);
		$img=efaImg('usergroup_s.png','');
		$a->title($img.' '.$oMain->translate('accessmanagers'));
		$a->add($tablechips);

		$html=$a->html();  
		
		return $html;
	}
	
	function moduleInfoHelp(&$email)
		{
		$oMain=$this->oMain;
		$img=efaImg('communicator_s.png',$oMain->translate('sendemail'), '', 'link');
		$linkSi="<a href=\"mailto:servicedesk@efacec.com?subject=Support\">$img</a>";
		$tablechipsSI='<span>
			<table border=0 width=100%>
			<tr align=left><tr align=left><td><div class="chip"> '.$linkSi.' <img class="photo" src="../_efaC/img/help2.png" title="IT Service Desk" height="65">IT Service Desk</div></td></tr>
			</table></span>';
			
		$a = new efalayout($this);
		$img=efaImg('help_s.png','');
		$a->title($img.' '.$oMain->translate('responsableis'));
		$a->add($tablechipsSI);

		$html=$a->html();  
		
		return $html;
		}
	function moduleInfoHelp_OLD(&$email)
	{
		$oMain=$this->oMain;
		
		$sql="SELECT documentation, PAGS.userid, USRS.username, USRS.email, USRS.employee,
			(SELECT texto FROM dbo.tbTraducoes AS TTXT WHERE (dc = PAGS.unitext) AND (idioma = 'PT')) AS tdesc,
			(SELECT texto FROM dbo.tbTraducoes AS TTXT WHERE (dc = PAGS.unitextobs) AND (idioma = 'PT')) AS remarks,
			(SELECT texto FROM dbo.tbTraducoes AS TTXT WHERE (dc = PAGS.unitext) AND (idioma = 'IN')) AS tdesc_in,
			(SELECT texto FROM dbo.tbTraducoes AS TTXT WHERE (dc = PAGS.unitextobs) AND (idioma = 'IN')) AS remarks_in
		FROM  dbo.tbpages AS PAGS LEFT OUTER JOIN
		dbo.tbusers AS USRS ON PAGS.userid = USRS.userid
		WHERE page = '$this->CONTEXT'";
		$tablechips="<span><table border=0 width=100%>
					<tr align=left>";
		$subject="SynergyNet | Comp:$oMain->comp | módulo $this->CONTEXT";
		$img=efaImg('communicator_s.png',$oMain->translate('sendemail'), '', 'link');
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$userid=$rst['userid'];
			$username=$rst['username'];
			$email=$rst['email'];
			$employee=$rst['employee'];
			
			$link="<a href=\"mailto:$email?subject=$subject\">$img</a>";
			
			$tablechips.='<tr align=left><td><div class="chip"> '.$link.' <img class="photo" src='.$oMain->stdGetUserPicture($employee).' title="'.$username.'" height="65">'.$username.'</div></td></tr>';

			$remarks= nl2br($rst['remarks']);
			if($remarks=='') {$remarks=nl2br($rst['remarks_in']);}
			$manual ='<a target=_blank href="docs/help/'.$rst['documentation'].'" title="help">
					 '.$oMain->Translate('openhelpmanual').' <img src="img/bookopen.png" border=0 height=20></a>';
		}
		else
		{
			$manual ="<img src=\"img/bookopen.png\" border=0> &nbsp; ".$oMain->Translate('helpmanualunavailable');
		}
		
		$tablechips.="</table></span>";
		
		$linkSi="<a href=\"mailto:servicedesk@efacec.com?subject=Support\">$img</a>";
		$tablechipsSI='<span>
			<table border=0 width=100%>
			<tr align=left><tr align=left><td><div class="chip"> '.$linkSi.' <img class="photo" src="../_efaC/img/help2.png" title="IT Service Desk" height="65">IT Service Desk</div></td></tr>
			</table></span>';
		
		$tableline='';
		if($remarks!='')
			$tableline=" <tr>
			<td colspan=2><BR><b>".$oMain->translate('remarks').":</b><BR>$remarks</td>
        </tr>";
		
		$table="<table border=0 width=100%>
        <tr>
			<td width=240px>$tablechipsSI</td>
				<td>".$oMain->translate('contactsupport')."</td>
        </tr>
		 <tr>
			<td>$tablechips</td>
				<td>".$oMain->translate('developer')."</td>
        </tr>
       $tableline
		</table>";
		
		$a = new efalayout($this);
		$img=efaImg('help_s.png','');
		$a->title($img.' '.$oMain->translate('responsableis'));
		$a->add($table);

		$html=$a->html();  
		
		return $html;
		
	}
	
	function info($ccemail)
	{
		$oMain=$this->oMain;
		
		$bullet=efaImg('bullet_square_grey_s.png','');
		
		$needTraining=$oMain->translate("needtraining"); //'Pretendo ter formação neste software'
		
		$asktraining=$oMain->stdImglink('asktraining','','','CONTEXT='.$this->CONTEXT,
			'',$needTraining,'',$oMain->translate("explainasktraining"),
			$oMain->translate("confirmasktraining"));
		
		$linkprofiler='<a href="'.$oMain->BaseLink('profiler','listmod_access','','tmodule='.$this->CONTEXT).'">'.
		$oMain->translate('watchaccesslist').' '.$this->CONTEXT.' (Profiler)</a>';
		
		$linkreleasenotes='<a href="'.$oMain->BaseLink('profiler','history_tmodules','','tmodule='.$this->CONTEXT).'">'.
		$oMain->translate('releasenotes').'</a>';
		
		$labelreport=$oMain->translate('reportbug');
		$labelnewfeat=$oMain->translate('asknewfeature');
		$img=efaImg('communicator_s.png',$oMain->translate('sendemail'));
		$linkreport="<a href=\"mailto:servicedesk@efacec.com?subject=Report Bug $this->CONTEXT&cc=$ccemail\"> $labelreport</a>";
		$linknewfeature="<a href=\"mailto:servicedesk@efacec.com?subject=New Feature $this->CONTEXT&cc=$ccemail\"> $labelnewfeat</a>";
		
		$imgformation=efaImg('formation2_s.png','');
		$imgrelease=efaImg('history_s.png','');
		$imglist=efaImg('list_s.png','');
		
		$table="<table border=0 style=\"line-height:2.0\">
        <tr>
			<td>$imgformation $asktraining </td>
        </tr>
		<tr>
			<td>$imglist $linkprofiler</td>
        </tr>
		<tr>
			<td>$imgrelease $linkreleasenotes</td>
        </tr>
		<tr>
			<td>$img $linkreport</td>
        </tr>
		<tr>
			<td>$img $linknewfeature</td>
        </tr>
		</table>";
		
		$a = new efalayout($this);
		$img=efaImg('info2_s.png','');
		$a->title($img.' '.$oMain->translate('otherinfo'));
		$a->add($table);

		$html=$a->html();  
		
		return $html;
	}
	
	
	function askTraining()
	{
		$oMain=$this->oMain;
		
		$tsendto='99990000'; // Efacec Academy user
			
		$cclist=$oMain->employee;
		$sql="SELECT dbo.efa_uid(supervisor) AS tsupervisor, dbo.efa_uid(supfunc) AS tsupfunc FROM dbo.tbusers where employee='$oMain->employee'";
		$rs=dbQuery($oMain->consql, $sql, $flds); //print $sql; print_r($rs);
		if($rs[0]['tsupervisor']>0) {$cclist.='|'.$rs[0]['tsupervisor'];}
		if($rs[0]['tsupfunc']>0 && $rs[0]['tsupervisor'] != $rs[0]['tsupfunc']) {$cclist.='|'.$rs[0]['tsupfunc'];}

		$tsubjdesc='Pedido de formação | Training request';			
		$tmsgdesc="Pretendo ter formação no software SynergyNet \"$this->CONTEXT ($this->tdesc)\".<BR>"
				. "I want training on the software SynergyNet \"$this->CONTEXT ($this->tdesc)\".<BR>"
				. "Utilizador|User: $oMain->employee - $oMain->username <BR>";

		$sql="[dbo].[spmsg] '$oMain->sid','insert',0,0,'$oMain->comp','help','".$this->CONTEXT."','$tsubjdesc','$tmsgdesc','$oMain->employee','$tsendto','',0,0,'M','W',0,'$cclist'";
		$rs=dbQuery($oMain->consql, $sql, $flds);
		if($rs[0]['tstatus']=='0')
		{	
			$oMain->stdShowResult($rs[0]['tstatus'], $oMain->translate('requestsentok'));
		}
		else
		{	
			$oMain->stdShowResult($rs[0]['tstatus'], $oMain->translate('requestsentko'));
		}
	}
	
	
	
	
}

