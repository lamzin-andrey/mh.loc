-- MySQL
DROP TABLE IF EXISTS `sts_stat`;
CREATE TABLE IF NOT EXISTS `sts_stat` (
   id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL COMMENT '��������� ����.',
   uid INTEGER COMMENT 'users.id',
   word_id INTEGER COMMENT '����� ����� �� ����� sts.txt',
   definition TEXT COMMENT '�����������, ������ �������������',
   is_deleted INTEGER DEFAULT 0 COMMENT '������ ��� ���. ����� ���������� �� �������, �� ����� � cdbfrselectmodel ���� �������, ��� ������',
   date_create DATETIME COMMENT '����� ��������',
   delta INTEGER COMMENT '�������.  ����� ���������� �� �������, �� ����� � cdbfrselectmodel ���� �������, ��� ������'
)ENGINE=InnoDB  DEFAULT CHARSET=utf8;
