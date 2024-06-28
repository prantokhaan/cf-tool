create database cftool;

use cftool;

create table users(
    id int primary key AUTO_INCREMENT,
    username varchar(255) not null unique,
    email varchar(255) not null unique,
    codeforces_handle varchar(255) not null,
    password varchar(255) not null
);

drop table users;

CREATE TABLE pending_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    codeforces_handle VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    verification_code VARCHAR(255) NOT NULL
);

select * from users;
