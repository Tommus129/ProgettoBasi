/**
 * mongodb_logs.js
 * Schema e struttura dei log MongoDB per ESG-BALANCE
 * Collezione: esg_logs
 *
 * Per eseguire questo script: mongosh esg_balance < mongodb_logs.js
 */

// Crea/seleziona il database dei log
use('esg_logs');

// Crea la collezione 'azioni' con validazione
db.createCollection('azioni', {
  validator: {
    $jsonSchema: {
      bsonType: 'object',
      required: ['tipo', 'timestamp'],
      properties: {
        tipo: {
          bsonType: 'string',
          description: 'Tipo di azione (login, logout, crea_bilancio, invia_bilancio, revisione, ecc.)'
        },
        descrizione: {
          bsonType: 'string',
          description: 'Descrizione testuale dell\'azione'
        },
        dati: {
          bsonType: 'object',
          description: 'Dati addizionali specifici per l\'azione'
        },
        id_utente: {
          bsonType: ['int', 'null'],
          description: 'ID utente MySQL che ha eseguito l\'azione'
        },
        ip: {
          bsonType: ['string', 'null'],
          description: 'Indirizzo IP del client'
        },
        timestamp: {
          bsonType: 'date',
          description: 'Timestamp dell\'azione'
        }
      }
    }
  }
});

// Indici per ricerche efficienti
db.azioni.createIndex({ timestamp: -1 });
db.azioni.createIndex({ tipo: 1, timestamp: -1 });
db.azioni.createIndex({ id_utente: 1, timestamp: -1 });

// Esempi di documenti log
const esempioLogin = {
  tipo: 'login',
  descrizione: 'Accesso al sistema',
  dati: { email: 'user@example.com', esito: 'successo' },
  id_utente: 1,
  ip: '127.0.0.1',
  timestamp: new Date()
};

const esempioBilancio = {
  tipo: 'crea_bilancio',
  descrizione: 'Creazione nuovo bilancio ESG',
  dati: { id_bilancio: 1, id_azienda: 1, anno: 2024 },
  id_utente: 3,
  ip: '192.168.1.10',
  timestamp: new Date()
};

const esempioRevisione = {
  tipo: 'revisione',
  descrizione: 'Revisione bilancio completata',
  dati: { id_bilancio: 1, esito: 'approvato', id_revisore: 2 },
  id_utente: 2,
  ip: '10.0.0.5',
  timestamp: new Date()
};

// Inserisci documenti di esempio
db.azioni.insertMany([esempioLogin, esempioBilancio, esempioRevisione]);

print('MongoDB ESG-BALANCE logs configurato con successo.');
print('Collezione: esg_logs.azioni');
print('Indici creati: timestamp, tipo+timestamp, id_utente+timestamp');
