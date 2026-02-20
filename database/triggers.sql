-- =============================================================
-- ESG-BALANCE - Trigger
-- =============================================================
USE esg_balance;

DELIMITER $$

-- -------------------------------------------------------------
-- TRIGGER 1: trg_stato_in_revisione
-- Quando viene inserita una nuova assegnazione revisore,
-- lo stato del bilancio passa automaticamente a 'in_revisione'
-- -------------------------------------------------------------
CREATE TRIGGER trg_stato_in_revisione
AFTER INSERT ON assegnazioni_revisori
FOR EACH ROW
BEGIN
    UPDATE bilanci
    SET stato = 'in_revisione'
    WHERE id_bilancio = NEW.id_bilancio
      AND stato = 'bozza';
END$$

-- -------------------------------------------------------------
-- TRIGGER 2: trg_aggiorna_stato_bilancio
-- Quando viene inserito un giudizio di revisione:
-- - Se tutti i revisori assegnati hanno espresso giudizio:
--   * Se almeno uno ha 'respingimento' -> stato = 'respinto'
--   * Altrimenti -> stato = 'approvato'
-- -------------------------------------------------------------
CREATE TRIGGER trg_aggiorna_stato_bilancio
AFTER INSERT ON giudizi_revisione
FOR EACH ROW
BEGIN
    DECLARE v_id_bilancio INT;
    DECLARE v_totale_revisori INT;
    DECLARE v_totale_giudizi INT;
    DECLARE v_respingimenti INT;

    -- Recupero l'id del bilancio dall'assegnazione
    SELECT id_bilancio INTO v_id_bilancio
    FROM assegnazioni_revisori
    WHERE id_assegnazione = NEW.id_assegnazione;

    -- Conto i revisori totali assegnati a quel bilancio
    SELECT COUNT(*) INTO v_totale_revisori
    FROM assegnazioni_revisori
    WHERE id_bilancio = v_id_bilancio;

    -- Conto i giudizi gia espressi per quel bilancio
    SELECT COUNT(*) INTO v_totale_giudizi
    FROM giudizi_revisione gr
    JOIN assegnazioni_revisori ar ON gr.id_assegnazione = ar.id_assegnazione
    WHERE ar.id_bilancio = v_id_bilancio;

    -- Se tutti i revisori hanno giudicato
    IF v_totale_giudizi = v_totale_revisori THEN

        -- Conto i respingimenti
        SELECT COUNT(*) INTO v_respingimenti
        FROM giudizi_revisione gr
        JOIN assegnazioni_revisori ar ON gr.id_assegnazione = ar.id_assegnazione
        WHERE ar.id_bilancio = v_id_bilancio
          AND gr.esito = 'respingimento';

        IF v_respingimenti > 0 THEN
            UPDATE bilanci SET stato = 'respinto' WHERE id_bilancio = v_id_bilancio;
        ELSE
            UPDATE bilanci SET stato = 'approvato' WHERE id_bilancio = v_id_bilancio;
        END IF;

    END IF;
END$$

-- -------------------------------------------------------------
-- TRIGGER 3: trg_incrementa_nrbilanci
-- Aggiorna il campo ridondante nrbilanci in aziende
-- quando viene inserito un nuovo bilancio
-- -------------------------------------------------------------
CREATE TRIGGER trg_incrementa_nrbilanci
AFTER INSERT ON bilanci
FOR EACH ROW
BEGIN
    UPDATE aziende
    SET nrbilanci = nrbilanci + 1
    WHERE id_azienda = NEW.id_azienda;
END$$

-- -------------------------------------------------------------
-- TRIGGER 4: trg_decrementa_nrbilanci
-- Aggiorna il campo ridondante nrbilanci in aziende
-- quando viene eliminato un bilancio
-- -------------------------------------------------------------
CREATE TRIGGER trg_decrementa_nrbilanci
AFTER DELETE ON bilanci
FOR EACH ROW
BEGIN
    UPDATE aziende
    SET nrbilanci = GREATEST(nrbilanci - 1, 0)
    WHERE id_azienda = OLD.id_azienda;
END$$

DELIMITER ;
