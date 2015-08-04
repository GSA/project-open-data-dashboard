DROP TABLE IF EXISTS `users_auth`;

CREATE TABLE `users_auth` (
  `user_id` int(8) NOT NULL,
  `username` varchar(255) CHARACTER SET latin1 NOT NULL,
  `username_url` varchar(255) CHARACTER SET latin1 NOT NULL,
  `name_full` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `provider_url` text CHARACTER SET latin1 NOT NULL,
  `provider_user_id` int(12) NOT NULL,
  `token` text CHARACTER SET latin1 NOT NULL,
  `provider` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT 'github',
  `permissions` varchar(256) CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;