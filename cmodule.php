<?php
/**
 * 2018-03-01	Luis Gomes		Modified SQL in synergyNetMan
 * 2017-05-31	Luis Gomes		R1704_00007
 * 2017-05-30	Luis Gomes		R1705_00009 - GetRS2 -> dbQuery
 * 2017-01-02	Luis Gomes		R1701_00002 - Documentar relases de software usando Communicator
 * 2016-05-17	Pedro Brandão Minor Changes
 * 2015-05-07	Pedro Brandão new dhtmlx chart
 * 2015-05-07	Pedro Brandão hits area in modules
 * 2015-04-23	Pedro Brandão use rh table to obtain obsolete users
 * 2015-04-22	Pedro Brandão added column with lasthit on obsolete user list
 * 2015-04-17	Pedro Brandão Corrected Hits to new tables and add moduleid to form of modules
 * 2013-11-20	Pedro Brandão Minor changes
 * 2013-11-04	Pedro Brandão Profiler 2.0
 * 2014-05-02	Luis Gomes		List users inactivated in the Active Directory (candidates to be removed in SynergyNet)
 * 2013-10-10	Luis Cruz		UTF-8 version
 * 2013-09-10	Pedro Brandão	removed pagetype from form CTModule. all modules are restricted
 * 2013-05-31	Pedro Brandão	corrected bug when insert a manager to a module
 */

require_once('ccommonsql.php');
/**
 * @@name	 	<description>
 * @@author	 	Generator 
 * @@version 	11-10-2012 17:18:55
 *
 * Revisions:
 */
class CTmodule
{
	var $tmodule;    /**  */
	var $pagedesc;    /**  */
	var $obs;    /**  */
	var $createdate;    /**  */
	var $userid;    /**  */
	var $useriddesc;
	var $pagetype;    /**  */
	var $unitext;    /**  */
	var $unitextobs;    /**  */
	var $documentation;    /**  */
	var $remarks;    /**  */
	var $modifiedby;    /**  */
	var $modifdate;    /**  */
	var $tstatus;    /**  */
	
	var $pagetypedesc;    /**  */
	var $unitextdesc; 
	var $unitextobsdesc; 
	var $tlang;
	var $ttype;
	
	//var $trefaid;
	var $tfolderid;
	var $tbindid;
	var $trefaid; 
	var $tdocid;
	
	var $moduleid;
	var $tproductid;
	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class pages mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		require_once('../common/ctranslation.php');
		$this->readFromRequest();
		$ent='tmodules'; 
		$tmodule=$this->tmodule;

		if ($mod =='deploy_'.$ent)
		{
			$html=$this->deploy();
			
			return $html;
		}		
		
		if ($mod =='history_'.$ent)
		{
			$html=$this->formMessages();
			
			//return $html;
		}
		
		if ($mod =='review_'.$ent)
		{
			$tstatus=$this->storeIntoDB('review', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$this->readfromdb ();
			
			$o=new CManage($oMain);
			$html=$o->getHtml('show_manage');
			
			return $html;
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$oMain->subtitle=$oMain->translate($mod).' '.$tmodule;

			$mod ='show_'.$ent;
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			$oMain->subtitle=$oMain->translate($mod).' '.$tmodule;

			$mod ='show_'.$ent;
		}


		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->form('insert_'.$ent);
		}

		if ($mod =='list_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			//$html=$oMain->Title('', $oMain->translate($mod));
			$html = $this->showListPages();
		}
		
		if ($mod =='search_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod).' '.$tmodule;
			$html=$this->searchModules();
			
		}
		
		
		if ($mod =='events_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->showListEvents();
		}
		
		if ($mod =='hits_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->dashboardhits();
		}
		
		
		//unitexts
		if($mod=='settdsca_'.$ent)
		{
			$this->readFromDB();
						
			if($oMain->accesslevel<5)
			{
				$oMain->stdShowResult(-1, 'no permission to set translations');
				$mod='show_'.$ent;
			}              
			else
			{
				$oTranslation=new CTranslation($oMain,$this->unitext,'tmodule',$this->tmodule);
				$html.=$oTranslation->formTranslation('savetdsca_'.$ent);
			}
		}  


		if($mod=='savetdsca_'.$ent)
		{
			$this->readFromDb();

			$oTranslation=new CTranslation($oMain);
			$oTranslation->readFromRequest();
						
			if($oMain->accesslevel<5)
			{
				$oMain->stdShowResult(-1, 'no permission to set translations');
			}
			else 
			{
				if($oTranslation->tunitext==$this->unitext)
				{
					$tstatus=$oTranslation->storeIntoDBTranslation('set', $tdesc); 
					$oMain->stdShowResult($tstatus, $tdesc);
				}
				else
					$oMain->stdShowResult(-1, 'error');     
			}
			$mod='show_'.$ent;
		}
		
////////////////////////////////////////////////////////////////////////////////////
		
		if($mod=='settdscaobs_'.$ent)
		{
			$this->readFromDB();
			
			if($oMain->accesslevel<5)
			{
				$oMain->stdShowResult(-1, 'no permission to set translations');
				$mod='show_'.$ent;
			}              
			else
			{
				$oTranslation=new CTranslation($oMain,$this->unitextobs,'tmodule',$this->tmodule); 
				$html.=$oTranslation->formTranslation('savetdscaobs_'.$ent);
			}
		}  


		if($mod=='savetdscaobs_'.$ent)
		{
			$this->readFromDb();

			$oTranslation=new CTranslation($oMain);
			$oTranslation->readFromRequest();
			
			if($oMain->accesslevel<5)
			{
				$oMain->stdShowResult(-1, 'no permission to set translations');
			}
			else 
			{
				if($oTranslation->tunitext==$this->unitextobs)
				{
					$tstatus=$oTranslation->storeIntoDBTranslation('set', $tdesc); 
					$oMain->stdShowResult($tstatus, $tdesc);
				}
				else
					$oMain->stdShowResult(-1, 'error');     
			}
			$mod='show_'.$ent;
		}
		
//unitexts
		
		
		if ($mod =='show_'.$ent)
		{
			$this->readFromDB();
			$oMain->subtitle=$oMain->translate('show_'.$ent).' '.$this->tmodule;
						
			$html.=$this->form('update_'.$ent);
		}
		
		
	// Mod for upload files	
	$ent='file';
	if ($mod =='new_'.$ent || $mod =='xnew_'.$ent)
	{
		$tstatus=$oMain->sh2UploadFile($tdesc,$tdocid,'efa','profiler','DOC',$this->tmodule,$this->tfolderid,'','');
		$oMain->stdShowResult($tstatus, $tdesc);
		
		$mod='list_file';
	}
	
	if ($mod =='del_'.$ent)
	{
		$tstatus=$oMain->shUnbindDoc($tdesc, $this->tbindid, $this->trefaid, $this->tdocid, $this->tfolderid);
		$oMain->stdShowResult($tstatus, $tdesc);
		
		$mod='list_file';
	}
	
