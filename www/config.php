<?php
$doc_root = $_SERVER["DOCUMENT_ROOT"];
$web_root = str_replace($doc_root, "", APP_ROOT);
$web_root = str_replace("Z:\\home\\" . $_SERVER['HTTP_HOST'] ."\\www", "", $web_root);
$web_root = str_replace("\\", "/", $web_root);
define ("WEB_ROOT", $web_root); //private
define ("WORK_FOLDER", "");     //public 
define ("APP_CACHE_LIFE", 15 * 60); //private
define ("SUMMER_TIME", -3600);      //private (3)
define ("DEV_MODE", true);          //public  (2)
define ("STATIC_VERSION", '2');     //private (4)

/*=============================DATABASE=================================*/
define ("DB_HOST", 'localhost');
define ("DB_USER", 'root');
define ("DB_PASSWORD", '');
define ("DB_NAME", 'sts');
/*=============================CUSTOM=================================*/

/*=============================CUSTOM=================================*/
define ("USE_GUID_SESSION", false);//public (3)
$PRIVATE_CONSTANTS = array('APP_ROOT', 'APP_CACHE_LIFE', 'SUMMER_TIME', 'STATIC_VERSION', 'WORK_FOLDER');


$STD_CONST_PREFIXES = array('TRUE', 'FALSE', 'NULL', 'SQL', 'NIL', 'SID', 'RADIX', 'NAN', 'CODESET', 'NOEXP', 'INF', 'ERA', 'CRNC',
							'SORT', 'TYPE', 'ENC', 'THOUSEP', 'YESEX', 'SOMAX',
'E_', 'DEBUG_', 'ZEND_', 'PHP_', 'DEFAULT_', 'PEAR_', 'UPLOAD_', 'DATE_', 'SUNFUNCS_', 'LIBXML_', 'OPENSSL_', 'X509_', 'PKCS7_',
'PREG_', 'PCRE_', 'SQLITE3_', 'FORCE_', 'ZLIB_', 'CAL_', 'CURLOPT_', 'CURLCLOSEPOLICY_', 'CURLE_', 'CURLINFO_', 'CURLMSG_', 'CURLVERSION_', 'CURLM_', 
'CURLPROXY_', 'CURLSHOPT_', 'CURL_', 'CURLAUTH_', 'CURLFTPSSL_', 'CURLFTPAUTH_', 'CURLFTPMETHOD_', 'CURLMOPT_', 'CURLUSESSL_',
'CURLPAUSE_', 'CURLSSH_', 'CURLPROTO_', 'CURLGSSAPI_', 'CURLSSLOPT_', 'XML_', 'DOM_', 'DOMSTRING_', 'HASH_', 'MHASH_', 'FILEINFO_',
'INPUT_', 'FILTER_', 'FTP_', 'IMG_', 'GD_', 'PNG_', 'ICONV_', 'INTL_', 'ULOC_', 'GRAPHEME_', 'U_', 'IDNA_', 'JSON_', 'LDAP_', 'MB_',
'MCRYPT_', 'MSSQL_', 'CONNECTION_', 'INI_', 'M_', 'INFO_', 'CREDITS_', 'HTML_', 'ENT_', 'STR_', 'PATHINFO_', 'CHAR_', 'LC_', 'SEEK_',
'LOCK_', 'STREAM_', 'FILE_', 'FNM_', 'PSFS_', 'PASSWORD_', 'ABDAY_', 'DAY_', 'ABMON_', 'MON_', 'AM_', 'PM_', 'D_', 'T_', 'ERA_',
'ALT_', 'CRYPT_', 'DIRECTORY_', 'PATH_', 'SCANDIR_', 'GLOB_', 'LOG_', 'EXTR_', 'SORT_', 'CASE_', 'COUNT_', 'ASSERT_', 'IMAGETYPE_',
'DNS_', 'MYSQLI_', 'MYSQL_', 'POSIX_', 'IMAP_', 'OP_', 'CL_', 'FT_', 'ST_', 'CP_', 'SE_', 'SO_', 'SA_', 'LATT_', 'SOAP_', 'UNKNOWN_',
'XSD_', 'APACHE_', 'WSDL_', 'AF_', 'SOCK_', 'MSG_', 'SOL_', 'TCP_', 'MCAST_', 'IP_', 'IPV6_', 'SOCKET_', 'IPPROTO_', 'SCM_', 'EXIF_',
'XSL_', 'LIBXSLT_', 'LIBEXSLT_', 'MEMCACHE_', 'MONGO_', 'XDEBUG_', 'apc_register_serializer-', 'APC_', 'APCU_APC_');
