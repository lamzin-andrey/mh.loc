<?php
class EmailValidator {
	/**
	 * Валидация формата email
	 * @return bool
	*/
	public static function format($sEmail, $sPattern = "#^[\w\.]+[^\.]@[\w]+\.[\w]{2,4}#")
	{
		return preg_match($sPattern, $sEmail, $m);
	}
	/**
	 * Валидация длины перед @
	 * @return bool
	*/
	public static function addressLength($sEmail, $nMinLength = 1, $nMaxLength = 20)
	{
		$a = explode('@', $sEmail);
		$s = $a[0];
		$n = strlen($s);
		return ( $n >= $nMinLength && $n <= $nMaxLength);
	}
	/**
	 * Валидация допустимых доменов
	 * @return bool
	*/
	public static function allowDomain($sEmail, $aAllowDomains)
	{
		$a = explode('.', $sEmail);
		$s = array_pop($a);
		foreach ($aAllowDomains as $sDomain) {
			if ($s == $sDomain) {
				return true;
			}
		}
		return false;
	}
	
}
