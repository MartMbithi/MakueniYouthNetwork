<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Database;
final class PageView {
 public static function record(string $path):void{try{$s=Database::connection()->prepare('INSERT INTO page_views(path,viewed_at) VALUES(:path,NOW())');$s->execute([':path'=>substr($path,0,255)]);}catch(\Throwable $e){}}
 public static function top(int $limit=10):array{$s=Database::connection()->prepare('SELECT path,COUNT(*) views FROM page_views GROUP BY path ORDER BY views DESC LIMIT :lim');$s->bindValue(':lim',$limit,\PDO::PARAM_INT);$s->execute();return $s->fetchAll()?:[];}
}
