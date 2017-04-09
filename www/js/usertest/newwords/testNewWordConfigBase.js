/**
 * Базовая конфигурация теста на знание слов, использующихся в тексте программ
 * */
(function () {
	$(document).ready(init);
	
	function init() {
		/**
		 * @desc Объект хранящий функцию shuffle для инициализации "неправильных" вариантов слов
		*/
		window.TestNewWordsHandler = {};
		/**
		 * @desc Добавляет к каждому слову неправильный вариант ответа, переставляет варианты ответа на вопрос
		*/
		window.TestNewWordsHandler.shuffle = function() {
			var L = TestNewWords.quests.length, i = L, j, o, arr = TestNewWords.quests, word, src;
			while (i--) {
				o = arr[i];
				j = TestNewWords.random(0, L - 1);
				while (i == j) {
					j = TestNewWords.random(0, L - 1);
				}
				word = arr[j].a[ arr[j].r ];
				src  = arr[i].a[ arr[i].r ];
				j = TestNewWords.random(0, 500) % 2;
				if (j) {
					j = 1;
				}
				TestNewWords.quests[i].r = j;
				TestNewWords.quests[i].a[j] = src;
				j++;
				if (j > 1) {
					j = 0;
				}
				TestNewWords.quests[i].a[j] = word;
			}
			//console.log(arr);
		}
		/** @var глобальный объект - экземпляр базового конфигуратора теста на новые слова*/
		window.TestNewWords = new TestEngine();
		TestNewWords.randomize = true; //вопросы будут выводится случайным образом
		/** @desc Объект реализующий интерфейс представления данных теста, через него тест взаимодействует с DOM */
			TestNewWords.view = {
			setScore:function(v){
				$("#score").text(v);
			},
			setTime: function(v){
				$("#time_left").text(v);
			},
			clearPrevStatus: function() {
				$('#qsTNWPlayscreen').removeClass('hide');
				$('#qsTNWDonescreen').addClass('hide');
				$('#qsTNWFailscreen').addClass('hide');
			},
			setQuest: function(v, answers, rule) {
				if (v == '') {
					v = '&nbsp;';
				}
				$('#tnwanswer').val('');
				$('#variants').html('');
				$("#quest").html(v);
				if (String(rule) == "undefined") {
					$('#variants').innerHTML = '';
					return;
				}
				$(answers).each(
					function(i, questText) {
						var btn = document.createElement('button');
						btn.setAttribute('data-n', i);
						$(btn).text(questText);
						$(btn).click(
							function(evt) {
								$('#variants button').prop('disabled', true);
								$('#tnwanswer').val( evt.target.getAttribute('data-n') );
								TestNewWords.state = C.CHECK_ONE_RESULT;
								//TestNewWords.checkOneResult();
								TestNewWords.tick();
								TestNewWords.tick();
							}
						);
						$('#variants').append( btn );
						$('#variants button').prop('disabled', false);
					}
				);
				$('#variants').append( $('<div class="clearfix"></div>') );
			},
			setBeginScreen: function(v){
				$('#qsTNWFailscreen').addClass('hide');
				$('#qsTNWPlayscreen').addClass('hide');
				$('#qsTNWHelloScreen').removeClass('hide');
				$("#tnwstartGame").prop('disabled', false);
				TestNewWords.state = 0;
			},
			setGameScreen: function(){
				$("#tnwstartGame").prop('disabled', true);
				this.beginScreenSets = false;
			},
			setLives: function(v) {
				$("#lives").text(v);
			},
			setDoneOneAnswerScreen: function(){
				$('#qsTNWPlayscreen').addClass('hide');
				$('#qsTNWDonescreen').removeClass('hide');
				if (!$('#tnwSuccessInfo').hasClass('hide')) {
					$('#tnwSuccessInfo').addClass('hide');
				}
				$('#tnwSuccess').html('Правильно!');
				return 1;
			},
			setFailOneAnswerScreen: function(){
				$('#tnwErr').text('Ошибка!');
				$('#qsTNWPlayscreen').addClass('hide');
				$('#qsTNWFailscreen').removeClass('hide');
				return 2;
			},
			setGameOverScreen: function(){
				$('#tnwErr').text('GAME OVER');
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
				return $('#tnwanswer').val();
			},
			setWinScreen: function(){
				this.clearPrevStatus();
				var s = 'Очень хорошо!';
				if (TestNewWords.lives == 1) {
					s = 'Хорошо!';
				}
				$('#tnwSuccess').html(s);
				$('#tnwSuccessInfo').removeClass('hide');
				$('#qsTNWPlayscreen').addClass('hide');
				$('#qsTNWDonescreen').removeClass('hide');
				//$("#tnwstartGame").prop('disabled', false);
				var o = this;
				TestNewWords.state = 0;
				setTimeout(
					function () {
						o.setBeginScreen();
					},
					5000
				);
			}
		};
		TestNewWords.configTime(5);	//Конфигурация
		TestNewWords.defaultScorePerAnswer = 2;
		TestNewWords.init();		//Запуск
		var C = TestNewWords.C;		//для более быстрого доступа
		$("#tnwstartGame").prop('disabled', false); //кнопку "Начать тест" сделаем пока ннедоступной
		
		
		/** @desc Взаимодействие пользователя с тестом*/
		$('#tnwstartGame').click( function() {
			$("#qsTNWPlayscreen").removeClass('hide');
			$("#qsTNWHelloScreen").addClass('hide');
			TestNewWords.state = C.START_GAME;
			TestNewWords.onGetQuest();
		});
	}
})()