	if ($mod =='list_'.$ent )
	{
		$oMain->subtitle=$oMain->translate($mod);
		$html=$this->listDocuments();
	}
	
		
	$oMain->toolbar_icon('img/new.png',$oMain->BaseLink('','new_tmodules'), $oMain->translate('createmodule'),'','','',$oMain->translate('createmodule'));
	
	
	if($mod<>'list_tmodules' AND $mod <>'new_tmodules')
	{
		$title=$oMain->Title('', $this->tmodule);
		$dashboard='<table width=100%><tr valign=top><td width=195 class=row1>'.$this->menuModule($this->tmodule).'</td>
		<td valign=top>'.$title.'<BR>'.$html.'</td></tr></table>';

		return($dashboard);
	}
	else
		return($html);
	
	}
	
	protected function formMessages()
	{
		$oMain=$this->oMain;

		$html = $oMain->getCommunicator($oMain->page, $this->tmodule, 1, '',$arr,500);
		
		return $html;
	}	
	
	function menuModule($tmodule)
	{
		$oMain=$this->oMain;
		
		$this->tmodule=$tmodule;
		$this->readfromdb();
		
		$param='&tmodule='.$this->tmodule.'&moduleid='.$this->moduleid;
		
		$label=$oMain->translate('show_tmodules');
		if($oMain->mod=='show_tmodules')
			$label='<b>'.$label.'</b>';
		$module=$oMain->stdImglink('show_tmodules', '', '', $param, 'img/apps_s.png', $label, '', $oMain->translate('show_tmodules'), '',$oMain->loading());
		
		
		$label=$oMain->translate('moduleparam');
		if($oMain->mod=='list_modparam')
			$label='<b>'.$label.'</b>';
		$parameters=$oMain->stdImglink('list_modparam', '', '', $param, 'img/parameters_s.png', $label, '', $oMain->translate('moduleparam'), '',$oMain->loading());
		
		
		$label=$oMain->translate('accesses');
		if($oMain->mod=='listmod_access')
			$label='<b>'.$label.'</b>';
		$access=$oMain->stdImglink('listmod_access', '', '', $param, 'img/access_s.png', $label, '', $oMain->translate('accesses'), '',$oMain->loading());
		
		$label=$oMain->translate('modulemanagers');
		if($oMain->mod=='list_mmanagers')
			$label='<b>'.$label.'</b>';
		$managers=$oMain->stdImglink('list_mmanagers', '', '', $param, 'img/user_comm_s.png', $label, '', $oMain->translate('modulemanagers'), '',$oMain->loading());
		
		
		$label=$oMain->translate('documents');
		if($oMain->mod=='list_file')
			$label='<b>'.$label.'</b>';
		$docs=$oMain->stdImglink('list_file', '', '', $param, 'img/shareplace_s.png', $label, '', $oMain->translate('documents'), '',$oMain->loading());
		
		$label='Hits';
		if($oMain->mod=='hits_tmodules')
			$label='<b>'.$label.'</b>';
		$hits=$oMain->stdImglink('hits_tmodules', '', '', $param, 'img/stats_s.png', $label, '', $oMain->translate('hits_tmodules'), '',$oMain->loading());

		$label='History';
		if($oMain->mod=='history_tmodules')
			$label='<b>'.$label.'</b>';
		$history=$oMain->stdImglink('history_tmodules', '', '', $param, 'img/history_s.png', $label, '', $oMain->translate('history_tmodules'), '',$oMain->loading());
		
		
		
		$bulletl20='';
		
		//base do link dos docs
		$sqllink="SELECT tvalue FROM [dbo].[tbcompparam] where company ='$oMain->comp' AND tparam='INTRANET_ROOT'";
		$rs1=dbQuery($oMain->consql, $sqllink, $flds);

		$link=$rs1[0]['tvalue'];
		//$profiler=$link.'efa.php?page=profiler&mod=show_tmodules&comp=efa&tmodule='.$tmodule.'&sid='.$oMain->sid.'';
		$profiler=$link.'efa.php?page='.$tmodule.'&comp='.$oMain->comp.'&sid='.$oMain->sid.'';
		
		$filename = 'img/'.$tmodule.'_l.png';
		if(!file_exists($filename))
			$picture='<a href='.$profiler.'><img src=img/imgnotavailable_xl.png width=100 border=0></a>';
		else
			$picture='<a href='.$profiler.'><img src=img/'.$tmodule.'_l.png width=100 border=0></a>';
		
	
		$events=$oMain->stdImglink('events_tmodules', '', '', 'tmodule='.$tmodule, 'img/log2.png', '', '', $oMain->translate('events'));

		$html="
		<table border=0>
		<tr>
			<td>$picture</td>
			<td width=30%>$events</td>
		</tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr><td colspan=2>$bulletl20 $module</td></tr>
		<tr><td colspan=2>$bulletl20 $parameters</td></tr>
		<tr><td colspan=2>$bulletl20 $access</td></tr>
		<tr><td colspan=2>$bulletl20 $managers</td></tr>
		<tr><td colspan=2>$bulletl20 $docs</td></tr>
		<tr><td colspan=2>$bulletl20 $hits</td></tr>
		<tr><td colspan=2>$bulletl20 $history</td></tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		</table>";			

		return($html);
	}
	
	 /**
	  * read class pages atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->tmodule=$oMain->GetFromArray('tmodule',$_REQUEST,'string_trim');
		$this->pagedesc=$oMain->GetFromArray('pagedesc',$_REQUEST,'string_trim');
		$this->obs=$oMain->GetFromArray('obs',$_REQUEST,'string_trim');
		$this->createdate=$oMain->GetFromArray('createdate',$_REQUEST,'date');
		$this->userid=$oMain->GetFromArray('userid',$_REQUEST,'string_trim');
		$this->pagetype=$oMain->GetFromArray('pagetype',$_REQUEST,'string_trim');
		$this->status=$oMain->GetFromArray('status',$_REQUEST,'string_trim');
		$this->unitext=$oMain->GetFromArray('unitext',$_REQUEST,'string_trim');
		$this->unitextobs=$oMain->GetFromArray('unitextobs',$_REQUEST,'string_trim');
		$this->documentation=$oMain->GetFromArray('documentation',$_REQUEST,'string_trim');
		$this->remarks=$oMain->GetFromArray('remarks',$_REQUEST,'string_trim');
		$this->modifiedby=$oMain->GetFromArray('modifiedby',$_REQUEST,'string_trim');
		$this->modifdate=$oMain->GetFromArray('modifdate',$_REQUEST,'date');
		$this->tstatus=$oMain->GetFromArray('tstatus',$_REQUEST,'string_trim');
		$this->unitextdesc=$oMain->GetFromArray('unitextdesc',$_REQUEST,'string_trim');
		$this->unitextobsdesc=$oMain->GetFromArray('unitextobsdesc',$_REQUEST,'string_trim');
		$this->tlang=$oMain->GetFromArray('tlang',$_REQUEST,'string_trim');
		
		$this->ttype = $oMain->getFromArray('ttype');
		
		$this->tfolderid=$oMain->GetFromArray('tfolderid',$_REQUEST,'int');
		$this->tbindid=$oMain->GetFromArray('tbindid',$_REQUEST,'int');
		$this->trefaid=$oMain->GetFromArray('trefaid',$_REQUEST,'int');
		$this->tdocid=$oMain->GetFromArray('tdocid',$_REQUEST,'int');
		
		$this->moduleid=	$oMain->GetFromArray('moduleid',	$_REQUEST,'int');
		$this->tproductid=	$oMain->GetFromArray('tproductid',	$_REQUEST,'int');	


		
		//compatibilidade para um link existente do antigo profiler
		if ($this->tmodule=='') $this->tmodule = $oMain->GetFromArray('moduleref',$_REQUEST);
		
	}
	
	function dashboard()
	{
		$oMain=$this->oMain;
		
		$omodman=new CModuleMan($oMain);
		$omodman->readfromrequest();
			
		$form=$this->form('update_'.$ent);
		$managers=$omodman->showListManagers();
		
		$html="<table width=99% border=1>
        <tr>
			<td width=70% align=top>$form</td>
			<td width=1%></td>
			<td align=top>$managers</td>
        </tr>
        </table>";
		
		
		return($html);
	}
	
	
	function dashboardhits()
	{
		$oMain=$this->oMain;
		
		$listusers=$this->listUsers();
		$listmods=$this->listMods();
		
		$chart=$this->chartmonthusage($this->moduleid);
			
		
		
		$html="<table width=99% border=0>
		<tr>
			<td colspan=3 align=center>$chart</td>
        </tr>
        <tr>
			<td width=49% align=top>$listusers</td>
			<td width=1%></td>
			<td align=top>$listmods</td>
        </tr>
        </table>";
		
		
		return($html);
	}
	
	function deploy()
	{

	$oMain=$this->oMain;

	$sql="SELECT tcreatedate, trefa, tsubject, ttext FROM dbo.tbmsg 
WHERE (tscope = 'profiler') AND (trefa <> 'Review') ORDER BY tcreatedate DESC"; //AND YEAR(tcreatedate)=2017 AND MONTH(tcreatedate)=01 
	$rsQ=dbQuery($oMain->consql, $sql, $flds);
	$rcQ=count($rsQ);
//print "$sql || $rcQ<HR>";

	for ($v = 0; $v < $rcQ; $v++)
	{
		$rsQ[$v]['tcreatedate']=$oMain->formatDate( $rsQ[$v]['tcreatedate']);
		$rsQ[$v]['ttext']=str_replace( chr(10),'<BR>', $rsQ[$v]['ttext']);
	}

	$html='';
	$oTable = new CTable(null, null, $rsQ);
	$oTable->SetSorting (1);
	//$oTable->SetFixedHead ();
	$oTable->addColumn($oMain->translate('date'), "left", "String");
	$oTable->addColumn($oMain->translate('tmodule'), "left", "String");
	$oTable->addColumn($oMain->translate('tsubject'), "left", "String");
	$oTable->addColumn($oMain->translate('ttext'), "left", "String");

	$html .= $oTable->getHtmlCode ();

	return($html);
	
	}
	
	
	
	function chartmonthusage($tmodule)
	{
		$oMain=$this->oMain;
		
		$this->year=date("Y");
		
		//print $this->year;
		
//		$sql="SELECT YEAR(tdate) AS tyear, MONTH(tdate) AS tmonth, SUM(tcount) AS total, SUBSTRING(DATENAME(MONTH, tdate),0,4) as chartlabel,
//		CASE
//		WHEN MONTH(tdate) = 12 AND YEAR(getdate())=YEAR(tdate)
//			THEN 0
//		WHEN MONTH(tdate) = 12
//			THEN ISNULL(
//			(SELECT SUM(tcount) FROM dbo.tbHitHist WHERE (tmodule = '$tmodule') and (YEAR(tdate)='$this->year'+1) and (MONTH(tdate)=1))-
//			(SELECT SUM(tcount) FROM dbo.tbHitHist WHERE (tmodule = '$tmodule') and (YEAR(tdate)='$this->year') and (MONTH(tdate)=12)),SUM(tcount)) 
//		WHEN MONTH(tdate) = MONTH(GETDATE()) AND YEAR(tdate) = YEAR(GETDATE()) 
//			THEN
//			(SELECT (SUM(HH.tcount)-(SELECT SUM(HH.tcount) AS numhits
//			FROM dbo.tbHitHist AS HH INNER JOIN 
//				dbo.tbpages AS PP ON HH.tmodule = PP.tmodule
//			GROUP BY HH.tmodule, PP.[page], YEAR(HH.tdate) * 100 + MONTH(HH.tdate), PP.tstatus
//			HAVING (YEAR(HH.tdate) * 100 + MONTH(HH.tdate) = (YEAR(getdate()) * 100 + MONTH(getdate()) ) ) AND PP.tstatus='A' and  HH.tmodule='$tmodule')) AS actualhits
//			FROM dbo.tbHit AS HH INNER JOIN
//				dbo.tbpages AS PP ON HH.tmodule = PP.tmodule
//			WHERE PP.tstatus='A' and HH.tmodule='$tmodule')
//		ELSE ISNULL((SELECT SUM(tcount) FROM dbo.tbHitHist GROUP BY tmodule, MONTH(tdate), YEAR(tdate)
//			HAVING (tmodule = '$tmodule') and (YEAR(tdate)='$this->year') and (MONTH(tdate)=MONTH(MTOT.tdate)+1))-SUM(tcount),SUM(tcount)) 
//		END AS chartval
//		FROM dbo.tbHitHist AS MTOT
//		WHERE (tmodule = '$tmodule') and (YEAR(tdate)='$this->year')
//		GROUP BY tmodule, MONTH(tdate), YEAR(tdate), SUBSTRING(DATENAME(MONTH, tdate),0,4)
//		ORDER BY tmonth";

		$sql="select mes.texto, '$this->year' AS tyear, mes.texto AS tmonth, SUM(tcount) AS total, SUBSTRING(DATENAME(MONTH, '$this->year' + '-'+ mes.texto+'-01'),0,4) as chartlabel,
		CASE
		WHEN MONTH(tdate) = 12 AND YEAR(getdate())=YEAR(tdate)
			THEN 0
		WHEN MONTH(tdate) = 12
			THEN ISNULL(
			(SELECT SUM(tcount) FROM dbo.tbHitHist WHERE (tmodule = '$tmodule') and (YEAR(tdate)='$this->year'+1) and (MONTH(tdate)=1))-
			(SELECT SUM(tcount) FROM dbo.tbHitHist WHERE (tmodule = '$tmodule') and (YEAR(tdate)='$this->year') and (MONTH(tdate)=12)),SUM(tcount)) 
		WHEN MONTH(tdate) = MONTH(GETDATE()) AND YEAR(tdate) = YEAR(GETDATE()) 
			THEN
			(SELECT (SUM(HH.tcount)-(SELECT SUM(HH.tcount) AS numhits
			FROM dbo.tbHitHist AS HH INNER JOIN 
				dbo.tbpages AS PP ON HH.tmodule = PP.tmodule
			GROUP BY HH.tmodule, PP.[page], YEAR(HH.tdate) * 100 + MONTH(HH.tdate), PP.tstatus
			HAVING (YEAR(HH.tdate) * 100 + MONTH(HH.tdate) = (YEAR(getdate()) * 100 + MONTH(getdate()) ) ) AND PP.tstatus='A' and  HH.tmodule='$tmodule')) AS actualhits
			FROM dbo.tbHit AS HH INNER JOIN
				dbo.tbpages AS PP ON HH.tmodule = PP.tmodule
			WHERE PP.tstatus='A' and HH.tmodule='$tmodule')
		ELSE COALESCE((SELECT SUM(tcount) FROM dbo.tbHitHist GROUP BY tmodule, MONTH(tdate), YEAR(tdate)
			HAVING (tmodule = '$tmodule') and (YEAR(tdate)='$this->year') and (MONTH(tdate)=MONTH(MTOT.tdate)+1))-SUM(tcount),SUM(tcount),0) 
		END AS chartval
		FROM dbo.ef_mult2 ('01,02,03,04,05,06,07,08,09,10,11,12',',') mes left outer join
		dbo.tbHitHist AS MTOT ON (YEAR(tdate) = '$this->year')  and mes.texto =Month(tdate)
		WHERE NOT ('$this->year'=YEAR (getdate()) and  mes.texto> MONTH (getdate()) ) AND (tmodule = '$tmodule' AND (YEAR(tdate)='$this->year') OR tmodule IS NULL)
		GROUP BY mes.texto,MONTH(tdate), YEAR(tdate)
		ORDER BY mes.texto";
//print $sql;		
		$rs=dbQuery($oMain->consql, $sql, $flds);

		require_once('cchart.php');
		$o = new Cchart($oMain);

		$o->series($rs);
		$o->type('line');
		$o->xtitle($oMain->translate('monthhits'));

		//$o->ytitle($oMain->translate('units'));
		//	$o->ystart(0);
		//	$o->ystep(1);
		//	$o->yend(3);

		$o->barwidth(35);
		//$o->barcolour('#66ccff');
		//$o->gradient('multi');

		//$o->title($oMain->translate('xpto2'));
		$o->width(1000); 
		$o->height(200);
		//$o->border('1px');
	
		$charthitmonth=$o->html();
		
		return($charthitmonth);
	}
	
	
	function listUsers()
	{
		$oMain=$this->oMain;
		
		$sqlusers="SELECT (U.username+' - '+cast(H.tuserid as varchar(50))) as username, SUM(H.tcount) AS hitcount, MAX(H.tlasthit) AS lasthit, U.company, H.tuserid, U.email
		FROM dbo.tbhit AS H INNER JOIN
		dbo.tbusers AS U ON H.tuserid = U.employee
		GROUP BY H.tmodule, H.tuserid, U.username, U.company, U.email
		HAVING (H.tmodule = '$this->moduleid')
		ORDER BY hitcount DESC";
		
		$rs=dbQuery($oMain->consql, $sqlusers, $flds);
		$rc=count($rs);
		for ($r = 0; $r < $rc; $r++)
		{	
			if($oMain->operation<>'excel')
			{
				$employee=$rs[$r]['tuserid'];
				$temnophoto="<img src=\"".$oMain->stdGetUserPicture($employee)."\"  height=25>";
			}
			$username=$rs[$r]['username'];
			$email=$rs[$r]['email'];
			
			
			$rs[$r]['username']=$oMain->stdImglink('show_users', '', '', 'tuserid='.$employee, '', $temnophoto.' '.$username, '', $email, $oMain->loading());
			
			$rs[$r]['lasthit']=$oMain->formatdate($rs[$r]['lasthit']);
		}

		
		
		$oTable = new CTable(null, null, $rs);
		$oTable->SetSorting();
		$oTable->SetFixedHead (true,350);

		$oTable->addColumn($oMain->translate('username'), 'left', 'String');
		$oTable->addColumn($oMain->translate('hitcount'), 'left', 'String');
		$oTable->addColumn($oMain->translate('lasthit'), 'left', 'String');
		$oTable->addColumn($oMain->translate('usercompany'), 'left', 'String');
		if($oMain->operation=='excel')
		{
			$oTable->addColumn($oMain->translate('tuserid'), 'left', 'String');
			$oTable->addColumn($oMain->translate('email'), 'left', 'String');
		}


		$title=$oMain->translate('exportmail');
		$excel=$oMain->stdImglink('hits_tmodules', '', 'excel', 'tmodule='.$this->tmodule.'&moduleid='.$this->moduleid, 'img/excel_s.png', 'Excel', '', $title);
		

		$html = $oMain->efaHR( $oMain->translate('usersace').' ('.$rc.') &nbsp; | &nbsp; '.$excel);
		
		if($oMain->operation=='excel')
			$oTable->setOutputToExcel(TRUE);

		$html.= $oTable->getHtmlCode();

		If($rc==0) {return $oMain->translate('nosearchresults');}
		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
		
		
		return($html);
	}
	
	function listMods()
	{
		$oMain=$this->oMain;
		
		$sqlusers="SELECT '' as pos, tmod, SUM(tcount) AS hitcount, MAX(tlasthit) AS lasthit
		FROM dbo.tbhit
		WHERE (tmodule = '$this->moduleid')
		GROUP BY tmod
		ORDER BY hitcount DESC";
		
		$rs=dbQuery($oMain->consql, $sqlusers, $flds);
		$rc=count($rs);
		for ($r = 0; $r < $rc; $r++)
		{	
			$rs[$r]['pos']=$r+1;
			$rs[$r]['lasthit']=$oMain->formatdate($rs[$r]['lasthit']);
		}

		$oTable = new CTable(null, null, $rs);
		$oTable->SetSorting();
		$oTable->SetFixedHead (true,350);

		$oTable->addColumn($oMain->translate('pos'), 'left', 'String');
		$oTable->addColumn($oMain->translate('tmod'), 'left', 'String');
		$oTable->addColumn($oMain->translate('hitcount'), 'left', 'String');
		$oTable->addColumn($oMain->translate('lasthit'), 'left', 'String');
		//$oTable->addColumn($oMain->translate('company'), 'left', 'String');


		$html = $oMain->efaHR( $oMain->translate('mods'));
		$html.= $oTable->getHtmlCode();

		If($rc==0) {return $oMain->translate('nosearchresults');}
		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
		return($html);
	}
	
	/**
	 * class pages form
	 */	
	function form($mod='show_tmodules')
	{
		$oMain=$this->oMain;
		$html_form=$oMain->stdJsPopUpWin('400');
		$formName='frmpages'; $operation='';$nCol=2;$width='100%';$ajax=false;
		$frmMod=CForm::MODE_EDIT;
		if($mod=='show_tmodules')
			$frmMod=CForm::MODE_VIEW;
		
		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$aForm = array();
		
		if($mod=='insert_tmodules')
		{
			$ro=false;
			$this->tstatus='A';
		}
		else
			$ro=true;
						
		if($mod=='insert_tmodules')
			$aForm[] = new CFormTitle($oMain->translate('new_module'), 'tit'.$formName);
		
		if($mod=='insert_tmodules')
			$aForm[] = new CFormText($oMain->translate('tmodule'),'tmodule', $this->tmodule,50,CForm::REQUIRED,$ro,'',CFormText::INPUT_STRING_CODEI);
		else
			$aForm[] = new CFormHidden('tmodule', $this->tmodule);

		if($mod=='insert_tmodules')
		{
			$aForm[] = new CFormText($oMain->translate('pagedesc'), 'unitextdesc', $this->unitextdesc, 50,CForm::REQUIRED,false);
		}
		else
		{
			$field_tdsca = new CFormText($oMain->translate('pagedesc'), 'unitextdesc', $this->unitextdesc,50,CForm::REQUIRED,true);
			$field_tdsca->setNumberOfColumns(1);
			$tdsca_popup=$oMain->stdImglink('settdsca_tmodules','','','tmodule='.$this->tmodule,'img/doctxt_s.png','','');                                                                                                                                   
			$field_tdsca->setExtraData($tdsca_popup);
			$aForm[]=$field_tdsca;
		}
		
		
		$search_userid=$oMain->stdPopupwin('GETCCUSER',$formName,'userid','useriddesc','userid','useriddesc','','');	
		$field_userid = new CFormText($oMain->translate('useridresp'),'userid', $this->userid,'',CForm::REQUIRED,false);
		$field_userid_desc = new CFormText($oMain->translate('useridresp'), 'useriddesc', $this->useriddesc, '','',false, '', '', '', 70);
		if($frmMod==CForm::MODE_EDIT)
		   $field_userid->setExtraData($search_userid);
		$aForm[] = new CFormMultipleElement(array($field_userid, $field_userid_desc), 0);
		

		if($mod=='insert_tmodules')
		{
			$field_tdsca = new CFormTextArea($oMain->translate('obs'), 'unitextobsdesc', $this->unitextobsdesc, 8,CForm::REQUIRED,false);
			$field_tdsca->setNumberOfColumns(3);
			$aForm[]=$field_tdsca;
		}
		else
		{
			$field_tdsca = new CFormTextArea($oMain->translate('obs'), 'unitextobsdesc', $this->unitextobsdesc, 8,CForm::REQUIRED,true);
			$field_tdsca->setNumberOfColumns(1);
			$tdsca_popup=$oMain->stdImglink('settdscaobs_tmodules','','','tmodule='.$this->tmodule,'img/doctxt_s.png','','');                                                                                                                                   
			$field_tdsca->setExtraData($tdsca_popup);
			$aForm[]=$field_tdsca;
		}
		
		$atextForm = new CFormTextArea($oMain->translate('remarks'), 'remarks', $this->remarks, 7,CForm::RECOMMENDED);
        $atextForm->setNumberOfColumns(1);
		$aForm[]=$atextForm;
			
		$sql="SELECT codeid, dbo.translate_optional(valunitext, '$oMain->l', codeid) AS codetxt FROM dbo.tbcodes WHERE (codetype = 'global_status')";
		$aForm[] = new CFormSelect($this->oMain->translate('tstatus'), 'tstatus', trim($this->tstatus), $this->tstatusdesc, $sql, $oMain->consql, '', '', ' ', CForm::REQUIRED);
				
		if($mod!='insert_tmodules')
		{
			//$aForm[] = new CFormFree();
			//$aForm[] = new CFormText($oMain->translate('tproductid'),'tproductid', $this->tproductid,4);
			
		$sql="SELECT tproductid, tdsca FROM tbproducts WHERE tstatus='I' ORDER BY tdsca";
		$aForm[] = new CFormSelect($this->oMain->translate('tproductid'), 'tproductid', $this->tproductid, $this->tproductid, $sql, $oMain->consql, '', '-', '0', CForm::RECOMMENDED);
			
			
			
			$aForm[] = new CFormText($oMain->translate('moduleid'),'moduleid', $this->moduleid,20,'',true);
			$aForm[] = new CFormText($oMain->translate('modifiedby'),'modifiedby', $this->modifiedby,20,'',true,'',CFormText::INPUT_STRING_CODE);
			$aForm[] = new CFormText($oMain->translate('modifdate'),'modifdate', $oMain->formatDate($this->modifdate),4,'',true,'',CFormText::INPUT_DATE);
		}
				
		//form buttons
		//$onSubmit="$formName.submit(); $oMain->loading;";
		$buttonSave = new CFormButton('save', $oMain->translate ('save'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);
		$aForm[]=$buttonSave;
		$oForm->addElementsCollection($aForm);
		$html_form.=$oForm->getHtmlCode();
		return $html_form;

	}
	
	function formFilter()
	{
		$oMain=$this->oMain;
		
		$formName='frmpages'; $operation='';$nCol=2;$width='100%';$ajax=false;
		$frmMod=CForm::MODE_EDIT;
			
		
		$oForm = $oMain->std_form('list_tmodules', 'filter',$formName,$nCol,$frmMod,$ajax,$width);
		$aForm = array();
		
		$aForm[] = new CFormText($oMain->translate('tmodule'),'tmodule', $this->tmodule,50,'',false,'',CFormText::INPUT_STRING_CODE);
		$aForm[] = new CFormText($oMain->translate('pagedesc'),'pagedesc', $this->pagedesc,250,'',false,'',CFormText::INPUT_STRING_CODE);

		//form buttons
		$buttonSave = new CFormButton('select', $oMain->translate ('filter'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$aForm[]=$buttonSave;
		$oForm->addElementsCollection($aForm);
		// $html_form.=$this->form_toolbar($mod);
		$html_form.=$oForm->getHtmlCode();
		return $html_form;
	}
//	
	function sqlGetFilter()
{
	$oMain=$this->oMain;
	$l=$oMain->l;
	$query='';
	
	if($this->tmodule<>'') 
		$query =" AND (page like '%$this->tmodule%')";
	if($this->pagedesc<>'')       
		$query.=" AND pagedesc LIKE '%".$this->pagedesc."%'";
	
	$sql=
		"SELECT page AS tmodule,pagedesc,dbo.efa_username(modifiedby) AS modifiedbydesc,modifdate,createdate,userid,pagetype,unitext,unitextobs,documentation,remarks,tstatus
		,modifiedby,obs
		FROM dbo.tbpages		
		WHERE 1=1 $query";

	return($sql);
}

	/**
	 * save class pages record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$oMain=$this->oMain;
		$sid=$this->oMain->sid;
		
//		if($this->tlang=='')
//			$this->tlang=$oMain->l;
		
		$sql="[dbo].[sppages]
			'$sid','$operation'
		,'$this->tmodule'
		,'$this->unitextdesc'
		,'$this->unitextobsdesc'
		,'$this->userid'
		,'$this->pagetype'
		,'$oMain->l'		
		,'$this->documentation'
		,'$this->remarks'
		,'$this->tstatus', 1
		,'$this->tproductid'
		";	//print $sql.'<HR>'; die;

		$rs=dbQuery($this->oMain->consql, $sql, $flds);
		$rst=$rs[0];
		$tdesc=$rst['tdesc'];
		return($rst['tstatus']);
	}
	/**
	 * query to get class pages record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
		$l=$oMain->l;
	
		$sql="SELECT page AS tmodule, pagedesc, obs, createdate, dbo.efa_username(userid) AS useriddesc, pagetype
				, dbo.translate_unitext(unitext, '$l') AS unitextdesc ,dbo.translate_unitext(unitextobs, '$l') AS unitextobsdesc
				, documentation, remarks, dbo.efa_username(modifiedby) AS modifiedby, modifdate
				,dbo.translate_code('global_status',tstatus, '$l') AS tstatusdesc, dbo.translate_code('profiler_pagetype',pagetype, '$l') AS pagetypedesc
				, tstatus, unitext, unitextobs, userid, tmodule as moduleid
				,(SELECT REFA.trefaid FROM dbo.tbshreftype AS RTYP INNER JOIN
				dbo.tbshref AS REFA ON RTYP.treftypeid = REFA.treftypeid
				WHERE (RTYP.tcompany = 'efa') AND (RTYP.tscope = 'profiler') AND (RTYP.ttype = 'DOC') AND (REFA.trefa ='$this->tmodule') AND (RTYP.tstatus='A')) as trefaid, tproductid
			FROM dbo.tbpages WHERE page='$this->tmodule'";		

		//print $sql;
		return($sql);
	}
	/**
	 * set class pages atributes with data from database
	 */	
	function readfromdb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->tmodule=$rst['tmodule'];
			//$this->pagedesc=$rst['pagedesc'];
			//$this->obs=$rst['obs'];
			$this->createdate=$rst['createdate'];
			$this->userid=$rst['userid'];
			$this->useriddesc=$rst['useriddesc'];
			$this->pagetype=$rst['pagetype'];
			$this->status=$rst['status'];
			$this->unitext=$rst['unitext'];
			$this->unitextobs=$rst['unitextobs'];
			$this->documentation=$rst['documentation'];
			$this->remarks=$rst['remarks'];
			$this->modifiedby=$rst['modifiedby'];
			$this->modifdate=$rst['modifdate'];
			$this->tstatus=$rst['tstatus'];
			
			$this->pagetypedesc=$rst['pagetypedesc'];
			$this->unitextdesc=$rst['unitextdesc'];
			$this->unitextobsdesc=$rst['unitextobsdesc'];
			
			$this->trefaid=$rst['trefaid'];
			//print $this->trefaid;
			if($this->trefaid=='')
				$this->createReference();
			
			$this->moduleid=$rst['moduleid'];
			$this->tproductid=$rst['tproductid'];
			//$this->unitextdesc=$rst['unitextdesc'];
			//$this->unitextobsdesc=$rst['unitextobsdesc'];
			
			
		}
		return $rc;
	}
	
function createReference()
{
	$oMain=$this->oMain;
	
	//create shareplace reference
	$sid=$oMain->sid;
	//$tmnggroup=1; //ADMIN IS tgpid
	
	$sql="SELECT treftypeid FROM dbo.tbshreftype WHERE tcompany='efa' AND tscope='profiler' AND ttype='DOC'";
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$treftypeid=$rs[0]['treftypeid'];

	$sqlcreate="[dbo].[spshref] '$sid', 'insert', '0', '0', '$treftypeid', '$this->tmodule', 'A', ''";
	$rscreate=dbQuery($oMain->consql, $sqlcreate, $flds); 
	if(trim($rscreate[0]['tstatus'])<>0)
	{
		$tdesc=$rscreate['tdesc']; 
		$oMain->stdShowResult(-1, $tdesc);
	}
	
}

function showListPages()
{
	$oMain=$this->oMain;
	$l=$oMain->l;
	//$this->userid=$userid;
/*		
	$sql = "
		SELECT 
			page as tmodule
			, dbo.translate_optional(unitext, '".$l."', unitext) AS pagedesc
			, userid +' - '+ dbo.efa_username(userid) AS manager
			, modifiedby +' - '+ dbo.efa_username(modifiedby) AS modifiedby
			, modifdate
			, (CASE WHEN tstatus='X' THEN dbo.translate('inactive', '".$l."', '@GERAL') ELSE dbo.translate('active', '".$l."', '@GERAL') END) as tstatus
		FROM 
			dbo.tbpages
		";
*/
	$sql="SELECT MM.page AS tmodule, dbo.translate_optional(MM.unitext, '$l', MM.unitext) AS pagedesc, 
	dbo.efa_username(MM.userid)  AS manager,
	dbo.translate_code('global_status', MM.tstatus, '$l') AS tstatus, PP.tdsca AS tapp,  dbo.efa_username(MM.modifiedby) AS modifiedby, MM.modifdate
	FROM dbo.tbpages AS MM LEFT OUTER JOIN
		 dbo.tbproducts AS PP ON MM.tproductid = PP.tproductid
	ORDER BY MM.tstatus, MM.page";
		
		
	
	$x = new efaGrid($this->oMain);
	$x->title($this->oMain->translate('list_tmodules'));
	$x->query($sql);
	$x->exportToExcel(true);
	$x->exportToPDF(true);
	$x->widthUsePercent(true);
	$x->autoExpandHeight(true);
	$x->dbClickLink($this->oMain->baseLink('', 'show_tmodules', '', 'tmodule=§§tmodule§§'));
	//$x->columnsFromData();
	$x->columnAdd('tmodule')->width(15);
	$x->columnAdd('pagedesc')->width(15);
	$x->columnAdd('manager')->width(15)->searchType('select');
	$x->columnAdd('tstatus')->width(15);
	$x->columnAdd('tapp')->width(15);
	$x->columnAdd('tstatus')->searchType('select');
	$x->columnAdd('modifdate')->type('date');
	$x->columnAdd('modifiedby');
	return $x->html();
	
//	
//	if($oMain->operation=='manage')
//	{
//		$html.= $oMain->stdImglink('list_tmodules', '', 'active', 'tstatus=A', 'img/plus_s.png', $oMain->translate('activemodules'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'manage', 'tstatus=A', 'img/plus_s.png', '<b>'.$oMain->translate('modulesman').'</b>', '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'resp', 'tstatus=A', 'img/plus_s.png', $oMain->translate('modulesresp'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.=$oMain->stdImglink('list_tmodules', '', 'inactive', 'tstatus=X', 'img/plus_s.png', $oMain->translate('disabledmodules'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//	}
//	elseif ($oMain->operation=='active')
//	{
//		$html.= $oMain->stdImglink('list_tmodules', '', 'active', 'tstatus=A', 'img/plus_s.png', '<b>'.$oMain->translate('activemodules').'</b>', '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'manage', 'tstatus=A', 'img/plus_s.png', $oMain->translate('modulesman'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'resp', 'tstatus=A', 'img/plus_s.png', $oMain->translate('modulesresp'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.=$oMain->stdImglink('list_tmodules', '', 'inactive', 'tstatus=X', 'img/plus_s.png', $oMain->translate('disabledmodules'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//
//	}
//	elseif ($oMain->operation=='inactive')
//	{
//		$html.= $oMain->stdImglink('list_tmodules', '', 'active', 'tstatus=A', 'img/plus_s.png', $oMain->translate('activemodules'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'manage', 'tstatus=A', 'img/plus_s.png', $oMain->translate('modulesman'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'resp', 'tstatus=A', 'img/plus_s.png', $oMain->translate('modulesresp'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.=$oMain->stdImglink('list_tmodules', '', 'inactive', 'tstatus=X', 'img/plus_s.png', '<b>'.$oMain->translate('disabledmodules').'</b>', '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//	}
//	elseif ($oMain->operation=='resp')
//	{
//		$html.= $oMain->stdImglink('list_tmodules', '', 'active', 'tstatus=A', 'img/plus_s.png', $oMain->translate('activemodules'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'manage', 'tstatus=A', 'img/plus_s.png', $oMain->translate('modulesman'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'resp', 'tstatus=A', 'img/plus_s.png', '<b>'.$oMain->translate('modulesresp').'</b>', '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.=$oMain->stdImglink('list_tmodules', '', 'inactive', 'tstatus=X', 'img/plus_s.png', $oMain->translate('disabledmodules'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//	}
//	else
//	{
//		$html.= $oMain->stdImglink('list_tmodules', '', 'active', 'tstatus=A', 'img/plus_s.png', $oMain->translate('activemodules'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'manage', 'tstatus=A', 'img/plus_s.png', $oMain->translate('modulesman'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.= $oMain->stdImglink('list_tmodules', '', 'resp', 'tstatus=A', 'img/plus_s.png', $oMain->translate('modulesresp'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//		$html.=$oMain->stdImglink('list_tmodules', '', 'inactive', 'tstatus=X', 'img/plus_s.png', $oMain->translate('disabledmodules'), '', '', '', $oMain->loading());
//		$html.=' &nbsp; ';
//	}
//	
//		
//	if($oMain->operation=='active' OR $oMain->operation=='manage')
//		{$cond="tstatus<>'X'";}
//	elseif ($oMain->operation=='inactive')				
//		{$cond="tstatus='X'";}
//	elseif ($oMain->operation=='resp')				
//		{$cond="userid='$oMain->login' AND tstatus<>'X'";}
//	
//			
//	if($oMain->operation=='manage') //Pages that user manage
//		
//		$sql="SELECT MOD.page as tmodule, dbo.translate_optional(unitext, '$l', MOD.unitext) AS tdesc, dbo.efa_username(MAN.manager) AS managerdesc,dbo.efa_username(MOD.modifiedby) AS modifiedbydesc,MOD.createdate, MOD.userid, MOD.modifdate, MAN.company,MAN.manager,MOD.modifiedby
//		FROM dbo.tbpages AS MOD 
//		INNER JOIN dbo.tbpagemanagers AS MAN ON MOD.page = MAN.page
//		WHERE (MAN.manager = '$oMain->login') AND (MAN.company = '$oMain->comp') AND $cond";
//	
//	elseif(($oMain->operation=='filter'))  //Selected Page (Module)
//		$sql=$this->sqlGetFilter();
//	
//	else
//		$sql="SELECT page as tmodule, dbo.translate_optional(unitext, '$l', unitext) AS tdesc,dbo.efa_username(modifiedby) AS modifiedbydesc,modifdate,userid,createdate,modifiedby
//		FROM dbo.tbpages
//		where $cond";
////print $sql;
//	$rs=dbQuery($oMain->consql, $sql, $flds);
//	$rc=count($rs);
//
//	for ($r = 0; $r < $rc; $r++)
//	{
//		$tmodule=$rs[$r]['tmodule'];
//		
//		$modifiedby=$rs[$r]['modifiedby'].' - '.$rs[$r]['modifiedbydesc'];	
//		$rs[$r]['modifiedby']=$modifiedby;
//		
//		$userid=$rs[$r]['userid'].' - '.$rs[$r]['useriddesc'];
//		$rs[$r]['userid']=$userid;
//			
//		$param='tmodule='.$tmodule;
//		
//		$rs[$r]['tmodule']=$oMain->stdImglink('show_tmodules', '', '',$param, '', $tmodule, '', $oMain->translate('edit_tmodules'), '');
//		
//		$rs[$r]['createdate']= $oMain->formatDate($rs[$r]['createdate']);
//		$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);			
//	}
//					
//	
//	$oTable = new CTable(null, null, $rs);
//	$oTable->SetSorting();
//	$oTable->SetFixedHead (true,400);
//	$oTable->addColumn($oMain->translate('module'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('pagedesc'), 'left', 'String');
//	
//	if($oMain->operation=='manage')
//		$oTable->addColumn($oMain->translate('Manager'), 'left', 'String');
//	
////	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
//	$oTable->addColumn($oMain->translate('tapp'), 'left', 'String');
//	//$oTable->addColumn('!');
//	
//	
//	$html.= $oMain->efaHR();
//	
//	
//	$html.= $oTable->getHtmlCode();
//	
//	$txt=$oMain->translate('modlist').' '.$oMain->translate('fromcomp').' '.$oMain->comp;
//	$txt = str_replace('$NUMBER$',	$rc,$txt);
//	$oMain->subtitle=$txt;
//	
//	
//
//
//	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
//	return($html);
}


	function formdocs($trefaid)
	{

		$oMain=$this->oMain;
	
		$sqlType="SELECT treftypeid FROM dbo.tbshreftype WHERE tcompany='efa' AND tscope='profiler' AND ttype='DOC'";	
		$rsType=dbQuery($oMain->consql, $sqlType, $flds);
		$treftypeid=$rsType[0]['treftypeid'];

		$frmMod=CForm::MODE_EDIT; $warn='';
		if(trim($treftypeid)=='')
		{
			$frmMod=CForm::MODE_VIEW;
			$oMain->stdShowResult(-1,$oMain->translate('noupldfilsn'));
		}

		$formName='frmmoddoc';
		
		$oForm=$oMain->std_form('new_file', '', $formName,1,$frmMod,'','50%', '', '', 'multipart/form-data');

		$aForm=array();
		//$aForm[] = new CFormHidden('tdocid', $this->tdocid);

//		//querie de folders
		$sqlFolders="SELECT RFLD.tfolderid, RFLD.tfoldername
				 FROM dbo.tbshfolder AS RFLD 
				 WHERE RFLD.trefaid='$trefaid'";
		$rs=dbQuery($oMain->consql, $sqlFolders, $flds);
		$this->tfolderid=$rs[0]['tfolderid'];
	
		$aForm[] = new CFormHidden('tfolderid',$this->tfolderid);
		$aForm[] = new CFormHidden('tmodule',$this->tmodule);
//print $this->tmodule;
		//$aForm[] = new CFormSelect($oMain->translate('tfolder'), 'tfolderid', '', '', $sqlFolders, $oMain->consql,'',' ',' ',CForm::REQUIRED,false); 

		$elem = new CFormFile($oMain->translate('file'), 'file', CForm::REQUIRED);
		$elem->setLabelHelp($oMain->translate('obsupload'));
		$aForm[] = $elem;

		$aForm[] = new CFormButton('upload', $oMain->translate ('upload'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);

		$oForm->addElementsCollection($aForm);
		$html=$warn.$oForm->getHtmlCode();

		return $html;

	}



function listDocuments() ///
{
	$oMain=$this->oMain;

	$sql="SELECT RTYP.tcompany, RTYP.tscope, RTYP.ttype, REF.trefa, REF.trefaid, BIND.tdocid, DOCS.trevorder, DOCS.tfileid, DOCS.docname, DOCS.tcreatedon, BIND.tfolderid, 
				  FOL.tfoldername, DOCS.userid, dbo.efa_username(DOCS.userid) AS useriddesc, BIND.tbindid
		  FROM dbo.tbshreftype AS RTYP INNER JOIN
				  dbo.tbshref AS REF ON RTYP.treftypeid = REF.treftypeid INNER JOIN
				  dbo.tbshbind AS BIND ON REF.trefaid = BIND.trefaid INNER JOIN
				  dbo.vwshdocfiles AS DOCS ON DOCS.tdocid = BIND.tdocid AND DOCS.trevorder=(SELECT MAX(trevorder) FROM dbo.tbshdocfvs WHERE tdocid=DOCS.tdocid) LEFT OUTER JOIN
				  dbo.tbshfolder AS FOL ON REF.trefaid = FOL.trefaid AND BIND.tfolderid = FOL.tfolderid
		  WHERE (RTYP.tcompany = 'efa') AND (RTYP.tscope = 'profiler') AND (RTYP.ttype = 'DOC') and REF.trefa='$this->tmodule'";

	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs); 

	//base do link dos docs
	$sqllink="SELECT tvalue FROM [dbo].[tbcompparam] where company ='efa' AND tparam='INTRANET_ROOT'";
	$rs1=dbQuery($oMain->consql, $sqllink, $flds);
	$link=$rs1[0]['tvalue'];
	$shareplace='efa.php?page=shareplace';

	$array=array();
	for ($j = 0; $j < $rc; $j++)
	{
		$rst=$rs[$j];

		$trefaid=$rst['trefaid'];
		$tbindid=$rst['tbindid'];
		$tfolderid=$rst['tfolderid'];
		$crtdate = $oMain->formatDate($rst['tcreatedon']);
		$tdocid=$rst['tdocid'];
		$trevorder=$rst['trevorder'];
		$name=$rst['docname'];

		$param='&tcompany=efa&tscope=profiler&ttype=DOC&tbindid='.$tbindid.'&trefaid='.$trefaid.'
			&trefid='.$this->tmodule.'&tdocid='.$tdocid.'&trevorder='.$trevorder.'&tfolderid='.$tfolderid.'&tmodule='.$this->tmodule;

		if ($oMain->accesslevel >=7) //Apagar Docs
		{
			$deldoc=$oMain->stdImglink('del_file','','',$param,'img/delete_s.png','','',$oMain->translate('del_file'),$oMain->translate('del_file'));
		}

		$img_ext = mb_substr($rst['docname'], mb_strrpos($rst['docname'], '.') + 1);
		$img = './img/shareplace/'.$img_ext.'_s.png';
		if (!file_exists($img))
			$img = './img/shareplace/unknown_s.png';

		$img_param=$docparam='tdocid='.$rst['tdocid'].'&trevorder='.$rst['trevorder'].'&tfileid='.$rst['tfileid'].'&tversion='.$rst['tversion'];
		$img_link = $oMain->linkImg('shareplaceDownloadFile', 'shareplace', $img_param, $img, '_blank', $rst['docname']);
		$directlink = $oMain->stdImglink('shareplaceDownloadFile','shareplace','',$img_param,'',$name);

		$array[]= array('img'		=> $img_link.' '.$directlink,
						'tfolder'	=> $rst['tfoldername'],
						'crtdate'	=> $crtdate,
						'useriddesc'	=> $rst['useriddesc'],
						'toperations'		=> $deldoc);

	}

	$oTable = new CTable(null, null, $array);
	$oTable->SetSorting();
	$oTable->SetFixedHead (false,350);
	$oTable->addColumn($oMain->translate('name'), 'left', 'String');
	$oTable->addColumn($oMain->translate('folder'), 'left', 'String');
	$oTable->addColumn($oMain->translate('creation'), 'center', 'String');
	$oTable->addColumn($oMain->translate('createdby'), 'center', 'String');

	if ($oMain->accesslevel >=7)
		$oTable->addColumn('!', 'center');

	if($rc==0) //obtem trefaid no caso de não existir nenhum documento na referencia
	{
		$sqlrefa="SELECT REF.trefaid
			  FROM dbo.tbshreftype AS TYP INNER JOIN
				  dbo.tbshref AS REF ON TYP.treftypeid = REF.treftypeid
			  WHERE (TYP.tcompany = 'efa') AND (TYP.tscope = 'profiler') AND (TYP.ttype = 'DOC') AND (REF.trefa = '$this->tmodule')";
		$rsrefa=dbQuery($oMain->consql, $sqlrefa, $flds);

		$trefaid=$rsrefa[0]['trefaid'];
	}
//print $trefaid.'<HR>';
	$lb='<a href="'.$link.$shareplace.'&mod=shareplaceViewReferenceTree&operation=&trefaid='.$trefaid.'&l='.$oMain->l.'&sid='.$oMain->sid.'" target="_blank""><img src="img/folder_tree_s.png" border=0></a> ';
	$tit="Module Documents - $lb" ;
	$newdoc=$oMain->showhide($oMain->translate('newdocument'), $this->formdocs($trefaid),'','img/shareplnew_s.png','img/shareplnew_s.png');
	$html= '<table width=100%><tr><td>'.$tit.' '.$newdoc.'</td></tr></table>';

	$html.= $oTable->getHtmlCode ();
	return($html);
}



function showListEvents()
{
	$oMain=$this->oMain;
	
	//$sql="select top 1000 data, Utilizador, maquina, obs, referencia from tbeventos where referencia='$this->tmodule' and aplicacao ='profiler' and tipoevento='tmod' order by data desc";
	
	$sql="SELECT tdate, dbo.efa_username(tuserid) as tuseriddesc, tdeviceid, ttype+' : '+tremarks, tuserid
			FROM [dbo].[tbeventcom] WHERE (tmodule ='profiler' OR tmodule='System') 
			AND trefa='$this->tmodule' ORDER BY tdate DESC";
	
//print $sql;	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{	
		$rs[$r]['data']= $oMain->formatDate($rs[$r]['data']);			
	}
					
	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);

	$oTable->addColumn($oMain->translate('date'), 'left', 'String');
	$oTable->addColumn($oMain->translate('tmodifiedby'), 'left', 'String');
	$oTable->addColumn($oMain->translate('maquina'), 'left', 'String');
	$oTable->addColumn($oMain->translate('obs'), 'left', 'String');
	
	
	$html = $oMain->efaHR( $oMain->translate('userparam'));
	$html.= $oTable->getHtmlCode();
	
	If($rc==0) {return $oMain->translate('nosearchresults');}
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}


}// Enf of pages

