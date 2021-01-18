ALTER TABLE `users`
ADD `cupid` int(10) unsigned NULL,
ADD FOREIGN KEY (`cupid`) REFERENCES `cups` (`id`) ON DELETE NO ACTION;

UPDATE users SET cupid = 2;