CREATE TABLE `user` (
    `id` bigint(10) NOT NULL,
    `step` varchar(50) NOT NULL,
    `voicename` char(200) DEFAULT NULL,
    `voicemode` char(50) DEFAULT NULL,
    `sendvoice` int(1) NOT NULL DEFAULT 0,
    `voiceedit` char(50) DEFAULT NULL,
    `sortby` char(100) NOT NULL DEFAULT 'newest',
    `badvoices` char(200) DEFAULT '0',
    `sendvoiceaction` char(50) DEFAULT 'byname'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `user`
    ADD PRIMARY KEY (`id`);