class CAccess
{
	var $company;    /**  */
	var $tmodule;    /**  */
	var $application;    /**  */
	var $groupid;    /**  */
	var $userid;    /**  */
	var $accesslevel;    /**  */
	var $modifiedby;    /**  */
	var $modifdate;    /**  */
	//var $copyuserid;
	var $tgpid;  //var to get tgpid from tbgroups

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class accesses mod
	 */	
	function getHtml(&$mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='access';
		
		
		if ($mod =='deluser_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			$mod='listuser_'.$ent;
		}
		
		if ($mod =='delgrp_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			$oMain->operation='GROUP';
			$oMain->default_tab=3;		
			$mod='dash_applications';			
		}
		
		if ($mod =='delmodule_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$mod='listmod_'.$ent;		
		}

		
		if ($mod =='insertuser_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$mod='listuser_'.$ent;		
		}
		
		
		if ($mod =='insertgrp_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$oMain->operation='GROUP';
			$oMain->default_tab=3;
			$mod='dash_applications';	
		}
		
		if ($mod =='insertmodule_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$mod='listmod_'.$ent;		
		}
		
		
		if ($mod =='updateuser_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
				
			$mod='listuser_'.$ent;	
		}
		
		if ($mod =='updategrp_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
				
			$oMain->default_tab=3;
			$mod='dash_applications';
		}
		
		if ($mod =='updatemodule_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
				
			$mod='listmod_'.$ent;
		}
		
		
		if ($mod =='listuser_'.$ent)
		{
			$xNew = new efalayout($this);
			$xNew->subtitle($oMain->translate('new'));
			$xNew->add($this->formUser());
			
			$cont = new efaHiddenBar($this->oMain);
			$cont->add('new')->content($xNew->html())->bar(false);
			$cont->add('user')->content($this->showListUserAccesses())->bar(false);
			$cont->add('group')->content($this->showListUserAcsByGrp())->bar(false);
			$cont->add('all')->content($this->showListUserAllAcs())->bar(false);
			$x = new efalayout($this->oMain);
			$x->title($this->oMain->translate($mod))->icon('img/access_s.png');
			$x->toolbar->add('new')->title($this->oMain->translate('new'))->onClick($cont->part('new')->jsShowHide());
			$x->toolbar->add('user')->title($this->oMain->translate('listuser_access'))->onClick($cont->part('user')->jsShowHide());
			$x->toolbar->add('group')->title($this->oMain->translate('useracsbygrp'))->onClick($cont->part('group')->jsShowHide());
			$x->toolbar->add('all')->title($this->oMain->translate('listalluser_access'))->onClick($cont->part('all')->jsShowHide());
			$x->add($cont->html());
			$html = $x->html();
			$html .='<script>'.$cont->part('user')->jsShowHide().'</script>';


		}
		
		if ($mod =='listalluser_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$x = new efalayout($this);
			$x->title($oMain->translate($mod))->icon('img/access_s.png');
			$x->toolbar->add('all')->link($this->oMain->baseLink('', 'listuser_access').'userid='.$this->userid)->title($oMain->translate('useraccess'));
			$x->add($this->showListUserAllAcs());
			$html = $x->html();
		}
		
		
		if ($mod =='listmod_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html.=$this->showListModAcs();		
			
			$oMain->toolbar_icon('img/new.png',$oMain->BaseLink('','new_tmodules'), $oMain->translate('createmodule'));
		}
		


		$oMain->subtitle=$oMain->translate($mod);
		
		
		if($oMain->operation<>'GROUP')
		{
			if($mod=='listuser_access' OR $mod =='listalluser_access')
			{
				$title=$oMain->Title($this->userid);
				$dashboard='<table width=100%><tr valign=top><td width=195 class=row1>'.$oMain->menuUser($this->userid).'</td>
				<td valign=top>'.$title.'<BR>'.$html.'</td></tr></table>';
				return $this->oMain->layoutUser($html);
			}
			
			if($mod=='listmod_access')
			{
				$omod=new CTmodule($oMain);
				$title=$oMain->Title('', $this->tmodule);
				$dashboard='<table width=100%><tr valign=top><td width=195 class=row1>'.$omod->menuModule($this->tmodule).'</td>
				<td valign=top>'.$title.'<BR>'.$html.'</td></tr></table>';
			}
			
			return($dashboard);
		}
		
		return($html);
	}
	
