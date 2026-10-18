/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: thrysus_project_db
-- ------------------------------------------------------
-- Server version	11.8.8-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `geological_eras`
--

DROP TABLE IF EXISTS `geological_eras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `geological_eras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `era_name` varchar(100) NOT NULL,
  `era_description` text DEFAULT NULL,
  `era_number` int(11) DEFAULT NULL,
  `colour_hex` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geological_eras`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `geological_eras` WRITE;
/*!40000 ALTER TABLE `geological_eras` DISABLE KEYS */;
INSERT INTO `geological_eras` VALUES
(1,'Paleogian','The first complex life, colonizations of land and adaptive radiation up to the Nivean Collapse.',1,'#534AB7'),
(2,'Deuterogian','Recovery and radiation of Nivean survivors into an emptied world.',2,'#1D9E75'),
(3,'Triogian','Consolidation of modern body plans and complex ecosystems.',3,'#BA7517'),
(4,'Neogian','Modern fauna arise. Ongoing SOHS documentation since 2338.',4,'#D85A30');
/*!40000 ALTER TABLE `geological_eras` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `species`
--

DROP TABLE IF EXISTS `species`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `species` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `common_name` varchar(100) DEFAULT NULL,
  `scientific_name` varchar(100) DEFAULT NULL,
  `species_description` text DEFAULT NULL,
  `species_image` varchar(255) DEFAULT NULL,
  `ancestor_id` int(11) DEFAULT NULL,
  `taxon_id` int(11) DEFAULT NULL,
  `status` enum('extant','extinct') DEFAULT 'extant',
  `body_length` varchar(50) DEFAULT NULL,
  `mass` varchar(50) DEFAULT NULL,
  `thermy` enum('ectothermy','endothermy','mesothermy') DEFAULT NULL,
  `encounter_risk` enum('low','moderate','high','severe') DEFAULT NULL,
  `body_plan` text DEFAULT NULL,
  `appearance` text DEFAULT NULL,
  `behaviour` text DEFAULT NULL,
  `habitat_and_distribution` text DEFAULT NULL,
  `evolutionary_history` text DEFAULT NULL,
  `first_described` date DEFAULT NULL,
  `lead` varchar(100) DEFAULT NULL,
  `last_edited` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taxon_id` (`taxon_id`),
  KEY `ancestor_id` (`ancestor_id`),
  CONSTRAINT `species_ibfk_1` FOREIGN KEY (`taxon_id`) REFERENCES `taxa` (`id`),
  CONSTRAINT `species_ibfk_2` FOREIGN KEY (`ancestor_id`) REFERENCES `species` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `species`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `species` WRITE;
