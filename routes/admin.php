<?php

declare(strict_types=1);

/*
 *   Conjured Upon This Day, Fri May 22 2026
 *
 *   From his finger tips, through his IDE to your deployment environment at full throttle with no bugs, loss of data,
 *   fluctuations, signal interference, or doubt—it can only be
 *
 *   ███╗   ███╗ ██████╗ ██████╗ ████████╗██╗███╗   ██╗
 *   ████╗ ████║██╔═══██╗██╔══██╗╚══██╔══╝██║████╗  ██║
 *   ██╔████╔██║███████║║██████╔╝   ██║   ██║██╔██╗ ██║
 *   ██║╚██╔╝██║██╔══██║ ██╔══██╗   ██║   ██║██║╚██╗██║
 *   ██║ ╚═╝ ██║██║  ██║ ██║  ██║   ██║   ██║██║ ╚████║
 *   ╚═╝     ╚═╝╚═╝  ╚═╝ ╚═╝  ╚═╝   ╚═╝   ╚═╝╚═╝  ╚═══╝
 *   M B I T H I — The Legendary Coding Wizard
 *
 *   📧  martin.mbithi@makueni.go.ke
 *   🌐  www.martmbithi.github.io
 *   🐙  https://github.com/MartMbithi
 *
 *   If this code works, you're welcome.
 *   If it doesn't, it's a feature you haven't understood yet.
 *
 *
 *   ┌─────────────────────────────────────────────────────────────┐
 *   │          GOVERNMENT OF MAKUENI COUNTY                       │
 *   │          Applications Development Section                   │
 *   │          www.makueni.go.ke | info@makueni.go.ke             │
 *   └─────────────────────────────────────────────────────────────┘
 *
 *   THE GOVERNMENT OF MAKUENI COUNTY
 *   Applications Development Section End-User License Agreement
 *   Copyright (c) 2023 Government of Makueni County
 *   All Rights Reserved.
 *
 *
 *   § 1. GRANT OF LICENSE
 *
 *   This software, designed and engineered by Martin Mbithi on behalf
 *   of the Government of Makueni County Applications Development
 *   Section, is licensed — not sold — to you. You are hereby granted
 *   a revocable, personal, non-exclusive, and non-transferable right
 *   to install and operate this system on ONE (1) authorized government
 *   workstation for official, non-commercial use only.
 *
 *   Commercial deployment requires a separate written license agreement.
 *   Unauthorized sharing, distribution, or public demonstration of this
 *   software is strictly prohibited. If you're thinking about it,
 *   don't. The paperwork alone would ruin your week.
 *
 *
 *   § 2. INTELLECTUAL PROPERTY
 *
 *   This software is the intellectual property of the Government of
 *   Makueni County, engineered by Martin Mbithi under the authority of
 *   the Applications Development Section. It is protected by the
 *   Copyright Act of Kenya, applicable international treaties, and the
 *   quiet determination of people who actually read license agreements.
 *
 *   You shall not remove, alter, or obscure any proprietary notices,
 *   labels, or marks contained within the software. They were placed
 *   there with intention. Respect them accordingly.
 *
 *
 *   § 3. RESTRICTIONS
 *
 *   You shall not, nor shall you permit any third party to:
 *
 *   (a) reverse engineer, decompile, decode, decrypt, disassemble, or
 *       otherwise attempt to derive the source code of this software.
 *       Curiosity is admirable. This is not the place for it;
 *
 *   (b) modify, adapt, distribute, or create derivative works based
 *       upon this software, in whole or in part;
 *
 *   (c) copy (except for one reasonable backup), distribute, publicly
 *       display, transmit, sell, rent, lease, sublicense, or otherwise
 *       exploit the software. It belongs to Makueni County.
 *       You are a guest. A welcome one, but still a guest.
 *
 *
 *   § 4. TERMINATION
 *
 *   This License remains in effect until terminated by either party.
 *   You may terminate at any time by destroying the software and all
 *   copies in your possession. The County may terminate this License
 *   immediately upon breach of any term herein.
 *
 *   Upon termination, all copies shall be destroyed. No exceptions,
 *   no 'I forgot it was on that flash drive.' That flash drive too.
 *
 *
 *   § 5. DISCLAIMER OF WARRANTIES
 *
 *   THIS SOFTWARE IS PROVIDED 'AS IS' WITHOUT WARRANTY OF ANY KIND,
 *   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE,
 *   AND NON-INFRINGEMENT.
 *
 *   The Applications Development Section has made every reasonable
 *   effort to ensure reliability, but software, much like government
 *   policy, may occasionally behave in unexpected ways. Use is at
 *   your own risk. Some jurisdictions may afford additional statutory
 *   rights.
 *
 *
 *   § 6. SEVERABILITY
 *
 *   If any provision of this Agreement is held to be invalid or
 *   unenforceable by a court of competent jurisdiction, the remaining
 *   provisions shall continue in full force and effect. One clause
 *   may fall. The rest stand. Much like county infrastructure
 *   during the long rains.
 *
 *
 *   § 7. LIMITATION OF LIABILITY
 *
 *   IN NO EVENT SHALL MARTIN MBITHI, THE GOVERNMENT OF MAKUENI
 *   COUNTY, THE APPLICATIONS DEVELOPMENT SECTION, OR THEIR
 *   RESPECTIVE OFFICERS, EMPLOYEES, OR AGENTS BE LIABLE FOR ANY
 *   INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR EXEMPLARY
 *   DAMAGES ARISING OUT OF OR IN CONNECTION WITH THE USE OF THIS
 *   SOFTWARE.
 *
 *   Total liability shall not exceed the license fee paid, if any.
 *   If the amount is zero, we trust you see where the math lands.
 *   No drama. Just governance, code, and service delivery.
 *
 */