	function dashUserAcces()
	{
		$oMain=$this->oMain;
		
		$compacces=$this->showListUserAccesses();
		$allacces=$this->showListUserAllAcs();
		
		$html="<table width=99% border=0>
        <tr>
			<td width=70%>$compacces</td>
			<td width=1%></td>
			<td>$allacces</td>
        </tr>
        </table>";
		
		
		return($html);
	}
	
	 /**
	  * read class accesses atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->company=$oMain->comp;
		$this->tmodule=$oMain->GetFromArray('tmodule',$_REQUEST,'string_trim');
		$this->application=$oMain->GetFromArray('application',$_REQUEST,'string_trim');
		$this->groupid=$oMain->GetFromArray('groupid',$_REQUEST,'string_trim');
		$this->userid=$oMain->GetFromArray('userid',$_REQUEST,'string_trim');
		$this->accesslevel=$oMain->GetFromArray('accesslevel',$_REQUEST,'string_trim');
		$this->modifiedby=$oMain->GetFromArray('modifiedby',$_REQUEST,'string_trim');
		$this->modifdate=$oMain->GetFromArray('modifdate',$_REQUEST,'date');
		

		$this->tgpid=$oMain->GetFromArray('tgpid',$_REQUEST,'int');
	}


function form($mod,$op='',$application='',$groupid='')
{	
	$oMain=$this->oMain;
	
	//print $application.'<HR>';
	
	$frmName='frmnewaccess';
	$aForm=array();
	
	if($oMain->operation=='userid' AND ($mod=='edit_access' OR $mod=='gsearch'))
	{
		$aForm[] = new CFormText($oMain->translate('userid'),'userid',$this->userid,4,CForm::REQUIRED,true,'',CFormText::INPUT_INTEGER);
		$aForm[] = new CFormHidden ('application', $this->application);
		$aForm[] = new CFormHidden('tgpid',$this->tgpid);

		$sql="SELECT page, page + ' ' + dbo.translate_unitext(unitext, '$oMain->l') AS tdesc
			  FROM dbo.tbpages WHERE (tstatus = 'A') ORDER BY page";
		$aForm[] = new CFormSelect($this->oMain->translate('tmodule'), 'tmodule', $this->tmodule, $this->tmodule, $sql, $oMain->consql, '', '', '', CForm::RECOMMENDED,true);

		$aForm[] = new CFormText($oMain->translate('accesslevel'),	'accesslevel',$this->accesslevel,4,CForm::REQUIRED,'','',CFormText::INPUT_INTEGER);

		$elem = new CFormButton	('button', $oMain->translate ('update'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$aForm[]=$elem;

		$oForm = $oMain->std_form('update_access', '',$frmName, 3, CForm::MODE_EDIT,'','100%');
		$oForm->addElementsCollection($aForm);
		$html= $oForm->getHtmlCode() ;	
	}
	elseif ($oMain->operation=='groupid' AND ($mod=='edit_access' OR $mod=='gsearch')) 
	{
		
		
		$aForm[] = new CFormText($oMain->translate('groupid'),'groupid',$this->groupid,4,CForm::REQUIRED,true,'',CFormText::INPUT_INTEGER);
		$aForm[] = new CFormHidden ('application', $this->application);
		$aForm[] = new CFormHidden ('tgpid', $this->tgpid);

		$sql="SELECT page, page + ' ' + dbo.translate_unitext(unitext, '$oMain->l') AS tdesc
			  FROM dbo.tbpages WHERE (tstatus = 'A') ORDER BY page";
		$aForm[] = new CFormSelect($this->oMain->translate('tmodule'), 'tmodule', $this->tmodule, $this->tmodule, $sql, $oMain->consql, '', '', '', CForm::RECOMMENDED,true);

		$aForm[] = new CFormText($oMain->translate('accesslevel'),	'accesslevel',$this->accesslevel,4,CForm::REQUIRED,'','',CFormText::INPUT_INTEGER);

		$elem = new CFormButton	('button', $oMain->translate ('update'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$aForm[]=$elem;

		$oForm = $oMain->std_form('update_access', 'groupid',$frmName, 3, CForm::MODE_EDIT,'','100%');
		$oForm->addElementsCollection($aForm);
		$html= $oForm->getHtmlCode() ;	
	
	}
	else //Insert user Access
	{
		$aForm[] = new CFormHidden ('userid', $oMain->userid);
		
		$sql="SELECT page, page + ' ' + dbo.translate_unitext(unitext, '$oMain->l') AS tdesc
			  FROM dbo.tbpages WHERE (tstatus = 'A') ORDER BY page";
		$aForm[] = new CFormSelect($this->oMain->translate('tmodule'), 'tmodule', $this->tmodule, $this->tmodule, $sql, $oMain->consql, '', '', '', CForm::RECOMMENDED);

		$aForm[] = new CFormText($oMain->translate('accesslevel'),	'accesslevel',$this->accesslevel,4,CForm::REQUIRED,'','',CFormText::INPUT_INTEGER);

		$elem = new CFormButton	('button', $oMain->translate ('insertuseraccess'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$aForm[]=$elem;

		$oForm = $oMain->std_form('insert_access', '',$frmName, 2, CForm::MODE_EDIT);
		$oForm->addElementsCollection($aForm);
		$html= $oForm->getHtmlCode() ;
	}
	
	return $html;
}

function formUser() //New User Access
{	
	$oMain=$this->oMain;
	
	$frmName='frmnewusrmodaccess';
	$aForm=array();
	
	$aForm[] = new CFormHidden ('userid', $oMain->userid);

	$sql="SELECT page, page + ' ' + dbo.translate_unitext(unitext, '$oMain->l') AS tdesc
		  FROM dbo.tbpages WHERE (tstatus = 'A') ORDER BY page";
	$aForm[] = new CFormSelect($this->oMain->translate('tmodule'), 'tmodule', $this->tmodule, $this->tmodule, $sql, $oMain->consql, '', '', '', CForm::RECOMMENDED,$lock);

	$aForm[] = new CFormText($oMain->translate('accesslevel'),	'accesslevel',$this->accesslevel,1,CForm::REQUIRED,'','',CFormText::INPUT_INTEGER);

	$elem = new CFormButton	('button', $oMain->translate ('insertuseraccess'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
	$aForm[]=$elem;

	$oForm = $oMain->std_form('insertuser_access', '',$frmName, 2, CForm::MODE_EDIT, false, '100%');
	$oForm->addElementsCollection($aForm);
	$html= $oForm->getHtmlCode() ;
	
	
	return $html;
}

function formModuleUser() //New Module User Access
{	
	$oMain=$this->oMain;
	$html=$oMain->stdJsPopUpWin('400');
	
	$frmName='frmnewusrmodaccess';
	$frmMod=CForm::MODE_EDIT;
	$aForm=array();
	
	$aForm[] = new CFormHidden ('tmodule', $this->tmodule);
	
	$search_userid=$oMain->stdPopupwin('GETCCUSER',$frmName,'userid','useriddesc','userid','useriddesc','','');	
	$field_userid = new CFormText($oMain->translate('userid'),'userid', $this->userid,'',CForm::REQUIRED,false);
	$field_userid_desc = new CFormText($oMain->translate('userid'), 'useriddesc', $this->useriddesc, '','',false, '', '', '', 70);
	if($frmMod==CForm::MODE_EDIT)
	   $field_userid->setExtraData($search_userid);
	$aForm[] = new CFormMultipleElement(array($field_userid, $field_userid_desc), 0);
	
	$aForm[] = new CFormText($oMain->translate('accesslevel'),	'accesslevel',$this->accesslevel,1,CForm::REQUIRED,'','',CFormText::INPUT_INTEGER);

	$elem = new CFormButton	('button', $oMain->translate ('insertuseraccess'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);
	$aForm[]=$elem;

	$oForm = $oMain->std_form('insertmodule_access', '',$frmName, 1, CForm::MODE_EDIT, false, '50%');
	$oForm->addElementsCollection($aForm);
	$html.= $oForm->getHtmlCode() ;
	
	
	return $html;
}

function formModuleGrp() //New Module Group Access
{	
	$oMain=$this->oMain;
	$html=$oMain->stdJsPopUpWin('400');
	
	$frmName='frmnewgrpmodaccess';
	//$frmMod=CForm::MODE_EDIT;
	$aForm=array();
	
	$aForm[] = new CFormHidden ('tmodule', $this->tmodule);
	
	$sqlapp="SELECT application, application AS tdesc FROM dbo.tbapplications WHERE (company = '$oMain->comp')";
	$cformsel = new CFormSelect($oMain->translate('application'), 'application', $this->application, '', $sqlapp, $oMain->consql, '', '', ' ', CForm::REQUIRED);
	$cformsel->addEvent("onChange=\"frmnewgrpmodaccess.mod.value='listmod_access'; frmnewgrpmodaccess.operation.value='onchange'; frmnewgrpmodaccess.submit(); ".$oMain->loading()."\"");
	$aForm[]=$cformsel;

	$sqlgroup="SELECT groupid, RTRIM(groupid) + ' - ' + groupdesc AS tdesc FROM dbo.tbgroups WHERE (company = '$oMain->comp') AND (application='$this->application')";
	$aForm[] = new CFormSelect($oMain->translate('groupid'), 'groupid', $this->groupid, '', $sqlgroup, $oMain->consql, '', '', '', CForm::REQUIRED);
	

	$aForm[] = new CFormText($oMain->translate('accesslevel'),	'accesslevel',$this->accesslevel,1,CForm::REQUIRED,'','',CFormText::INPUT_INTEGER);

	$elem = new CFormButton	('button', $oMain->translate ('insertgrpaccess'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_BOTTOM);
	$aForm[]=$elem;

	$oForm = $oMain->std_form('insertmodule_access', '',$frmName, 1, CForm::MODE_EDIT, false, '50%');
	$oForm->addElementsCollection($aForm);
	$html.= $oForm->getHtmlCode() ;
	
	
	return $html;
}




function formGrp($groupid,$application,$tgpid)
{	
	$oMain=$this->oMain;
	
	$frmName='frmGrpAccess';
	$aForm=array();
	
	$aForm[] = new CFormHidden('groupid',$groupid);
	$aForm[] = new CFormHidden('application',$application);
	$aForm[] = new CFormHidden('company',$oMain->comp);
	$aForm[] = new CFormHidden('tgpid',$tgpid);
	$aForm[] = new CFormHidden('tnodeid',$tgpid);

	$sql="SELECT page, page + ' ' + dbo.translate_unitext(unitext, '$oMain->l') AS tdesc
		  FROM dbo.tbpages WHERE (tstatus = 'A') ORDER BY page";
	$aForm[] = new CFormSelect($this->oMain->translate('tmodule'), 'tmodule', $this->tmodule, $this->tmodule, $sql, $oMain->consql, '', '', '', CForm::RECOMMENDED);

	$aForm[] = new CFormText($oMain->translate('accesslevel'),	'accesslevel','',4,CForm::REQUIRED,'','',CFormText::INPUT_INTEGER);

	$elem = new CFormButton	('button', $oMain->translate ('insertgrpaccess'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
	$aForm[]=$elem;

	$oForm = $oMain->std_form('insertgrp_access', 'groupid',$frmName, 2, CForm::MODE_EDIT);
	$oForm->addElementsCollection($aForm);
	$html= $oForm->getHtmlCode() ;
	
	return $html;
}

	/**
	 * save class accesses record into database
	 */	
function storeIntoDB($operation, &$tdesc)
{
	$sid=$this->oMain->sid;
	$sql="[dbo].[spaccesses] '$sid','$operation'
	,'$this->tmodule'
	,'$this->application'
	,'$this->groupid'
	,'$this->userid'
	,'$this->accesslevel'
	";
//print $sql.'<HR>'; die;

	$rs=dbQuery($this->oMain->consql, $sql, $flds);
	$rst=$rs[0];
	$tdesc=$rst['tdesc'];
	return($rst['tstatus']);
}
	/**
	 * query to get class accesses record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;	
		
		$sql="SELECT GRP.tgpid, ACC.company, ACC.page as tmodule, ACC.application, ACC.groupid, ACC.userid, ACC.accesslevel, ACC.modifiedby, ACC.modifdate
			  FROM dbo.tbgroups AS GRP 
			  INNER JOIN dbo.tbaccesses AS ACC ON GRP.company = ACC.company
			  WHERE ACC.company='$this->company' AND ACC.page='$this->tmodule' AND ACC.application='$this->application' AND ACC.groupid='$this->groupid' 
		      AND ACC.userid='$this->userid'";

		//print $sql.'<HR>';

		return($sql);
	}
	/**
	 * set class accesses atributes with data from database
	 */	
	function readfromdb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		//print $sql;
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->company=$rst['company'];
			$this->tmodule=$rst['tmodule'];
			$this->application=$rst['application'];
			$this->groupid=$rst['groupid'];
			$this->tgpid=$rst['tgpid'];
			$this->userid=$rst['userid'];
			$this->accesslevel=$rst['accesslevel'];
			$this->modifiedby=$rst['modifiedby'];
			$this->modifdate=$rst['modifdate'];	
		}
		return $rc;
	}

