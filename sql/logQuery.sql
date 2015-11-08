--- http://stackoverflow.com/questions/650238/how-to-show-the-last-queries-executed-on-mysql/678310#678310
SET GLOBAL log_output = 'TABLE';
SET GLOBAL general_log = 'ON';

-- Take a look at the table mysql.general_log
