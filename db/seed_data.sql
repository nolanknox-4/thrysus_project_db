
INSERT INTO taxa (name, rank, description, colour_hex, parent_taxon_id) VALUES
('Thyrsozoa',   'kingdom', 'The glass-bodied animal kingdom of Dionysus: complex heterotrophs unified by silica biomineralisation, encompassing all three known phyla.', '#5C6672', NULL),
('Malakokelia', 'phylum',  'Softbodied invertebrates with radula derrived mouthparts, segmented bodies and jointed appendages.', '#C85A86', 1),
('Myctida',  'phylum',  'Endoskeletal vertebrae analogues, they have radial brains and silica bones.', '#2E73B5', 1),
('Rotapodia',   'phylum',  'Exoskeletal invertebrates that evolved from a radial bodyplan, they have silica-argonite composite exoskeltons, and saclungs.', '#9B4D9B', 1);


INSERT INTO species (common_name, scientific_name, taxon_id, ancestor_id, species_description, body_plan, appearance, behaviour, habitat_and_distribution, evolutionary_history, status, species_image, lead, last_edited) VALUES
('Exioculia', 'Precursor of the Malakokelia', 2, NULL,
'The earliest ancestors of the Malakokelia phylum, originating in the ocean during the Cambrian analogue and predating all terrestrial life.',
'A soft body sheathed in a pill-shaped shell and carried on ten foundational legs. The internal anatomy is organized into a muscle-driven chamber for each leg and a single dorsal tube lung running the length of the body, served by a distributed nervous network.',
'Pill-shaped and smooth-shelled in drab olive-grey, with six small eyes clustered at the front of the body above a simple radula mouth.',
'Pushed itself slowly across the seafloor on its ten legs, grazing detritus and small shelled organisms with its radula. It breathed through rhythmic gill-flaps on its underside.',
'A benthic bottom-dweller of the Cambrian-analogue seas, long before any life had reached land.',
'The founding form of the Malakokelia. Its versatile body plan, the per-leg muscle chambers, dorsal tube lung and underside gill-flaps, became the foundation from which the diverse terrestrial lineages that later dominated the land all descend.',
'extinct', 'exioculia.jpg', 'Dr. E. Vane', '2341-10-28');

INSERT INTO geological_eras (era_name, era_description, era_number, colour_hex) VALUES
('Paleogian', 'The first complex life, colonizations of land and adaptive radiation up to the Nivean Collapse.', 1, '#534AB7'),
('Deuterogian', 'Recovery and radiation of Nivean survivors into an emptied world.', 2, '#1D9E75'),
('Triogian', 'Consolidation of modern body plans and complex ecosystems.', 3, '#BA7517'),
('Neogian', 'Modern fauna arise. Ongoing SOHS documentation since 2338.', 4, '#D85A30');


-- Flora kingdom (id 5). Must be inserted BEFORE its phyla (self-FK on parent_taxon_id).
INSERT INTO taxa (name, rank, parent_taxon_id, category, colour_hex, description, body_plan, appearance, habitat_and_distribution, evolutionary_history, image) VALUES
('Hygrophytes', 'kingdom', NULL, 'flora', '#4B5C93',
'The plant kingdom of Dionysus: a lineage of methanotroph symbionts descended from the pioneers of Invasion 0, the first stage of terrestrial colonization, whose global forest prepared the world for animal life.',
'To withstand the 1.2g gravity the Hygrophytes sprawl outward rather than upward, growing in dense, interlocked columns rather than single trunks. Growth is powered by exterior nodules housing symbiotic methanotrophs, and the resulting exothermic reaction forces the textured bark to act as heat fins for cooling. Waxy skins and water-heavy sap leave the whole plant effectively fireproof.',
'Low, spreading forms crowned with clusters of glossy, water-swollen leaves in deep blue-violet, raised on short knotted columns of textured bark. The bark is studded with pale rounded nodules, and the plants root into a specialized organic mat that stands in for absent topsoil.',
'Ubiquitous across early Dionysus, forming a single global pioneer forest over the raw mineral substrate. They spread outward wherever the organic mat can establish, colonizing bare ground ahead of all other flora.',
'The Hygrophytes define Invasion 0, the first stage of terrestrial colonization. Their revolutionary innovation was assimilating local methanotrophs into their own tissues: they metabolize atmospheric methane for energy while the internal symbionts generate water and carbon dioxide for the plant to reuse. This self-contained cycle let them green a barren world and prepare it for animal life.', 'hygrophytes.jpg');

