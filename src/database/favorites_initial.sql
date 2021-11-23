CREATE TABLE `favorites` (
    `id` bigint(10) NOT NULL,
    `voiceid` char(10) NOT NULL,
    `userid` char(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `favorites`
    ADD PRIMARY KEY (`id`);