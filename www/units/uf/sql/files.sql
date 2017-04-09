-- MySQL
DROP TABLE IF EXISTS `files`;
--
-- Структура таблицы `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Первичный ключ.',
  `is_deleted` int(11) DEFAULT '0' COMMENT 'Удален или нет.',
  `is_accepted` int(11) DEFAULT '0' COMMENT 'Одобрен или нет.',
  `date_create` datetime DEFAULT NULL COMMENT 'время создания',
  `delta` int(11) DEFAULT NULL COMMENT 'Позиция.',
  `name` varchar(64) DEFAULT NULL COMMENT 'Относительный путь к файлу от каталога files, for example /2015/10/filename.ext',
  `display_name` varchar(64) DEFAULT NULL COMMENT 'Отображаемое имя файла',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
