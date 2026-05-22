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

final class Program
{
    /** @return list<array<string,mixed>> */
    public static function all(?string $status = 'published'): array
    {
        if ($status !== null) {
            $stmt = Database::connection()->prepare(
                'SELECT * FROM programs WHERE status = :s ORDER BY sort_order, title'
            );
            $stmt->execute([':s' => $status]);
        } else {
            $stmt = Database::connection()->query('SELECT * FROM programs ORDER BY sort_order, title');
        }
        return $stmt->fetchAll() ?: [];
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM programs WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** @return array<string,mixed>|null */
    public static function findBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = 'SELECT * FROM programs WHERE slug = :slug';
        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }
        $sql .= ' LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** @return list<array<string,mixed>> */
    public static function children(int $parentId, bool $publishedOnly = true): array
    {
        $sql = 'SELECT * FROM programs WHERE parent_id = :pid';
        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }
        $sql .= ' ORDER BY sort_order, title';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([':pid' => $parentId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Top-level programs with their children attached under a `children` key.
     *
     * @return list<array<string,mixed>>
     */
    public static function tree(bool $publishedOnly = true): array
    {
        $status = $publishedOnly ? "status = 'published'" : '1=1';
        $pdo = Database::connection();
        $parents = $pdo->query(
            "SELECT * FROM programs WHERE parent_id IS NULL AND {$status} ORDER BY sort_order, title"
        )->fetchAll() ?: [];

        $children = $pdo->query(
            "SELECT * FROM programs WHERE parent_id IS NOT NULL AND {$status} ORDER BY sort_order, title"
        )->fetchAll() ?: [];

        $byParent = [];
        foreach ($children as $c) {
            $byParent[(int) $c['parent_id']][] = $c;
        }
        foreach ($parents as &$p) {
            $p['children'] = $byParent[(int) $p['id']] ?? [];
        }
        unset($p);

        return $parents;
    }

    /** @param array<string,mixed> $data */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO programs (parent_id, slug, title, summary, body, cover_image, sort_order, status)
             VALUES (:parent_id, :slug, :title, :summary, :body, :cover_image, :sort_order, :status)'
        );
        $stmt->execute([
            ':parent_id'   => $data['parent_id']   ?? null,
            ':slug'        => $data['slug'],
            ':title'       => $data['title'],
            ':summary'     => $data['summary']     ?? null,
            ':body'        => $data['body']        ?? null,
            ':cover_image' => $data['cover_image'] ?? null,
            ':sort_order'  => (int) ($data['sort_order'] ?? 0),
            ':status'      => $data['status']      ?? 'published',
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE programs SET parent_id=:parent_id, slug=:slug, title=:title,
             summary=:summary, body=:body, cover_image=:cover_image,
             sort_order=:sort_order, status=:status WHERE id=:id'
        );
        $stmt->execute([
            ':parent_id'   => $data['parent_id']   ?? null,
            ':slug'        => $data['slug'],
            ':title'       => $data['title'],
            ':summary'     => $data['summary']     ?? null,
            ':body'        => $data['body']        ?? null,
            ':cover_image' => $data['cover_image'] ?? null,
            ':sort_order'  => (int) ($data['sort_order'] ?? 0),
            ':status'      => $data['status']      ?? 'published',
            ':id'          => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM programs WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
