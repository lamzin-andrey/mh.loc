/**
 * @desc Слова, появившиеся на первом занятии
*/
(function () {
	$(document).ready(init);
	function init() {
		TestNewWords.quests.push({t:1, q:"function", a:["функция"],	r:0});
		TestNewWords.quests.push({t:1, q:"name", a:["имя"],	r:0});
		TestNewWords.quests.push({t:1, q:"alert", a:["внимание"],	r:0});
		TestNewWords.quests.push({t:1, q:"other", a:["другой"],	r:0});
		TestNewWordsHandler.shuffle();
	}
})()

