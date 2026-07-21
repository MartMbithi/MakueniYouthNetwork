<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Response;
use App\Core\View;
use App\Models\AdvocacyContent;
use App\Models\PageView;
final class AdvocacyController {
 public function campaigns(): string { return $this->index('campaigns'); }
 public function campaignShow(string $slug): string { return $this->show('campaigns',$slug); }
 public function opportunities(): string { return $this->index('opportunities'); }
 public function opportunityShow(string $slug): string { return $this->show('opportunities',$slug); }
 public function resources(): string { return $this->index('resources'); }
 public function resourceShow(string $slug): string { return $this->show('resources',$slug); }
 public function media(): string { return $this->index('media'); }
 public function mediaShow(string $slug): string { return $this->show('media',$slug); }
 private const MAP=[
  'campaigns'=>['type'=>'campaign','title'=>'Advocacy Campaigns','intro'=>'Follow the causes, policy priorities and community actions championed by Makueni Youth Network.'],
  'opportunities'=>['type'=>'opportunity','title'=>'Youth Opportunities','intro'=>'Discover grants, scholarships, jobs, internships, fellowships, competitions and other opportunities for young people.'],
  'resources'=>['type'=>'resource','title'=>'Resource Centre','intro'=>'Access useful policies, toolkits, guides, reports and advocacy resources.'],
  'media'=>['type'=>'media','title'=>'Media Centre','intro'=>'Browse news, press releases, publications and media updates from Makueni Youth Network.'],
 ];
 public function index(string $section): string { $cfg=self::MAP[$section]??null;if(!$cfg){Response::notFound();return '';} PageView::record('/'.$section);return View::render('public/advocacy-index.twig',['section'=>$section,'title'=>$cfg['title'],'intro'=>$cfg['intro'],'items'=>AdvocacyContent::all($cfg['type'],true)]); }
 public function show(string $section,string $slug): string { $cfg=self::MAP[$section]??null;if(!$cfg){Response::notFound();return '';} $item=AdvocacyContent::findBySlug($cfg['type'],$slug);if(!$item){Response::notFound();return '';} PageView::record('/'.$section.'/'.$slug);return View::render('public/advocacy-show.twig',['section'=>$section,'item'=>$item]); }
}
