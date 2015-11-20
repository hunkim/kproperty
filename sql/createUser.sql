
CREATE USER 'trend@localhost' IDENTIFIED BY 'only!trend!';
GRANT ALL PRIVILEGES ON trend.* TO 'trend'@'localhost';


CREATE USER 'trend'@'%' IDENTIFIED BY 'only!trend!';
GRANT ALL PRIVILEGES ON trend.* TO 'trend'@'%';
