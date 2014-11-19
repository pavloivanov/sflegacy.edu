CREATE TABLE portals_urls (id INT AUTO_INCREMENT, title VARCHAR(32), url VARCHAR(255) UNIQUE, PRIMARY KEY(id)) ENGINE = INNODB;
CREATE TABLE statistics (id INT AUTO_INCREMENT, hits INT, url VARCHAR(255) UNIQUE, portals_urls_id INT, INDEX portals_urls_id_idx (portals_urls_id), PRIMARY KEY(id)) ENGINE = INNODB;
ALTER TABLE statistics ADD CONSTRAINT statistics_portals_urls_id_portals_urls_id FOREIGN KEY (portals_urls_id) REFERENCES portals_urls(id);