function showListUserAccesses()
{
	$oMain=$this->oMain;
	
	$sql="SELECT page AS tmodule,accesslevel,modifiedby,modifdate,'' as toperations,company,userid,application,groupid,dbo.efa_username(modifiedby) AS modifiedbydesc
			  FROM dbo.tbaccesses 
			  WHERE company='$oMain->comp' AND userid='$this->userid'
			  ORDER BY accesslevel DESC";
	
	//print $sql;
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		$userid=$rs[$r]['userid'];
		$company=$rs[$r]['company'];
		$tmodule=$rs[$r]['tmodule'];
		$application=$rs[$r]['application'];
		$groupid=$rs[$r]['groupid'];
		
		$modifiedby=$rs[$r]['modifiedby'].' - '.$rs[$r]['modifiedbydesc'];
		
		$param='company='.$company.'&tmodule='.$tmodule.'&application='.$application.'&groupid='.$groupid.'&userid='.$userid;
		
		$rs[$r]['accesslevel']='<table CELLPADDING=0 CELLSPACING=0>'.$oMain->stdForm('updateuser_access','','frmuseraccess'.$r).'<tr><td>
			<input type=text size=5 value="'.$rs[$r]['accesslevel'].'" name=accesslevel>
			<input type=hidden value='.$userid.' name=userid>
			<input type=hidden value='.$tmodule.' name=tmodule>
			</td></tr></form></table>';
		
		$rs[$r]['tmodule']=$oMain->stdImglink('show_tmodules', '', '',$param, '', $tmodule, '', $oMain->translate('editaccess'), '');
		
		$rs[$r]['modifiedby']=$modifiedby;
		
		$rs[$r]['toperations']='<img src="img/save_s.png" border=0 onmouseover="this.style.cursor=\'hand\';" onclick="frmuseraccess'.$r.'.submit();">';
		$rs[$r]['toperations'].=' '.$oMain->stdImglink('deluser_access', '', '',$param, 'img/delete_s.png', '', '', $oMain->translate('delaccess'), $oMain->translate('confdel'));
		
		$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);
				
	}
					
	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (false,250);

	$oTable->addColumn($oMain->translate('tmodule'), 'left', 'String');
	$oTable->addColumn($oMain->translate('accesslevel'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
	$oTable->addColumn('!');
	
	$allcomps=$oMain->stdImglink('listalluser_access', '', '', 'userid='.$this->userid, 'img/comps_s.png', $oMain->translate('userallaccess'), '', '', '', $oMain->loading());
	$new=$oMain->showHide($oMain->translate('new'), $this->formUser(),'','img/new_s.png','img/new_s.png', '', '', 'class="rowpink"');
	$html = $oMain->efaHR( $oMain->translate('useraccess').' - ('.$oMain->comp.') &nbsp; | &nbsp; '.$allcomps.' &nbsp; | &nbsp; '.$new);
	
	$html.= $oTable->getHtmlCode();
	
	$x = new efalayout($this);
	$x->subtitle($oMain->translate('useraccess'));
	$x->add($oTable->getHtmlCode());
	return $x->html();
	
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}


function showListUserAllAcs()
{
	$oMain=$this->oMain;
	
	//$this->userid=$userid;
	
	
	$sql="SELECT page AS tmodule,accesslevel,company, modifiedby,modifdate,'' as toperations,userid,application,groupid,dbo.efa_username(modifiedby) AS modifiedbydesc
			  FROM dbo.tbaccesses 
			  WHERE company<>'$oMain->comp' AND userid='$this->userid'
			  ORDER BY company";
	
	//print $sql;
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
					
	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);

	$oTable->addColumn($oMain->translate('tmodule'), 'left', 'String');
	$oTable->addColumn($oMain->translate('accesslevel'), 'left', 'String');
	$oTable->addColumn($oMain->translate('company'), 'left', 'String');
	
	$comp=$oMain->stdImglink('listuser_access', '', '', 'userid='.$this->userid, 'img/comps_s.png', $oMain->translate('useraccess'), '', '', '', $oMain->loading());
	$html = $oMain->efaHR($oMain->translate('alluseraccess').' &nbsp; | &nbsp; '.$comp);
	$html = '';
	$html.= $oTable->getHtmlCode();
	
	$x = new efalayout($this);
	$x->subtitle($oMain->translate('alluseraccess'));
	$x->add($oTable->getHtmlCode());
	return $x->html();
	
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}


function showListUserAcsByGrp()
{
	$oMain=$this->oMain;

	$tuserid=$oMain->getTuserid($this->userid);
	
	$sql="SELECT ACS.page, ACS.application, ACS.groupid, ACS.accesslevel, GRP.tgpid
			FROM dbo.tbgroupmember AS MENB INNER JOIN
			dbo.tbgroups AS GRP ON MENB.tgpid = GRP.tgpid INNER JOIN
			dbo.tbaccesses AS ACS ON GRP.company = ACS.company AND GRP.application = ACS.application AND GRP.groupid = ACS.groupid
		WHERE MENB.tuserid = '$tuserid' AND GRP.company = '$oMain->comp' AND GRP.tstatus='A'
		ORDER BY ACS.page, ACS.application, ACS.groupid";
	
	//print $sql;
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
					
	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,250);

	$oTable->addColumn($oMain->translate('tmodule'), 'left', 'String');
	$oTable->addColumn($oMain->translate('application'), 'left', 'String');
	$oTable->addColumn($oMain->translate('groupid'), 'left', 'String');
	$oTable->addColumn($oMain->translate('accesslevel'), 'left', 'String');

	
	$x = new efalayout($this);
	$x->subtitle($oMain->translate('useracsbygrp'));
	$x->add($oTable->getHtmlCode());
	return $x->html();
}



