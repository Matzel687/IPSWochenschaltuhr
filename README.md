# Wochenschaltuhr / WeeklyTimer
Dieses Modul basiert auf dem Wochenplaner von IPS.
Es können die Wochentage und Uhrzeiten über das Konfigurationsformular eingestellt werde. 
Außerdem können die Startzeiten über das Web Front verändert werden.
Über die Reset Funktion im Web Front wird die Ursprungs Konfiguration geladen. 

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Erstellen einer Wochenschaltuhr (Wochenplan) über ein Konfigurationsformular
* Beleibige Kombination von Wochentagen (jeder Tag darf nur einmal vorkommen).
* Erstellen Aktionen im Konfigurationsformular.
* Einstellen der Offset Zeit für jede Aktion.
* Erstellen eines Skriptes welches vom Wochenplan aufgerufen wird. 
  Wenn das Skript übers Modul Aktualisiert wird, wird dieses Überschriebn !
* Ändern der Wochentage und der Start Uhrzeiten übers Webfront
* Reset der Wohentage und der Start Uhrzeiten übers Webfront. 
  Es werden wieder die Tage und Zeiten aus dem Konfigurationsformular übernommen.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.3

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/Matzel687/IPSWochenschaltuhr.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Wochenschaltuhr' / 'WeeklyTimer'-Modul unter dem Hersteller '(Matzel687)' aufgeführt.  

![Bildtext](imgs/Konfigurationsformular.jpg "Bildtitel")

### 7. PHP-Befehlsreferenz

####*1. Funktion um die aktuellen Wetterdaten auszugeben
`Array WD_Weathernow(integer $ModulID, String $Key);`

$Key                    | Beschreibung
----------------------- | --------- 
'all'                   | Gibt alle unten stehenden Variablen als Array aus.     
'Temp_now'              | Aktuelle Temperatur
'Temp_feel'             | Gefühle Temperatur     
'Temp_dewpoint'         | Taupunkt      
'Hum_now'               | Luftfeuchtigkeit     
'Pres_now'              | Luftdruck    
'Wind_deg'              | Windrichtung     
'Wind_now'              | Windstärke     
'Wind_gust'             | Windböe    
'Rain_now'              | Regen Jetzt     
'Rain_today'            | Regen Tagesverlauf
'Solar_now'             | Sonnenenergie    
'Vis_now'               | Sichtweite 
'UV_now',               | UV Wert 
'Icon'                  | Icon
'Text'                  | Wetter Text

// Beispiel Ausgabe
`print_r(WD_Weathernow($ModulID, "all"));`

```
Array
(
    
    [Temp_now] => 15.8
    [Temp_feel] => 15.8
    [Temp_dewpoint] => 15
    [Hum_now] => 94
    [Pres_now] => 1015
    [Wind_deg] => 161
    [Wind_now] => 8
    [Wind_gust] => 17.7
    [Rain_now] => 3
    [Rain_today] => 5
    [Solar_now] => 33
    [Vis_now] => 9
    [UV_now] => 1
    [Icon] => user\Wetter_Icons\rain.png
    [Text] => Leichter Regen
)
```

####*2. Funktion um die Wetterdaten für die nächsten 3 Tage auszugeben
`Array WD_Weathernextdays(integer $ModulID,);`

// Beispiel Ausgabe
`print_r(WD_Weathernextdays($ModulID));`
```
Array
(
    [0] => Array
        (
            [Date] => 1466442000
            [Text] => Regen. Tiefsttemperatur 15C.
            [Icon] => user\Wetter_Icons\rain.png
            [TempHigh] => 20
            [TempLow] => 15
            [Humidity] => 91
            [Wind] => 6
            [MaxWind] => 31
            [Rain] => 6
            [Pop] => 100
        )
    [1] => Array
          .....
    [2] => Array
          .......
    [3] => Array
          ......
)
```
####*3. Funktion um die Wetterdaten für die nächsten 24 Stunden auszugeben
`Array WD_Weathernexthours(integer $ModulID,);`

// Beispiel Ausgabe
`print_r(WD_Weathernexthours($ModulID));`
```
Array
(
    [0] => Array
        (
            [Date] => 1466442000
            [Text] => Regen
            [Icon] => user\Wetter_Icons\rain.png
            [Temp] => 17
            [Tempfeel] => 17
            [Tempdewpoint] => 14
            [Humidity] => 88
            [Wind] => 16
            [Pres] => 1015
            [Rain] => 6
            [Pop] => 100
        )
        ....
      [23] => Array
        (
            [Date] => 1466524800
            [Text] => Wolkig
            [Icon] => user\Wetter_Icons\mostlycloudy.png
            [Temp] => 21
            [Tempfeel] => 21
            [Tempdewpoint] => 15
            [Humidity] => 69
            [Wind] => 11
            [Pres] => 1019
            [Rain] => 0
            [Pop] => 12
        )
)
```
####*4. Funktion um die Wetter Warnungen auszugeben
`Array WD_Weatheralerts(integer $ModulID,);`

// Beispiel Ausgabe
`print_r(WD_Weatheralerts($ModulID));`
```
Array
(
    [0] => Array
        (
            [Date] => 2017-04-22 07:00:15 GMT
            [Expires] => 2017-04-22 17:00:00 GMT
            [Type] => WND
            [Name] => Wind
            [Color] => Yellow
            [Text] => Potential disruption due to wind from 8AM CEST SAT until 7PM CEST SAT
        )

)
```