CREATE DATABASE IF NOT EXISTS thrysus_project_db;
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
    FOREIGN KEY (parent_taxon_id) REFERENCES taxa(id),
    category ENUM('fauna','flora','tapetae') DEFAULT 'fauna',
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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

-- biomes: habitat-type chapters (Dixon-style "The Plains"/"The Forests"), independent of
-- taxonomy and era. A plate is a scene set IN a biome; many plates can share one biome.
CREATE TABLE biomes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    colour_hex VARCHAR(7)
);

-- plates: a single illustration depicting an ecosystem/scene with several species or taxa in it.
-- Lets a species/taxon borrow a shared illustration instead of requiring a unique commission.
CREATE TABLE plates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    image VARCHAR(255) NOT NULL,
    tour_text TEXT,
    location VARCHAR(150),
    era_id INT,
    biome_id INT,
    first_described DATE,
    lead VARCHAR(100),
    last_edited DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (era_id) REFERENCES geological_eras(id),
    FOREIGN KEY (biome_id) REFERENCES biomes(id)
);

-- plate_subjects: which species/taxa appear in a given plate.
-- x_pct/y_pct are reserved for future hotspot placement and are unused for now.
-- subject_type='unlinked' is a caption-only background subject: no species/taxon row,
-- no entry page, just a name typed straight into the plate (subject_id is NULL, label holds the name).
CREATE TABLE plate_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plate_id INT NOT NULL,
    subject_type ENUM('species','taxon','unlinked') NOT NULL,
    subject_id INT,
    label VARCHAR(150),
    caption_note VARCHAR(255),
    x_pct DECIMAL(5,2),
    y_pct DECIMAL(5,2),
    FOREIGN KEY (plate_id) REFERENCES plates(id)
);



-- COLOUR CONVENTION:
-- Eras, taxa, and biomes must each use SEPARATE, non-overlapping colour palettes —
-- they appear together (era timeline + phylum sidebar + plate tags), so a shared
-- colour would make one hue mean two different things. Reserve one palette per axis.