/*!40000 ALTER TABLE `species` DISABLE KEYS */;
INSERT INTO `species` VALUES
(1,'Exioculia','Precursor of the Malakokelia','The earliest ancestors of the Malakokelia phylum, originating in the ocean during the Cambrian analogue and predating all terrestrial life.','exioculia.jpg',NULL,2,'extinct',NULL,NULL,NULL,NULL,'A soft body sheathed in a pill-shaped shell and carried on ten foundational legs. The internal anatomy is organized into a muscle-driven chamber for each leg and a single dorsal tube lung running the length of the body, served by a distributed nervous network.','Pill-shaped and smooth-shelled in drab olive-grey, with six small eyes clustered at the front of the body above a simple radula mouth.','Pushed itself slowly across the seafloor on its ten legs, grazing detritus and small shelled organisms with its radula. It breathed through rhythmic gill-flaps on its underside.','A benthic bottom-dweller of the Cambrian-analogue seas, long before any life had reached land.','The founding form of the Malakokelia. Its versatile body plan, the per-leg muscle chambers, dorsal tube lung and underside gill-flaps, became the foundation from which the diverse terrestrial lineages that later dominated the land all descend.',NULL,'Dr. E. Vane','2341-10-28');
/*!40000 ALTER TABLE `species` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `taxa`
--

DROP TABLE IF EXISTS `taxa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `taxa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_taxon_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `rank` varchar(55) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `colour_hex` varchar(7) DEFAULT NULL,
  `body_plan` text DEFAULT NULL,
  `appearance` text DEFAULT NULL,
  `behaviour` text DEFAULT NULL,
  `habitat_and_distribution` text DEFAULT NULL,
  `evolutionary_history` text DEFAULT NULL,
  `status` enum('extant','extinct') DEFAULT 'extant',
  `first_described` date DEFAULT NULL,
  `lead` varchar(100) DEFAULT NULL,
  `last_edited` date DEFAULT NULL,
  `category` enum('fauna','flora','tapetae') DEFAULT 'fauna',
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_taxon_id` (`parent_taxon_id`),
  CONSTRAINT `taxa_ibfk_1` FOREIGN KEY (`parent_taxon_id`) REFERENCES `taxa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `taxa` WRITE;
/*!40000 ALTER TABLE `taxa` DISABLE KEYS */;
INSERT INTO `taxa` VALUES
(1,NULL,'Hyalozoa','kingdom','The glass-bodied animal kingdom of Diotha b: complex heterotrophs unified by silica biomineralisation, encompassing all three known phyla.','#5C6672',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'fauna',NULL),
(2,1,'Malakokelia','phylum','Softbodied invertebrates with radula derrived mouthparts, segmented bodies and jointed appendages.','#C85A86',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'fauna',NULL),
(3,1,'Myctida','phylum','Endoskeletal vertebrae analogues, they have radial brains and silica bones.','#2E73B5',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'fauna',NULL),
(4,1,'Rotapodia','phylum','Exoskeletal invertebrates that evolved from a radial bodyplan, they have silica-argonite composite exoskeltons, and saclungs.','#9B4D9B',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'fauna',NULL),
(5,NULL,'Hygrophytes','kingdom','The plant kingdom of Diotha b: a lineage of methanotroph symbionts descended from the pioneers of Invasion 0, the first stage of terrestrial colonization, whose global forest prepared the world for animal life.','#4B5C93','To withstand the 1.2g gravity the Hygrophytes sprawl outward rather than upward, growing in dense, interlocked columns rather than single trunks. Growth is powered by exterior nodules housing symbiotic methanotrophs, and the resulting exothermic reaction forces the textured bark to act as heat fins for cooling. Waxy skins and water-heavy sap leave the whole plant effectively fireproof.','Low, spreading forms crowned with clusters of glossy, water-swollen leaves in deep blue-violet, raised on short knotted columns of textured bark. The bark is studded with pale rounded nodules, and the plants root into a specialized organic mat that stands in for absent topsoil.',NULL,'Ubiquitous across early Diotha b, forming a single global pioneer forest over the raw mineral substrate. They spread outward wherever the organic mat can establish, colonizing bare ground ahead of all other flora.','The Hygrophytes define Invasion 0, the first stage of terrestrial colonization. Their revolutionary innovation was assimilating local methanotrophs into their own tissues: they metabolize atmospheric methane for energy while the internal symbionts generate water and carbon dioxide for the plant to reuse. This self-contained cycle let them green a barren world and prepare it for animal life.','extant',NULL,NULL,NULL,'flora','hygrophytes.jpg'),
(6,5,'Sarcophyta','phylum','Basal terrestrial phototrophs with water-storing succulent tissue and a low creeping habit. They anchor by shallow holdfasts rather than true roots and dominate the arid crust and rockpan margins.','#7E9B5E',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'flora',NULL),
(7,5,'Thallophyta','phylum','Aquatic thallophytes lacking differentiated stems or leaves; the body is a single flat photosynthetic sheet. They range from thin surface films to broad anchored fronds across the shallow seas and tidal shelves of Diotha b.','#1F6E6E',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'flora',NULL),
(8,5,'Bathyphyta','phylum','Ground-anchored vascular flora with true rhizoids and upright fronded shoots. They form the dominant understorey scrub of the humid lowlands and reproduce by shed spore-bodies rather than seed.','#4E7A34',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'flora',NULL),
(9,5,'Aerophyta','phylum','Buoyant flora that inflate gas-filled bladders to drift free of the substrate. Loosely tethered or fully airborne, they harvest light and airborne moisture at height and disperse on the steady thermal winds.','#A7C4B5',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'flora',NULL),
(10,5,'Polystelophyta','phylum','Structural flora built from bundled parallel stems, or steles, fused into rigid load-bearing columns. This redundant architecture lets them reach canopy height without relying on a single woody trunk.','#5A6E2A',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'flora',NULL),
(11,10,'Edafophyte','genus','The large-bodied Hygrophyte genus: robust columns crowned with a dense rosette of swollen, glossy blue leaves and ringed at the base by prominent methanotroph nodules.','#3C4E8C',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'flora','edafophyte.jpg'),
(12,10,'Dentrolovophyte','genus','The smaller, tree-like Hygrophyte genus: a single stout column topped with a compact tuft of blue-grey foliage, common in the open sunlit margins of the pioneer forest.','#6E7CA8',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'flora','dentrolovophyte.jpg'),
(15,NULL,'Tapetae','kingdom','<p><br></p>','#6E5A63',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'tapetae',NULL),
(17,15,'Necrotapeta','phylum','<p>Generalist free-living decomposers, litter/detritus breakdown, terrestrial and shallow aquatic.</p>','#8A7A82',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'tapetae',NULL),
(18,15,'Methanotapeta','phylum','<p>Methanotrophs spanning free-living mobile/dispersed forms <em>and</em> mat/biofilm-forming colonial forms as a within-phylum growth-form split. </p>','#A98C6B',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'tapetae',NULL),
(20,15,'Endotapeta','phylum','<p>Parasitic and mutualistic lineages living in/on other organisms, spanning harmful-to-beneficial, the only Tapetae phylum capable of limited locomotion.</p>','#7C4A5D',NULL,NULL,NULL,NULL,NULL,'extant',NULL,NULL,NULL,'tapetae',NULL);
/*!40000 ALTER TABLE `taxa` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-07-13 11:08:14