function showListAllCompUsrAces($userid)
{
	$oMain=$this->oMain;
	
	$this->userid=$userid;
	
	
	$sql="SELECT company, page AS tmodule,accesslevel,modifiedby,modifdate,'' as toperations,company,userid,application,groupid,dbo.efa_username(modifiedby) AS modifiedbydesc
		  FROM dbo.tbaccesses 
		  WHERE userid='$this->userid' and company<>'$oMain->comp'
		  ORDER BY company,tmodule,accesslevel";
	
	//print $sql;
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		$userid=$rs[$r]['userid'];
		$company=$rs[$r]['company'];
		$tmodule=$rs[$r]['tmodule'];
		$application=$rs[$r]['application'];
		$groupid=$rs[$r]['groupid'];
		
		$modifiedby=$rs[$r]['modifiedby'].' - '.$rs[$r]['modifiedbydesc'];
		
		//$param='company='.$company.'&tmodule='.$tmodule.'&application='.$application.'&groupid='.$groupid.'&userid='.$userid;
		
		$rs[$r]['modifiedby']=$modifiedby;
		$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);
				
	}
					
	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);

	$oTable->addColumn($oMain->translate('company'), 'left', 'String');
	$oTable->addColumn($oMain->translate('tmodule'), 'left', 'String');
	$oTable->addColumn($oMain->translate('accesslevel'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
	//$oTable->addColumn('!');
	
	
	$html = $oMain->efaHR( $oMain->translate('useraccess'));
	
	$html.= $oTable->getHtmlCode();
	
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}


function showListGroupAccesses($tgroupid, $application, $tgpid)
{
	$oMain=$this->oMain;

	$sql="SELECT AC.page AS tmodule,AC.accesslevel,AC.modifiedby,AC.modifdate,'' as toperations,AC.company,AC.userid,AC.application,AC.groupid
		,dbo.efa_username(AC.modifiedby) AS modifiedbydesc, GR.tgpid
		FROM dbo.tbaccesses AC INNER JOIN
		dbo.tbgroups GR ON GR.application=AC.application AND GR.groupid=AC.groupid AND GR.company=AC.company
		WHERE AC.company='$oMain->comp' AND AC.groupid='$tgroupid' and AC.application='$application'";
//print $sql;	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		$company=$rs[$r]['company'];
		$tmodule=$rs[$r]['tmodule'];
		$application=$rs[$r]['application'];
		$groupid=$rs[$r]['groupid'];
		$tgpid=$rs[$r]['tgpid'];
		$modifiedby=$rs[$r]['modifiedby'].' - '.$rs[$r]['modifiedbydesc'];
		
		$param='company='.$company.'&tmodule='.$tmodule.'&application='.$application.'&groupid='.$groupid.'&tnodeid='.$tgpid.'&tgpid='.$tgpid;
		$rs[$r]['tmodule']=$oMain->stdImglink('show_tmodules', '', '',$param, '', $tmodule, '', $oMain->translate('editaccess'), '');
		
		$rs[$r]['accesslevel']='<table CELLPADDING=0 CELLSPACING=0>'.$oMain->stdForm('updategrp_access','','frmgrpaccess'.$r).'<tr><td>
			<input type=text size=5 value="'.$rs[$r]['accesslevel'].'" name=accesslevel>
			<input type=hidden value='.$tmodule.' name=tmodule>
			<input type=hidden value='.$application.' name=application>
			<input type=hidden value='.$groupid.' name=groupid>
			<input type=hidden value='.$tgpid.' name=tnodeid>
			<input type=hidden value='.$tgpid.' name=tgpid>
			</td></tr></form></table>';
		
		$rs[$r]['modifiedby']=$modifiedby;
		
		//$rs[$r]['toperations']=$oMain->stdImglink('edit_access', '', 'groupid',$param, 'img/edit_s.png', '', '', $oMain->translate('editaccess'), '')
		$rs[$r]['toperations']='<img src="img/save_s.png" border=0 onmouseover="this.style.cursor=\'hand\';" onclick="frmgrpaccess'.$r.'.submit();">';
		$rs[$r]['toperations'].=' '.$oMain->stdImglink('delgrp_access', '', 'groupid',$param, 'img/delete_s.png', '', '', $oMain->translate('delaccess'), $oMain->translate('confdel'));
		
		$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);		
	}
					
	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);

	$oTable->addColumn($oMain->translate('tmodule'), 'left', 'String');
	$oTable->addColumn($oMain->translate('accesslevel'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
	$oTable->addColumn('!');
	
	$add=$oMain->showHide($oMain->translate('insertgrpaccess'), $this->formGrp($tgroupid,$application,$tgpid), '', 'img/new_s.png', 'img/new_s.png');
	$html = $oMain->efaHR($oMain->translate('group').' &nbsp; | &nbsp; '.$tgroupid.' &nbsp; | &nbsp; '.$add);
	
	$html.= $oTable->getHtmlCode();
	
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}

function showListModAcs()
{
	$oMain=$this->oMain;
//print 111;	
	
	$sql="SELECT '' as type,(SELECT TOP 1 company FROM dbo.tbgroups WHERE tgpid=A.tgpid) AS comp
		, A.[application],A.groupid,dbo.efa_username(A.userid)+' - '+A.userid AS useriddesc
		,A.accesslevel,A.modifiedby,A.modifdate
			,'' as toperations,dbo.efa_username(A.modifiedby) AS modifiedbydesc,A.userid, A.[page] AS tmodule,A.company
			, tgpid = CASE WHEN (groupid <>'' AND [application]<>'')  THEN (SELECT tgpid FROM dbo.tbgroups WHERE company=A.company AND [application]=A.[application] AND groupid=A.groupid)
			ELSE -1
			END
		FROM dbo.tbaccesses A
		WHERE A.company='$oMain->comp' AND A.[page]='$this->tmodule'
		ORDER BY A.groupid DESC";
	
	//print $sql;
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		$userid=$rs[$r]['userid'];
		$company=$rs[$r]['company'];
		$tmodule=$rs[$r]['tmodule'];
		$application=$rs[$r]['application'];
		$groupid=$rs[$r]['groupid'];
		$tgpid=$rs[$r]['tgpid'];

		$modifiedby=$rs[$r]['modifiedby'].' - '.$rs[$r]['modifiedbydesc'];
		
		$param='company='.$company.'&tmodule='.$tmodule.'&application='.$application.'&groupid='.$groupid.'&userid='.$userid;
		
		$rs[$r]['type']='<img src="img/user_s.png" border=0 title="User">';
		if($userid=='')
			$rs[$r]['type']='<img src="img/group_s.png" border=0 title="Group">';
		
		$appparam='tnodeid=APP_'.strtoupper($application).'&tappl='.strtoupper($application).'&company='.$company;
		$grpparam='tnodeid='.$tgpid.'&company='.$company.'&tgpid='.$tgpid;
		$rs[$r]['application']=$oMain->stdImglink('dash_applications', '', '',$appparam, '', $application, '', $oMain->translate('editapp'), '');
		$rs[$r]['groupid']=$oMain->stdImglink('dash_applications', '', 'groupid',$grpparam, '', $groupid, '', $oMain->translate('editgroups'), '');
		
//		$rs[$r]['groupid']=$oMain->stdImglink('edit_groups', '', 'groups',$param, '', $groupid, '', $oMain->translate('editgroups'), '');
//		$rs[$r]['application']=$oMain->stdImglink('edit_applications', '', 'applications',$param, '', $application, '', $oMain->translate('editapplications'), '');
		
		$rs[$r]['accesslevel']='<table CELLPADDING=0 CELLSPACING=0>'.$oMain->stdForm('updatemodule_access','','frmmodaccess'.$r).'<tr><td>
			<input type=text size=5 value="'.$rs[$r]['accesslevel'].'" name=accesslevel>
			<input type=hidden value='.$userid.' name=userid>
			<input type=hidden value='.$application.' name=application>
			<input type=hidden value='.$groupid.' name=groupid>
			<input type=hidden value='.$tmodule.' name=tmodule>
			<input type=hidden value='.$company.' name=company>
			</td></tr></form></table>';
		
		
		$rs[$r]['modifiedby']=$modifiedby;
		
		$rs[$r]['toperations']='<img src="img/save_s.png" border=0 onmouseover="this.style.cursor=\'hand\';" onclick="frmmodaccess'.$r.'.submit();">';
		//$rs[$r]['toperations']=$oMain->stdImglink('edit_access', '', 'module',$param, 'img/edit_s.png', '', '', $oMain->translate('editaccess'), '');
		$rs[$r]['toperations'].=' '.$oMain->stdImglink('delmodule_access', '', 'module',$param, 'img/delete_s.png', '', '', $oMain->translate('delaccess'), $oMain->translate('confdel'));
		
		$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);
				
	}
					
	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);

	$oTable->addColumn($oMain->translate('type'), 'left', 'String');
	$oTable->addColumn($oMain->translate('company'), 'left', 'String');
	$oTable->addColumn($oMain->translate('application'), 'left', 'String');
	$oTable->addColumn($oMain->translate('groupid'), 'left', 'String');
	$oTable->addColumn($oMain->translate('userid'), 'left', 'String');
	$oTable->addColumn($oMain->translate('accesslevel'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
	$oTable->addColumn('!');
	
	
	

	$expand=0;
	if($oMain->operation=='onchange')
		$expand=1;
	$newgroup=$oMain->showHide($oMain->translate('newgrpaccess'), $this->formModuleGrp(),$expand,'img/new_s.png','img/new_s.png', '', '', 'class="rowpink"');
	
	$newuser=$oMain->showHide($oMain->translate('newuseraccess'), $this->formModuleUser(),'','img/new_s.png','img/new_s.png', '', '', 'class="rowpink"',' &nbsp; | &nbsp; '.$newgroup);
	
	$html = $oMain->efaHR($oMain->translate('moduleaccess').' - '.$this->tmodule.' &nbsp; | &nbsp; '.$newuser);
	
	$html.= $oTable->getHtmlCode();
	
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}



function formModuleAccesses($module)
{
	$oMain=$this->oMain;
	
	$loading="document.getElementById('pleaseWait').style.display='block'; return true;";
	if($oMain->accesslevel>=0)
	{
		if($oMain->mod=='group_access')
		{
			$appgrpreq = CForm::REQUIRED;
			$userreq='';
		}
		else if($oMain->mod=='userid_access')
		{
			$appgrpreq = '';
			$userreq = CForm::REQUIRED;
		}
		else if($oMain->mod=='edit_access')
		{
			//$this->readfromdb();
			$appgrpreq = '';
			$userreq = '';
		}
		else
		{
			$appgrpreq = CForm::REQUIRED;
			$userreq = CForm::REQUIRED;
		}

		//applications listbox
		$sql="SELECT application, application AS tdesc FROM dbo.tbapplications WHERE (company = '$oMain->comp')";
		$applications = new CFormSelect($this->oMain->translate('application'), 'application', $this->application, '', $sql, $oMain->consql, '', '', ' ', $appgrpreq);
		$applications->addEvent("onChange=\"frm_newaccess.mod.value='group_access'; frm_newaccess.userid.value=''; javascript:frm_newaccess.submit(); $loading\"");

		//group listbox
		$sql="SELECT groupid, RTRIM(groupid) + ' - ' + groupdesc AS tdesc FROM dbo.tbgroups WHERE (company = '$oMain->comp') AND (application='$this->application')";
		$groups = new CFormSelect($this->oMain->translate('groups'), 'groupid', $this->groupid, '', $sql, $oMain->consql, '', '', '', $appgrpreq);

		//user listbox
		$sql="SELECT userid, username + ' - (' + userid + ')' AS username FROM dbo.vwcompany_members WHERE (company = '$oMain->comp') AND (tstatus = 'A') ORDER BY username";
		$users = new CFormSelect($oMain->translate('user'), 'userid', $this->userid, '', $sql, $oMain->consql, '', '', ' ', $userreq);
		$users->addEvent("onChange=\"frm_newaccess.mod.value='userid_access'; frm_newaccess.application.value=''; frm_newaccess.groupid.value=''; javascript:frm_newaccess.submit(); $loading\"");

		//new record
		if($oMain->mod=='edit_access')
			$formEdit = $oMain->std_form('update_access', 'module','frm_newaccess', 1, CForm::MODE_EDIT, true, '98%');
		else
			$formEdit = $oMain->std_form('insert_access', 'module','frm_newaccess', 1, CForm::MODE_EDIT, true, '98%');
		$form_elem = array();
		$form_elem[] = new CFormHidden('inactives',$this->inactives);
		$form_elem[] = new CFormHidden('tmodule',$module);
		$form_elem[] = new CFormHidden('scope','module');
		$form_elem[] = new CFormHidden('default_tab',2);
		$form_elem[] = $applications;
		$form_elem[] = $groups;
		$form_elem[] = $users;
		$form_elem[] = new CFormText($oMain->translate('accesslevel'), 'accesslevel', $this->accesslevel, 10, CForm::REQUIRED, '', '', CFormText::INPUT_INTEGER, '', '20');
		$formEdit->addElementsCollection($form_elem);

		if($oMain->mod=='edit_access')
			$button_new =  new CFormButton('button', $this->oMain->translate('update'), CFormButton::TYPE_SUBMIT, '', CFormButton::LOCATION_FORM_RIGHT);
		else
			$button_new =  new CFormButton('button', $this->oMain->translate('insert'), CFormButton::TYPE_SUBMIT, '', CFormButton::LOCATION_FORM_RIGHT);
		$formEdit->addElement($button_new);
		
		$html = $formEdit->getHtmlCode();
	}

	return($html);
}


}// Enf of CAccesses

class CNetStat
{
	public $oMain;
	
	var $year;

	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

function getHtml($mod)
{
	
	$oMain=$this->oMain;
	$this->readFromRequest();
	$ent='netstat'; 
	//$userid=$this->userid;
	$rc='';
	
	if($mod=='show_netstat')
	{
		$oMain->subtitle=$oMain->translate($mod);
		$html=$this->drawTabs();
	}
	
	if($mod=='admin_netstat')
	{
		$oMain->subtitle=$oMain->translate($mod);
		$html=$this->synergyNetMan();
		$html.='<BR>';
		$html.=$this->synergyNetAdmin();
	}
	
	if($mod=='topmod_netstat')
	{
		$oMain->subtitle=$oMain->translate($mod);
		$topmonth=$this->topmodulesmonth();
		$all.=$this->topmodules();
		
		$html="<table border=0 width=100%>
        <tr>
        <td>$topmonth</td>
		<td width=1%></td>
		<td>$all</td>
        </tr>
		</table>";
	}
	
	if($mod=='active_netstat')
	{
		$oMain->subtitle=$oMain->translate($mod);
		$html=$this->activeSessions($rc);
	}
	
	if($mod=='synusers_netstat')
	{
		$oMain->subtitle=$oMain->translate($mod);
		$html.=$this->synergieUsers();
	}
	

	if($mod=='obsolete_netstat')
	{
		$oMain->subtitle=$oMain->translate($mod);
		$html=$this->obsoleteUsers();
	}
	
	if($rc<>'')
		$rc=' | ('.$rc.')';
	
	$title=$oMain->Title('',$oMain->translate($mod).' '.$rc);
	$dashboard='<table width=100%><tr valign=top><td width=195 class=row1>'.$oMain->menuInfo().'</td>
	<td valign=top>'.$title.'<BR>'.$html.'</td></tr></table>';

	return($dashboard);

}

function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->year=$oMain->GetFromArray('year',$_REQUEST,'int');
//		$this->company=$oMain->GetFromArray('company',$_REQUEST,'string_trim');
//		$this->tfield=$oMain->GetFromArray('tfield',$_REQUEST,'string_trim');
//		$this->tvalue=$oMain->GetFromArray('tvalue',$_REQUEST,'string_trim');
//		$this->modifiedby=$oMain->GetFromArray('modifiedby',$_REQUEST,'string_trim');
//		$this->modifdate=$oMain->GetFromArray('modifdate',$_REQUEST,'date');
		
	}


// Get obsolete users still active in SynergyNet
function obsoleteUsers()
{
	$oMain=$this->oMain;
	
	if($oMain->accesslevel<9)
	{return $oMain->translate('snetmanagersonly');}	

	$sql="SELECT  UU.employee, UU.email
		,(select max(tlasthit) from tbhit where tuserid=UU.employee) as lasthit		
		,'' as toper, UU.userid, UU.username 
	
	FROM webrh.dbo.vcadastro_activos AS RH RIGHT OUTER JOIN
	   dbo.tbusers AS UU ON RH.nre = UU.employee
	WHERE  (RH.nre IS NULL) AND UU.partner='' AND UU.tstatus='A' AND UU.employee<998900
	order by UU.employee"; //UU.employee<98000000
//print $sql;
	$rs=dbQuery($oMain->consql, $sql, $flds);
	//var_dump($rs);
	$rc=count($rs); //print($rc.'||'.$sql.'<HR>');
	for ($r = 0; $r < $rc; $r++) //number formating
	{
		$rst=$rs[$r];
		$employee=$rst['employee'];
		$username=$rst['username'];
		$lasthit=$oMain->formatDate($rst['lasthit']);
		$temnophoto="<img src=\"".$oMain->stdGetUserPicture($employee)."\"  height=25>";
		//print $lasthit.'<BR>';
		
		$rs[$r]['lasthit']=$lasthit;
		$rs[$r]['employee']=$oMain->stdImglink('show_users', '', '', 'userid='.$rst['userid'], '', $temnophoto.' '.$employee.' - '.$username, '', '', $oMain->loading());
		$rs[$r]['toper']=$oMain->stdImglink('setstatus_users', '', '', "employee=$rst[employee]&tstatus=X&userid=$rst[userid]", 'img/plus_s.png', $oMain->translate('deactivateuser'), '', $oMain->translate('deactivateuser'), $oMain->translate('confDeactivateuser'), $oMain->loading());
	}

	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (TRUE,400);
	$oTable->addColumn($oMain->translate('employee'),	'left',		'String');
	$oTable->addColumn($oMain->translate('email'),		'left',		'String');
	$oTable->addColumn($oMain->translate('lasthit'),	'left',		'String');
	$oTable->addColumn($oMain->translate('operations'),	'left');

	return $oMain->translate('inactivatecandidates').' - ('.$rc.')'.$oTable->getHtmlCode ();
	
}

function activeSessions(&$rc)
{
	$oMain=$this->oMain;

	$sql = "SELECT USERS.userid, SESS.startdate, SESS.Localizacao, SESS.TimeOut, USERS.username
	FROM dbo.tbSessoes SESS LEFT OUTER JOIN dbo.tbusers USERS ON SESS.Utilizador = USERS.userid
	ORDER BY USERS.username";

	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
//print($rc.'||'.$sql.'<HR>');
	//$num=$rc;
	for ($r = 0; $r < $rc; $r++) //number formating
	{
		$userid=$rs[$r]['userid'];
		$username=$rs[$r]['username'];
		$rs[$r]['userid']=$oMain->stdImglink('show_users', '', '', 'userid='.$rs[$r]['userid'], '', $userid.' - '.$username, '', '', $oMain->loading());
		$rs[$r]['startdate']= $oMain->formatDate($rs[$r]['startdate']);
	}

	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (TRUE,400);
	$oTable->addColumn($oMain->translate('user'),	'left',		'String');
	//$oTable->addColumn($oMain->translate('name'),	'left',		'String');
	$oTable->addColumn($oMain->translate('date'),	'left',		'String');
	$oTable->addColumn($oMain->translate('local'),	'left',		'String');
	$oTable->addColumn($oMain->translate('timeout'),	'center',	'String');

	$html = $oTable->getHtmlCode ();
	return($html);
}


