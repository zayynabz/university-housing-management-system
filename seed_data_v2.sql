USE universite_db;

-- ------------------------------------------------------------
-- UTILISATEURS — Admins
-- Mot de passe : Admin123!
-- ------------------------------------------------------------
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, role) VALUES
('Marzak', 'Zaynab', 'zaynab@universite.com', '$2y$10$8zaw.kcVgAKlaSzsdwva3u.5QEpCjTJ/ojUfoPIPS5dnvEnmuFHMa', '0612000001', 'Admin'),
('Karai',  'Sara',   'sara@universite.com',   '$2y$10$8zaw.kcVgAKlaSzsdwva3u.5QEpCjTJ/ojUfoPIPS5dnvEnmuFHMa', '0612000002', 'Admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO admin (id_admin)
SELECT id_utilisateur FROM utilisateur WHERE email = 'zaynab@universite.com'
ON DUPLICATE KEY UPDATE id_admin = id_admin;
INSERT INTO admin (id_admin)
SELECT id_utilisateur FROM utilisateur WHERE email = 'sara@universite.com'
ON DUPLICATE KEY UPDATE id_admin = id_admin;

-- ------------------------------------------------------------
-- UTILISATEURS — Résidents
-- Mot de passe : Resident123!
-- ------------------------------------------------------------
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, role) VALUES
('Martin',  'Alice',   'alice.martin@etudiant.com',   '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345678', 'Resident'),
('Bernard', 'Bob',     'bob.bernard@etudiant.com',    '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345679', 'Resident'),
('Durand',  'Charlie', 'charlie.durand@etudiant.com', '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345680', 'Resident'),
('Dupont',  'David',   'david.dupont@etudiant.com',   '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345681', 'Resident'),
('Lefèvre', 'Emma',    'emma.lefevre@etudiant.com',   '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345682', 'Resident'),
('Garnier', 'Franck',  'franck.garnier@etudiant.com', '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345683', 'Resident'),
('Henry',   'Gisèle',  'gisele.henry@etudiant.com',   '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345684', 'Resident'),
('Izard',   'Henri',   'henri.izard@etudiant.com',    '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345685', 'Resident'),
('Jacobs',  'Irène',   'irene.jacobs@etudiant.com',   '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345686', 'Resident'),
('Keller',  'Jacques', 'jacques.keller@etudiant.com', '$2y$10$lqLG4/IcyjqREm13i0MFquYrmrHAGvv5xw4rNEKDwRiSeQVKkCQFy', '0612345687', 'Resident')
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- Profils résidents (un par un pour MariaDB)
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024001, '2004-05-15', 'Informatique' FROM utilisateur WHERE email='alice.martin@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024002, '2003-08-22', 'Informatique' FROM utilisateur WHERE email='bob.bernard@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024003, '2004-01-10', 'Informatique' FROM utilisateur WHERE email='charlie.durand@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024004, '2003-11-05', 'Informatique' FROM utilisateur WHERE email='david.dupont@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024005, '2004-03-18', 'Génie Civil' FROM utilisateur WHERE email='emma.lefevre@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024006, '2003-07-30', 'Génie Civil' FROM utilisateur WHERE email='franck.garnier@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024007, '2004-02-14', 'Génie Civil' FROM utilisateur WHERE email='gisele.henry@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024008, '2003-09-25', 'Électronique' FROM utilisateur WHERE email='henri.izard@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024009, '2004-04-12', 'Électronique' FROM utilisateur WHERE email='irene.jacobs@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);
INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere)
SELECT id_utilisateur, 2024010, '2003-06-08', 'Électronique' FROM utilisateur WHERE email='jacques.keller@etudiant.com'
ON DUPLICATE KEY UPDATE filiere = VALUES(filiere);

