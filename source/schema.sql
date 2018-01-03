CREATE TABLE userinfo(uname VARCHAR(60) NOT NULL, pass VARCHAR(255) NOT NULL, firstname VARCHAR(60), lastname VARCHAR(60), email VARCHAR(60), identity CHAR(1), PRIMARY KEY (uname));

CREATE TABLE userclass(uname VARCHAR(60), classname VARCHAR(20), FOREIGN KEY (uname) REFERENCES userinfo(uname), FOREIGN KEY (classname) REFERENCES classinfo(classname));

CREATE TABLE classinfo(classname VARCHAR(20) NOT NULL, Y INT, N INT, PRIMARY KEY (classname));

