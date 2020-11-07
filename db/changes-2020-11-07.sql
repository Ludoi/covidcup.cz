ALTER TABLE `results`
ADD `finish_time` timestamp NULL AFTER `start_time`;

ALTER TABLE `results`
CHANGE `start_time` `start_time` timestamp NULL AFTER `weatherid`;

UPDATE results SET finish_time = DATE_ADD( start_time, INTERVAL time_seconds SECOND);