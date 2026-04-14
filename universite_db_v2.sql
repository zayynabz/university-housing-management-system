CREATE DATABASE universite_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE universite_db;

-- TABLE CENTRALE : utilisateur (Admin + Resident + Technicien)

CREATE TABLE utilisateur (
    id_utilisateur  INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(50)  NOT NULL,
    prenom          VARCHAR(50)  NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,          -- bcrypt hash uniquement
    telephone       VARCHAR(20),
    status_compte   ENUM('Actif','Inactif') DEFAULT 'Actif',
    role            ENUM('Admin','Resident','Technicien') NOT NULL,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- PROFIL ADMIN
CREATE TABLE admin (
    id_admin        INT PRIMARY KEY,
    FOREIGN KEY (id_admin) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);
-- PROFIL RESIDENT
CREATE TABLE resident (
    id_resident     INT PRIMARY KEY,
    numero_etudiant INT UNIQUE,
    date_naissance  DATE,
    filiere         VARCHAR(50),
    FOREIGN KEY (id_resident) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);
-- PROFIL TECHNICIEN
CREATE TABLE technicien (
    id_technicien   INT PRIMARY KEY,
    specialite      VARCHAR(50),
    disponibilite   ENUM('Disponible','Occupé') DEFAULT 'Disponible',
    type_technicien ENUM('Interne','Externe') NOT NULL,
    FOREIGN KEY (id_technicien) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

-- RESIDENCE
CREATE TABLE residence (
    id_residence        INT AUTO_INCREMENT PRIMARY KEY,
    id_admin            INT NOT NULL,
    nom_residence       VARCHAR(100) NOT NULL UNIQUE,
    adresse_residence   VARCHAR(255),
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin)
);

-- APPARTEMENT
CREATE TABLE appartement (
    id_appartement      INT AUTO_INCREMENT PRIMARY KEY,
    id_residence        INT NOT NULL,
    numero_appartement  VARCHAR(20) NOT NULL,
    etage               INT NOT NULL,
    nb_chambres         INT CHECK (nb_chambres BETWEEN 1 AND 6),
    CONSTRAINT uq_appartement UNIQUE (id_residence, numero_appartement, etage),
    FOREIGN KEY (id_residence) REFERENCES residence(id_residence) ON DELETE CASCADE
);

-- CHAMBRE
CREATE TABLE chambre (
    id_chambre      INT AUTO_INCREMENT PRIMARY KEY,
    id_appartement  INT NOT NULL,
    numero_chambre  VARCHAR(20) NOT NULL,
    loyer_mensuel   DECIMAL(10,2) NOT NULL CHECK (loyer_mensuel > 0),
    status_chambre  ENUM('Libre','Occupée') DEFAULT 'Libre',
    CONSTRAINT uq_chambre UNIQUE (id_appartement, numero_chambre),
    FOREIGN KEY (id_appartement) REFERENCES appartement(id_appartement) ON DELETE CASCADE
);

-- OCCUPATION (affectation résident ↔ chambre)
CREATE TABLE occupation (
    id_occupation       INT AUTO_INCREMENT PRIMARY KEY,
    id_resident         INT NOT NULL,
    id_chambre          INT NOT NULL,
    date_debut          DATE NOT NULL,
    date_fin            DATE DEFAULT NULL,
    status_occupation   ENUM('En cours','Terminée') DEFAULT 'En cours',
    FOREIGN KEY (id_resident) REFERENCES resident(id_resident),
    FOREIGN KEY (id_chambre)  REFERENCES chambre(id_chambre)
);

-- Index pour éviter deux occupations actives sur la même chambre
CREATE INDEX idx_occupation_chambre_status ON occupation(id_chambre, status_occupation);
CREATE INDEX idx_occupation_resident_status ON occupation(id_resident, status_occupation);

-- PAIEMENT
CREATE TABLE paiement (
    id_paiement     INT AUTO_INCREMENT PRIMARY KEY,
    id_occupation   INT NOT NULL,
    montant_a_payer DECIMAL(10,2) NOT NULL CHECK (montant_a_payer > 0),
    mois_concerne   VARCHAR(7) NOT NULL,            -- format : 'YYYY-MM'
    date_echeance   DATE,
    date_paiement   DATE DEFAULT NULL,              -- quand le paiement a été effectué
    status_paiement ENUM('Payé','En retard','En attente') DEFAULT 'En attente',
    CONSTRAINT uq_paiement UNIQUE (id_occupation, mois_concerne),
    FOREIGN KEY (id_occupation) REFERENCES occupation(id_occupation)
);

CREATE INDEX idx_paiement_status ON paiement(status_paiement, date_echeance);

-- INCIDENT
CREATE TABLE incident (
    id_incident         INT AUTO_INCREMENT PRIMARY KEY,
    id_resident         INT NOT NULL,
    id_chambre          INT NOT NULL,
    id_technicien       INT DEFAULT NULL,
    titre_incident      VARCHAR(100) NOT NULL,
    description_incident TEXT,
    status_incident     ENUM('En attente','En cours','Résolu') DEFAULT 'En attente',
    priorite_incident   ENUM('Faible','Moyenne','Haute') DEFAULT 'Moyenne',
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_resolution     DATE DEFAULT NULL,
    FOREIGN KEY (id_resident)   REFERENCES resident(id_resident),
    FOREIGN KEY (id_chambre)    REFERENCES chambre(id_chambre),
    FOREIGN KEY (id_technicien) REFERENCES technicien(id_technicien)
);

CREATE INDEX idx_incident_status ON incident(status_incident, priorite_incident);


