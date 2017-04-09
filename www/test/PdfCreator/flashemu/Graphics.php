<?php
class Graphics {
    
    const TYPE_POINT = 1;
    const TYPE_RECT  = 2;
    
    /** Содержит объекты типа точки линий и четырехугольники
     * item[type=TYPE_POINT]: type, x, y, color, fill_color, thikness, is_start, is_begin_fill, is_end_fill
     * *  is_start 1 если был вызван moveTo
     * item[type=TYPE_RECT] : type, x, y, w, h, color, fill_color, thikness
     
    */
    public $_objects = array();
    
    private $_last_object = null; //для удобства проверки, что там у нас
    
    /** Текущий цвет */
    private $_color = '0x000000';
    /** Текущий цвет заливки*/
    private $_fill_color = '0xFFFFFF';
    /** Текущая толщина */
    private $_thikness = 0.25;
    
    //все тоже для нового
    /** Новый цвет */
    private $_new_color;
    /** Новый цвет заливки*/
    private $_new_fill_color;
    /** Новая толщина */
    private $_new_thikness;
    
    /** True if begin */
    private $_is_begin_fill = false;
    
    /** True if end */
    private $_is_end_fill = false;
    
    public function lineTo($x, $y) {
		$o = $this->_last_object;
		if (!$o) {
			throw new Exception('need call moveTo before drawLine');
		}
		if ($o['type'] == self::TYPE_RECT) {
			throw new Exception('need call moveTo before drawRect');
		}
		$t = self::TYPE_POINT;
		$this->_applyLineStyle($clr, $thi);
		if ($clr == $o['color']) {
			$clr = null;
		}
		if ($thi == $o['thikness']) {
			$thi = null;
		}
		$item = $this->_createPoint($t, $x, $y, $clr, $thi);
		$this->_last_object = $item;
		$this->_objects[] = $item;
    }
    /** Тупо берет последнюю точку в массиве, и если она is_start, перезаписывает ее, иначе добавляет новую в массив*/
    public function moveTo($x, $y) {
		$t = self::TYPE_POINT;
		$this->_applyLineStyle($clr, $thi);
		$point = $this->_createPoint($t, $x, $y, $clr, $thi, true);
		$o = $this->_last_object;
		if ($o && $o['is_start']) { //rewrite
			$i = count($this->_objects) - 1;
			$this->_objects[$i] = $point;
		} else {//append
			$this->_objects[] = $point;
		}
		$this->_last_object = $point;
    }
    public function drawRect($x, $y, $width, $height) {
		//item[type=TYPE_RECT] : type, x, y, w, h, color, fill_color, thikness
		$o = array(
			'type' => self::TYPE_RECT,
			'x'    => $x,
			'y'    => $y,
			'w'    => $w,
			'h'    => $h,
			'color' => $this->_color,
			'thikness' => $this->_thikness,
			'fill_color' => $this->fill_color
		);
		$this->_objects[]   = $o;
		$this->_last_object =  $o;
    }
    public function beginFill($color) {
		$this->_fill_color = $color;
		$this->_is_begin_fill = true;
    }
    public function setLineStyle($color, $thikness) {
		$this->_new_color = $color;
		$this->_new_thikness = $thikness;
    }
    public function endFill() {
		$o = $this->_objects;
		if ($this->_objects && is_array($o)) {
			$c = count($o);
			if ($c && $o[$c - 1]['type'] == self::TYPE_POINT) {
				$this->_objects[$c - 1]['is_end_fill'] = true;
			}
		} else {
			$this->_is_end_fill = true;
		}
    }
    
    private function _applyLineStyle(&$color, &$thikness) {
		if ($this->_new_color && $this->_new_color != $this->_color) {
			$color = $this->_color = $this->_new_color;
			$this->_new_color = null;
		} else {
			$color = $this->_color;
		}
		
		if ($this->_new_thikness && $this->_new_thikness != $this->_thikness) {
			$thikness = $this->_thikness = $this->_new_thikness;
			$this->_new_thikness = null;
		} else {
			$thikness = $this->_thikness;
		}
	}
    
    private function _createPoint($type, $x, $y, $color = null, $thikness = null, $is_start = false, $is_begin_fill = false, $is_end_fill = false) {
		if (!$is_begin_fill) {
			$is_begin_fill = $this->_is_begin_fill;
			$this->_is_begin_fill = false;
		}
		if (!$is_end_fill) {
			$is_end_fill = $this->_is_end_fill;
			$this->_is_end_fill = false;
		}
		$fill_color = false;
		if ($this->_last_object && $this->_last_object['fill_color'] != $this->_fill_color) {
			$fill_color = $this->_fill_color;
		}
		$o = array(
			'type' => $type,
			'x'    => $x,
			'y'    => $y,
			'color'    => $color,
			'fill_color'    => $fill_color,
			'thikness' => $thikness,
			'is_start' => $is_start,
			'is_begin_fill' => $is_begin_fill,
			'is_end_fill' => $is_end_fill
		);
		return $o;
	}
    
}
