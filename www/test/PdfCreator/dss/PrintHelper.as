package drake.dss
{
	import flash.display.Graphics;
	import flash.display.Sprite;
	import flash.display.Stage;
	
	import flash.printing.PrintJobOptions;
	import flash.external.ExternalInterface;
	import flash.text.TextField;
	import flash.text.TextFormat;
	import flash.text.Font;
	import flash.text.TextFieldAutoSize;
	class PrintHelper
	{
	  static private var formats:MapPoint;
	  static public var MARGIN:Number = 12; //mm
	  static public var lMargin:Number;
	  static public var rMargin:Number;
	  static public var tMargin:Number;
	  static public var bMargin:Number;
	  static public var LANDSCAPE:String;
	  static public var PORTRAIT:String;
	  static public var MARKER_TEXT:String = "| Сделано в программе Redcafe | Redcafestore.com | Сделано в программе Redcafe | Redcafestore.com |";
	  static public var addWatermark:Boolean = false;
	  static public var trc:String = '';
	  static public var flashVars:Object;
		 public function PrintHelper() { }
		
		 static public function _trace(s, _throw = false )
		 {
			/*if (_throw)
			{
				var str = PrintHelper.trc + "\r\n" + s;
				//PrintHelper.trc = '';
				throw new Error(str);
			}
			else
			{
				PrintHelper.trc += "\r\n" + s;
			}*/
			//ExternalInterface.call("console.log", s);
		 }
		 static public function drawGrid(spr:Sprite, w:Number, h:Number, delta:Number, color:Number, pageID:String, mmW = 0, mmH = 0)
		 {
			 //_trace("pageID =  "  + pageID);
			 var arr = pageID.split(":");
			 var rowID = Number(arr[0]);
			 var colID = Number(arr[1]);
			 
			 if (addWatermark) {
				 var offset = toPix(rand(12, 58 ));
				 var watermarkColor = 0xe3e3e3;
				 _trace("test 0");
				 _trace(PrintHelper.flashVars);
				 var clr = parseInt(String(PrintHelper.flashVars["watermarkFontColor"]).replace("#", ""), 16);
				 _trace("test");
				 _trace(clr);
				 if (!isNaN(clr)) {
					 watermarkColor =  clr;
				 }
				 var tf3:TextFormat = new TextFormat(null, null, watermarkColor);
				 
				 
				 /*Кусок неудачной попытки рисования под углом
				  * 
				  * var dtf:TextFormat = new TextFormat();
				 var a     : Array      = Font.enumerateFonts(false);
				 if (a.length > 0) {
					dtf.font = a[0]["fontName"];// required to rotate text
				} else {
				}
				 offset = toPix( 20 ); //начинаю с 2 см
				 var limit = Math.round(Math.sqrt( w*w + h*h));
				 var limit2 = Math.round(Math.sqrt( 
					(w - toPix(2*lMargin + 5) ) *  (w - toPix(2*lMargin + 5) ) +   
					(h - toPix(2*tMargin + 5) ) *  (h - toPix(2*tMargin + 5) ) 
				 ) ) - 100;
				 while (true) {
					 var tef:TextField = new TextField();
					 tef.border = true;
					 tef.borderColor = 0xff0000;
					 tef.defaultTextFormat = dtf;
					 tef.embedFonts = true;
					 var maxW = w - toPix(lMargin + 8);
					 //tef.width = maxW;
					 //tef.text = MARKER_TEXT;
					 //var oW = 
					 var lineW = 2 * offset;
					 if (offset > (limit2 / 2)) {
						 //lineW = limit2 - (lineW - limit2);
						 lineW = 2 * Math.round((limit2 / 2) - (offset - (limit2 / 2)));
					 }
					 tef.x = getStart(rowID, mmW) + toPix(lMargin);
					 tef.y = Math.round( Math.sqrt(offset * offset * 2) ) + toPix(tMargin);
					 
					 var k = 0;
					 var str = '';
					 //ExternalInterface.call("console.log", "tef.tw =  " + tef.textWidth + ", maxW = " + maxW);
					 while (tef.textWidth < lineW) {
						str += MARKER_TEXT.charAt(k % (MARKER_TEXT.length - 1));
						k++;
						tef.text = str;
					 }
					 tef.setTextFormat(tf3);
					 tef.rotation = -45;
					 ExternalInterface.call("console.log", tef.getBounds(spr));
					 ExternalInterface.call("console.log", tef.x + ", tef.y = " + tef.y + ", width = " + tef.width + ", height = " + tef.height);
					 
					 spr.addChild(tef);
					 //break;
					 if (offset + tef.height > limit2) {
						break;
					 }
					 
					 offset += toPix(10);
					 
					 if (offset + tef.height > limit2) {
						break;
					 }
					 
					 if (offset > limit) {
						break;
					 }
				 }
				 */
				 
				 
				 while (true) {
					 var tef:TextField = new TextField();
					 var maxW = w - toPix(lMargin + 6);
					 tef.width = maxW;
					 //tef.text = MARKER_TEXT;
					 var k = 0;
					 var str = '';
					 while (tef.textWidth < maxW) {
						str += MARKER_TEXT.charAt(k % (MARKER_TEXT.length - 1));
						k++;
						tef.text = str;
					 }
					 
					 //tef.width = maxW;
					  
					 tef.setTextFormat(tf3);
					 tef.x = getStart(rowID, mmW) + toPix(lMargin + 4);
					 tef.y = offset;
					 spr.addChild(tef);
					 offset += toPix(70); //70
					 
					 if (offset > h) {
					    break;
					 }
				 }
			 }
			 
			 
			 var gridSprite:Sprite = new Sprite();
			 gridSprite.name = "gridSprite";
			 gridSprite.graphics.lineStyle(0.25, color);
			 //hor
			 var start = getStart(rowID, mmH) + toPix(tMargin);
			 for (var i = start; i < h; i+= delta)
			 {
				 gridSprite.graphics.moveTo(toPix(lMargin), i);
				 gridSprite.graphics.lineTo(w, i);
			 }
			 //vert
			 start = getStart(colID, mmW) + toPix(lMargin);
			 for (i = start; i < w; i+= delta)
			 {
				 gridSprite.graphics.moveTo(i, toPix(tMargin));
				 gridSprite.graphics.lineTo(i, h);
			 }
			 spr.addChild(gridSprite);
		 }
		 
		 static public function getStart(multiplier, length)
		 {
			var start = 0;
			var k = multiplier * length;
			if (k != 0)
			{
				var n = k / 10;
				n = Math.ceil(n) * 10;
				_trace("round (" + k +  ")  = "  + n);
				start = n - k;
			}
			return toPix(start);
		 }
		 
		 static public function getFormat(o:Object)
		 {
		  formats = new MapPoint();
		  formats.insert("A4", {x:210, y: 297});
		  formats.insert("A4A", {x:216, y: 279});
		  formats.insert("A3", {x:297, y: 420});
		  formats.insert("A2", {x:420, y: 594});
		  formats.insert("A1", {x:594, y: 841});
		  formats.insert("A0", {x:841, y: 1189});
		  var dw = o.paperWidth - o.pageWidth;
		  if (dw != 0) dw = dw / 2;
		  lMargin = PrintHelper.MARGIN - toMm(dw);
		  rMargin = PrintHelper.MARGIN + toMm(dw);
		  var dh = o.paperHeight - o.pageHeight;		  
		  var resp:CResponder = new CResponder("action=saveprintdata&pageW=" +  toMm(o.pageWidth) + "&pageH=" + toMm(o.pageHeight) + "&papW=" + toMm(o.paperWidth) + "&papH=" + toMm(o.paperHeight));
		  if (dh != 0) dh = dh / 2;
		  tMargin = PrintHelper.MARGIN - toMm(dh); // toMm(5);// 14.5;
		  bMargin = PrintHelper.MARGIN + toMm(dh); // toMm(5);// 14.5;
		  LANDSCAPE = "landscape";
		  PORTRAIT  = "portrait";  
		  var w = toMm(o.paperWidth);
		  //ExternalInterface.call("alert", toMm(o.paperHeight));
		  if (o.orientation == PORTRAIT) {
			  if (w == 210) return "A4";
			  if (w == 216) return "A4A";
			  if (w == 297) return "A3";
			  if (w == 420) return "A2";
			  if (w == 594) return "A1";
			  if (w == 841) return "A0";
		  }
		  /*var b = tMargin;
		  tMargin = lMargin;
		  lMargin = b;
		  
		  b = rMargin;
		  rMargin = bMargin;
		  bMargin = b;*/
		  var h = toMm(o.paperHeight);
		  if (h == 210) return "A4";
		  if (h == 216) return "A4A";
		  if (h == 297) return "A3";
		  if (h == 420) return "A2";
		  if (h == 594) return "A1";
		  if (h == 841) return "A0";
		 }
		 //---------------
		 //возвращает массив номеров страниц. Номер страницы представляет собой строку "N:M"
		 //		где  N - номер строки прямоугольной таблицы, M - номер столбца	 
		 static public function getPages(format:String, lineInfo:Object, listOrientation:String, delta:Object):	Array 
		 {
			var result:Array  = new Array();
			if ( (listOrientation != LANDSCAPE)&& (listOrientation != PORTRAIT))
			{
				trace ("unknown orientation");
				result.push("-1:-1");
				return result;
			}
			lineInfo.startX -= delta.dx;
			lineInfo.endX -= delta.dx;
			
			lineInfo.endY -= delta.dy;
			lineInfo.startY -= delta.dy;
			
			var l = lineLength(lineInfo);
			if (lineLength(lineInfo) > 10)
			{
				//исходя из того что линия прямая вычисляем её пересечение с прямоугольниками
				//если она перпендикулярна одной из паре сторон прямоугольника получаем все, что между ними.
				var page = getPageName(lineInfo.startX, lineInfo.startY, format, listOrientation);
				var page2 = getPageName(lineInfo.endX, lineInfo.endY, format, listOrientation);
				var debug = false;
				/*if ((page == "3:2")&&(page2 == "1:4")) 
				{
					trace ("NEWFIND");
					trace ("page1 == " + page);
					trace ("page2 == " + page2);	
					debug  = true;
				}/**/
				var norm = isNormal(page, page2);
				if (norm != false)
				{
						//if (debug == true) trace ("norm != false");
						result  = getMiddleRects(norm, page, page2);
						return result;
				} 		
				//иначе получаем массив прямоугольников составляющих прямоугольник, в углах которого прямоугольники,
				//						которым принадлежат точки прямых 
				else
				{
					if (debug == true) trace ("WILL GET INNER RECTS");
					var pages = getInnerRects(page, page2);
					if ((debug == true))
						{
							trace ("startX == " + lineInfo.startX);
							trace ("startY == " + lineInfo.startY);
							trace ("endX   == " + lineInfo.endX);
							trace ("endY   == " + lineInfo.endY);
						}/**/
					//и для каждого из них смотрим пересечение для каждой из сторон
					for (var i = 0; i < pages.length; i++)
					{
						if (debug == true)trace ("pages[" + i + "]" + pages[i]);
						var _rect = pageNameToRect(pages[i], format, listOrientation);
						/*if ((debug == true))
						{
							trace ("rect_x == " + _rect.x);
							trace ("rect_y == " + _rect.y);
							trace ("rect_w == " + _rect.w);
							trace ("rect_h == " + _rect.h);
						}/**/
						if (pages[i] == "2:3")
						{
							if (debug)
							{
								trace ("сейчас проверим на пересечение....");
							}
						}

						if (isIntersect(lineInfo, _rect)) 
						{
							if (pages[i] == "2:3")
							{
								if (debug) trace ('Да, пересекается');
							}
							result.push(pages[i]);
						}
					}
					return result;
				}
				
			}
			else
			{
			 page = getPageName(lineInfo.startX, lineInfo.startY, format, listOrientation);
			 if (noExist(result, page)) result.push(page);
			 page = getPageName(lineInfo.endX, lineInfo.endY, format, listOrientation);
			 if (noExist(result, page)) result.push(page);
			}
			return result;
		 }
		 //---------------
		 //возвращает объект с полями аналогичными dssLoader.lines[i],
		 //но пересчитанными в соответствии с форматом листа, его номером и его положением
		 static public function getData(listFormat:String, lineInfo:Object, listOrientation:String, pageNumber:String)
		 { 
			PrintHelper.trc = ''; 
			var debug  = true; 
			
			/*if (debug)
			{
				_trace (String(pageNumber));
				_trace ("startX = " + lineInfo.startX);
				_trace ("startY = " + lineInfo.startY);
				_trace ("endX = " + lineInfo.endX);
				_trace ("endY = " + lineInfo.endY);
			}/**/
			var format = formats.getPoint(listFormat);
			var w = format.x - (lMargin + rMargin);
			var h = format.y - (tMargin + bMargin);
			if (listOrientation == LANDSCAPE)
			{
				var b = w;
				w = h;
				h = b;
			}
			var p = toObject(pageNumber);
			var startX = lineInfo.startX - p.x * w;
			var endX = lineInfo.endX - p.x * w;
			var endY = lineInfo.endY - p.y * h;
			var startY = lineInfo.startY - p.y * h;
			var rFalse = false;
			if (pointInOutSide(startX, startY, w, h))
			{
				var o = rewritePoint(w, h, { startX:startX, startY:startY, endX:endX, endY:endY }, "start");
				if (o != false)
				{
					try
					{
						startX = o.x;
						startY = o.y;
					}
					catch (e)
					{
						rFalse = true; 
						//_trace('test1');
						//_trace('', true);
					}
				}
			}
			
			if (pointInOutSide(endX, endY, w, h))
			{
				o = rewritePoint(w, h, { startX:startX, startY:startY, endX:endX, endY:endY }, "end");
				if (o != false)
				{
					try
					{
						endX = o.x;
						endY = o.y;
					}
					catch (e)
					{
						rFalse = true; 
						//_trace('test1');
						//_trace('', true);
					}
				}
			}
			if (rFalse) return false;
			return {startX:startX, startY:startY, endX:endX, endY:endY};
		 }
		 //---------------
		 static private function pointInOutSide(x, y, w, h)
		 {
			 if (
					   (x < 0)
					|| (x > w)
					|| (y > h)
					|| (y < 0)
			    ) return true;
			return false;	
		 }
		 //---------------
		 static private function getIntersectPointLineWithPiece(line, piece)
		 {
			//если отрезок горизонтальный
			if (piece.startY == piece.endY)
			{
				var y = piece.startY;
				//если прямая не вертикальная  и не горизонтальная
				if ((line.startX != line.endX)&&(line.startY != line.endY))
				{
					var eLine  = getLineKoeff(line);
					var x = (y - eLine.b) / eLine.k; 
					if ((x >= piece.startX) && (x <= piece.endX)) return {x:x, y:y };
				}
				else //прямая горизонтальная
				if (line.startY == line.endY)
				{
					if (line.startY == piece.startY) //они лежат на одной пряиой
					{
						x = line.startX;
						if ((x >= piece.startX) && (x <= piece.endX)) return { x:x, y:y };
						x = line.endX;
						if ((x >= piece.startX) && (x <= piece.endX)) return {x:x, y:y };
					}
				}
				else //прямая вертикальная
				if (line.startX == line.endX)
				{
					x = line.startX;
					if ((x >= piece.startX) && (x <= piece.endX)) return {x:x, y:y };
				}
			}
			else  //отрезок вертикальный
			if (piece.startX == piece.endX)
			{
				x = piece.startX;
				//если прямая не вертикальная  и не горизонтальная
				if ((line.startX != line.endX)&&(line.startY != line.endY))
				{
					eLine  = getLineKoeff(line);
					y = eLine.k * x + eLine.b; 
					if ((y >= piece.startY) && (y <= piece.endY)) return {x:x, y:y };
				}else //если прямая горизонтальная
				if (line.startY == line.endY)
				{
					y = line.startY;
					if ((y >= piece.startY) && (y <= piece.endY)) return { x:x, y:y };
				}else //если прямая вертикальная
				if (line.startY == line.endY)
				{
					if (line.startX == piece.startX)	//и отрезок принадлежит ей
					{
						y = line.startY;
						if ((y >= piece.startY) && (y <= piece.endY)) return { x:x, y:y };
						y = line.endY;
						if ((y >= piece.startY) && (y <= piece.endY)) return { x:x, y:y };
					}
				}
			}
			return false;
		 }
		 //---------------
		 static private function rewritePoint(w, h, line, type)
		 {
			//верхняя горизонталь 
			var wPiece = { startX:0, startY:0, endX:w, endY:0 }; 
			//получить точку пересечения прямой с отрезком
			var point = getIntersectPointLineWithPiece(line, wPiece);
			var points:Array = new Array();
			if (point != false) //точка пересекается с отрезком
			{
				points.push(point);
			}
			//нижняя горизонталь 
			wPiece = { startX:0, startY:h, endX:w, endY:h }; 
			//получить точку пересечения прямой с отрезком
			point = getIntersectPointLineWithPiece(line, wPiece);
			if (point != false) //точка пересекается с отрезком
			{
				points.push(point);
			}
			//левая вертикаль
			var hPiece = { startX:0, startY:0, endX:0, endY:h }; 
			//получить точку пересечения прямой с отрезком
			point = getIntersectPointLineWithPiece(line, hPiece);
			if (point != false) //точка пересекается с отрезком
			{
				points.push(point);
			}
			//правая вертикаль
			hPiece = { startX:w, startY:0, endX:w, endY:h }; 
			//получить точку пересечения прямой с отрезком
			point = getIntersectPointLineWithPiece(line, hPiece);
			if (point != false) //точка пересекается с отрезком
			{
				points.push(point);
			}
			//if (points.length < 2) return false;
			return getNearestPoint(points, line, type);
		 }
		 
		 //Возвращает ближайшую к одному из краев отрезкa прямой точку
		 static public function getNearestPoint(cache:Array, line, type)
		 {
			if (cache.length > 1)
			{
				var p = cache[0];
				var min = getMinDist(p, line, type);
				for (var i = 0; i < cache.length; i++ )
				{
					var A = cache[i];
					var s = getMinDist(A, line, type);
					if (s < min)
					{
						p = A;
						min = s;
					}
				}
				return p;
			}	
		 }
		 
		 //Возвращает наименьшее расстояние от точки до края отрезка
		 static public function getMinDist(point, line, type)
		 {
			var x1 = point.x;
			var y1 = point.y;
			var x2 = line.startX;
			var y2 = line.startY;
			var r_2 = (x2 - x1)*(x2 - x1) + (y2 - y1)*(y2 - y1);
			var r1 = Math.sqrt(r_2);
			x2 = line.endX;
			y2 = line.endY;
			r_2 = (x2 - x1) * (x2 - x1) + (y2 - y1) * (y2 - y1);
			var r2 = Math.sqrt(r_2);
			if (type == "end") return r2;
			return r1;
		 }
		 //Возвращает объект приращения для сдвига страниц к краю листа
		 static public function  getDelta(lines:Array)
		 {
			var dx = undefined;
			var dy = undefined;
			for (var i = 0; i < lines.length; i++)
			{
				if (dx == undefined) dx = lines[i].startX;
				if (dx > lines[i].startX) dx = lines[i].startX;
				if (dx > lines[i].endX) dx = lines[i].endX;
				
				if (dy == undefined) dy = lines[i].startY;
				if (dy > lines[i].startY) dy = lines[i].startY;
				if (dy > lines[i].endY) dy = lines[i].endY;
			}
			//if (dx < 5) dx = 0;
			//if (dy < 5) dy = 0;
			return {dx:dx, dy:dy};
		 }
		 //---------------
		 //Возвращает размер страницы
		 static public function getFormatSize(key:String)
		 {
			 return formats.getPoint(key);
		 }
		 //--------------
		 //Возвращает булеву величину, в зависимости от того, пересекается ли линия lineInfo с какой либо из сторон
		 //прямоугольника
		 static private function isIntersect(lineInfo, _rect)
		 {
			 /*
			 * A-------------------B
			 * |                   |
			 * |                   |
			 * |                   |
			 * |                   |
			 * C-------------------D
			 * */
			//Получить коэффициенты уравнения прямой y= kx + b. (o = {b, k})
			
			var line:Object = getLineKoeff(lineInfo);
			var AB:Object = getLineKoeff( { startX:_rect.x, startY:_rect.y, endX:_rect.x + _rect.w, endY:_rect.y} );
			var point = getIntersectPoint(line, AB);
			if (point == undefined) trace ("Линия параллельна Y???");
			if ((point.x >= _rect.x) && (point.x <= _rect.x + _rect.w)) return true;
			var CD:Object = getLineKoeff( {startX:_rect.x, startY:_rect.y + _rect.h, endX:_rect.x + _rect.w, endY:_rect.y + _rect.h } );
			point = getIntersectPoint(line, CD);
			if (point == undefined) trace ("Линия параллельна Y???");
			/*if (debug == true)
			{
				trace ("find point");
				trace ("point_x == " + point.x);
				trace ("point_y == " + point.y);
			}*/
			if ((point.x >= _rect.x) && (point.x <= _rect.x + _rect.w)) return true;
			//AC
			var x = _rect.x;
			var y = line.k * x + line.b;
			var tv = _rect.y + _rect.h;
			/*if (debug == true)
			{
				trace ("AC");
				trace ("x == " + x);
				trace ("y == " + y);
				trace ("rect.y == " + _rect.y);
				trace ("rect.y +  rect.h == " + tv);
			}*/
			if ((y >= _rect.y) && (y <= _rect.y + _rect.h)) return true;
			//BD
			x = _rect.x + _rect.w;
			y = line.k * x + line.b;
			/*if (debug == true)
			{
				trace ("BD");
				trace ("x == " + x);
				trace ("y == " + y);
				trace ("rect.y == " + _rect.y);
				trace ("rect.y +  rect.h == " + tv);
			}*/
			if ((y >= _rect.y) && (y <= tv)) return true;
			return false;
		 }
		 //-------------- 
		 //возвращает точку пересечения прямых
		 //line_1, line_2 - объекты {b , k} (y = kx + b) 
		 static private function getIntersectPoint(line_1, line_2)
		 {
			var k0 = line_1.k; 
			var k2 = line_2.k; 
			var b2 = line_2.b; 
			var b0 = line_1.b; 
			if ((k0 != undefined) && (k2 != undefined))
			{
			  /*y = k0 * x + b0;
				y = k2 * x + b2;
				k2 * x + b2 = k0 * x + b0;
				k2 * x  = k0 * x + b0 - b2;
				k2 * x  - k0 * x  = b0 - b2;
				(k2 - k0) * x  = (b0 - b2);*/
				var x  = (b0 - b2) / (k2 - k0);
				var y = k0 * x + b0;
				return { x:x, y:y };
			}
			return undefined; //исходя из того что line_1 параллелльной осям координат быть не может (в этом случае данная функция не вызывается)
		 }
		 //---------------
		 //Получить коэффициенты уравнения прямой y= kx + b. (o = {b, k})
		 static private function getLineKoeff(lineInfo)
		 {
			var x1 = lineInfo.startX;
			var y1 = lineInfo.startY;
			var y2 = lineInfo.endY;
			var x2 = lineInfo.endX;
			var k = undefined;
			if (x2 - x1 != 0) k = (y2 - y1) / (x2 - x1);
			// y = kx + b;
			//y = kx - kx1 + y1
			var b = y1 - k * x1;
			var res = { b:b, k:k };
			return res;
		 }
		 //---------------
		 static private function getInnerRects(page:String, page2:String)
		 {
			var p1 = toObject(page);
			var p2 = toObject(page2);
			var res:Array = new Array();
			if (p1.x > p2.x)
			{
				var buf = { x:p1.x, y:p1.y };
				p1.x = p2.x; p1.y = p2.y;
				p2.x = buf.x; p2.y = buf.y;				
			}
			for (var i = p1.x; i <= p2.x; i++)
			{
				var _p1 = { x:p1.x, y:p1.y };
				var _p2 = { x:p2.x, y:p2.y };
				if (_p1.y > _p2.y)
				{
					var _buf = { x:_p1.x, y:_p1.y };
					_p1.x = _p2.x; _p1.y = _p2.y;
					_p2.x = _buf.x; _p2.y = _buf.y;				
				}
				for (var j = _p1.y; j <= _p2.y; j++)
				{
					var s = String(j) + ":" + String(i);
					res.push(s);
				}
			}
			return res;
		 }
		 //---------------
		 static private function getMiddleRects(norm:String, page:String, page2:String)
		 {
			var res:Array = new Array();
			if (norm == "h")
			{
				var p1 = toObject(page);
				var p2 = toObject(page2);
				if (p1.x > p2.x)
				{
					p1 = toObject(page2);
					p2 = toObject(page);
				}
				var N = String(p1.y);
				for (var i = p1.x; i <= p2.x; i++)
				{
					var s = N + ":" + String(i);
					res.push(s);
				}
				return res;
			}
			if (norm == "v")
			{
				p1 = toObject(page);
				p2 = toObject(page2);
				if (p1.y > p2.y)
				{
					p1 = toObject(page2);
					p2 = toObject(page);
				}
				var M = String(p1.x);
				for (i = p1.y; i <= p2.y; i++)
				{
					s = String(i) + ":" + M;
					res.push(s);
				}
				return res;
			}
			return res;
		 }
		 //---------------
		 static private function isNormal(page:String, page2:String)
		 {
			var p1 = toObject(page);
			var p2 = toObject(page2);
			if (p1.x == p2.x) return "v";
			if (p1.y == p2.y) return "h";
			return false;
		 }
		 //----------------
		 static public function toObject(pageStr:String)
		 {
			var arr:Array = pageStr.split(":");
			var res:Object = new Object();
			res.y = Number(arr[0]);
			res.x = Number(arr[1]);
			return res;
		 }
		 //----------------
		 static public function lineLength(lineInfo)
		 {
			var x1 = lineInfo.startX;
			var x2 = lineInfo.endX;
			var y2 = lineInfo.endY;
			var y1 = lineInfo.startY;
			return Math.sqrt((x2 - x1)*(x2 - x1) + (y2 - y1)*(y2 - y1));
		 }
		 //----------------
		 static public function pageNameToRect(page:String, format:String, listOrientation:String):Object
		 {
			var arr:Array = page.split(":");
			var sN:String = arr[0];
			var sM:String = arr[1];
			var N:Number = Number(sN);
			var M:Number = Number(sM);
			var sz = formats.getPoint(format);
			var w = sz.x -  lMargin;
			var h = sz.y -  tMargin;
			if (listOrientation == LANDSCAPE)
			{
			 var b = w;
				w = h;
				h = b;
			}
			N *= h;
			M *= w;
			var res:Object = {x:M, y:N, w:w, h:h};
			return res;
		 }
		 //----------------
		 static public function getPageName(x:Number, y:Number, format:String, listOrientation:String):String
		 {
			 //trace ("enter getPageName");
			var p = formats.getPoint(format);
			//trace ("post get Point");
			var w = p.x  - (lMargin + rMargin);
			var h = p.y  - (tMargin + bMargin);
			//trace ("post recalc");
			if (listOrientation == LANDSCAPE)
			{
				w = p.y - (tMargin + bMargin);
				h = p.x - (lMargin + rMargin);
			}
			/*var _x = 0;
			var xw = w;
			var M = 0;
			trace ("before first cycle");
			while (! ((x >= _x)&&(x <= xw)))
			{
				_x += w;
				xw += w;
				M++;
			}*/
			var M =  Math.floor(x/w);
			//trace ("post first cycle");
			/*var _y = 0;
			var yh = h;
			var N = 0;
			trace ("before second cy");
			while (! ((y >= _y)&&(y <= yh)))
			{
				_y += h;
				yh += h;
				N++;
			}*/
			var N =  Math.floor(y/h);
			//trace ("post second cy");
			var res:String = String(N) + ":" + String(M);
			return res;
		 }
		 //---------------
		 static public function noExist(arr:Array, item:String):Boolean
		 {
		  var i = 0;
		  while (i < arr.length)
		  {
		   if (arr[i] == item) return false;
			i++;
		  }
		  return true;
		 }
		 //---------------------
		 static public function toMm(pix:Number):Number
			{
				return Math.round((pix*20)/56.7);
			}
			
		static public function toPix(mm:Number):Number
		{
			return (mm*56.7)/20;
		}
		
		static public function rand(min:Number, max:Number):Number {	
			var v = Math.random();
			var ord = max.toString().length;
			v = Math.round( v * (ord + 1) * 10 ) % max;
			if (v < min) v = min;
			return v;
		}
	}//end calss

}