<?php

class xetCache {
        // Gibt an, ob die Dateinamen mit sha1 gehasht werden  sollen, oder nicht
        var $bHashFilenamesSHA1 = true;

        // Die Lebenszeit in Sekunden (1800 Sekunden = 30 Minuten)
        var $iDefaultLifetime = 30000000;

        // Der Pfad zu den Cache-Dateien (absolut oder relativ)
        var $sCachePath = './cache/';

        // Die Dateierweiterung für die Cache-Dateien (z.B. ".cache")
        var $sFileExtension = '.cache';

        /**
         * Konstruktor der Klasse
         *
         */
        function Cache() {
                // Wandelt den eingegebenen Pfad in einen absoluten Pfad um
                //$this -> sCachePath = realpath($this -> sCachePath);
        } 

        /**
         * Säubert die Cache-Daten
         *     - Sucht nach alten, abgelaufenen Cache-Daten und entfernt diese
         *     - Gibt die Anzahl der gelöschten Cache-Einträge zurück
         *
         * @return integer
         */
        function cleanUpCache() {
                // Suche nach allen Dateien, die zum Cache gehören
                $aFiles = glob($this -> sCachePath . DIRECTORY_SEPARATOR . '*' . $this -> sFileExtension);

                // Falls keine Dateien verfübar sind 
                if(count($aFiles) < 1) {
                        // gib 0 zurück (0 Dateien wurden entfernt)
                        return 0;
                }

                $iCounter = 0;
                // Nun, es gibt mindestens eine Datei ...
                foreach($aFiles as $sFileName) {
                        // Datei versuchen zu öffnen ..
                        $rHandle = @fopen($sFileName, 'r');
                        if(is_resource($rHandle) === false) {
                                continue;
                        }

                        // Da die Verbindung nun steht, wird diese Datei
                        // tempoär gesperrt
                        flock($rHandle, LOCK_SH);

                        // Lesen wir die Daten ein ...
                        $sData = '';
                        while(!feof($rHandle)) {
                                $sData .= fread($rHandle, 4096);
                        }

                        $mData = @unserialize($sData);
                        if(!$mData) {
                                // Es ist wohl etwas schief gelaufen,
                                // weiter mit der nächsten Datei
                                continue;
                        }

                        if(time() + $this -> iDefaultLifetime > $mData[0]) {
                                // Die Lebenszeit ist abgelaufen,
                                // also wird dieser Cache-Eintrag gelöscht
                                fclose($rHandle);
                                if($this -> deleteCache($sFileName, true) === true) {
                                        $iCounter ++;
                                }
                        }

                        else {
                                // Die Datei ist noch gültig,
                                // weiter mit der nächsten Datei
                                fclose($rHandle);
                        }

                }

                // Jetzt nur noch die Anzahl der gelöschten Dateien
                // zurückgeben, fertig.
                return $iCounter;
        } 

        /**
         * Löscht einen existierenden Cache-Eintrag
         *
         * @param string $sCacheName
         * @param boolean $bIsFileName
         * @return boolean
         */
        function deleteCache($sCacheName, $bIsFileName = false) {
                // Falls der übergebene Wert kein Dateiname ist,
                // muss dieser erst generiert werden
                if($bIsFileName === false) {
                        $sFileName = $this -> getFileName($sCacheName);
                }

                else {
                        // Ansonsten kann der Name 1:1 übernommen werden
                        $sFileName = $sCacheName;
                }

                // Löscht die Datei und gibt bei Erfolg "true",
                // bei Misserfolg "false" zurück.
                return @unlink($sFileName);
        } 

        /**
         * Gibt den Dateinamen inklusive Pfadangabe zurück
         * Auf Wunsch wird dieser Dateiname mittels der Funktion sha1() gehasht.
         *
         * @param string $sCacheName
         * @return string
         */
        function getFileName($sCacheName) {
                $sFileIdentifier = ($this -> bHashFilenamesSHA1 === true) ? sha1($sCacheName) : $sCacheName;
                return $this -> sCachePath . DIRECTORY_SEPARATOR . $sFileIdentifier . $this -> sFileExtension;
        } 

