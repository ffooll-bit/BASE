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
$routes->get('profil-pt', 'ProfilPT::index');
$routes->get('mahasiswa', 'Mahasiswa::index');
$routes->get('aktivitas-kuliah', 'AktivitasKuliah::index');
$routes->get('mahasiswa-lulus-do', 'MahasiswaLulusDo::index');

/*
 * --------------------------------------------------------------------
 * PISN Graduation Wizard Routes (ENH-013)
 * --------------------------------------------------------------------
 */
$routes->get('graduation', 'Graduation::index');
$routes->post('graduation/upload', 'Graduation::upload');
$routes->get('graduation/resume', 'Graduation::resume');
$routes->get('graduation/step', 'Graduation::step');
$routes->post('graduation/step', 'Graduation::stepPost');
$routes->get('graduation/guidance', 'Graduation::guidance');
