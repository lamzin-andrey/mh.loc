DROP TABLE IF EXISTS mapping;
CREATE TABLE mapping
(
	intf integer,
	floatf float ,
	doublef double ,
	money_f decimal(10, 2) ,
	str_4 varchar(4) ,
	text_long LONGTEXT ,
	text_small TINYTEXT ,
	bf BINARY,
	datetimef DATETIME ,
	booleanf BOOLEAN 
)engine=InnoDB DEFAULT CHARSET=utf8;
