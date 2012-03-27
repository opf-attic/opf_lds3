DROP table if exists Users;
create table Users ( 
	id int(11) not null auto_increment,
	email varchar(255) not null,
	name varchar(255),
	pic_path blob,
	enabled int(2) not null,
	primary key (id,email)
);

DROP table if exists Access_Keys;
create table Access_Keys (
	id int(11) not null auto_increment,
	user_id int(11) not null,
	descrip blob,
	access_key varchar(255) not null,
	secret varchar(255) not null,
	enabled int(2) not null,
	primary key (id)
);
