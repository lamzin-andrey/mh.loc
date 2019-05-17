/**
 * @class Model имитирует механизм ActiveREcord на уровне localStorage
 * 
 * Работает с двумя "таблицами": основной, в которой сохраняются записи с известным id (предполагается, что он получен с сервера)
 *  							 и в дополнительной, в которой сохраняются записи с неизвестным id (например сохранили пост в блоге, а соединения с сервером нет)
 * 								 При попытке получить запись, которая сохранена в дополнительной таблице методами find, findAll, найденый объект будет иметь id == 0, isLocalRecord = 1, __local__ будет равен "виртуальному" id записи
 * 								 "Виртуальный id" вычисляется таким образом: берется максимальный известный id и инкрементится.
*/
function Model() {
	this.local = '__local__';
	this.indexKey = '__indexdata__';
	this.isLocalRecord = 0;
	this.u = 'undefined';
	this.excludeFields = {
		local:1,
		indexKey:1,
		isLocalRecord : 1,
		u:1,
		excludeFields:1,
		table:1,
		className:1
	};
}
Model.prototype.save = function() {
	var i, o = this, j, k, s = '_', max, idx = o.id, data = {};
	idx = idx ? idx : 0;
	
	for (i in o) {
		if (i in o.excludeFields) {
			continue;
		}
		j = typeof(o[i]);
		//console.log( 'field ' + i  + ',  type = ' + j);
		if (j == 'string' || j == 'number') {
			data[i] = o[i];
		}
	}
	//если id известен, сохраняем в текущей таблице
	//иначе с именем как у текущей  + __local__
	if (idx) {
		k = o.table + s + idx;
		//удалим тут же её из временной, если она там была
		if (+o[o.local]) {
			console.log('Ye, will delete local');
			o.deleteLocal(+o[o.local]);
			o.dropIndex(o.local, 0);
			delete data[o.local];
			o.isLocalRecord = 0;
		}
	} else {
		if (+o[o.local]) {
			idx = parseInt(o[o.local]);
		} else {
			idx = o.getMaxId();
			idx++;
		}
		data[o.local] = idx;
		k = o.table + o.local + idx;
	}
	console.log(data);
	console.log("Save as " + k);
	storage(k, data);
	o.updateIndex(idx, 0);
	o.writeIndex();
}
/**
 * @description  Если запись удалось найти только в локальной таблице this.isLocalRecord = true
*/
Model.prototype.find = function(id) {
	//TODO o = new this.className();
	var o = this, rec, s = '_';
	rec = storage(o.table + s + id);
	if (rec) {
		o.isLocalRecord = 0;
	} else {
		rec = storage(o.table + o.local + id);
		o.isLocalRecord = 1;
	}
	if (!rec) {
		return null;
	}
	console.log('Before cycle local = ' + o.local);
	for (i in o) {
		if (i in o.excludeFields) {
			continue;
		}
		j = typeof(o[i]);
		if (j == 'string' || j == 'number') {
			console.log('Process field ' + i);
			if (rec[i]) {
				console.log('...field  exists in record and = ' + rec[i]);
				if (o.isLocalRecord && i == 'id') {
					console.log('...it local record and is id field');
					o[o.local] = o[i];
					console.log('...safe value "' + o[i] + '" with key "' + o.local + '"');
					o[i] = 0;
					console.log('...write value "0" with key "' +i + '"');
				} else {
					o[i] = rec[i];
				}
			} else {
				o[i] = 0;
			}
		}
	}
	if (rec[o.local] && o.isLocalRecord) {
		o[o.local] = rec[o.local];
	}
	return o;
}
/**
 * @description 
*/
Model.prototype.findAll = function(offset, limit) {
	offset = offset ? offset : 0;
	var o = this, i, j, m, index = o.getIndexArray(), n = sz(index), res = {}, nFound = 0, deleteIndexes = [];
	limit = limit ? limit : n;
	for (i = offset; i < limit; i++) {
		j = o.find(index[i]);
		if (j) {
			//TODO if (j.checkCondition(condition)) {
				res[index[i]] = j;
				nFound++;
			//}
		} else {
			deleteIndexes.push(index[i]);
		}
	}
	if (deleteIndexes.length) {
		for (i = 0; i < deleteIndexes.length; i++) {
			o.dropIndex(deleteIndexes[i], 0);
		}
		o.writeIndex();
	}
	return res;
}
/**
 * @description Удаляет запись из таблицы table (если она там есть), из таблицы table__local (если в table нет а в table__local есть). Удаляет индекс
 * @param {Boolean} writeIndexNow  = true по умолчанию сразу после удаления записывается в ls
*/
Model.prototype.remove = function(writeIndexNow) {
	var o = this, k;
	k = o.table + o.local + o.id;
	localStorage.removeItem(k);
	k = o.table + '_' + o.id;
	localStorage.removeItem(k);
	o.dropIndex(o.id, writeIndexNow);
}

