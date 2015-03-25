<?php
class CViewHelper {
	/**
	 * @var Функция обратного вызова при рендеринге комментариев
	 * @see self::renderUlTree
	*/
	static public $UlTreeItemRenderCallback = null;
	
	/**
	 * @desc Рендерит элемнент списка комментариев
	 * @param array $commentInfo
	*/
	static public function renderComment($commentInfo) {
		$date_modify = '';
		$lang = utils_getCurrentLang();
		if ($commentInfo['date_modify'] != $commentInfo['date_create']) {
			$date_modify = '<div class="cmv_modify iblock"><img title="'. $lang['edit_time'] .'" src="'. WEB_ROOT .'/img/edit16.png">'. utils_dateE2R($commentInfo['date_modify']) .'</div>';
		}
		
		$s = '
		<div class="trow">
			<div class="tcell pseavatar"><img src="'. WEB_ROOT . '/img/user_say.png" alt"'. $commentInfo['name'] . ' ' . $commentInfo['surname'] .'" title="'. $commentInfo['name'] . ' ' . $commentInfo['surname'] .'"></div>
			<div class="tcell cmv_right_part">
				<div class="left userinfo">'. $commentInfo['name'] . ' ' . $commentInfo['surname'] .': </div>
				<div class="left cmv_title">'. $commentInfo['title'] .'</div>
				<div class="clearfix"></div>
				<div class="cmv_body">'. str_replace("\n", "<br>", $commentInfo['body']) .'</div>
			</div>
		</div>
		
		<div class="iblock left">
			<div class="cmv_created iblock"><img title="'. $lang['publish_time'] .'" src="'. WEB_ROOT .'/img/timer16.png">'. utils_dateE2R($commentInfo['date_create']) .'</div>
			'. $date_modify . '
		</div>
		<div class="iblock right">';
	
		
		if (sess('uid')) {
			//$s .= '<a href="#" class="right cmv_alink" data-id="'. $commentInfo['id'] .'"> '. $lang['Aswer_him'] .'</a>';
			$s .= '<div class="right cmv_created"><a href="#" class="right cmv_alink" data-id="'. $commentInfo['id'] .'">' . $lang['Aswer_him']  .'</a></div>';
		}
		if (sess('uid') == $commentInfo['uid']) {
			$s .= '<div class="right cmv_created"><a href="#" class="right cmv_elink" data-id="'. $commentInfo['id'] .'"><img  src="'. WEB_ROOT .'/img/edit16c.png">' . $lang['Edit'] .'</a></div>';
		}
		$s .= '</div><div class="clearfix cmv_bottom_view"></div>';
		return $s;
	}
	/**
	 * @desc Рендерит элемнент списка комментариев для страницы newcomments
	 * @param array $commentInfo
	*/
	static public function renderCommentForAdmin($commentInfo) {
		$date_modify = '';
		$lang = utils_getCurrentLang();
		if ($commentInfo['date_modify'] != $commentInfo['date_create']) {
			$date_modify = '<div class="cmv_modify"><img title="'. $lang['edit_time'] .'" src="'. WEB_ROOT .'/img/edit16.png">'. utils_dateE2R($commentInfo['date_modify']) .'</div>';
		}
		$s = '<div class="left userinfo">'. $commentInfo['name'] . ' ' . $commentInfo['surname'].'</div>
		<div class="left cmv_title">'. $commentInfo['title'] .'</div>
		<div class="clearfix"></div>
		<div class="left cmv_timestamps oh">
			<div class="clearfix"></div>
			<div class="cmv_created"><img title="'. $lang['publish_time'] .'" src="'. WEB_ROOT .'/img/timer16.png">'. utils_dateE2R($commentInfo['date_create']) .'</div>
			'. $date_modify .'
			<div class="clearfix"></div>
		</div>
		<div class="cmv_body">'. str_replace("\n", "<br>", $commentInfo['body']) .'</div>
		<div class="clearfix"></div>';
		$s .= '<a href="#" class="right cmv_elink" data-id="'. $commentInfo['id'] .'"><img class="e16c" src="'. WEB_ROOT .'/img/edit16c.png"> '. $lang['Edit'] .'</a>';
		if ($commentInfo['is_accept'] == 0) {
			$s .= '<a href="#" class="right cmv_acceptlink" data-id="'. $commentInfo['id'] .'">'. $lang['Accept'] .'</a>';
		}
		$s .= '<a href="#" class="right cmv_removelink" data-id="'. $commentInfo['id'] .'">'. $lang['Delete'] .'</a>';
		$s .= '<div class="clearfix"></div>';
		return $s;
	}
	
