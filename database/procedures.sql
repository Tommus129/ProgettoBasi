-- =============================================================
-- ESG-BALANCE - Stored Procedure
-- =============================================================
USE esg_balance;

DELIMITER $$

-- -------------------------------------------------------------
-- SP: sp_registra_utente
-- Registra un nuovo utente nel sistema
-- -------------------------------------------------------------
CREATE PROCEDURE sp_registra_utente(
    IN p_nome VARCHAR(100),
    IN p_cognome VARCHAR(100),
    IN p_username VARCHAR(100),
    IN p_password_hash VARCHAR(255),
    IN p_nome_ruolo VARCHAR(50),
    IN p_email VARCHAR(255)
)
BEGIN
    DECLARE v_id_ruolo INT;
    DECLARE v_id_utente INT;

    SELECT id_ruolo INTO v_id_ruolo FROM ruoli WHERE nome_ruolo = p_nome_ruolo;

    INSERT INTO utenti (nome, cognome, username, password_hash, id_ruolo)
    VALUES (p_nome, p_cognome, p_username, p_password_hash, v_id_ruolo);

    SET v_id_utente = LAST_INSERT_ID();

    INSERT INTO email_utenti (id_utente, email) VALUES (v_id_utente, p_email);
END$$

-- -------------------------------------------------------------
-- SP: sp_registra_azienda
-- Registra una nuova azienda associata a un responsabile
-- -------------------------------------------------------------
CREATE PROCEDURE sp_registra_azienda(
    IN p_ragione_sociale VARCHAR(255),
    IN p_partita_iva VARCHAR(20),
    IN p_indirizzo VARCHAR(300),
    IN p_settore VARCHAR(150),
    IN p_percorso_logo VARCHAR(500),
    IN p_id_responsabile INT
)
BEGIN
    INSERT INTO aziende (ragione_sociale, partita_iva, indirizzo, settore, percorso_logo, id_responsabile)
    VALUES (p_ragione_sociale, p_partita_iva, p_indirizzo, p_settore, p_percorso_logo, p_id_responsabile);
END$$

-- -------------------------------------------------------------
-- SP: sp_crea_bilancio
-- Crea un nuovo bilancio in bozza e popola le voci dal template
-- -------------------------------------------------------------
CREATE PROCEDURE sp_crea_bilancio(
    IN p_id_azienda INT,
    IN p_anno_esercizio YEAR
)
BEGIN
    DECLARE v_id_bilancio INT;

    INSERT INTO bilanci (id_azienda, anno_esercizio, stato)
    VALUES (p_id_azienda, p_anno_esercizio, 'bozza');

    SET v_id_bilancio = LAST_INSERT_ID();

    -- Popola automaticamente le voci dal template
    INSERT INTO valori_voci_bilancio (id_bilancio, id_voce, valore)
    SELECT v_id_bilancio, id_voce, NULL
    FROM voci_template;

    SELECT v_id_bilancio AS id_bilancio_creato;
END$$

-- -------------------------------------------------------------
-- SP: sp_aggiorna_valore_voce
-- Aggiorna il valore di una voce contabile in un bilancio
-- -------------------------------------------------------------
CREATE PROCEDURE sp_aggiorna_valore_voce(
    IN p_id_bilancio INT,
    IN p_id_voce INT,
    IN p_valore DECIMAL(18,2),
    IN p_note TEXT
)
BEGIN
    UPDATE valori_voci_bilancio
    SET valore = p_valore, note = p_note
    WHERE id_bilancio = p_id_bilancio AND id_voce = p_id_voce;
END$$

-- -------------------------------------------------------------
-- SP: sp_inserisci_indicatore_esg
-- Inserisce un nuovo indicatore ESG (solo admin)
-- -------------------------------------------------------------
CREATE PROCEDURE sp_inserisci_indicatore_esg(
    IN p_nome VARCHAR(255),
    IN p_descrizione TEXT,
    IN p_immagine VARCHAR(500),
    IN p_rilevanza TINYINT,
    IN p_categoria ENUM('ambientale','sociale','altro')
)
BEGIN
    INSERT INTO indicatori_esg (nome_indicatore, descrizione, percorso_immagine, rilevanza, categoria)
    VALUES (p_nome, p_descrizione, p_immagine, p_rilevanza, p_categoria);
