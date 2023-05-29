function Communicator (container, sid, t_user, t_username, extraRequest,
						t_company, t_module, t_reference, scopes,
						showAllInScopeRef, defaultFilter)
{
	window.dhx_globalImgPath="./img/communicator/";

	var _container = container;
	var _sid = sid;
	var _t_user = t_user;
	var _t_username = t_username;
	//var _t_uservar = t_uservar;
	//var _language = language;
	var _t_company = t_company;
	var _t_module = t_module;
	var _t_reference = t_reference;
	//var _scope = scope;

	var _showAllInScopeRef;
	if (showAllInScopeRef == 1)
		_showAllInScopeRef = true;
	else
		_showAllInScopeRef = false;

	var _scopes = scopes;

	var _extraRequest = extraRequest;
	var _defaultFilter = defaultFilter;

	var translations;

	var tmp = '';
	for (var i = 0; i < _scopes.length; i++)
		tmp += "&scopes[]=" + _scopes[i];

	var baseLnk = "./efa.php?page=" + _t_module + tmp + "&t_reference=" + _t_reference + "&t_user=" + _t_user + "&" + _extraRequest + "&sid=" + _sid;

	var recipients = null;

	var currFilter;

	var dhxLayout;
	var dhxWins;
	var dhxWinMsg;
	var statusBar;
	var dhxWebBar;
	var dhxMsgWebBar;
	var dhxGrid;
	var dhxForm;

	var xmlHttpObj;

	/***** INITIALIZATION *****/
	GetTranslations();
	/***** INITIALIZATION *****/


	/***** PUBLIC METHODS *****/

	this.Show = function ()
	{
		dhxLayout = new dhtmlXLayoutObject(_container, "1C");
		statusBar = dhxLayout.attachStatusBar();
		dhxLayout.cells("a").hideHeader();

		var t;
		window.onresize = function()
		{
			window.clearTimeout(t);
			t = window.setTimeout(function()
			{
				dhxLayout.setSizes()
			}, 200);
		}

		currFilter = _defaultFilter;
		CreateMainToolbar();
		CreateMainGrid();
		ActionLoadMessagesList();
	}

	this.AddRecipient = function (userId, userName)
	{
		if (recipients == null)
		{
			recipients = new Array ();
			recipients[0] = new Array(userId, userName);
		}
		else
		{
			recipients[recipients.length] = new Array(userId, userName);
		}
	}

	this.SendNewMessage = function ()
	{
		ActionNewMessage (false);
	}

	/***** PUBLIC METHODS *****/


	/***** INTERFACES *****/

	function GetTranslations()
	{
		xmlHttpObj = CreateXmlHttpRequestObject();
		if(xmlHttpObj)
		{
			var lnk = baseLnk + "&mod=communicatorGetTranslations&UNIK=" + new Date().getTime();
			xmlHttpObj.open('GET',lnk, false);
			xmlHttpObj.send(null);
			var xmlResponse = xmlHttpObj.responseXML;

			translations = new Array();
			var nodes = xmlResponse.getElementsByTagName("row");
			for(var i = 0; i < nodes.length; i++)
			{
				var node = nodes[i];
				var index = node.getElementsByTagName("code")[0].textContent;
				var value = nodes[i].getElementsByTagName("text")[0].textContent;
				if (index == null) // textContent does not exists in IE xml object
				{
					index = node.getElementsByTagName("code")[0].text;
					value = nodes[i].getElementsByTagName("text")[0].text;
				}

				translations[index] = value;
			}
		}
	}

	function CreateMainToolbar ()
	{
		dhxWebBar = dhxLayout.cells("a").attachToolbar();
		dhxWebBar.setIconsPath("./img/communicator/");

		var index = 0;

		dhxWebBar.addButtonTwoState("allinscoperef", index++, translations["allinscoperef"], "allinscoperef_msgs.gif", "allinscoperef_msgs_dis.gif");
		dhxWebBar.setItemToolTip("allinscoperef", translations["allinscopereftooltip"]);
		//if (_scopes[0] == '' || _t_reference == '')
		if (!_showAllInScopeRef)
		{
			dhxWebBar.hideItem("allinscoperef");
		}

		dhxWebBar.addButtonTwoState("myunreaded", index++, translations["myunreaded"], "unreaded_msgs.gif", "unreaded_msgs_dis.gif");
		dhxWebBar.setItemToolTip("myunreaded", translations["myunreadedtooltip"]);

		dhxWebBar.addButtonTwoState("mysentandunreaded", index++, translations["mysentandunreaded"], "unreaded_sent_msgs.gif", "unreaded_sent_msgs_dis.gif");
		dhxWebBar.setItemToolTip("mysentandunreaded", translations["mysentandunreadedtooltip"]);

		//dhxWebBar.addSeparator("sep01", 2);

		dhxWebBar.addButtonTwoState("myreaded", index++, translations["myreaded"], "readed_msgs.gif", "readed_msgs_dis.gif");
		dhxWebBar.setItemToolTip("myreaded", translations["myreadedtooltip"]);

		dhxWebBar.addButtonTwoState("mysentandreaded", index++, translations["mysentandreaded"], "readed_sent_msgs.gif", "readed_sent_msgs_dis.gif");
		dhxWebBar.setItemToolTip("mysentandreaded", translations["mysentandreadedtooltip"]);

		dhxWebBar.addSeparator("sep02", index++);

		dhxWebBar.addButton("newmsg", index++, translations["newmsg"], "new_msg.gif", "new_msg_dis.gif");
		dhxWebBar.setItemToolTip("newmsg", translations["newmsgtooltip"]);

		dhxWebBar.addButton("reply", index++, translations["reply"], "reply_msg.gif", "reply_msg_dis.gif");
		dhxWebBar.setItemToolTip("reply", translations["replytooltip"]);

		dhxWebBar.addButton("open", index++, translations["open"], "open_msg.gif", "open_msg_dis.gif");
		dhxWebBar.setItemToolTip("open", translations["opentooltip"]);

		dhxWebBar.addButton("unopen", index++, translations["unopen"], "unreaded_msgs.gif", "unreaded_msgs_dis.gif");
		dhxWebBar.setItemToolTip("unopen", translations["unopentooltip"]);

		dhxWebBar.addButton("forward", index++, translations["forward"], "forward_msg.gif", "forward_msg_dis.gif");
		dhxWebBar.setItemToolTip("forward", translations["forwardtooltip"]);

		dhxWebBar.addSeparator("sep03", index++);

		dhxWebBar.addText("search", index++, translations["search"]);
		dhxWebBar.addInput("gsearch", index++, "", 100);

		dhxWebBar.attachEvent("onClick", function(id)
		{
			if (id == "newmsg")
				ActionNewMessage(false);

			if (id == "open")
				ActionMarkMessageAsTreated();

			if (id == "reply")
				ActionNewMessage(true);

			if (id == "forward")
				ActionForwardMessage();

			if (id == "unopen")
				ActionMarkMessageAsNotTreated();
		});

		dhxWebBar.attachEvent("onStateChange", function(id)
		{
			currFilter = id;
			EnableDisableFilterButtons();
			ActionLoadMessagesList();
		});

		dhxWebBar.attachEvent("onEnter", function(id, value)
		{
			if (id == "gsearch")
			{
				ActionSearchMessage(value);
			}
		});

		dhxWebBar.setItemState (currFilter, true);
	}

	function CreateMainGrid ()
	{
		dhxGrid = dhxLayout.cells("a").attachGrid();
		dhxGrid.setImagePath("./addin/dhtmlxSuite/dhtmlxGrid/codebase/imgs/");

		var header = "&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;";
		header += "," + translations["subject"];
		header += "," + translations["from"];
		header += "," + translations["to"];
		header += "," + translations["date"];

		//dhxGrid.setHeader("&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,Assunto,De,Para,Data");
		//dhxGrid.setHeader("&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;,&nbsp;," + translations["subject"] + "," + translations["from"] + "," + translations["to"] + "," + translations["date"]);
		dhxGrid.setHeader(header);
		dhxGrid.setColSorting("na,na,na,na,na,na,na,na,na,na,na,na,na,na,na,str,str,date");
		dhxGrid.setColAlign(",,,,,,,,,,,,center,center,left,left,left,center");
		dhxGrid.setColVAlign(",,,,,,,,,,,,top,top,top,top,top,top");
		//dhxGrid.setInitWidths("0,0,0,0,0,0,0,0,0,0,0,0,50,50,620,175,175,100");
		dhxGrid.setInitWidthsP("0,0,0,0,0,0,0,0,0,0,0,0,5,5,60,15,15,15");
		dhxGrid.attachHeader(",,,,,,,,,,,,,,#text_filter,#select_filter,#select_filter,");
		dhxGrid.enableTooltips(",,,,,,,,,,,,false,false,false,false,false,false");

		dhxGrid.ai = true;
		dhxGrid.enableMultiline(true);
		dhxGrid.enableEditEvents(false, false,false);
		dhxGrid.setAwaitedRowHeight(100);
		dhxGrid.enableSmartRendering = true;

		//dhxGrid.setPagingSkin("bricks");
		//dhxGrid.enablePaging(true, 4, 5, _container + "paging", true);

		dhxGrid.attachEvent("onRowSelect", function(id,ind)
		{
			var msgObj = new CommunicatorMessage();

			msgObj.t_sendto = dhxGrid.cellById(dhxGrid.getSelectedId(), 7).getValue();
			msgObj.t_readdata = dhxGrid.cellById(dhxGrid.getSelectedId(), 8).getValue();

			EnableDisableToolbarButtons(msgObj);
		});

		dhxGrid.attachEvent("onRowDblClicked", function(rId, cInd)
		{
			ActionViewMessage ();
		});

		dhxGrid.init();
	}

	function HideNonRequiredColumns()
	{
		// Hide no requiered columns
		dhxGrid.setColumnHidden(0,true);
		dhxGrid.setColumnHidden(1,true);
		dhxGrid.setColumnHidden(2,true);
		dhxGrid.setColumnHidden(3,true);
		dhxGrid.setColumnHidden(4,true);
		dhxGrid.setColumnHidden(5,true);
		dhxGrid.setColumnHidden(6,true);
		dhxGrid.setColumnHidden(7,true);
		dhxGrid.setColumnHidden(8,true);
		dhxGrid.setColumnHidden(9,true);
		dhxGrid.setColumnHidden(10,true);
		dhxGrid.setColumnHidden(11,true);

		if (currFilter == "gsearch" || currFilter == "allinscoperef")
		{
			dhxGrid.setColumnHidden(15,false);
			dhxGrid.setColumnHidden(16,false);
			dhxGrid.setColWidth (14, 45);
		}
		else if (currFilter == "myunreaded" || currFilter == "myreaded")
		{
			dhxGrid.setColumnHidden(15,false);
			dhxGrid.setColumnHidden(16,true);
			dhxGrid.setColWidth (14, 60);
		}
		else
		{
			dhxGrid.setColumnHidden(15,true);
			dhxGrid.setColumnHidden(16,false);
			dhxGrid.setColWidth (14, 60);
		}
	}

	function EnableDisableFilterButtons()
	{
		if (currFilter != "allinscoperef")
			dhxWebBar.setItemState("allinscoperef", false);

		if (currFilter != "myunreaded")
			dhxWebBar.setItemState("myunreaded", false);

		if (currFilter != "mysentandunreaded")
			dhxWebBar.setItemState("mysentandunreaded", false);

		if (currFilter != "myreaded")
			dhxWebBar.setItemState("myreaded", false);

		if (currFilter != "mysentandreaded")
			dhxWebBar.setItemState("mysentandreaded", false);

		if (currFilter != "gsearch")
		{
			dhxWebBar.setItemState(currFilter, true);
		}
	}

	function EnableDisableToolbarButtons(msgObj)
	{
//		var selId = dhxGrid.getSelectedId();
//
//		dhxWebBar.disableItem("open");
//		dhxWebBar.disableItem("unopen");
//		dhxWebBar.disableItem("reply");
//		dhxWebBar.disableItem("forward");
//
//		if (dhxWebBar.getItemState("myunreaded") && selId != null)
//		{
//			dhxWebBar.enableItem("open");
//			dhxWebBar.enableItem("reply");
//			dhxWebBar.enableItem("forward");
//		}
//		if (dhxWebBar.getItemState("myreaded") && selId != null)
//		{
//			dhxWebBar.enableItem("unopen");
//		}

		dhxWebBar.disableItem("newmsg");
		dhxWebBar.disableItem("open");
		dhxWebBar.disableItem("unopen");
		dhxWebBar.disableItem("reply");
		dhxWebBar.disableItem("forward");

		if (_scopes.length == 1)
			dhxWebBar.enableItem("newmsg");

		if (msgObj != null)
		{
			if (msgObj.t_sendto == _t_user && msgObj.t_readdata == "0")
			{
				dhxWebBar.enableItem("open");
				dhxWebBar.enableItem("reply");
				dhxWebBar.enableItem("forward");
			}

			if (msgObj.t_sendto == _t_user && msgObj.t_readdata != "0")
			{
				dhxWebBar.enableItem("unopen");
			}
		}
	}

	function CreateMessageWindow(onlySendActive, onlyOkButton, msgObj)
	{
		dhxWins = dhxLayout.dhxWins;
		dhxWins.setImagePath("./addin/dhtmlxSuite/dhtmlxWindows/codebase/imgs/");

		dhxWinMsg = dhxWins.window("view_message");
		if (dhxWinMsg != null && !dhxWinMsg.isHidden())
			dhxWinMsg.close();


		// Create message window
		dhxWinMsg = dhxWins.createWindow("view_message",10,10,700,450);
		dhxWinMsg.setText(translations["message"] + " (" + msgObj.scope + ": " + msgObj.t_reference + ")");
		dhxWinMsg.button("park").hide();
		dhxWinMsg.button("close").attachEvent("onClick",function()
		{
			dhxWinMsg.setModal(false);
			dhxWinMsg.hide();
		});

		// attach toolbar to window
		CreateMessageWindowToolbar(msgObj, onlyOkButton);

		// attach form to window
		CreateMessageWindowForm(onlyOkButton, onlySendActive, msgObj);

		dhxWinMsg.hide();

		dhxWinMsg.setModal(true);
		dhxWinMsg.bringToTop();
		dhxWinMsg.center();
	}

	function CreateMessageWindowToolbar(msgObj, allowActions)
	{
		dhxMsgWebBar = dhxWinMsg.attachToolbar();
		dhxMsgWebBar.setIconsPath("./img/communicator/");

		dhxMsgWebBar.addButton("reply1", 1, translations["reply"], "reply_msg.gif", "reply_msg_dis.gif");
		dhxMsgWebBar.setItemToolTip("reply1", translations["replytooltip"]);

		dhxMsgWebBar.addSeparator("sep03", 2);

		dhxMsgWebBar.addButton("open1", 3, translations["open"], "open_msg.gif", "open_msg_dis.gif");
		dhxMsgWebBar.setItemToolTip("open1", translations["opentooltip"]);

		dhxMsgWebBar.addSeparator("sep05", 4);

		dhxMsgWebBar.addButton("unopen1", 5, translations["unopen"], "unreaded_msgs.gif", "unreaded_msgs_dis.gif");
		dhxMsgWebBar.setItemToolTip("unopen1", translations["unopentooltip"]);

		dhxMsgWebBar.addSeparator("sep04", 6);

		dhxMsgWebBar.addButton("forward1", 7, translations["forward"], "forward_msg.gif", "forward_msg_dis.gif");
		dhxMsgWebBar.setItemToolTip("forward1", translations["forwardtooltip"]);


		dhxMsgWebBar.attachEvent("onClick", function(id)
		{
			if (id == "open1")
			{
				dhxWinMsg.close();
				ActionMarkMessageAsTreated();
			}

			if (id == "forward1")
			{
				dhxWinMsg.close();
				ActionForwardMessage();
			}

			if (id == "reply1")
			{
				dhxWinMsg.close();
				ActionNewMessage (true);
			}

			if (id == "unopen1")
			{
				dhxWinMsg.close();
				ActionMarkMessageAsNotTreated();
			}
		});

		dhxMsgWebBar.disableItem("open1");
		dhxMsgWebBar.disableItem("reply1");
		dhxMsgWebBar.disableItem("forward1");
		dhxMsgWebBar.disableItem("unopen1");

		if (msgObj != null && allowActions)
		{
			// find if user is in CC
			var commucc = msgObj.commucc;
			var found = false;
			var t_readdata = "";
			for (var i = 0; i < commucc.length && !found; i++)
			{
				if (_t_user == commucc[i].t_user)
				{
					t_readdata = commucc[i].t_readdata;
					found = true;
				}
			}

			if ((msgObj.t_sendto == _t_user && msgObj.t_readdata == "0") ||
				found && t_readdata == "0")
			{
				dhxMsgWebBar.enableItem("open1");
				dhxMsgWebBar.enableItem("reply1");
				dhxMsgWebBar.enableItem("forward1");
			}

			if ((msgObj.t_sendto == _t_user && msgObj.t_readdata != "0") ||
				found && t_readdata != "0")
			{
				dhxMsgWebBar.enableItem("unopen1");
			}
		}
	}

	function CreateMessageWindowForm(onlyOkButton, onlySendActive, messageObj)
	{
		//http://docs.dhtmlx.com/doku.php?id=dhtmlxform:form_controls

		var formData;
		if (onlyOkButton)
		{
			formData = [
				{type: "settings", position: "absolute",
					labelLeft:"0", labelWidth: "80", labelHeight:"20",
					inputLeft:"100", inputWidth: "500", inputHeight:"20"},

				{type: "hidden", name: "t_company", value: messageObj.t_company},
				{type: "hidden", name: "t_user", value: messageObj.t_user},
				{type: "hidden", name: "relatedto", value: messageObj.relatedto},
				{type: "hidden", name: "scope", value: messageObj.scope},
				{type: "hidden", name: "t_reference", value: messageObj.t_reference},

				{type: "input", name: "t_username", value: messageObj.t_username, label: translations["from"] + ":", readonly:true,
					labelTop:"0", inputTop:"0", inputLeft:"100"},

				{type: "input", name: "t_sendto", label: translations["to"] + ":", value: messageObj.t_sendtoname, readonly:true,
					labelTop:"25", inputTop:"25", inputLeft:"100"},

				{type: "input", name: "t_sendtocc", label: translations["copycarbon"] + ":", value: messageObj.t_sendtocc, readonly:true,
					labelTop:"50", inputTop:"50", inputLeft:"100", inputWidth: "500"},

//				{type: "input", name: "scope", label: translations["scope"] + ":", value: messageObj.scope, readonly:true,
//					labelTop:"75", inputTop:"75", inputLeft:"100"},
//
//				{type: "input", name: "t_reference", value: messageObj.t_reference, label: translations["reference"] + ":", readonly:true,
//					labelTop:"100", inputTop:"100", inputLeft:"100"},

				{type: "input", name: "subject", value: messageObj.subject, label: translations["subject"] + ":", readonly:true,
					labelTop:"75", inputTop:"75", inputLeft:"100", inputWidth: "500"},

				{type: "input", name: "t_description", rows: 6, value: messageObj.t_description, label: translations["message"] + ":", readonly:true,
					labelTop:"100", inputTop:"100", inputHeight:"120", inputLeft:"100", inputWidth: "500"},

				{type: "input", name: "t_links", value: messageObj.t_links, label: translations["links"] + ":", readonly:true,
					labelTop:"225", inputTop:"225", inputLeft:"100", inputWidth: "500"},

				{type: "input", name: "tdocid", value: messageObj.tdocid, label: translations["documents"] + ":", inputWidth: 500,
					labelTop:"250", inputTop:"250", inputLeft:"100"},

				{type: "button", value: "Ok", command: "ok",
					inputLeft:"0", inputTop:"285"}
			];
		}
		else if (onlySendActive)
		{
			formData = [
				{type: "settings", position: "absolute",
					labelLeft:"0", labelWidth: "80", labelHeight:"20",
					inputLeft:"100", inputWidth: "500", inputHeight:"20"},

				{type: "hidden", name: "t_company", value: messageObj.t_company},
				{type: "hidden", name: "t_user", value: messageObj.t_user},
				{type: "hidden", name: "relatedto", value: messageObj.relatedto},
				{type: "hidden", name: "scope", value: messageObj.scope},
				{type: "hidden", name: "t_reference", value: messageObj.t_reference},

				{type: "input", name: "t_username", value: messageObj.t_username, label: translations["from"] + ":", readonly:true,
					labelTop:"0", inputTop:"0", inputLeft:"100"},

				{type: "combo", name: "t_sendto", label: translations["to"] + ":", validate: "NotEmpty",
					labelTop:"25", inputTop:"25", inputLeft:"100"},

				{type: "input", name: "t_sendtocc", label: translations["copycarbon"] + ":", value: messageObj.t_sendtocc, readonly:true,
					labelTop:"50", inputTop:"50", inputLeft:"100", inputWidth: "500"},

//				{type: "input", name: "scope", label: translations["scope"] + ":", value: messageObj.scope, readonly:true,
//					labelTop:"75", inputTop:"75", inputLeft:"100"},
//
//				{type: "input", name: "t_reference", value: messageObj.t_reference, label: translations["reference"] + ":", readonly:true,
//					labelTop:"100", inputTop:"100", inputLeft:"100"},

				{type: "input", name: "subject", value: messageObj.subject, label: translations["subject"] + ":", inputWidth: 500, readonly:true,
					labelTop:"75", inputTop:"75", inputLeft:"100"},

				{type: "input", name: "t_description", rows: 6, value: messageObj.t_description, label: translations["message"] + ":", inputWidth: 500, readonly:true,
					labelTop:"100", inputTop:"100", inputHeight:"120", inputLeft:"100"},

				{type: "input", name: "t_links", value: messageObj.t_links, label: translations["links"] + ":", inputWidth: 500, readonly:true,
					labelTop:"225", inputTop:"225", inputLeft:"100"},

				{type: "input", name: "tdocid", value: messageObj.tdocid, label: translations["documents"] + ":", inputWidth: 500,
					labelTop:"250", inputTop:"250", inputLeft:"100"},

				{type: "label", name: "lbl1", label: translations["docattachhelp"], labelWidth: 500,
					labelLeft: "100", labelTop:"275"},

				{type: "button", value: translations["send"], command: "send",
					inputLeft:"0", inputTop:"310"},

				{type: "button", value: translations["cancel"], command: "cancel",
					inputLeft:"100", inputTop:"310"}
			];
		}
		else
		{
			var btnWidth = "75";
			if (navigator.appName.indexOf("Explorer") != -1)
				btnWidth = "110";

			formData = [
				{type: "settings", position: "absolute",
					labelLeft:"0", labelWidth: "80", labelHeight:"20",
					inputLeft:"100", inputWidth: "500", inputHeight:"20"},

				{type: "hidden", name: "t_company", value: messageObj.t_company},
				{type: "hidden", name: "t_user", value: messageObj.t_user},
				{type: "hidden", name: "relatedto", value: messageObj.relatedto},
				{type: "hidden", name: "scope", value: messageObj.scope},
				{type: "hidden", name: "t_reference", value: messageObj.t_reference},

				{type: "input", name: "t_username", value: messageObj.t_username, label: translations["from"] + ":", readonly:true,
					labelTop:"0", inputTop:"0", inputLeft:"100"},

				{type: "combo", name: "t_sendto", label: translations["to"] + ":", validate: "NotEmpty",
					labelTop:"25", inputTop:"25", inputLeft:"100"},

				{type: "combo", name: "seluserscc", label: translations["copycarbon"] + ":",
					labelTop:"50", inputTop:"50", inputLeft:"100", inputWidth: "375"},

				{type: "button", value: "Adicionar CC", name: "addToCc", command: "addToCc", width: btnWidth,
					inputLeft:"485", inputTop:"50"},

				{type: "input", name: "t_sendtocc", label: "", value: messageObj.t_sendtocc, readonly:true,
					labelTop:"75", inputTop:"75", inputLeft:"100", inputWidth: "375"},

				{type: "button", value: "Limpar CC", name: "delToCc", command: "delToCc", width: btnWidth,
					inputLeft:"485", inputTop:"75"},

//				{type: "combo", name: "scope", label: translations["scope"] + ":", validate: "NotEmpty",
//					labelTop:"100", inputTop:"100", inputLeft:"100"},
//
//				{type: "input", name: "t_reference", value: messageObj.t_reference, label: translations["reference"] + ":", validate: "NotEmpty",
//					labelTop:"125", inputTop:"125", inputLeft:"100"},
//
				{type: "input", name: "subject", value: messageObj.subject, label: translations["subject"] + ":", inputWidth: 500,
					labelTop:"100", inputTop:"100", inputLeft:"100"},

				{type: "input", name: "t_description", rows: 6, value: messageObj.t_description, label: translations["message"] + ":", inputWidth: 500, validate: "NotEmpty",
					labelTop:"125", inputTop:"125", inputHeight:"120", inputLeft:"100"},

				{type: "input", name: "t_links", value: messageObj.t_links, label: translations["links"] + ":", inputWidth: 500,
					labelTop:"250", inputTop:"250", inputLeft:"100"},

				{type: "input", name: "tdocid", value: messageObj.tdocid, label: translations["documents"] + ":", inputWidth: 500,
					labelTop:"275", inputTop:"275", inputLeft:"100"},

				{type: "label", name: "lbl1", label: translations["docattachhelp"], labelWidth: 500,
					labelLeft: "100", labelTop:"300"},

				{type: "button", value: translations["send"], command: "send",
					inputLeft:"0", inputTop:"340"},

				{type: "button", value: translations["cancel"], command: "cancel",
					inputLeft:"100", inputTop:"340"}
			];
		}

		dhxForm = dhxWinMsg.attachForm(formData);
	}

	/***** INTERFACES *****/


	/***** ACTIONS *****/

	/* Handles Action: List of items in grid */
	function ActionLoadMessagesList ()
	{
		statusBar.setText(translations["loadingmsg"] + " ...");
		dhxGrid.clearAll();
		//var lnk = "./efa.php?page=communicator&mod=communicatorGetMsgList&operation=" + currFilter + "&scope=" + _scope + "&t_reference=" + _t_reference + "&t_user=" + _t_user + "&sid=" + _sid + "UNIK="+new Date().getTime();
		var lnk = baseLnk + "&mod=communicatorGetMsgList&operation=" + currFilter + "&t_user=" + _t_user + "&UNIK=" + new Date().getTime();
		dhxGrid.load(lnk, function()
		{
			HideNonRequiredColumns();
			statusBar.setText(dhxGrid.getRowsNum() + " " + translations["messages"]);
			EnableDisableFilterButtons();
			EnableDisableToolbarButtons(null);
		}, "xml");
	}

	/* Handles Action: New Message */
	function ActionNewMessage (reply)
	{
		message = new CommunicatorMessage ();

		if (reply)
		{
			message.t_company = dhxGrid.cellById(dhxGrid.getSelectedId(), 0).getValue();
			message.t_reference = dhxGrid.cellById(dhxGrid.getSelectedId(), 1).getValue();
			message.t_sequence = '';
			message.scope = dhxGrid.cellById(dhxGrid.getSelectedId(), 3).getValue();
			message.subject = dhxGrid.cellById(dhxGrid.getSelectedId(), 5).getValue();
			message.t_sendto = dhxGrid.cellById(dhxGrid.getSelectedId(), 9).getValue();
			message.t_links = dhxGrid.cellById(dhxGrid.getSelectedId(), 10).getValue();

			message.relatedto = dhxGrid.cellById(dhxGrid.getSelectedId(), 2).getValue();

			message.t_user = _t_user;
			message.t_username = _t_username;
		}
		else
		{
			message.t_company = _t_company;
			message.t_reference = _t_reference;
			message.t_user = _t_user;
			message.t_username = _t_username;
			message.scope = _scopes[0];
		}

		CreateMessageWindow(false, false, message);

		// Load users list
		var toCombo = dhxForm.getCombo("t_sendto");
		if (recipients != null)
		{
			toCombo.addOption (recipients);
		}
		else
		{
			//toCombo.loadXML ("./efa.php?page=communicator&mod=communicatorGetUsersList&sid=" + _sid + "&UNIK=" + new Date().getTime(), function()
			var lnk = baseLnk + "&mod=communicatorGetUsersList&UNIK=" + new Date().getTime();
			toCombo.loadXML (lnk, function()
			{
				var opt = toCombo.getIndexByValue (message.t_sendto);
				if (opt != null && opt != -1)
					toCombo.selectOption (opt, null, true);
			});
		}
		toCombo.enableFilteringMode(true);

//		// Load scopes list
//		var scopeCombo = dhxForm.getCombo("scope");
//		scopeCombo.loadXML ("./efa.php?page=communicator&mod=communicatorGetScopesList&sid=" + _sid + "&UNIK=" + new Date().getTime(), function()
//		{
//			var opt = scopeCombo.getIndexByValue (message.scope);
//			if (opt != null && opt != -1)
//				scopeCombo.selectOption (opt, null, true);
//
////			var opt;
////			var i = 10;
////			do
////			{
////				opt = scopeCombo.getOptionByIndex(i);
////				i++;
////			}while (opt != null);
//		});
//		scopeCombo.enableFilteringMode(true);


		// Load users list
		var toCCCombo = dhxForm.getCombo("seluserscc");
		if (recipients != null)
		{
			toCCCombo.addOption (recipients);
		}
		else
		{
			//toCCCombo.loadXML ("./efa.php?page=communicator&mod=communicatorGetUsersList&sid=" + _sid + "&UNIK=" + new Date().getTime());
			lnk = baseLnk + "&mod=communicatorGetUsersList&UNIK=" + new Date().getTime();
			toCCCombo.loadXML (lnk);
			toCCCombo.enableFilteringMode(true);
		}

		// Handle events
		dhxForm.attachEvent("onButtonClick", function(name, command)
		{
			if (command == "addToCc")
			{
				var userid = dhxForm.getCombo("seluserscc").getSelectedValue();
				var username = dhxForm.getCombo("seluserscc").getSelectedText();
				if(userid != null && userid != '')
				{
					var currVal = dhxForm.getItemValue("t_sendtocc");
					if (currVal == null || currVal == '')
						dhxForm.setItemValue("t_sendtocc", username + "<" + userid + ">");
					else
						dhxForm.setItemValue("t_sendtocc", currVal + "; " + username + "<" + userid + ">");

					toCCCombo.setComboText("");
				}
			}

			if (command == "delToCc")
			{
				var t_sendtocc = dhxForm.getItemValue("t_sendtocc");
				if (t_sendtocc != null && t_sendtocc != "")
				{
					var oldval = t_sendtocc.split(";");
					if (oldval.length > 0)
					{
						var newval = "";
						for (var i = 0; i < oldval.length - 1; i++)
						{
							if (i == 0)
								newval += oldval[i];
							else
								newval += ";" + oldval[i];
						}

						dhxForm.setItemValue("t_sendtocc", newval);
					}
				}
			}

			if (command == "send")
			{
				if(dhxForm.validate())
					ActionSendNewMessage();
			}

			if (command == "cancel")
				dhxWinMsg.close();
		});

		dhxWinMsg.show();

//		if (reply)
//			dhxForm.setItemFocus("t_description");
	}

	/* Handles Action: Send New Message */
	function ActionSendNewMessage ()
	{
		message = new CommunicatorMessage ();
		message.t_company = dhxForm.getItemValue("t_company");
		message.t_reference = dhxForm.getItemValue("t_reference");
		//message.scope = dhxForm.getCombo("scope").getSelectedValue();
		message.scope = dhxForm.getItemValue("scope");
		message.subject = dhxForm.getItemValue("subject");
		message.t_description = dhxForm.getItemValue("t_description");
		message.t_sendto = dhxForm.getCombo("t_sendto").getSelectedValue();
		message.t_user = dhxForm.getItemValue("t_user");
		message.t_links = dhxForm.getItemValue("t_links");
		message.relatedto = dhxForm.getItemValue("relatedto");

		message.t_sendtocc = dhxForm.getItemValue("t_sendtocc");

		message.tdocid = dhxForm.getItemValue("tdocid");

		xmlHttpObj = CreateXmlHttpRequestObject();
		if(xmlHttpObj)
		{
			//var lnk = "./efa.php?page=communicator&mod=communicatorSendNewMsg&sid=" + _sid + "&" + message.buildMessageStringLink() + "&UNIK=" + new Date().getTime();
			var lnk = baseLnk + "&mod=communicatorSendNewMsg&" + message.buildMessageStringLink() + "&UNIK=" + new Date().getTime();
			xmlHttpObj.open('GET',lnk, true);
			xmlHttpObj.onreadystatechange = eval(ProcessActionSendNewMessage);
			xmlHttpObj.send(null);
		}
	}

	/* Handles ActionSendNewMessage AJAX call */
	function ProcessActionSendNewMessage ()
	{
		if(xmlHttpObj.readyState == 4 && xmlHttpObj.status == 200)
		{
			var xmlResponse = xmlHttpObj.responseXML;
			if (xmlResponse != null)
			{
				var response = new CommunicatorResponse ();
				response.loadFromXML(xmlResponse);

				// Success
				if (response.result == '1')
				{
					dhxWinMsg.close();
					ActionLoadMessagesList();
				}
				// Error
				else
				{
					var sb = dhxWinMsg.attachStatusBar();
					sb.setText(response.tdesc);
				}
			}
		}
	}

	/* Handles Action: View Message */
	function ActionViewMessage ()
	{
		xmlHttpObj = CreateXmlHttpRequestObject();
		if(xmlHttpObj)
		{
			var msg = new CommunicatorMessage ();
			msg.t_company = dhxGrid.cellById(dhxGrid.getSelectedId(), 0).getValue();
			msg.t_reference = dhxGrid.cellById(dhxGrid.getSelectedId(), 1).getValue();
			msg.t_sequence = dhxGrid.cellById(dhxGrid.getSelectedId(), 2).getValue();

			//var lnk = "./efa.php?page=communicator&mod=communicatorGetMsgItem&" + msg.buildMessageStringLink() + "&sid=" + _sid + "&UNIK=" + new Date().getTime()
			var lnk = baseLnk + "&mod=communicatorGetMsgItem&" + msg.buildMessageStringLink() + "&UNIK=" + new Date().getTime();
			xmlHttpObj.open('GET',lnk, true);
			xmlHttpObj.onreadystatechange = eval(ProcessActionViewMessage);
			xmlHttpObj.send(null);
		}
	}

	/* Handles ActionViewMessage AJAX call */
	function ProcessActionViewMessage ()
	{
		if(xmlHttpObj.readyState == 4 && xmlHttpObj.status == 200)
		{
			var xmlMsg = xmlHttpObj.responseXML;
			if (xmlMsg != null)
			{
				var message = new CommunicatorMessage ();
				message.loadFromXML(xmlMsg);

				CreateMessageWindow(false, true, message);
				dhxForm.attachEvent("onButtonClick", function(name, command)
				{
					dhxWinMsg.close();
				});
				dhxWinMsg.show();
			}
		}
	}

	/* Handles Action: Mark Message As Treated */
	function ActionMarkMessageAsTreated()
	{
		xmlHttpObj = CreateXmlHttpRequestObject();
		if(xmlHttpObj)
		{
			var msg = new CommunicatorMessage ();
			msg.t_company = dhxGrid.cellById(dhxGrid.getSelectedId(), 0).getValue();
			msg.t_reference = dhxGrid.cellById(dhxGrid.getSelectedId(), 1).getValue();
			msg.t_sequence = dhxGrid.cellById(dhxGrid.getSelectedId(), 2).getValue();

			//var lnk = "./efa.php?page=communicator&mod=communicatorMarkMsgAsTrtd&" + msg.buildMessageStringLink() + "&sid=" + _sid + "&UNIK=" + new Date().getTime()
			var lnk = baseLnk + "&mod=communicatorMarkMsgAsTrtd&" + msg.buildMessageStringLink() + "&UNIK=" + new Date().getTime();
			xmlHttpObj.open('GET', lnk, true);
			xmlHttpObj.onreadystatechange = eval(ProcessActionMarkMessageAsTreated);
			xmlHttpObj.send(null);
		}
	}

	/* Handles ActionViewMessage AJAX call */
	function ProcessActionMarkMessageAsTreated()
	{
		if(xmlHttpObj.readyState == 4 && xmlHttpObj.status == 200)
		{
			var xmlResponse = xmlHttpObj.responseXML;
			if (xmlResponse != null)
			{
				var response = new CommunicatorResponse ();
				response.loadFromXML(xmlResponse);

				// Success
				if (response.result == '1')
				{
					ActionLoadMessagesList();
				}
				// Error
				else
				{
					statusBar.setText(response.tdesc);
				}
			}
		}
	}

	/* Handles Action: Mark Message As Not Treated */
	function ActionMarkMessageAsNotTreated()
	{
		xmlHttpObj = CreateXmlHttpRequestObject();
		if(xmlHttpObj)
		{
			var msg = new CommunicatorMessage ();
			msg.t_company = dhxGrid.cellById(dhxGrid.getSelectedId(), 0).getValue();
			msg.t_reference = dhxGrid.cellById(dhxGrid.getSelectedId(), 1).getValue();
			msg.t_sequence = dhxGrid.cellById(dhxGrid.getSelectedId(), 2).getValue();

			//var lnk = "./efa.php?page=communicator&mod=communicatorMarkMsgAsNotTrtd&" + msg.buildMessageStringLink() + "&sid=" + _sid + "&UNIK=" + new Date().getTime()
			var lnk = baseLnk + "&mod=communicatorMarkMsgAsNotTrtd&" + msg.buildMessageStringLink() + "&UNIK=" + new Date().getTime();
			xmlHttpObj.open('GET', lnk, false);
			xmlHttpObj.send(null);

			var xmlResponse = xmlHttpObj.responseXML;
			if (xmlResponse != null)
			{
				var response = new CommunicatorResponse ();
				response.loadFromXML(xmlResponse);

				// Success
				if (response.result == '1')
				{
					ActionLoadMessagesList();
				}
				// Error
				else
				{
					statusBar.setText(response.tdesc);
				}
			}
		}
	}

	/* Handles Action: Forward a Message */
	function ActionForwardMessage()
	{
		xmlHttpObj = CreateXmlHttpRequestObject();
		if(xmlHttpObj)
		{
			var msg = new CommunicatorMessage ();
			msg.t_company = dhxGrid.cellById(dhxGrid.getSelectedId(), 0).getValue();
			msg.t_reference = dhxGrid.cellById(dhxGrid.getSelectedId(), 1).getValue();
			msg.t_sequence = dhxGrid.cellById(dhxGrid.getSelectedId(), 2).getValue();

			//var lnk = "./efa.php?page=communicator&mod=communicatorGetMsgItem&" + msg.buildMessageStringLink() + "&sid=" + _sid + "&UNIK=" + new Date().getTime()
			var lnk = baseLnk + "&mod=communicatorGetMsgItem&" + msg.buildMessageStringLink() + "&UNIK=" + new Date().getTime();
			xmlHttpObj.open('GET',lnk, true);
			xmlHttpObj.onreadystatechange = eval(ProcessActionForwardMessage);
			xmlHttpObj.send(null);
		}
	}

	/* Handles ActionForwardMessage AJAX call */
	function ProcessActionForwardMessage ()
	{
		if(xmlHttpObj.readyState == 4 && xmlHttpObj.status == 200)
		{
			var xmlMsg = xmlHttpObj.responseXML;
			if (xmlMsg != null)
			{
				var message = new CommunicatorMessage ();
				message.loadFromXML(xmlMsg);
				message.t_user = _t_user;
				message.t_username = _t_username;

				CreateMessageWindow(true, false, message);

				// Load users list
				var toCombo = dhxForm.getCombo("t_sendto");
				if (recipients != null)
				{
					toCombo.addOption (recipients);
				}
				else
				{
					//toCombo.loadXML ("./efa.php?page=communicator&mod=communicatorGetUsersList&sid=" + _sid + "&UNIK=" + new Date().getTime());
					var lnk = baseLnk + "&mod=communicatorGetUsersList&UNIK=" + new Date().getTime();
					toCombo.loadXML (lnk);
					toCombo.enableFilteringMode(true);
				}

				// Handle events
				dhxForm.attachEvent("onButtonClick", function(name, command)
				{
					if (command == "send")
					{
						if(dhxForm.validate())
							SubActionForwardMessageExec();
					}

					if (command == "cancel")
						dhxWinMsg.close();
				});

				dhxWinMsg.show();
			}
		}
	}

	/* Handles Action: Forward a Message */
	function SubActionForwardMessageExec()
	{
		xmlHttpObj = CreateXmlHttpRequestObject();
		if(xmlHttpObj)
		{
			var msg = new CommunicatorMessage ();
			msg.t_company = dhxGrid.cellById(dhxGrid.getSelectedId(), 0).getValue();
			msg.t_reference = dhxGrid.cellById(dhxGrid.getSelectedId(), 1).getValue();
			msg.t_sequence = dhxGrid.cellById(dhxGrid.getSelectedId(), 2).getValue();
			msg.t_sendto = dhxForm.getCombo("t_sendto").getSelectedValue();

			//var lnk = "./efa.php?page=communicator&mod=communicatorForwardMsg&" + msg.buildMessageStringLink() + "&sid=" + _sid + "&UNIK=" + new Date().getTime()
			var lnk = baseLnk + "&mod=communicatorForwardMsg&" + msg.buildMessageStringLink() + "&UNIK=" + new Date().getTime();
			xmlHttpObj.open('GET',lnk, true);
			xmlHttpObj.onreadystatechange = eval(SubProcessActionForwardMessageExec);
			xmlHttpObj.send(null);
		}
	}

	/* Handles SubActionForwardMessageExec AJAX call */
	function SubProcessActionForwardMessageExec ()
	{
		if(xmlHttpObj.readyState == 4 && xmlHttpObj.status == 200)
		{
			var xmlResponse = xmlHttpObj.responseXML;
			if (xmlResponse != null)
			{
				var response = new CommunicatorResponse ();
				response.loadFromXML(xmlResponse);

				// Success
				if (response.result == '1')
				{
					dhxWinMsg.close();
					ActionLoadMessagesList();
				}
				// Error
				else
				{
					//statusBar.setText(response.tdesc);
					var sb = dhxWinMsg.attachStatusBar();
					sb.setText(response.tdesc);
				}
			}
		}
	}

	/* Handles Action: Express Search */
	function ActionSearchMessage(value)
	{
		statusBar.setText(translations["loadingmsg"] + " ...");
		dhxGrid.clearAll();
		//var lnk = "./efa.php?page=communicator&mod=communicatorGSearch&gsearch=" + value + "&scope=" + _scope + "&t_user=" + _t_user + "&sid=" + _sid + "UNIK="+new Date().getTime();
		var lnk = baseLnk + "&mod=communicatorGSearch&gsearch=" + value + "&UNIK=" + new Date().getTime();
		dhxGrid.load(lnk, function()
		{
			currFilter = "gsearch";
			HideNonRequiredColumns();
			statusBar.setText(dhxGrid.getRowsNum() + " " + translations["messages"]);
			EnableDisableFilterButtons();
			EnableDisableToolbarButtons(null);
		}, "xml");
	}

	/***** ACTIONS *****/



	/***** HELPERS *****/

	/* Create an AJAX object handler */
	function CreateXmlHttpRequestObject()
	{
		var xmlHttpObj = null;
		if (window.XMLHttpRequest) // IE 7 e Firefox
			xmlHttpObj=new XMLHttpRequest()

		else if (window.ActiveXObject) // IE 5 e 6
			xmlHttpObj=new ActiveXObject("Microsoft.XMLHTTP")

		return xmlHttpObj;
	}

	/***** HELPERS *****/
}

