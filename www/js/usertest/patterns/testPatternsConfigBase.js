/**
 * Базовая конфигурация теста на знание слов, использующихся в тексте программ
 * */
(function () {
	$(document).ready(init);
	
	function init() {
		/** @var глобальный объект - экземпляр базового конфигуратора теста по паттернам*/
		window.TestPatterns = new TestEngine();
		TestPatterns.configTime(60);	//Конфигурация
		TestPatterns.defaultScorePerAnswer = 10;
		//Паттерны
		TestPatterns.quests.push({q:"Какие паттерны реализуют связку MVC?", a:"Наблюдатель, Компоновщик, Стратегия"});
		TestPatterns.quests.push({q:"Перечислить классы из реализации паттерна Observer, не включая посредника", a:"Observer, ConcreteObserver, Subject, ConcreteSubject"});
		TestPatterns.quests.push({q:"Перечислить методы и свойства класса Subject паттерна Observer", a:"attach(observer) detach(observer) notify() _observers"});
		TestPatterns.quests.push({q:"Перечислить методы и свойства класса Observer паттерна Observer", a:"update(subject) _subject"});
		TestPatterns.quests.push({q:"Перечислить методы и свойства класса ChangeManager паттерна Observer", a:"register(subject, observer) unregister(subject, observer) notify()"});
		
		//TestPatterns.randomize = true; //вопросы будут выводится случайным образом
		/** @desc Объект реализующий интерфейс представления данных теста, через него тест взаимодействует с DOM */
			TestPatterns.view = {
			setScore:function(v){
				$("#tptscore").text(v);
			},
			setTime: function(v){
				$("#tpttime_left").text(v);
			},
			clearPrevStatus: function() {
				$('#qsPTPlayscreen').removeClass('hide');
				$('#qsPTDonescreen').addClass('hide');
				$('#qsPTFailscreen').addClass('hide');
			},
			setQuest: function(v, answers, rule) {
				if (v == '') {
					v = '&nbsp;';
				}
				$('#tptanswer').val('');
				$("#tptquest").html(v);
				if (String(rule) == "undefined") {
					return;
				}
			},
			setBeginScreen: function(v){
				$('#qsPTFailscreen').addClass('hide');
				$('#qsPTPlayscreen').addClass('hide');
				$('#qsPTHelloScreen').removeClass('hide');
				$("#tptstartGame").prop('disabled', false);
				TestPatterns.state = 0;
			},
			setGameScreen: function(){
				$("#tptstartGame").prop('disabled', true);
				this.beginScreenSets = false;
			},
			setLives: function(v) {
				$("#tptlives").text(v);
			},
			setDoneOneAnswerScreen: function(){
				$('#qsPTPlayscreen').addClass('hide');
				$('#qsPTDonescreen').removeClass('hide');
				if (!$('#tptSuccessInfo').hasClass('hide')) {
					$('#tptSuccessInfo').addClass('hide');
				}
				$('#tptSuccess').html('Правильно!');
				return 1;
			},
			setFailOneAnswerScreen: function(){
				$('#tptErr').text('Ошибка!');
				$('#qsPTPlayscreen').addClass('hide');
				$('#qsPTFailscreen').removeClass('hide');
				return 2;
			},
			setGameOverScreen: function(){
				$('#tptErr').text('GAME OVER');
				if ( !this.beginScreenSets ) {
					this.beginScreenSets = true;
					var o = this;
					setTimeout(
						function () {
							o.setBeginScreen();
						},
						2000
					);
				}
			},
			getAnswer: function(){
				return $('#tptanswer').val();
			},
			setWinScreen: function(){
				this.clearPrevStatus();
				var s = 'Очень хорошо!';
				if (TestPatterns.lives == 1) {
					s = 'Хорошо!';
				}
				$('#tptSuccess').html(s);
				$('#tptSuccessInfo').removeClass('hide');
				$('#qsPTPlayscreen').addClass('hide');
				$('#qsPTDonescreen').removeClass('hide');
				//$("#tptstartGame").prop('disabled', false);
				var o = this;
				TestPatterns.state = 0;
				setTimeout(
					function () {
						o.setBeginScreen();
					},
					5000
				);
			}
		};
		
		TestPatterns.init();		//Запуск
		var C = TestPatterns.C;		//для более быстрого доступа
		$("#tptstartGame").prop('disabled', false); //кнопку "Начать тест" сделаем пока ннедоступной
		
		
		/** @desc Взаимодействие пользователя с тестом*/
		$('#tptstartGame').click( function() {
			$("#qsPTPlayscreen").removeClass('hide');
			$("#qsPTHelloScreen").addClass('hide');
			TestPatterns.state = C.START_GAME;
			TestPatterns.tick();
		});
		$('#tptOK').click( function() {
			TestPatterns.state = C.CHECK_ONE_RESULT;
			TestPatterns.tick();
			TestPatterns.tick();
		});
	}
})()