END$$

-- -------------------------------------------------------------
-- SP: sp_inserisci_valore_indicatore
-- Collega un valore ESG a una voce contabile di un bilancio
-- -------------------------------------------------------------
CREATE PROCEDURE sp_inserisci_valore_indicatore(
    IN p_id_valore_voce INT,
    IN p_id_indicatore INT,
    IN p_valore_esg DECIMAL(18,4),
    IN p_fonte VARCHAR(300),
    IN p_data_rilevazione DATE
)
BEGIN
    INSERT INTO valori_indicatori_esg (id_valore_voce, id_indicatore, valore_esg, fonte, data_rilevazione)
    VALUES (p_id_valore_voce, p_id_indicatore, p_valore_esg, p_fonte, p_data_rilevazione)
    ON DUPLICATE KEY UPDATE
        valore_esg = p_valore_esg,
        fonte = p_fonte,
        data_rilevazione = p_data_rilevazione;
END$$

-- -------------------------------------------------------------
-- SP: sp_assegna_revisore
-- Assegna un revisore ESG a un bilancio (solo admin)
-- -------------------------------------------------------------
CREATE PROCEDURE sp_assegna_revisore(
    IN p_id_bilancio INT,
    IN p_id_revisore INT
)
BEGIN
    INSERT INTO assegnazioni_revisori (id_bilancio, id_revisore)
    VALUES (p_id_bilancio, p_id_revisore);
    -- Il trigger trg_stato_in_revisione si attiva automaticamente
END$$

-- -------------------------------------------------------------
-- SP: sp_inserisci_nota_revisione
-- Il revisore inserisce una nota su una voce contabile
-- -------------------------------------------------------------
CREATE PROCEDURE sp_inserisci_nota_revisione(
    IN p_id_assegnazione INT,
    IN p_id_valore_voce INT,
    IN p_testo_nota TEXT
)
BEGIN
    INSERT INTO note_revisione (id_assegnazione, id_valore_voce, testo_nota)
    VALUES (p_id_assegnazione, p_id_valore_voce, p_testo_nota);
END$$

-- -------------------------------------------------------------
-- SP: sp_inserisci_giudizio
-- Il revisore inserisce il giudizio complessivo sul bilancio
-- -------------------------------------------------------------
CREATE PROCEDURE sp_inserisci_giudizio(
    IN p_id_assegnazione INT,
    IN p_esito ENUM('approvazione','approvazione_con_rilievi','respingimento'),
    IN p_rilievi TEXT
)
BEGIN
    INSERT INTO giudizi_revisione (id_assegnazione, esito, rilievi)
    VALUES (p_id_assegnazione, p_esito, p_rilievi);
    -- Il trigger trg_aggiorna_stato_bilancio si attiva automaticamente
END$$

-- -------------------------------------------------------------
-- SP: sp_aggiungi_competenza_revisore
-- Un revisore aggiunge una propria competenza
-- -------------------------------------------------------------
CREATE PROCEDURE sp_aggiungi_competenza_revisore(
    IN p_id_utente INT,
    IN p_nome_competenza VARCHAR(150),
    IN p_livello TINYINT
)
BEGIN
    INSERT INTO competenze_revisori (id_utente, nome_competenza, livello)
    VALUES (p_id_utente, p_nome_competenza, p_livello);
END$$

-- -------------------------------------------------------------
-- SP: sp_aggiungi_voce_template
-- Admin aggiunge una voce al template di bilancio
-- -------------------------------------------------------------
CREATE PROCEDURE sp_aggiungi_voce_template(
    IN p_nome_voce VARCHAR(255),
    IN p_descrizione TEXT
)
BEGIN
    INSERT INTO voci_template (nome_voce, descrizione)
    VALUES (p_nome_voce, p_descrizione);
END$$

DELIMITER ;
