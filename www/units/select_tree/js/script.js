function SelectTree() {
	this.div_filter = '.j-select_tree_block';
	this.select_sign = '.j-select_tree';
	this.option_sign = '.j-option_select_tree';
	this.default_source = 'childs';
	this.select_template = '<select class="j-select_tree"><option value="-1">' + 'Ничего не выбрано' + '</option></select>';
	var self = this;
	$(this.div_filter).each(function (i, div) {
		self.initSelect(i, $(div));
	});
}
SelectTree.prototype.initSelect = function(i, $div) {
	$div.html('');
	var d = $div, id = this._getId(d).replace('get_', ''); 
	d.append('<input type="hidden" id="' + id + '" name="' + id + '">');
		
	d.attr('data-n', i);
	var a =  this._getId(d), self = this, data = {parent_id:0, i:i};
	window.req(
		data,
		function(response) {
			self.createSelect(response.list, $('div[data-n=' + response.i + ']'));
		}, 
		function(){},
		a
	);
};
/**
 * TODO on jq не проверялось ни разу
 * @param $div - контейнер, из которого надо удалить все select
 * @param select - который не надо удалять
*/
SelectTree.prototype.dropAllSelects = function($div, select) {
	//$div.find('select').each(
	var found  = (select ? 0 : 1);
	$div.find('select').each(
		function removeOldSelects(i, sel){
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
 * TODO на jq не проверялось, но должно быть независимым
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
SelectTree.prototype._getId = function($div) {
	var a = $div.data('source');
	a = a ? a : this.default_source;
	return 'get_' + a;
};
SelectTree.prototype.createSelect = function(data, $div) {
	var $select = $(this.select_template), a =  this._getId($div), i, j = 1, select = $select[0], self = this;
	$div.append($select);
	for (i in data) {
		select.options[j] = new Option(data[i].name, data[i].id);
		j++;
	}
	$select.change(
		function(evt) {
			var select = this, found = 0;
			$(select).parent().find('input[type=hidden]').first().val(select.value);
			$div.find('select').each(
				function removeOldSelects(i, sel){
					if (sel == select) {
						found = 1;
						return;
					}
					if (found) {
						$(sel).remove();
					}
				}
			);
			window.req(
				{parent_id:select.value},
				function(response) {
					self.createSelect(response.list,  $div);
				}, 
				function(){},
				self._getId($div)
			);
		}
	);
};

(
    function() {
		$(init);
		function init() {
			new SelectTree();
		}
	}
)()




/* TODO remove on MT*/
//ajax helper
	function req(data, success, fail, id, url, method) {
		if (!method) {
			method = 'post';
		}
		data.xhr = 1;
		data.action = id;
		//if (!data.token && $('#token')[0]) {
			data.token = window.token;
		//}
		$.ajax({
			dataType:'JSON',
			data:data,
			method:method,
			url:(url ? url : window.location.href),
			success:success,
			error:fail
		});
	}
	window.req = req;