use App\Core\Auth;

/** @var \App\Core\Router $router */

// Public auth pages
$router->get('/admin/login',  'Admin\AuthController@loginForm');
$router->post('/admin/login', 'Admin\AuthController@login');
$router->post('/admin/logout','Admin\AuthController@logout');

// Everything below requires login. We guard once here for the entire
// remainder of the admin surface.
if (str_starts_with(\App\Core\Request::path(), '/admin') && \App\Core\Request::path() !== '/admin/login') {
    Auth::requireLogin();
}

// Dashboard
$router->get('/admin', 'Admin\DashboardController@index');

// Posts
$router->get('/admin/posts',            'Admin\PostController@index');
$router->get('/admin/posts/create',     'Admin\PostController@create');
$router->post('/admin/posts',           'Admin\PostController@store');
$router->get('/admin/posts/{id}/edit',  'Admin\PostController@edit');
$router->post('/admin/posts/{id}',      'Admin\PostController@update');
$router->post('/admin/posts/{id}/delete','Admin\PostController@destroy');

// Programs
$router->get('/admin/programs',             'Admin\ProgramController@index');
$router->get('/admin/programs/create',      'Admin\ProgramController@create');
$router->post('/admin/programs',            'Admin\ProgramController@store');
$router->get('/admin/programs/{id}/edit',   'Admin\ProgramController@edit');
$router->post('/admin/programs/{id}',       'Admin\ProgramController@update');
$router->post('/admin/programs/{id}/delete','Admin\ProgramController@destroy');

// Events
$router->get('/admin/events',             'Admin\EventController@index');
$router->get('/admin/events/create',      'Admin\EventController@create');
$router->post('/admin/events',            'Admin\EventController@store');
$router->get('/admin/events/{id}/edit',   'Admin\EventController@edit');
$router->post('/admin/events/{id}',       'Admin\EventController@update');
$router->post('/admin/events/{id}/delete','Admin\EventController@destroy');

// Pages
$router->get('/admin/pages',             'Admin\PageController@index');
$router->get('/admin/pages/create',      'Admin\PageController@create');
$router->post('/admin/pages',            'Admin\PageController@store');
$router->get('/admin/pages/{id}/edit',   'Admin\PageController@edit');
$router->post('/admin/pages/{id}',       'Admin\PageController@update');
$router->post('/admin/pages/{id}/delete','Admin\PageController@destroy');

// Stats
$router->get('/admin/stats',             'Admin\StatController@index');
$router->get('/admin/stats/create',      'Admin\StatController@create');
$router->post('/admin/stats',            'Admin\StatController@store');
$router->get('/admin/stats/{id}/edit',   'Admin\StatController@edit');
$router->post('/admin/stats/{id}',       'Admin\StatController@update');
$router->post('/admin/stats/{id}/delete','Admin\StatController@destroy');

// Partners
$router->get('/admin/partners',             'Admin\PartnerController@index');
$router->get('/admin/partners/create',      'Admin\PartnerController@create');
$router->post('/admin/partners',            'Admin\PartnerController@store');
$router->get('/admin/partners/{id}/edit',   'Admin\PartnerController@edit');
$router->post('/admin/partners/{id}',       'Admin\PartnerController@update');
$router->post('/admin/partners/{id}/delete','Admin\PartnerController@destroy');

