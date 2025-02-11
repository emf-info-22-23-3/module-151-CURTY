DROP database if exists BaoBull;
CREATE DATABASE BaoBull;
USE BaoBull;

CREATE TABLE t_user (
    pk_user INT PRIMARY KEY AUTO_INCREMENT,
    name CHAR(50) NOT NULL,
    familyName CHAR(50) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(64) NOT NULL
);

CREATE TABLE t_portfolio (
    pk_portfolio INT PRIMARY KEY AUTO_INCREMENT,
    fk_user INT UNIQUE NOT NULL,  -- One portfolio per user
    FOREIGN KEY (fk_user) REFERENCES t_user(pk_user) ON DELETE CASCADE
);

CREATE TABLE t_stock (
    pk_stock INT PRIMARY KEY AUTO_INCREMENT,
    name CHAR(30) UNIQUE NOT NULL
);

CREATE TABLE tr_portfolio_stock (
    fk_portfolio INT NOT NULL,
    fk_stock INT NOT NULL,
    avgBuyPrice DECIMAL NOT NULL,
    boughtQuantity INT NOT NULL,
    soldQuantity INT DEFAULT 0,
    avgSoldPrice DECIMAL DEFAULT 0,
    PRIMARY KEY (fk_portfolio, fk_stock),
    FOREIGN KEY (fk_portfolio) REFERENCES t_portfolio(pk_portfolio) ON DELETE CASCADE,
    FOREIGN KEY (fk_stock) REFERENCES t_stock(pk_stock) ON DELETE CASCADE
);

#insert data
#USE BaoBull;
INSERT INTO t_user (name, familyName, email, password)
VALUES 
('John', 'Doe', 'john.doe@example.com', "$2y$10$b.xgDxgdJ.At4YeSTG/ESu8FnrvbTEVpKUYzHdpCauLAWI22MeD22"),
('Jane', 'Smith', 'jane.smith@example.com', "$2y$10$b.xgDxgdJ.At4YeSTG/ESu8FnrvbTEVpKUYzHdpCauLAWI22MeD22");
update t_user set password = '$2y$10$zhcot4c4Kf6bucjpOSwpf.4dkKD78ssSpnRpICzmZmyqaRToyUfeW' where pk_user = 1;
insert into BaoBull.t_stock (name) VALUES ("RBRK");
INSERT INTO BaoBull.tr_portfolio_stock (fk_portfolio, fk_stock, avgBuyPrice, boughtQuantity) VALUES (2,1,25.33, 12);
