/** @var {Number} window.SelectTreeResetCategory если определено  и найдено в дереве, на этом надо остановиться. Если не найдено, остается только корневой select */

function SelectTree() {
	this.div_filter = '.j-select_tree_block';
	this.select_sign = '.j-select_tree';
	this.option_sign = '.j-option_select_tree';
	this.default_source = 'childs';
	this.storageKey = 'categories';
	
	var self = this;
	var i = 0;
	$$(this.div_filter).each(function (div) {
		self.initSelect(i, div);
		i++;
	});
}
SelectTree.prototype.initSelect = function(i, div) {
	div.innerHTML = '';
	var d = div, id = this._getId(d).replace('get_', '');
	var input = Element('input');
	input.type = 'hidden';
	input.name = input.id = id;
	$(d).grab(input, 'bottom');
	d.setAttribute('data-n', i);
	
	
	var a =  this._getId(d), self = this, data = {parent_id:0, i:i};
	var stored = localStorage.getItem(this.storageKey), i,
		actualData = $('cdata' + id);
	actualData = actualData ? actualData .value : false;
	if (stored || actualData) {
		if (actualData) {
			stored = this._actualizeStored(actualData, stored);
		}
		//try {
			stored = JSON.parse(stored);
			console.log(stored);
			var store = stored.store, 
			/** @var {Boolean} selectSuccess true если удалось выделить текущий элемент списка */
			selectSuccess,
			/** @var {Boolean} resetSuccess true если удалось переустановить значение текущего элемента на переданное в window.SelectTreeResetCategory */
			resetSuccess,
			afterResetSuccessIteration;
			
			for (var j = 0; j < store.length; j++) {
				var $div = $(document.body).getElement('div[data-n=' + i + ']'),
					select = self.createSelect(store[j].items, $div);
				if (select) {
					if (afterResetSuccessIteration) {//Уже нашли, добавляем следующий с "Ничего не выбрано"
						break;
					}
					if (window.SelectTreeResetCategory) {
						selectSuccess = selectByValue(select, SelectTreeResetCategory);
						if (!selectSuccess) {
							selectByValue(select, store[j].value);
						} else {
							resetSuccess = true;
							store[j].value = SelectTreeResetCategory;
						}
					} else {
						selectByValue(select, store[j].value);
					}
				}
				if (resetSuccess) {//Уже нашли, дальше не идём
					afterResetSuccessIteration = 1;
					continue;
				}
			}
			input.value = stored.value;
			if (resetSuccess) {
				stored.value = input.value = SelectTreeResetCategory;
				localStorage.setItem(this.storageKey, JSON.stringify(stored));
			} else if(window.SelectTreeResetCategory) {
				//не нашли, оставляем только первый
				this.dropAllSelects($$(this.div_filter)[0]);
				for (var j = 0; j < store.length; j++) {
					var select = self.createSelect(store[j].items, $(document.body).getElement('div[data-n=' + i + ']'));
					break;
				}
			}
		//} catch(e) {console.log(e);/**/}
	} else {
		window.req(
			data,
			function(response) {
				self.createSelect(response.list, $(document.body).getElement('div[data-n=' + response.i + ']'));
			}, 
			function(){},
			a
		);
		
	}
	
	
};
SelectTree.prototype._getId = function(div) {
	var a = div.getAttribute('data-source');
	a = a ? a : this.default_source;
	return 'get_' + a;
};
/**
 * @param $div - контейнер, из которого надо удалить все select
 * @param select - который не надо удалять
*/
SelectTree.prototype.dropAllSelects = function($div, select) {
	//$div.find('select').each(
	var found  = (select ? 0 : 1);
	$div.getElements('select').each(
		function removeOldSelects(sel){
			if (sel == select) {
				found = 1;
				return;
			}
			if (found) {
				$(sel).remove();
			}
		}
	);
}
/**
 * Актуализирует данные я сохраненные в localStorage
 * @param {String} actualData  - данные из php SelectTree::_selectedData кодированые в JSON
 * @param {String} defaultData - данные, сохраненные ранее в localStorage кодированые в JSON
 * @return {String}  actualData преобразованое в формат defaultData и коодированое в JSON
*/
SelectTree.prototype._actualizeStored = function(actualData, defaultData) {
	var data;
	try {
		actualData = JSON.parse(actualData);
		console.log(actualData);
		if (actualData.cats && actualData.selected) {
			var oStored = {}, store = [], i, j, item, oBuf = {};
			oStored.value = actualData.selected[actualData.selected.length - 1];
			if (actualData.cats instanceof Array) {
				for (i = 0; i < actualData.cats.length; i++) {
					oBuf[i] = actualData.cats[i];
				}
				actualData.cats = oBuf;
			}
			j = 0;
			for (i in actualData.cats) {
				item = {};
				item.items = actualData.cats[i];
				//item.items.unshift() это скорее всего не надо так как "Ничего не выбрано " добавится на этапе рендеринга
				item.value = actualData.selected[j];
				store.push(item);
				j++;
			}
			oStored.store = store;
			console.log(oStored);
			data = JSON.stringify(oStored);
		}
	} catch(e){
		data = defaultData;
	}
	return data;
}			
SelectTree.prototype.createSelect = function(data, $div) {
	if (!data.length) {
		return null;
	}
	this.select_template = '<select class=""><option value="-1">' + 'Ничего не выбрано' + '</option></select>';
	var $select = Element('select'), a =  this._getId($div), i, j = 1, select, self = this,
		noth = 'Ничего не выбрано';
	$select.setAttribute('class', 'j-select_tree');
	$select.options[0] = new Option(noth, -1);
	//console.log($select);
	select = $select;
	
	//$div.append($select);
	$($div).grab($select, 'bottom');
	
	for (i in data) {
		if (data[i].id) {
			if (data[i].name != noth) {
				select.options[j] = new Option(data[i].name, data[i].id);
				j++;
			}
		}
	}
	$select.addEvent('change', 
		function(evt) {
			var select = this, found = 0, value = select.value,  store = [], ls, i, negValueFound = 0;
			if (value != -1) {
				$(select.parentNode).getElements('input[type=hidden]')[0].value = value;
			} else {
				ls = $(select.parentNode).getElements('select');
				if (ls.length > 0) {
					i = 1;
					while (value == -1 || negValueFound == 0) {
						value = ls[ls.length - i].value;
						if (value == -1) {
							negValueFound = 1;
						}
						i++;
						if (i > ls.length) {
							break;
						}
					}
					$(select.parentNode).getElements('input[type=hidden]')[0].value = value;
				}
			}
			self.dropAllSelects($div, select);
			window.req(
				{parent_id:select.value},
				function(response) {
					self.createSelect(response.list,  $div);
					
					//store selected value
					$div.getElements('select').each(function(sel){
						var i, obj, items = [];
						for (i = 0; i < sel.options.length; i++) {
							obj = {id:sel.options[i].value, name:sel.options[i].text};
							items.push(obj);
						}
						store.push({items:items, value:sel.value});
					});
					localStorage.setItem(self.storageKey, JSON.stringify({store:store, value:value}));
				}, 
				function(){},
				self._getId($div)
			);
		}
	);
	return select;
	
};

(
    function() {
		$(window).addEvent('DOMContentLoaded', init);
		function init() {
			window.selectTree = new SelectTree();
		}
	}
)()
