<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\View;
use App\Models\Faq;
use App\Models\PageView;
final class FaqController { public function index():string{PageView::record('/faq');return View::render('public/faq.twig',['faqs'=>Faq::all(true)]);} }
