-- Reagansoft Innovation Limited
-- Repeatable text normalisation for both databases.
--
-- Older demo data used en dashes inside project titles and the company
-- opening hours, and the two-word brand "Reagan Soft". Both are replaced
-- with the current, natural wording. Safe to run more than once.

SET NAMES utf8mb4;

USE `reagansoft_system`;

UPDATE `projects`
SET `title` = REPLACE(`title`, '–', '-')
WHERE `title` LIKE '%–%';

UPDATE `projects`
SET `description` = REPLACE(`description`, '–', '-')
WHERE `description` LIKE '%–%';

UPDATE `projects`
SET `requirements` = REPLACE(`requirements`, '–', '-')
WHERE `requirements` LIKE '%–%';

UPDATE `settings`
SET `setting_value` = REPLACE(`setting_value`, '–', '-')
WHERE `setting_value` LIKE '%–%';

UPDATE `settings`
SET `setting_value` = REPLACE(`setting_value`, 'Reagan Soft', 'Reagansoft')
WHERE `setting_value` LIKE '%Reagan Soft%';

UPDATE `users`
SET `company` = REPLACE(`company`, 'Reagan Soft', 'Reagansoft')
WHERE `company` LIKE '%Reagan Soft%';

USE `reagansoft_website`;

UPDATE `projects`
SET `title` = REPLACE(`title`, '–', '-')
WHERE `title` LIKE '%–%';

UPDATE `projects`
SET `description` = REPLACE(`description`, '–', '-')
WHERE `description` LIKE '%–%';

UPDATE `projects`
SET `requirements` = REPLACE(`requirements`, '–', '-')
WHERE `requirements` LIKE '%–%';

UPDATE `settings`
SET `setting_value` = REPLACE(`setting_value`, '–', '-')
WHERE `setting_value` LIKE '%–%';

UPDATE `settings`
SET `setting_value` = REPLACE(`setting_value`, 'Reagan Soft', 'Reagansoft')
WHERE `setting_value` LIKE '%Reagan Soft%';

UPDATE `users`
SET `company` = REPLACE(`company`, 'Reagan Soft', 'Reagansoft')
WHERE `company` LIKE '%Reagan Soft%';
