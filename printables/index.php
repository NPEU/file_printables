<?php
#echo "<pre>\n"; var_dump($_GET); echo "</pre>\n";;
#echo "<pre>\n"; var_dump($_SERVER); echo "</pre>\n";
#echo "<pre>\n"; var_dump($_COOKIE); echo "</pre>\n";
$application_env = $_SERVER['SERVER_NAME'] == 'dev.npeu.ox.ac.uk' ? 'development' : ($_SERVER['SERVER_NAME'] == 'test.npeu.ox.ac.uk' ? 'testing' : 'production');
if ($application_env == 'development') {
	@define('DEV', true);
    ini_set('display_errors', 'on');
    error_reporting(-1);
    $my_error_handler = set_error_handler(function() { return false; });
} else {
	@define('DEV', false);
}

if ($application_env == 'testing') {
	@define('TEST', true);
} else {
	@define('TEST', false);
}

require_once __DIR__ . '/vendor/autoload.php';

//session_start();

// Note the following file contains database passwords and is .gitignored in the repo.
// It's PARAMOUNT that this file does not find it's way outside the server.
#require_once('_settings.php');

$params = array();

// Set up Joomla User stuff:
define('DS', DIRECTORY_SEPARATOR);
$base_path = realpath(dirname(dirname(dirname(__DIR__))));
define('BASE_PATH', $base_path . DS);

define( 'JDATE', 'Y-m-d H:i:s A' );
define( '_JEXEC', 1 );

if (DEV) {
    define( 'JPATH_BASE', BASE_PATH . 'jan_dev' . DS .'public' );
    define( 'TOP_DOMAIN', 'https://dev.npeu.ox.ac.uk' );
    define( 'JDB', 'jan_dev' );
} elseif (TEST) {	
    define( 'JPATH_BASE', BASE_PATH . 'jan_test' . DS .'public' );
    define( 'TOP_DOMAIN', 'https://test.npeu.ox.ac.uk' );
    define( 'JDB', 'jan_test' );	
} else {	
    define( 'JPATH_BASE', BASE_PATH . 'jan' . DS .'public' );
    define( 'TOP_DOMAIN', 'https://www.npeu.ox.ac.uk' );
    define( 'JDB', 'jan' );	
}
#echo "<pre>"; var_dump( DEV ); echo "</pre>"; exit;

require_once ( JPATH_BASE . DS .'includes' . DS . 'defines.php' );
require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );
$app = JFactory::getApplication('site');
$app->initialise(null, false);

JPluginHelper::importPlugin('system');
JPluginHelper::importPlugin('user');

$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onAfterInitialise');

$session = JFactory::getSession();
$user    = JFactory::getUser();

$authorised = false;

if (array_key_exists(10, $user->groups) || $user->authorise('core.admin')) {
    $authorised = true;
}
#echo "<pre>"; var_dump( $user->authorise('core.admin') ); echo "</pre>"; exit;
#echo '$authorised' . "<pre>"; var_dump( $authorised ); echo "</pre>"; exit;

if (!$authorised) {
    die;
}



//

/*
#$json = json_encode(array('staff' => $is_staff_member));
$json = json_encode($user);
#$json = json_encode($_COOKIE);
#$json = json_encode($_SESSION);
#$json = json_encode(array('test', 'ing'));

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo $json;
exit;
*/
//

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
/*
set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__,
	'PrintableService',
	get_include_path(),
	)));
spl_autoload_register(function($class) {
        @include str_replace('_', '/', $class) . '.php';
    }
);
*/
$my_error_handler = set_error_handler(function() { return false; });

require_once 'server_vars.php';
#require_once 'PrintableHelpers.php';

require_once '../detect_server.php';

$classname = preg_replace('/[^a-z0-9-]/', '', $_SERVER['PATH_INFO']);
$classname = ucwords(preg_replace('/-/', ' ', $classname));
$classname = trim(preg_replace('/\s/', '', $classname), '/');
$classname = '\\PrintableService\\' . $classname . '\\' . $classname;
#echo "<pre>"; var_dump( $classname ); echo "</pre>"; #exit;

/* LOG ---------------------------------*/
/*
$log_host     = 'localhost';
$log_database = 'printable_service_log';

if (DEV) {
    $log_database = 'printable_service_log_dev';
}
if (TEST) {
    $log_database = 'printable_service_log_test';
}

$log_username = NPEU_DATABASE_USR;
$log_password = NPEU_DATABASE_PWD;

$log_db = new PDO("mysql:host=$log_host;dbname=$log_database", $log_username, $log_password, array(
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8;'
));

$date           = $log_db->quote(date('c'));
$timestamp      = time();
$user_agent     = isset($_SERVER['HTTP_USER_AGENT']) ? $log_db->quote($_SERVER['HTTP_USER_AGENT']) : '""';
$remote_address = isset($_SERVER['REMOTE_ADDR']) ? $log_db->quote($_SERVER['REMOTE_ADDR']) : '""';
$request_uri    = isset($_SERVER['REQUEST_URI']) ? $log_db->quote($_SERVER['REQUEST_URI']) : '""';
$request_method = isset($_SERVER['REQUEST_METHOD']) ? $log_db->quote($_SERVER['REQUEST_METHOD']) : '""';
$post_data      = isset($_POST) ? $log_db->quote(stripslashes(json_encode($_POST))) : '""';
$post_body      = isset($_POST) ? $log_db->quote(file_get_contents('php://input')) : '""';

#echo "Body<pre>"; var_dump( $post_body ); echo "</pre>"; exit;

$sql = "INSERT INTO `log` (`date`,`timestamp`,`user_agent`,`remote_address`,`request_uri`,`request_method`,`post_data`,`post_body`) "
	 . "VALUES ($date,$timestamp,$user_agent,$remote_address,$request_uri,$request_method,$post_data,$post_body);";
$log_db->exec($sql);
*/
/*-------------------------------------*/
#echo 'here'; exit;

$service = new $classname();

$r = $service->run();

if (!$r) {
   echo '<h1>Error</h1><p>Something wasn\'t right or went wrong - we couldn\'t complete that request.</p>';
}

exit;

?>