-- MySQL
DROP TABLE IF EXISTS `users`;

CREATE TABLE IF NOT EXISTS `users`
(
   id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL COMMENT 'Первичный ключ.',
   pwd VARCHAR(32) COMMENT 'пароль',
   email    VARCHAR(64) COMMENT 'email',
   guest_id VARCHAR(32) COMMENT 'md5( ip datetime) анонимного пользователя загрузившего файл',
   last_access_time DATETIME COMMENT 'время последнего обращения к файлу',
   is_deleted INTEGER DEFAULT 0 COMMENT 'Удален или нет. Может называться по другому, но тогда в cdbfrselectmodel надо указать, как именно',
   date_create DATETIME COMMENT 'время создания',
   delta INTEGER COMMENT 'Позиция.  Может называться по другому, но тогда в cdbfrselectmodel надо указать, как именно'
)ENGINE=InnoDB  DEFAULT CHARSET=utf8;