function CommunicatorMessage ()
{
	this.t_company = "";
	this.t_reference = "";
	this.t_sequence = "";
	this.scope = "";
	this.t_date = "";
	this.subject = "";
	this.t_description = "";
	this.t_sendto = "";
	this.t_sendtocc = "";
	this.t_sendtoname = "";
	this.t_readdata = "";
	this.t_user = "";
	this.t_username = "";
	this.t_links = "";
	this.relatedto = "";
	this.id = "";
	this.tdocid = "";

	this.commucc = new Array();

	this.loadFromXML = function (xmlObject)
	{
		if (xmlObject.getElementsByTagName("t_company").length > 0 &&
			xmlObject.getElementsByTagName("t_company")[0].childNodes.length > 0)
			this.t_company = xmlObject.getElementsByTagName("t_company")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_reference").length > 0 &&
			xmlObject.getElementsByTagName("t_reference")[0].childNodes.length > 0)
			this.t_reference = xmlObject.getElementsByTagName("t_reference")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_sequence").length > 0 &&
			xmlObject.getElementsByTagName("t_sequence")[0].childNodes.length > 0)
			this.t_sequence = xmlObject.getElementsByTagName("t_sequence")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("scope").length > 0 &&
			xmlObject.getElementsByTagName("scope")[0].childNodes.length > 0)
			this.scope = xmlObject.getElementsByTagName("scope")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_date").length > 0 &&
			xmlObject.getElementsByTagName("t_date")[0].childNodes.length > 0)
			this.t_date = xmlObject.getElementsByTagName("t_date")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("subject").length > 0 &&
			xmlObject.getElementsByTagName("subject")[0].childNodes.length > 0)
			this.subject = xmlObject.getElementsByTagName("subject")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_description").length > 0 &&
			xmlObject.getElementsByTagName("t_description")[0].childNodes.length > 0)
			this.t_description = xmlObject.getElementsByTagName("t_description")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_sendto").length > 0 &&
			xmlObject.getElementsByTagName("t_sendto")[0].childNodes.length > 0)
			this.t_sendto = xmlObject.getElementsByTagName("t_sendto")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_sendtocc").length > 0 &&
			xmlObject.getElementsByTagName("t_sendtocc")[0].childNodes.length > 0)
			this.t_sendtocc = xmlObject.getElementsByTagName("t_sendtocc")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_sendtoname").length > 0 &&
			xmlObject.getElementsByTagName("t_sendtoname")[0].childNodes.length > 0)
			this.t_sendtoname = xmlObject.getElementsByTagName("t_sendtoname")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_readdata").length > 0 &&
			xmlObject.getElementsByTagName("t_readdata")[0].childNodes.length > 0)
			this.t_readdata = xmlObject.getElementsByTagName("t_readdata")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_user").length > 0 &&
			xmlObject.getElementsByTagName("t_user")[0].childNodes.length > 0)
			this.t_user = xmlObject.getElementsByTagName("t_user")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_username").length > 0 &&
			xmlObject.getElementsByTagName("t_username")[0].childNodes.length > 0)
			this.t_username = xmlObject.getElementsByTagName("t_username")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_links").length > 0 &&
			xmlObject.getElementsByTagName("t_links")[0].childNodes.length > 0)
			this.t_links = xmlObject.getElementsByTagName("t_links")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("relatedto").length > 0 &&
			xmlObject.getElementsByTagName("relatedto")[0].childNodes.length > 0)
			this.relatedto = xmlObject.getElementsByTagName("relatedto")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("id").length > 0 &&
			xmlObject.getElementsByTagName("id")[0].childNodes.length > 0)
			this.id = xmlObject.getElementsByTagName("id")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("tdocid").length > 0 &&
			xmlObject.getElementsByTagName("tdocid")[0].childNodes.length > 0)
			this.tdocid = xmlObject.getElementsByTagName("tdocid")[0].childNodes[0].nodeValue;

		var commuccNodes = xmlObject.getElementsByTagName("commucc");
		if (commuccNodes.length > 0)
		{
			//this.commucc = new Array ();
			for (var i = 0; i < commuccNodes.length; i++)
			{
				var cc = new CommunicatorMessageCC();
				cc.loadFromXML (commuccNodes[i]);
				this.commucc[i] = cc;
			}
		}

	}

	this.buildMessageStringLink = function ()
	{
		var lnk;

		lnk = 't_company=' + escape (this.t_company);
		lnk += '&t_reference=' + escape (this.t_reference);
		lnk += '&t_sequence=' + escape (this.t_sequence);
		lnk += '&scope=' + escape (this.scope);
		lnk += '&t_date=' + escape (this.t_date);
		lnk += '&subject=' + escape (this.subject);
		lnk += '&t_description=' + escape (this.t_description);
		lnk += '&t_sendto=' + escape (this.t_sendto);
		lnk += '&t_sendtocc=' + escape (this.t_sendtocc);
		lnk += '&t_readdata=' + escape (this.t_readdata);
		lnk += '&t_user=' + escape (this.t_user);
		lnk += '&t_reference=' + escape (this.t_reference);
		lnk += '&t_links=' + escape (this.t_links);
		lnk += '&relatedto=' + escape (this.relatedto);
		lnk += '&tdocid=' + escape (this.tdocid);

		return lnk;
	}
}

