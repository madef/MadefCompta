MadefCompta
=========

Madef Compta is a symfony 2 project, adapted from a little project to manage the accounting of my new company developped in 2013 and propulsed by Silex.
This project is for accounting of small french companies. I'm sure it can be used in other countries.

A demo is avalable [here] [1]

Functionalities
-----------

  - Account line
    - Export as CSV
    - Get tax amount of a period
    - Get tax to pay (VAT)
    - Filter by type (cheque, credit cards, ...)
    - Filter by transmitter
    - Filter by receiver
  - Invoice
    - Upload copy of your invoice
    - Filter by transmitter
    - Filter by receiver

See more in the [demo website] [1]

Tech
-----------

* [Symfony 2.4] - PHP 5.3 full-stack web framework
* [Bootstrap] - Front-end framework for developing responsive,
* [jQuery] - Feature-rich JavaScript library

Installation
--------------

Composer is required to install the project. For that, download composer and copy it into your bin directory:
```sh
get composer: php -r "readfile('https://getcomposer.org/installer');" | php
mv composer.phar /usr/local/bin/composer
```

Import the project using git:
```sh
git clone https://github.com/madef/MadefCompta.git compta
# OR git clone git@github.com:madef/MadefCompta.git compta
```

Create a database:
```sql
CREATE DATABASE compta;
```

Install and configure it using composer:
```sh
cd compta
composer install
```

Create the tables:
```sh
php app/console doctrine:schema:update --force
```

Allow the application to write in cache and log directories:
```sh
chmod a+rw app/logs/
chmod a+rw app/cache/
```


License
----

The project is open source, under BSD license.


[1]:http://compta.demo.madef.fr

