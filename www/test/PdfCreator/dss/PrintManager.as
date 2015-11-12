package drake.dss
{
	import flash.display.*
	import flash.display.*
	import flash.printing.*
	import flash.text.*
	import flash.external.ExternalInterface; //debug
	import flash.filters.GlowFilter;
	import flash.filters.BitmapFilterQuality;
	public class PrintManager
	{
		static private var pj:PrintJob;
		static public  var flashVars:Array;	//flashVars 
		static public  var frames:SpriteMap;	//спрайты для распчатки 
		static public  var framesDisplay:SpriteMap;	//спрайты для отображения 
		static public  var firstLandscape:Boolean = false;	// принимает значение true когда первый раз в свойствах печати выбрано landscape
		//Переменные использующиеся при генерации pdf
		static public  var pageOrientation:String = "portrait";	// принимает значение true когда твердо определились с landscape
		static public  var pageSize:Object;	   //{w,h} размеры страницы в мм
		static public  var pageFormat:String;	   //string value of format page
		
		static public function preparePrint(o:Object, _sprite:Sprite, grid:Boolean/*, _pj:PrintJob*/)
		{
			//pj = _pj;
			pj = new PrintJob();
			if (pj.start())
			{
				var listOrientation:String = pj.orientation;
				var dumpObj = {pageW:pj.pageWidth, paperW:pj.paperWidth, pageH:pj.pageHeight, paperH:pj.paperHeight };
				var format = PrintHelper.getFormat(pj);	//получили формат бумаги А1, А2, А3 и т д	
				PrintManager.pageFormat = format;
				var fSize = PrintHelper.getFormatSize(format);
				var w = fSize.x;//210;
				var h = fSize.y;//297;
				if (listOrientation == "landscape")
				{
					firstLandscape = true;
					var b = w;
					w = h;
					h = b;				
				} else {
					firstLandscape = false;
				}
				PrintManager.pageOrientation = listOrientation;
				PrintManager.pageSize = {w:w, h:h};
				var dO = PrintHelper.getDelta(o.lines);
				var dx = dO.dx;
				var dy = dO.dy;
				var map = new SpriteMap();
				var mapView = new SpriteMap();
				var i = 0; 
				while (i < o.lines.length)
				{
				 //PrintHelper.getPages возвращает массив номеров страниц. Номер страницы представляет собой строку "N:M"
				 //	где  N - номер строки прямоугольной таблицы, M - номер столбца
				 var tr = false;
				 if (Math.round(o.lines[i].endX) == 1138) 
				 {
					 tr = true;
					 //trace("FIND (PM)");
				 }
				  var pages = PrintHelper.getPages(format, o.lines[i], listOrientation, dO);
				 //Рисуем все страницы
				 var j = 0;
				 while (j < pages.length)
				 {
				  //if (pages[j] == "1:5") trace ("pages[" + j + "] = "   + pages[j]);
				  var sprite = map.getSprite(pages[j]);
				  var sw     = mapView.getSprite(pages[j]);
				  //PrintHelper.getData возвращает объект с полями аналогичными o.lines[i],
				  //но пересчитанными в соответствии с форматом листа, его номером и его положением
				  var printData = PrintHelper.getData(format, o.lines[i], listOrientation, pages[j]);
				  if (!printData) { j++;  continue;}
				  sprite.graphics.lineStyle(1, printData.color); //это страницы
				  sprite.graphics.moveTo(toPix(printData.startX), toPix(printData.startY));
				  sprite.graphics.lineTo(toPix(printData.endX),   toPix(printData.endY));
				  
				  sw.graphics.lineStyle(3, printData.color); //это превью
				  sw.graphics.moveTo(toPix(printData.startX), toPix(printData.startY));
				  sw.graphics.lineTo(toPix(printData.endX), toPix(printData.endY));
				  j++;
				 }
				 i++;
				}
				
				var items:Array = map.getAllSprites();
				var itemsV:Array = mapView.getAllSprites();
				var view:Sprite = new Sprite();
				var keys:Array = map.getKeys();
				frames = new SpriteMap();
				framesDisplay = new SpriteMap();
				for (i = 0; i < keys.length; i++ ) 
				{
					var key = keys[i];
					var str = PrintHelper.toObject(key);
					var displayFrame:Sprite = framesDisplay.getSprite(keys[i]);
					displayFrame.x = PrintHelper.toObject(keys[i]).x * toPix(w) + str.x * 25 + 24;
					displayFrame.y = PrintHelper.toObject(keys[i]).y * toPix(h) + str.y * 25 + 24;
					
					if (grid) {
						PrintHelper.addWatermark = false;
						PrintHelper.drawGrid(displayFrame, toPix(w), toPix(h), toPix(10), 0xE0E0E0, "0:0");
					}
					var borderSprite:Sprite = new Sprite();
					borderSprite.graphics.lineStyle(3, 0xA0A0A0);

					borderSprite.graphics.drawRect(0, 0, toPix(w), toPix(h));
					displayFrame.addChild(borderSprite);
					
					displayFrame.addChild(itemsV[i]);
					view.addChild(displayFrame);
					
					var frame:Sprite = frames.getSprite(keys[i]);	
					frame.graphics.beginFill(0xFFFFFF);
					frame.graphics.drawRect(0, 0, toPix(w), toPix(h));
					frame.graphics.endFill();
					frame.graphics.lineStyle(0.25, 0x555555);
					//отрисовка рамки
					//горизонт.
					var padding = 0;					
					with (frame.graphics)
					{
						var start = toPix(PrintHelper.lMargin - padding);
						moveTo(start, toPix(h - PrintHelper.bMargin + padding));
						lineTo(toPix(w - PrintHelper.rMargin + padding), toPix(h - PrintHelper.bMargin + padding));
						moveTo(start, toPix(PrintHelper.tMargin - padding));
						lineTo(toPix(w - PrintHelper.rMargin + padding), toPix(PrintHelper.tMargin - padding));
						
						//corners
						lineStyle(0.25, 0xFF0000, 0, false, LineScaleMode.NORMAL, CapsStyle.SQUARE);
						var y = toPix(h - PrintHelper.bMargin + padding);
						beginFill(0x000000);
						drawRect(start, y, toPix(15), -2);
						endFill();
						beginFill(0x000000);
						drawRect(toPix(w - PrintHelper.rMargin + padding - 15), y, toPix(15), -2);
						endFill();
						
						y = toPix(PrintHelper.tMargin - padding) - 0.5;
						beginFill(0x000000);
						drawRect(start, y, toPix(15), 2);
						endFill();
						beginFill(0x000000);
						drawRect(toPix(w - PrintHelper.rMargin + padding - 15), y, toPix(15), 2);
						endFill();
					}
					//верт
					with (frame.graphics)
					{
						lineStyle(0.25, 0x555555, 1.0);
						start = toPix(PrintHelper.tMargin - padding);
						moveTo(toPix(w - PrintHelper.rMargin + padding), start);
						lineTo(toPix(w - PrintHelper.rMargin + padding), toPix(h - PrintHelper.bMargin + padding));
						moveTo(toPix(PrintHelper.lMargin - padding), start);
						lineTo(toPix(PrintHelper.lMargin - padding), toPix(h - PrintHelper.bMargin + padding));
						
						//corners
						lineStyle(0.25, 0xFF0000, 0, false, LineScaleMode.NORMAL, CapsStyle.SQUARE);
						var x = toPix(w - PrintHelper.rMargin + padding);
						beginFill(0x000000);
						drawRect(x, start ,  -2, toPix(15));
						endFill();
						
						beginFill(0x000000);
						drawRect(x, toPix(h - PrintHelper.bMargin + padding - 15) ,  -2, toPix(15));
						endFill();
						x = toPix(PrintHelper.lMargin - padding);
						beginFill(0x000000);
						drawRect(x, start , 2, toPix(15));
						endFill();
						beginFill(0x000000);
						drawRect(x, toPix(h - PrintHelper.bMargin + padding - 15), 2, toPix(15));
						endFill();
					}					
					
					var _w = w - (PrintHelper.rMargin);
					var _h = h - (PrintHelper.bMargin);
					var pixW = toPix(_w);
					var pixH = toPix(_h);
					
					
					if (grid) {
						PrintHelper.addWatermark = true;
						PrintHelper.drawGrid(frame, pixW, pixH, toPix(10), 0xA5A5A5, keys[i], _w, _h);
					}
					
					var text:TextField = new TextField();
					text.text = incIndexes(keys[i]);
					var tfor:TextFormat = text.getTextFormat();
					tfor.size += 2;
					text.setTextFormat(tfor);
					/*drawTopMarker(keys[i],   frame, keys);
					drawBtmMarker(keys[i],   frame, keys);
					drawLeftMarker(keys[i],  frame, keys);
					drawRightMarker(keys[i], frame, keys);*/
					items[i].x += toPix(PrintHelper.lMargin + padding);
					items[i].y += toPix(PrintHelper.tMargin + padding);
					frame.addChild(items[i]);
					//if (listOrientation == "landscape") frame.rotation += 90;
					frame.addChild(text);		
					//pj.addPage(frame);
				}
				//PrintHelper._trace("exit", true);
				var canvas:Sprite = new Sprite();
				canvas.addChild(view);
				
				view.x = 5;
				view.y = 5;
				var sc = _sprite.width / (canvas.width + 50);
				addGreenCheckBox(sc);
				canvas.width *= sc;
				canvas.height *= sc;
				setPagesNumber();
				var fon:Sprite = new Sprite();
				with (fon.graphics)
				{
					beginFill(0xF0F0F0);
					drawRect(0, 0, canvas.width + 70, canvas.height + 50);
					endFill();
				}
				_sprite.addChild(fon);
				_sprite.addChild(canvas);
				//var mmc23 = _sprite as MovieClip;
				//mmc23.shadow.height = canvas.height + 50;
				
				pj.send();				
				return dumpObj;
			}//end if pj.start
			return false;
		}
		
		static public function incIndexes(s:String)
		{
			var arr = s.split(":");
			var m = Number(arr[0]); m++;
			var n = Number(arr[1]); n++;
			s = String(m) + ":" + String(n);
			return s; 
		}
		
		static private  function setPagesNumber()
		{
			var pageInfo = new PageOrderHelper(framesDisplay);
			var items = pageInfo.getArray();
			var j:Number = 1;
			for (var i = 1; i < items.length; i++)
			{
				var key = items[i];
				if (key != "-1")
				{
					var sprite = framesDisplay.getSprite(key);
					var child = sprite.getChildAt(sprite.numChildren - 1);
					if (child.name != "pageNumber") 
					{
						createPageNumber(sprite, j);
						j++;
					}
				}
			}
		}
		
		static function createPageNumber(sprite:Sprite, value:Number)
		{
			var s:String  = String(value);
			var pageNumber:TextField = new TextField();
			var spr:Sprite = sprite.getChildAt(sprite.numChildren - 1) as Sprite;
			pageNumber.x      = spr.x - toPix(40 + (s.length - 1)*35);
			pageNumber.y      = spr.y - toPix(20);
			pageNumber.width  = toPix(60);
			pageNumber.height = toPix(60);
			pageNumber.text   = s;
			var tf:TextFormat = pageNumber.getTextFormat();
			tf.size = toPix(40);
			tf.font = "Verdana";
			//tf.bold = true;
			tf.color = 0xFFFFFF;
			pageNumber.setTextFormat(tf);
			 
			// Apply the glow filter to the cross shape. 
			var glow:GlowFilter = new GlowFilter(); 
			glow.color = 0x000000; 
			glow.strength = 1000;
			//glow.alpha = 1; 
			glow.blurX = 1.5; 
			glow.blurY = 1.5; 
			glow.quality = BitmapFilterQuality.HIGH;
			pageNumber.filters = [glow];
			sprite.addChild(pageNumber);
		}
		
		static private function addGreenCheckBox(sc:Number)
		{
			var sprites = framesDisplay.getAllSprites();
			if (sprites.length > 0)
			{
				var listW = sprites[0].width;
				var listH = sprites[0].height;
				var widthSc = 13.9 / 104.2; 	//требуемое соотношение ширины галки и листа
				var heightSc = 16 / 147.95; 	//требуемое соотношение ширины галки и листа
				var greenBoxW = listW * widthSc;
				var greenBoxH = listH * heightSc;
				
				var crossHSc = 11.2 / 147.95;
				var crossWSc = 13.2 / 104.2;
				var divider = 20;
				
				for (var i = 0; i < sprites.length; i++ )
				{
					var fail = new GrFail();
					fail.name = "redCross";
					fail.width  = listW * crossWSc;
					fail.height = listH * crossHSc;
					fail.x = listW  - fail.width - (listW / divider);
					fail.y = listH  - fail.height - (listH / divider);
					sprites[i].addChild(fail);
					
					var okGr = new GrOK();
					okGr.name = "greenAllow";
					okGr.width  = greenBoxW;
					okGr.height = greenBoxH;
					okGr.x = listW  - okGr.width - (listW / divider)		//3*listW / 4;
					okGr.y = listH  - fail.height - (listH / divider); 		// 3 * listH / 4;
					sprites[i].addChild(okGr);
				}
			}			
		}
		
		static public function __print(pagesNumber)
		{
			try
			{
				printPages(pagesNumber);
			}catch (err)
			{
				trace (err);
				pj =  null;
				pj =  new PrintJob();
				if (pj.start())
				{
					printPages(pagesNumber);
				}
			}
		}
		
		static private function printPages(pagesNumber)
		{
			var pageInfo = new PageOrderHelper(frames);
			var items = pageInfo.getArray();
			for (var i = 1; i < items.length; i++ )
			{
				var key = items[i];
				var frame:Sprite = frames.getSprite(key);
				if (pagesNumber[0] == "ALL") 
				{
					if (pj.orientation == "portrait" && firstLandscape == true) {
						frame.rotation += 90;
						setHalfLandscapeMarkers(frame, key, items);
					} else if (pj.orientation) { //иначе при повороте все плывет куда-то, устранить удалось н все равно маркеры не выводились
						drawTopMarker(key,   frame, items);
						drawBtmMarker(key,   frame, items);
						drawLeftMarker(key,  frame, items);
						drawRightMarker(key, frame, items);
					}
					if (key != "-1") pj.addPage(frame);
				}else 
				if ((key != "-1") && (pagesNumber[i] == 1)) {
					if (pj.orientation == "portrait" && firstLandscape == true) {
						frame.rotation += 90;
						setHalfLandscapeMarkers(frame, key, items);
					} else if (pj.orientation) { //иначе при повороте все плывет куда-то, устранить удалось н все равно маркеры не выводились
						drawTopMarker(key,   frame, items);
						drawBtmMarker(key,   frame, items);
						drawLeftMarker(key,  frame, items);
						drawRightMarker(key, frame, items);
					}
					pj.addPage(frame);
				}
			}
			pj.send();
		}
		
		static private function setHalfLandscapeMarkers(frame:Sprite, key:String, items:Array) {
			//page number
			var s:String  = incIndexes(key);
			var pNsPr:Sprite = new Sprite();
			strToBitmap(pNsPr, s);
			pNsPr.x = 10;
			frame.addChild(pNsPr);
			//right
			var id = toObj(key);
			s = Number(id.m - 1) + ":" + id.n;
			if (IDExist(items, s))
			{
				setVerticalArrowEx(s, frame, toPix(5 + PrintHelper.rMargin), 0);
			}
			//left
			id = toObj(key);
			s = Number(id.m + 1) + ":" + id.n;
			if (IDExist(items, s))
			{
				setVerticalArrowEx(s, frame, frame.width - toPix(5 + PrintHelper.lMargin), 180);
			}
			//top
			id = toObj(key);
			s = id.m + ":" + Number(id.n - 1);
			if (IDExist(items, s))
			{
				setHorizontalArrowEx(s, frame, toPix(5 + PrintHelper.tMargin), -90);
			}
			//bottom
			id = toObj(key);
			s = id.m + ":" + Number(id.n + 1);
			if (IDExist(items, s))
			{
				setHorizontalArrowEx(s, frame, frame.height - toPix(17 + PrintHelper.bMargin), 90);
			}
		}
		
		static public function IDExist(keys:Array, ID)
		{
			for (var i = 0; i < keys.length; i++ )
			{
				if (keys[i] == ID) return true;
			}
			return false;
		}
		
		static public function toObj(s:String)
		{
			var pair = s.split(":");;
			var m = Number(pair[0]);
			var n = Number(pair[1]);
			var ret = { m:m, n:n };
			return ret;
		}
		
		static public function  setVerticalArrow(s, sprite, y, degree)
		{
				var tfl:TextField = new TextField();
				tfl.text = incIndexes(s);
				var tf:TextFormat = new TextFormat();
				tf.size = 12;
				/*if (degree == 0) tf.color = 0x0000aa;
				 else tf.color = 0xaa0000;*/
				tfl.setTextFormat(tf);
				var spr:Sprite = new Sprite();
				var arrow = new Arrow();
				arrow.rotation += degree;
				if (degree != 0) arrow.y += arrow.height;
				spr.addChild(arrow);
				tfl.x += arrow.x + arrow.width;
				tfl.y -= 5;
				//if (degree != 0) tfl.y += 10;
				spr.addChild(tfl);
				spr.x = (sprite.width /*- spr.width*/) / 2;
				spr.y = y;
				sprite.addChild(spr);
				return spr;
		}
		
		
		static public function  setVerticalArrowEx(s, sprite, y, degree)
		{
				s = incIndexes(s);
				var tfl:Sprite = new Sprite();
				strToBitmap(tfl, s);
				var spr:Sprite = new Sprite();
				var arrow = new Arrow();
				arrow.rotation += degree;
				if (degree != 0) arrow.y += arrow.height;
				spr.addChild(arrow);
				tfl.x += arrow.x + arrow.width;
				tfl.y -= 5;
				//if (degree != 0) tfl.y += 10;
				spr.addChild(tfl);
				spr.x = (sprite.height /*- spr.width*/) / 2;
				spr.y = y;
				sprite.addChild(spr);
				return spr;
		}
		
		
		static private function  strToBitmap(sprite:Sprite, s:String) {
			var offset = 0;
			for (var i = 0; i < s.length; i++ ) {
				var sp = null;
				switch (s.charAt(i)) {
					case "0":
						sp = new f0();
						break;
					case "1":
						sp = new f1();
						break;
					case "2":
						sp = new f2();
						break;
					case "3":
						sp = new f3();
						break;
					case "4":
						sp = new f4();
						break;
					case "5":
						sp = new f5();
						break;
					case "6":
						sp = new f6();
						break;
					case "7":
						sp = new f7();
						break;
					case "8":
						sp = new f8();
						break;
					case "9":
						sp = new f9();
						break;
					case ":":
						sp = new fw();
						break;
				}
				if (sp != null) {
					sp.x = offset;
					sprite.addChild(sp);
					offset += sp.width + 2;
					sp = null;
				}
			}
		}
		
		static public function  setHorizontalArrowEx(s, sprite, x, degree)
		{
				s = incIndexes(s);
				var tfl:Sprite = new Sprite();
				strToBitmap(tfl, s);
				var spr:Sprite = new Sprite();
				var arrow = new Arrow();
				arrow.rotation += degree;
				if (degree > 0) arrow.x += arrow.width*2;
				spr.addChild(arrow);
				tfl.y += arrow.y + arrow.height - 3;
				spr.addChild(tfl);
				spr.x = x;
				spr.y = (sprite.width - spr.height) / 2;
				sprite.addChild(spr);
				return spr;
		}
		
		static public function  setHorizontalArrow(s, sprite, x, degree)
		{
				var tfl:TextField = new TextField();
				tfl.text = incIndexes(s);
				var tf:TextFormat = new TextFormat();
				tf.size = 12;
				/*if (degree == 90) tf.color = 0x00aa00;
				 else tf.color = 0xaaaa00;*/
				tfl.setTextFormat(tf);
				var spr:Sprite = new Sprite();
				var arrow = new Arrow();
				
				arrow.rotation += degree;
				if (degree > 0) arrow.x += arrow.width*2;
				spr.addChild(arrow);
				tfl.y += arrow.y + arrow.height - 3;
				spr.addChild(tfl);
				spr.x = x;
				spr.y = (sprite.height - spr.height) / 2;
				sprite.addChild(spr);
				return spr;
		}
		
		static private function drawTopMarker(ID, sprite, keys:Array)
		{
			var id = toObj(ID);
			var s:String = id.m - 1 + ":" + id.n;
			if (IDExist(keys, s))
			{
				setVerticalArrow(s, sprite, toPix(5 + PrintHelper.tMargin), 0);
			}
		}
		
		static private function drawBtmMarker(ID, sprite, keys:Array)
		{
			var id = toObj(ID);
			var s:String = id.m + 1 + ":" + id.n;
			if (IDExist(keys, s))
			{
				setVerticalArrow(s, sprite, sprite.height - toPix(5 + PrintHelper.bMargin), 180);
			}
		}
		
		static private function drawLeftMarker(ID, sprite, keys:Array)
		{
			var id = toObj(ID);
			var s:String = id.m + ":" + Number(id.n - 1);
			if (IDExist(keys, s))
			{
				setHorizontalArrow(s, sprite, toPix(5 + PrintHelper.lMargin), -90);
			}
		}
		
		static private function drawRightMarker(ID, sprite, keys:Array)
		{ 
			var id = toObj(ID);
			var s:String = id.m + ":" + Number(id.n + 1);
			if (IDExist(keys, s))
			{
				setHorizontalArrow(s, sprite, sprite.width - toPix(7 + PrintHelper.rMargin), 90);
			}
		}
		
		static public function toMm(pix:Number):Number
		{
			return (pix*20)/56.7;
		}

		static public function toPix(mm:Number):Number
		{
			return (mm*56.7)/20;
		}
		
		static public function getPrintJob():PrintJob
		{
			return PrintManager.pj;
		}
		static public function getQuantityPages(o) {
			PrintHelper.LANDSCAPE = "landscape";
			PrintHelper.PORTRAIT  = "portrait";  
			var format = PrintHelper.getFormat({pageWidth:toPix(210), paperWidth:toPix(210), pageHeight:toPix(297), paperHeight:toPix(297), orientation : 'portrait'});
			var dObj = PrintHelper.getDelta(o.lines);
			var listOrientation = PrintHelper.PORTRAIT;
			var pagesCounterObj = { };
			var count = 0;
			var i = 0;
			while (i < o.lines.length) {
				//PrintHelper.getPages возвращает массив номеров страниц. Номер страницы представляет собой строку "N:M"
				//	где  N - номер строки прямоугольной таблицы, M - номер столбца
				var pages = PrintHelper.getPages(format, o.lines[i], listOrientation, dObj);
				//Рисуем все страницы
				var j = 0;
				while (j < pages.length) {
					if (!pagesCounterObj[ pages[j] ]) {
						count++;
						pagesCounterObj[ pages[j] ] = 1;
					}
					j++;
				}
				i++;
			}
			return count;
		}
	}
}