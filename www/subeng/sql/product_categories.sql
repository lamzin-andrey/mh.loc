DROP TABLE product_categories;
CREATE TABLE product_categories 
(
  id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
  parent_id INTEGER DEFAULT 0,
  name VARCHAR(255),
  is_accepted TINYINT DEFAULT 0,
  is_deleted TINYINT DEFAULT 0,
  delta INTEGER DEFAULT 0
)