/**
 * @return {Number} максимальный id для текущей таблицу базы данных
*/
Model.prototype.getMaxId = function() {
	var o = this, max = 0, i, n, ls;
	ls = o.getIndexArray();
	n = sz(ls);
	for (i = 0; i < n; i++) {
		if (ls[i] > max) {
			max = ls[i];
		}
	}
	return max;
}
/**
 * @description Получить массив с существующими id записей
 * @return {Array}
*/
Model.prototype.getIndexArray = function() {
	var o = this, i;
	o.loadIndex();
	return o.aIndex;
}
/**
 * @description Обновляет данные о позиции
 * @param {Number} id идентификатор записи
 * @param {Boolean} writeNow  = true по умолчанию сразу после удаления записывается в ls
*/
Model.prototype.updateIndex = function(id, writeNow) {
	var o = this, i, k, N, isChange;
	o.loadIndex();
	N = sz(o.aIndex);
	writeNow = String(writeNow) === o.u ? true : writeNow;
	if (!isNaN(+o.oIndex[id]) && o.aIndex[+o.oIndex[id]] == id) {
		return;
	}
	o.aIndex.push(id);
	o.oIndex[id] = o.aIndex.length - 1;
	if (writeNow) {
		o.writeIndex();
	}
}
/**
 * @description Удаляет из индекса id === n. 
 * @param {Number} n идентификатор записи
 * @param {Boolean} writeNow  = true по умолчанию сразу после удаления записывается в ls
*/
Model.prototype.dropIndex = function(n, writeNow) {
	var o = this, i, k, N;
	o.loadIndex();
	N = sz(o.aIndex);
	writeNow = String(writeNow) === o.u ? true : writeNow;
	k = o.oIndex[n];
	if (!k) {
		for (i = 0; i < N; i++) {
			if (o.aIndex[i] == n) {
				k = i;
				break;
			}
		}
	}
	if (k) {
		o.aIndex.splice(k, 1);
	}
	delete o.oIndex[n];
	if (writeNow) {
		o.writeIndex();
	}
}
/**
 * @description Загружает из локального хранилища данные о существующих в таблице  id (aIndex) и позициях этих id в списке aIndex (oIndex)
*/
Model.prototype.loadIndex = function(force) {
	var o = this, k, data;
	if (o.aIndex && o.oIndex && !force) {
		return;
	}
	k = o.table + o.indexKey;
	data = storage(k);
	if (!data) {
		o.aIndex = [];
		o.oIndex = {};
	} else {
		o.aIndex = data.a ? data.a : [];
		o.oIndex = data.o ? data.o : {};
	}
}
/**
 * @description Принудительно сохраняет индекс в локальное хранилище 
*/
Model.prototype.writeIndex = function() {
	var o = this, data = {};
	if (o.aIndex && o.oIndex) {
		data = {
			a:o.aIndex,
			o:o.oIndex
		}
		storage(o.table + o.indexKey, data);
	}
}
/**
 * @description Удаляет запись из таблицы table__local (после сохранения в основной копии таблицы). Не удаляет индекс!
*/
Model.prototype.deleteLocal = function(locId) {
	var o = this, k;
	k = o.table + o.local + locId;
	localStorage.removeItem(k);
}
