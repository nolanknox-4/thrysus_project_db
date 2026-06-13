CREATE DATABASE thrysus_project_db;
USE thrysus_project_db;

-- taxa table (classification tree: kingdom → phylum → … → clade)
CREATE TABLE taxa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_taxon_id INT,
    name VARCHAR(100) NOT NULL,
    rank VARCHAR(55),
    description TEXT,
    colour_hex VARCHAR(7),
    body_plan TEXT,
    appearance TEXT,
    behaviour TEXT,
    habitat_and_distribution TEXT,
    evolutionary_history TEXT,
    status ENUM('extant', 'extinct') Default 'extant',
    first_described DATE,
    lead VARCHAR(100),
    last_edited DATE,
    FOREIGN KEY (parent_taxon_id) REFERENCES taxa(id)
);


-- species table
CREATE TABLE species (
    id INT PRIMARY KEY AUTO_INCREMENT,
    common_name VARCHAR(100),
    scientific_name VARCHAR(100),
    species_description TEXT,
    species_image VARCHAR(255),
    ancestor_id INT,
    taxon_id INT, 
    status ENUM('extant', 'extinct') DEFAULT 'extant',
    body_length VARCHAR(50),
    mass VARCHAR(50),
    thermy ENUM('ectothermy', 'endothermy', 'mesothermy'),
    encounter_risk ENUM('low', 'moderate', 'high', 'severe'),
    body_plan TEXT,
    appearance TEXT,
    behaviour TEXT,
    habitat_and_distribution TEXT,
    evolutionary_history TEXT,
    first_described DATE,
    lead VARCHAR(100),
    last_edited DATE,
    FOREIGN KEY (taxon_id) REFERENCES taxa(id),
    FOREIGN KEY (ancestor_id) REFERENCES species(id)
);

CREATE TABLE geological_eras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    era_name VARCHAR(100) NOT NULL,
    era_description TEXT,
    era_number INT,
    colour_hex VARCHAR(7)
);



-- COLOUR CONVENTION:
-- Eras and phyla must use SEPARATE, non-overlapping colour palettes.
-- Both appear together on the landing page (era timeline + phylum sidebar),
-- so a shared colour would make one hue mean two different things.
-- Reserve one palette for era colour_hex values, a distinct palette for phyla.



