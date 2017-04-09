function SelectTree() {
	this.div_filter = '.j-select_tree_block';
	this.select_sign = '.j-select_tree';
	this.option_sign = '.j-option_select_tree';
	this.default_source = 'childs';
	this.select_template = '<select class="j-select_tree"><option value="-1">' + window.appLang['Dont_select'] + '</option></select>';
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
