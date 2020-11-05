ALTER TABLE `measurements`
    DROP FOREIGN KEY `measurements_ibfk_2`;

ALTER TABLE `measurements`
    DROP INDEX `routeid`;

ALTER TABLE `measurements`
    CHANGE `routeid` `raceid` int(10) unsigned NOT NULL AFTER `userid`;

ALTER TABLE `measurements`
    ADD INDEX `raceid` (`raceid`);

ALTER TABLE `measurements`
    ADD FOREIGN KEY (`raceid`) REFERENCES `cups_routes` (`id`);

ALTER TABLE `measurements`
    DROP FOREIGN KEY `measurements_ibfk_1`;

ALTER TABLE `measurements`
    DROP INDEX `userid`;

ALTER TABLE `measurements`
    CHANGE `userid` `racerid` int(10) unsigned NOT NULL AFTER `id`;

ALTER TABLE `measurements`
    ADD INDEX `racerid` (`racerid`);

ALTER TABLE `measurements`
    ADD FOREIGN KEY (`racerid`) REFERENCES `cups_racers` (`id`);


ALTER TABLE `messages`
    DROP FOREIGN KEY `messages_ibfk_1`;

ALTER TABLE `messages`
    DROP INDEX `userid`;

ALTER TABLE `messages`
    CHANGE `userid` `racerid` int(10) unsigned NOT NULL AFTER `id`;

ALTER TABLE `messages`
    ADD INDEX `racerid` (`racerid`);

ALTER TABLE `messages`
    ADD FOREIGN KEY (`racerid`) REFERENCES `cups_racers` (`id`);

ALTER TABLE `plans`
    DROP FOREIGN KEY `plans_ibfk_3`;

ALTER TABLE `plans`
    DROP FOREIGN KEY `plans_ibfk_4`;

ALTER TABLE `plans`
    DROP INDEX `routeid`,
    DROP INDEX `userid`;

ALTER TABLE `plans`
    CHANGE `userid` `racerid` int(10) unsigned NOT NULL AFTER `cupid`,
    CHANGE `routeid` `raceid` int(10) unsigned NOT NULL AFTER `racerid`;

ALTER TABLE `plans`
    ADD INDEX `racerid` (`racerid`),
    ADD INDEX `raceid` (`raceid`);

ALTER TABLE `plans`
    ADD FOREIGN KEY (`racerid`) REFERENCES `cups_racers` (`id`);

ALTER TABLE `plans`
    ADD FOREIGN KEY (`raceid`) REFERENCES `cups_routes` (`id`);

ALTER TABLE `results`
    DROP FOREIGN KEY `results_ibfk_1`;

ALTER TABLE `results`
    DROP FOREIGN KEY `results_ibfk_2`;

ALTER TABLE `results`
    DROP INDEX `routeid`,
    DROP INDEX `userid`;

ALTER TABLE `results`
    CHANGE `routeid` `raceid` int(10) unsigned NOT NULL AFTER `cupid`,
    CHANGE `userid` `racerid` int(10) unsigned NOT NULL AFTER `raceid`;

ALTER TABLE `results`
    ADD INDEX `raceid` (`raceid`),
    ADD INDEX `racerid` (`racerid`);

ALTER TABLE `results`
    ADD FOREIGN KEY (`racerid`) REFERENCES `cups_racers` (`id`);

ALTER TABLE `results`
    ADD FOREIGN KEY (`raceid`) REFERENCES `cups_routes` (`id`);

ALTER TABLE `chat`
    DROP FOREIGN KEY `chat_ibfk_2`;

ALTER TABLE `chat`
    DROP INDEX `userid`;

ALTER TABLE `chat`
    CHANGE `userid` `racerid` int(10) unsigned NOT NULL AFTER `cupid`;

ALTER TABLE `chat`
    ADD INDEX `racerid` (`racerid`);

ALTER TABLE `chat`
    ADD FOREIGN KEY (`racerid`) REFERENCES `cups_racers` (`id`);

CREATE TABLE `results_overall`
(
    `id`      int(10) unsigned NOT NULL,
    `cupid`   int(10) unsigned NOT NULL,
    `created` timestamp        NOT NULL,
    `content` mediumtext       NULL,
    FOREIGN KEY (`cupid`) REFERENCES `cups` (`id`)
);
ALTER TABLE `results_overall`
    CHANGE `id` `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `results_overall`
    ADD INDEX `cupid_created` (`cupid`, `created`);

ALTER TABLE `cups`
    ADD `calc_class` varchar(100) NOT NULL;
UPDATE `cups`
SET `calc_class` = 'App\\ResultCalc01 '
WHERE `id` = '1';

ALTER TABLE `cups_routes`
    ADD `legend_name` varchar(2) NOT NULL;
UPDATE `cups_routes`
SET `legend_name` = 'A'
WHERE `id` = '1';
UPDATE `cups_routes`
SET `legend_name` = 'B'
WHERE `id` = '2';
UPDATE `cups_routes`
SET `legend_name` = 'C'
WHERE `id` = '3';
UPDATE `cups_routes`
SET `legend_name` = 'D'
WHERE `id` = '4';
UPDATE `cups_routes`
SET `legend_name` = 'E'
WHERE `id` = '5';
UPDATE `cups_routes`
SET `legend_name` = 'F'
WHERE `id` = '6';
UPDATE `cups_routes`
SET `legend_name` = 'G'
WHERE `id` = '7';
UPDATE `cups_routes`
SET `legend_name` = 'H'
WHERE `id` = '8';
UPDATE `cups_routes`
SET `legend_name` = 'I'
WHERE `id` = '9';
UPDATE `cups_routes`
SET `legend_name` = 'J'
WHERE `id` = '10';
UPDATE `cups_routes`
SET `legend_name` = 'K'
WHERE `id` = '11';
UPDATE `cups_routes`
SET `legend_name` = 'L'
WHERE `id` = '12';

CREATE TABLE `categories`
(
    `id`       int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `cupid`    int(10) unsigned NOT NULL,
    `catid`    varchar(5)       NOT NULL,
    `gender`   varchar(1)       NOT NULL,
    `age_from` int(3)           NOT NULL,
    `age_to`   int(3)           NOT NULL,
    FOREIGN KEY (`cupid`) REFERENCES `cups` (`id`)
);

INSERT INTO `categories` (`cupid`, `catid`, `gender`, `age_from`, `age_to`)
VALUES ('1', 'M', 'm', '0', '999');
INSERT INTO `categories` (`cupid`, `catid`, `gender`, `age_from`, `age_to`)
VALUES ('1', 'Z', 'f', '0', '999');

CREATE TABLE `racers_categories`
(
    `id`      int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `racerid` int(10) unsigned NOT NULL,
    `catid`   int(10) unsigned NOT NULL,
    FOREIGN KEY (`racerid`) REFERENCES `cups_racers` (`id`),
    FOREIGN KEY (`catid`) REFERENCES `categories` (`id`)
);















