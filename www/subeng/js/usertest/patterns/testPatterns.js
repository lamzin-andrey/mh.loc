/**
 * @desc Инициализация окошка для показа теста на новые слова
 * */
(function () {
	$(document).ready(init);
	function init() {
		function reinitTest() {
			TestPatterns.reset(1);
			$('.popup-content').removeClass('bgnone');
		}
		$('#patternTestRun').click(
			function() {
				appWindow('qs-test-patterns-wrap', 'Проверь себя', reinitTest);
				$('.popup-content').addClass('bgnone');//TODO удалить класс при закрытии окна
				$('#qs-test-patterns').css('background', 'url("' + WEB_ROOT + '/img/wordtest/bg3.png")');
			}
		);
	}
})()
