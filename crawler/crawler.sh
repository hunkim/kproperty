#!/bin/sh
php /home/ubuntu/kp/crawler/crawler.php housesale &
php /home/ubuntu/kp/crawler/crawler.php flatsale &
php /home/ubuntu/kp/crawler/crawler.php officetelsale &
php /home/ubuntu/kp/crawler/crawler.php aptlots&
php /home/ubuntu/kp/crawler/crawler.php landsale&

php /home/ubuntu/kp/crawler/crawler.php houserent&
php /home/ubuntu/kp/crawler/crawler.php aptrent&
php /home/ubuntu/kp/crawler/crawler.php flatrent&
php /home/ubuntu/kp/crawler/crawler.php officetelrent&

php /home/ubuntu/kp/crawler/crawler.php aptsale
php /home/ubuntu/kp/crawler/mkreg