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

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Slug;
use App\Core\View;
use App\Models\Category;
use App\Models\Post;
use App\Services\ImageProcessor;

final class PostController
{
    public function index(): string
    {
        return View::render('admin/posts/index.twig', [
            'title' => 'Posts',
            'posts' => Post::all(),
        ]);
    }

    public function create(): string
    {
        return View::render('admin/posts/form.twig', [
            'title'      => 'New post',
            'post'       => $this->blank(),
            'categories' => Category::all(),
            'action'     => '/admin/posts',
            'mode'       => 'create',
        ]);
    }

    public function store(): string
    {
        Csrf::requireValid();
        $data = $this->collect();
        $errors = $this->validate($data, null);
        if ($errors !== []) {
            return $this->renderForm($data, $errors, 'create', '/admin/posts');
        }
        $slug = $this->resolveSlug($data['slug'] !== '' ? $data['slug'] : $data['title'], null);
        $cover = ImageProcessor::resolve('cover_image', null);

        $id = Post::create([
            'slug'         => $slug,
            'title'        => $data['title'],
            'excerpt'      => $data['excerpt'] !== '' ? $data['excerpt'] : null,
            'body'         => $data['body'],
            'cover_image'  => $cover,
            'category_id'  => $data['category_id'] !== null ? (int) $data['category_id'] : null,
            'author_id'    => Auth::user()['id'] ?? null,
            'status'       => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? ($data['published_at'] !== '' ? $data['published_at'] : date('Y-m-d H:i:s'))
                : null,
        ]);
        View::flash('Post created.', 'success');
        Response::redirect('/admin/posts/' . $id . '/edit');
        return '';
    }

    public function edit(string $id): string
    {
        $post = Post::find((int) $id);
        if (!$post) {
            Response::notFound();
            return '';
        }
        return View::render('admin/posts/form.twig', [
            'title'      => 'Edit post',
            'post'       => $post,
            'categories' => Category::all(),
            'action'     => '/admin/posts/' . $id,
            'mode'       => 'edit',
        ]);
    }

    public function update(string $id): string
    {
        Csrf::requireValid();
        $post = Post::find((int) $id);
        if (!$post) {
            Response::notFound();
            return '';
        }
        $data = $this->collect();
        $errors = $this->validate($data, (int) $id);
        if ($errors !== []) {
            return $this->renderForm(array_merge($post, $data), $errors, 'edit', '/admin/posts/' . $id);
        }
        $slug = $this->resolveSlug($data['slug'] !== '' ? $data['slug'] : $data['title'], (int) $id);
        $cover = ImageProcessor::resolve('cover_image', $post['cover_image'] ?? null);

        Post::update((int) $id, [
            'slug'         => $slug,
            'title'        => $data['title'],
            'excerpt'      => $data['excerpt'] !== '' ? $data['excerpt'] : null,
            'body'         => $data['body'],
            'cover_image'  => $cover,
            'category_id'  => $data['category_id'] !== null ? (int) $data['category_id'] : null,
            'status'       => $data['status'],
            'published_at' => $data['status'] === 'published'
                ? ($data['published_at'] !== '' ? $data['published_at'] : ($post['published_at'] ?? date('Y-m-d H:i:s')))
                : null,
        ]);
        View::flash('Post saved.', 'success');
        Response::redirect('/admin/posts/' . $id . '/edit');
        return '';
    }

    public function destroy(string $id): string
    {
        Csrf::requireValid();
        Post::delete((int) $id);
        View::flash('Post deleted.', 'info');
        Response::redirect('/admin/posts');
        return '';
    }

    /** @return array<string,mixed> */
    private function collect(): array
    {
        $action = (string) Request::input('action', 'save');
        $status = $action === 'publish' ? 'published' : 'draft';

        return [
            'title'        => trim((string) Request::input('title', '')),
            'slug'         => trim((string) Request::input('slug', '')),
            'excerpt'      => trim((string) Request::input('excerpt', '')),
            'body'         => (string) Request::input('body', ''),
            'category_id'  => (string) Request::input('category_id', '') !== '' ? Request::input('category_id') : null,
            'published_at' => trim((string) Request::input('published_at', '')),
            'status'       => $status,
            'cover_image'  => Request::file('cover_image_file'),
            'cover_url'    => trim((string) Request::input('cover_image', '')),
        ];
    }

    /** @return array<string,array<int,string>> */
    private function validate(array $data, ?int $id): array
    {
        $errors = [];
        if ($data['title'] === '') {
            $errors['title'][] = 'Title is required.';
        }
        if ($data['body'] === '') {
            $errors['body'][] = 'Body cannot be empty.';
        }
        return $errors;
    }

    private function resolveSlug(string $source, ?int $id): string
    {
        $pdo = Database::connection();
        return Slug::unique($source, static function (string $slug) use ($pdo, $id): bool {
            $stmt = $pdo->prepare('SELECT id FROM posts WHERE slug = :s LIMIT 1');
            $stmt->execute([':s' => $slug]);
            $row = $stmt->fetch();
            return $row !== false && (int) $row['id'] !== $id;
        });
    }

    private function renderForm(array $post, array $errors, string $mode, string $action): string
    {
        return View::render('admin/posts/form.twig', [
            'title'      => $mode === 'create' ? 'New post' : 'Edit post',
            'post'       => $post,
            'categories' => Category::all(),
            'action'     => $action,
            'mode'       => $mode,
            'errors'     => $errors,
        ]);
    }

    /** @return array<string,mixed> */
    private function blank(): array
    {
        return [
            'id' => null, 'slug' => '', 'title' => '', 'excerpt' => '', 'body' => '',
            'cover_image' => null, 'category_id' => null, 'status' => 'draft', 'published_at' => null,
        ];
    }
}
