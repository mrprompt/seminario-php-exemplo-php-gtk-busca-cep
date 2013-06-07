<?php
/**
 * Exemplo de Busca de CEP em PHP-GTK para o Seminário PHP.
 *
 * License GPL-3.0+
 * 
 * @author Thiago Paes <mrprompt@gmail.com>
 * @package BuscaCEP
 */
define('DS', DIRECTORY_SEPARATOR);
 
$load = require_once __DIR__ . DS . 'vendor' . DS . 'autoload.php';
$load->add('Main', __DIR__ . DS . 'app');
 
$app = new Main;
$app->connect_simple('destroy', array('gtk', 'main_quit'));
$app->show_all();
 
Gtk::main();