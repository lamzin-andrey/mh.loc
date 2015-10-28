<?php
class Graphics {
    
    /** Содержит точки линий */
    public $_line_points = array();
    /**
     * Последний вызванный метод из lineTo / moveTo рисуем две точки только для lineTo если последний был moveTo
    */
    public $_last_line_method;
    /** Содержит цвета линий */
    public $_colors = array();
    /** Содержит прямоугольники */
    public $_rects = array();
    /** Текущий цвет линии */
    public $_rects = array();
    
    public function lineTo($x, $y) {
    }
    public function moveTo($x, $y) {
    }
    public function drawRect($x, $y, $width, $height) {
    }
    public function beginFill($color) {
    }
    public function setLineStyle($color, $thikness) {
    }
    public function endFill($color) {
    }
    
}
