<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Slug;
use App\Core\View;
use App\Models\AdvocacyContent;
final class AdvocacyContentController {
 public function campaignIndex():string{return $this->index('campaign');} public function campaignCreate():string{return $this->create('campaign');} public function campaignStore():string{return $this->store('campaign');} public function campaignEdit(string $id):string{return $this->edit('campaign',$id);} public function campaignUpdate(string $id):string{return $this->update('campaign',$id);} public function campaignDestroy(string $id):string{return $this->destroy('campaign',$id);}
 public function opportunityIndex():string{return $this->index('opportunity');} public function opportunityCreate():string{return $this->create('opportunity');} public function opportunityStore():string{return $this->store('opportunity');} public function opportunityEdit(string $id):string{return $this->edit('opportunity',$id);} public function opportunityUpdate(string $id):string{return $this->update('opportunity',$id);} public function opportunityDestroy(string $id):string{return $this->destroy('opportunity',$id);}
 public function resourceIndex():string{return $this->index('resource');} public function resourceCreate():string{return $this->create('resource');} public function resourceStore():string{return $this->store('resource');} public function resourceEdit(string $id):string{return $this->edit('resource',$id);} public function resourceUpdate(string $id):string{return $this->update('resource',$id);} public function resourceDestroy(string $id):string{return $this->destroy('resource',$id);}
 public function mediaIndex():string{return $this->index('media');} public function mediaCreate():string{return $this->create('media');} public function mediaStore():string{return $this->store('media');} public function mediaEdit(string $id):string{return $this->edit('media',$id);} public function mediaUpdate(string $id):string{return $this->update('media',$id);} public function mediaDestroy(string $id):string{return $this->destroy('media',$id);}
 private const LABELS=['campaign'=>'Campaigns','opportunity'=>'Opportunities','resource'=>'Resources','media'=>'Media'];
 public function index(string $type):string{return View::render('admin/advocacy/index.twig',['type'=>$type,'base_path'=>$this->basePath($type),'label'=>self::LABELS[$type]??'Content','items'=>AdvocacyContent::all($type)]);}
 public function create(string $type):string{return View::render('admin/advocacy/form.twig',['type'=>$type,'base_path'=>$this->basePath($type),'label'=>self::LABELS[$type]??'Content','item'=>null,'action'=>$this->basePath($type)]);}
 public function store(string $type):string{Csrf::requireValid();$d=$this->collect($type);AdvocacyContent::create($d);$_SESSION['flash'][]=['type'=>'success','message'=>'Item created successfully.'];Response::redirect($this->basePath($type));return '';}
 public function edit(string $type,string $id):string{$item=AdvocacyContent::find((int)$id);if(!$item||$item['content_type']!==$type){Response::notFound();return '';}return View::render('admin/advocacy/form.twig',['type'=>$type,'base_path'=>$this->basePath($type),'label'=>self::LABELS[$type]??'Content','item'=>$item,'action'=>$this->basePath($type).'/'.$id]);}
 public function update(string $type,string $id):string{Csrf::requireValid();$item=AdvocacyContent::find((int)$id);if(!$item||$item['content_type']!==$type){Response::notFound();return '';}AdvocacyContent::update((int)$id,$this->collect($type,(int)$id));$_SESSION['flash'][]=['type'=>'success','message'=>'Item updated successfully.'];Response::redirect($this->basePath($type));return '';}
 public function destroy(string $type,string $id):string{Csrf::requireValid();AdvocacyContent::delete((int)$id);Response::redirect($this->basePath($type));return '';}

 private function basePath(string $type): string { return '/admin/'.($type === 'media' ? 'media' : $type.'s'); }
 private function collect(string $type,?int $id=null):array{$title=trim((string)Request::input('title',''));$raw=trim((string)Request::input('slug',''));$base=$raw?:$title;$slug=Slug::unique($base,fn(string $s)=>AdvocacyContent::slugExists($s,$id));return ['content_type'=>$type,'slug'=>$slug,'title'=>$title,'summary'=>trim((string)Request::input('summary','')),'body'=>trim((string)Request::input('body','')),'category'=>trim((string)Request::input('category','')),'organization'=>trim((string)Request::input('organization','')),'location'=>trim((string)Request::input('location','')),'deadline'=>trim((string)Request::input('deadline','')),'external_url'=>trim((string)Request::input('external_url','')),'file_url'=>trim((string)Request::input('file_url','')),'status'=>(string)Request::input('status','draft'),'published_at'=>trim((string)Request::input('published_at',''))?:date('Y-m-d H:i:s')];}
}
