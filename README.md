# ProgettoBasi - ESG-BALANCE

Progetto universitario per il corso di **Basi di Dati** - Piattaforma ESG-BALANCE.

## Descrizione

ESG-BALANCE e' una piattaforma web per la gestione e revisione di bilanci ESG (Environmental, Social, Governance) aziendali. Permette a tre tipologie di utenti di collaborare nella creazione, compilazione e revisione dei bilanci di sostenibilita'.

## Architettura del Sistema

- **Frontend**: HTML/CSS (Bootstrap) + PHP
- **Backend**: Apache + PHP (stack AMP)
- **DB Relazionale**: MySQL (dati strutturati, procedure, trigger, viste)
- **DB NoSQL**: MongoDB (log eventi)

## Struttura del Progetto

```
ProgettoBasi/
|
|-- database/                  # SQL del database MySQL
|   |-- schema.sql             # Schema completo (tabelle, chiavi, vincoli)
|   |-- triggers.sql           # 4 trigger (stato bilancio + nrbilanci)
|   |-- procedures.sql         # 10 stored procedure
|   |-- views.sql              # 4 viste statistiche
|
|-- app/                       # Applicazione Web PHP
|   |-- config/
|   |   |-- db_config.example.php   # Template config MySQL (rinomina in db_config.php)
|   |
|   |-- includes/
|   |   |-- auth.php           # Autenticazione, sessioni, controllo ruoli
|   |
|   |-- pages/
|   |   |-- login.php          # Pagina di login
|   |   |-- dashboard.php      # Redirect alla dashboard per ruolo
|   |   |
|   |   |-- admin/             # Pagine Amministratore
|   |   |   |-- dashboard_admin.php
|   |   |   |-- gestione_indicatori.php    (da implementare)
|   |   |   |-- gestione_template.php      (da implementare)
|   |   |   |-- assegna_revisori.php       (da implementare)
|   |   |   |-- statistiche.php            (da implementare)
|   |   |
|   |   |-- revisore/          # Pagine Revisore ESG
|   |   |   |-- dashboard_revisore.php
|   |   |   |-- gestione_competenze.php    (da implementare)
|   |   |   |-- revisione_bilancio.php     (da implementare)
|   |   |   |-- inserisci_giudizio.php     (da implementare)
|   |   |
|   |   |-- responsabile/      # Pagine Responsabile Aziendale
|   |       |-- dashboard_responsabile.php
|   |       |-- registra_azienda.php       (da implementare)
|   |       |-- gestione_bilanci.php       (da implementare)
|   |       |-- inserisci_valori_esg.php   (da implementare)
|   |
|   |-- assets/
|       |-- css/style.css      (da implementare)
|
|-- mongodb/
|   |-- log_events.php         # Sistema di logging eventi su MongoDB
|
|-- .gitignore
|-- README.md
```

## Ruoli Utente

| Ruolo | Funzionalita' |
|---|---|
| **Amministratore** | Gestione indicatori ESG, template bilancio, assegnazione revisori, statistiche |
| **Revisore ESG** | Gestione competenze, note sulle voci, giudizi sui bilanci |
| **Responsabile Aziendale** | Registrazione aziende, creazione bilanci, inserimento valori indicatori ESG |

## Database MySQL - Componenti

### Tabelle principali
- `ruoli`, `utenti`, `email_utenti` - Anagrafica utenti e ruoli
- `competenze_revisori`, `cv_responsabili` - Specializzazioni utenti
- `aziende` - Aziende con campo ridondante `nrbilanci`
- `voci_template`, `bilanci`, `valori_voci_bilancio` - Bilanci
- `indicatori_esg`, `indicatori_ambientali`, `indicatori_sociali` - Indicatori ESG
- `valori_indicatori_esg` - Link voce contabile <-> indicatore ESG
- `assegnazioni_revisori`, `note_revisione`, `giudizi_revisione` - Revisione

### Trigger
- `trg_stato_in_revisione` - Stato bilancio -> 'in_revisione' all'assegnazione revisore
- `trg_aggiorna_stato_bilancio` - Aggiorna stato a 'approvato'/'respinto' dopo tutti i giudizi
- `trg_incrementa_nrbilanci` - Mantiene ridondanza nrbilanci
- `trg_decrementa_nrbilanci` - Mantiene ridondanza nrbilanci

### Viste statistiche
- `v_numero_aziende` - Totale aziende
- `v_numero_revisori` - Totale revisori ESG
- `v_affidabilita_aziende` - Classifica per % bilanci approvati senza rilievi
- `v_classifica_bilanci_esg` - Classifica bilanci per numero indicatori ESG

## Setup

1. Clona la repository
2. Importa `database/schema.sql` su MySQL
3. Importa `database/triggers.sql`, `procedures.sql`, `views.sql`
4. Copia `app/config/db_config.example.php` in `app/config/db_config.php` e configura le credenziali
5. Installa il driver MongoDB per PHP: `composer require mongodb/mongodb`
6. Avvia Apache e accedi a `/app/pages/login.php`

## Autore

Tommus129 - Corso di Basi di Dati

---

## Schema Database

| Tabella | Descrizione |
|---|---|
| `Utenti` | Utenti del sistema (admin, revisore, azienda) |
| `Aziende` | Anagrafica aziende registrate |
| `IndicatoriESG` | Indicatori E/S/G configurabili dall'admin |
| `Bilanci` | Bilanci ESG per anno e azienda |
| `ValoriBilancio` | Valori numerici degli indicatori per ogni bilancio |
| `Revisioni` | Storico revisioni con esito e commento |

---

## Ruoli Utente

| Ruolo | Permessi |
|---|---|
| `admin` | Gestione completa: utenti, aziende, indicatori, assegnazione revisori |
| `revisore` | Visualizza bilanci assegnati, approva / rifiuta / richiede modifiche |
| `azienda` | Inserisce bilanci ESG, invia per revisione, monitora lo stato |

---

## Tecnologie Utilizzate

| Tecnologia | Versione | Utilizzo |
|---|---|---|
| PHP | >= 8.0 | Backend e logica applicativa |
| MySQL | >= 8.0 | Database relazionale principale |
| MongoDB | >= 6.0 | Log delle azioni utente |
| Apache | >= 2.4 | Web server |
| HTML5 / CSS3 | - | Interfaccia utente |
| JavaScript | ES6+ | Interattivita' lato client |

---

## Flusso Applicativo

```
[Azienda]
  1. Accede al sistema tramite login
  2. Crea un nuovo bilancio ESG (stato: bozza)
  3. Inserisce i valori per ogni indicatore E / S / G
  4. Invia il bilancio per revisione

[Admin]
  - Gestisce utenti, aziende e indicatori ESG
  - Assegna un revisore al bilancio inviato

[Revisore]
  1. Visualizza i bilanci assegnati nella sua dashboard
  2. Consulta i valori di ogni indicatore
  3. Approva, rifiuta o richiede modifiche con commento

[MongoDB - Log]
  - Ogni azione viene registrata: login, logout,
    invio bilancio, revisione, con timestamp e IP
```

---

## Sicurezza

- Password cifrate con `password_hash()` (algoritmo bcrypt)
- Protezione SQL injection tramite **PDO prepared statements**
- Controllo ruoli su ogni pagina tramite `requireRole()` in `auth.php`
- Rigenerazione session ID al login (`session_regenerate_id(true)`)
- Corretta distruzione della sessione al logout

---

> **Corso di Basi di Dati** - Anno Accademico 2024/2025
> Universita' di Bologna
