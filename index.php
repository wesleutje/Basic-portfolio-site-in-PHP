<?php
require 'vendor/autoload.php';
date_default_timezone_set ('Europe/Amsterdam');

//$log = new Monolog\Logger('name');
//$log->pushHandler(new Monolog\Handler\StreamHandler('app.txt', Monolog\Logger::WARNING));
//$log->addWarning('Foo');

$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->get('/', function() use($app){
	$app->render('about.twig');
})->name('home');

$app->get('/contact', function() use($app){
	$app->render('contact.twig');
})->name('contact');

$app->post('/contact', function() use($app){
	$name = $app->request->post('name');
	$email = $app->request->post('email');
	$msg = $app->request->post('msg');
	
	if(!empty($name) && !empty($email) && !empty($msg)){
		$cleanName = filter_var($name, FILTER_SANITIZE_STRING);
		$cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
		$cleanMsg = filter_var($msg, FILTER_SANITIZE_STRING);
	} else { 
		$app->redirect('/contact');
	}
	
	$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
	$mailer = \Swift_Mailer::newInstance($transport);
	
	$message = \Swift_Message::newInstance();
	$message->setSubject('Email from our website');
	$message->setFrom(array($cleanEmail => $cleanName));
	$message->setTo(array('treehouse@localhost'));
	$message->setBody($cleanMsg);
	
	$result = $mailer->send($message);
	
	if($result > 0){
		$app->redirect('/');	
	} else {
		$app->redirect('/contact');
	}
	
});
	
$app->run();