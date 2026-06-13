
INSERT INTO taxa (name, rank, description, colour_hex, parent_taxon_id) VALUES
('Hyalozoa',    'kingdom', 'The glass-bodied animal kingdom of Diotha b: complex heterotrophs unified by silica biomineralisation, encompassing all three known phyla.', '#5C6672', NULL),
('Malakokelia', 'phylum',  'Softbodied invertebrates with radula derrived mouthparts, segmented bodies and jointed appendages.', '#C85A86', 1),
('Myctida',  'phylum',  'Endoskeletal vertebrae analogues, they have radial brains and silica bones.', '#2E73B5', 1),
('Rotapodia',   'phylum',  'Exoskeletal invertebrates that evolved from a radial bodyplan, they have silica-argonite composite exoskeltons, and saclungs.', '#9B4D9B', 1);


INSERT INTO species (common_name, scientific_name, taxon_id, ancestor_id, species_description, species_image) VALUES
('Pill Snail', 'Exioculia', 2, NULL, 'A small soft-bodied invertebrate of the Malakokelia phylum.', 'placeholder.png');

INSERT INTO geological_eras (era_name, era_description, era_number, colour_hex) VALUES
('Paleogian', 'The first complex life, colonizations of land and adaptive radiation up to the Nivean Collapse.', 1, '#534AB7'),
('Deuterogian', 'Recovery and radiation of Nivean survivors into an emptied world.', 2, '#1D9E75'),
('Triogian', 'Consolidation of modern body plans and complex ecosystems.', 3, '#BA7517'),
('Neogian', 'Modern fauna arise. Ongoing SOHS documentation since 2338.', 4, '#D85A30');
