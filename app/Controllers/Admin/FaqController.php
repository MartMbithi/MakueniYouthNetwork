<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Csrf;use App\Core\Request;use App\Core\Response;use App\Core\View;use App\Models\Faq;
final class FaqController {
 public function index():string{return View::render('admin/faqs/index.twig',['faqs'=>Faq::all()]);}
 public function create():string{return View::render('admin/faqs/form.twig',['faq'=>null,'action'=>'/admin/faqs']);}
 public function store():string{Csrf::requireValid();Faq::create($this->collect());Response::redirect('/admin/faqs');return '';}
 public function edit(string $id):string{$f=Faq::find((int)$id);if(!$f){Response::notFound();return '';}return View::render('admin/faqs/form.twig',['faq'=>$f,'action'=>'/admin/faqs/'.$id]);}
 public function update(string $id):string{Csrf::requireValid();Faq::update((int)$id,$this->collect());Response::redirect('/admin/faqs');return '';}
 public function destroy(string $id):string{Csrf::requireValid();Faq::delete((int)$id);Response::redirect('/admin/faqs');return '';}
 private function collect():array{return ['question'=>trim((string)Request::input('question','')),'answer'=>trim((string)Request::input('answer','')),'category'=>trim((string)Request::input('category','')),'sort_order'=>(int)Request::input('sort_order',0),'status'=>(string)Request::input('status','published')];}
}
