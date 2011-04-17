<?php
/**
 * Retrieves and creates the wp-config.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the wp-config.php to be created using this page.
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * We are installing.
 *
 * @package WordPress
 */
define('WP_INSTALLING', true);

/**
 * We are blissfully unaware of anything.
 */
define('WP_SETUP_CONFIG', true);

/**
 * Disable error reporting
 *
 * Set this to error_reporting( E_ALL ) or error_reporting( E_ALL | E_STRICT ) for debugging
 */
error_reporting(0);

/**#@+
 * These three defines are required to allow us to use require_wp_db() to load
 * the database class while being wp-content/db.php aware.
 * @ignore
 */
define('ABSPATH', dirname(dirname(__FILE__)).'/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_DEBUG', false);
/**#@-*/

require_once(ABSPATH . WPINC . '/load.php');
require_once(ABSPATH . WPINC . '/compat.php');
require_once(ABSPATH . WPINC . '/functions.php');
require_once(ABSPATH . WPINC . '/class-wp-error.php');
require_once(ABSPATH . WPINC . '/version.php');

if (!file_exists(ABSPATH . 'wp-config-sample.php'))
	wp_die('Tarvitsen wp-config-sample.php -tiedoston lähtökohdaksi. Siirrä tämä tiedosto uudelleen WordPressin asennuskansioosi.');

$configFile = file(ABSPATH . 'wp-config-sample.php');

// Check if wp-config.php has been created
if (file_exists(ABSPATH . 'wp-config.php'))
	wp_die("<p>Asetustiedosto 'wp-config.php' on jo olemassa. Jos haluat nollata tiedoston sisältämät asetukset, poista se ensin. Voit sitten yrittää <a href='install.php'>asennusta uudelleen</a>.</p>");

// Check if wp-config.php exists above the root directory but is not part of another install
if (file_exists(ABSPATH . '../wp-config.php') && ! file_exists(ABSPATH . '../wp-settings.php'))
	wp_die("<p>Tiedosto 'wp-config.php' on jo olemassa ylemmässä hakemistossa. Jos haluat nollata tiedoston sisältämät asetukset, poista se ensin. Voit sitten yrittää <a href='install.php'>asennusta uudelleen</a>.</p>");

if ( version_compare( $required_php_version, phpversion(), '>' ) )
	wp_die( sprintf( /*WP_I18N_OLD_PHP*/'Palvelimellasi on PHP-versio %1$s mutta WordPress vaatii ainakin version %2$s.'/*/WP_I18N_OLD_PHP*/, phpversion(), $required_php_version ) );

if ( !extension_loaded('mysql') && !file_exists(ABSPATH . 'wp-content/db.php') )
	wp_die( /*WP_I18N_OLD_MYSQL*/'PHP-asennuksestasi vaikuttaa puuttuvan MySQL-laajennus, joka on WordPressille välttämätön. Asenna se ensin.'/*/WP_I18N_OLD_MYSQL*/ );

if (isset($_GET['step']))
	$step = $_GET['step'];
else
	$step = 0;

/**
 * Display setup wp-config.php file header.
 *
 * @ignore
 * @since 2.3.0
 * @package WordPress
 * @subpackage Installer_WP_Config
 */
function display_header() {
	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>WordPress &rsaquo; Asetustiedosto</title>
<link rel="stylesheet" href="css/install.css" type="text/css" />

</head>
<body>
<h1 id="logo"><img alt="WordPress" src="images/wordpress-logo.png" /></h1>
<?php
}//end function display_header();