	/**
	 * @desc Рендерит дерево построенное CAbstractDbTree::buildTree в html UL список
	 * @param array  $data - результат работы Funcs::buildTree
	 * @param string $display_value будет выведено <li>$data[N][$display_value]</li>
	 * @param array  $data_attributes для каждого будет выведено <li data-{$data_attributes_item}=$data_attributes[$data_attributes_item]
	 * @param string $ul_css класс для списков UL, также автоматически добавляется level-N
	 * @param string $li_css класс для элементов списков LI
	 * @param int    $level уровень вложенности
	**/
	static public function renderUlTree($data, $display_value, $data_attributes, $ul_css, $li_css, $level = 1) {
		if (count($data)) {
			echo "<ul class=\"{$ul_css} level-{$level}\">\n";
			foreach ($data as $item) {
				$attr = self::_prepareUlTreeElemAttributes($item, $data_attributes);
				if (self::$UlTreeItemRenderCallback) {
					$class = 'CViewHelper';
					$method = self::$UlTreeItemRenderCallback;
					$item[$display_value] = $class::$method($item);
				}
				echo "<li class=\"{$li_css}\" {$attr} >{$item[$display_value]}</li>\n";
				if (isset($item['childs'])) {
					CViewHelper::renderUlTree($item['childs'], $display_value, $data_attributes, $ul_css, $li_css, $level + 1);
				}
			}
			echo "</ul>\n";
		}
	}
	/**
	 * @see renderUlTree
	 * @desc Готовит атрибуты для renderTableTree
	 * @param array  $item - элемент массива - результата работы Funcs::buildTree
	 * @param array  $data_attributes для каждого будет выведено <li data-{$data_attributes_item}=$data_attributes[$data_attributes_item]
	**/
	static private function _prepareUlTreeElemAttributes($item, $data_attributes) {
		$res = array();
		foreach ($data_attributes as $i) {
			if (isset($item[$i])) {
				$res[] = "data-{$i}={$item[$i]}";
			} 
		}
		$s = join(' ', $res);
		return $s;
	}
}
class H {
	static public function imgtitle($s) {
		return 'alt="' . $s . '" title="' . $s . '"';
	}
	static public function img($src, $title, $attrAssocArray = null) {
		$v ='<img src="' . $src . '" alt="' . $s . '" title="' . $s . '"';
		foreach ($attrAssocArray as $attr => $val) {
			$v .= ' ' . $attr . '="' . $val . '"';
		}
		$v .= '/>';
	}
}


class FV {
	static public $obj = null;
	
	static public function  i($id, $value = null, $isPassword = 0) {
		$type = "text";
		if ($isPassword) {
			$type = "password";
		}
		self::checkValue($value, $id);
		return '<input type="'.$type.'" name="'.$id.'" id="'.$id.'" value="'.$value.'" />';
	}
	static public function  checkbox($id, $label, $space = ' ') {
		self::checkValue($v, $id);
		$ch = '';
		if ($v) {
			$ch = 'checked="checked"';
		}
		return '<input type="checkbox" name="'.$id.'" id="'.$id.'" value="1" '.$ch.'/>' . $space . '<label for="'.$id.'">'.$label.'</label>';
	}
	static public function  radio($id, $name, $label, $value = null) {
		self::checkValue($value, $id);
		$ch = '';
		if ($value) {
			$ch = 'checked="checked"';
		}
		$label = str_replace('*', '<span class="red">*</span>', $label);
		return '<input type="radio" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$ch.'/> <label for="'.$id.'">'.$label.'</label>';
	}
	static public function  sub($id, $value = null) {
		self::checkValue($value, $id);
		return '<input type="submit" name="'.$id.'" id="'.$id.'" value="'.$value.'" />';
	}
	static public function  but($id, $value = null, $css = '', $dataattr = array()) {
		self::checkValue($value, $id);
		if ($css) {
			$css = ' class="' . $css . '" ';
		}
		$attr = '';
		foreach ($dataattr as $k => $i) {
			$attr .= "data-$k=\"$i\" ";
		}
		return '<input type="button" name="'.$id.'" id="'.$id.'" value="'.$value.'" ' . $css . ' ' . $attr . ' />';
	}
	static public function  inplab($id, $label, $value = null) {
		self::checkValue($value, $id);
		$label = str_replace('*', '<span class="red">*</span>', $label);
		return '<input type="text" name="'.$id.'" id="'.$id.'" value="'.$value.'" /> <label for="'.$id.'">'.$label.'</label>';
	}
	static public function  labinp($id, $label, $value = null, $maxlength = 0, $ispass = 0, $disabled = 0) {
		self::checkValue($value, $id);
		$label = str_replace('*', '<span class="red">*</span>', $label);
		$s =  '';
		if ($maxlength) {
			$s = 'maxlength="'.$maxlength.'"';
		}
		$type = "text";
		if ($ispass) {
			$type = "password";
		}
		$dis = '';
		if ($disabled) {
			$dis = 'disabled="disabled"';
		}
		return '<label for="'.$id.'">'.$label.'</label> <input type="'.$type.'" name="'.$id.'" id="'.$id.'" value="'.$value.'" '.$maxlength.' ' . $dis . '/>';
	}
	static public function  hid($id, $value = null ) {
		self::checkValue($value, $id);
		$type = "hidden";
		return '<input type="'.$type.'" name="'.$id.'" id="'.$id.'" value="'.$value.'"/>';
	}
	static private function checkValue(&$value, $id) {
		if ($value ===  null && @self::$obj->$id) {
			$value = self::$obj->$id;
		}
	}
}
