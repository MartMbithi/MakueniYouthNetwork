<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use App\Models\PageView;
final class SearchController {
 public function index():string{$q=trim((string)Request::input('q',''));$results=[];if(mb_strlen($q)>=2){$pdo=Database::connection();$like='%'.$q.'%';$sql="SELECT title,slug,excerpt AS summary,'impact' section FROM posts WHERE status='published' AND (title LIKE :q1 OR excerpt LIKE :q2 OR body LIKE :q3) UNION ALL SELECT title,slug,summary,CASE content_type WHEN 'campaign' THEN 'campaigns' WHEN 'opportunity' THEN 'opportunities' WHEN 'resource' THEN 'resources' ELSE 'media' END section FROM advocacy_content WHERE status='published' AND (title LIKE :q4 OR summary LIKE :q5 OR body LIKE :q6) UNION ALL SELECT title,slug,summary,'programs' section FROM programs WHERE status='published' AND (title LIKE :q7 OR summary LIKE :q8 OR body LIKE :q9) LIMIT 60";$s=$pdo->prepare($sql);$p=[];for($i=1;$i<=9;$i++)$p[':q'.$i]=$like;$s->execute($p);$results=$s->fetchAll()?:[];}PageView::record('/search');return View::render('public/search.twig',['query'=>$q,'results'=>$results]);}
}
