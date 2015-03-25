function sz(o) {
	return o.length;
}

function to_i(n) {
	return (parseInt(n) ? parseInt(n) : 0);
}

var Tool = {
	host:function(s) {
		if (!s) {
			s = window.location.href;
		}
		return s.split('/').slice(0, 3).join('/');
	},
	/**
	 * @desc Добавляет к корню слова окончание в зависимости от величины числа n
	 * @param n - число
	 * @param root корень слова
	 * @param one окончание в ед. числе
	 * @param less4 окончание при величине числа от 1 до 4
	 * @param more19 окончание при величине числа более 19
	 * @returString
	 */
	getSuffix:function(n, root, one, less4, more19) {
         m = String(n);
         if (sz(m) > 1) {
             m =  to_i( m.charAt( sz(m) - 2 ) + m.charAt( sz(m) - 1 ) );
         }
         lex = root + less4;
         if (m > 20) {
             r = String(n);
             i = to_i( r.charAt( String(r) - 1 ) );
             if (i == 1) {
                 lex = root + one;
             } else {
                 if (i == 0 || i > 4) {
                    lex = root + more19;
                 }
             }
         } else if (m > 4 || m == '00') {
             lex = root +  more19;
         } else if (m == 1) {
             lex = root + one;
         }
         return lex;
	},
	/**
	 * @desc Добавляет к корню слова окончание в зависимости от величины числа n
	 * @param id Идентификатор группы полей
	 */
	clearInputs:function(id) {
		$('#' + id + ' input, #' + id + ' textarea').each(
			function(i, j) {
				if (j.type != 'button' && j.type != 'submit') {
					$(j).val('');
				}
				if (j.type == 'checkbox') {
					$(j).prop('checked', false);
				}
			}
		);
	},
	/**
	 * @desc Возвращает строку адреса в виде массива. Не содержит протокол и GET часть
	 */
	aUrl:function(){
		var a = window.location.href.split('/'), b = [], i;
		for (i = 2; i < a.length; i++) {
			b.push( a[i] );
		}
		return b;
	}
}
