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
use App\Core\SpamGuard;
use App\Core\View;
use App\Models\Donation;
use App\Services\Paystack;

final class DonationController
{
    public function form(): string
    {
        return View::render('public/donate.twig', [
            'title' => 'Donate',
            'old'   => $_SESSION['_old_donate'] ?? [],
            'errors'=> $_SESSION['_errors_donate'] ?? [],
        ] + $this->clearOld());
    }

    public function initiate(): string
    {
        Csrf::requireValid();

        if (!SpamGuard::passes()) {
            Response::redirect('/donate');
        }

        $ip = Request::ip();
        if (!RateLimit::attemptCombined('donate:' . $ip, 5, 600, 20)) {
            View::flash('Too many attempts. Please try again later.', 'error');
            Response::redirect('/donate');
        }

        $amountKes = (int) Request::input('amount', 0);
        $name      = trim((string) Request::input('donor_name', ''));
        $email     = trim((string) Request::input('donor_email', ''));
        $phone     = trim((string) Request::input('donor_phone', ''));

        $errors = [];
        if ($amountKes < 100) {
            $errors['amount'][] = 'Minimum donation is 100 KES.';
        }
        if ($amountKes > 5_000_000) {
            $errors['amount'][] = 'Amount looks unusually large — please contact us to arrange.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['donor_email'][] = 'A valid email is required so we can send your receipt.';
        }
        if (strlen($name) < 2) {
            $errors['donor_name'][] = 'Please share your name.';
        }
        if ($errors !== []) {
            $_SESSION['_errors_donate'] = $errors;
            $_SESSION['_old_donate']    = compact('amountKes', 'name', 'email', 'phone');
            Response::redirect('/donate');
        }

        $reference = $this->generateReference();
        Donation::create([
            'donor_name'  => $name,
            'donor_email' => $email,
            'donor_phone' => $phone !== '' ? $phone : null,
            'amount'      => $amountKes,
            'currency'    => Paystack::currency(),
            'provider'    => 'paystack',
            'reference'   => $reference,
            'status'      => 'pending',
        ]);

        $callback = ($_ENV['APP_URL'] ?? '') !== ''
            ? rtrim($_ENV['APP_URL'], '/') . '/donate/callback'
            : null;

        try {
            $init = Paystack::initialize(
                $amountKes * 100,
                $email,
                $reference,
                $callback,
                ['donor_name' => $name, 'donor_phone' => $phone]
            );
        } catch (\Throwable $e) {
            error_log('[Paystack] initialize failed: ' . $e->getMessage());
            View::flash(
                'Could not start the payment right now. Please try again in a moment.',
                'error'
            );
            Response::redirect('/donate');
            return '';
        }

        Response::redirect($init['authorization_url']);
        return '';
    }

    /**
     * Donor returns here from Paystack checkout. Always re-verify server-side
     * — never trust the redirect alone.
     */
    public function callback(): string
    {
        $reference = trim((string) (Request::input('reference', '') ?: Request::input('trxref', '')));
        if ($reference === '') {
            return View::render('public/donate-thanks.twig', ['title' => 'Thank you', 'status' => 'failed']);
        }

        $donation = Donation::findByReference($reference);
        if ($donation === null) {
            return View::render('public/donate-thanks.twig', ['title' => 'Thank you', 'status' => 'failed']);
        }

        $resolved = $this->resolveStatus($donation, $reference);

        return View::render('public/donate-thanks.twig', [
            'title'  => 'Thank you',
            'status' => $resolved,
        ]);
    }

    /**
     * Paystack webhook endpoint. Signature-verified, then re-verifies via the
     * API to defeat replay/forgery. Idempotent.
     */
    public function webhook(): string
    {
        $rawBody = (string) file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? null;

        if (!Paystack::verifyWebhookSignature($rawBody, is_string($signature) ? $signature : null)) {
            http_response_code(400);
            return '';
        }

        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            http_response_code(400);
            return '';
        }

        $event = (string) ($payload['event'] ?? '');
        $reference = (string) ($payload['data']['reference'] ?? '');
        if ($reference === '') {
            http_response_code(200);
            return '';
        }

        $donation = Donation::findByReference($reference);
        if ($donation === null) {
            http_response_code(200);
            return '';
        }

        // Always go back to the API for ground truth — webhooks can be forged
        // even with a valid signature if someone replays an old payload.
        $this->resolveStatus($donation, $reference);

        http_response_code(200);
        return '';
    }

    /**
     * Re-verify with Paystack and persist the outcome, returning the new
     * status (pending/completed/failed). Safe to call repeatedly.
     */
    private function resolveStatus(array $donation, string $reference): string
    {
        try {
            $tx = Paystack::verify($reference);
        } catch (\Throwable $e) {
            error_log('[Paystack] verify failed for ' . $reference . ': ' . $e->getMessage());
            return $donation['status'];
        }

        $expectedMinor = (int) round(((float) $donation['amount']) * 100);
        $gotMinor      = (int) ($tx['amount']   ?? 0);
        $gotCurrency   = (string) ($tx['currency'] ?? '');
        $remoteStatus  = (string) ($tx['status']   ?? '');

        $isSuccess = $remoteStatus === 'success'
            && $gotMinor === $expectedMinor
            && strcasecmp($gotCurrency, (string) $donation['currency']) === 0;

        if ($isSuccess && $donation['status'] !== 'completed') {
            Donation::markCompleted(
                $reference,
                isset($tx['id']) ? (int) $tx['id'] : null,
                (string) ($tx['gateway_response'] ?? null),
                (string) ($tx['channel'] ?? null) ?: null
            );
            return 'completed';
        }
        if ($remoteStatus === 'failed' && $donation['status'] !== 'failed') {
            Donation::markFailed($reference, (string) ($tx['gateway_response'] ?? null));
            return 'failed';
        }
        if ($remoteStatus === 'abandoned' && $donation['status'] === 'pending') {
            // Leave as pending in our ledger; user may retry. (Admin ledger
            // shows the gateway_response.)
            return 'pending';
        }
        return $donation['status'] === 'completed' ? 'completed' : 'pending';
    }

    private function generateReference(): string
    {
        return 'MYN-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
    }

    /** @return array<string,mixed> */
    private function clearOld(): array
    {
        unset($_SESSION['_old_donate'], $_SESSION['_errors_donate']);
        return [];
    }
}
