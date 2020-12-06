CREATE TABLE `results_racer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resultid` int(10) unsigned NOT NULL,
  `categoryid` int(10) unsigned NULL,
  `pos` int(10) unsigned NOT NULL,
  `points` decimal(8,2) NOT NULL,
  FOREIGN KEY (`resultid`) REFERENCES `results` (`id`),
  FOREIGN KEY (`categoryid`) REFERENCES `categories` (`id`),
  PRIMARY KEY `id` (`id`)
);