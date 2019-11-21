CREATE TABLE order_products (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_order_products_order_id FOREIGN KEY (order_id)
        REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT FK_order_products_product_id FOREIGN KEY (product_id)
        REFERENCES products (id) ON DELETE CASCADE
)
ENGINE = INNODB;