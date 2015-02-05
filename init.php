<?php
/*
Plugin Name: Marketo Connector - Public Version
Plugin URI: http://hooshmarketing.com.au/internal/wordpress-plugin-documentation/
Description: This public version of Marketo Connector for Wordpress is a cut down version of <a href="https://launchpoint.marketo.com/hoosh-marketing/1181-wordpress-integration-for-marketo/">MarketoConnector for Wordpress</a>. It supports Marketo Spark Edition and above.
Version: 1.0.0
Author: Hoosh Marketing
Author URI: http://hooshmarketing.com.au/internal/wordpress-plugin-documentation/
Text Domain: marketoconnector-public
*/
include_once ( 'marketo-connector.php' );
include_once ( 'marketo-admin.php' );
$marketoConnector = new MarketoConnector();

if (is_admin()){
	$marketoAdmin = new MarketoAdmin( $marketoConnector );
}
register_activation_hook( __FILE__, array($marketoConnector,'activate'));
register_deactivation_hook( __FILE__, array($marketoConnector,'deactivate'));