# Project Fedea
This is a social platform for a university, demo page can be reached here: [fedea.games](https://fedea.games)

[Link to this document page](https://fedea.games/docs)

[Backup link](https://github.com/KhasFedea/docs)

The main goal of this project is to aid students in socializing during this pandemic.

### How to Deploy
This website should work in every environment where the dependencies are available.

The dependencies are:
```
nginx or apache2
php7.2-fpm
mysql or mariadb
python3-cert-bot (optional)
```

The things you need to do:
- Install the database server and create a database.
- Install HTTP server and PHP FPM.
- Configure your HTTP server to use PHP FPM back proxy.
- (Recommended) Enable HTTPS support with python3-cert-bot.
- Put fedea folders in the website folder, (ex: "/var/www/html")
- Put database credentials inside "config.php"