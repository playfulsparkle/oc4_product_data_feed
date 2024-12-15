<?php
// Heading
$_['heading_title']                = 'Playful Sparkle - Google Base';
$_['heading_categories']           = 'Associazioni Categorie';
$_['heading_authentication']       = 'Autenticazione';
$_['heading_tax_deffinitions']     = 'Definizioni Fiscali';
$_['heading_getting_started']      = 'Introduzione';
$_['heading_setup']                = 'Configurazione di Google Base';
$_['heading_troubleshot']          = 'Risoluzione dei Problemi Comuni';
$_['heading_faq']                  = 'Domande Frequenti';
$_['heading_contact']              = 'Contatta il Supporto';

// Text
$_['text_extension']               = 'Estensioni';
$_['text_success']                 = 'Successo: Hai modificato il feed di Google Base!';
$_['text_import_success']          = 'Successo: Hai importato correttamente la lista delle categorie di Google!';
$_['text_add_category_success']    = 'Successo: Hai aggiunto correttamente l\'associazione della categoria!';
$_['text_remove_category_success'] = 'Successo: Hai rimosso correttamente l\'associazione della categoria!';
$_['text_edit']                    = 'Modifica Google Base';
$_['text_import']                  = 'Per scaricare l\'ultima lista delle categorie di Google, <a href="https://support.google.com/merchants/answer/160081?hl=en" target="_blank" rel="external noopener noreferrer" class="alert-link">clicca qui</a> e seleziona la tassonomia con ID numerici in un file di testo (.txt). Successivamente, carica il file scaricato utilizzando il pulsante verde.';
$_['text_getting_started']         = '<p><strong>Panoramica:</strong> Google Base per OpenCart 4.x consente agli utenti di esportare facilmente i loro prodotti su <a href="https://merchants.google.com" target="_blank" rel="external noopener noreferrer">Google Merchant Center</a> in formato XML. Questo strumento essenziale aumenta la visibilità dei prodotti su Google Shopping, facilitando la scoperta e l\'acquisto da parte dei potenziali clienti.</p><p><strong>Requisiti:</strong> Per utilizzare questa estensione, assicurati di avere OpenCart 4.x+, PHP 7.4+ installato e configurato. Inoltre, è necessario un account attivo su Google Merchant Center per gestire efficacemente le tue inserzioni di prodotti.</p>';
$_['text_setup']                   = '<ul><li>Scarica l\'ultima lista delle categorie di Google da <a href="https://support.google.com/merchants/answer/160081?hl=en" target="_blank" rel="external noopener noreferrer">Google Merchant Center Support</a>.</li><li>Associa le categorie del tuo negozio alle categorie appropriate di Google.</li><li>Configura l\'estensione per escludere i prodotti esauriti, se desiderato.</li><li>Usa i prezzi al netto delle tasse nel feed abilitando l\'opzione corrispondente e personalizza le definizioni fiscali di conseguenza.</li></ul>';
$_['text_troubleshot']             = '<ul><li><strong>Le categorie di Google non vengono visualizzate:</strong> Assicurati di aver scaricato l\'ultima lista delle categorie di Google e di averle importate nel tuo sistema. Questo è essenziale per una corretta mappatura tra il tuo negozio online e Google Merchant Center.</li><li><strong>Nessun output del feed di prodotto:</strong> Verifica che l\'estensione Google Base sia abilitata nel pannello di amministrazione di OpenCart. Se è abilitata e non vedi ancora alcun output, controlla le impostazioni dell\'estensione per eventuali configurazioni errate.</li><li><strong>I prodotti non appaiono in Google Merchant Center:</strong> Verifica che i tuoi prodotti siano correttamente classificati e che non ci siano errori nei dati del prodotto. Assicurati di rispettare le politiche di Google Merchant Center riguardanti le inserzioni di prodotti.</li></ul>';
$_['text_faq']                     = '<details><summary>Cos\'è l\'estensione Google Base?</summary><p>L\'estensione Google Base aiuta gli utenti di OpenCart 4.x a esportare i dati dei loro prodotti su Google Merchant Center, aumentando la visibilità su Google Shopping.</p></details><details><summary>Come attivo l\'estensione Google Base?</summary><p>Puoi attivare l\'estensione dal pannello di amministrazione di OpenCart nella sezione Estensioni. Assicurati di configurare le impostazioni come necessario.</p></details><details><summary>Posso personalizzare il mio feed di prodotto?</summary><p>Sì, l\'estensione ti consente di personalizzare diverse impostazioni, tra cui l\'uso dei prezzi al netto delle tasse e le definizioni fiscali, assicurando che il feed soddisfi le tue esigenze.</p></details><details><summary>Perché il mio feed di prodotto non appare in Google Merchant Center?</summary><p>Assicurati che l\'estensione Google Base sia abilitata e che le categorie dei tuoi prodotti siano correttamente associate alle categorie di Google. Inoltre, verifica eventuali errori nella configurazione del feed.</p></details>';
$_['text_contact']                 = '<p>Per ulteriore assistenza, contatta il nostro team di supporto:</p><ul><li><strong>Contatto:</strong> <a href="mailto:%s">%s</a></li><li><strong>Documentazione:</strong> <a href="%s" target="_blank" rel="noopener noreferrer">Documentazione utente</a></li></ul>';
$_['text_gbc2c_restore']           = 'Il processo di ripristino è distruttivo e cancellerà le associazioni di categorie correnti per il negozio selezionato. Assicurati di aver selezionato il negozio corretto per l\'importazione e che il file di backup che carichi (gbc2c_backup_store_x.txt) termini con l\'ID del negozio (x) nel quale desideri ripristinare i dati.';

