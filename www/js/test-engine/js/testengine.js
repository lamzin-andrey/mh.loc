/**
 * @class Тип объекта, реализующий "движок" тестов 
*/
function TestEngine() {
	this.initTestEngine();
}
/**
 * @desc Инициализация движка тестов, содержит также описание конфигурации и интерфейса объекта - представления данных.
*/
TestEngine.prototype.initTestEngine = function() {
	//config - можно изменять для каждого объекта TestEngine
	this.quests = [/*
		//Первые два вопроса ожидают ответа в виде введенного текста
		{q:"Что такое осень?", a:"Это небо"}, //вопрос - ответ
		{q:"Переведите: Я буду читать", a:"I will read"},
		//Последний вопрос предполагает, что пользователь выберет один из вариантов
		{
		 	t:1,  //вопрос с варианиами ответа
		 	q:"Один в поле... ", //вопрос 
			a:["Не пойман", "Не вор", "Не воин", "Биткоин"], //варианты
			r:2 //правильный вариант
		}*/
	];
	this.minTime = 30 * 1000;
	this.maxTime = 500 * 1000;
	this.limit   = 5 * 1000;
	this.time    = 5 * 1000;
	this.defaultScorePerAnswer = 10;
	this.score = 0;
	this.beginLives = 2;
	this.lives = 2;
	this.failAnswerDelay = 1;
	this.successAnswerDelay = 1;
	this.randomize = false;
	//при текстовых ответах
	this.caseSensitive = false; 
	this.fullCompliance = false; 
	this.orderWordsSensitive = false; 
	this.dropSigns = true; 
	this.dropSignsRe = /[.,:!-?+]/gm; 
	//end config
	
	/**
	 * Каждому экземпляру надо передать объект - представление, реализующий такие функции:
	 * */
	this.viewIfc = { //view required functions
		setScore:0/*funciton(v){}*/, //Показывает количество очков v
		setTime: 0,					 //(v) показывает оставшеся для ответа на вопрос время
		setQuest: 0,				 //(String text[, Array answers, Number rule]) выводит текст вопроса, если переданы варианты answers, выводит варианты
		setBeginScreen: 0,			 //Обновляет экран перед началом теста
		setGameScreen: 0,			 //Обновляет экран в начале игры
		setLives: 0,				 //(v) выводит кол-во здоровья, жизней и т п 
		setDoneOneAnswerScreen: 0,   //Показывает экран или сообщение об успешном ответе на вопрос. Должен вернуть количество секунд, которое будети показываться этот экран
		setFailOneAnswerScreen: 0,   //Показывает экран или сообщение о неуспешном ответе на вопрос. Должен вернуть количество секунд, которое будети показываться этот экран
		setGameOverScreen: 0,		 //Показывает экран при провальном окончании игры
		getAnswer: 0,                //Должен возвращать ответ веденный пользователем или номер выбранного пользователем варианта при ответе на вопрос
		clearPrevStatus:0            //Вызывается перед показом нового вопроса. Можно использовать чтобы удалить сообщения об успешном или неуспешном ответе на вопрос
	};
	
	//constants
	this.C = {
		NOT_BEGIN:0,
		GET_QUEST:1,
		START_GAME:2,
		WAIT_ANSWER:5,
		CHECK_ONE_RESULT:10,
		SUCCESS_ONE_RESULT:11,
		SUCCESS_RESULT_SHOWING:12,
		FAIL_RESULT:15,
		FAIL_RESULT_SHOWING:16,
		GAME_OVER:20,
		WIN:25
	};
	//end constats
	
	
	//end view interface
	this.iterator = -1;
	this.interval = null;
	this.state = 0;
}
/**
 * @desc Установить время ожидания ответа на вопрос
 * @param {Number} seconds
*/
TestEngine.prototype.configTime = function(seconds) {
	var s = seconds;
	this.limit = s * 1000;
	this.time  = s * 1000;
}
/**
 * @desc Установить количество жизней
 * @param {Number} lives
*/
TestEngine.prototype.configLives = function(lives) {
	var l = lives;
	this.beginLives = l;
	this.lives = l;
}
/**
 * @desc Запуск таймера теста, логика вызова методов объекта в зависимотси от состояния процесса тестирования
*/
TestEngine.prototype.init = function() {
		var data = {};
		/*if (!this.checkView(data)) {
			throw new Error("view required functions: " + data.join(','));
		}*/
		//alert(this.iterator + ', LABEL_1');
		var o = this;
		this.interval = setInterval(
			function() {
				o.tick();
			}, 1*1000
		);
}
/**
 * @desc "Тик" таймера
*/
TestEngine.prototype.tick = function () {
	var o = this;
	switch (o.state) {
		case o.C.WIN:
			o.time = o.limit;
			o.view.setQuest('');
			o.winner();
			break;
		case o.C.CHECK_ONE_RESULT:
			o.checkOneResult();
			break;
		case o.C.WAIT_ANSWER:
			o.decrementTime();
			break;
		case o.C.SUCCESS_RESULT_SHOWING:
			o.decrementSuccessResultTime();
			break;
		case o.C.FAIL_RESULT_SHOWING:
			o.decrementFailResultTime();
			break;
		case o.C.FAIL_RESULT:
			o.checkLives();
			break;
		case o.C.SUCCESS_ONE_RESULT:
			o.incrementScores();
			break;
		case o.C.GAME_OVER:
			o.lives = o.beginLives;
			o.time = o.limit;
			o.iterator = -1;
			o.view.setQuest('');
			o.view.setGameOverScreen();
			break;
		case o.C.GET_QUEST:	
		case o.C.START_GAME:
			o.onGetQuest();
			break;
	}
}
/**
 * @desc Обратный отсчет времени при ожидании ответа на вопрос
*/
TestEngine.prototype.decrementTime = function() {
	this.time = this.time - 1 * 1000;
	if (this.time > 0) {
		this.view.setTime(this.time / 1000);
	} else {
		this.view.setTime(this.time / 1000);
		this.state = this.C.FAIL_RESULT;
	}
}
/**
 * @desc Обратный отсчет времени при показе экрана неудачного ответа на вопрос
*/
TestEngine.prototype.decrementFailResultTime = function() {
	this.failAnswerDelay--;
	if (this.failAnswerDelay <= 0) {
		this.failAnswerDelay = 1;
		if (this.iterator + 1 >= this.quests.length) {
			this.state = this.C.WIN;
			//this.view.setWinScreen();
		} else {
			this.state = this.C.GET_QUEST;
		}
	}
}
/**
 * @desc Вызывается при неверном ответе на вопрос. Уменьшает количество здоровья и при достижении нуля устанавливает состояние проигрыша
*/
TestEngine.prototype.checkLives = function() {
	if (this.lives--) {
		var d = parseInt(this.view.setFailOneAnswerScreen(), 10);
		this.failAnswerDelay = d ? d : this.failAnswerDelay;
		this.view.setLives(this.lives);
		this.state = this.C.FAIL_RESULT_SHOWING;
		this.time = this.limit;
		if (!this.lives) {
			this.state = this.C.GAME_OVER;
		}
	} else {
		this.state = this.C.GAME_OVER;
	}
}
/**
 * @desc Следующий вопрос
*/
TestEngine.prototype.nextQuest = function() {
	this.iterator++;
	//console.log('iterator = ' + this.iterator);
	if (this.iterator >= this.quests.length) {
		this.state = this.C.WIN;
	} else {
		this.view.setQuest(this.quests[this.iterator].q, this.quests[this.iterator].a, this.quests[this.iterator].r);
		this.state = this.C.WAIT_ANSWER;
	}
}
/**
 * @desc Проверка правильности введенного ответа
*/
TestEngine.prototype.checkOneResult = function() {
	var quest = this.quests[this.iterator], type = quest.t;
	if (!type) {
		if (this.isAnswersEqual()) {
			this.state = this.C.SUCCESS_ONE_RESULT;
		} else {
			this.state = this.C.FAIL_RESULT;
		}
	} else {
		if (quest.r == this.view.getAnswer()) {
			this.state = this.C.SUCCESS_ONE_RESULT;
		} else {
				this.state = this.C.FAIL_RESULT;
		}
	}
}
/**
 * @desc Увеличение очков при вводе верного ответа
*/
TestEngine.prototype.incrementScores = function() {
	this.score += this.defaultScorePerAnswer;
	this.view.setScore(this.score);
	var d = parseInt(this.view.setDoneOneAnswerScreen(), 10);
	this.successAnswerDelay = d ? d : this.successAnswerDelay;
	this.state = this.C.SUCCESS_RESULT_SHOWING;
}
/**
 * @desc Обратный отсчет времени при показе экрана удачного ответа на вопрос
*/
TestEngine.prototype.decrementSuccessResultTime = function() {
	this.successAnswerDelay--;
	if (this.successAnswerDelay <= 0) {
		this.successAnswerDelay = 1;
		if (this.iterator + 1 >= this.quests.length) {
			this.state = this.C.WIN;
			this.view.setWinScreen();
		} else {
			this.state = this.C.GET_QUEST;
		}
	}
}
/**
 * @desc Показ экрана удачного завершения игры
*/
TestEngine.prototype.winner = function() {
	this.view.setWinScreen();
}
/**
 * @desc Если this.randomize == true перемешивает вопросы
*/
TestEngine.prototype.shuffleQuests = function() {
	if (this.randomize) {
		var i = this.quests.length, copy = [], j;
		while (i--) {
			if (this.quests.length > 1) {
				j = this.random(0, this.quests.length - 1);
				copy.push( this.quests[j] );
				this.quests.splice(j, 1);
			} else {
				copy.push( this.quests[0] );
				break;
			}
		}
		this.quests = copy;
	}
	//console.log( this.quests);
}
/**
 * @desc Возвращает случайное число от min до max
*/
TestEngine.prototype.random = function (min, max) {
	var n, iOne = false;
	if (max - min == 1) {
		max++;
		iOne = true;
	}
	max = parseInt(max, 10);
	min = parseInt(min, 10);
	max = max ? max : 0;
	min = min ? min : 0;
	n = Math.random();
	n = Math.round(n * Math.pow(10, String(max).length ) );
	if (n < min) {
		n += min;
	}
	if (n > max) {
		n = n % max + min;
	}
	if (iOne && n == max) {
		n--;
	}
	return n;
}
/**
 * @desc событие запроса вопроса
*/
TestEngine.prototype.onGetQuest = function () {
	//console.log("st = " + this.state);
	var o = this;
	o.time = o.limit;
	o.view.setTime(o.limit / 1000);
	o.view.clearPrevStatus();
	if (o.state == o.C.START_GAME) {
		o.reset();
		o.state == o.C.GET_QUEST;
	}
	o.nextQuest();
}
/**
 * @desc Сбросить все в состояние не начатой игры
*/
TestEngine.prototype.reset = function(showBeginScreen) {
	var o = this;
	o.lives = o.beginLives;
	o.iterator = -1;
	o.shuffleQuests();
	o.view.setLives(o.beginLives);
	o.view.setScore(0);
	o.score = 0;
	o.view.setGameScreen();
	o.state = o.C.NOT_BEGIN;
	if (showBeginScreen) {
		o.view.setBeginScreen();
	}
}
/**
 * @desc Проверка правильности введенного текстового ответа
*/
TestEngine.prototype.isAnswersEqual = function() {
	var i, a, b;
	function _lc(s) { return s.toLowerCase();}
	function _split(s) {
		var ar = s.split(/\s/), b = [];
		i = ar.length;
		while(i--) {
			if (ar[i]) {
				b.push(ar[i]);
			}
		}
		return b;
	}
	a = String(this.view.getAnswer()), b = String(this.quests[this.iterator].a);
	if (!a) {
		return false;
	}
	if (!this.caseSensitive) {
		a = _lc(a);
		b = _lc(b);
	}
	if (this.fullCompliance) {
		return (a == b);
	}
	if (this.dropSigns) {
		a = a.replace(this.dropSignsRe, '');
		b = b.replace(this.dropSignsRe, '')
	}
	a = _split(a);
	b = _split(b);
	if (!this.orderWordsSensitive) {
		a = a.sort();
		b = b.sort();
	}
	i = a.length;
	while (i--) {
		if (a[i] != b[i]) {
			return false;
		}
	}
	return true;
}
