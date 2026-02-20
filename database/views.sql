-- =============================================================
-- ESG-BALANCE - Viste (Statistiche)
-- =============================================================
USE esg_balance;

-- -------------------------------------------------------------
-- VISTA 1: v_numero_aziende
-- Numero totale di aziende registrate
-- -------------------------------------------------------------
CREATE OR REPLACE VIEW v_numero_aziende AS
SELECT COUNT(*) AS totale_aziende
FROM aziende;

-- -------------------------------------------------------------
-- VISTA 2: v_numero_revisori
-- Numero totale di revisori ESG registrati
-- -------------------------------------------------------------
CREATE OR REPLACE VIEW v_numero_revisori AS
SELECT COUNT(*) AS totale_revisori
FROM utenti u
JOIN ruoli r ON u.id_ruolo = r.id_ruolo
WHERE r.nome_ruolo = 'revisore_esg';

-- -------------------------------------------------------------
-- VISTA 3: v_affidabilita_aziende
-- Classifica aziende per affidabilita:
-- percentuale di bilanci approvati senza rilievi
-- (esclude bilanci approvazione_con_rilievi e respinti)
-- -------------------------------------------------------------
CREATE OR REPLACE VIEW v_affidabilita_aziende AS
SELECT
    a.id_azienda,
    a.ragione_sociale,
    COUNT(b.id_bilancio) AS totale_bilanci,
    SUM(
        CASE
            WHEN b.stato = 'approvato'
             AND NOT EXISTS (
                SELECT 1 FROM giudizi_revisione gr
                JOIN assegnazioni_revisori ar ON gr.id_assegnazione = ar.id_assegnazione
                WHERE ar.id_bilancio = b.id_bilancio
                  AND gr.esito = 'approvazione_con_rilievi'
             )
            THEN 1 ELSE 0
        END
    ) AS bilanci_approvati_senza_rilievi,
    ROUND(
        100.0 * SUM(
            CASE
                WHEN b.stato = 'approvato'
                 AND NOT EXISTS (
                    SELECT 1 FROM giudizi_revisione gr
                    JOIN assegnazioni_revisori ar ON gr.id_assegnazione = ar.id_assegnazione
                    WHERE ar.id_bilancio = b.id_bilancio
                      AND gr.esito = 'approvazione_con_rilievi'
                 )
                THEN 1 ELSE 0
            END
        ) / NULLIF(COUNT(b.id_bilancio), 0),
    2) AS percentuale_affidabilita
FROM aziende a
LEFT JOIN bilanci b ON a.id_azienda = b.id_azienda
GROUP BY a.id_azienda, a.ragione_sociale
ORDER BY percentuale_affidabilita DESC;

-- -------------------------------------------------------------
-- VISTA 4: v_classifica_bilanci_esg
-- Classifica bilanci in base al numero totale di
-- indicatori ESG collegati alle voci contabili
-- -------------------------------------------------------------
CREATE OR REPLACE VIEW v_classifica_bilanci_esg AS
SELECT
    b.id_bilancio,
    a.ragione_sociale,
    b.anno_esercizio,
    b.stato,
    COUNT(vie.id_valore_esg) AS totale_indicatori_esg
FROM bilanci b
JOIN aziende a ON b.id_azienda = a.id_azienda
LEFT JOIN valori_voci_bilancio vvb ON b.id_bilancio = vvb.id_bilancio
LEFT JOIN valori_indicatori_esg vie ON vvb.id_valore = vie.id_valore_voce
GROUP BY b.id_bilancio, a.ragione_sociale, b.anno_esercizio, b.stato
ORDER BY totale_indicatori_esg DESC;
