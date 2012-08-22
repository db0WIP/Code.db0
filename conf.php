<?php;
// ************************************************************************** //
// Project: Code.db0                                                          //
// Description: Configuration file for a simple web-based code viewer         //
// Author: db0 (db0company@gmail.com, http://db0.fr/)                         //
// Latest Version is on GitHub: https://github.com/db0company/Code.db0        //
// ************************************************************************** //

// ************************************************************************** //
// Requiered configurations                                                   //
// ************************************************************************** //

// General Informations

$title = ucfirst(str_replace('.', ' . ', $_SERVER['SERVER_NAME']));

$description = 'Lorem ipsum';

$author = 'db0';
$author_contact = 'db0company@gmail.com';
$author_website = 'http://db0.fr/';

// Paths

$projects_path = './projects/';
$bootstrap_path = './bootstrap/';
$jquery_path = './jquery.js';

// Logo and Favicon

$logo = 'example/logo.png';
$favicon = 'example/favicon.ico';

// ************************************************************************** //
// Bonus configurations                                                       //
// ************************************************************************** //

// Filename pattern matching for file to ignore (will not be showned at all)
// (wildcard pattern matching)

$ignore = array('.*');

// How to order your projects?
// Available values: 'date', 'random', 'name', 'langage'

$order_by = 'name';

// Maximum lenght of projects descriptions in character

$max_descr = 150;

