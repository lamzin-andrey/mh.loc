<?php
require_once dirname(__FILE__) . '/DssLine.php';
/**
 * @author Andrey Lamzin lamzin80@mail.ru
 * @class DssParser Парсит бинарный dss файл в массив данных
*/
class DssParser {
	/** Говорит о том, что в метод readUnsignedInt пришло невалидное количество байт*/
	const INVALID_INT = 1;
	/** @proprety lines array of struct:array{startX, endX, startY, endY}*/
	public $lines = array();
	
	public function parseData($file) {
		$hStart  = 0;
		$shStart = 12;
		$lnStart = 12 + 24;
		$lStart  = 12 + 24 + 4;
		$lineLength = 3 + 4*4;
		$i = 0;
		$sub = array();
		$linesData = array();
		$line = array();
		$lineStart = false;
		$lineEnd = false;
		$lineNum = floor((filesize($file) - $lStart) / $lineLength);
		$krat = 0;
		$dk = 0;
		$di = 0;
		$hstr = '';
		$fileHandler = fopen($file, 'r');
		$header = array();
		$subheader = array();
		while (!feof($fileHandler)) {
			$byte = ord(fread($fileHandler, 1));
			if (($i >= 0) && ($i < $shStart))  {
				$header[$i] = $byte;
			} else if (($i >= $shStart) && ($i < $lnStart)) {
				if ($i == $shStart) {
					$header = array_map(function($o){ return chr($o); }, $header);
					$hstr = join('', $header);
					if (substr($hstr, 3, 8) != "DSHP0JMX") {
						die ('Fail: header = '. substr($hstr, 3, 5));
					}
				}
				$subheader[$i - $shStart] = $byte;
			} else if (($i >= $lnStart) && ($i < $lStart)) {
				$sub[3 - ($i - $lnStart)] = $byte;
			} else if (($i >= $lStart) && ($i < ($lStart + ($lineNum) * $lineLength + 1))) {
				if ($i == $lStart) {
					$lineNum = $this->_readUnsignedInt($sub);
				}
				$krat = (($i - $lStart) / $lineLength);
				if ($krat == floor($krat)) {
					$dk = $krat;
					if ($dk > 0) {
						$this->lines[$dk - 1] = new DSSLine($line);
					}
					$lineEnd = true;
				} else {
					$lineEnd = false;
				}
				$linesData[$i - $lStart] = $byte;
				$di = $dk * $lineLength + $lStart;

				$line[$i - $di] = $byte;
			}
			$i++;
		}
		fclose($fileHandler);
	}
	/**
	 * Считывает целое число из массива байт в big endian порядке
	 * @param array $data ассив с целыми числами - значениями байт составляющих целое
	 * @return int
	*/
	private function _readUnsignedInt($data) {
		$data = $data;
		$sz = count($data);
		$format = '';
		$bData = "";
		switch ($sz) {
			case 1:
				$format = 'C'; //unsigned char
				$bData = pack('c*', $data[0]);
				break;
			case 2:
				$format = 'n'; //unsigned short big endian
				$bData = pack('c*', $data[0], $data[1]);
				break;
			case 4:
				$format = 'N'; //unsigned long big endian
				$bData = pack('c*', $data[0], $data[1], $data[2], $data[3]);
				break;
			case 8:
				$format = 'J'; //unsigned long long big endian
				$bData = pack('c*', $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
				break;
		}
		if (!$format) {
			throw new Exception("Invalid quantity bytes for integer", self::INVALID_INT);
		}
		
		if ($format == 'C') {
			$v = (int)join('', unpack($format, $bData ));
		} else {
			$v = (int)join('', unpack($format, $bData ));
		}
		$v = (int)join('', unpack($format, $bData ));
		return $v;
	}
}
