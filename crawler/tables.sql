--
-- Table structure for table `aptrent`
--

DROP TABLE IF EXISTS `aptrent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aptrent` (
  `aptName` varchar(99) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `monthlyPay` int(11) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `builtYear` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`,`aptName`,`area`,`monthlyPay`,`amount`,`year`,`month`, `day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;



DROP TABLE IF EXISTS `aptsale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aptsale` (
  `aptName` varchar(99) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `builtYear` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`,`aptName`,`area`,`amount`,`year`,`month`, `day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `flatrent`
--

DROP TABLE IF EXISTS `flatrent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flatrent` (
  `aptName` varchar(99) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `monthlyPay` int(11) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `builtYear` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`,`aptName`,`area`, `amount`,`monthlyPay`,`year`,`month`, `day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `flatsale`
--

DROP TABLE IF EXISTS `flatsale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flatsale` (
  `aptName` varchar(99) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `landArea` double DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `builtYear` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`,`aptName`,`area`,`amount`,`year`,`month`,`day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `houserent`
--

DROP TABLE IF EXISTS `houserent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `houserent` (
  `area` double DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `monthlyPay` int(11) DEFAULT NULL,
  `builtYear` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`,`monthlyPay`,`amount`,`year`,`month`, `day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `housesale`
--

DROP TABLE IF EXISTS `housesale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `housesale` (
  `type` varchar(99) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `landArea` double DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `builtYear` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`,`amount`,`year`,`month`,`day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;



DROP TABLE IF EXISTS `officetelrent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `officetelrent` (
  `aptName` varchar(99) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `monthlyPay` int(11) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `builtYear` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`,`aptName`,`area`, `monthlyPay`, `amount`,`year`,`month`, `day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `officetelsale`;
CREATE TABLE `officetelsale` (
  `aptName` varchar(99) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `builtYear` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`,`aptName`,`area`,`year`,`month`,`amount`, `day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `aptlots`;
CREATE TABLE `aptlots` (
  `aptName` varchar(99) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `rightAmount` int(11) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `builtYear` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`,`aptName`,`area`,`year`,`month`,`amount`, `day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `landsale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `landsale` (
  `type` varchar(99) DEFAULT NULL,
  `usedType` varchar(99) DEFAULT NULL,
  `area` double DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `avenue` int(11) DEFAULT 0,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `state` varchar(99) DEFAULT NULL,
  `city` varchar(99) DEFAULT NULL,
  `county` varchar(99) DEFAULT NULL,
  `region` varchar(99) DEFAULT "",
  KEY `all_index` (`state`,`city`,`county`,`region`, `type`, `usedType`, `year`,`month`,`amount`,`day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
