#/bin/sh
goaccess -f /var/log/apache2/access.log -a | mailx -a 'Content-Type: text/html' -s "KProperty Log: `date`" hunkim@gmail.com
