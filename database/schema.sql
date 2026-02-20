-- =============================================================
-- ESG-BALANCE - Schema del Database Relazionale
-- Corso di Basi di Dati
-- =============================================================

CREATE DATABASE IF NOT EXISTS esg_balance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE esg_balance;

-- -------------------------------------------------------------
-- TABELLA: ruoli
-- Gestisce i tre ruoli: amministratore, revisore_esg, responsabile_aziendale
-- -------------------------------------------------------------
CREATE TABLE ruoli (
    id_ruolo INT AUTO_INCREMENT PRIMARY KEY,
    nome_ruolo VARCHAR(50) NOT NULL UNIQUE -- 'amministratore', 'revisore_esg', 'responsabile_aziendale'
);

-- -------------------------------------------------------------
-- TABELLA: utenti
-- Anagrafica comune a tutti gli utenti
-- -------------------------------------------------------------
CREATE TABLE utenti (
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    id_ruolo INT NOT NULL,
    data_registrazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ruolo) REFERENCES ruoli(id_ruolo)
);

-- -------------------------------------------------------------
-- TABELLA: email_utenti
-- Un utente puo avere piu recapiti email
-- -------------------------------------------------------------
CREATE TABLE email_utenti (
    id_email INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    FOREIGN KEY (id_utente) REFERENCES utenti(id_utente) ON DELETE CASCADE
);

-- -------------------------------------------------------------
-- TABELLA: competenze_revisori
-- Le competenze (con livello) dei revisori ESG
-- -------------------------------------------------------------
CREATE TABLE competenze_revisori (
    id_competenza INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    nome_competenza VARCHAR(150) NOT NULL,
    livello TINYINT NOT NULL CHECK (livello BETWEEN 1 AND 5),
    FOREIGN KEY (id_utente) REFERENCES utenti(id_utente) ON DELETE CASCADE
);

-- -------------------------------------------------------------
-- TABELLA: cv_responsabili
-- CV in formato PDF per i responsabili aziendali
-- -------------------------------------------------------------
CREATE TABLE cv_responsabili (
    id_cv INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL UNIQUE,
    percorso_file VARCHAR(500) NOT NULL,
    data_caricamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES utenti(id_utente) ON DELETE CASCADE
);

-- -------------------------------------------------------------
-- TABELLA: aziende
-- Aziende registrate dai responsabili aziendali
-- -------------------------------------------------------------
CREATE TABLE aziende (
    id_azienda INT AUTO_INCREMENT PRIMARY KEY,
    ragione_sociale VARCHAR(255) NOT NULL UNIQUE,
    partita_iva VARCHAR(20) NOT NULL UNIQUE,
    indirizzo VARCHAR(300),
    settore VARCHAR(150),
    percorso_logo VARCHAR(500),
    id_responsabile INT NOT NULL,
    nrbilanci INT DEFAULT 0, -- campo ridondante per ottimizzazione
    FOREIGN KEY (id_responsabile) REFERENCES utenti(id_utente)
);

-- -------------------------------------------------------------
-- TABELLA: voci_template
-- Voci contabili condivise nel template di bilancio (gestite da admin)
-- -------------------------------------------------------------
CREATE TABLE voci_template (
    id_voce INT AUTO_INCREMENT PRIMARY KEY,
    nome_voce VARCHAR(255) NOT NULL UNIQUE,
    descrizione TEXT
);

-- -------------------------------------------------------------
-- TABELLA: bilanci
-- Bilanci di esercizio delle aziende
-- -------------------------------------------------------------
CREATE TABLE bilanci (
    id_bilancio INT AUTO_INCREMENT PRIMARY KEY,
    id_azienda INT NOT NULL,
    anno_esercizio YEAR NOT NULL,
    data_creazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    stato ENUM('bozza','in_revisione','approvato','respinto') DEFAULT 'bozza',
    FOREIGN KEY (id_azienda) REFERENCES aziende(id_azienda) ON DELETE CASCADE
);

-- -------------------------------------------------------------
-- TABELLA: valori_voci_bilancio
-- Valori delle singole voci contabili per un bilancio
-- -------------------------------------------------------------
CREATE TABLE valori_voci_bilancio (
    id_valore INT AUTO_INCREMENT PRIMARY KEY,
    id_bilancio INT NOT NULL,
    id_voce INT NOT NULL,
    valore DECIMAL(18,2),
    note TEXT,
    UNIQUE KEY uq_bilancio_voce (id_bilancio, id_voce),
    FOREIGN KEY (id_bilancio) REFERENCES bilanci(id_bilancio) ON DELETE CASCADE,
    FOREIGN KEY (id_voce) REFERENCES voci_template(id_voce)
);