// Column
$_['column_google_category']       = 'Categoria Google';
$_['column_category']              = 'Categoria';
$_['column_action']                = 'Azione';

// Tab
$_['tab_general']                  = 'Generale';
$_['tab_help_and_support']         = 'Aiuto &amp; Supporto';

// Entry
$_['entry_google_category']        = 'Categoria Google';
$_['entry_category']               = 'Categoria';
$_['entry_data_feed_url']          = 'URL del Feed Dati';
$_['entry_status']                 = 'Stato';
$_['entry_login']                  = 'Nome Utente';
$_['entry_password']               = 'Password';
$_['entry_skip_out_of_stock']      = 'Salta Prodotti Esauriti';
$_['entry_tax']                    = 'Usa prezzi al netto delle tasse';
$_['entry_country']                = 'Paese';
$_['entry_region']                 = 'Regione';
$_['entry_tax_rate']               = 'Aliquota Fiscale';
$_['entry_tax_ship']               = 'Tassa di spedizione';
$_['entry_active_store']           = 'Negozio attivo';
$_['entry_additional_images']      = 'Includi immagini aggiuntive';
$_['entry_backup_restore']         = 'Backup/Ripristinare';

// Button
$_['button_backup']                = 'Backup';
$_['button_restore']               = 'Ripristina';

// Help
$_['help_copy']                    = 'Copia URL';
$_['help_open']                    = 'Apri URL';
$_['help_additional_images']       = 'Attivando questa opzione, verranno aggiunte immagini aggiuntive al feed di Google Base. Si noti che questa opzione potrebbe rallentare il processo di generazione del feed e aumentare la dimensione del file XML generato.';

// Error
$_['error_permission']             = 'Attenzione: Non hai permesso di modificare il feed di Google Base!';
$_['error_store_id']               = 'Attenzione: Il modulo non contiene store_id!';
$_['error_currency']               = 'Attenzione: Seleziona una valuta dalla lista';
$_['error_upload']                 = 'Impossibile caricare il file!';
$_['error_filetype']               = 'Tipo di file non valido!';
$_['error_tax_country']            = 'Seleziona il paese per la tassa.';
$_['error_tax_region']             = 'Il campo regione fiscale non può essere lasciato vuoto.';
$_['error_tax_rate_id']            = 'Seleziona l\'aliquota fiscale per la tassa';
$_['error_no_data_to_backup']      = 'Non ci sono dati di associazione delle categorie disponibili per il backup.';
