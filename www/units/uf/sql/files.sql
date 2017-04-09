-- MySQL
DROP TABLE IF EXISTS `files`;
--
-- ��������� ������� `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '��������� ����.',
  `is_deleted` int(11) DEFAULT '0' COMMENT '������ ��� ���.',
  `is_accepted` int(11) DEFAULT '0' COMMENT '������� ��� ���.',
  `date_create` datetime DEFAULT NULL COMMENT '����� ��������',
  `delta` int(11) DEFAULT NULL COMMENT '�������.',
  `name` varchar(64) DEFAULT NULL COMMENT '������������� ���� � ����� �� �������� files, for example /2015/10/filename.ext',
  `display_name` varchar(64) DEFAULT NULL COMMENT '������������ ��� �����',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
