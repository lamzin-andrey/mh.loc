<?php
/**
 * @author Andrey Lamzin lamzin80@mail.ru
 * @class DssParser Создает объект линии из массива байт
*/
class DssLine {
	/** @property float startX*/
	public $startX;
	/** @property float endX*/
	public $endX;
	/** @property float startY*/
	public $startY;
	/** @property float endY*/
	public $endY;
	/** @property int color*/
	public $color;
	/**
	 * @param array $bytes
	*/
	public function __construct($bytes) {
		$i = 0;
		$num = array();
		$rawData = $bytes;
		$this->color = $color = ($bytes[2] * 256 * 256) + ($bytes[1] * 256) + $bytes[0];
		$this->startX = $startX = $this->_readPoint($bytes, 3);
		$this->startY = $startY = -1 * $this->_readPoint($bytes, 7);
		$this->endX = $endX = $this->_readPoint($bytes, 11);
		$this->endY = $endY = -1 * $this->_readPoint($bytes, 15);
	}
	/**
	 * @desc считать точку из массива байт
	 * @param array $bytes массив байт
	 * @param int   $start с какого байта начинать
	 * @return float
	*/
	private function _readPoint($bytes, $start) {
		$order = array(3, 2, 1, 0);
		$result = array();
		for ($i = 0, $j = $start; $i < 4; $i++) {
			$result[$order[$i]] = $bytes[$j];
			$j++;
		}
		$fNum = $this->_readFloat($result);
		return $fNum;
	}
	/**
	 * @desc Конвертировать массив байт в одно целое число
	 * @param array $bytes массив байт
	 * @return double
	*/
	private function _readFloat($bytes) {
		$bytes = array_values($bytes);
		$bData = pack('c*', $bytes[0], $bytes[1], $bytes[2], $bytes[3]);
		$v = (float)join('', unpack('f', $bData ));
		return $v;
	}
}