switch($step) {
	case 0:
		display_header();
?>

<p>Tervetuloa käyttämään WordPressiä. Tarvitset seuraavat tietokantaan liittyvät tiedot ennen jatkamista.</p>
<ol>
	<li>Tietokannan nimi</li>
	<li>Tietokannan käyttäjänimi</li>
	<li>Tietokannan salasana</li>
	<li>Tietokantapalvelin</li>
	<li>Tietokantataulujen etuliite (jos haluat tehdä useamman WordPress-asennuksen samaan tietokantaan) </li>
</ol>
<p><strong>Jos tämä automaattinen asetustyökalu ei jostain syystä toimi, ei hätää. Voit myös avata tiedoston <code>wp-config-sample.php</code> tekstieditoriin, täyttää tarvittavat tiedot ja tallentaa sen nimellä <code>wp-config.php</code>. </strong></p>
<p>Olet saanut todennäköisesti yllämainitut tiedot palveluntarjoajaltasi. Muussa tapauksessa sinun pitää olla ensin palveluntarjoajaasi yhteydessä tietojen saamiseksi. Jos olet valmis&hellip;</p>

<p class="step"><a href="setup-config.php?step=1<?php if ( isset( $_GET['noapi'] ) ) echo '&amp;noapi'; ?>" class="button">Jatketaan!</a></p>
<?php
	break;

	case 1:
		display_header();
	?>
<form method="post" action="setup-config.php?step=2">
	<p>Kirjoita alle tietokannan tiedot. Jos et ole varma yksityiskohdista, ota yhteys palveluntarjoajaasi.</p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname">Tietokannan nimi</label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="wordpress" /></td>
			<td>Sen tietokannan nimi, jota haluat käyttää WordPressin kanssa. </td>
		</tr>
		<tr>
			<th scope="row"><label for="uname">Tunnus</label></th>
			<td><input name="uname" id="uname" type="text" size="25" value="username" /></td>
			<td>MySQL-käyttäjätunnuksesi</td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd">Salasana</label></th>
			<td><input name="pwd" id="pwd" type="text" size="25" value="password" /></td>
			<td>...ja MySQL-salasanasi.</td>
		</tr>
		<tr>
			<th scope="row"><label for="dbhost">Tietokantapalvelin</label></th>
			<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
			<td>Jos <code>localhost</code> ei toimi, sinun pitäisi saada tämä tieto palveluntarjoajaltasi.</td>
		</tr>
		<tr>
			<th scope="row"><label for="prefix">Tietokantataulujen etuliite</label></th>
			<td><input name="prefix" id="prefix" type="text" id="prefix" value="wp_" size="25" /></td>
			<td>Jos haluat tehdä useamman WordPress-asennuksen samaan tietokantaan, muuta tämä joksikin muuksi.</td>
		</tr>
	</table>
	<?php if ( isset( $_GET['noapi'] ) ) { ?><input name="noapi" type="hidden" value="true" /><?php } ?>
	<p class="step"><input name="submit" type="submit" value="Tallenna" class="button" /></p>
</form>
<?php
	break;

	case 2:
	$dbname  = trim($_POST['dbname']);
	$uname   = trim($_POST['uname']);
	$passwrd = trim($_POST['pwd']);
	$dbhost  = trim($_POST['dbhost']);
	$prefix  = trim($_POST['prefix']);
	if ( empty($prefix) )
		$prefix = 'wp_';

	// Validate $prefix: it can only contain letters, numbers and underscores
	if ( preg_match( '|[^a-z0-9_]|i', $prefix ) )
		wp_die( /*WP_I18N_BAD_PREFIX*/'<strong>VIRHE</strong>: "Tietokantataulun etuliite" voi sisältää vain numeroita, kirjaimia ja alaviivan.'/*/WP_I18N_BAD_PREFIX*/ );

	// Test the db connection.
	/**#@+
	 * @ignore
	 */
	define('DB_NAME', $dbname);
	define('DB_USER', $uname);
	define('DB_PASSWORD', $passwrd);
	define('DB_HOST', $dbhost);
	/**#@-*/

	// We'll fail here if the values are no good.
	require_wp_db();
	if ( ! empty( $wpdb->error ) ) {
		$back = '<p class="step"><a href="setup-config.php?step=1" onclick="javascript:history.go(-1);return false;" class="button">Try Again</a></p>';
		wp_die( $wpdb->error->get_error_message() . $back );
	}

	// Fetch or generate keys and salts.
	$no_api = isset( $_POST['noapi'] );
	require_once( ABSPATH . WPINC . '/plugin.php' );
	require_once( ABSPATH . WPINC . '/l10n.php' );
	require_once( ABSPATH . WPINC . '/pomo/translations.php' );
	if ( ! $no_api ) {
		require_once( ABSPATH . WPINC . '/class-http.php' );
		require_once( ABSPATH . WPINC . '/http.php' );
		wp_fix_server_vars();
		/**#@+
		 * @ignore
		 */
		function get_bloginfo() {
			return ( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . str_replace( $_SERVER['PHP_SELF'], '/wp-admin/setup-config.php', '' ) );
		}
		/**#@-*/
		$secret_keys = wp_remote_get( 'https://api.wordpress.org/secret-key/1.1/salt/' );
	}

	if ( $no_api || is_wp_error( $secret_keys ) ) {
		$secret_keys = array();
		require_once( ABSPATH . WPINC . '/pluggable.php' );
		for ( $i = 0; $i < 8; $i++ ) {
			$secret_keys[] = wp_generate_password( 64, true, true );
		}
	} else {
		$secret_keys = explode( "\n", wp_remote_retrieve_body( $secret_keys ) );
		foreach ( $secret_keys as $k => $v ) {
			$secret_keys[$k] = substr( $v, 28, 64 );
		}
	}
	$key = 0;

	foreach ($configFile as $line_num => $line) {
		switch (substr($line,0,16)) {
			case "define('DB_NAME'":
				$configFile[$line_num] = str_replace("tietokannan_nimi", $dbname, $line);
				break;
			case "define('DB_USER'":
				$configFile[$line_num] = str_replace("'tietokannan_tunnus'", "'$uname'", $line);
				break;
			case "define('DB_PASSW":
				$configFile[$line_num] = str_replace("'tietokannan_salasana'", "'$passwrd'", $line);
				break;
			case "define('DB_HOST'":
				$configFile[$line_num] = str_replace("localhost", $dbhost, $line);
				break;
			case '$table_prefix  =':
				$configFile[$line_num] = str_replace('wp_', $prefix, $line);
				break;
			case "define('AUTH_KEY":
			case "define('SECURE_A":
			case "define('LOGGED_I":
			case "define('NONCE_KE":
			case "define('AUTH_SAL":
			case "define('SECURE_A":
			case "define('LOGGED_I":
			case "define('NONCE_SA":
				$configFile[$line_num] = str_replace('oma uniikki lauseesi', $secret_keys[$key++], $line );
				break;
		}
	}
	if ( ! is_writable(ABSPATH) ) :
		display_header();
?>
<p>En pysty kirjoittamaan <code>wp-config.php</code>-tiedostoon.</p>
<p>Voit luoda tiedoston <code>wp-config.php</code> käsin ja liittää allaolevan tekstin siihen.</p>
<textarea cols="98" rows="15" class="code"><?php
		foreach( $configFile as $line ) {
			echo htmlentities($line, ENT_COMPAT, 'UTF-8');
		}
?></textarea>
<p>Kun olet saanut sen tehtyä, napsauta "Asenna WordPress."</p>
<p class="step"><a href="install.php" class="button">Asenna WordPress</a></p>
<?php
	else :
		$handle = fopen(ABSPATH . 'wp-config.php', 'w');
		foreach( $configFile as $line ) {
			fwrite($handle, $line);
		}
		fclose($handle);
		chmod(ABSPATH . 'wp-config.php', 0666);
		display_header();
?>
<p>Mainiota! Selvisit tähän asti, WordPress voi nyt ottaa yhteyden tietokantaasi. Jos olet valmis&hellip;</p>

<p class="step"><a href="install.php" class="button">Asenna WordPress</a></p>
<?php
	endif;
	break;
}
?>
</body>
</html>
