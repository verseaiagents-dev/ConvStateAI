CREATE TABLE intents (
  id CHAR(36) PRIMARY KEY,
  code VARCHAR(64) UNIQUE,   -- örn. product_recommend, cart_add, order_checkout
  name VARCHAR(255),
  description TEXT,
  threshold DECIMAL(5,2) DEFAULT 0.75, -- benzerlik eşiği
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE intent_keywords (
  id CHAR(36) PRIMARY KEY,
  intent_id CHAR(36) NOT NULL,
  keyword VARCHAR(128) NOT NULL, -- örn. "öner", "tavsiye", "sepete ekle"
  weight DECIMAL(5,2) DEFAULT 1.0,
  FOREIGN KEY (intent_id) REFERENCES intents(id) ON DELETE CASCADE
);


CREATE TABLE event_templates (
  id CHAR(36) PRIMARY KEY,
  intent_id CHAR(36) NOT NULL,
  name VARCHAR(255),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (intent_id) REFERENCES intents(id) ON DELETE CASCADE
);

CREATE TABLE event_actions (
  id CHAR(36) PRIMARY KEY,
  event_template_id CHAR(36) NOT NULL,
  action_type ENUM('http_call','db_insert','log','notify') NOT NULL,
  config JSON NOT NULL, -- http: {url, method, headers, body}, db: {table, data}
  seq INT DEFAULT 1,    -- sıralı çalıştırma
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (event_template_id) REFERENCES event_templates(id) ON DELETE CASCADE
);

CREATE TABLE event_logs (
  id CHAR(36) PRIMARY KEY,
  intent_code VARCHAR(64),
  event_template_id CHAR(36),
  status ENUM('pending','success','failed'),
  request_payload JSON,
  response_payload JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
