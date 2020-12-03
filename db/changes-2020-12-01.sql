CREATE TABLE `followers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `racerid` int(10) unsigned NOT NULL,
  `follow_racerid` int(10) unsigned NOT NULL,
  `created` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`racerid`) REFERENCES `cups_racers` (`id`),
  FOREIGN KEY (`follow_racerid`) REFERENCES `cups_racers` (`id`)
);