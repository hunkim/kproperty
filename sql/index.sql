 drop table aptrent,aptrent_agg,aptrent_reg,aptsale,aptsale_agg,aptsale_reg,flatrent_reg,flatsale,flatsale_agg,flatsale_reg,houserent ,houserent_agg ,houserent_reg ,housesale,housesale_agg ,housesale_reg


CREATE INDEX all_index ON aptrent (state,city,county,region, aptName, area,monthlyType,year,month);
CREATE INDEX all_index ON  flatrent (state,city,county,region, aptName, area,monthlyType,year,month );
CREATE INDEX all_index ON  houserent  (state,city,county,region,monthlyType,year,month);


CREATE INDEX all_index ON  housesale (state,city,county,region, year,month, amount);
CREATE INDEX all_index ON aptsale  (state,city,county,region, aptName, area,year,month,amount);
CREATE INDEX all_index ON  flatsale (state,city,county,region, aptName, area, year,month,amount);


select v1.state, v1.city, v1.county, v1.year, v1.a, v2.year, v2.a, v2.a-v1.a as d from
(select avg(amount/area) as a, year,  state, city, county from housesale where year = 2006 group by state, city, county) v1,
(select avg(amount/area) as a, year,  state, city, county from housesale where year = 2007 group by state, city, county) v2
where v1.city=v2.city and v1.county=v2.county and v1.state=v2.state order by d desc;
