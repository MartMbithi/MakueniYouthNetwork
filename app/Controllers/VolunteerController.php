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

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Setting;
use App\Models\Volunteer;
use App\Services\Mailer;

final class VolunteerController
{
    public function form(): string
    {
        $data = [
            'title'  => 'Volunteer with us',
            'old'    => $_SESSION['_old_volunteer'] ?? [],
            'errors' => $_SESSION['_errors_volunteer'] ?? [],
        ];
        unset($_SESSION['_old_volunteer'], $_SESSION['_errors_volunteer']);
        return View::render('public/volunteer.twig', $data);
    }

    public function submit(): string
    {
        Csrf::requireValid();

        $ip = Request::ip();
        if (!RateLimit::attempt('volunteer:' . $ip, 3, 600)) {
            View::flash('Too many submissions from your network. Please try again in a few minutes.', 'error');
            Response::redirect('/volunteer');
        }

        $fullName = trim((string) Request::input('full_name', ''));
        $email    = trim((string) Request::input('email', ''));
        $phone    = trim((string) Request::input('phone', ''));
        $interest = trim((string) Request::input('interest', ''));
        $message  = trim((string) Request::input('message', ''));

        $errors = $this->validate($fullName, $email, $phone);
        if ($errors !== []) {
            $_SESSION['_errors_volunteer'] = $errors;
            $_SESSION['_old_volunteer']    = compact('fullName', 'email', 'phone', 'interest', 'message');
            Response::redirect('/volunteer');
        }

        $id = Volunteer::create([
            'full_name' => $fullName,
            'email'     => $email,
            'phone'     => $phone   !== '' ? $phone : null,
            'interest'  => $interest!== '' ? $interest : null,
            'message'   => $message !== '' ? $message : null,
        ]);

        $notifyTo = Setting::get('email', 'info@makueniyouth.org') ?? 'info@makueniyouth.org';
        Mailer::send(
            $notifyTo,
            'New volunteer application: ' . $fullName,
            $this->notificationHtml($id, $fullName, $email, $phone, $interest, $message),
            $email
        );

        View::flash('Thanks for stepping up — we have received your application and will be in touch.', 'success');
        Response::redirect('/volunteer');
        return '';
    }

    /** @return array<string,array<int,string>> */
    private function validate(string $fullName, string $email, string $phone): array
    {
        $errors = [];
        if (strlen($fullName) < 2) {
            $errors['full_name'][] = 'Please share your full name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'A valid email is needed so we can reach you.';
        }
        if ($phone !== '' && !preg_match('/^[+0-9 ()\-]{7,20}$/', $phone)) {
            $errors['phone'][] = 'Phone number looks off — use digits, spaces and + only.';
        }
        return $errors;
    }

    private function notificationHtml(int $id, string $name, string $email, string $phone, string $interest, string $message): string
    {
        $h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $rows = [
            'Name'     => $name,
            'Email'    => $email,
            'Phone'    => $phone !== '' ? $phone : '—',
            'Interest' => $interest !== '' ? $interest : '—',
        ];
        $out = '<h2>New volunteer #' . $id . '</h2><table style="border-collapse:collapse">';
        foreach ($rows as $k => $v) {
            $out .= '<tr><td style="padding:4px 12px;font-weight:bold">' . $h($k) . '</td>'
                  . '<td style="padding:4px 12px">' . $h($v) . '</td></tr>';
        }
        $out .= '</table>';
        if ($message !== '') {
            $out .= '<h3 style="margin-top:18px">Message</h3><p>' . nl2br($h($message)) . '</p>';
        }
        return $out;
    }
}
