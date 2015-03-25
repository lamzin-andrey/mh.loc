(
		function () {
			var D = document,
			W = window;
			function $(i) {
				if (i&& i.tagName || D == i) return i;
				return D.getElementById(i);
			}
			function $$(p, c) {
				p = $(p);
				return p.getElementsByTagName(c);
			}
			function hasClass(obj, css) {
				var obj = $(obj);
				var c = obj.className, _css = css.replace(/\-/g, "\\-"), 
				re1 = new RegExp("^\\s?" + _css + "\\s*"), 
				re2 = new RegExp("\\s+" + _css + "(\\s+[\\w\\s]*|\\s*)$");
				if (c == css || re1.test(c) || re2.test(c)) {
					return true;
				}
				return false;
			}
			function removeClass(obj, css) {
				obj = $(obj);
				var c = obj.className, re = /[0-9a-zA-Z\-_]+/gm,
				arr = c.match(re),
				i, result = [];
				if (arr) for (i = 0; i < arr.length; i++) {
					if (arr[i] !== css) {
						result.push(arr[i]);
					}
				}
				obj.className = result.join(' ');
			}
			function addClass(obj, css) {
				obj = $(obj);
				removeClass(obj, css);
				obj.className += ' ' + css;
			}
			//getviewport
			window.getViewport = function() {
				var w = W.innerWidth, h = W.innerHeight;
				if (!w && D.documentElement && D.documentElement.clientWidth) {
					w = D.documentElement.clientWidth;
				} else if (!w) {
					w = D.getElementsByTagName('body')[0].clientWidth;
				}
				if (!h && D.documentElement && D.documentElement.clientHeight) {
					h = D.documentElement.clientHeight;
				} else if (!h) {
					h = D.getElementsByTagName('body')[0].clientHeight;
				}
				return {w:w, h:h};
			}
			/**
			 * 
			 * @param string id идентификатор блока
			 * @param string s тайтл окна
			 * @param Function onclose вызывается перед закрытием окна
			 * @param Number clear говорит о том, что поля ввода следует очистить
			 * 
			 * */
			W.appWindow = function(id, s, onclose, clear) {
			var bgId = "popupbg",
			winId = "popup",
			current = "current_wnd_content",
			content = "appWindowPopup",
			tmp,
			winW,
			winH,
			vp = getViewport(),
			w = vp.w,
			h = vp.h;
			//вызвать appWindowClose
			appWindowClose();
			W.appCloseCallback = onclose;
			if (!$(id)) {
				return;
			}
			if (clear) {
				var ls = $$(id, "input"), i, lt = $$(id, "textarea");
				for (i = 0; i < (ls.length > lt.length ? ls.length : lt.length); i++) {
					if (ls[i] && ls[i].name != "authenticity_token" ) {
						if (ls[i].type != "checkbox" && ls[i].type != "button" && ls[i].type != "submit") {
							 ls[i].value = "";
						 } else {
							 ls[i].checked = false;
						 }
					}
					if (lt[i]) {
						lt[i].value = "";
					}
				}
			}
			$(winId).style = '';
			$(content).style ='';
			if (s) {
				$("popuptitle").innerHTML = s;
			}
			//Для фона и окна  - установить opacity в ноль а видимость в истину
			$(winId).style.opacity = 0;
			removeClass(bgId, "hide");
			removeClass(winId, "hide");
			 
			//Взять заданный див.
			//Заменить его созданным дивом current_wnd_content
			tmp = D.createElement("div");
			$$(D, "body")[0].appendChild(tmp);
			tmp.id = current;
			swapNodes(id, tmp);
			//Добавить в контент попапа.
			removeClass(id, "hide");
			removeClass(content, "scrollx");
			removeClass(content, "scrolly");
			$(content).appendChild( $(id) );
			
			//Определить размер
			$(id).opacity = 0;
			
			winW = $(winId).offsetWidth;
			winH = $(winId).offsetHeight;
			//Если меньше чем у вьюпорта, установить размеры окна в авто и отцентрировать
			if (winW < w && winH < h) {
				with ($(winId).style) {
					left = Math.round((w - winW) / 2) + 'px';
					top  = Math.round((h - winH) / 2) + 'px';
				}
			} else {//Иначе установить размеры как у вьюпорта.
				with ($(winId).style) {
					left = '0px';
					top  = '0px';
					width = '100%';
					height = h + 'px';
					overflowX = 'scroll';
					overflowY = 'scroll';
				}
			}
			if ($(id).offsetWidth > $(content).offsetWidth) {
				addClass(content, "scrollx");
				$(content).style.maxWidth = (w - 5) + "px";
			}
			if ($(id).offsetHeight > (h - 35) ) {
				addClass(content, "scrolly");
				$(content).style.maxHeight = (h - 40) + "px";
			}
			//Установить opacity в 1
			with ($(bgId).style) {
				width = w + 'px';
				height = h + 'px';
			}
			$(winId).style.opacity = 1;
			$(id).setAttribute("data-srcid", id);
			$(id).id = "execute_block";
		}
		W.swapNodes = function(b1, b2) {
			b1 = $(b1);
			b2 = $(b2);
			var t = D.createElement("i"),
			p1 = b1.parentNode, p2 = b2.parentNode;
			p1.replaceChild(t, b1);
			p2.replaceChild(b1, b2);
			p1.replaceChild(b2, t);
			delete t;
		}
		W.appWindowClose = function() {
			var bgId = "popupbg",
			winId = "popup",
			current = "current_wnd_content",
			execute = "execute_block",
			old = $(execute);
			//сделать невидимыми фон и окно
			//removeClass(bgId, "hide");
			//removeClass(winId, "hide");
			addClass(bgId, "hide");
			addClass(winId, "hide");
			//Если существует на странице див с идентификатором current_wnd_content
			if ($(current)) {
				//взять див с айди execute_block, получить его атрибут srcid, заменить его значением id 
				if (old) {
					old.id = old.getAttribute("data-srcid");
					//и заменить эти дивом current_wnd_content, 
					swapNodes(current, old);
					//не забыв сделать невидимым
					addClass(old, "hide");
				}
				//удалить див current_wnd_content
				$(current).parentNode.removeChild($(current));
			}
			if (W.appCloseCallback instanceof Function) {
				W.appCloseCallback();
			}
		}
		
		W.showLoader = function() {
			var bgId = "loaderbg",
				ldId = "ldrbig",
				vp = getViewport(),
				w = vp.w,
				h = vp.h;
			
			removeClass(bgId, "hide");
			with ($(bgId).style) {
				width = w + 'px';
				height = h + 'px';
			}
			removeClass(ldId, "hide");
			with ($(ldId).style) {
				opacity = 0;
				left = Math.round((w - $(ldId).offsetWidth) / 2) + 'px';
				top  = Math.round((h - $(ldId).offsetHeight) / 2) + 'px';
				opacity = 1;
				position = 'absolute';
			}
		}
		W.hideLoader = function() {
			addClass("ldrbig", "hide");
			addClass("loaderbg", "hide");
		}
		
	} //end anonim function
)
();
