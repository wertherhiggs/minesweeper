<VirtualHost *:80>
    ServerAdmin mailt@test.it
    DocumentRoot /vagrant/web
    ServerName minesweeper

    <Directory /vagrant/web>
        Require all granted
    </Directory>
</VirtualHost>