// Settings
$router->get('/admin/settings',  'Admin\SettingsController@edit');
$router->post('/admin/settings', 'Admin\SettingsController@update');

// Inline image uploads from the TinyMCE editor.
$router->post('/admin/upload-image', 'Admin\UploadController@image');

// Inboxes
$router->get('/admin/messages',                'Admin\MessageController@index');
$router->post('/admin/messages/{id}/toggle',   'Admin\MessageController@toggleRead');
$router->post('/admin/messages/{id}/delete',   'Admin\MessageController@destroy');

$router->get('/admin/volunteers',            'Admin\VolunteerController@index');
$router->get('/admin/volunteers/export.csv', 'Admin\VolunteerController@exportCsv');

$router->get('/admin/donations',             'Admin\DonationController@index');

// Users (admin only — enforced inside controller methods)
$router->get('/admin/users',              'Admin\UserController@index');
$router->get('/admin/users/create',       'Admin\UserController@create');
$router->post('/admin/users',             'Admin\UserController@store');
$router->get('/admin/users/{id}/edit',    'Admin\UserController@edit');
$router->post('/admin/users/{id}',        'Admin\UserController@update');
$router->post('/admin/users/{id}/delete', 'Admin\UserController@destroy');

// Advocacy platform phase 1
$router->get('/admin/campaigns',                    'Admin\\AdvocacyContentController@campaignIndex');
$router->get('/admin/campaigns/create',             'Admin\\AdvocacyContentController@campaignCreate');
$router->post('/admin/campaigns',                   'Admin\\AdvocacyContentController@campaignStore');
$router->get('/admin/campaigns/{id}/edit',          'Admin\\AdvocacyContentController@campaignEdit');
$router->post('/admin/campaigns/{id}',              'Admin\\AdvocacyContentController@campaignUpdate');
$router->post('/admin/campaigns/{id}/delete',       'Admin\\AdvocacyContentController@campaignDestroy');
$router->get('/admin/opportunities',                'Admin\\AdvocacyContentController@opportunityIndex');
$router->get('/admin/opportunities/create',         'Admin\\AdvocacyContentController@opportunityCreate');
$router->post('/admin/opportunities',               'Admin\\AdvocacyContentController@opportunityStore');
$router->get('/admin/opportunities/{id}/edit',      'Admin\\AdvocacyContentController@opportunityEdit');
$router->post('/admin/opportunities/{id}',          'Admin\\AdvocacyContentController@opportunityUpdate');
$router->post('/admin/opportunities/{id}/delete',   'Admin\\AdvocacyContentController@opportunityDestroy');
$router->get('/admin/resources',                    'Admin\\AdvocacyContentController@resourceIndex');
$router->get('/admin/resources/create',             'Admin\\AdvocacyContentController@resourceCreate');
$router->post('/admin/resources',                   'Admin\\AdvocacyContentController@resourceStore');
$router->get('/admin/resources/{id}/edit',          'Admin\\AdvocacyContentController@resourceEdit');
$router->post('/admin/resources/{id}',              'Admin\\AdvocacyContentController@resourceUpdate');
$router->post('/admin/resources/{id}/delete',       'Admin\\AdvocacyContentController@resourceDestroy');
$router->get('/admin/media',                        'Admin\\AdvocacyContentController@mediaIndex');
$router->get('/admin/media/create',                 'Admin\\AdvocacyContentController@mediaCreate');
$router->post('/admin/media',                       'Admin\\AdvocacyContentController@mediaStore');
$router->get('/admin/media/{id}/edit',              'Admin\\AdvocacyContentController@mediaEdit');
$router->post('/admin/media/{id}',                  'Admin\\AdvocacyContentController@mediaUpdate');
$router->post('/admin/media/{id}/delete',           'Admin\\AdvocacyContentController@mediaDestroy');
$router->get('/admin/faqs',                         'Admin\\FaqController@index');
$router->get('/admin/faqs/create',                  'Admin\\FaqController@create');
$router->post('/admin/faqs',                        'Admin\\FaqController@store');
$router->get('/admin/faqs/{id}/edit',               'Admin\\FaqController@edit');
$router->post('/admin/faqs/{id}',                   'Admin\\FaqController@update');
$router->post('/admin/faqs/{id}/delete',            'Admin\\FaqController@destroy');
$router->get('/admin/newsletter',                   'Admin\\NewsletterController@index');
$router->get('/admin/analytics',                    'Admin\\AnalyticsController@index');
