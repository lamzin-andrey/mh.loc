<?php
require_once dirname(__FILE__) . '/Graphics.php';

class Sprite {
    /** @property Graphics */
    public $graphics;//TODO it
    /** @property int x */
    public $x;
    /** @property int y */
    public $y;
    /** @property int width */
    public $width;
    /** @property int height */
    public $height;
    /** @property array _childs */
    private $_childs;
    
    public function __construct() {
       $this->graphics = new Graphics(); 
    }
    
    public function addChild($clip) {
        $this->_childs[] = $clip;
    }
    /**
     * @return Clip or Null
    */
    public function getChildAt($i) {
        if (isset($this->_childs[$i])) {
            return $this->_childs[$i];
        }
        return null;
    }
    /**
     * @return Clip or Null
    */
    public function getChildByName($name) {
        $a = $this->_childs;
        foreach ($a as $clip) {
            if (isset($clip->name) && $clip->name == $name) {
                return clip;
            }
        }
        return null;
    }
}
