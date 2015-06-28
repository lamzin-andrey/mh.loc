var Tool = {
	post: function(url, data, F, E) {
		var a = data.action;
		delete data.action;
		window.req(data, F, E, a, url, 'post');
	},
	lskey: function (data) {
		var key = '', i;
		for (i in data) {
			if (i != 'token' && i != 'xhr') {
				key += i + "=" + data[i];
			}
		}
		return key;
	},
	cachepost: function(url, data, F, E) {
		if (!E) {E = F;}
		if (data.action && url) {
			var key = Tool.lskey(data), s, r;
			if (localStorage.getItem(key)) {
				try {
					r = localStorage.getItem(key);
					s = JSON.decode(r);
				    F(s, r);
				    return;
				} catch(err) {;}
			}
			Tool.cSucc = F;
			Tool.cFail = E;
			Tool.lsdata = data;
			Tool.post(url, data, Tool.cacheSuccess, Tool.cacheFail);
		}
	},
	cacheSuccess: function (data, raw) {
		localStorage.setItem(Tool.lskey(Tool.lsdata), raw);
		Tool.cSucc(data, raw);
	},
	cacheFail: function (xrh) {Tool.cFail(xrh);	},
	
	enableInputs: function(id, f) {
		if ($('#' + id)) {
			$(id).find("input").each(
				function(j, i) {
					i[0].disabled = f;
				}
			);
			$('#' + id).find("select").each(
				function(j, i) {
					i[0].disabled = f;
				}
			);
		}
	},
	parseData:function(s, pairSep, sep) {
		if (!pairSep) {
			pairSep = "&";
		}
		if (!sep) {
			sep = "=";
		}
		if (!s) {
			s = String(window.location.href.split("?")[1]);
		}
		var a = s.split(pairSep), r = {}, i, j;
		for (i = 0; i < sz(a); i++) {
			j = a[i].split(sep);
			r[j[0]] = j[1];
		}
		return r;
	},
	host:function(s) {
		if (!s) {
			s = window.location.href;
		}
		return s.split('/').slice(0, 3).join('/');
	}
}
function sz(obj) {return obj.length}
function to_i(n) {
	var v = parseInt(n);
	v = v?v:0;
	return  v;
}

function selectByValue(select, n) {
	for (var i = 0; i < sz($('#' + select)[0].options); i++) {
		if ( $('#' + select)[0].options[i].value == n ) {
			$('#' + select)[0].options.selectedIndex = i;
			$('#' + select)[0].onchange();
			break;
		}
	}
}
//==============================================================================

var loc = window.location.href;
function LocationGroup() {
	//handlers
	if (/*!$("country") || */!$("#region")[0] || !$("#city")[0]) {
		return;
	}
	$("#region")[0].onchange = function() {
		var cid = to_i(this.options[this.options.selectedIndex].value);
		Tool.cachepost(loc, {action:"city", regionId:cid}, onCityList);
		localStorage.setItem("region", cid);
		
	}
	$("#city")[0].onchange = function() {
		var cid = to_i(this.options[this.options.selectedIndex].value);
		localStorage.setItem("city", cid);
	}
	Tool.cachepost(loc, {action:"country"}, onCountryList);
}
//loadcountries
function fillLocSelect(id, data, name, getLast, lex) {
	var sl = $('#' + id)[0], n = 1;
	sl.options.length = 0;
	sl.options[0] = new Option(lex, 0);
	if(sz(data.list)) {
		$(data.list).each(
			function (jj, i) {
				sl.options[n] = new Option(i[name], i.id);
				n++;
			}
		);
	}
	//get last
	if (getLast) {
		if (fromRegionsPage() && $('#selected' + id + 'id')) {
			localStorage.setItem(id, $('#selected' + id + 'id').value);
		}
		if (to_i(localStorage.getItem(id))) {
			selectByValue(id, localStorage.getItem(id));
		} else {
			$('#' + id)[0].onchange();
		}
	}
}
function fromRegionsPage() {
	if (document.referrer) {
		if (Tool.host() == Tool.host(document.referrer)) {
			return true;
		}
	}
	return false;
}
function onCountryList(data) {
	Tool.cachepost(loc, {action:"region", countryId:3}, onRegionList);
	localStorage.setItem("country", 3);
}
//load regions
function onRegionList(data) {
	var id = "region";
	fillLocSelect(id, data, id + "_name", true, "Все регионы");
}
//load cities
function onCityList(data) {
	var id = "city";
	fillLocSelect(id, data, id + "_name", true, 'Все города');
}

(
    function() {
		$(init);
		function init() {
			new LocationGroup();
		}
	}
)()
