<?php
	//import flash.accessibility.AccessibilityProperties;
	//TODO
	//import flash.display.*;
	require_once dirname(__FILE__) . '/../flashemu/Sprite.php';
	//import flash.events.*;
	//TODO
	//import flash.net.*;
	//TODO
	//import flash.printing.*;
	require_once dirname(__FILE__) . '/../flashemu/PrintJob.php';
	//import flash.geom.*;
	//import flash.text.*;
	//import flash.ui.Mouse;
	//import flash.utils.Timer;
	//import flash.external.ExternalInterface;
	//import weiner.utils.as3.Utils;
	
	class DSSPrintForm /*extends Sprite*/ {
		const LIST_PER_PRINT = 39; //Количество листов стоимостью "одна печать", может быть переписано с помощью flashVars
		/*private*/ const MAX_RECOVERY_HELP_LINK = "/help.php"; //Ссылка "Как это исправить см в разделе помощь" при количестве листов более чем лимит, может быть переписано с помощью flashVars
		//private var dssLoader:DSSLoader;
		private $dssParser;
		//dep? private var formView:PrintForm;
		//dep? private var formViewShadow:Sprite;
		//dep? private var viewW:Number;
		//dep? private var viewH:Number;
		//dep? private var viewX:Number;
		//dep? private var viewY:Number;
		//dep? private var responder:CResponder;
		//dep? private var dlg:DSSAlertDialog;
		//dep? private var printInfo:Array;
		private $fileName;
		//dep? private var confirmDlg :DSSConfirmPrintDialog;
		//dep? private var confirmDialogShadow:Sprite;
		public  $fileID;
		//dep? private var handCursor:HandView;
		private var eng_locale:Boolean = false;//true;
		private var sendWriteStatAction:Boolean = false;//true;
		private var flashVars:Object;
		private var pdfCreator:PdfCreator = new PdfCreator();
		private var _is_expire_time_wait:Boolean = false;
		
		public function DSSPrintForm(_fileName:String, dssL:DSSLoader, w:Number, h:Number, _handCursor:HandView, flashVars:Object, fileId, _printInfo:Array, is_expire_time_wait:Boolean) {
			this.printInfo = _printInfo;
			this._is_expire_time_wait = is_expire_time_wait;
			Locale.LocaleInit();
			this.flashVars = flashVars;
			_setListPerPrint();
			handCursor = _handCursor;
			fileName = _fileName;
			fileID = fileId;
			//pj = _pj;
			dssLoader = dssL;
			super();
			formView = new PrintForm();
			viewW = formView.layer2.viewArea.width;
			viewH = formView.layer2.viewArea.height;
			viewX = formView.layer2.viewArea.x;
			viewY = formView.layer2.viewArea.y;
			createShadow();
			initBehavior();
			//formView.layer2.sh1.visible = false;
			
			PrintHelper.flashVars = flashVars;
			
			//TODO здесь разбираюсь, что и как, зачем передаю viewArea?
			var o = PrintManager.preparePrint(dssLoader.parsedData, formView.layer2.viewArea, true/*, pj*/);
			
			if (o == false) return;
			this.addChild(formView);
			
		
			dlg = new DSSAlertDialog();
			this.addChild(dlg);
			dlg.x = (formView.width - dlg.width) / 2 + 120;
			dlg.y = (formView.height - dlg.height) / 2 ;
			dlg.visible = false;
			dlg.ok_btn.addEventListener(MouseEvent.CLICK, hideDlg);
			createConfirmDlg();
			
			_processUserData();
			
			/*var dump:String = "";
			var dbg  = dssLoader.parsedData;
			dump += "class Dtd{\nstatic public function Init(){\n";
			dump += "var data:Dynamic;\n";
			dump += "data = {lines:new CArray()};\n";
			for (var i = 0; i <  dbg.lines.length; i++ )
			{
				o = dbg.lines[i];
				dump += "var object:Dynamic;\n";
				dump += "object = {startX:" + o.startX + ", startY:" + o.startY + ", endX:" + o.endX + ", endY:" + o.endY + "};\n";
				dump += "data.lines.push(object);\n";
			}
			dump += "return data;\n}//end Init\n}//end class";
			alert(dump);*/
			//alert("paperWidth = " + o.paperW + ", paperHeight = " + o.paperH + ", pageW = " + o.pageW + ", pageH = " + o.pageH);
		}
		/**
		 * @desc Устанавливает объект "тени" с кнопками связанными с аккаунтом
		 *       назначает обработчик кнопке отмены ожидания печати
		*/
		
		
		//-------------------------------
		
		private function initBehavior()
		{
			formView.printBtn.buttonMode = true;
			formView.printBtn.useHandCursor = true;
			formView.printBtn.addEventListener(MouseEvent.ROLL_OVER, onPrintBtnRollOver);
			formView.printBtn.addEventListener(MouseEvent.ROLL_OUT, onPrintBtnRollOut);
			formView.printBtn.addEventListener(MouseEvent.CLICK, onPrintBtnClick );
			
			//cancelBtnBg
			formView.cancelBtnBg.buttonMode = true;
			formView.cancelBtnBg.useHandCursor = true;
			formView.cancelBtnBg.addEventListener(MouseEvent.ROLL_OVER, onCancelBtnRollOver);
			formView.cancelBtnBg.addEventListener(MouseEvent.ROLL_OUT, onCancelBtnRollOut);
			formView.cancelBtnBg.addEventListener(MouseEvent.CLICK, onCancelBtnClick );			
			
			formView.showGridTrue.gotoAndStop(1);
			formView.showGridTrue.buttonMode = true;
			formView.showGridTrue.useHandCursor = true;
			formView.showGridTrue.addEventListener(MouseEvent.CLICK, onShowGridClick);
			
			formView.allPagesRb.gotoAndStop(1);
			formView.allPagesRb.buttonMode = true;
			formView.allPagesRb.useHandCursor = true;
			formView.allPagesRb.addEventListener(MouseEvent.CLICK, onAllPagesRbClick);
			formView.pagesLineDisabled.visible = true;
			
			
			formView.customPagesRb.gotoAndStop(2);
			formView.customPagesRb.buttonMode    = true;
			formView.customPagesRb.useHandCursor = true;
			formView.customPagesRb.addEventListener(MouseEvent.CLICK, onCustomPagesRbClick);
			
			
			formViewShadow.addEventListener(MouseEvent.MOUSE_WHEEL, onMouseWheel);
			formViewShadow.addEventListener(MouseEvent.MOUSE_DOWN, mouseDownEvent);
			formViewShadow.addEventListener(MouseEvent.MOUSE_UP, mouseUpEvent);
			formViewShadow.addEventListener(MouseEvent.MOUSE_MOVE, mouseMoveEvent);
			
			//formView.pagesLine.addEventListener(TextEvent.TEXT_INPUT, onTextChange);
			formView.pagesLine.addEventListener(KeyboardEvent.KEY_DOWN, onTextChange);
			formView.pagesLine.addEventListener(KeyboardEvent.KEY_UP, onTextChange);
			
			formView.text_info_clip.visible = false;	
			formView.text_info_stable.visible = false;	
			formView.mc_maximum_text_info.visible = false;	
			
			this.addEventListener(MouseEvent.MOUSE_MOVE, 		mouseMoveEvent);
			handCursor.addEventListener(MouseEvent.MOUSE_DOWN,  mouseDownEvent);
			handCursor.addEventListener(MouseEvent.MOUSE_UP,    mouseUpEvent);
			handCursor.addEventListener(MouseEvent.MOUSE_WHEEL, onMouseWheel);
			_setMaxRecoveryLink();
			
		}
		//-------------------------------
		private function onTextChange(e:KeyboardEvent) {
			_log("oTC");
			var frames = PrintManager.framesDisplay;
			var sprites = frames.getAllSprites();
			for (var j = 0; j < sprites.length; j++)
			{
				var spr:Sprite = sprites[j];
				var child = spr.getChildByName("greenAllow");
				child.visible = false;
				child = spr.getChildByName("redCross");
				child.visible = false;
				_setGrayBorder(spr, 0, 0);
			}	
			_log('all hiddeen...');
			var pagesNumber = createCustomPagesArray(false);
			var limitPagesNumber = _setLimitOnPagesNumber(pagesNumber);
			var pageInfo = new PageOrderHelper(frames);
			var items = pageInfo.getArray();
			var quantity = 0; //количество выбранных страниц
			var i, key, sprite:Sprite;
			var redBorderW = 3,
				grayBorderW = 3;
			
			/*if (pagesNumber[0] == "ALL") {
				quantity = sprites.length;
			}*/
			
			if (limitPagesNumber[0] == "ALL") {//укладываемся в лимит
				for (i = 1; i < items.length; i++ ) //использую старый алгоритм, никаких красных рамок
				{
					key = items[i];
					if (pagesNumber[0] == "ALL") 
					{
						quantity = sprites.length;
						if (key != "-1")
						{
							sprite = frames.getSprite(key);
							child = sprite.getChildAt(sprite.numChildren - 2);
							child.visible = true;
							_setGrayBorder(sprite, grayBorderW, redBorderW);
						}
					}else  if ((key != "-1") && (pagesNumber[i] != undefined)) 
					{
							sprite = frames.getSprite(key);
							child = sprite.getChildAt(sprite.numChildren - 2);
							child.visible = true;
							_setGrayBorder(sprite, grayBorderW, redBorderW);
							quantity++;
					}
				}
			} else {//не укладываемся в лимит
				if (pagesNumber[0] == "ALL") {//здесь просто рисуем красные рамки для всех, кто выше лимта
					//throw new Error('alloe');
					quantity = sprites.length;
					for (i = 1; i < items.length; i++ ) {
						key = items[i];
						if ((key != "-1")) {
								sprite = frames.getSprite(key);
								if (limitPagesNumber[i] == 1) {
									child = sprite.getChildByName("greenAllow");
									child.visible = true;
									_setGrayBorder(sprite, grayBorderW, redBorderW);
									quantity++;
								} else {
									//TODO показать крестик
									//red border
									child = sprite.getChildByName("redCross");
									child.visible = true;
									_setRedBorder(sprite, redBorderW, grayBorderW);
								}
						}
					}
				} else {//здесь поинтереснее
					for (i = 1; i < items.length; i++ ) {
						key = items[i];
						if ((key != "-1")) {
								sprite = frames.getSprite(key);
								if (pagesNumber[i] == 1) {
									child = sprite.getChildByName("greenAllow");
									child.visible = true;
									quantity++;
									_setGrayBorder(sprite, grayBorderW, redBorderW);
									if (limitPagesNumber[i] == 0) {
										quantity--;
										child.visible = false;
										//TODO показать крестик
										//red border
										child = sprite.getChildByName("redCross");
										child.visible = true;
										_setRedBorder(sprite, redBorderW, grayBorderW);
									}
								}
						}
					}
				}
			}
			if (Number(printInfo["balance"]) <= 0) {
				_setPrintInfoViewOnFreePrint(quantity);
			} else {
				_setPrintInfoView(quantity);
			}
		}
		/**
		 * @desc Показать серую рамку у клипа
		*/
		private function _setGrayBorder(sprite:Sprite, gW:int, rW:int) {
			_setViewBorder(sprite, 0xA0A0A0);
		}
		/**
		 * @desc Показать красную рамку у клипа
		*/
		private function _setRedBorder(sprite:Sprite, rW:int, gW:int) {
			_setViewBorder(sprite, 0xFF0000);
		}
		private function _setViewBorder(sprite:Sprite, color, th = 1) {
			var child:Sprite = sprite.getChildAt(1) as Sprite;
			var w = child.width, h = child.height;
			child.graphics.clear();
			child.graphics.lineStyle(th, color);
			child.graphics.drawRect(th, th, w - th, h - th);
		}
		/**
		 * @return Number Возвращает количество страниц, поставленных в очередь на печать
		*/
		private function _getQuantityPrintedPages(pagesNumber:Array = null) {
			var frames = PrintManager.framesDisplay;
			var sprites = frames.getAllSprites();
			if (pagesNumber == null) {
				pagesNumber = createCustomPagesArray(false);
			}
			if (pagesNumber[0] == "ALL") {
				return sprites.length;
			}
			var pageInfo = new PageOrderHelper(frames);
			var items = pageInfo.getArray();
			var quantity = 0; //количество выбранных страниц
			for (var i = 1; i < items.length; i++ )
			{
				var key = items[i];
				if ((key != "-1") && (pagesNumber[i] == 1)) {
						quantity++;
				}
			}
			return quantity; 
		}
		
		//-------------------------------
		private function unsetHandCursor(e:MouseEvent)
		{
			if (handCursor != null)
			{
				mouseUpEvent(e);
				Mouse.show();
				handCursor.visible = false;
			}
		}
		//-------------------------------
		private function setHandCursor(/*e:MouseEvent*/)
		{
			if (handCursor == null)
			{
				/*handCursor = new HandView();
				handCursor.addEventListener(MouseEvent.MOUSE_DOWN,  on_mouseDown);
				handCursor.addEventListener(MouseEvent.MOUSE_UP,    on_mouseUp);
				handCursor.addEventListener(MouseEvent.MOUSE_WHEEL,    mWheel);
				this.addChild(handCursor);*/
			}
			else
			{
				handCursor.visible = true;
			}
			Mouse.hide();
		}
		//-------------------------------
		private function mouseDownEvent(e:MouseEvent)
		{
			formViewShadow.startDrag();
			handCursor.gotoAndStop(2);
			//formView.layer2.viewArea.startDrag();
		}
		//-------------------------------
		private function mouseUpEvent(e:MouseEvent)
		{
			formViewShadow.stopDrag();
			formViewShadow.x = formView.layer2.viewArea.x;
			formViewShadow.y = formView.layer2.viewArea.y;
			handCursor.gotoAndStop(1);
		}
		//-------------------------------
		private function mouseMoveEvent(e:MouseEvent)
		{
			if (formViewShadow.x > viewX) formView.layer2.viewArea.x = viewX;
			else 
			{
				formView.layer2.viewArea.x = formViewShadow.x;
			}
			
			if (formViewShadow.y > viewY) formView.layer2.viewArea.y = viewY;
			else 
			{
				formView.layer2.viewArea.y = formViewShadow.y;
			}
			
			if ((formView.layer2.viewArea.x + formView.layer2.viewArea.width < viewX + viewW))
				formView.layer2.viewArea.x = viewX + viewW - formView.layer2.viewArea.width;
				
			if ((formView.layer2.viewArea.y + formView.layer2.viewArea.height < viewY + viewH))
				formView.layer2.viewArea.y = viewY + viewH - formView.layer2.viewArea.height;
			
			if (handCursor != null)
			{
				handCursor.x = this.mouseX;
				handCursor.y = this.mouseY;
			}
			if (formView.layer2.viewAreaSrc.hitTestPoint(this.mouseX, this.mouseY))
			 setHandCursor();
			else
			  unsetHandCursor(e);
		}
		//-------------------------------
		private function onMouseWheel(e:MouseEvent)
		{
			var w = formView.layer2.viewArea.width + 	e.delta*10;
			if (w >= viewW)
			{
				var sc = w / formView.layer2.viewArea.width;
				formView.layer2.viewArea.width *= sc;
				formView.layer2.viewArea.height *= sc;
				formViewShadow.width *= sc;
				formViewShadow.height *= sc;
				mouseMoveEvent(e);
				formViewShadow.x = formView.layer2.viewArea.x;
				formViewShadow.y = formView.layer2.viewArea.y;
			}
		}
		//-------------------------------
		private function onPrintBtnClick(e:MouseEvent)
		{
			    if (sendWriteStatAction) {
				    return;
				}
				var s:String = printInfo["confirmPrintMessage"];
				//s = s.replace("{filename}", "“" + fileName + "”");
				//s = s.replace("{oneListPrice}", "<b>" + printInfo["oneListPrice"]  + "</b>" );
				var quantity = PrintManager.frames.getAllSprites().length;
				//var log = "";
				if (formView.allPagesRb.currentFrame == 2) {
					var pagesNumber = new Array();
					pagesNumber = createCustomPagesArray();
					quantity = 0;
					for (var i = 0; i < pagesNumber.length; i++ )
					{
						if (pagesNumber[i] == 1) quantity++;
					}
				}
				if (Number(printInfo["balance"]) > 0 && Number(printInfo["oneListPrice"]) > 0) {
					var limit_with_format = _getLimitWithFormat(PrintManager.pageFormat);
					quantity = (quantity < limit_with_format ? quantity : limit_with_format);
					var onePrice = Number(printInfo["oneListPrice"]);
					var n = getA4Quantity(quantity);
					var price = 0;
					while (n > 0) { n -= self::LIST_PER_PRINT; price += onePrice; }
					var post_balance = Number(printInfo["balance"]) - price;  
					if (post_balance < 0) {
						var str = "You have not enough means for a seal";
						if (!eng_locale ) str = "У Вас недостаточно средств для печати";
						alert(str);
						return;
					}
					
					/*if (Number(printInfo["is_expire"]) == 1) { //баланс выше 0, но в месяце не пролонгирован
						s = Locale.data["expire_month_text"];
						n = Number(printInfo["balance"]);
						s = s.replace('N', n);
						s = s.replace('{units}', Utils.plural(n, ['печать', 'печати', 'печатей']));
						alert(s);
						return;
					}*/
					
					/*s = s.replace("{numPrintList}", "<b>" + quantity + "</b>");
					s = s.replace("{postBalance}",  "<b>" + post_balance + "</b>");*/
					printInfo["balanceDecrement"] = price;// Number(printInfo["oneListPrice"]) * quantity;
					printInfo["quantity"] = quantity;
					s = "Вы уверены, что хотите распечатать файл?";
					
					showConfirm(s, quantity, price);
				} else {
					var _pagesNumber:Array = getSelectedPagesNumber();
					_pagesNumber = _setLimitOnPagesNumber(_pagesNumber);
					printInfo["fileIdForPdf"] = fileID;
					
					if (!_is_expire_time_wait && printInfo["oneListPrice"] > 0) {
						pdfCreator.storeCopy(dssLoader.parsedData, _pagesNumber, (formView.showGridTrue.currentFrame == 1), fileName, printInfo);
					}
					
					if (!sendWriteStatAction) {
					    var sender:CResponder = new CResponder("action=f_write_statistics&file_id=" + fileID + "&q=" + quantity);
					    sender.addEventListener(ResponderEvent.LOAD_COMPLETE, onSendUserAccept); 
						sendWriteStatAction = true;
					}
				}
		}
		//-------------------------------
		/**
		 * @desc Возвращает количество листов AN  в зависмотси от установленного LIST_PER_PRINT
		 * @param formatStr - A0 - A4
		 * @return quantity - количество листов AN
		*/
		private function _getLimitWithFormat(formatStr) {
			var multiplier = 1;
			switch (formatStr) {
				case 'A5':
					multiplier = 0.5;
				case 'A4':
					multiplier = 1;
					break;
				case 'A4A':
					multiplier = 1;
					break;
				case 'A3':
					multiplier = 2;
					break;
				case 'A2':
					multiplier = 4;
					break;
				case 'A1':
					multiplier = 8;
					break;
				case 'A0':
					multiplier = 16;
					break;
			}
			var lim = self::LIST_PER_PRINT;
			if (printInfo['balance']  > 0 && printInfo['oneListPrice'] > 0) {
				lim *= printInfo['balance'];
			}
			return Math.floor(lim / multiplier); //TODO floor
		}
		
		/**
		 * @desc Возвращает количество листов A4  в засисмотси от формата печати
		 * @param quantity - количество листов
		 * @return quantity - количество листов A4
		*/
		private function getA4Quantity(quantity) {
			var multiplier = 1;
			switch (PrintManager.pageFormat) {
				case 'A5':
					multiplier = 0.5;
				case 'A4':
					multiplier = 1;
					break;
				case 'A4A':
					multiplier = 1;
					break;
				case 'A3':
					multiplier = 2;
					break;
				case 'A2':
					multiplier = 4;
					break;
				case 'A1':
					multiplier = 8;
					break;
				case 'A0':
					multiplier = 16;
					break;
			}
			return (quantity * multiplier);
		}
		private function onSendUserAccept(e:ResponderEvent)
		{
			sendWriteStatAction = false;
			confirmDlg.visible = false;
			var response = decodeResponseData(e.info);
			//ExternalInterface.call("alert", 'r.b = ' + response["balance"]);
			if (response["balance"] != undefined) {
				setText(formView.text_info_clip.balance_text, response["balance"]);
				ExternalInterface.call("setBalanceText", response["balance"]);
				//ExternalInterface.call("alert", 'r.b = ' + response["balance"]);
				printInfo["balance"] = response["balance"];
			}
			if (response["login"] != undefined) {
				printInfo["login"] = response["login"];
			}
			if (response["date"] != undefined) {
				printInfo["date"] = response["date"];
			}
			var pagesNumber:Array = getSelectedPagesNumber();
			pagesNumber = _setLimitOnPagesNumber(pagesNumber);
			PrintManager.__print(pagesNumber);
			confirmDialogShadow.visible = false;
			//this.addEventListener(MouseEvent.MOUSE_MOVE, 		mouseMoveEvent);
			onCancelBtnClick(new MouseEvent(MouseEvent.CLICK));
		}
		/**
		 * @return Array : ключи  - номера страниц, которые надо распечатать, со значением 1
		 * может содержать строку "ALL" в нулевом элементе, это говорит о том, что надо печатать все  листы
		*/
		private function getSelectedPagesNumber() :Array
		{
			var pagesNumber = new Array();
			pagesNumber.push("ALL");
			if (formView.allPagesRb.currentFrame == 2){
				pagesNumber = new Array();
				pagesNumber = createCustomPagesArray();
			}
			return pagesNumber;
		}
		/**
		 * @return Array : ключи  - номера страниц, которые надо распечатать, со значением 1
		 * может содержать строку "ALL" в нулевом элементе, это говорит о том, что надо печатать все листы
		*/
		private function _setLimitOnPagesNumber(pagesNumber:Array, dbg = false) :Array {
			var printedQuantity = _getQuantityPrintedPages(pagesNumber);
			//Проверить, не превышает ли количество страниц идущих в печать лимит
			//если не превышает вернуть аргумент
			var limit_with_format = _getLimitWithFormat(PrintManager.pageFormat);
			if (printedQuantity <= limit_with_format) {
				if (dbg) {
					throw new Error("pQ = " + printedQuantity + ", lwf = " + limit_with_format);
				}
				return pagesNumber;
			}
			//если превышает
			var result:Array;
			if (pagesNumber[0] == "ALL") {
				//создать массив от  1 до лимит включительно, 
				//затереть ALL, 
				var sz = (limit_with_format + 1 > pagesNumber.length ? limit_with_format + 1 : pagesNumber.length);
				result = new Array(sz);
				for (var i = 1; i <=  limit_with_format; i++ ) {
					result[i] = 1;
				}
				result[0] = '';
				if (dbg) {
					throw new Error("wtf 1");
				}
				return result;
			} else {
				//иначе 
				//пройти пао всем элементам, как только количество значений "1" превысит лимит ставить в 0 все остальные такие значения
				var c = 0;
				result = new Array(1);
				result[0] = '';
				for (var j = 1; j < pagesNumber.length; j++ ) {
					if (pagesNumber[j] == 1) {
						result[j] = 1;
						c++;
					}
					if (pagesNumber[j] == 1 && c > limit_with_format) {
						result[j] = 0;
					}
				}
				if (dbg) {
					throw new Error("wtf 2");
				}
				return result;
			}
		}
		
		//-------------------------------
		private function createCustomPagesArray(setTextInLine = true)
		{
			var allow = "1234567890,-";
			var text:String = formView.pagesLine.text;
			if (text == "все") 
			{
				var pagesNumber = new Array();
				pagesNumber.push("ALL");
				return pagesNumber;
			}
			var r:String = "";
			for (var i = 0; i < text.length; i++ )
			{
				if (allow.indexOf(text.charAt(i)) != -1) r += text.charAt(i);
			}
			var re = /^\-/;
			text = r.replace(re, ""); 
			re = /^\,/;
			while (text.indexOf("--") != -1) text = text.replace("--", "-");
			while (text.indexOf(",,") != -1) text = text.replace(",,", ",");
			text = text.replace(re, ""); 
			var copyText:String = text;
			re = /\,$/;
			text = text.replace(re, ""); 
			re = /\-$/;
			text = text.replace(re, ""); 
			if (setTextInLine) formView.pagesLine.text = text;
			else formView.pagesLine.text = copyText;
			var res:Array = new Array();
			var nTest:Number = Number(text);
			if (String(nTest) != "NaN")
			{
				res[nTest] = 1;
				return res;
			}
			if ((text.indexOf(",") != -1)&&(text != ","))
			{
				var arr = text.split(",");
				for (i = 0; i < arr.length; i++ )
				{
					var sKey = String (arr[i]);
					if ((sKey.indexOf("-") != -1)&&(text != "-"))
					{
						var arr2 = sKey.split("-");
						var min = Number(arr2[0]);
						var max = Number(arr2[1]);
						if ((String(min) != "NaN") && (String(max) != "NaN"))
						{
							for (var j = min; j <= max; j++ )
							{
								res[j] = 1;
							}
						}
					}
					var key = Number(arr[i]);
					if (String(key) != "NaN") res[key] = 1;
				}
			}else
			if ((text.indexOf("-") != -1)&&(text != "-"))
			{
				arr2 = text.split("-");
				min = Number(arr2[0]);
				max = Number(arr2[1]);
				if ((String(min) != "NaN") && (String(max) != "NaN"))
				{
					for (j = min; j <= max; j++ )
					{
						res[j] = 1;
					}
				}
			}
			return res;
		}
		//-------------------------------
		private function onPrintBtnRollOver(e:MouseEvent)
		{
			formView.printBtnBg.gotoAndStop(2);
		}
		//-------------------------------
		private function onPrintBtnRollOut(e:MouseEvent)
		{
			formView.printBtnBg.gotoAndStop(1);
		}
		//--------------------------------
		private function createShadow()
		{
			formViewShadow = new Sprite();
			formViewShadow.x = this.viewX;
			formViewShadow.y = this.viewY;
			formViewShadow.graphics.beginFill(0x000000, 0);
			formViewShadow.graphics.drawRect(0, 0, viewW, viewH);
			formViewShadow.graphics.endFill();
			formView.layer2.addChild(formViewShadow);
		}
		//-----------------------------------
		private function onShowGridClick(e:MouseEvent)
		{
			if (formView.showGridTrue.currentFrame == 1)
				hideGrid();
			else showGrid();	
		}
		//-----------------------------------
		private function hideGrid()
		{
			formView.showGridTrue.gotoAndStop(2);
			var items = PrintManager.frames.getAllSprites();
			var items2 = PrintManager.framesDisplay.getAllSprites();
			for (var i = 0; i < items.length; i++ )
			{
				var dObj = items[i].getChildByName("gridSprite");
				var grid:Sprite = dObj as Sprite;
				grid.visible = false;
				
				var dObj2 = items2[i].getChildByName("gridSprite");
				var grid2:Sprite = dObj2 as Sprite;
				grid2.visible = false;
			}
		}
		//-----------------------------------
		private function showGrid()
		{
			formView.showGridTrue.gotoAndStop(1);
			var items = PrintManager.frames.getAllSprites();
			var items2 = PrintManager.framesDisplay.getAllSprites();
			for (var i = 0; i < items.length; i++ )
			{
				var dObj = items[i].getChildByName("gridSprite");
				var grid:Sprite = dObj as Sprite;
				grid.visible = true;
				
				var dObj2 = items2[i].getChildByName("gridSprite");
				var grid2:Sprite = dObj2 as Sprite;
				grid2.visible = true;
			}
		}
		//---------------------------------------
		private function onAllPagesRbClick(e:MouseEvent)
		{
			if (formView.allPagesRb.currentFrame == 2)
			{
				formView.allPagesRb.gotoAndStop(1);
				formView.customPagesRb.gotoAndStop(2);
				//formView.pagesLine.enabled = false;
				formView.pagesLineDisabled.visible = true;
				formView.pagesLine.text = "все";
				if(eng_locale)  formView.pagesLine.text = "all";
				onTextChange(new KeyboardEvent(""));
				formView.pagesLine.text = "все";
				if(eng_locale)  formView.pagesLine.text = "all";
			}
		}
		//---------------------------------------
		private function onCustomPagesRbClick(e:MouseEvent)
		{
			if (formView.customPagesRb.currentFrame == 2)
			{
				formView.customPagesRb.gotoAndStop(1);
				formView.allPagesRb.gotoAndStop(2);
				//formView.pagesLine.enabled = true;
				formView.pagesLineDisabled.visible = false;
				formView.pagesLine.text = "";
				onTextChange(new KeyboardEvent(""));
				formView.pagesLine.text = "";
				stage.focus = formView.pagesLine;
			}
		}
		//--------------------------------------------
		private function onCancelBtnRollOver(e:MouseEvent)
		{
			formView.cancelBtnBg.gotoAndStop(2);
		}
		//--------------------------------------------
		private function onCancelBtnRollOut(e:MouseEvent)
		{
			formView.cancelBtnBg.gotoAndStop(1);
		}
		//--------------------------------------------
		/**
		 * @desc Закрытие формы параметров печати
		 * @param e {MouseEvent}
		 * @param closedAsTimeWait {Boolean}
		 * @param fileName {String}
		*/
		public function onCancelBtnClick(e:MouseEvent, closedAsTimeWait:Boolean = false, fileName:String = '') {
			//formView.visible = false;
			handCursor.removeEventListener(MouseEvent.MOUSE_DOWN,  mouseDownEvent);
			handCursor.removeEventListener(MouseEvent.MOUSE_UP,    mouseUpEvent);
			handCursor.removeEventListener(MouseEvent.MOUSE_WHEEL, onMouseWheel);
			this.removeEventListener(MouseEvent.MOUSE_MOVE, 	   mouseMoveEvent);
			
			/*TODO не забыть перенести куда слежует, или убедиться, что это не надо! if (accountShadow) {
				accountShadow.hide();
				accountShadow.btnCancel.removeEventListener(MouseEvent.CLICK, onCancelBtnClick);
				accountShadow.btnCancel.removeEventListener(DSSAccountShadow.DSS_ACCOUNT_SHADOW_SUCCESS_HIDE, onTimeWait);
			}*/
			
			if (closedAsTimeWait) {//TODO ыот с этим разобраться
				this.dispatchEvent(new PrintFormEvent(PrintFormEvent.CLOSE, fileName));
			} else {
				this.dispatchEvent(new PrintFormEvent(PrintFormEvent.CLOSE));
			}
			
			this.removeChild(formView);
		}

		private function _processUserData() {
			formView.wait_screen.visible = false;
			if (Number(printInfo["oneListPrice"]) > 0) {
				_setPrintInfoView(PrintManager.frames.getAllSprites().length);
			} else {
				_setPrintInfoViewOnFreePrint(PrintManager.frames.getAllSprites().length);
			}
			formView.pagesLine.text = "все";
			if (eng_locale)  formView.pagesLine.text = "all";
			_log("bef otc");
			onTextChange(new KeyboardEvent(""));
			formView.pagesLine.text = "все";
			if(eng_locale)  formView.pagesLine.text = "all";
		}
		
		/**
		 * @desc Информационное сообщение при бесплатной печати
		 * @param quantity - количество листов которые будут распечатаны
		*/
		private function _setPrintInfoViewOnFreePrint(quantity) {
			formView.text_info_clip.visible = false;
			setText(formView.text_info_stable.txt_total_lists, PrintManager.frames.getAllSprites().length + ' ' + Utils.plural(PrintManager.frames.getAllSprites().length, ['лист', 'листа', 'листов'] ) + ' ' + PrintManager.pageFormat);
			var quantityA4 = getA4Quantity(quantity);
			var limit_with_format = _getLimitWithFormat(PrintManager.pageFormat);
			quantity = (quantity < limit_with_format ? quantity : limit_with_format);
			setText(formView.text_info_stable.txt_printed_lists, quantity + ' ' + Utils.plural(quantity, ['лист', 'листа', 'листов'] ) + ' ' + PrintManager.pageFormat);
			formView.text_info_stable.visible = true;
			if (quantityA4 > self::LIST_PER_PRINT) {
				setText(formView.mc_maximum_text_info.txt_limit_one_print, self::LIST_PER_PRINT + ' ' + Utils.plural(self::LIST_PER_PRINT, ['лист', 'листа', 'листов'] ) );
				setText(formView.mc_maximum_text_info.txt_current_format, 'A4'/*PrintManager.pageFormat*/);
				formView.mc_maximum_text_info.visible = true;
			}
		}
		/**
		 * @desc Устанавливает данные о текущем и грядущнм балансе, колве листов в печать
		 * @param quantity - количество листов которые будут распечатаны
		*/
		private function _setPrintInfoView(quantity) {
			setText(formView.text_info_stable.txt_total_lists, PrintManager.frames.getAllSprites().length + ' ' + Utils.plural(PrintManager.frames.getAllSprites().length, ['лист', 'листа', 'листов'] ) + ' ' + PrintManager.pageFormat);
			var quantityA4 = getA4Quantity(quantity);
			var limit_with_format = _getLimitWithFormat(PrintManager.pageFormat);
			quantity = (quantity < limit_with_format ? quantity : limit_with_format);
			setText(formView.text_info_stable.txt_printed_lists, quantity + ' ' + Utils.plural(quantity, ['лист', 'листа', 'листов'] ) + ' ' + PrintManager.pageFormat);
			formView.text_info_stable.visible = true;
			
			this.setText(formView.text_info_clip.balance_text, String(printInfo["balance"]) + ' <font color="#FF0000">R</font>edcoin', false);
			
			var oneListPrice = Number(printInfo["oneListPrice"]);
			var n = getA4Quantity(quantity);
			var price = 0;
			
			while (n > 0) { n -= self::LIST_PER_PRINT; price += oneListPrice; }
			_setPriceText(formView.text_info_clip.price_text, String(price));
			var post_balance = Number(printInfo["balance"]) - price;//Number(printInfo["oneListPrice"]) * quantity;
			//this.setText(formView.text_info_clip.quantity_list_text, String(quantity) + " шт.");
			this.setText(formView.text_info_clip.post_balance_text, String(post_balance) + ' <font color="#ff0000">R</font>edcoin', false );
			formView.text_info_clip.visible = true;
			printInfo["postBalance"] = post_balance;
		}
		/**
		 * Подсветка R в стоимости
		*/
		private function _setPriceText(t:TextField, s:String) {
			t.htmlText = '<b>' + s + '</b><font color="#ff0000">R</font> (' + s + ' <font color="#ff0000">R</font>edcoin)';
		}
		/**
		 * Устанавливает текст в текстовое поле, добавляет многоточие
		*/
		private function _setFilenameInPrintInfo(t:TextField, s:String) {
			var tf:TextFormat = t.getTextFormat();
			t.text = s;
			t.setTextFormat(tf);
			var arr:Array = s.split(/\s/);
			var L = arr.length - 2;
			
			while (t.textWidth > t.width) {
				arr = arr.slice(0, L);
				t.text = arr.join(' ') + '...';
				t.setTextFormat(tf);
				L--;
			}
		}
		private function decodeResponseData(s:String)
		{
			var sI = String(s);
			var arr = sI.split("&");
			var res:Array = new Array();
			for (var i = 0; i < arr.length; i++ )
			{
				var arr2 = arr[i].split("=");
				res[arr2[0]] = arr2[1];
			}
			return res;
		}
		
		private function setText(tfield:TextField, value:String, setTextFormat:Boolean = true)
		{
			if (setTextFormat)
			{
				var tf:TextFormat = tfield.getTextFormat();
				tfield.text = value;
				tfield.setTextFormat(tf);
			}
			else tfield.htmlText = value;
		}
		
		private function alert(s)
		{
			var tf:TextFormat = dlg.message_txt.getTextFormat();
			dlg.message_txt.text = s;
			dlg.message_txt.setTextFormat(tf);
			dlg.visible = true;
		}
		
		private function hideDlg(e:MouseEvent)
		{
			dlg.visible = false;
		}
		
		private function createConfirmDlg()
		{
			confirmDialogShadow = new Sprite();
			confirmDialogShadow.graphics.beginFill(0x000000, 0.5);
			confirmDialogShadow.graphics.drawRect(0, 0, formView.width, formView.height);
			confirmDialogShadow.graphics.endFill();
			confirmDialogShadow.visible = false;
			this.addChild(confirmDialogShadow);
			
			confirmDlg = new DSSConfirmPrintDialog();
			this.addChild(confirmDlg);
			confirmDlg.x = (770 - confirmDlg.width) / 2 + 140;
			confirmDlg.y = (460 - confirmDlg.height) / 2 ;
			confirmDlg.visible = false;
			//confirmDlg.message_txt.html = true;
			var tf = confirmDlg.message_txt.getTextFormat();
			tf.align = TextFormatAlign.LEFT;
			confirmDlg.ok_btn.addEventListener(MouseEvent.CLICK, sendPrintData);
			confirmDlg.cancel_btn.addEventListener(MouseEvent.CLICK, hideConfirmDlg);
			
			confirmDlg.title_txt.selectable = false;
			confirmDlg.message_txt.selectable = false;
			confirmDlg.message_txt.visible = false;
		}
		
		private function sendPrintData(e:MouseEvent) {
			confirmDlg.ok_btn.removeEventListener(MouseEvent.CLICK, sendPrintData);
			var pagesNumber:Array = getSelectedPagesNumber();
			pagesNumber = _setLimitOnPagesNumber(pagesNumber);
			printInfo["fileIdForPdf"] = fileID;
			if (!_is_expire_time_wait && printInfo["oneListPrice"] > 0) {
				pdfCreator.storeCopy(dssLoader.parsedData, pagesNumber, (formView.showGridTrue.currentFrame == 1), fileName, printInfo);
			}
			
			//TODO по получении выполнить код ниже
			var decr = printInfo["balanceDecrement"];
			if (_is_expire_time_wait) {
				decr = 0;
			}
			var vars = "action=f_print_plane&balance_decrement=" + decr + "&file_name=" + fileName + "&file_id=" + fileID + "&q=" + printInfo["quantity"];
			var resp = new CResponder(vars);
			resp.addEventListener(ResponderEvent.LOAD_COMPLETE, onSendUserAccept);
		}
		/**
		 * @desc Listeners определены в createConfirmDlg
		 * @param s:String
		 * @param quantity:Number
		 * @param price:Number
		*/
		private function showConfirm(s:String, quantity:Number, price:Number) {
			Mouse.show();
			this.removeEventListener(MouseEvent.MOUSE_MOVE, mouseMoveEvent);
			confirmDialogShadow.visible = true;
			//setText(confirmDlg.message_txt, s, false);
			_setFilenameInPrintInfo(confirmDlg.filename_text, fileName);
			setText(confirmDlg.quantity_text, String(quantity));
			
			if (Number(printInfo["is_unlim"]) == 1) {
				var activeMessage = 'Активация 24 до\n' + printInfo["unlim_expire"];
				setText(confirmDlg.price_text, activeMessage);
			} else {
				_setPriceText(confirmDlg.price_text, String(price));
			}
			confirmDlg.visible = true;
		}
		
		private function hideConfirmDlg(e:MouseEvent)
		{
			this.addEventListener(MouseEvent.MOUSE_MOVE, 		mouseMoveEvent);
			confirmDlg.visible          = false;
			confirmDialogShadow.visible = false;
		}
		/**
		 * @desc Ищет переменную listPerPrint в flashVars
		*/
		private function _setListPerPrint() {
			var n:Number = parseInt(this.flashVars.listPerPrint);
			n = n ? n : self::LIST_PER_PRINT;
			self::LIST_PER_PRINT = n;
		}
		/**
		 * @desc Ищет переменную maxRecoveryLink в flashVars
		*/
		private function _setMaxRecoveryLink() {
			var s:String = this.flashVars.maxRecoveryLink;
			s = s ? s : self::MAX_RECOVERY_HELP_LINK;
			self::MAX_RECOVERY_HELP_LINK = s;
			formView.mc_maximum_text_info.btn_recovery_help.addEventListener(MouseEvent.CLICK, onRecoveryMaxClick);
		}
		/**
		 * @desc Клик на ссылке "Как это исправить см в разделе помощь" при количестве листов более чем лимит
		*/
		private function onRecoveryMaxClick(evt:MouseEvent) {
			navigateToURL( new URLRequest(self::MAX_RECOVERY_HELP_LINK), "_blank" );
		}
		/**
		 * @desc console.log
		*/
		private function _log(s, alert = false) {
			var cmd = "console.log";
			if (alert) {
				cmd = "alert";
			}
			//ExternalInterface.call(cmd, s);
		}
	}//end class
