//Программка вычисления факториала
/**
 * 
 * */
function task1() {
	var n = parseInt(readln('Введите n'));	
	if (n === 0 || n === '0') {
		writeln(n + "! = 1");
		return;
	}
	if (isNaN(n) || !n) {
		writeln('n должно быть числом');
		return;
	}
	var r = 1;
	for (var i = 1; i <= n; i++) {
		r *= i;
	}
	writeln(n + "! = " + r);
}
function boo () {
}
