// ALTER TABLE `my_table` ADD INDEX (`my_column`)

mysql> desc housesale;
+-----------+--------------+------+-----+---------+-------+
| Field     | Type         | Null | Key | Default | Extra |
+-----------+--------------+------+-----+---------+-------+
| _id       | varchar(255) | NO   | PRI | NULL    |       |
| fullLoc   | varchar(255) | YES  |     | NULL    |       |
| type      | varchar(255) | YES  |     | NULL    |       |
| area      | double       | YES  |     | NULL    |       |
| landArea  | double       | YES  |     | NULL    |       |
| day       | int(11)      | YES  |     | NULL    |       |
| amount    | int(11)      | YES  |     | NULL    |       |
| builtYear | int(11)      | YES  |     | NULL    |       |
| avenue    | varchar(255) | YES  |     | NULL    |       |
| year      | int(11)      | YES  |     | NULL    |       |
| month     | int(11)      | YES  |     | NULL    |       |
| state     | varchar(255) | YES  |     | NULL    |       |
| city      | varchar(255) | YES  |     | NULL    |       |
| county    | varchar(255) | YES  |     | NULL    |       |
| region    | varchar(255) | YES  |     | NULL    |       |
+-----------+--------------+------+-----+---------+-------+
15 rows in set (0.00 sec)

+-------------+--------------+------+-----+---------+-------+
| Field       | Type         | Null | Key | Default | Extra |
+-------------+--------------+------+-----+---------+-------+
| _id         | varchar(255) | NO   | PRI | NULL    |       |
| fullLoc     | varchar(255) | YES  |     | NULL    |       |
| num1        | varchar(255) | YES  |     | NULL    |       |
| num2        | varchar(255) | YES  |     | NULL    |       |
| aptName     | varchar(255) | YES  |     | NULL    |       |
| monthlyType | varchar(255) | YES  |     | NULL    |       |
| area        | double       | YES  |     | NULL    |       |
| day         | int(11)      | YES  |     | NULL    |       |
| deposit     | int(11)      | YES  |     | NULL    |       |
| monthlyPay  | int(11)      | YES  |     | NULL    |       |
| floor       | int(11)      | YES  |     | NULL    |       |
| builtYear   | int(11)      | YES  |     | NULL    |       |
| avenue      | varchar(255) | YES  |     | NULL    |       |
| year        | int(11)      | YES  |     | NULL    |       |
| month       | int(11)      | YES  |     | NULL    |       |
| state       | varchar(255) | YES  |     | NULL    |       |
| city        | varchar(255) | YES  |     | NULL    |       |
| county      | varchar(255) | YES  |     | NULL    |       |
| region      | varchar(255) | YES  |     | NULL    |       |
+-------------+--------------+------+-----+---------+-------+

ALTER TABLE aptrent ADD INDEX (state,city,county,region, aptName, area,monthlyType,year,month );
ALTER TABLE flatrent ADD INDEX (state,city,county,region, aptName, area,monthlyType,year,month );
ALTER TABLE houserent ADD INDEX (state,city,county,region,monthlyType,year,month);


ALTER TABLE housesale ADD INDEX (state,city,county,region, year,month, amount);
ALTER TABLE aptsale ADD INDEX (state,city,county,region, aptName, area,year,month,amount);
ALTER TABLE flatsale ADD INDEX (state,city,county,region, aptName, area, year,month,amount);


select v1.state, v1.city, v1.county, v1.year, v1.a, v2.year, v2.a, v2.a-v1.a as d from
(select avg(amount/area) as a, year,  state, city, county from housesale where year = 2006 group by state, city, county) v1,
(select avg(amount/area) as a, year,  state, city, county from housesale where year = 2007 group by state, city, county) v2
where v1.city=v2.city and v1.county=v2.county and v1.state=v2.state order by d desc;