-- ------------------------------------------------------------
-- UTILISATEURS — Techniciens
-- Mot de passe : Tech123!
-- ------------------------------------------------------------
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, role) VALUES
('Rousseau', 'Pierre',    'pierre.rousseau@maintenance.com',   '$2y$10$lkmAmaymMr/285SvXjcfOOfqvqTH/yPymygRVHzL4dq7nfk/OvzTm', '0698765432', 'Technicien'),
('Serreau',  'Sacha',     'sacha.serreau@maintenance.com',     '$2y$10$lkmAmaymMr/285SvXjcfOOfqvqTH/yPymygRVHzL4dq7nfk/OvzTm', '0698765433', 'Technicien'),
('Thibault', 'Thomas',    'thomas.thibault@maintenance.com',   '$2y$10$lkmAmaymMr/285SvXjcfOOfqvqTH/yPymygRVHzL4dq7nfk/OvzTm', '0698765434', 'Technicien'),
('Univert',  'Urbain',    'urbain.univert@maintenance.com',    '$2y$10$lkmAmaymMr/285SvXjcfOOfqvqTH/yPymygRVHzL4dq7nfk/OvzTm', '0698765435', 'Technicien'),
('Vincent',  'Véronique', 'veronique.vincent@maintenance.com', '$2y$10$lkmAmaymMr/285SvXjcfOOfqvqTH/yPymygRVHzL4dq7nfk/OvzTm', '0698765436', 'Technicien')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO technicien (id_technicien, specialite, disponibilite, type_technicien)
SELECT id_utilisateur, 'Plomberie',   'Disponible', 'Interne' FROM utilisateur WHERE email='pierre.rousseau@maintenance.com'
ON DUPLICATE KEY UPDATE specialite = VALUES(specialite);
INSERT INTO technicien (id_technicien, specialite, disponibilite, type_technicien)
SELECT id_utilisateur, 'Électricité', 'Occupé',     'Interne' FROM utilisateur WHERE email='sacha.serreau@maintenance.com'
ON DUPLICATE KEY UPDATE specialite = VALUES(specialite);
INSERT INTO technicien (id_technicien, specialite, disponibilite, type_technicien)
SELECT id_utilisateur, 'Électricité', 'Disponible', 'Interne' FROM utilisateur WHERE email='thomas.thibault@maintenance.com'
ON DUPLICATE KEY UPDATE specialite = VALUES(specialite);
INSERT INTO technicien (id_technicien, specialite, disponibilite, type_technicien)
SELECT id_utilisateur, 'Plomberie',   'Disponible', 'Externe' FROM utilisateur WHERE email='urbain.univert@maintenance.com'
ON DUPLICATE KEY UPDATE specialite = VALUES(specialite);
INSERT INTO technicien (id_technicien, specialite, disponibilite, type_technicien)
SELECT id_utilisateur, 'Généraliste', 'Disponible', 'Externe' FROM utilisateur WHERE email='veronique.vincent@maintenance.com'
ON DUPLICATE KEY UPDATE specialite = VALUES(specialite);

-- ------------------------------------------------------------
-- RESIDENCES (liées à l'admin zaynab = id 1, sara = id 2)
-- ------------------------------------------------------------
INSERT INTO residence (id_admin, nom_residence, adresse_residence)
SELECT id_utilisateur, 'Résidence A', 'Av de UDI, Fès' FROM utilisateur WHERE email='zaynab@universite.com'
ON DUPLICATE KEY UPDATE nom_residence = nom_residence;
INSERT INTO residence (id_admin, nom_residence, adresse_residence)
SELECT id_utilisateur, 'Résidence B', 'Av de UDI, Fès' FROM utilisateur WHERE email='zaynab@universite.com'
ON DUPLICATE KEY UPDATE nom_residence = nom_residence;
INSERT INTO residence (id_admin, nom_residence, adresse_residence)
SELECT id_utilisateur, 'Résidence C', 'Av de UDI, Fès' FROM utilisateur WHERE email='sara@universite.com'
ON DUPLICATE KEY UPDATE nom_residence = nom_residence;
INSERT INTO residence (id_admin, nom_residence, adresse_residence)
SELECT id_utilisateur, 'Résidence D', 'Av de UDI, Fès' FROM utilisateur WHERE email='sara@universite.com'
ON DUPLICATE KEY UPDATE nom_residence = nom_residence;

-- ------------------------------------------------------------
-- APPARTEMENTS (référencés par nom de résidence)
-- ------------------------------------------------------------
-- Résidence A
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 1, 2 FROM residence WHERE nom_residence='Résidence A'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A2', 1, 3 FROM residence WHERE nom_residence='Résidence A'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 2, 2 FROM residence WHERE nom_residence='Résidence A'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A2', 2, 1 FROM residence WHERE nom_residence='Résidence A'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 3, 3 FROM residence WHERE nom_residence='Résidence A'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);