-- Flora phyla (ids 6-10), all children of the Hygrophytes kingdom (id 5).
INSERT INTO taxa (name, rank, parent_taxon_id, description, colour_hex, category) VALUES
('Sarcophyta', 'phylum', 5, 'Basal terrestrial phototrophs with water-storing succulent tissue and a low creeping habit. They anchor by shallow holdfasts rather than true roots and dominate the arid crust and rockpan margins.', '#7E9B5E', 'flora'),
('Thallophyta', 'phylum', 5, 'Aquatic thallophytes lacking differentiated stems or leaves; the body is a single flat photosynthetic sheet. They range from thin surface films to broad anchored fronds across the shallow seas and tidal shelves of Dionysus.', '#1F6E6E', 'flora'),
('Bathyphyta', 'phylum', 5, 'Ground-anchored vascular flora with true rhizoids and upright fronded shoots. They form the dominant understorey scrub of the humid lowlands and reproduce by shed spore-bodies rather than seed.', '#4E7A34', 'flora'),
('Aerophyta', 'phylum', 5, 'Buoyant flora that inflate gas-filled bladders to drift free of the substrate. Loosely tethered or fully airborne, they harvest light and airborne moisture at height and disperse on the steady thermal winds.', '#A7C4B5', 'flora'),
('Polystelophyta', 'phylum', 5, 'Structural flora built from bundled parallel stems, or steles, fused into rigid load-bearing columns. This redundant architecture lets them reach canopy height without relying on a single woody trunk.', '#5A6E2A', 'flora');

-- Hygrophyte genera (ids 11-12), children of Polystelophyta (id 10, the structural-column phylum).
INSERT INTO taxa (name, rank, parent_taxon_id, category, colour_hex, description, image) VALUES
('Edafophyte', 'genus', 10, 'flora', '#3C4E8C', 'The large-bodied Hygrophyte genus: robust columns crowned with a dense rosette of swollen, glossy blue leaves and ringed at the base by prominent methanotroph nodules.', 'edafophyte.jpg'),
('Dentrolovophyte', 'genus', 10, 'flora', '#6E7CA8', 'The smaller, tree-like Hygrophyte genus: a single stout column topped with a compact tuft of blue-grey foliage, common in the open sunlit margins of the pioneer forest.', 'dentrolovophyte.jpg');


-- Tapetae kingdom (id 13 on a fresh seed). The third kingdom, a peer of Thyrsozoa/Hygrophytes.
-- Must be inserted BEFORE its phyla (self-FK on parent_taxon_id).
INSERT INTO taxa (name, rank, parent_taxon_id, category, colour_hex, description) VALUES
('Tapetae', 'kingdom', NULL, 'tapetae', '#6E5A63', '<p><br></p>');

-- Tapetae phyla (ids 14-16), all children of the Tapetae kingdom (id 13).
INSERT INTO taxa (name, rank, parent_taxon_id, category, colour_hex, description) VALUES
('Necrotapeta', 'phylum', 13, 'tapetae', '#8A7A82', '<p>Generalist free-living decomposers, litter/detritus breakdown, terrestrial and shallow aquatic.</p>'),
('Methanotapeta', 'phylum', 13, 'tapetae', '#A98C6B', '<p>Methanotrophs spanning free-living mobile/dispersed forms <em>and</em> mat/biofilm-forming colonial forms as a within-phylum growth-form split. </p>'),
('Endotapeta', 'phylum', 13, 'tapetae', '#7C4A5D', '<p>Parasitic and mutualistic lineages living in/on other organisms, spanning harmful-to-beneficial, the only Tapetae phylum capable of limited locomotion.</p>');

-- Biomes: habitat-type chapters (Dixon-style "The Plains"/"The Forests"), independent of
-- taxonomy/era. Plates are tagged with the biome they're set in.
INSERT INTO biomes (name, description, colour_hex) VALUES
('Pioneer Forest', 'The Hygrophyte global forest margins — dense interlocked columns over raw mineral substrate.', '#7A8B5A'),
('Arid Crust', 'Bare rockpan and mineral flats where only the hardiest succulent flora anchor.', '#C2A878'),
('Tidal Shelf', 'Shallow seas and tidal margins where aquatic thallophytes and benthic fauna meet the shore.', '#5B8A9A'),
('High Thermals', 'The open air above the canopy, ridden by buoyant airborne flora and whatever preys on them.', '#A9B8C4');
