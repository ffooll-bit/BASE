<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

/*
 * --------------------------------------------------------------------
 * Authentication & Dashboard Routes
 * --------------------------------------------------------------------
 */
$routes->get('login', 'Login::index');
$routes->post('login', 'Login::attemptLogin');
$routes->post('logout', 'Login::logout');
$routes->get('dashboard', 'Dashboard::index');