-- Résidence B
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 1, 2 FROM residence WHERE nom_residence='Résidence B'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A2', 1, 3 FROM residence WHERE nom_residence='Résidence B'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 2, 1 FROM residence WHERE nom_residence='Résidence B'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A2', 2, 2 FROM residence WHERE nom_residence='Résidence B'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 3, 3 FROM residence WHERE nom_residence='Résidence B'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);

-- Résidence C
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 1, 2 FROM residence WHERE nom_residence='Résidence C'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A2', 1, 3 FROM residence WHERE nom_residence='Résidence C'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 2, 1 FROM residence WHERE nom_residence='Résidence C'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A2', 2, 2 FROM residence WHERE nom_residence='Résidence C'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 3, 3 FROM residence WHERE nom_residence='Résidence C'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);

-- Résidence D
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 1, 2 FROM residence WHERE nom_residence='Résidence D'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A2', 1, 3 FROM residence WHERE nom_residence='Résidence D'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 2, 1 FROM residence WHERE nom_residence='Résidence D'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A2', 2, 2 FROM residence WHERE nom_residence='Résidence D'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);
INSERT INTO appartement (id_residence, numero_appartement, etage, nb_chambres)
SELECT id_residence, 'A1', 3, 3 FROM residence WHERE nom_residence='Résidence D'
ON DUPLICATE KEY UPDATE nb_chambres = VALUES(nb_chambres);

-- ------------------------------------------------------------
-- CHAMBRES — générées par résidence/appartement/étage
-- ex: A101, A102, B201, C302...
-- ------------------------------------------------------------
INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'A101', 2100.00, 'Occupée'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence A' AND ap.numero_appartement='A1' AND ap.etage=1
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'A102', 2100.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence A' AND ap.numero_appartement='A1' AND ap.etage=1
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'A201', 2200.00, 'Occupée'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence A' AND ap.numero_appartement='A1' AND ap.etage=2
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'A202', 2200.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence A' AND ap.numero_appartement='A1' AND ap.etage=2
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'A301', 2300.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence A' AND ap.numero_appartement='A1' AND ap.etage=3
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

-- Résidence B
INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'B101', 1900.00, 'Occupée'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence B' AND ap.numero_appartement='A1' AND ap.etage=1
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'B102', 1900.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence B' AND ap.numero_appartement='A1' AND ap.etage=1
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'B201', 2000.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence B' AND ap.numero_appartement='A1' AND ap.etage=2
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'B202', 2000.00, 'Occupée'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence B' AND ap.numero_appartement='A1' AND ap.etage=2
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'B301', 2100.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence B' AND ap.numero_appartement='A1' AND ap.etage=3
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

-- Résidence C
INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'C101', 2000.00, 'Occupée'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence C' AND ap.numero_appartement='A1' AND ap.etage=1
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'C102', 2000.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence C' AND ap.numero_appartement='A1' AND ap.etage=1
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'C201', 2100.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence C' AND ap.numero_appartement='A1' AND ap.etage=2
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'C202', 2100.00, 'Occupée'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence C' AND ap.numero_appartement='A1' AND ap.etage=2
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'C301', 2200.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence C' AND ap.numero_appartement='A1' AND ap.etage=3
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

-- Résidence D
INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'D101', 2100.00, 'Occupée'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence D' AND ap.numero_appartement='A1' AND ap.etage=1
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'D102', 2100.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence D' AND ap.numero_appartement='A1' AND ap.etage=1
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'D201', 2200.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence D' AND ap.numero_appartement='A1' AND ap.etage=2
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'D202', 2200.00, 'Occupée'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence D' AND ap.numero_appartement='A1' AND ap.etage=2
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);

INSERT INTO chambre (id_appartement, numero_chambre, loyer_mensuel, status_chambre)
SELECT ap.id_appartement, 'D301', 2300.00, 'Libre'
FROM appartement ap JOIN residence r ON ap.id_residence=r.id_residence
WHERE r.nom_residence='Résidence D' AND ap.numero_appartement='A1' AND ap.etage=3
ON DUPLICATE KEY UPDATE loyer_mensuel=VALUES(loyer_mensuel);