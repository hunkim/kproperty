
CREATE USER 'trend@localhost' IDENTIFIED BY 'only!trend!';
GRANT ALL PRIVILEGES ON trend.* TO 'trend'@'localhost';


CREATE USER 'trend'@'localhost' IDENTIFIED BY 'only!trend!';
GRANT ALL PRIVILEGES ON trend.* TO 'trend'@'localhost';

CREATE USER 'trend'@'localhost' IDENTIFIED BY 'only!trend!';
GRANT ALL PRIVILEGES ON rtrend.* TO 'trend'@'localhost';