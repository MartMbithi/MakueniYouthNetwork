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

namespace App\Models;

use App\Core\Database;

final class Donation
{
    /** @param array<string,mixed> $data */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO donations (donor_name, donor_phone, donor_email, amount, currency,
                                    provider, reference, status)
             VALUES (:name, :phone, :email, :amount, :currency, :provider, :reference, :status)'
        );
        $stmt->execute([
            ':name'      => $data['donor_name']  ?? null,
            ':phone'     => $data['donor_phone'] ?? null,
            ':email'     => $data['donor_email'] ?? null,
            ':amount'    => $data['amount'],
            ':currency'  => $data['currency']    ?? 'KES',
            ':provider'  => $data['provider']    ?? 'paystack',
            ':reference' => $data['reference'],
            ':status'    => $data['status']      ?? 'pending',
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    /** @return array<string,mixed>|null */
    public static function findByReference(string $reference): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM donations WHERE reference = :r LIMIT 1'
        );
        $stmt->execute([':r' => $reference]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM donations WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public static function markCompleted(string $reference, ?int $paystackId, ?string $gatewayResponse, ?string $channel): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE donations
             SET status = :s, paystack_id = :pid, gateway_response = :gw, channel = :ch, paid_at = NOW()
             WHERE reference = :ref'
        );
        $stmt->execute([
            ':s'   => 'completed',
            ':pid' => $paystackId,
            ':gw'  => $gatewayResponse,
            ':ch'  => $channel,
            ':ref' => $reference,
        ]);
    }

    public static function markFailed(string $reference, ?string $gatewayResponse): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE donations SET status = :s, gateway_response = :gw WHERE reference = :ref'
        );
        $stmt->execute([
            ':s'   => 'failed',
            ':gw'  => $gatewayResponse,
            ':ref' => $reference,
        ]);
    }

    /** @return list<array<string,mixed>> */
    public static function all(?string $status = null, ?string $from = null, ?string $to = null, int $limit = 500): array
    {
        $sql = 'SELECT * FROM donations WHERE 1=1';
        $params = [];
        if ($status !== null && $status !== '') {
            $sql .= ' AND status = :s';
            $params[':s'] = $status;
        }
        if ($from !== null && $from !== '') {
            $sql .= ' AND created_at >= :from';
            $params[':from'] = $from . ' 00:00:00';
        }
        if ($to !== null && $to !== '') {
            $sql .= ' AND created_at <= :to';
            $params[':to'] = $to . ' 23:59:59';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :lim';
        $stmt = Database::connection()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public static function totalCompleted(?string $from = null, ?string $to = null): float
    {
        $sql = "SELECT COALESCE(SUM(amount),0) FROM donations WHERE status = 'completed'";
        $params = [];
        if ($from !== null && $from !== '') {
            $sql .= ' AND created_at >= :from';
            $params[':from'] = $from . ' 00:00:00';
        }
        if ($to !== null && $to !== '') {
            $sql .= ' AND created_at <= :to';
            $params[':to'] = $to . ' 23:59:59';
        }
        $stmt = Database::connection()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return (float) $stmt->fetchColumn();
    }

    public static function countByStatus(string $status): int
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM donations WHERE status = :s');
        $stmt->execute([':s' => $status]);
        return (int) $stmt->fetchColumn();
    }
}
