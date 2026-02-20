<?php
// =============================================================
// ESG-BALANCE - Log Eventi su MongoDB
// Richiede: composer require mongodb/mongodb
// Oppure estensione PHP mongodb
// =============================================================

require_once __DIR__ . '/../app/config/db_config.php';

// Costanti MongoDB (configurare prima dell'uso)
define('MONGO_URI',  'mongodb://localhost:27017');
define('MONGO_DB',   'esg_balance_log');
define('MONGO_COLL', 'eventi');

/**
 * Restituisce il client MongoDB.
 * Usa il driver ufficiale mongodb/mongodb.
 */
function getMongoCollection(): MongoDB\Collection {
    $client = new MongoDB\Client(MONGO_URI);
    return $client->selectCollection(MONGO_DB, MONGO_COLL);
}

/**
 * Inserisce un evento di log su MongoDB.
 *
 * @param string $tipo     Tipo evento (es. 'crea_bilancio', 'assegna_revisore')
 * @param string $testo    Descrizione testuale dell'evento
 * @param array  $contesto Dati aggiuntivi (id_utente, id_azienda, id_bilancio, ecc.)
 */
function logEvento(string $tipo, string $testo, array $contesto = []): void {
    try {
        $collection = getMongoCollection();
        $collection->insertOne([
            'tipo'       => $tipo,
            'testo'      => $testo,
            'timestamp'  => new MongoDB\BSON\UTCDateTime(),
            'contesto'   => $contesto,
        ]);
    } catch (Exception $e) {
        // Il logging non deve bloccare l'applicazione
        error_log('[MongoDB Log Error] ' . $e->getMessage());
    }
}

// =============================================================
// ESEMPI DI UTILIZZO (chiamare da PHP dopo ogni operazione)
// =============================================================
//
// logEvento('crea_bilancio', 'Creato bilancio 2024 per azienda XYZ', [
//     'id_utente'  => 5,
//     'id_azienda' => 3,
//     'id_bilancio'=> 12,
// ]);
//
// logEvento('assegna_revisore', 'Assegnato revisore ID 7 al bilancio 12', [
//     'id_revisore' => 7,
//     'id_bilancio' => 12,
// ]);
//
// logEvento('inserisci_giudizio', 'Revisore ID 7 ha inserito giudizio sul bilancio 12', [
//     'id_revisore'    => 7,
//     'id_bilancio'    => 12,
//     'esito'          => 'approvazione',
// ]);
//
// logEvento('login', 'Utente username ha effettuato il login', [
//     'id_utente'  => 5,
//     'username'   => 'mario.rossi',
// ]);
//
// logEvento('registra_azienda', 'Registrata nuova azienda: ACME Srl', [
//     'id_responsabile' => 5,
//     'id_azienda'      => 10,
// ]);
?>