-- -------------------------------------------------------------
-- TABELLA: indicatori_esg
-- Indicatori ESG (gestiti da admin)
-- -------------------------------------------------------------
CREATE TABLE indicatori_esg (
    id_indicatore INT AUTO_INCREMENT PRIMARY KEY,
    nome_indicatore VARCHAR(255) NOT NULL UNIQUE,
    descrizione TEXT,
    percorso_immagine VARCHAR(500),
    rilevanza TINYINT NOT NULL DEFAULT 5 CHECK (rilevanza BETWEEN 0 AND 10),
    categoria ENUM('ambientale','sociale','altro') NOT NULL
);

-- -------------------------------------------------------------
-- TABELLA: indicatori_ambientali
-- Specializzazione indicatori ESG per categoria ambientale
-- -------------------------------------------------------------
CREATE TABLE indicatori_ambientali (
    id_indicatore INT PRIMARY KEY,
    codice_normativa VARCHAR(100),
    FOREIGN KEY (id_indicatore) REFERENCES indicatori_esg(id_indicatore) ON DELETE CASCADE
);

-- -------------------------------------------------------------
-- TABELLA: indicatori_sociali
-- Specializzazione indicatori ESG per categoria sociale
-- -------------------------------------------------------------
CREATE TABLE indicatori_sociali (
    id_indicatore INT PRIMARY KEY,
    ambito VARCHAR(150),
    frequenza_rilevazione VARCHAR(100),
    FOREIGN KEY (id_indicatore) REFERENCES indicatori_esg(id_indicatore) ON DELETE CASCADE
);

-- -------------------------------------------------------------
-- TABELLA: valori_indicatori_esg
-- Collegamento voce contabile - indicatore ESG con valore rilevato
-- -------------------------------------------------------------
CREATE TABLE valori_indicatori_esg (
    id_valore_esg INT AUTO_INCREMENT PRIMARY KEY,
    id_valore_voce INT NOT NULL,
    id_indicatore INT NOT NULL,
    valore_esg DECIMAL(18,4),
    fonte VARCHAR(300),
    data_rilevazione DATE,
    UNIQUE KEY uq_voce_indicatore (id_valore_voce, id_indicatore),
    FOREIGN KEY (id_valore_voce) REFERENCES valori_voci_bilancio(id_valore) ON DELETE CASCADE,
    FOREIGN KEY (id_indicatore) REFERENCES indicatori_esg(id_indicatore)
);

-- -------------------------------------------------------------
-- TABELLA: assegnazioni_revisori
-- Assegnazione dei revisori ESG ai bilanci (fatta da admin)
-- -------------------------------------------------------------
CREATE TABLE assegnazioni_revisori (
    id_assegnazione INT AUTO_INCREMENT PRIMARY KEY,
    id_bilancio INT NOT NULL,
    id_revisore INT NOT NULL,
    data_assegnazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_bilancio_revisore (id_bilancio, id_revisore),
    FOREIGN KEY (id_bilancio) REFERENCES bilanci(id_bilancio) ON DELETE CASCADE,
    FOREIGN KEY (id_revisore) REFERENCES utenti(id_utente)
);

-- -------------------------------------------------------------
-- TABELLA: note_revisione
-- Note inserite dai revisori per singole voci del bilancio
-- -------------------------------------------------------------
CREATE TABLE note_revisione (
    id_nota INT AUTO_INCREMENT PRIMARY KEY,
    id_assegnazione INT NOT NULL,
    id_valore_voce INT NOT NULL,
    testo_nota TEXT NOT NULL,
    data_nota DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_assegnazione) REFERENCES assegnazioni_revisori(id_assegnazione) ON DELETE CASCADE,
    FOREIGN KEY (id_valore_voce) REFERENCES valori_voci_bilancio(id_valore) ON DELETE CASCADE
);

-- -------------------------------------------------------------
-- TABELLA: giudizi_revisione
-- Giudizio complessivo del revisore sul bilancio
-- -------------------------------------------------------------
CREATE TABLE giudizi_revisione (
    id_giudizio INT AUTO_INCREMENT PRIMARY KEY,
    id_assegnazione INT NOT NULL UNIQUE,
    esito ENUM('approvazione','approvazione_con_rilievi','respingimento') NOT NULL,
    rilievi TEXT,
    data_giudizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_assegnazione) REFERENCES assegnazioni_revisori(id_assegnazione) ON DELETE CASCADE
);

-- -------------------------------------------------------------
-- Dati iniziali: ruoli
-- -------------------------------------------------------------
INSERT INTO ruoli (nome_ruolo) VALUES
    ('amministratore'),
    ('revisore_esg'),
    ('responsabile_aziendale');
