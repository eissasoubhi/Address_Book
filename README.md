# Address_Book

## Introduction

This repository is a simple test for a job application that represents an address book which we can add, edit and delete entries. we
also have an overview of all contacts.

The address book contains the following data:
- Firstname
- Lastname
- Street and number
- Zip
- City
- Country
- Phonenumber
- Birthday
- Email address
- Picture (optional)

The used technologies:
- Symfony 3.4
- Doctrine with SQLite
- Twig
- PHP 7.0

# installation

`$ git clone git@github.com:eissasoubhi/Address_Book.git`   
`$ cd Address_Book`  
`$ composer install`  
`$ php bin/console doctrine:migrations:migrate` *(type "y" in the command prompt)*     
`$ php bin/console server:run`

now go to http://localhost:8000/
