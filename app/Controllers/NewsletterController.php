<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Models\Newsletter;
final class NewsletterController { public function subscribe():string{Csrf::requireValid();$email=trim((string)Request::input('email',''));$name=trim((string)Request::input('name',''));if(!filter_var($email,FILTER_VALIDATE_EMAIL)){$_SESSION['flash'][]=['type'=>'error','message'=>'Please enter a valid email address.'];}else{Newsletter::subscribe($email,$name);$_SESSION['flash'][]=['type'=>'success','message'=>'Thank you for subscribing to Makueni Youth Network updates.'];} $back=$_SERVER['HTTP_REFERER']??'/';Response::redirect($back);return '';} }
