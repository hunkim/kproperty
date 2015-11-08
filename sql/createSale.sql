
Create Table Sale 
(
    fullCity varchar(255),
        type varchar(255),
        area varchar(255),
        landArea varchar(255),
        date varchar(255),
        amount bigint,
        builtYear varchar(255),
        avenue varchar(255),

        year int,
        month int,
    
        city varchar(255),
        county varchar(255),
        region varchar(255),
        region1 varchar(255),
        region2 varchar(255)
);


ALTER TABLE Sale ADD INDEX (year, month, city, county, region);
