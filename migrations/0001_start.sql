create table `migrations`
(
    `name`       varchar(255) not null,
    `apply_time` timestamp default current_timestamp,
    primary key (name)
)
    engine = innodb
    auto_increment = 1
    character set utf8
    collate utf8_general_ci;