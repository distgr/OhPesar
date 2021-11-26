CREATE TABLE `voices` (
    `id` int(10) NOT NULL,
    `unique_id` char(250) NOT NULL,
    `accepted` char(200) DEFAULT '0',
    `name` char(50) NOT NULL,
    `sender` char(200) NOT NULL,
    `messageid` char(200) NOT NULL,
    `mode` char(200) NOT NULL,
    `usecount` int(50) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `voices`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `voices`
    MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT = 1;
    COMMIT;