        /** 
         * Liest die Daten aus dem Cache, falls diese existieren
         * und noch gültig sind.
         *
         * @param string $sCacheName
         * @return boolean (if not succeeded) | mixed (if succeeded)
         */
        function readCache($sCacheName) {
                // Leere Cache-Eintrag-Namen sind nicht erlaubt :-)
                if(trim($sCacheName) == '') {
                        return false;
                }

                // Der Cache-Name ist also nicht leer, nun wird
                // der Dateiname inklusive Pfad generiert
                $sFileName = $this -> getFileName($sCacheName);

                // Nun müssen wir prüfen, ob die Datei überhaupt
                // existiert.. Wenn nicht, wird die Funktion abgebrochen
                // und false zurückgegeben.
                if(file_exists($sFileName) === false) {
                        return false;
                }

                // Hier müssen wir prüfen, ob die Datei lesbar ist.
                // Wenn nicht, wird die Funktion ebenfalls abgebrochen
                // und false zurückgegeben.
                if(is_readable($sFileName) === false) {
                        return false;
                } 
                $rHandle = @fopen($sFileName, 'r');
                if(is_resource($rHandle) === false) {
                        // Falls troztdem etwas schiefgegangen ist,
                        // gib wieder false zurück
                        return false;
                }

                // Jetzt haben wir die Datei geöffnet, nun sperren wir sie
                flock($rHandle, LOCK_SH);

                // Nun lesen wir die Daten aus ..
                $sData = '';
                while(!feof($rHandle)) {
                        $sData .= fread($rHandle, 4096);
                }

                // und schließen diese Datei wieder (wichtig!).
                fclose($rHandle); 
                // Nun ent-serialisieren wir die gegebenen Daten
                $mData = @unserialize($sData);

                // Falls beim ent-serialisieren etwas schiefgelaufen ist,
                // oder der aktuelle Zeitstempel bereits größer als der
                // im Cache ist (d.h. der Cache ist verfallen) wird
                // die Datei gelöscht, und es wird false zurückgegeben.
                if(!$mData or time() > $mData[0]) {
                        // Delete that file and return false
                        $this -> deleteCache($sCacheName);
                        return false;
                } 
                return $mData[1];
        } 												


        /**
         * Diese Funktion löscht jedliche Cache-Einträge, die zu finden sind 
         * und nimmt dabei keine Rücksicht auf die Verfallsdaten
         * der jeweiligen Dateien.
         *
         * @return integer
         */
        function truncateCache() {
                // Suche nach allen Dateien, die zum Cache gehören
                $aFiles = glob($this -> sCachePath . DIRECTORY_SEPARATOR . '*' . $this -> sFileExtension);

                // Falls keine Dateien verfübar sind ..
                if(count($aFiles) < 1) {
                        // .. geben wir 0 zurück (integer), denn die Funktion
                        // gibt die Anzahl der gelöschten Dateien zurück
                        return 0;
                }

                // gib 0 zurück (0 Dateien wurden entfernt)
                $iCounter = 0;

                // Nun, es gibt mindestens eine Datei ...
                foreach($aFiles as $sFileName) {
                        // Jetzt prüfen wir nicht, ob der Eintrag gültig ist
                        // oder nicht - sondern löschen ihn einfach :-)
                        if($this -> deleteCache($sFileName, true) === true) {
                                $iCounter ++;
                        }

                }

                // Zuletzt geben wie die Anzahl der gelöschten
                // Dateien zurück
                return $iCounter;
        } 

        /**
         * Schreibt die übergebenen Daten in den Cache
         *
         * @param string $sCacheName
         * @param mixed $mData
         * @param integer $iLifetime ( in seconds )
         * @return boolean
         */
        function writeCache($sCacheName, $mData, $iLifetime = -1) { 
                if(is_int($iLifetime) === false or $iLifetime < 0) {
                        // Falls der übergebene Lebensdauer-Wert keine Zahl
                        // ist oder kleiner Null, wird der standardmäßige Wert
                        // genommen
                        $iLifetime = $this -> iDefaultLifetime;
                }

                // Hier wird wieder der Dateiname zusammengebaut
                //$sFileName = $this -> getFileName($sCacheName);
				$sFileName = $sCacheName;
                // Wir versuche die Datei zu öffnen
                $rHandle = @fopen($sFileName, 'a');

                // Falls dies nicht gelungen ist, geben wir false zurück
                if(is_resource($rHandle) === false) {
                        return false;
                }

                // Danach sperren wir die Datei, um eventuelle
                // race-conditions zu vermeiden
                flock($rHandle, LOCK_EX);

                // Nun leeren wir die Datei
                ftruncate($rHandle, 0); 
                $sSerializedData = serialize(array( (time() + $iLifetime), $mData)); 								
                // Nun schreiben wir die neuen Cache-Daten
                // (oder versuchen es zumindest)
                if(@fwrite($rHandle, $sSerializedData) === false) {
                        // Sollte hier an dieser Stelle ein Fehler auftreten,
                        // wird false zurückgegeben
                        return false;
                }

                fclose($rHandle);
                return true;
        } 
		
}
?> 