function CommunicatorMessageCC ()
{
	this.t_user = "";
	this.t_username = "";
	this.t_readdata = "";

	this.loadFromXML = function (xmlObject)
	{
		if (xmlObject.getElementsByTagName("t_user").length > 0 &&
			xmlObject.getElementsByTagName("t_user")[0].childNodes.length > 0)
			this.t_user = xmlObject.getElementsByTagName("t_user")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_username").length > 0 &&
			xmlObject.getElementsByTagName("t_username")[0].childNodes.length > 0)
			this.t_username = xmlObject.getElementsByTagName("t_username")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("t_readdata").length > 0 &&
			xmlObject.getElementsByTagName("t_readdata")[0].childNodes.length > 0)
			this.t_readdata = xmlObject.getElementsByTagName("t_readdata")[0].childNodes[0].nodeValue;
	}
}

function CommunicatorResponse ()
{
	this.result = '';
	this.tdesc = '';

	this.loadFromXML = function (xmlObject)
	{
		if (xmlObject.getElementsByTagName("result")[0].childNodes.length > 0)
			this.result = xmlObject.getElementsByTagName("result")[0].childNodes[0].nodeValue;

		if (xmlObject.getElementsByTagName("tdesc")[0].childNodes.length > 0)
			this.tdesc = xmlObject.getElementsByTagName("tdesc")[0].childNodes[0].nodeValue;
	}

}
