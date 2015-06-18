ALTER TABLE  `document` ADD  `user_id` INT NOT NULL AFTER  `id`;
ALTER TABLE  `document` ADD INDEX  `idx_user` (  `user_id` );
update `document` set user_id = 2;

ALTER TABLE  `users` ADD  `isactive` TINYINT( 1 ) NOT NULL ,
ADD  `storage_backend` VARCHAR( 10 ) NOT NULL;

update `users` set isactive = 1, storage_backend = 'dropbox';

ALTER TABLE  `document` ADD  `file_md5` VARCHAR( 40 ) NULL AFTER  `last_found`;

ALTER TABLE  `doc_group` ADD  `user_id` INT NOT NULL AFTER  `id`;

-- token table!
CREATE TABLE `users_dropbox_oauth_tokens`
(
	`uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`userID` int(10) unsigned NOT NULL,
	`token` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
	PRIMARY KEY (`uid`),
	UNIQUE KEY `userID` (`userID`)
)
ENGINE=InnoDB;