function synergyNetAdmin()
{
	$oMain=$this->oMain;

	$sql="SELECT USR.employee, USR.username, USR.email, USR.modifiedby, USR.modifdate, USR.userid
			FROM dbo.tbaccesses AS ACS FULL OUTER JOIN
                      dbo.tbgroupmember AS MEM INNER JOIN
                      dbo.tbgroups AS GRP ON MEM.tgpid = GRP.tgpid INNER JOIN
                      dbo.tbusers AS USR ON MEM.tuserid = USR.employee ON ACS.company = GRP.company AND ACS.application = GRP.application AND 
                      ACS.groupid = GRP.groupid
			WHERE (ACS.page = 'profiler') AND (ACS.accesslevel = 9) AND (ACS.company = '$oMain->comp') AND (ACS.groupid <> '')
			UNION
			SELECT USR.employee, USR.username, USR.email, USR.modifiedby, USR.modifdate,  USR.userid
			FROM  dbo.tbaccesses AS ACS INNER JOIN
				dbo.tbusers AS USR ON ACS.userid = USR.userid
			WHERE  (ACS.page = 'profiler') AND (ACS.accesslevel = 9) AND (ACS.company = '$oMain->comp') AND (ACS.groupid = '')";

//print $sql;
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	$num=$rc;
	for ($r = 0; $r < $rc; $r++) //number formating
	{
		$company=$rs[$r]['company'];
		$param='userid='.$rs[$r]['userid'];
		$email=$rs[$r]['email'];
		$emailto='<a href=mailto:'.$email.'>'.$email.'</a>';
		$link=$oMain->stdImglink('show_users', '', '', $param, '',$rs[$r]['employee'], '', $oMain->translate('show_users'));
		
		$rs[$r]['employee']= $link;
		$rs[$r]['email']=$emailto;
		$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);
	}

	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (0,100);
	$oTable->addColumn($oMain->translate('tuserid'),	'left',		'String');
	$oTable->addColumn($oMain->translate('username'),	'left',		'String');
	$oTable->addColumn($oMain->translate('email'),	'left',		'String');
	$oTable->addColumn($oMain->translate('modifiedby'),	'left',		'String');
	$oTable->addColumn($oMain->translate('modifdate'),	'center',	'String');

	$html= $oMain->efaHR($oMain->translate('synadmin').' '.$company);
	$html.= $oTable->getHtmlCode ();
	return($html);
}


function synergyNetMan()
{
	$oMain=$this->oMain;

	$sql="SELECT USR.employee, USR.username, USR.email, USR.modifiedby, USR.modifdate, USR.userid
			FROM dbo.tbaccesses AS ACS FULL OUTER JOIN
                      dbo.tbgroupmember AS MEM INNER JOIN
                      dbo.tbgroups AS GRP ON MEM.tgpid = GRP.tgpid INNER JOIN
                      dbo.tbusers AS USR ON MEM.tuserid = USR.employee ON ACS.company = GRP.company AND ACS.application = GRP.application AND 
                      ACS.groupid = GRP.groupid
			WHERE (ACS.page = 'profiler') AND (ACS.accesslevel = 8) AND (ACS.company = '$oMain->comp') AND (ACS.groupid <> '')
			UNION
SELECT USR.employee, USR.username, USR.email, ACS.tmodifiedby, ACS.tmodifdate, ACS.tmodifiedby 
FROM dbo.tbmoduseracc	AS ACS INNER JOIN 
	 dbo.tbusers		AS USR ON ACS.tuserid = USR.employee 
WHERE (ACS.tmodule = 195) AND (ACS.taccesslevel = 8) AND (ACS.tcompany = '$oMain->comp')"; 

/*	
			SELECT USR.employee, USR.username, USR.email, USR.modifiedby, USR.modifdate,  USR.userid
			FROM  dbo.tbaccesses AS ACS INNER JOIN
				dbo.tbusers AS USR ON ACS.userid = USR.userid
			WHERE  (ACS.page = 'profiler') AND (ACS.accesslevel = 8) AND (ACS.company = '$oMain->comp') AND (ACS.groupid = '')";
*/

//print $sql;
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	$num=$rc;
	for ($r = 0; $r < $rc; $r++) //number formating
	{
		$company=$rs[$r]['company'];
		$param='userid='.$rs[$r]['userid'];
		$email=$rs[$r]['email'];
		$emailto='<a href=mailto:'.$email.'>'.$email.'</a>';
		$link=$oMain->stdImglink('show_users', '', '', $param, '',$rs[$r]['employee'], '', $oMain->translate('show_users'));
		
		$rs[$r]['employee']= $link;
		$rs[$r]['email']=$emailto;
		$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);
	}

	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (0,100);
	$oTable->addColumn($oMain->translate('tuserid'),	'left',		'String');
	$oTable->addColumn($oMain->translate('username'),	'left',		'String');
	$oTable->addColumn($oMain->translate('email'),	'left',		'String');
	$oTable->addColumn($oMain->translate('modifiedby'),	'left',		'String');
	$oTable->addColumn($oMain->translate('modifdate'),	'center',	'String');

	$html= $oMain->efaHR($oMain->translate('synman').' '.$company);
	$html.= $oTable->getHtmlCode ();
	return($html);
}



function topmodulesmonth()
{
	$oMain=$this->oMain;

	//all hits
	
	$sqla="SELECT PP.[page] as moduledesc, SUM(HH.tcount) AS numhits, HH.tmodule
			FROM dbo.tbHit AS HH INNER JOIN
				dbo.tbpages AS PP ON HH.tmodule = PP.tmodule
			WHERE PP.tstatus='A'
			GROUP BY HH.tmodule,PP.[page]
			ORDER BY numhits DESC";
	$rsa=dbQuery($oMain->consql, $sqla, $flds);
	$rca=count($rsa);
	
	//hits from current month
	
	$sqlb="SELECT PP.[page] as moduledesc, HH.tmodule, SUM(HH.tcount) AS numhits, YEAR(HH.tdate) * 100 + MONTH(HH.tdate) AS tdate
		FROM dbo.tbHitHist AS HH INNER JOIN 
			dbo.tbpages AS PP ON HH.tmodule = PP.tmodule
		GROUP BY HH.tmodule, PP.[page], YEAR(HH.tdate) * 100 + MONTH(HH.tdate), PP.tstatus
		HAVING (YEAR(HH.tdate) * 100 + MONTH(HH.tdate) = (YEAR(getdate()) * 100 + MONTH(getdate()) ) ) AND PP.tstatus='A'
		ORDER BY numhits DESC";
	
	//print $sqlb;
	
	$rsb=dbQuery($oMain->consql, $sqlb, $flds);
	$rcb=count($rsb);

	$array=array();
	$arr=array();
	
	for ($r = 0; $r < $rca; $r++)
	{
		$tothits=$rsa[$r]['numhits'];
		$tmodulea=$rsa[$r]['moduledesc'];
	
		for ($j = 0; $j < $rcb; $j++)
		{
			$hits=$rsb[$j]['numhits'];
			$tmoduleb=$rsb[$j]['moduledesc'];
			
			if($tmodulea==$tmoduleb)
			{
				
				$calc=$tothits-$hits;
				if($calc>0)
				{
					$link=$oMain->stdImglink('show_tmodules', '', '','tmodule='.$tmodulea, '', $tmodulea, '', $oMain->translate('edit_tmodules'), '');
					$arr[]=array('pos'	=> '',
								 'page'	=> $link,
								 'hits'	=> $calc);
				}
			}
		}
	}
	
	$array=$oMain->arraySortInt($arr, 'hits');
	
	foreach ($array as $k => $v) 
	{
		$array[$k]['pos']=$k+1;
	}
	
	$oTable = new CTable(null, null, $array);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);
	$oTable->addColumn($oMain->translate('pos'),	'left', 'Number');
	$oTable->addColumn($oMain->translate('module'),	'left',	'String');
	$oTable->addColumn($oMain->translate('total'),	'left',	'Number');

	$month=date('M');
	$trad=$oMain->translate('topmonth');
	$html=$oMain->efaHr($trad.': ('.$month.')');
	$html.= $oTable->getHtmlCode ();
	return($html);
}


function topmodules()
{
	$oMain=$this->oMain;

	//all hits
	
	$sql="SELECT '' as pos, PP.[page] as moduledesc, SUM(HH.tcount) AS numhits, HH.tmodule
			FROM dbo.tbHit AS HH INNER JOIN
				dbo.tbpages AS PP ON HH.tmodule = PP.tmodule
			WHERE PP.tstatus='A'
			GROUP BY HH.tmodule,PP.[page]
			ORDER BY numhits DESC";
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	
	for ($r = 0; $r < $rc; $r++)
	{
		$tmodule=$rs[$r]['moduledesc'];
		$rs[$r]['pos']=$r+1;
		$rs[$r]['moduledesc']=$oMain->stdImglink('show_tmodules', '', '','tmodule='.$tmodule, '', $tmodule, '', $oMain->translate('edit_tmodules'), '');
	}

	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);
	$oTable->addColumn($oMain->translate('pos'),	'left', 'Number');
	$oTable->addColumn($oMain->translate('module'),	'left',		'String');
	$oTable->addColumn($oMain->translate('total'),	'left',		'Number');

	$trad=$oMain->translate('allhits');
	$html=$oMain->efaHr($trad);
	$html.= $oTable->getHtmlCode ();
	return($html);
}

function formFilterStats()
{
	$oMain=$this->oMain;
	require_once ('../common/ccommonsql.php');
	$frm_mod=CForm::MODE_EDIT;
	$formName='frm_stats';
	$onChange="$formName.mod.value='synusers_netstat';$formName.submit(); ".$oMain->loading().";";
	
	if($this->year=='')
		$this->year=date('Y');
	
	$sql="SELECT DISTINCT YEAR(tlasthit) as codi, YEAR(tlasthit) as tdesc from dbo.tbhit ORDER BY codi DESC";
//print $sql;
	$tyear = new CFormSelect($oMain->translate('year'), 'year', $this->year,'', $sql, $this->oMain->consql,'','','','',false);
	$tyear->addEvent("onChange=\"$formName.operation.value='tyear'; ".$onChange." \"");
	$aForm[] = $tyear;      
	
	$oForm = $oMain->std_form('synusers_netstat', 'tyear','frm_stats', 1, $frm_mod,'',150);
	$oForm->addElementsCollection($aForm);
	
	return  $oForm->getHtmlCode() ;
}


function statsChart($year)
{
	$oMain=$this->oMain;
	
/*	$sql="select count(distinct tuserid) as chartval, year(tlasthit) as y, month(tlasthit) as chartlabel
		, SUBSTRING(DATENAME(MONTH, tlasthit),0,4) as monthname
		from tbhit
		GROUP BY YEAR(tlasthit), MONTH(tlasthit), SUBSTRING(DATENAME(MONTH, tlasthit),0,4)
		HAVING (YEAR(tlasthit) = '$year')
		ORDER BY chartlabel";
*/		
	$sql="SELECT COUNT(DISTINCT tuserid) AS chartval, YEAR(tlasthit) AS y, MONTH(tlasthit) AS chartlabel, SUBSTRING(DATENAME(MONTH, tlasthit),0,4) as monthname 
FROM            dbo.tbhithistuser
GROUP BY YEAR(tlasthit), MONTH(tlasthit), SUBSTRING(DATENAME(MONTH, tlasthit),0,4)
HAVING        (YEAR(tlasthit) = '$year')
ORDER BY chartlabel";
			
	$rs=dbQuery($oMain->consql, $sql, $flds, 43200);
	
	require_once 'cchart.php';
	$o = new Cchart($oMain);

	$o->series($rs);
	$o->type('line');
	$o->xtitle($oMain->translate('synusersmonth'));
//	$o->barcolour('#009999');
//	$o->barwidth(35);
	
	//$o->title($oMain->translate('xpto'));
	$o->width(500); 
	$o->height(300);
	$o->border('1px');
	
	$chartsteering=$o->html();
	
	return($chartsteering);
}

function synergieUsers()
{
	$oMain=$this->oMain;
	include_once ('std_common.php');

	if($oMain->operation<>'tyear')
	{
		$sqlmaxyear="SELECT MAX(YEAR(tlasthit)) AS maxyear from dbo.tbhit";
		$rsyear=dbQuery($oMain->consql, $sqlmaxyear, $flds);
		$maxyear=$rsyear[0]['maxyear'];

		$actualyear=date('Y');
		
		$cond=$actualyear;
		IF($actualyear>$maxyear)
			$cond=$maxyear;

		$this->year=$cond;
	}
	
	$filter=$this->formFilterStats();
	
	$graph=$this->statsChart($this->year);
	
//	$sql="select count(distinct tuserid) as users, year(tlasthit) as y, month(tlasthit) as m
//		, SUBSTRING(DATENAME(MONTH, tlasthit),0,4) as monthname
//		from tbhit
//		GROUP BY YEAR(tlasthit), MONTH(tlasthit), SUBSTRING(DATENAME(MONTH, tlasthit),0,4)
//		HAVING (YEAR(tlasthit) = '$this->year')
//		ORDER BY m";
////print $sql;
//	$graph=data_area_graph_time($oMain,$oMain->consql,$sql,'usersmonth',12,'monthname','y','users','qty','#FFFACD','#AFEEEE','nototal');
	
	
	
	
	
	$html="<table border=0 width=100%>
		<tr>
			<td>$filter</td>
        </tr>
        <tr>
			<td>$graph</td>
        </tr>
		</table>";

	return ($html);
}


//function arraySortInt($arr, $field)
//{			
//	$array=array();
//	foreach ($arr as $k => $v) 
//	{
//		$tnumber=(int)$arr[$k][$field];
//		$empty=$k;
//		
//		foreach ($arr as $k1 => $v1) 
//		{
//			if((int)$arr[$k1][$field]>$tnumber)
//			{
//				$tnumber=$arr[$k1][$field];
//				$empty=$k1;
//			}
//		}
//		$array[]=$arr[$empty];
//		unset($arr[$empty]);
//	} 
//
//	return $array;
//}


} // End CNetStat

