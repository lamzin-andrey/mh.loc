-- MySQL
DROP TABLE IF EXISTS `users`;
--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Первичный ключ.',
  `pwd` varchar(32) DEFAULT NULL COMMENT 'пароль',
  `email` varchar(64) DEFAULT NULL COMMENT 'email',
  `guest_id` varchar(32) DEFAULT NULL COMMENT 'md5( ip datetime) анонимного пользователя загрузившего файл',
  `last_access_time` datetime DEFAULT NULL COMMENT 'время последнего обращения к файлу',
  `is_deleted` int(11) DEFAULT '0' COMMENT 'Удален или нет. Может называться по другому, но тогда в cdbfrselectmodel надо указать, как именно',
  `date_create` datetime DEFAULT NULL COMMENT 'время создания',
  `delta` int(11) DEFAULT NULL COMMENT 'Позиция.  Может называться по другому, но тогда в cdbfrselectmodel надо указать, как именно',
  `name` varchar(64) DEFAULT NULL COMMENT 'Имя пользователя',
  `surname` varchar(64) DEFAULT NULL COMMENT 'Фамилия пользователя',
  `role` int(11) DEFAULT '0' COMMENT 'Роль пользователя 0 - пользователь 1 - модератор - 2 - админ',
  `current_task` varchar(7) DEFAULT NULL COMMENT 'Выбранная в данный момент задача формат: Вариант:Задание',
  `recovery_hash` varchar(32) DEFAULT NULL COMMENT 'Хэш md5 для восстановления пароля',
  `recovery_hash_created` datetime DEFAULT NULL COMMENT 'Время которое хеш действителен',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
