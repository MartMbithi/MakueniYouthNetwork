<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Database;
final class AdvocacyContent
{
    private const TYPES = ['campaign','opportunity','resource','media'];
    public static function all(string $type, bool $publishedOnly=false): array {
        if (!in_array($type,self::TYPES,true)) return [];
        $sql='SELECT * FROM advocacy_content WHERE content_type=:type';
        if($publishedOnly) $sql.=" AND status='published'";
        $sql.=' ORDER BY COALESCE(deadline,published_at,created_at) DESC, id DESC';
        $s=Database::connection()->prepare($sql); $s->execute([':type'=>$type]); return $s->fetchAll()?:[];
    }
    public static function find(int $id): ?array { $s=Database::connection()->prepare('SELECT * FROM advocacy_content WHERE id=:id');$s->execute([':id'=>$id]);$r=$s->fetch();return $r?:null; }
    public static function findBySlug(string $type,string $slug): ?array { $s=Database::connection()->prepare("SELECT * FROM advocacy_content WHERE content_type=:type AND slug=:slug AND status='published' LIMIT 1");$s->execute([':type'=>$type,':slug'=>$slug]);$r=$s->fetch();return $r?:null; }
    public static function slugExists(string $slug, ?int $except=null): bool { $sql='SELECT COUNT(*) FROM advocacy_content WHERE slug=:slug';$p=[':slug'=>$slug];if($except){$sql.=' AND id<>:id';$p[':id']=$except;} $s=Database::connection()->prepare($sql);$s->execute($p);return (int)$s->fetchColumn()>0; }
    public static function create(array $d): int { $s=Database::connection()->prepare('INSERT INTO advocacy_content(content_type,slug,title,summary,body,category,organization,location,deadline,external_url,file_url,status,published_at) VALUES(:content_type,:slug,:title,:summary,:body,:category,:organization,:location,:deadline,:external_url,:file_url,:status,:published_at)');$s->execute(self::params($d));return (int)Database::connection()->lastInsertId(); }
    public static function update(int $id,array $d): void { $p=self::params($d);$p[':id']=$id;$s=Database::connection()->prepare('UPDATE advocacy_content SET content_type=:content_type,slug=:slug,title=:title,summary=:summary,body=:body,category=:category,organization=:organization,location=:location,deadline=:deadline,external_url=:external_url,file_url=:file_url,status=:status,published_at=:published_at WHERE id=:id');$s->execute($p); }
    public static function delete(int $id): void { $s=Database::connection()->prepare('DELETE FROM advocacy_content WHERE id=:id');$s->execute([':id'=>$id]); }
    private static function params(array $d): array { return [':content_type'=>$d['content_type'],':slug'=>$d['slug'],':title'=>$d['title'],':summary'=>$d['summary']?:null,':body'=>$d['body']?:null,':category'=>$d['category']?:null,':organization'=>$d['organization']?:null,':location'=>$d['location']?:null,':deadline'=>$d['deadline']?:null,':external_url'=>$d['external_url']?:null,':file_url'=>$d['file_url']?:null,':status'=>$d['status']??'draft',':published_at'=>$d['published_at']?:null]; }
}
