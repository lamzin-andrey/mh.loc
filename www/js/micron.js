var D = document,
W = window, S = String;
function e(i) {
	if (i && i.tagName || D == i) return i;
	return D.getElementById(i);
}
W.micron$ = e;
function ee(p, c) {
	p = e(p);
	return p.getElementsByTagName(c);
}
W.micron$$ = ee;
function cs(p, c) {
	p = e(p);
	if (p.getElementsByClassName) {
		return p.getElementsByClassName(c);
	}
	return [];
}
function hasClass(obj, css) {
	var obj = e(obj);
	var c = obj.className, _css = css.replace(/\-/g, "\\-"), 
	re1 = new RegExp("^\\s?" + _css + "\\s*"), 
	re2 = new RegExp("\\s+" + _css + "(\\s+[\\w\\s]*|\\s*)$");
	if (c == css || re1.test(c) || re2.test(c)) {
		return true;
	} 
	return false;
}
function removeClass(obj, css) {
	obj = e(obj);
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
	obj = e(obj);
	removeClass(obj, css);
	obj.className += ' ' + css;
}
//getviewport
function getViewport() {
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
function appendChild(parent, tag, innerHTML, obj, dataObj) {
	var e = D.createElement(tag), i;
	if (obj) {
		for (i in obj) {
			if (obj[i] instanceof Function) {
				e[i] =  obj[i];
			} else {
				e.setAttribute(i, obj[i]);
			}
		}
	}
	if (dataObj) {
		for (i in dataObj) {
			e.setAttribute('data-' + i, dataObj[i]);
		}
	}
	e.innerHTML = innerHTML;
	e(parent).appendChild(e);
}
function sz(a) {
	return a.length;
}
function attr(o, name, val) {
	o = e(o);
	if (val) {
		o.setAttribute(name, val);
	}
	if (o.hasAttribute(name)) {
		return o.getAttribute(name);
	}
	return null;
}
function stl(o, s, v) {
	o = e(o);
	o.style[s] = v;
}
function show(o, v) {
	v = v ? v : 'block';
	stl(o, 'display', v);
}
function hide(o) {
	stl(o, 'display', 'none');
}
function trim(s) {
	s = S(s).replace(/^\s+/mig, '');
	s = S(s).replace(/\s+$/mig, '');
	return s;
}
function In(a) {
	var i, o = {};
	if (a instanceof Array) {
		for (i = 0; i < sz(a); i++) {
			o[a[i]] = 1;
		}
	} else if (a instanceof Object) {
		for (i in a) {
			o[a[i]] = 1;
		}
	} else {
		for (i = 0; i < sz(arguments); i++) {
			o[arguments[i]] = 1;
		}
	}
	return o;
}
/**
 * @description Индексирует массив по указанному полю
 * @param {Array} data
 * @param {String} id = 'id'
 * @return {Object};
*/
function storage(key, data) {
	var L = window.localStorage;
	if (L) {
		if (data === null) {
			L.removeItem(key);
		}
		if (!(data instanceof String)) {
			data = JSON.stringify(data);
		}
		if (!data) {
			data = L.getItem(key);
			if (data) {
				try {
					data = JSON.parse(data);
				} catch(e){;}
			}
		} else {
			L.setItem(key, data);
		}
	}
	return data;
}
/**
 * @description Преобразовывается изображение в dataUri
 * @param {Image} 
 * @return {String}
*/
function imgToDataUri(i) {
	var canvas;
	try {
		canvas = document.createElement('canvas');
		canvas.width = i.naturalWidth;
		canvas.height = i.naturalHeight;
		canvas.getContext('2d').drawImage(i, 0, 0);
		var r = canvas.toDataURL('image/png');
		delete canvas;
		return r;
	} catch(e) {
		if (canvas) {
			delete canvas;
		}
	}
	return false;
}
/**
 * @description Сохранить объект имеющий поле id в базе webSql (Chrome) по аналогии со storage
 * @param {String} table
 * @param {Mixed} mixed если передан объект с полем id будкет сохранен в таблице под этим i но если передана функция, сделаем запрос на выборку, и вернем объект
 * @param {Number} id при выборке обязателен, при вставке обязателен, если нет в обьъекте поля id или он не объект
*/
function wstorago(table, mixed, id) {
	if (mixed instanceof Function && id) {
		wsStorage(table, id, mixed);
	} else {
		if ((mixed instanceof Object) && !id) {
			id = mixed.id;
		}
		if (id) {
			wsStorage(table, id, 0, mixed);
		}
	}
}
/**
 * @description Сохранить данные в базе webSql (Chrome). По аналогии со storage храним все как JSON объект, но в записи с id как у объекта
 * @param {String} table
 * @param {Number} id
 * @param {Function} | {Null} onData если запрос на выборку, вернем объект
 * @param {Object}  data
*/
function wsStorage(table, id, onData, data) {
	var db, tableNotExists, recordFound, sql = '', args = [],
		isSelectQuery;
	try {
		db = openDatabase("microndb", "0.1", "MicronDb", 200000);
	} catch(e) {
		onData(false);
		return;
	}
	
	if (db) {
		//существует ли таблица?
		db.transaction(function(tx) {
			//tx.executeSql("DROP TABLE " + table, [], null, null);
			tx.executeSql("SELECT data FROM " + table + ' WHERE id = ?', [id],
				function(result){},
				function(tx, error){
					console.log(error);
					if (~error.message.indexOf('no such table')) {
						tableNotExists = 1;
						tx.executeSql("CREATE TABLE " + table + " (id REAL UNIQUE, data TEXT)", [], null, null);
					}
				});
		});
		db.transaction(function(tx) {
			tx.executeSql("SELECT data FROM " + table + ' WHERE id = ?', [id], 
				function(tx, result){
					//console.log('Result exists...');
					if (result.rows && result.rows.item instanceof Function) {
						result = result.rows.item(0)['data'];	
						if (result) {
							try {
								result = JSON.parse(result);
							} catch(e){;}
						}
						recordFound = 1;
					} else {
						result = false;
					}
					if (onData instanceof Function) {
						//console.log(result);
						onData(result);
						return;
					}
					//console.log('Insert / update');
					if (!(data instanceof String)) {
						data = JSON.stringify(data);
					}
					args.push(data);
					args.push(id);
					if (!recordFound) {
						sql = "INSERT INTO " + table + ' (data, id) values(?, ?)';
					} else {
						sql = "UPDATE " + table + ' SET data = ? WHERE id = ?';
					}
					//console.log(sql);
					//console.log(args);
					db.transaction(function(tx) {
						tx.executeSql(sql, args, 
							function(result){
								//console.log('Ins/Upd Result:');
								//console.log(result);
							},
							function(tx, error){
								//console.log('Ins/Upd Fail:');
								console.log(error);
							});
					});
					
					
				},
				function(tx, error){
					console.log(error);
				});
		});
		
	}//end if db
}
