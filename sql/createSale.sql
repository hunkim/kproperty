
Create Table HouseSale 
(
        fullLoc varchar(255),
        type varchar(255),
        area float,
        landArea float,
        date int,
        amount bigint,
        builtYear int(255),
        avenue varchar(255),

        year int,
        month int,
    
        city varchar(255),
        county varchar(255),
        region varchar(255),
        region1 varchar(255),
        region2 varchar(255)
);

ALTER TABLE HouseSale ADD INDEX (year, month, city, county, region);



--- 시군구 계약면적(㎡) 전월세구분   계약일 보증금(만원) 월세(만원)  건축년도    도로명
Create Table HouseRent 
(
        fullLoc varchar(255),
        area float,
        type varchar(255),
        date int,
        deposit bigint,
        monthly bigint,
        builtYear int,
        avenue varchar(255),

        year int,
        month int,
    
        city varchar(255),
        county varchar(255),
        region varchar(255),
        region1 varchar(255),
        region2 varchar(255)
);

--- APT: 시군구 본번  부번  단지명 전용면적(㎡) 계약일 거래금액(만원)    층   건축년도    도로명
Create Table APTSale 
(
        fullLoc varchar(255),
        mainNum int,
        secondNum int,
        buildName varchar(255),
        area float(255),
        date varchar(255),
        amount bigint,
        floor int,
        builtYear int(255),
        avenue varchar(255),

        year int,
        month int,
    
        city varchar(255),
        county varchar(255),
        region varchar(255),
        region1 varchar(255),
        region2 varchar(255)
);

--- 연립: 시군구 본번  부번  단지명 전용면적(㎡) 대지권면적(㎡)    계약일 거래금액(만원)    층   건축년도    도로명
Create Table FlatSale 
(
        fullLoc varchar(255),
        mainNum int,
        secondNum int,
        buildName varchar(255),
        area float,
        landArea float,
        date varchar(255),
        amount bigint,
        floor int,
        builtYear int(255),
        avenue varchar(255),

        year int,
        month int,
    
        city varchar(255),
        county varchar(255),
        region varchar(255),
        region1 varchar(255),
        region2 varchar(255)
);

--- APT: 시군구 본번  부번  단지명 전월세구분   전용면적(㎡) 계약일 보증금(만원) 월세(만원)  층   건축년도    도로명
Create Table APTRent 
(
        fullLoc varchar(255),
        mainNum int,
        secondNum int,
        buildName varchar(255),
        type varchar(255),
        area float,
        date int,
        deposit bigint,
        monthly bigint,
        floor int,
        builtYear int,
        avenue varchar(255),

        year int,
        month int,
    
        city varchar(255),
        county varchar(255),
        region varchar(255),
        region1 varchar(255),
        region2 varchar(255)
);

--- 연립: 시군구 본번  부번  단지명 전월세구분   전용면적(㎡) 계약일 보증금(만원) 월세(만원)  층   건축년도    도로명
Create Table FlatRent 
(
        fullLoc varchar(255),
        mainNum int,
        secondNum int,
        buildName varchar(255),
        type varchar(255),
        area float,
        date int,
        deposit bigint,
        monthly bigint,
        floor int,
        builtYear int,
        avenue varchar(255),

        year int,
        month int,
    
        city varchar(255),
        county varchar(255),
        region varchar(255),
        region1 varchar(255),
        region2 varchar(255)
);


