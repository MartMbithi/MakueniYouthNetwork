<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\View;use App\Models\Newsletter;
final class NewsletterController {public function index():string{return View::render('admin/newsletter/index.twig',['subscribers'=>Newsletter::all()]);}}