class CModuleMan
{
	var $company;    /**  */
	var $tmodule;    /**  */
	var $manager;    /**  */
	var $assignby;    /**  */
	var $modifdate;    /**  */
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class moduleman mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='mmanagers'; 
		$company=$this->company;

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$mod ='list_'.$ent;
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$mod ='list_'.$ent;
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$mod ='list_'.$ent;
		}

		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->form('insert_'.$ent);
		}

		if ($mod =='list_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod).' '.$company;
			//$html=$this->showList();
			$html=$this->dashboard();
		}

		
		$oMain->subtitle=$oMain->translate($mod);
		
		$oMain->toolbar_icon('img/new.png',$oMain->BaseLink('','new_tmodules'), $oMain->translate('createmodule'));
		
		$omod=new CTmodule($oMain);
		$title=$oMain->Title('', $this->tmodule);
		$dashboard='<table width=100%><tr valign=top><td width=195 class=row1>'.$omod->menuModule($this->tmodule).'</td>
		<td valign=top>'.$title.'<BR>'.$html.'</td></tr></table>';
			
		return($dashboard);
		
		
		//return($html);
	}
	
	
	function dashboard()
	{
		$oMain=$this->oMain;
		
		$manager=$this->showList();
		$managerallcomp=$this->showListAllComp();
		$nomanager=$this->showListCompNoManager();
		
		$html="<table width=100% border=0>
        <tr valign=TOP>
			<td colspan=3>$manager</td>
        </tr>
		<tr valign=TOP>
			<td width=70%>$managerallcomp</td>
			<td width=1%></td>
			<td>$nomanager</td>
        </tr>
        </table>";
		
		
		return($html);
	}
	
	 /**
	  * read class moduleman atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->company=$oMain->GetFromArray('company',$_REQUEST,'string_trim');
		$this->tmodule=$oMain->GetFromArray('tmodule',$_REQUEST,'string_trim');
		$this->manager=$oMain->GetFromArray('manager',$_REQUEST,'string_trim');
		$this->assignby=$oMain->GetFromArray('assignby',$_REQUEST,'string_trim');
		$this->modifdate=$oMain->GetFromArray('modifdate',$_REQUEST,'date');
		

	}
	/**
	 * class moduleman form
	 */	
	function form()
	{

		$oMain=$this->oMain;
		$html_form=$oMain->stdJsPopUpWin('400');
		
//		if($module!='')
//			$this->tmodule=$module;
		
		$formName='frmmoduleman'; $operation='';$nCol=1;$width='50%';$ajax=false;
		$frmMod=CForm::MODE_EDIT;
	
		//$onChange="$formName.mod.value='$modChange';$formName.submit(); $oMain->loading;";
		
		$oForm = $oMain->std_form('insert_mmanagers', $operation,$formName,$nCol,$frmMod,$ajax,$width);
		$aForm = array();
//print $this->tmodule.' :module';
		//general
		$aForm[] = new CFormHidden('company', $oMain->comp);
		$aForm[] = new CFormHidden('tmodule', $this->tmodule);
		
		
		$search_manager=$oMain->stdPopupwin('GETCCUSER',$formName,'manager','managerdesc','manager','managerdesc','','');	
		$field_manager = new CFormText($oMain->translate('manager'),'manager', $this->manager,'',CForm::REQUIRED,false);
		$field_manager_desc = new CFormText($oMain->translate('manager'), 'managerdesc', $this->managerdesc, '','',false, '', '', '', 70);
		if($frmMod==CForm::MODE_EDIT)
		   $field_manager->setExtraData($search_manager);
		$aForm[] = new CFormMultipleElement(array($field_manager, $field_manager_desc), 0);

		

		//form buttons
		//$onSubmit="$formName.submit(); $oMain->loading;";
		$buttonSave = new CFormButton('insert', $oMain->translate ('insert'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$aForm[]=$buttonSave;

		$oForm->addElementsCollection($aForm);
		$html_form.=$oForm->getHtmlCode();
		return $html_form;

	}

	/**
	 * save class moduleman record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$sid=$this->oMain->sid;
		$sql="[dbo].[sppagemanagers] '$sid','$operation'
		,'$this->company'
		,'$this->tmodule'
		,'$this->manager'
		";
		
//print $sql;
		
		$rs=dbQuery($this->oMain->consql, $sql, $flds);
		$rst=$rs[0];
		$tdesc=$rst['tdesc'];
		return($rst['tstatus']);
	}
	/**
	 * query to get class moduleman record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT  company,page AS tmodule,manager,assignby,modifdate 
			FROM dbo.tbpagemanagers 
			WHERE company='$this->company' and page='$this->tmodule' and manager='$this->manager'";		

		return($sql);
	}
	/**
	 * set class moduleman atributes with data from database
	 */	
	function readfromdb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->company=$rst['company'];
			$this->tmodule=$rst['tmodule'];
			$this->manager=$rst['manager'];
			$this->assignby=$rst['assignby'];
			$this->modifdate=$rst['modifdate'];
			
		}
		return $rc;
	}

	function showList()
	{
		$oMain=$this->oMain;

		$sql="SELECT dbo.efa_username(manager) +' - '+ manager AS managerdesc,dbo.efa_username(assignby) AS assignbydesc,modifdate,'' AS toperations
				,assignby,company,manager,page AS tmodule
			  FROM dbo.tbpagemanagers 
			  WHERE company='$oMain->comp' and page='$this->tmodule'";

		//print $sql;

		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		for ($r = 0; $r < $rc; $r++)
		{
			$company=$rs[$r]['company'];
			$tmodule=$rs[$r]['tmodule'];
			$manager=$rs[$r]['manager'];
			$managerdesc=$rs[$r]['managerdesc'];

			//$assignby=$rs[$r]['assignby'].' - '.$rs[$r]['assignbydesc'];

			$param='company='.$company.'&tmodule='.$tmodule.'&manager='.$manager;

			$rs[$r]['managerdesc']=$oMain->stdImglink('show_users', '', '','&userid='.$manager, '', $managerdesc, '', $oMain->translate('show_users'));

			//$rs[$r]['modifiedby']=$modifiedby;
			$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);

			$rs[$r]['toperations']=$oMain->stdImglink('del_mmanagers', '', '',$param, 'img/delete_s.png', '', '', $oMain->translate('delaccess'), $oMain->translate('confdel'));		
		}

		$oTable = new CTable(null, null, $rs);
		$oTable->SetSorting();
		$oTable->SetFixedHead (false,400);

		//$oTable->addColumn($oMain->translate('tmodule'), 'left', 'String');
		$oTable->addColumn($oMain->translate('manager'), 'left', 'String');
		$oTable->addColumn($oMain->translate('assignby'), 'left', 'String');
		$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
		$oTable->addColumn('!');

		$new=$oMain->showHide($oMain->translate('new'), $this->form(),0,'img/new_s.png','img/new_s.png', '', '', 'class="rowpink"');
		$html = $oMain->efaHR($oMain->translate('modulemanagers').' ('.$oMain->translate('company').' '.$oMain->comp.') '.$new);


		$html.= $oTable->getHtmlCode();

		//If($rc==0) {return $oMain->translate('nosearchresults');}
		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
		return($html);
	}
	
	
	function showListAllComp()
	{
		$oMain=$this->oMain;

		$sql="SELECT company, dbo.efa_username(manager) +' - '+ manager AS managerdesc,dbo.efa_username(assignby) AS assignbydesc,modifdate,'' AS toperations
				,assignby,company,manager,page AS tmodule
			  FROM dbo.tbpagemanagers 
			  WHERE company NOT IN ('$oMain->comp') and page='$this->tmodule'";

//		print $sql;

		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		for ($r = 0; $r < $rc; $r++)
		{
//			$company=$rs[$r]['company'];
//			$tmodule=$rs[$r]['tmodule'];
			$manager=$rs[$r]['manager'];
			$managerdesc=$rs[$r]['managerdesc'];

			//$assignby=$rs[$r]['assignby'].' - '.$rs[$r]['assignbydesc'];

			//$param='company='.$company.'&tmodule='.$tmodule.'&manager='.$manager;

			$rs[$r]['managerdesc']=$oMain->stdImglink('show_users', '', '','&userid='.$manager, '', $managerdesc, '', $oMain->translate('show_users'));

			//$rs[$r]['modifiedby']=$modifiedby;
			//$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);

			//$rs[$r]['toperations']=$oMain->stdImglink('del_mmanagers', '', '',$param, 'img/delete_s.png', '', '', $oMain->translate('delaccess'), $oMain->translate('confdel'));		
		}

		$oTable = new CTable(null, null, $rs);
		$oTable->SetSorting();
		$oTable->SetFixedHead (true,400);

		$oTable->addColumn($oMain->translate('company'), 'left', 'String');
		$oTable->addColumn($oMain->translate('manager'), 'left', 'String');
//		$oTable->addColumn($oMain->translate('assignby'), 'left', 'String');
//		$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
//		$oTable->addColumn('!');

		//$new=$oMain->showHide($oMain->translate('new'), $this->form(),0,'img/new_s.png','img/new_s.png', '', '', 'class="rowpink"');
		$html = $oMain->efaHR($oMain->translate('modulemanagersallcomp'));


		$html.= $oTable->getHtmlCode();

		//If($rc==0) {return $oMain->translate('nosearchresults');}
		if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
		return($html);
	}
	
	
	function showListCompNoManager()
	{
		$oMain=$this->oMain;
	//print 111;	

		$sql="SELECT DISTINCT A.company, M.manager, A.page, C.tstatus
				FROM dbo.tbaccesses AS A INNER JOIN
					dbo.tbcompanies AS C ON A.company = C.company LEFT OUTER JOIN
					dbo.tbpagemanagers AS M ON A.company = M.company AND A.page = M.page
				WHERE (A.page = '$this->tmodule') AND (M.manager IS NULL) AND (C.tstatus = 'A')";	
		$rs=dbQuery($oMain->consql, $sql, $flds);

		$oTable = new CTable(null, null, $rs);
		$oTable->SetSorting();
		$oTable->SetFixedHead (true,400);

		$oTable->addColumn($oMain->translate('company'), 'left', 'String');

		$html = $oMain->efaHR($oMain->translate('moduleaccessnomanager'));
		$html.= $oTable->getHtmlCode();

		return($html);
	}

}// Enf of CModuleMan

class CModuleParam
{
	var $tmodule;    /**  */
	var $company;    /**  */
	var $tfield;    /**  */
	var $tvalue;    /**  */
	var $modifiedby;    /**  */
	var $modifdate;    /**  */
	

	/**
	 * constructor
	 */
	function  __construct($oMain)
	{
		$this->oMain=$oMain;
	}

	/**
	 * set class CModuleParam mod
	 */	
	function getHtml($mod)
	{
		$oMain=$this->oMain;
		$this->readFromRequest();
		$ent='modparam'; 
		//$module=$this->module;

		if ($mod =='del_'.$ent)
		{
			$tstatus=$this->storeIntoDB('delete', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);

			$mod ='list_'.$ent;
		}

		if ($mod =='insert_'.$ent)
		{
			$tstatus=$this->storeIntoDB('insert', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
			
			$mod ='list_'.$ent;	
		}

		if ($mod =='update_'.$ent)
		{
			$tstatus=$this->storeIntoDB('update', $tdesc);
			$oMain->stdShowResult($tstatus, $tdesc);
		
			$mod ='list_'.$ent;;
		}


		if ($mod =='new_'.$ent or $mod =='xnew_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod);
			$html=$this->form('insert_'.$ent);
		}

		if ($mod =='list_'.$ent)
		{
			$oMain->subtitle=$oMain->translate($mod).' '.$module;
			$html=$this->showList();
		}

		if ($mod =='show_'.$ent)
		{
			$this->readFromDB();
			$oMain->subtitle=$oMain->translate('show_'.$ent).' '.$module;
			$html=$this->form('show_'.$ent);
		}
		
		
		$oMain->toolbar_icon('img/new.png',$oMain->BaseLink('','new_tmodules'), $oMain->translate('createmodule'));
		
		$omod=new CTmodule($oMain);
		
		$title=$oMain->Title('', $this->tmodule);
		$dashboard='<table width=100%><tr valign=top><td width=195 class=row1>'.$omod->menuModule($this->tmodule).'</td>
		<td valign=top>'.$title.'<BR>'.$html.'</td></tr></table>';

//		if($this->mod=='SEARCH' OR $this->mod=='GSEARCH')
//			$dashboard=$html;

		return($dashboard);
		
		//return($html);
	}
	
	 /**
	  * read class CModuleParam atributes from request
	  */	
	function readfromrequest()
	{
		$oMain = $this->oMain;
		$this->tmodule=$oMain->GetFromArray('tmodule',$_REQUEST,'string_trim');
		$this->company=$oMain->GetFromArray('company',$_REQUEST,'string_trim');
		$this->tfield=$oMain->GetFromArray('tfield',$_REQUEST,'string_trim');
		$this->tvalue=$oMain->GetFromArray('tvalue',$_REQUEST,'string_trim');
		$this->modifiedby=$oMain->GetFromArray('modifiedby',$_REQUEST,'string_trim');
		$this->modifdate=$oMain->GetFromArray('modifdate',$_REQUEST,'date');
		
	}
	/**
	 * class CModuleParam form
	 */	
	function form($mod='show_modparam')
	{
	
		$oMain=$this->oMain;

		$formName='frmCModuleParam'; $operation='';$nCol=3;$width='100%';$ajax=false;
	
		$frmMod=CForm::MODE_EDIT;
		
		$aForm = array();

		//general
		$oForm = $oMain->std_form($mod, $operation,$formName,$nCol,$frmMod,$ajax,$width);

		$aForm[] = new CFormHidden('tmodule',$this->tmodule);
		$aForm[] = new CFormHidden('company',$oMain->comp);

		$sql="select tparam, tparam as tdesc from tbparameters where tscope = 'M' ORDER BY tdesc";
		$aForm[] = new CFormSelect($oMain->translate('tfield'), 'tfield', $this->tfield, $this->tfield, $sql, $oMain->consql, '', '', ' ', CForm::REQUIRED, $cparameters_readonly);
		$aForm[] = new CFormText($oMain->translate('tvalue'),'tvalue', $this->tvalue,250,'',false,'',CFormText::INPUT_STRING_CODE);

		$onSubmit="$formName.submit(); $oMain->loading;";
		$buttonSave = new CFormButton('save', $oMain->translate ('save'),CFormButton::TYPE_SUBMIT,'',CFormButton::LOCATION_FORM_RIGHT);
		$aForm[]=$buttonSave;

		$oForm->addElementsCollection($aForm);
		$html_form.=$oForm->getHtmlCode();
		return $html_form;

	}

	/**
	 * save class CModuleParam record into database
	 */	
	function storeIntoDB($operation, &$tdesc)
	{
		$sid=$this->oMain->sid;
		$sql="[dbo].[spmodparam] '$sid','$operation'
		,'$this->tmodule'
		,'$this->company'
		,'$this->tfield'
		,'$this->tvalue'
		";
		
//print $sql; die;
		$rs=dbQuery($this->oMain->consql, $sql, $flds);
		$rst=$rs[0];
		$tdesc=$rst['tdesc'];
		return($rst['tstatus']);
	}
	/**
	 * query to get class CModuleParam record from database
	 */	
	function sqlGet()
	{
		$oMain = $this->oMain;
	
		$sql="SELECT  module AS tmodule,company,tfield,tvalue,modifiedby,modifdate FROM dbo.tbmodparam WHERE module='$this->module' and company='$this->company' and tfield='$this->tfield'";		

		return($sql);
	}
	/**
	 * set class CModuleParam atributes with data from database
	 */	
	function readfromdb()
	{
		$oMain = $this->oMain;
		$sql=$this->sqlGet();
		$rs=dbQuery($oMain->consql, $sql, $flds);
		$rc=count($rs);
		if($rc>0)
		{
			$rst=$rs[0];
			$this->tmodule=$rst['tmodule'];
			$this->company=$rst['company'];
			$this->tfield=$rst['tfield'];
			$this->tvalue=$rst['tvalue'];
			$this->modifiedby=$rst['modifiedby'];
			$this->modifdate=$rst['modifdate'];
			
		}
		return $rc;
	}

function showList()
{
	$oMain=$this->oMain;
	
	$sql="SELECT MOD.tfield, MOD.tvalue, PAR.remarks, dbo.efa_username(MOD.modifiedby) AS modifiedby, MOD.modifdate, '' AS toperations, MOD.module, MOD.company
		FROM dbo.tbmodparam AS MOD 
		INNER JOIN dbo.tbparameters AS PAR ON MOD.tfield = PAR.tparam
		WHERE(MOD.module = '$this->tmodule') AND (MOD.company = '$oMain->comp' OR
			MOD.company = '')
		ORDER BY MOD.company";
	
	$rs=dbQuery($oMain->consql, $sql, $flds);
	$rc=count($rs);
	for ($r = 0; $r < $rc; $r++)
	{
		$company=$rs[$r]['company'];
		$tfield=$rs[$r]['tfield'];
		$tvalue=$rs[$r]['tvalue'];
		$tmodule=$rs[$r]['module'];
			
		$param='company='.$company.'&tfield='.$tfield.'&tvalue='.$tvalue.'&tmodule='.$tmodule;
		
		if($company != '')
		{	
			$rs[$r]['tvalue']='<table CELLPADDING=0 CELLSPACING=0>'.$oMain->stdForm('update_modparam','','frmmodparam'.$r).'<tr><td>
			<input type=text size=30 value="'.$rs[$r]['tvalue'].'" name=tvalue>
			<input type=hidden value='.$company.' name=company>
			<input type=hidden value='.$tfield.' name=tfield>
			<input type=hidden value='.$tmodule.' name=tmodule>
			</td></tr></form></table>';
		}
		
		$rs[$r]['modifdate']= $oMain->formatDate($rs[$r]['modifdate']);
		
		if($company != '')
		{
			//$rs[$r]['toperations']=$oMain->stdImglink('edit_modparam', '', 'edit',$param, 'img/edit_s.png', '', '', $oMain->translate('editmodparam'), '');
			$rs[$r]['toperations']='<img src="img/save_s.png" border=0 onmouseover="this.style.cursor=\'hand\';" onclick="frmmodparam'.$r.'.submit();">';
			$rs[$r]['toperations'].=' '.$oMain->stdImglink('del_modparam', '', '',$param, 'img/delete_s.png', '', '', $oMain->translate('delmodparam'), $oMain->translate('confdel'));
			$rs[$r]['toperations'].=' '.$oMain->stdImglink('edit_cparameters', 'compman', '','&tparam='.$tfield, 'img/compman_s.png', '', '', $oMain->translate('compman'));
		}
		else
			$rs[$r]['toperations']=$oMain->translate('globalparam');		
	}
					
	$oTable = new CTable(null, null, $rs);
	$oTable->SetSorting();
	$oTable->SetFixedHead (true,400);

	$oTable->addColumn($oMain->translate('tfield'), 'left', 'String');
	$oTable->addColumn($oMain->translate('tvalue'), 'left', 'String');
	$oTable->addColumn($oMain->translate('tremarks'), 'left', 'String',100);
	$oTable->addColumn($oMain->translate('modifiedby'), 'left', 'String');
	$oTable->addColumn($oMain->translate('modifdate'), 'left', 'String');
	$oTable->addColumn('!');
	
	
	$new=$oMain->showHide($oMain->translate('new'), $this->form('insert_modparam'),0,'img/new_s.png','img/new_s.png', '', '', 'class="rowpink"');
	$html = $oMain->efaHR($oMain->translate('moduleparam').' &nbsp; | &nbsp; '.$new);
	
	
	$html.= $oTable->getHtmlCode();
	
	//If($rc==0) {return $oMain->translate('nosearchresults');}
	if($rc>=1000) {$oMain->setwarning($oMain->translate('toomanyrecords'));}
	return($html);
}

}// Enf of CModuleParam

?>
