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

namespace App\Services;

use RuntimeException;

final class Paystack
{
    private static string $secretKey = '';
    private static string $publicKey = '';
    private static string $currency  = 'KES';
    private static string $baseUrl   = 'https://api.paystack.co';

    /**
     * @param array{
     *   secret_key:?string, public_key:?string, currency:string, base_url:string, env:string,
     *   callback_url:?string
     * } $config
     */
    public static function configure(array $config): void
    {
        self::$secretKey = (string) ($config['secret_key'] ?? '');
        self::$publicKey = (string) ($config['public_key'] ?? '');
        self::$currency  = (string) ($config['currency']   ?? 'KES');
        self::$baseUrl   = rtrim((string) ($config['base_url'] ?? 'https://api.paystack.co'), '/');
    }

    public static function publicKey(): string
    {
        return self::$publicKey;
    }

    public static function currency(): string
    {
        return self::$currency;
    }

    /**
     * Initialize a Paystack transaction.
     *
     * @param int $amountMinor amount in the minor unit of the currency (kobo / cents)
     * @param array<string,mixed> $metadata
     * @return array{authorization_url:string,access_code:string,reference:string}
     */
    public static function initialize(int $amountMinor, string $email, string $reference, ?string $callbackUrl = null, array $metadata = []): array
    {
        self::requireSecret();

        $payload = [
            'email'     => $email,
            'amount'    => $amountMinor,
            'currency'  => self::$currency,
            'reference' => $reference,
        ];
        if ($callbackUrl !== null && $callbackUrl !== '') {
            $payload['callback_url'] = $callbackUrl;
        }
        if ($metadata !== []) {
            $payload['metadata'] = $metadata;
        }

        $body = self::request('POST', '/transaction/initialize', $payload);

        if (empty($body['status']) || empty($body['data']['authorization_url'])) {
            throw new RuntimeException('Paystack initialize failed: ' . ($body['message'] ?? 'unknown'));
        }

        return [
            'authorization_url' => (string) $body['data']['authorization_url'],
            'access_code'       => (string) $body['data']['access_code'],
            'reference'         => (string) $body['data']['reference'],
        ];
    }

    /**
     * Verify a transaction by its reference.
     *
     * @return array<string,mixed>  the `data` block from Paystack's response
     */
    public static function verify(string $reference): array
    {
        self::requireSecret();
        $body = self::request('GET', '/transaction/verify/' . rawurlencode($reference));
        if (empty($body['status'])) {
            throw new RuntimeException('Paystack verify failed: ' . ($body['message'] ?? 'unknown'));
        }
        return (array) ($body['data'] ?? []);
    }

    /**
     * Validate the X-Paystack-Signature header. Paystack signs the raw request
     * body with HMAC-SHA512 keyed by the secret key. Constant-time compare.
     */
    public static function verifyWebhookSignature(string $rawBody, ?string $signatureHeader): bool
    {
        if (self::$secretKey === '' || $signatureHeader === null || $signatureHeader === '') {
            return false;
        }
        $expected = hash_hmac('sha512', $rawBody, self::$secretKey);
        return hash_equals($expected, $signatureHeader);
    }

    /** @param array<string,mixed> $payload */
    private static function request(string $method, string $path, array $payload = []): array
    {
        $url = self::$baseUrl . $path;
        $ch  = curl_init();

        $headers = [
            'Authorization: Bearer ' . self::$secretKey,
            'Accept: application/json',
        ];

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_FOLLOWLOCATION => false,
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $headers[] = 'Content-Type: application/json';
        }
        $opts[CURLOPT_HTTPHEADER] = $headers;

        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);

        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Paystack network error: ' . $err);
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $body = json_decode((string) $raw, true);
        if (!is_array($body)) {
            throw new RuntimeException('Paystack returned non-JSON (HTTP ' . $status . ').');
        }
        return $body;
    }

    private static function requireSecret(): void
    {
        if (self::$secretKey === '') {
            throw new RuntimeException('PAYSTACK_SECRET_KEY is not configured.');
        }
    }
}
