# Table structure for table `ep_archives`
CREATE TABLE `ep_archives`(`date` int(15) NOT NULL default '0',`hits` int(9) NOT NULL default '0',`visits` int(9) NOT NULL default '0') TYPE=MyISAM;
# Dumping data for table `ep_archives`
INSERT INTO `ep_archives` VALUES (0, 0, 0);
# --------------------------------------------------------
# Table structure for table `ep_stats`
CREATE TABLE `ep_stats`(`ip` varchar(15) NOT NULL default '',`hits` int(9) NOT NULL default '1',`visits` int(9) NOT NULL default '1',`time` int(15) NOT NULL default '0',PRIMARY KEY  (`ip`)) TYPE=MyISAM;