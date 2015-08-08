-- MySQL
DROP TABLE IF EXISTS `users`;
--
-- ��������� ������� `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '��������� ����.',
  `pwd` varchar(32) DEFAULT NULL COMMENT '������',
  `email` varchar(64) DEFAULT NULL COMMENT 'email',
  `guest_id` varchar(32) DEFAULT NULL COMMENT 'md5( ip datetime) ���������� ������������ ������������ ����',
  `last_access_time` datetime DEFAULT NULL COMMENT '����� ���������� ��������� � �����',
  `is_deleted` int(11) DEFAULT '0' COMMENT '������ ��� ���. ����� ���������� �� �������, �� ����� � cdbfrselectmodel ���� �������, ��� ������',
  `date_create` datetime DEFAULT NULL COMMENT '����� ��������',
  `delta` int(11) DEFAULT NULL COMMENT '�������.  ����� ���������� �� �������, �� ����� � cdbfrselectmodel ���� �������, ��� ������',
  `name` varchar(64) DEFAULT NULL COMMENT '��� ������������',
  `surname` varchar(64) DEFAULT NULL COMMENT '������� ������������',
  `role` int(11) DEFAULT '0' COMMENT '���� ������������ 0 - ������������ 1 - ��������� - 2 - �����',
  `current_task` varchar(7) DEFAULT NULL COMMENT '��������� � ������ ������ ������ ������: �������:�������',
  `recovery_hash` varchar(32) DEFAULT NULL COMMENT '��� md5 ��� �������������� ������',
  `recovery_hash_created` datetime DEFAULT NULL COMMENT '����� ������� ��� ������������',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
