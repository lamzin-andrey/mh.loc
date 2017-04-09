/**
 * @desc Слова, появившиеся на первом занятии
*/
(function () {
	$(document).ready(init);
	function init() {
		TestNewWords.quests.push({t:1, q:"array", a:["массив"],	r:0});
		TestNewWords.quests.push({t:1, q:"break", a:["прервать"],	r:0});
		TestNewWords.quests.push({t:1, q:"case", a:["случай, вариант"],	r:0});
		TestNewWords.quests.push({t:1, q:"catch", a:["ловить"],	r:0});
		TestNewWords.quests.push({t:1, q:"continue", a:["продолжить"],	r:0});
		TestNewWords.quests.push({t:1, q:"default", a:["по умолчанию"],	r:0});
		TestNewWords.quests.push({t:1, q:"date", a:["дата"],	r:0});
		TestNewWords.quests.push({t:1, q:"do", a:["делать"],	r:0});
		TestNewWords.quests.push({t:1, q:"error", a:["ошибка"],	r:0});
		TestNewWords.quests.push({t:1, q:"else", a:["иначе"],	r:0});
		TestNewWords.quests.push({t:1, q:"false", a:["ложь"],	r:0});
		TestNewWords.quests.push({t:1, q:"finally ", a:["в заключении"],	r:0});
		TestNewWords.quests.push({t:1, q:"for", a:["для, цикл"],	r:0});
		TestNewWords.quests.push({t:1, q:"if", a:["если"],	r:0});
		TestNewWords.quests.push({t:1, q:"in", a:["в"],	r:0});
		TestNewWords.quests.push({t:1, q:"instanceof", a:["экземпляр ли"],	r:0});
		TestNewWords.quests.push({t:1, q:"instance", a:["экземпляр"],	r:0});
		TestNewWords.quests.push({t:1, q:"of", a:["из"],	r:0});
		TestNewWords.quests.push({t:1, q:"infinity", a:["бесконечность"],	r:0});
		TestNewWords.quests.push({t:1, q:"math", a:["мат(ематика)"],	r:0});
		TestNewWords.quests.push({t:1, q:"nan", a:["не число"],	r:0});
		TestNewWords.quests.push({t:1, q:"number", a:["число"],	r:0});
		TestNewWords.quests.push({t:1, q:"new", a:["новый"],	r:0});
		TestNewWords.quests.push({t:1, q:"null", a:["ничто"],	r:0});
		TestNewWords.quests.push({t:1, q:"оbject", a:["объект"],	r:0});
		TestNewWords.quests.push({t:1, q:"prototype", a:["прототип"],	r:0});
		TestNewWords.quests.push({t:1, q:"regular expression", a:["регулярное выражение"],	r:0});
		TestNewWords.quests.push({t:1, q:"regular", a:["регулярное"],	r:0});
		TestNewWords.quests.push({t:1, q:"expression", a:["выражение"],	r:0});
		TestNewWords.quests.push({t:1, q:"return", a:["вернуть"],	r:0});
		TestNewWords.quests.push({t:1, q:"string", a:["строка"],	r:0});
		TestNewWords.quests.push({t:1, q:"while", a:["пока"],	r:0});
		TestNewWords.quests.push({t:1, q:"with ", a:["с"],	r:0});
		TestNewWords.quests.push({t:1, q:"variable", a:["переменная"],	r:0});
		TestNewWords.quests.push({t:1, q:"undefined", a:["неопределено"],	r:0});
		TestNewWords.quests.push({t:1, q:"true", a:["истина"],	r:0});
		TestNewWords.quests.push({t:1, q:"try", a:["пытаться"],	r:0});
		TestNewWords.quests.push({t:1, q:"throw", a:["бросать"],	r:0});
		TestNewWords.quests.push({t:1, q:"switch", a:["переключатель"],	r:0});
		TestNewWordsHandler.shuffle();
	}
})()

