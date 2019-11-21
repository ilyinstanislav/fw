CREATE TABLE orders (
  id int(11) NOT NULL AUTO_INCREMENT,
  status tinyint(4) DEFAULT 0,
  price decimal(10, 2) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB;