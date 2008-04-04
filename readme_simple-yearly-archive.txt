Plugin Name: Simple Yearly Archive
Plugin URI:  http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/
Description: Simple Yearly Archive ist ein recht einfaches Wordpress Plugin, das die Wordpress-Archive nicht wie gewohnt monatsbasierend darstellt, sondern in einer jahresbasierenden Liste.
Author:      Oliver Schlöbe
Version:     0.5
Author URI:  http://www.schloebe.de/


Installation
------------

   1. Downloaden Sie das Plugin und entpacken Sie es.
   2. Laden Sie die Datei simple-yearly-archive.php in den /wp-content/plugins/ Ordner hoch.
   3. Aktivieren Sie das Plugin im Wordpress Administrationsbereich.
   4. Fertig die Installation.


Implementierung
---------------

Die Funktion muss wie folgt aufgerufen werden:

<?php simpleYearlyArchive('type','CategoryIDs','before','after'); ?>

Die folgenden Optionen stehen zur Verfügung:

    * type:
      - yearly: Die Liste wird jahresbasierend dargestellt (Default). Der Wert kann auch leer gelassen werden.
      - yearly_act: Nur die Beiträge des aktuellen Jahres werden dargestellt.
      - yearly_past: Nur die Beiträge der vergangenen Jahre werden dargestellt.
    * CategoryIDs:
      - Eine kommaseparierte Liste der Kategorie-IDs, die Sie ausschließen wollen.
    * before:
      - Ein HTML-Tag. Dieser wird vor den übergeordneten Jahreszahlen eingefügt; Standard ist <h1>.
    * after:
      - Ein HTML-Tag. Dieser wird nach den übergeordneten Jahreszahlen eingefügt; Standard ist </h1>.

Folgende Aufrufe sind Beispiele und funktionsfähig:

<?php simpleYearlyArchive(); ?>
<?php simpleYearlyArchive('','1','<h2>','</h2>'); ?>
<?php simpleYearlyArchive('yearly','4,7,9','',''); ?>
<?php simpleYearlyArchive('','','',''); ?>
<?php simpleYearlyArchive('yearly_act','','<h3>','</h3>'); ?>
<?php simpleYearlyArchive('yearly_past','','<h3>','</h3>'); ?>


Hinweis
-------

Da Wordpress in Beiträgen und Seiten eingefügten PHP-Code standardmäßig nicht ausführt, wird dafür ein Plugin benötigt. Empfehlen kann ich Exec-PHP, da ich dieses selbst nutze und es seinen Dienst prima leistet: http://bluesome.net/post/2005/08/18/50/


Versionshinweise
----------------

    * Beiträge mit einem Datum in der Zukunft werden nicht angezeigt, ebensowenig wie statische Seiten.
    * Private sowie im Entwurf befindliche Beiträge werden nicht angezeigt.
    * Jahreslinks werden angezeigt, auch wenn es keine Beiträge darin gibt. Der Übersicht halber.
    * Die Benutzung des Plugins erfolgt auf eigene Gefahr.