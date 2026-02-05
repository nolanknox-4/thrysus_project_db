CREATE DATABASE thrysus_project_db;
USE thrysus_project_db;


-- phyla table
CREATE TABLE phyla (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phylum_name VARCHAR(100) NOT NULL,
    description TEXT 
);

--species table
CREATE TABLE species (
    id INT PRIMARY KEY AUTO_INCREMENT,
    common_name VARCHAR(100),
    scientific_name VARCHAR(100),
    phylum_id INT, --Foreign key to phyla table
    ancestor_id INT,
    FOREIGN KEY (phylum_id) REFERENCES phyla(id),
    FOREIGN KEY (ancestor_id) REFERENCES species(id)
       
);