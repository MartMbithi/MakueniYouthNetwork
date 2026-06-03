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

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Slug;
use App\Core\View;
use App\Models\Program;
use App\Services\ImageProcessor;

final class ProgramController
{
    public function index(): string
    {
        return View::render('admin/programs/index.twig', [
            'title'    => 'Programs',
            'programs' => Program::all(null),
        ]);
    }

    public function create(): string
    {
        return View::render('admin/programs/form.twig', [
            'title'   => 'New program',
            'program' => $this->blank(),
            'parents' => Program::all(null),
            'action'  => '/admin/programs',
            'mode'    => 'create',
        ]);
    }

    public function store(): string
    {
        Csrf::requireValid();
        $data = $this->collect();
        $errors = $this->validate($data);
        if ($errors !== []) {
            return $this->renderForm($data, $errors, 'create', '/admin/programs');
        }
        $slug = $this->resolveSlug($data['slug'] !== '' ? $data['slug'] : $data['title'], null);
        $cover = ImageProcessor::resolve('cover_image', null);

        $id = Program::create([
            'parent_id'   => $data['parent_id'],
            'slug'        => $slug,
            'title'       => $data['title'],
            'summary'     => $data['summary'],
            'body'        => $data['body'],
            'cover_image' => $cover,
            'sort_order'  => $data['sort_order'],
            'status'      => $data['status'],
        ]);
        View::flash('Program created.', 'success');
        Response::redirect('/admin/programs/' . $id . '/edit');
        return '';
    }

    public function edit(string $id): string
    {
        $program = Program::find((int) $id);
        if (!$program) {
            Response::notFound();
            return '';
        }
        return View::render('admin/programs/form.twig', [
            'title'   => 'Edit program',
            'program' => $program,
            'parents' => array_filter(Program::all(null), static fn (array $p): bool => (int) $p['id'] !== (int) $id),
            'action'  => '/admin/programs/' . $id,
            'mode'    => 'edit',
        ]);
    }

    public function update(string $id): string
    {
        Csrf::requireValid();
        $program = Program::find((int) $id);
        if (!$program) {
            Response::notFound();
            return '';
        }
        $data = $this->collect();
        $errors = $this->validate($data);
        if ($errors !== []) {
            return $this->renderForm(array_merge($program, $data), $errors, 'edit', '/admin/programs/' . $id);
        }
        $slug = $this->resolveSlug($data['slug'] !== '' ? $data['slug'] : $data['title'], (int) $id);
        $cover = ImageProcessor::resolve('cover_image', $program['cover_image'] ?? null);

        Program::update((int) $id, [
            'parent_id'   => $data['parent_id'],
            'slug'        => $slug,
            'title'       => $data['title'],
            'summary'     => $data['summary'],
            'body'        => $data['body'],
            'cover_image' => $cover,
            'sort_order'  => $data['sort_order'],
            'status'      => $data['status'],
        ]);
        View::flash('Program saved.', 'success');
        Response::redirect('/admin/programs/' . $id . '/edit');
        return '';
    }

    public function destroy(string $id): string
    {
        Csrf::requireValid();
        Program::delete((int) $id);
        View::flash('Program deleted.', 'info');
        Response::redirect('/admin/programs');
        return '';
    }

    /** @return array<string,mixed> */
    private function collect(): array
    {
        $action = (string) Request::input('action', 'save');
        $status = $action === 'publish' ? 'published' : ($action === 'draft' ? 'draft' : (string) Request::input('status', 'published'));
        $parent = Request::input('parent_id', '');
        return [
            'title'      => trim((string) Request::input('title', '')),
            'slug'       => trim((string) Request::input('slug', '')),
            'summary'    => trim((string) Request::input('summary', '')),
            'body'       => (string) Request::input('body', ''),
            'parent_id'  => $parent !== '' && $parent !== null ? (int) $parent : null,
            'sort_order' => (int) Request::input('sort_order', 0),
            'status'     => $status,
            'cover_file' => Request::file('cover_image_file'),
            'cover_image'=> trim((string) Request::input('cover_image', '')),
        ];
    }

    /** @return array<string,array<int,string>> */
    private function validate(array $data): array
    {
        $errors = [];
        if ($data['title'] === '') {
            $errors['title'][] = 'Title is required.';
        }
        return $errors;
    }

    private function resolveSlug(string $source, ?int $id): string
    {
        $pdo = Database::connection();
        return Slug::unique($source, static function (string $slug) use ($pdo, $id): bool {
            $stmt = $pdo->prepare('SELECT id FROM programs WHERE slug = :s LIMIT 1');
            $stmt->execute([':s' => $slug]);
            $row = $stmt->fetch();
            return $row !== false && (int) $row['id'] !== $id;
        });
    }

    private function renderForm(array $program, array $errors, string $mode, string $action): string
    {
        return View::render('admin/programs/form.twig', [
            'title'   => $mode === 'create' ? 'New program' : 'Edit program',
            'program' => $program,
            'parents' => Program::all(null),
            'action'  => $action,
            'mode'    => $mode,
            'errors'  => $errors,
        ]);
    }

    /** @return array<string,mixed> */
    private function blank(): array
    {
        return [
            'id' => null, 'slug' => '', 'title' => '', 'summary' => '',
            'body' => '', 'cover_image' => null, 'parent_id' => null,
            'sort_order' => 0, 'status' => 'published',
        ];
    }
}
