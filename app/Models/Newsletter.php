<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Database;
final class Newsletter {
 public static function subscribe(string $email,string $name=''):void{$s=Database::connection()->prepare("INSERT INTO newsletter_subscribers(email,name,status,subscribed_at) VALUES(:email,:name,'active',NOW()) ON DUPLICATE KEY UPDATE name=VALUES(name),status='active',subscribed_at=NOW()");$s->execute([':email'=>$email,':name'=>$name?:null]);}
 public static function all():array{return Database::connection()->query('SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC')->fetchAll()?:[];}
}
