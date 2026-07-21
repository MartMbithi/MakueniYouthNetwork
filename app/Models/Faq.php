<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Database;
final class Faq {
 public static function all(bool $publishedOnly=false): array {$sql='SELECT * FROM faqs'.($publishedOnly?" WHERE status='published'":'').' ORDER BY sort_order ASC,id ASC';return Database::connection()->query($sql)->fetchAll()?:[];}
 public static function find(int $id):?array{$s=Database::connection()->prepare('SELECT * FROM faqs WHERE id=:id');$s->execute([':id'=>$id]);$r=$s->fetch();return $r?:null;}
 public static function create(array $d):void{$s=Database::connection()->prepare('INSERT INTO faqs(question,answer,category,sort_order,status) VALUES(:question,:answer,:category,:sort_order,:status)');$s->execute([':question'=>$d['question'],':answer'=>$d['answer'],':category'=>$d['category']?:null,':sort_order'=>(int)$d['sort_order'],':status'=>$d['status']]);}
 public static function update(int $id,array $d):void{$s=Database::connection()->prepare('UPDATE faqs SET question=:question,answer=:answer,category=:category,sort_order=:sort_order,status=:status WHERE id=:id');$s->execute([':question'=>$d['question'],':answer'=>$d['answer'],':category'=>$d['category']?:null,':sort_order'=>(int)$d['sort_order'],':status'=>$d['status'],':id'=>$id]);}
 public static function delete(int $id):void{$s=Database::connection()->prepare('DELETE FROM faqs WHERE id=:id');$s->execute([':id'=>$id]);}
}
