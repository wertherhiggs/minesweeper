minesweeper
===========

A simple symfony-minesweeper game created following a BDD approach.

To run this you should have installed:

 * vagrant
 * virtual box
 * virtual box guest additions

Remember to perform this before start

    sudo cat '192.168.33.99 minesweeper' >> /etc/hosts

and to access vagrant via `ssh` to generate database

    cd /vagrant
    php app/consolle doctrine:migrations:migrate

