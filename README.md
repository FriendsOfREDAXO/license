# Lizenzgrabber für REDAXO 5

Dieses Addon dient zur Lizenz-Prüfung, indem eine Übersicht vorhandener Projekt-Lizenzen zu erhalten, z.B. aus Add-ons und dessen Vendoren sowie eigenen Projektdateien.

Das Addon hält Ausschau nach `composer.json`, `package.json` und `LICENSE.*`-Dateien.

![www redaxo local_redaxo_index php_page=license_main(iPad Air)](https://user-images.githubusercontent.com/3855487/194813067-62a0debf-5eac-43cf-a7eb-83cebedb6829.png)


## Systemvoraussetzungen

* `PHP >= 7.4`
* `REDAXO >= 5.12`

## Console

Kurze Auflistung aller Lizenzen
```php redaxo/bin/console license:list```

Komplette Auflistung aller Lizenzen mit Lizenz text
```php redaxo/bin/console license:list-full``` 

## Lizenz

[MIT Lizenz](https://github.com/FriendsOfREDAXO/license/blob/master/LICENSE.md) 

## Autor

**Friends Of REDAXO**
http://www.redaxo.org 
https://github.com/FriendsOfREDAXO 

**Projekt-Lead** 
[N.N.](https://github.com/n.n.)

## Credits

License basiert auf: [YLicense von Yakamara](https://github.com/yakamara/), federführend entwickelt von [Jan Kristinus](https://github.com/dergel) und [Kai Kristinus](https://github.com/chip75). Portiert zu FriendsOfREDAXO von [Alexander Walther](https://github.com/alxndr-w).
