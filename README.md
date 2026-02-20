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
