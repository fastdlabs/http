CREATE TABLE test_session(
  id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  session_id VARCHAR(48) NOT NULL ,
  title VARCHAR(32) NOT NULL,
  value VARCHAR(128) NOT NULL ,
  expire INT(10) NOT NULL DEFAULT 0,
  create_at INT(10) NOT NULL
)engine=innodb charset=utf8;