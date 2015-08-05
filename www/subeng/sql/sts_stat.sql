-- MySQL
DROP TABLE IF EXISTS `sts_stat`;
CREATE TABLE IF NOT EXISTS `sts_stat` (
   id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL COMMENT 'Первичный ключ.',
   uid INTEGER COMMENT 'users.id',
   word_id INTEGER COMMENT 'Номер слова из файла sts.txt',
   definition TEXT COMMENT 'Определение, данное пользователем',
   is_deleted INTEGER DEFAULT 0 COMMENT 'Удален или нет. Может называться по другому, но тогда в cdbfrselectmodel надо указать, как именно',
   date_create DATETIME COMMENT 'время создания',
   delta INTEGER COMMENT 'Позиция.  Может называться по другому, но тогда в cdbfrselectmodel надо указать, как именно'
)ENGINE=InnoDB  DEFAULT CHARSET=utf8;
