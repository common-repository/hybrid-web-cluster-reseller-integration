<?php
/**
 * @package hwcreseller
 * @version 2.0
 */
/*
Plugin Name: Hybrid Web Cluster - Reseller Integration
Plugin URI: http://www.hybrid-cluster.com/
Description: This plugin turns any Wordpress blog into a full-featured resale channel for any Hybrid Web Cluster powered web hosting platform. All you need to get started is a resale account on any Hybrid Web Cluster.
Author: Kieran Simkin, Luke Marsden (Hybrid Logic)
Version: 2.0
Author URI: http://hybrid-cluster.com/
 */
global $hwcreseller_db_version;
global $hwcreseller_table_prefix;
global $hwcreseller_basketid;
global $hwcreseller_currencycache;
global $hwcreseller_eucountrycache;
global $hwcreseller_tldcache;
global $hwcreseller_costkeycache;
global $hwcreseller_accounttype_idcache;
global $hwcreseller_accounttype_namecache;
global $hwcreseller_pluginfoldername;
global $hwcreseller_session;
$hwcreseller_pluginfoldername='hybrid-web-cluster-reseller-integration';
$hwcreseller_currencycache=array();
$hwcreseller_tldcache=array();
$hwcreseller_eucountrycache=array();
$hwcreseller_accounttype_idcache=array();
$hwcreseller_accounttype_namecache=array();
$hwcreseller_db_version="1.0";
$hwcreseller_table_prefix='hwc_';
add_action('admin_menu', 'hwc_reseller_integration_plugin_menu');
add_action('hwcresellerupdatecurrencies', 'hwc_reseller_integration_update_currencies');
add_action('hwcresellerupdatetldinfo', 'hwc_reseller_integration_update_tldinfo');
add_action('hwcresellerupdateresourceprices', 'hwc_reseller_integration_update_resource_prices');
add_action('hwcresellerupdateaccounttypes', 'hwc_reseller_integration_update_accounttypes');
add_action('init', 'hwc_reseller_integration_session_init');
add_action('plugins_loaded','hwc_reseller_integration_loaded');
register_activation_hook(__FILE__,'hwc_reseller_integration_install');
register_deactivation_hook(__FILE__,'hwc_reseller_integration_uninstall');
// E.g., [hwc_account_type_info_block name='Bronze']
add_shortcode('hwc_account_type_info_block', 'hwc_reseller_integration_accounttype_infoBlockByName_wrapper');
add_shortcode('hwc_account_type_storage', 'hwc_reseller_integration_accounttype_storage_wrapper');
add_shortcode('hwc_account_type_price', 'hwc_reseller_integration_accounttype_price_wrapper');

add_shortcode('hwc_set_affiliate','hwc_reseller_integration_set_affiliate_wrapper');
add_shortcode('hwc_register_campaign_code','hwc_reseller_integration_register_campaign_code_wrapper');
add_shortcode('hwc_order_widget', 'hwc_reseller_integration_order_widget');
add_shortcode('hwc_messages', 'hwc_message');

add_shortcode('hwc_custom_package_sliders','hwc_reseller_integration_custom_package_sliders');
add_shortcode('hwc_resource_prices','hwc_reseller_integration_resource_prices');

add_shortcode('hwc_hosting_action', 'hwc_reseller_hosting_action');
add_shortcode('hwc_domains_action', 'hwc_reseller_domains_action');
add_shortcode('hwc_complete_action', 'hwc_reseller_complete_action');
add_shortcode('hwc_hosting_permalink', 'hwc_reseller_hosting_permalink');
add_shortcode('hwc_domains_permalink', 'hwc_reseller_domains_permalink');
add_shortcode('hwc_complete_permalink','hwc_reseller_complete_permalink');
add_shortcode('hwc_ajax_permalink','hwc_reseller_ajax_permalink');
add_shortcode('hwc_ajax_action', 'hwc_reseller_ajax_action');
add_shortcode('hwc_shopping_feed_action', 'hwc_reseller_shopping_feed_action');
add_shortcode('hwc_request_variable', 'hwc_reseller_request_variable');

// Install the plugin
function hwc_reseller_integration_install () {
    global $wpdb;
    global $hwcreseller_db_version;
    global $hwcreseller_table_prefix;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE `".$table_name."` (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            BasketID varchar(128) NOT NULL,
            AccountTypeID mediumint(9) default null,
            AffiliateID int(22) default null,
            Periodicity enum('monthly','yearly') default null,
            Currency char(3) NOT NULL default 'GBP',
            TimezoneIdentifier char(255) NOT NULL default 'Europe/London',
            IPAddress varchar(15) NOT NULL default '',
            NoDomain int(1) NOT NULL default 0,
            Country char(2) NOT NULL default 'GB',
            primary key (id),
            unique key (basketid)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket_domains";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE `".$table_name."` (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            BasketID varchar(128) NOT NULL,
            DomainName varchar(255) not null,
            `Type` enum('registration','transfer') not null,
            TLD varchar(20) not null,
            NumYears int(11) default null,
            primary key (id),
            unique key (BasketId,DomainName)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix.$hwcreseller_table_prefix."basket_userdata";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) { 
        $sql = "CREATE TABLE `".$table_name."` (
            BasketID varchar(128) NOT NULL,
            firstname varchar(255) NOT NULL default '',
            lastname varchar(255) NOT NULL default '',
            email varchar(1024) NOT NULL default '',
            addr1 varchar(255) NOT NULL default '',
            addr2 varchar(255) NOT NULL default '',
            town varchar(255) NOT NULL default '',
            county varchar(255) NOT NULL default '',
            postcode varchar(50) NOT NULL default '',
            phone varchar(255) NOT NULL default '',
            country char(3) NOT NULL,
            vat_no varchar(255) NOT NULL default '',
            primary key (BasketID)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix.'basket_campaigncodes';
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) { 
        $sql = "CREATE TABLE `".$table_name."` (
            id mediumint(9) NOT NULL auto_increment,
            BasketID varchar(128) NOT NULL,
            campaign_code varchar(256) NOT NULL,
            primary key (id),
            index (BasketID)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."accounttypes";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) { 
        $sql = "CREATE TABLE `".$table_name."` (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            AccountTypeID int(100) NOT NULL,
            Name varchar(100) default null,
            EmailAccounts int(22) default null,
            `ComputeUnits` int(22) default null,
            `Databases` int(22) default null,
            DiskQuota bigint(22) default null,
            FTPAccounts int(22) default null,
            Websites int(11) default null,
            TransferQuota bigint(22) default null,
            MonthlySalePrice varchar(60) not null default '0.00',
            YearlySalePrice varchar(60) not null default '0.00',
            Currency char(3) NOT NULL default 'GBP',
            primary key (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."currencies";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) { 
        $sql = "CREATE TABLE `".$table_name."` (
            `id` int(22) NOT NULL AUTO_INCREMENT,
            `alpha3` char(3) NOT NULL,
            `name` varchar(255) NOT NULL,
            `symbol` varchar(255) NOT NULL,
            `symbolbefore` enum('false','true') NOT NULL DEFAULT 'true',
            `smallunitmultiplier` int(10) NOT NULL,
            `smallunitsymbol` varchar(255) NOT NULL,
            `smallunitsymbolbefore` enum('false','true') NOT NULL DEFAULT 'false',
            `rate` varchar(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `alpha3` (`alpha3`),
            KEY `alpha3_2` (`alpha3`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."countries";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) { 
        $sql = "CREATE TABLE `".$table_name."` (
            `id` int(32) NOT NULL AUTO_INCREMENT,
            `name` varchar(256) NOT NULL,
            `alpha2` char(2) NOT NULL,
            `alpha3` char(3) NOT NULL,
            `EUMember` enum('false','true') NOT NULL default 'false',
            `Currency` char(3) NOT NULL default 'USD',
            PRIMARY KEY (`id`),
            index (`EUMember`),
            UNIQUE KEY `alpha2` (`alpha2`),
            UNIQUE KEY `alpha3` (`alpha3`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix.'us_states';
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE `".$table_name."` (
            `id` int(22) NOT NULL auto_increment, 
            `name` varchar(255) NOT NULL, 
            `alpha2` char(2) NOT NULL, 
            primary key (`id`), 
            index (`alpha2`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix.'tldinfo';
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) { 
        $sql = "CREATE TABLE `$table_name` (
            `id` int(100) NOT NULL auto_increment,
            `TLD` varchar(255) NOT NULL,
            `Currency` char(3) NOT NULL default 'GBP',
            `RegistrationSalePrice` varchar(50) NOT NULL,
            `TransferSalePrice` varchar(50) NOT NULL,
            `RenewalSalePrice` varchar(50) NOT NULL,
            `Lockable` enum('false','true') NOT NULL,
            `RealTime` enum('false','true') NOT NULL,
            `Transferable` enum('false','true') NOT NULL,
            `AuthInfo` enum('false','true') NOT NULL,
            `AutoTrans` enum('false','true') NOT NULL,
            `FaxTrans` enum('false','true') NOT NULL,
            `Protocol` varchar(255) NOT NULL,
            `MinPeriod` int(10) NOT NULL default 1,
            `MaxPeriod` int(10) NOT NULL default 10,
            `Default` enum('false','true') NOT NULL default 'false',
            `Checked` enum('false','true') NOT NULL default 'false',
            primary key (id),
            unique key (TLD)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix.'resourceprices';
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) { 
        $sql = "CREATE TABLE `$table_name` ( 
            `id` int(100) NOT NULL auto_increment,
            `costkey` varchar(255) NOT NULL,
            `costvalue` varchar(255) NOT NULL,
            `costcurrency` char(3) NOT NULL,
            primary key (id),
            unique key (costkey)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        dbDelta($sql);
    }
    wp_schedule_event(time(), 'daily', 'hwcresellerupdatecurrencies');
    wp_schedule_event(time(), 'daily', 'hwcresellerupdatetldinfo');
    wp_schedule_event(time(), 'daily', 'hwcresellerupdateaccounttypes');
    wp_schedule_event(time(), 'daily', 'hwcresellerupdateresourceprices');
    add_option("hwcreseller_db_version", $hwcreseller_db_version);
}
function hwc_reseller_integration_uninstall() { 
    wp_clear_scheduled_hook('hwcresellerupdatecurrencies');
    wp_clear_scheduled_hook('hwcresellerupdateaccounttypes');
    wp_clear_scheduled_hook('hwcresellerupdatetldinfo');
    wp_clear_scheduled_hook('hwcresellerupdateresourceprices');
}

global $hwcreseller_done_init;
$hwcreseller_done_init = false;

// Initialise the page
function hwc_reseller_integration_session_init() { 
    global $wpdb;
    global $hwcreseller_done_init;
    global $hwcreseller_table_prefix;
    global $hwcreseller_basketid;
    global $hwcreseller_pluginfoldername;
    global $hwcreseller_session;
    global $hwcreseller_message;
    if(!$hwcreseller_done_init) {
        $hwcreseller_done_init = true;
    } else {
        return;
    }
    if ( !is_admin() ) { // instruction to only load if it is not the admin area
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-widget', WP_PLUGIN_URL.'/'.$hwcreseller_pluginfoldername.'/jquery.ui.widget.js', array('jquery', 'jquery-ui-core'), '1.8.9');
            wp_enqueue_script('jquery-mouse', WP_PLUGIN_URL.'/'.$hwcreseller_pluginfoldername.'/jquery.ui.mouse.js', array('jquery', 'jquery-ui-core','jquery-widget'), '1.8.9');
            wp_enqueue_script('jquery-slider', WP_PLUGIN_URL.'/'.$hwcreseller_pluginfoldername.'/jquery.ui.slider.js', array('jquery', 'jquery-ui-core','jquery-mouse','jquery-widget'), '1.8.9');
        
        wp_enqueue_script('hwcreseller_custom_script',WP_PLUGIN_URL.'/'.$hwcreseller_pluginfoldername.'/frontendscripts.js',array('jquery','jquery-ui-core'));
        wp_enqueue_script('hwcreseller_password_strength_script',WP_PLUGIN_URL.'/'.$hwcreseller_pluginfoldername.'/digitalspaghetti.password.js',array('jquery'));
        wp_enqueue_script('hwcreseller_color_script',WP_PLUGIN_URL.'/'.$hwcreseller_pluginfoldername.'/jquery.color.js',array('jquery'));
        wp_enqueue_style('hwcreseller_custom_style',WP_PLUGIN_URL.'/'.$hwcreseller_pluginfoldername.'/frontendcss.css');
    }

    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket";
    if (!isset($_COOKIE['BasketID'])) {
        $hwcreseller_basketid=hwc_reseller_integration_random_alpha_num_string(64);
        if (!function_exists('hwcreseller_geoip_country_code_by_addr')) { 
            require_once(ABSPATH . 'wp-content/plugins/hybrid-web-cluster-reseller-integration/geoip.inc.php');
        }
        $gi = hwcreseller_geoip_open(ABSPATH . 'wp-content/plugins/hybrid-web-cluster-reseller-integration/GeoIP.dat',GEOIP_STANDARD);
        $ccode=hwcreseller_geoip_country_code_by_addr($gi,$_SERVER['REMOTE_ADDR']);
        hwcreseller_geoip_close($gi);
        $ctable_name = $wpdb->prefix . $hwcreseller_table_prefix."countries";
        $currency=$wpdb->get_var("SELECT Currency FROM $ctable_name WHERE alpha2='".$ccode."' LIMIT 1");
        $wpdb->insert($table_name,array('BasketID'=>$hwcreseller_basketid,'IPAddress'=>$_SERVER['REMOTE_ADDR'],'Country'=>$ccode,'Currency'=>$currency));
        setcookie("BasketID",$hwcreseller_basketid,time()+60*60*24*365*100,'/');
    } else {
        $hwcreseller_basketid=$_COOKIE['BasketID'];
    }
    $hwcreseller_session=hwc_get_session();
    ob_start();
    if (is_null($hwcreseller_session)) { 
        $hwcreseller_basketid=hwc_reseller_integration_random_alpha_num_string(64);
        if (!function_exists('hwcreseller_geoip_country_code_by_addr')) { 
            require_once(ABSPATH . 'wp-content/plugins/hybrid-web-cluster-reseller-integration/geoip.inc.php');
        }
        $gi = hwcreseller_geoip_open(ABSPATH . 'wp-content/plugins/hybrid-web-cluster-reseller-integration/GeoIP.dat',GEOIP_STANDARD);
        $ccode=hwcreseller_geoip_country_code_by_addr($gi,$_SERVER['REMOTE_ADDR']);
        hwcreseller_geoip_close($gi);
        $ctable_name = $wpdb->prefix . $hwcreseller_table_prefix."countries";
        $currency=$wpdb->get_var("SELECT Currency FROM $ctable_name WHERE alpha2='".$ccode."' LIMIT 1");
        $wpdb->insert($table_name,array('BasketID'=>$hwcreseller_basketid,'IPAddress'=>$_SERVER['REMOTE_ADDR'],'Country'=>$ccode,'Currency'=>$currency));
        setcookie("BasketID",$hwcreseller_basketid);
    }
    if (@$_POST['hwcreseller_action']=='changecurrency') { 
        $wpdb->query("UPDATE `$table_name` SET `Currency`='".$wpdb->escape($_POST['hwcreseller_currency'])."' WHERE `BasketID`='".$wpdb->escape($hwcreseller_basketid)."' LIMIT 1");
        $hwcreseller_session=hwc_get_session();
    } else if (@$_POST['hwcreseller_action']=='addaccounttobasket') { 
        hwc_set_account_type($_POST['hwcreseller_accounttypeid']);
        echo "Done";
        die;
    } else if (@$_POST['hwcreseller_action']=='updatebasketsummary') { 
        echo hwc_reseller_basket_widget_get_contents();
        die;
    } else if (@$_REQUEST['hwcreseller_action']=='checkusername') { 
        echo (hwc_check_username_availability(@$_REQUEST['hwcreseller_username'])==true) ? 'true' : 'false';
           die;    
    }
    
    // Handle actions which modify the basket here
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket_domains";
    if($_REQUEST['delete_domain']) {
        $domain = $_REQUEST['delete_domain'];
        $wpdb->query("DELETE FROM `$table_name` WHERE DomainName='".$wpdb->escape($domain)."' AND BasketID='".$wpdb->escape($hwcreseller_basketid)."'");
        $hwcreseller_session=hwc_get_session();
    }
    if (@$_REQUEST['affiliate']) { 
    hwc_set_affiliate_id($_REQUEST['affiliate']);
    }
    if (@$_REQUEST['code']) {
    hwc_register_campaign_code($_REQUEST['code']);
    }
    if(@$_REQUEST['add_hosting']) {
        $account=hwc_reseller_integration_accounttype_getAccountByName($_REQUEST['add_hosting']);
        hwc_set_account_type($account->AccountTypeID);
    if (@$_REQUEST['yearly']=='true') { 
        hwc_set_account_periodicity(true);
    } else {
        hwc_set_account_periodicity();
    }
    }
    if($_REQUEST['add_domains']) {
        if(is_array($_REQUEST['domains'])) {
            foreach($_REQUEST['domains'] as $domain => $checked) {
                // Insert into domains_orders table
                if($checked == 'checked') {
                    $fragments = explode(".", $domain);
                    array_shift($fragments);
                    $tld = implode(".", $fragments);
                    $years = $_REQUEST['yearlist'][$domain];
                    if(!$years) {
                        $type = "'transfer'";
            $table_name2 = $wpdb->prefix . $hwcreseller_table_prefix."tldinfo";
            $numyears = $wpdb->get_var("select MinPeriod from `$table_name2` where `TLD`='".$wpdb->escape($tld)."' LIMIT 1");
            $numyears='\''.$numyears.'\'';
                    } else {
                        $type = "'registration'";
                        $numyears = "'".$wpdb->escape($years)."'";
                    }
                    $wpdb->query("REPLACE INTO `$table_name` SET
                            BasketID='".$wpdb->escape($hwcreseller_basketid)."',
                            DomainName='".$wpdb->escape($domain)."',
                            NumYears=$numyears,
                            `Type`=$type,
                            TLD='".$wpdb->escape($tld)."'
                            ");
                }
            }
            $hwcreseller_message = __('Successfully added domains to basket.','hwcreseller');
        }

        if($_REQUEST['move_on']=='true') {
            wp_redirect(hwc_reseller_complete_permalink()); exit;
            // Otherwise the user wants to add more domains
        } else {
            // Update the session
            $hwcreseller_session=hwc_get_session();
        }
    }
    
}

// Sets the account type in the basket
function hwc_set_account_type($val=false) {
    global $hwcreseller_session;
    global $hwcreseller_basketid;
    global $hwcreseller_table_prefix;
    global $wpdb;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket";
    if($val) {
        $query = "UPDATE `$table_name` SET `AccountTypeID`='".$wpdb->escape($val)."' WHERE `BasketID`='".$wpdb->escape($hwcreseller_basketid)."' LIMIT 1";
    } else {
        $query = "UPDATE `$table_name` SET `AccountTypeID`=NULL WHERE `BasketID`='".$wpdb->escape($hwcreseller_basketid)."' LIMIT 1";
    }
    $wpdb->query($query);
    $hwcreseller_session=hwc_get_session();
}
// Sets the account periodicity in the basket
function hwc_set_account_periodicity($yearly=false) {
    global $hwcreseller_session;
    global $hwcreseller_basketid;
    global $hwcreseller_table_prefix;
    global $wpdb;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket";
    if($yearly) {
        $wpdb->query("UPDATE `$table_name` SET `Periodicity`='yearly' WHERE `BasketID`='".$wpdb->escape($hwcreseller_basketid)."' LIMIT 1");
    } else {
        $wpdb->query("UPDATE `$table_name` SET `Periodicity`='monthly' WHERE `BasketID`='".$wpdb->escape($hwcreseller_basketid)."' LIMIT 1");
    }
    $hwcreseller_session=hwc_get_session();
}

// Sets the referal affiliate id
function hwc_set_affiliate_id($affiliate_id=null) { 
        global $hwcreseller_session;
    global $hwcreseller_basketid;
    global $hwcreseller_table_prefix;
    global $wpdb;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket";
    $wpdb->query("UPDATE `$table_name` SET `AffiliateID`='".$wpdb->escape($affiliate_id)."' WHERE `BasketID`='".$wpdb->escape($hwcreseller_basketid)."' LIMIT 1");
        $hwcreseller_session=hwc_get_session();
}
// Registered a campaign code with the session
function hwc_register_campaign_code($code) { 
    global $hwcreseller_basketid;
    global $hwcreseller_table_prefix;
    global $wpdb;
    $code=strtolower($code);    
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket_campaigncodes";
    $wpdb->insert($table_name,array('BasketID'=>$hwcreseller_basketid,'campaign_code'=>$code));

}
// Get the session
function hwc_get_session() {
    global $hwcreseller_session;
    global $hwcreseller_basketid;
    global $hwcreseller_table_prefix;
    global $hwcreseller_done_init;
    if(!$hwcreseller_done_init) {
        hwc_reseller_integration_session_init();
    }
    global $wpdb;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket";
    $table_name_domains = $wpdb->prefix . $hwcreseller_table_prefix."basket_domains";
    $session = $wpdb->get_row("SELECT * FROM `$table_name` WHERE `BasketID`='".$wpdb->escape($hwcreseller_basketid)."' LIMIT 1",OBJECT);
    if (is_null($session))  {
        $hwcreseller_basketid=hwc_reseller_integration_random_alpha_num_string(64);
        $wpdb->insert($table_name,array('BasketID'=>$hwcreseller_basketid,'IPAddress'=>$_SERVER['REMOTE_ADDR']));
        setcookie("BasketID", $hwcreseller_basketid, time()+60*60*24*365*100,'/');
        $session = $wpdb->get_row("SELECT * FROM `$table_name` WHERE `BasketID`='".$wpdb->escape($hwcreseller_basketid)."' LIMIT 1",OBJECT);
    }
    $domains = $wpdb->get_results("SELECT * FROM `$table_name_domains` WHERE BasketID='".$wpdb->escape($hwcreseller_basketid)."' ORDER BY DomainName");
    $session->Domains = $domains;
    $hwcreseller_session = $session;
    return $session;
}

// Register the widgets
function hwc_reseller_integration_loaded() { 
    $widget_ops = array('classname' => 'hwc_reseller_basket_widget', 'description' => __('Displays the current web hosting basket contents.','hwcreseller') );
    wp_register_sidebar_widget('hwc_reseller_basket_widget', __('Shopping Basket','hwcreseller'), 'hwc_reseller_basket_widget', $widget_ops);
    $widget_ops = array('classname' => 'hwc_reseller_currency_widget', 'description' => __('Allows the visitor to change the currency that prices are displayed in.','hwcreseller') );
    wp_register_sidebar_widget('hwc_reseller_currency_widget', __('Currency','hwcreseller'), 'hwc_reseller_currency_widget', $widget_ops);
}

// Output the basket summary sidebar widget
function hwc_reseller_basket_widget($args) { 
    extract($args); // extracts before_widget,before_title,after_title,after_widget
    echo $before_widget . $before_title . __('Shoppping Basket','hwcreseller') . $after_title ;
    echo '<div id="hwcreseller-basket-summary-widget">';
    echo hwc_reseller_basket_widget_get_contents();
    echo '</div>';
    echo $after_widget;
}
function hwc_reseller_basket_widget_get_contents() { 
    $ret='';
    $ret='<ul>';
    $itemcount=0;
    global $hwcreseller_session;
    if (!is_null($hwcreseller_session->AccountTypeID)) {
        $account=hwc_reseller_integration_accounttype_getAccount($hwcreseller_session->AccountTypeID);
        $ret.='<li>'.$account->Name.' '.__('Cloud Hosting Account','hwcreseller').'</li>';
        $itemcount++;
    }
    if ($itemcount==0) {
        $ret.='<li>'.__('Empty Basket','hwcreseller').'</li>';
    }
    $ret.='</ul>';
    return $ret;
}

// Output the currency selector sidebar widget
function hwc_reseller_currency_widget($args) { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_session;
    global $hwcreseller_basketid;
    extract($args); // extracts before_widget,before_title,after_title,after_widget
    echo $before_widget . $before_title . 'Currency' . $after_title;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."currencies";
    ?><form method="POST" action="<?=$_SERVER['REQUEST_URI'];?>" id="hwcreseller_currency_selector_form">
    <input type="hidden" name="hwcreseller_action" value="changecurrency">
    <select id="hwcreseller_currency_selector" name="hwcreseller_currency">
    <?php
    $currencies=$wpdb->get_results("SELECT * FROM `$table_name` ORDER BY name");
    foreach ($currencies as $currency) { 
        $selected='';
        if ($currency->alpha3==$hwcreseller_session->Currency) { 
            $selected=' selected="selected"';
        } 
        ?>
            <option<?=$selected;?> value="<?=$currency->alpha3;?>"><?=$currency->name;?> (<?=$currency->symbol;?>) - <?=$currency->alpha3;?></option>
        <?php
    }
    ?>
    </select>
    </form>
    <?php
    echo $after_widget;
}

// Query the API to determine registration availability of domain
function hwc_check_domain_availability($domain) { 
    $availability=hwc_reseller_integration_api_query("CHECKDOMAIN",array('Domain'=>$domain));
    return ($availability->Availability=='true') ? true : false;
}

// Query the API to determine username availability
function hwc_check_username_availability($name) { 
    $availability=hwc_reseller_integration_api_query("CHECKUSERNAME",array('Username'=>$name));
    return ($availability->Availability=='true') ? true : false;
}
// Query the API to update the local copy of the US states database
function hwc_reseller_integration_update_us_states() { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    $table_name=$wpdb->prefix.$hwcreseller_table_prefix."us_states";
    $states=hwc_reseller_integration_api_query("USSTATELIST");
    if ($states->ErrorCount>0) { 
        var_dump($states);
        return;
    }
    $wpdb->query("DELETE FROM `$table_name`");
    foreach ($states->States->Item as $state) { 
        $rows_affected=$wpdb->insert($table_name,array(    'name'=>$state->Name,
                                'alpha2'=>$state->Alpha2));
    }
}

// Query the API to update the local copy of the countries database
function hwc_reseller_integration_update_countries() { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."countries";
    $countries=hwc_reseller_integration_api_query('COUNTRYLIST');
    if ($countries->ErrorCount>0) { 
        var_dump($countries);
        return;
    }
    $wpdb->query("DELETE FROM `$table_name`");
    foreach ($countries->Countries->Item as $country) { 
        $rows_affected = $wpdb->insert ($table_name, array(    'name'=>$country->Name,
                                    'alpha2'=>$country->Alpha2,
                                    'alpha3'=>$country->Alpha3,
                                    'EUMember'=>$country->EUMember,
                                    'Currency'=>$country->Currency
                                ) );
    }
}
// Query the API to update the local copy of the resource price database
function hwc_reseller_integration_update_resource_prices() { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    $table_name=$wpdb->prefix.$hwcreseller_table_prefix."resourceprices";
    $prices=hwc_reseller_integration_api_query('RESOURCESALEPRICELIST');
    if ($prices->ErrorCount>0) { 
        var_dump($prices);
        return;
    }
    $wpdb->query("delete from `$table_name`");
    foreach ($prices->Prices->Item as $price) { 
        $rows_affected=$wpdb->insert($table_name,array('costkey'=>$price->Key,'costvalue'=>$price->Value,'costcurrency'=>$prices->Currency));
    }
}
// Query the API to update the local copy of the TLD info database
function hwc_reseller_integration_update_tldinfo() { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."tldinfo";
    $tlds=hwc_reseller_integration_api_query('TLDINFOLIST');
    if ($tlds->ErrorCount>0) { 
        var_dump($tlds);
        return;
    }
    $wpdb->query("DELETE FROM `$table_name`");
    foreach ($tlds->TLDs->Item as $tld) { 
        $rows_affected = $wpdb->insert ($table_name, array(    'TLD'=>$tld->TLD,
                                    'Currency'=>$tld->Currency,
                                    'RegistrationSalePrice'=>$tld->RegistrationSalePrice,
                                    'TransferSalePrice'=>$tld->TransferSalePrice,
                                    'RenewalSalePrice'=>$tld->RenewalSalePrice,
                                    'Lockable'=>$tld->Lockable,
                                    'RealTime'=>$tld->RealTime,
                                    'Transferable'=>$tld->Transferable,
                                    'AuthInfo'=>$tld->AuthInfo,
                                    'AutoTrans'=>$tld->AutoTrans,
                                    'FaxTrans'=>$tld->FaxTrans,
                                    'Protocol'=>$tld->Protocol,
                                    'MinPeriod'=>$tld->MinPeriod,
                                    'MaxPeriod'=>$tld->MaxPeriod));
    }
    $dtlds=hwc_reseller_integration_api_query('DEFAULTTLDLIST');
    foreach ($dtlds->TLDs->Item as $tld) { 
        $wpdb->query("UPDATE `$table_name` SET `Default`='true' WHERE TLD='".$wpdb->escape($tld->TLD)."' LIMIT 1");
        if ($tld->Checked=='true') { 
            $wpdb->query("UPDATE `$table_name` SET `Checked`='true' WHERE TLD='".$wpdb->escape($tld->TLD)."' LIMIT 1");
        }
    }
}
// Query the API to update the local copy of the currency database
function hwc_reseller_integration_update_currencies() { 
    hwc_reseller_integration_update_countries();
    hwc_reseller_integration_update_us_states();
    global $wpdb;
    global $hwcreseller_table_prefix;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."currencies";
    $currencies=hwc_reseller_integration_api_query('CURRENCYLIST');
    if ($currencies->ErrorCount>0) { 
        var_dump($currencies);
        return;
    }
    $wpdb->query("DELETE FROM `$table_name`");
    foreach ($currencies->Currencies->Item as $currency) { 
        $rows_affected = $wpdb->insert( $table_name, array(     'alpha3' => $currency->Alpha3, 
                                    'name' => $currency->Name, 
                                    'symbol' => $currency->Symbol,
                                           'symbolbefore' => $currency->SymbolBefore,
                                    'smallunitmultiplier' => $currency->SmallUnitMultiplier,
                                    'smallunitsymbol' => $currency->SmallUnitSymbol,
                                    'smallunitsymbolbefore' => $currency->SmallUnitSymbolBefore,
                                    'rate' => $currency->Rate
                                ) );
    }    
}

// Query the API to update the local copy of the accounttypes database
function hwc_reseller_integration_update_accounttypes() { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."accounttypes";
    $accounttypes=hwc_reseller_integration_api_query('ACCOUNTTYPEINFOLIST');
    if ($accounttypes->ErrorCount>0) {
        var_dump($accounttypes);
        return;
    }
    $wpdb->query("DELETE FROM `$table_name`");
    foreach ($accounttypes->AccountTypes->Item as $accounttype) { 
        if (@$accounttype->Hidden=='true') { 
            continue;
        }
        $rows_affected = $wpdb->insert( $table_name, array(    'AccountTypeID'=>$accounttype->AccountTypeID,
                                    'Name'=>$accounttype->Name,
                                    'EmailAccounts'=>$accounttype->EmailAccounts,
                                    'Databases'=>$accounttype->Databases,
                                    'ComputeUnits'=>$accounttype->ComputeUnits,
                                    'DiskQuota'=>$accounttype->DiskQuota,
                                    'FTPAccounts'=>$accounttype->FTPAccounts,
                                    'Websites'=>$accounttype->Websites,
                                    'TransferQuota'=>$accounttype->TransferQuota,
                                    'MonthlySalePrice'=>$accounttype->MonthlySalePrice,
                                    'YearlySalePrice'=>$accounttype->YearlySalePrice,
                                    'Currency'=>$accounttypes->Currency
                                ) );

    }

}

// Add the options menu item
function hwc_reseller_integration_plugin_menu() {
    add_options_page(__('Integration Options','hwcreseller'), __('HWC Reseller Integration','hwcreseller'), 'manage_options', 'hwcreseller', 'hwc_reseller_integration_plugin_options');
}

// Display the admin console options page
function hwc_reseller_integration_plugin_options() {
    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    ?><div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div><h2><?=__('Hybrid Web Cluster - Reseller Integration Settings','hwcreseller');?></h2>
    <form method="post" action="options.php">
    <?php wp_nonce_field('update-options'); ?>
    <p><?=__('Use this page to configure access to the Hybrid Web Cluster.','hwcreseller');?></p>
    <table class="form-table">
    <tr valign="top"><th scope="row"><?=__('POST URL','hwcreseller');?></th><td><input class="regular-text" type="text" name="HWCPOSTURL" value="<?=get_option('HWCPOSTURL');?>" /></td></tr>
    <tr valign="top"><th scope="row"><?=__('Reseller Username','hwcreseller');?></th><td><input type="text" name="HWCReseller" value="<?=get_option('HWCReseller');?>" /></td></tr>
    <tr valign="top"><th scope="row"><?=__('API Key','hwcreseller');?></th><td><input class="regular-text" type="text" name="HWCAPIKey" value="<?=get_option('HWCAPIKey');?>" size="60" /></td></tr>
    <tr valign="top"><th scope="row"><?=__('Domains Page','hwcreseller');?></th><td><?php wp_dropdown_pages(array('name'=>'HWCDomainPageID','selected'=>get_option('HWCDomainPageID'))); ?></td></tr>
    <tr valign="top"><th scope="row"><?=__('Hosting Packages Page','hwcreseller');?></th><td><?php wp_dropdown_pages(array('name'=>'HWCHostingPageID','selected'=>get_option('HWCHostingPageID'))); ?></td></tr>
    <tr valign="top"><th scope="row"><?=__('Complete Page','hwcreseller');?></th><td><?php wp_dropdown_pages(array('name'=>'HWCCompletePageID','selected'=>get_option('HWCCompletePageID')));?></td></tr>
    <tr valign="top"><th scope="row"><?=__('AJAX Page','hwcreseller');?></th><td><?php wp_dropdown_pages(array('name'=>'HWCAjaxPageID','selected'=>get_option('HWCAjaxPageID')));?></td></tr>
    <tr valign="top"><th scope="row"><?=__('Signup Payments','hwcreseller');?></th><td><input type="radio" name="HWCSignupPayments" value="enabled" id="HWCSignupPaymentsEnabled"<?php if (get_option('HWCSignupPayments')=='enabled') { echo ' checked="checked"'; } ?>> <label for="HWCSignupPaymentsEnabled"><?=__('Enabled','hwcreseller');?></label> &nbsp; <input type="radio" name="HWCSignupPayments" value="disabled" id="HWCSignupPaymentsDisabled"<?php if (get_option('HWCSignupPayments')=='disabled') { echo ' checked="checked"'; } ?>> <label for="HWCSignupPaymentsDisabled"><?=__('Disabled','hwcreseller');?></label></td></tr>
    <tr valign="top"><th scope="row"><?=__('Yearly Accounts','hwcreseller');?></th><td><input type="radio" name="HWCYearlyAccounts" value="enabled" id="HWCYearlyAccountsEnabled"<?php if (get_option('HWCYearlyAccounts')=='enabled') { echo ' checked="checked"'; } ?>> <label for="HWCYearlyAccountsEnabled"><?=__('Enabled','hwcreseller');?></label> &nbsp; <input type="radio" name="HWCYearlyAccounts" value="disabled" id="HWCYearlyAccountsDisabled"<?php if (get_option('HWCYearlyAccounts')=='disabled') { echo ' checked="checked"'; } ?>> <label for="HWCYearlyAccountsDisabled"><?=__('Disabled','hwcreseller');?></label></td></tr>
    <tr valign="top"><th scope="row"><?=__('Collect VAT numbers for EU countries','hwcreseller');?></th><td><input type="radio" name="HWCCollectVAT" value="enabled" id="HWCCollectVATEnabled"<?php if (get_option('HWCCollectVAT')=='enabled') { echo ' checked="checked"'; } ?>> <label for="HWCCollectVATEnabled"><?=__('Enabled','hwcreseller');?></label> &nbsp; <input type="radio" name="HWCCollectVAT" value="disabled" id="HWCCollectVATDisabled"<?php if (get_option('HWCCollectVAT')=='disabled') { echo ' checked="checked"'; } ?>> <label for="HWCCollectVATDisabled"><?=__('Disabled','hwcreseller');?></label></td></tr>
    <tr valign="top"><th scope="row"><?=__('Use HTTPS for completion process','hwcreseller');?></th><td><input type="radio" name="HWCSecureComplete" value="enabled" id="HWCSecureCompleteEnabled"<?php if (get_option('HWCSecureComplete')=='enabled') { echo ' checked="checked"'; } ?>> <label for="HWCSecureCompleteEnabled"><?=__('Enabled','hwcreseller');?></label> &nbsp; <input type="radio" name="HWCSecureComplete" value="disabled" id="HWCSecureCompleteDisabled"<?php if (get_option('HWCSecureComplete')=='disabled') { echo ' checked="checked"'; } ?>> <label for="HWCSecureCompleteDisabled"><?=__('Disabled','hwcreseller');?></label></td></tr>
    <tr valign="top"><th scope="row"><?=__('Choose GeoIP database version','hwcreseller');?></th><td><input type="radio" name="HWCGeoIP" value="country" id="HWCGeoIPCountry"<?php if (get_option('HWCGeoIP')=='country') { echo ' checked="checked"'; } ?>> <label for="HWCGeoIPCountry"><?=__('Country','hwcreseller');?></label> &nbsp; <input type="radio" name="HWCGeoIP" value="city" id="HWCGeoIPCity"<?php if (get_option('HWCGeoIP')=='city') { echo ' checked="checked"'; } ?>> <label for="HWCGeoIPCity"><?=__('City','hwcreseller');?></label> &nbsp; <input type="radio" name="HWCGeoIP" value="region" id="HWCGeoIPRegion"<?php if (get_option('HWCGeoIP')=='region') { echo ' checked="checked"'; } ?>> <label for="HWCGeoIPRegion"><?=__('Region','hwcreseller');?></label></td></tr>
    </table>
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="HWCReseller,HWCAPIKey,HWCPOSTURL,HWCDomainPageID,HWCHostingPageID,HWCCompletePageID,HWCAjaxPageID,HWCSignupPayments,HWCYearlyAccounts,HWCCollectVAT,HWCSecureComplete,HWCGeoIP" />
    <p class="submit">
    <input type="submit" class="button-primary" value="<?=__('Save Changes / sync with cluster','hwcreseller');?>" /></p></form>

    <?php
    if($_REQUEST['settings-updated']=='true' || $_REQUEST['action'] == 'update') {
        hwc_reseller_integration_update_currencies();
        print "<p>".__('Updated Currencies from cluster.','hwcreseller')."</p>";
        hwc_reseller_integration_update_accounttypes();
        print "<p>".__('Updated Account Types from cluster.','hwcreseller')."</p>";
        hwc_reseller_integration_update_tldinfo();
        print "<p>".__('Updated TLDs from cluster.','hwcreseller')."</p>";
        hwc_reseller_integration_update_resource_prices();
        print "<p>".__('Updated resource prices from cluster.','hwcreseller')."</p>";
    }

    ?>
    </div>
    <?


}

// This function sends a query via the API
function hwc_reseller_integration_api_query($command='',$args=array()) {
    $args['Reseller']=get_option('HWCReseller');
    $args['APIKey']=get_option('HWCAPIKey');
    $args['ResponseFormat']='xml';
    $args['Command']=$command;
    $args['APIVersion']='1.0';
    foreach ($args as &$arg) { 
        $arg=(string)$arg;    
    }
    $querystring=http_build_query($args);
    $context=stream_context_create(array(
        'http'=>array(
            'method'=>'POST',
            'header'=>"Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($querystring)."\r\n",
            'content'=>$querystring
        )
    ));
    if(strlen(get_option('HWCPOSTURL'))==0) {
        return null;
    }
    $result=file_get_contents(get_option('HWCPOSTURL'),false,$context);
    $ret=simplexml_load_string($result);
    if (!$ret) {
        /*
        ?><hr><?
        print htmlentities($result);
        ?><hr><?
         */
        $ret=simplexml_load_string(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<APIResponse>
    <ErrorCount>1</ErrorCount>
    <Errors>
        <Item>
        <ID>0</ID>
        <Error>API XML Parse Error</Error>
        </Item>
    </Errors>
</APIResponse>
XML
);
    }
    if ($result->ErrorCount > 0) { 
        print "=== API Error === <br/>";
        var_dump($result);
    }
    return $ret;
}

// This function returns a randomly generated alphanumeric string
function hwc_reseller_integration_random_alpha_num_string($length=8,$includecaps=true) { 
    if ($includecaps) { 
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    } else {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    }
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $string;
}

// These functions return markup for a button which will add an account type to the basket
function hwc_reseller_integration_accounttype_addToBasketByNameButton($accountname,$buttoncontent=null) { 
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return hwc_reseller_integration_accounttype_addToBasketButton($account);
}
function hwc_reseller_integration_accounttype_addToBasketByIDButton($AccountTypeID,$buttoncontent=null) { 
    $account=hwc_reseller_integration_accounttype_getAccount($accountTypeID);
    return hwc_reseller_integration_accounttype_addToBasketButton($account,$buttoncontent);
}
function hwc_reseller_integration_accounttype_addToBasketButton($account,$buttoncontent=null) { 
    if (is_null($buttoncontent)) { 
        $buttoncontent=__('Add to Basket','hwcreseller');
    }
    return '<button class="hwcreseller-accounttype-addtobasketbutton" data-accounttypeid="'.$account->AccountTypeID.'">'.$buttoncontent.'</button>';
}

// Generate account type info blocks
function hwc_reseller_integration_accounttype_infoBlockByName($accountname,$extendedinfo=false,$includeprices=false) { 
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return hwc_reseller_integration_accounttype_infoBlock($account,$extendedinfo,$includeprices);
}
function hwc_reseller_integration_accounttype_infoBlockByID($AccountTypeID,$extendedinfo=false,$includeprices=false) { 
    $account=hwc_reseller_integration_accounttype_getAccount($AccountTypeID);
    return hwc_reseller_integration_accounttype_infoBlock($account,$extendedinfo,$includeprices);
}
function hwc_reseller_integration_accounttype_infoBlock($account,$extendedinfo=false,$includeprices=false) { 
    $html='';
    $html.='<ul class="hwcreseller-accounttype-infoblock">';
    if($account->ComputeUnits != 1) {
        $html.='<li><strong>'.number_format($account->ComputeUnits,0,'.',' ').' '.__('Compute<br />Units','hwcreseller').'</strong></li>';
    } else {
        $html.='<li><strong>'.$account->ComputeUnits.' '.__('Compute Units','hwcreseller').'</strong></li>';
    }
    if ($account->TransferQuota==0) { 
        $html.='<li>'.__('Unlimited Monthly Transfer','hwcreseller').'</li>';
    } else {
        $html.='<li>'.number_format($account->TransferQuota,0,'.',' ').' '.__('GB Monthly Transfer','hwcreseller').'</li>';
    }
    if ($extendedinfo) { 
        if ($account->DiskQuota==0) { 
            $html.='<li>'.__('Unlimited Disk Quota','hwcreseller').'</li>';
        } else {
            $html.='<li>'.$account->DiskQuota.' '.__('GB Disk Quota','hwcreseller').'</li>';
        }
    }
    if ($account->Websites==0) {
        $html.='<li>'.__('Unlimited Websites','hwcreseller').'</li>';
    } else if ($account->Websites>1) { 
        $html.='<li>'.$account->Websites.' '.__('Websites','hwcreseller').'</li>';
    } else {
        $html.='<li>'.$account->Websites.' '.__('Website','hwcreseller').'</li>';
    }
    if ($account->EmailAccounts==0) { 
        $html.='<li>'.__('Unlimited Email','hwcreseller').'</li>';
    } else if ($account->EmailAccounts>1) { 
        $html.='<li>'.$account->EmailAccounts.' '.__('Email Accounts','hwcreseller').'</li>';
    } else {
        $html.='<li>'.$account->EmailAccounts.' '.__('Email Account','hwcreseller').'</li>';
    }
    if ($account->Databases==0) { 
        $html.='<li>'.__('Unlimited Databases','hwcreseller').'</li>';
    } else if ($account->Databases>1) {
        $html.='<li>'.$account->Databases.' '.__('Databases','hwcreseller').'</li>';
    } else {
        $html.='<li>'.$account->Databases.' '.__('Database','hwcreseller').'</li>';
    }
    if ($extendedinfo) { 
        if ($account->FTPAccounts==0) { 
            $html.='<li>'.__('Unlimited FTP Accounts','hwcreseller').'</li>';
        } else if ($account->FTPAccounts>1) { 
            $html.='<li>'.$account->FTPAccounts.' '.__('FTP Accounts','hwcreseller').'</li>';
        } else {
            $html.='<li>'.$account->FTPAccounts.' '.__('FTP Account','hwcreseller').'</li>';
        }
    }
    if ($includeprices) { 
        $html.='<li>'.hwc_reseller_integration_accounttype_monthlySalePrice($account->AccountTypeID).__(' / Month','hwcreseller').'</li>';
        $html.='<li>'.hwc_reseller_integration_accounttype_yearlySalePrice($account->AccountTypeID).__(' / Year','hwcreseller').'</li>';
    }
    $html.="</ul>";
    return $html;
}

function hwc_reseller_integration_accounttype_infoBlockByName_wrapper($atts) {
    return hwc_reseller_integration_accounttype_infoBlockByName($atts['name'], false, false);
}

function hwc_reseller_integration_accounttype_storage_wrapper($atts) {
    $account=hwc_reseller_integration_accounttype_getAccountByName($atts['name']);
    return $account->DiskQuota;
}

function hwc_reseller_integration_accounttype_price_wrapper($atts) {
    $account=hwc_reseller_integration_accounttype_getAccountByName($atts['name']);
    if(!$atts['yearly']) {
        return hwc_reseller_integration_accounttype_monthlySalePrice($account->AccountTypeID).__(' / mo','hwcreseller');
    } else {
        return hwc_reseller_integration_accounttype_yearlySalePrice($account->AccountTypeID).__(' / yr','hwcreseller');
    }
}
function hwc_reseller_integration_set_affiliate_wrapper($atts) { 
    hwc_set_affiliate_id($atts['affiliate']);    
}
function hwc_reseller_integration_register_campaign_code_wrapper($atts) { 
    hwc_register_campaign_code($atts['code']);
}
function hwc_message($atts) {
    global $hwcreseller_message;
    return $hwcreseller_message;
}

function hwc_reseller_request_variable($atts) {
    return htmlentities($_REQUEST[$atts['var']]);
}
function hwc_reseller_shopping_feed_action() { 
    global $hwcreseller_table_prefix;
        global $wpdb;
    ob_end_clean();
    header("Content-type: text/xml");
    $title=get_bloginfo('name','raw');
    $description=get_bloginfo('description','raw');
    $link=site_url();
    $title=get_bloginfo('name','raw');
    $description=get_bloginfo('description','raw');
    $link=site_url();
    print <<<XML
<?xml version="1.0" encoding="UTF-8" ?>

<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">

<channel>
    <title>{$title}</title>
    <description>{$description}</description>
    <link>{$link}</link>
XML;
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."accounttypes";
    $packages = $wpdb->get_results("select * from `$table_name`", OBJECT);
    foreach ($packages as $package) { 
        // TODO: Fill this in properly!
        print <<<XML
<item>
  <title>{$package->Name}</title> 
  <link>http://www.example.com/electronics/tv/LGM2262D.html</link> 
  <description>A {$package->Name} web hosting package</description> 
  <g:id>TV_123456</g:id> 
  <g:condition>used</g:condition> 
  <g:price>159.00 USD</g:price> 
  <g:availability>in stock</g:availability> 
  <g:image_link>http://images.example.com/TV_123456.png</g:image_link> 
  <g:shipping>
  <g:country>US</g:country> 
  <g:service>Standard</g:service> 
  <g:price>14.95 USD</g:price> 
  </g:shipping>
  <g:gtin>8808992787426</g:gtin> 
  <g:brand>LG</g:brand> 
  <g:mpn>M2262D-PC</g:mpn> 
  <g:product_type>Consumer Electronics > TVs > Flat Panel TVs</g:product_type> 
</item>

XML;
    }
    print <<<XML
</channel>
</rss>
XML;
    die;
}
function hwc_reseller_ajax_action() {
    ob_end_clean();
    header("Content-type: application/json");
    if($_REQUEST['action']=='check_domain') {
        $available = hwc_check_domain_availability($_REQUEST['domain']);
        print json_encode(array($_REQUEST['domain'], $available));
    }
    die;
}
function hwc_reseller_integration_resource_prices() { 
    global $hwcreseller_session;
    ob_start();
    ?>
    <table border="0">
    <tr><th scope="row"><?=__('Data transfer:','hwcreseller');?></th><td><?php echo hwc_reseller_integration_costKeySalePrice('monthlybandwidth',false,true);?> <?=__('/ GB','hwcreseller');?></td></tr>
    <tr><th scope="row"><?=__('Web Space:','hwcreseller');?></th><td><?php echo hwc_reseller_integration_costKeySalePrice('monthlydisk',false,true);?> <?=__('/ GB','hwcreseller');?></td></tr>
    <tr><th scope="row"><?=__('HCUs:','hwcreseller');?></th><td><?php echo hwc_reseller_integration_costKeySalePrice('monthlycompute',false,true);?> <?=__('/ HCU','hwcreseller');?></td></tr>
    <tr><th scope="row"><?=__('Websites:','hwcreseller');?></th><td><?php echo hwc_reseller_integration_costKeySalePrice('monthlywebsite',false,true);?> <?=__('/ Website','hwcreseller');?></td></tr>
    <tr><th scope="row"><?=__('Databases:','hwcreseller');?></th><td><?php echo hwc_reseller_integration_costKeySalePrice('monthlydbase',false,true);?> <?=__('/ Database','hwcreseller');?></td></tr>
    <tr><th scope="row"><?=__('Email Accounts:','hwcreseller');?></th><td><?php echo hwc_reseller_integration_costKeySalePrice('monthlyemail',false,true);?> <?=__('/ Email Account','hwcreseller');?></td></tr>
    </table>
    <?php
    return ob_get_clean();
}
function hwc_reseller_integration_custom_package_sliders() { 
    global $hwcreseller_session;
    $symbol=hwc_reseller_integration_currency_getCurrencySymbol($hwcreseller_session->Currency);
    ob_start();
?>
    <div class="hwcreseller_custom_package_sliders">
    <div class="hwcreseller_custom_package_sliders_total hwcreseller_custom_package_sliders_top_total">
    <div class="hwcreseller_custom_package_sliders_total_inner ui-corner-all">
    <label for="hwcreseller_top_total_text"><?php echo __('Total:','hwcreseller');?></label><input type="text" class="hwcreseller_top_total_text" id="hwcreseller_top_total_text" readonly="readonly">
    </div>
    </div>
    <input type="hidden" id="hwcreseller_currencysymbol" value="<?php echo htmlentities($symbol['symbol'],ENT_COMPAT,'UTF-8');?>">
    <input type="hidden" id="hwcreseller_currencysymbolbefore" value="<?php echo $symbol['symbolbefore'];?>">
    <input type="hidden" id="hwcreseller_monthlybandwidth" value="<?php echo hwc_reseller_integration_costKeySalePrice('monthlybandwidth',true);?>">
    <input type="hidden" id="hwcreseller_monthlydisk" value="<?php echo hwc_reseller_integration_costKeySalePrice('monthlydisk',true);?>">
    <input type="hidden" id="hwcreseller_monthlycompute" value="<?php echo hwc_reseller_integration_costKeySalePrice('monthlycompute',true);?>">
    <input type="hidden" id="hwcreseller_monthlywebsite" value="<?php echo hwc_reseller_integration_costKeySalePrice('monthlywebsite',true);?>">
    <input type="hidden" id="hwcreseller_monthlydbase" value="<?php echo hwc_reseller_integration_costKeySalePrice('monthlydbase',true);?>">
    <input type="hidden" id="hwcreseller_monthlyemail" value="<?php echo hwc_reseller_integration_costKeySalePrice('monthlyemail',true);?>">
    <input type="hidden" id="hwcreseller_monthlybandwidth_price">
    <input type="hidden" id="hwcreseller_monthlydisk_price">
    <input type="hidden" id="hwcreseller_monthlycompute_price">
    <input type="hidden" id="hwcreseller_monthlywebsite_price">
    <input type="hidden" id="hwcreseller_monthlydbase_price">
    <input type="hidden" id="hwcreseller_monthlyemail_price">
    <p class="hwcreseller_monthlybandwidth_label"><label for="hwcreseller_monthly_bandwidth_text"><?php echo __('Data Transfer:','hwcreseller');?></label><input type="text" class="hwcreseller_monthlybandwidth_text" readonly="readonly" id="hwcreseller_monthly_bandwidth_text"></p><div class="hwcreseller_monthlybandwidth_slider"></div>
    <p class="hwcreseller_monthlydisk_label"><label for="hwcreseller_monthly_disk_text"><?php echo __('Disk Quota:','hwcreseller');?></label><input type="text" class="hwcreseller_monthlydisk_text" readonly="readonly" id="hwcreseller_monthly_disk_text"></p><div class="hwcreseller_monthlydisk_slider"></div>
    <p class="hwcreseller_monthlycompute_label"><label for="hwcreseller_monthly_compute_text"><?php echo __('Compute Units:','hwcreseller');?></label><input type="text" class="hwcreseller_monthlycompute_text" readonly="readonly" id="hwcreseller_monthly_compute_text"></p><div class="hwcreseller_monthlycompute_slider"></div>
    <p class="hwcreseller_monthlywebsite_label"><label for="hwcreseller_monthly_website_text"><?php echo __('Websites:','hwcreseller');?></label><input type="text" class="hwcreseller_monthlywebsite_text" readonly="readonly" id="hwcreseller_monthly_website_text"></p><div class="hwcreseller_monthlywebsite_slider"></div>
    <p class="hwcreseller_monthlydbase_label"><label for="hwcreseller_monthly_dbase_text"><?php echo __('Databases:','hwcreseller');?></label><input type="text" class="hwcreseller_monthlydbase_text" readonly="readonly" id="hwcreseller_monthly_dbase_text"></p><div class="hwcreseller_monthlydbase_slider"></div>
    <p class="hwcreseller_monthlyemail_label"><label for="hwcreseller_monthly_email_text"><?php echo __('Email Accounts:','hwcreseller');?></label><input type="text" class="hwcreseller_monthlyemail_text" readonly="readonly" id="hwcreseller_monthly_email_text"></p><div class="hwcreseller_monthlyemail_slider"></div>
    <div class="hwcreseller_custom_package_sliders_total hwcreseller_custom_package_sliders_bottom_total">
    <div class="hwcreseller_custom_package_sliders_total_inner ui-corner-all">
    <label for="hwcreseller_bottom_total_text"><?php echo __('Total:','hwcreseller');?></label><input type="text" class="hwcreseller_bottom_total_text" id="hwcreseller_bottom_total_text" readonly="readonly">
    </div>
    </div>
    </div>
    <?php    
    return ob_get_clean();
}
function hwc_reseller_integration_order_widget() {
    global $hwcreseller_session;
    ob_start();
    $got_package = false;
    $got_domain = false;
    $total = 0;
    ?>
    <div class="inner" style="clear: left;">
        <table border="0" style="padding-top:10px; padding-bottom:10px;">
        <?

    ?><tr><td colspan="2"><strong><a href="<?php echo hwc_reseller_hosting_permalink();?>"><?=__('Package choice:','hwcreseller');?></a></strong></td></tr><?
        if (!is_null($hwcreseller_session->AccountTypeID)) {
            $got_package = true;
        $account = hwc_reseller_integration_accounttype_getAccount($hwcreseller_session->AccountTypeID);
        $trialperiodtext='';
        if (get_option('HWCSignupPayments')!='enabled') { 
            $trialperiodtext=__(' with 48 hour free trial','hwcreseller');
        }
        $periodicitytext='';
        $salepricetype='monthly';
        if (get_option('HWCYearlyAccounts')=='enabled') { 
        if ($hwcreseller_session->Periodicity=='monthly') { 
            $periodicitytext=__('Monthly ','hwcreseller');
        } else {
            $salepricetype='yearly';
            $periodicitytext=__('Yearly ','hwcreseller');
        }
        }
        ?><tr><td><span><?=$periodicitytext;?><?=$account->Name?> <?=__('Cloud Hosting Account','hwcreseller')?><?=$trialperiodtext;?></span></td><td>
<?
        if ($salepricetype=='monthly'){ 
            $value = hwc_reseller_integration_accounttype_monthlySalePrice($hwcreseller_session->AccountTypeID, true);
        } else{
            $value = hwc_reseller_integration_accounttype_yearlySalePrice($hwcreseller_session->AccountTypeID, true);
        }
            $total += $value;
            ?>
        <?php if ($salepricetype=='monthly') { echo hwc_reseller_integration_accounttype_monthlySalePrice($hwcreseller_session->AccountTypeID); }else{ echo hwc_reseller_integration_accounttype_yearlySalePrice($hwcreseller_session->AccountTypeID); } ?><br />(<a href="<?php echo hwc_reseller_hosting_permalink();?>">change</a>)
            </td></tr>
            <?
        } else {
        ?><tr><td colspan="2"><?=__('In progress...','hwcreseller');?></td></tr><?
        }
        ?><?

        if($hwcreseller_session->Domains && sizeof($hwcreseller_session->Domains) > 0) {
            $got_domain = true;
        ?><tr><td colspan="2"><br /><strong><a href="<?php hwc_reseller_domains_permalink();?>"><?=__('Domain choice','hwcreseller');?></a></strong></td></tr>
            <?
            foreach($hwcreseller_session->Domains as $domain) {
                $fragments = explode(".", $domain->DomainName); array_shift($fragments); $tld = implode(".", $fragments);
                ?><tr><td><span><?=$domain->Type=='registration'?"{$domain->NumYears} year ":''?> <?=$domain->Type=='registration'?'registration of ':'Transfer of'?> <em><?=$domain->DomainName?></em></span></td><td>
                <?
                if($domain->Type=='registration') {
                    $value = hwc_reseller_integration_tldinfo_registrationSalePrice($tld, true,$domain->NumYears);
                    echo hwc_reseller_integration_tldinfo_registrationSalePrice($tld,false,$domain->NumYears);
                } else {
                    $value = hwc_reseller_integration_tldinfo_transferSalePrice($tld, true,$domain->NumYears);
                    echo hwc_reseller_integration_tldinfo_transferSalePrice($tld,false,$domain->NumYears);
                }
                $total += $value;
        ?><br />(<a href="<?php echo hwc_reseller_domains_permalink();?>?delete_domain=<?=$domain->DomainName?>"><?=__('delete','hwcreseller');?></a>)</td><?
                ?></tr><?
            }
        } elseif($hwcreseller_session->NoDomain) {
            $got_domain = true;
        ?><tr><td colspan="2"><br /><strong><a href="<?php echo hwc_reseller_domains_permalink();?>"><?=__('Domain choice:','hwcreseller');?></a></strong></td></tr>
            <td><?=__('No domain','hwcreseller');?></td><td>(<a href="<?php echo hwc_reseller_domains_permalink();?>"><?=__('add some','hwcreseller');?></a>)</td></tr><?
        } else {
        ?><tr><td><br /><strong><a href="<?php echo hwc_reseller_domains_permalink();?>"><?=__('Domain choice:','hwcreseller');?></a></strong></td></tr>
        <tr><td><?=__('In progress...','hwcreseller');?></td></tr><?
        }

        if($got_domain && $got_package) {
        ?><tr><td><br /><strong><?=__('Pre-tax total:','hwcreseller');?></strong></td><td><br /><?
                echo hwc_reseller_integration_currency_format($hwcreseller_session->Currency, $total);
            ?></td></tr><?
        }

        if($got_domain && $got_package) {
        ?><tr><td><br /><strong><a href="<?php echo hwc_reseller_complete_permalink();?>"><?=__('User details:','hwcreseller');?></a></strong></td></tr><?
        ?><tr><td><?=__('In progress...','hwcreseller');?></td></tr><?
        } else {
        ?><tr><td><br /><strong><?=__('User details:','hwcreseller');?></strong></td></tr><?
        ?><tr><td><?=__('Pending.','hwcreseller');?></td></tr><?
        }
        ?>
        </table>
    </div>
    <?
    return ob_get_clean();
}
function hwc_reseller_hosting_permalink() { 
    $url_endpoint = get_permalink(get_option('HWCHostingPageID'));
    $url_endpoint = parse_url( $url_endpoint );
    return $url_endpoint['path'];
    //return get_permalink(get_option('HWCHostingPageID'));
}
function hwc_reseller_domains_permalink() { 
    $url_endpoint = get_permalink(get_option('HWCDomainPageID'));
    $url_endpoint = parse_url( $url_endpoint );
    return $url_endpoint['path'];
    //return get_permalink(get_option('HWCDomainPageID'));
} 
function hwc_reseller_complete_permalink() { 
    $url_endpoint = get_permalink(get_option('HWCCompletePageID'));
    $url_endpoint = parse_url( $url_endpoint );
    return $url_endpoint['path'];
    //return get_permalink(get_option('HWCCompletePageID'));    
}
function hwc_reseller_secure_complete_permalink() {
    $url_endpoint = get_permalink(get_option('HWCCompletePageID'));
    return str_replace("http://","https://",$url_endpoint);
}
function hwc_reseller_ajax_permalink() { 
    $url_endpoint = get_permalink(get_option('HWCAjaxPageID'));
    $url_endpoint = parse_url( $url_endpoint );
    return $url_endpoint['path'];
    //return get_permalink(get_option('HWCAjaxPageID'));
}
function hwc_reseller_hosting_action() {
    global $hwcreseller_session;
    global $hwcreseller_basketid;
    if($_REQUEST['add_hosting']) {
        if($hwcreseller_session->Domains && sizeof($hwcreseller_session->Domains) > 0) {
            wp_redirect(hwc_reseller_complete_permalink()); exit;
        } else {
            wp_redirect(hwc_reseller_domains_permalink()); exit;
        }
    } elseif($_REQUEST['remove_hosting']) {
        hwc_set_account_type(false);
    }
}

function hwc_reseller_domains_action() {
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_basketid;
    global $hwcreseller_message;
    ?><img src="/wp-content/plugins/hybrid-web-cluster-reseller-integration/loading.gif" style="display:none;"><? // preload

    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket_domains";
    if($_REQUEST['delete_domain']) {
        $wpdb->query("DELETE FROM `$table_name` WHERE DomainName='".$wpdb->escape($domain)."' AND BasketID='".$wpdb->escape($hwcreseller_basketid)."'");
        print mysql_error();
        $hwcreseller_session=hwc_get_session();
    }
    if($_REQUEST['add_domains']) {
        if(is_array($_REQUEST['domains'])) {
            foreach($_REQUEST['domains'] as $domain => $checked) {
                // Insert into domains_orders table
                if($checked == 'checked') {
                    $fragments = explode(".", $domain);
                    array_shift($fragments);
                    $tld = implode(".", $fragments);
                    $years = $_REQUEST['yearlist'][$domain];
                    if(!$years) {
            $table_name2 = $wpdb->prefix . $hwcreseller_table_prefix."tldinfo";
            $numyears = $wpdb->get_var("select MinPeriod from `$table_name2` where `TLD`='".$wpdb->escape($tld)."' LIMIT 1");
            $numyears='\''.$numyears.'\'';
                        $type = "'transfer'";
                    } else {
                        $type = "'registration'";
                        $numyears = "'".$wpdb->escape($years)."'";
                    }
                    $wpdb->query("REPLACE INTO `$table_name` SET
                            BasketID='".$wpdb->escape($hwcreseller_basketid)."',
                            DomainName='".$wpdb->escape($domain)."',
                            NumYears=$numyears,
                            `Type`=$type,
                            TLD='".$wpdb->escape($tld)."'
                            ");
                }
            }
        }

        if($_REQUEST['move_on']=='true') {
            wp_redirect(hwc_reseller_complete_permalink()); exit;
            // Otherwise the user wants to add more domains
        } else {
            // Update the session
            $hwcreseller_session=hwc_get_session();
        }
    }

    ob_start();
    $domain=trim(strtolower($_REQUEST['domain']));
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."tldinfo";
    $tlds_result = $wpdb->get_results("select * from `$table_name` where `Default`='true'", OBJECT);
    $tlds = array();
    $default_checked = array();
    $tld_min_periods=array();
    $tld_max_periods=array();
    foreach($tlds_result as $tld) {
        $tlds[] = $tld->TLD;
        $tld_min_periods[$tld->TLD]=$tld->MinPeriod;
        $tld_max_periods[$tld->TLD]=$tld->MaxPeriod;
        if($tld->Checked === 'true') {
            $default_checked[$tld->TLD] = true;
        }
    }
    if($domain) {
        $domain=str_replace("www.","",$domain);
        foreach($tlds as $tld) {
                if(strstr($domain,".$tld")){
                        $selected[]=$tld;
                        $domain=str_replace(".$tld","",$domain);
                }
        }
        if(sizeof($selected)<1) $selected=array_keys($default_checked);
        $punctuation=array("'",",",".");
        $domain=preg_replace("/[^A-Za-z0-9\- ]/","",$domain);
        if(strstr($domain," ")){
                $shrap=explode(" ",$domain);
                foreach(array("-","") as $f){
                        $domains[]=implode($f,$shrap);
                }
        } else {
                $domains[]=$domain;
        }
        foreach($domains as $domain) {
                foreach($tlds as $tld) {
                        $dlist["$domain.$tld"]=$tld;
                }
        }
        ?><form action="<?php echo hwc_reseller_domains_permalink();?>?add_domains=true" method="post" id="domform"><?
        foreach($dlist as $d => $tld) {
            ?><div id="domain_<?=$d?>" style="display:block; float:left; width:280px; font-size:12px; line-height:2;"><img src="/wp-content/plugins/hybrid-web-cluster-reseller-integration/loading.gif" style="margin-left:5px;"> <b><?=$d?></b> </div>
            <?
        }

        ?>
        </form>
        <div style="display:none;" id="domain_info">
            <div style="width:510px; height:310px; background-color:white; padding:20px;">
        <h1><?=__('About Domain Transfers','hwcreseller');?></h1>
        <p><?=__('How you transfer a domain depends on the type of domain. If additional information (such as an authorisation code) is required, <strong>you will be prompted for it in your Control Panel as soon as you pay for your account</strong>.','hwcreseller');?></p>
        <h2><?=__('.com, .net, .org, .biz and .info','hwcreseller');?></h2>
        <p><?=__('These are the most common domains and you transfer them by "unlocking" the domain at the current registrar and providing us with the domain transfer authorisation code via the Domain Transfers section of the Control Panel after you sign up.');?></p>
        <h2><?=__('.uk domains (.co.uk, org,uk, etc)','hwcreseller');?></h2>
        <p><?=__('With these domains you have to set the "IPS tag" to <strong>ENOM</strong> at the existing registrar. You should do this shortly after signing up.','hwcreseller');?></p>
            </div>
        </div>
        <script>
    dlist = <?=json_encode($dlist)?>;
    min_periods = <?=json_encode($tld_min_periods);?>;
    max_periods = <?=json_encode($tld_max_periods);?>;
        default_checked = <?=json_encode($default_checked)?>;
    <?php
$url_endpoint = get_permalink(get_option('HWCAjaxPageID'));
$url_endpoint = parse_url( $url_endpoint );
$url_endpoint = $url_endpoint['path'];

    ?>
        jQuery().ready(function() {
            jQuery.each(dlist,function(dom,tld) {
                checkDomain(dom, tld);
            });
            jQuery('#domainbox').focus();
    });
    var url_endpoint='<?=$url_endpoint;?>';
        domain_info = jQuery('#domain_info').html();
        </script>
        <div style="clear:both;"></div>
    <p><strong><?=__('Please note:','hwcreseller');?></strong> <?=__('You must be the registered owner of domains you attempt to transfer in.','hwcreseller');?> <a href="javascript:void(0);" onclick="Shadowbox.open({content:domain_info,height:350, width:550, player:'html'}); return false;"><?=__('Important Information About Domain Transfers','hwcreseller');?></a></p>
    <a href="javascript:void(0);" onclick="jQuery('#domform').attr('action','<?php echo hwc_reseller_domains_permalink();?>?add_domains=true&move_on=true'); jQuery('#domform').submit();" class="button" style="float:right; font-size:13px;"><div style="position:relative; top:2px; left:-3px;"><?=__('Add &amp; Continue','hwcreseller');?></div></a>
    <a href="javascript:void(0);" onclick="jQuery('#domform').attr('action','<?php echo hwc_reseller_domains_permalink();?>?add_domains=true&move_on=false'); jQuery('#domform').submit();" class="button" style="float:right; font-size:13px;"><div style="position:relative; top:2px; left:-9px;"><?=__('Add &amp; Find More','hwcreseller');?></div></a>
        <?
    } else {
        ?><p class="domain-go-explanation"><?=__('Then click "go" when you\'re ready.','hwcreseller');?></p><?
        if($hwcreseller_message) {
            ?><p><b><?=$hwcreseller_message?></b></p><?
        }
    ?><a href="<?php echo hwc_reseller_domains_permalink();?>?add_domains=true&move_on=true&no_domain=true" style="font-size:13px;"><div style="position:relative; top:2px;"><?=__('No Domain Thanks','hwcreseller');?></div></a>
        <?
    }
    return ob_get_clean();
}

function dump_js_errors($post, $errors) {
    // Save some errors to a javascript variable, so the UI can mark the errors
    // appropriately.
    ?>
    <script>
    js_errors = <?=json_encode($errors)?>;
    </script>
    <?
}

function hwc_reseller_complete_action() {
    global $hwcreseller_session;
    global $hwcreseller_table_prefix;
    global $wpdb;
    global $hwcreseller_basketid;

    // TODO: Add checks that the user has actually selected a hosting account (it's difficult for them to get here if they haven't)
    if (is_null($hwcreseller_session->AccountTypeID)) {
        wp_redirect(hwc_reseller_hosting_permalink()); exit;
    }

    ob_start();
    if($_POST['submission']) {
        if($_POST['submission_type'] == 'simple') {
            $check_function = "hwc_find_errors_simple";
            $error_function = "dump_js_errors";
            $_POST['username'] = $_POST['email'];
        } else {
            $check_function = "hwc_find_errors";
            $error_function = "hwc_new_user_form";
        }
        if(($errors = call_user_func($check_function, $_POST))
                && !isset($_POST['failed_card'])) {
            echo call_user_func($error_function, $_POST, $errors);
        } else {
            // Validation passed
            // So at this point we've got all the validated user data in $_POST
            // The list of domains in $hwcreseller_session->Domains
            // And the account type in $hwcreseller_session->AccounTypeID
            // Lookup country
            if (get_option('HWCSignupPayments')!='enabled' || !$errors=hwc_find_payment_errors($_POST)) { 
                if ($_POST['country']=='USA') { 
                    $_POST['county']=$_POST['state'];
                }
                if (!isset($_POST['failed_card'])) { 
                    $user=array(
                        'Username'=>$_POST['username'],
                        // (it's already been checked to see if it's available)

                        'Country'=>$_POST['country'],     // Must be alpha3
                        'AccountTypeID'=>$hwcreseller_session->AccountTypeID,
                        'Email'=>$_POST['email'],
                        'Currency'=>$hwcreseller_session->Currency, // alpha 3
                        'FirstName'=>$_POST['firstname'],
                        'LastName'=>$_POST['lastname'],
                        'Address1'=>$_POST['addr1'],
                        'Address2'=>$_POST['addr2'],
                        'Town'=>$_POST['town'],
                        'County'=>$_POST['county'],
                        'Postcode'=>$_POST['postcode'],
                        'Password'=>$_POST['password'],
                        'VatNumber'=>$_POST['vatnumber'],
                        'Phone'=>$_POST['phone'],
                        'TimezoneIdentifier'=>'UTC', // Need to guess this out based on country
                        'Language'=>'EN',
                        'TimeFormatIdentifier'=>'hh:mm t', 
                        'DateFormatIdentifier'=>'dd/mm/yy',
                        'AccountClass' => 'User',
                        'LooseRequirements' => 'true',
                    );
                    if($_POST['submission_type'] == 'simple') {
                        // We have to guess the country and currency.  Default
                        // to US if GeoIP has nothing for us.  TODO: Guess timezone.
                        $user['Currency'] = $hwcreseller_session->Currency?$hwcreseller_session->Currency:'GBP';
                        $alpha2 = $hwcreseller_session->Country?$hwcreseller_session->Country:'GB';
                        $table_name = $wpdb->prefix . $hwcreseller_table_prefix . "countries";
                        $results = $wpdb->get_results("SELECT alpha3 FROM $table_name where alpha2='".$wpdb->escape($alpha2)."'");
                        if($results) {
                            $user['Country'] = $results[0]->alpha3;
                        } else {
                            $user['Country'] = 'GBR';
                        }
                    }
                    if (!is_null($hwcreseller_session->AffiliateID)) { 
                        $user['AffiliateID']=$hwcreseller_session->AffiliateID;
                    }
                    $adduserresponse = hwc_reseller_integration_api_query('ADDUSER', $user);
                    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."basket_campaigncodes";
                    $codes=$wpdb->get_results("SELECT campaign_code FROM $table_name where BasketID='".$wpdb->escape($hwcreseller_basketid)."'",OBJECT);
                    $ucodearray=array();
                    foreach ($codes as $code) {
                        if (!in_array($code->campaign_code,$ucodearray)) { 
                            $query=array(    'UserID'=>$adduserresponse->UserID,
                                    'CampaignCode'=>$code->campaign_code);
                            $coderesponse=hwc_reseller_integration_api_query('REGISTERUSERCAMPAIGNCODE',$query);
                            $ucodearray[]=$code->campaign_code;
                        }
                    }
                    /* is this even needed?
                    $order = array(
                        'UserID'=> $adduserresponse->UserID,
                        'Currency'=>$hwcreseller_session->Currency
                    );
                     */
                    $orderresponse = hwc_reseller_integration_api_query('RAISEORDER',array('Currency'=>$hwcreseller_session->Currency,'UserID'=>(string)$adduserresponse->UserID));
                    $orderid=(string)$orderresponse->OrderID;
                    hwc_reseller_integration_api_query('ORDERADDHOSTING',array('OrderID'=>$orderid,"AccountTypeID" => $hwcreseller_session->AccountTypeID,'Periodicity'=>$hwcreseller_session->Periodicity,'InitialPeriod'=>'1'));
                    foreach ($hwcreseller_session->Domains as $domain) {
                    $tld = $domain->TLD;
                    $domainname = $domain->DomainName;

                    if($domain->Type == 'registration') {
                        $query=array('OrderID'=>$orderid,
                            'Domain'=>$domainname,
                            'Years'=>$domain->NumYears);
                        hwc_reseller_integration_api_query('ORDERADDREGISTRATION',$query);

                    } elseif($domain->Type == 'transfer') {
                        $query=array('OrderID'=>$orderid,
                            'Domain'=>$domainname,
                            'IgnoreAuthInfo'=>'true');
                        hwc_reseller_integration_api_query('ORDERADDTRANSFER',$query);
                    }

                    }

                    $postresponse = hwc_reseller_integration_api_query('POSTORDER',array('OrderID'=>$orderid));
                } else {
                    $orderid=$_POST['failed_card'];
                }
                if (get_option('HWCSignupPayments')=='enabled') { 
                    $query=array('OrderID'=>$orderid,
                        'CardHolder'=>$_POST['CardHolder'],
                        'CardNumber'=>$_POST['CardNumber'],
                        'CardType'=>$_POST['CardType'],
                        'CV2'=>$_POST['CV2'],
                        'ExpiryDateM'=>str_pad($_POST['ExpiryDateM'],2,'0',STR_PAD_LEFT),
                        'ExpiryDateY'=>str_pad($_POST['ExpiryDateY'],2,'0',STR_PAD_LEFT),
                        'StartDateM'=>str_pad($_POST['StartDateM'],2,'0',STR_PAD_LEFT),
                        'StartDateY'=>str_pad($_POST['StartDateY'],2,'0',STR_PAD_LEFT),
                        'IssueNumber'=>$_POST['IssueNumber'],
                        'Surname'=>$_POST['lastname'],
                        'Firstnames'=>$_POST['firstname'],
                        'Address1'=>$_POST['addr1'],
                        'Address2'=>$_POST['addr2'],
                        'City'=>$_POST['town'],
                        'County'=>$_POST['county'],
                        'Postcode'=>$_POST['postcode'],
                        'Country'=>$_POST['country'],
                        );
                    $payresponse=hwc_reseller_integration_api_query('PAYORDER',$query);
                    if ($payresponse->Status!='OK') { 
                        if (strlen($payresponse->StatusDetail)>0) { 

                            $errors=array('CardNumber'=>$payresponse->StatusDetail);
                        } else {
                            $errors=array('CardNumber'=>$payresponse->Errors->Item[0]->Error);
                        }
                        echo hwc_payment_form($_POST,$errors,$orderid);
                        return ob_get_clean();
                    }
                }
                if (get_option('HWCSignupPayments')!='enabled') { 
            ?>
            <p><strong><?=__('Your account has been set up! Please use the following link to log into the Control Panel and get started!','hwcreseller');?></strong></p>
        <?php } else { ?>
            <p><strong><?=__('Your account has been set up! Please use the following link to log into the Control Panel and get started!','hwcreseller');?></strong></p>

            <?php }
            $urlInfo = parse_url(get_option('HWCPOSTURL'));
            $onwardLink = "{$urlInfo['scheme']}://{$urlInfo['host']}/";
            ?>
            <p><strong><a class="button" href="<?=$onwardLink?>"><?=__('Control Panel','hwcreseller');?></a></strong></p>
            <style>
            #order_process_widget, #signup_container {
                display:none;
            }
            </style>
            <?

        } else {
        echo hwc_payment_form($_POST,$errors);
        }
    }
    } else {
        echo hwc_new_user_form($_POST, $errors);
    }
    return ob_get_clean();
}
function hwc_payment_form($defaults=array(),$errors,$failedcard=false) { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_pluginfoldername;
    // This should maybe be an API method to dynamically fetch the form (or the data to construct it) from the CP, so that updates to the various databases of timezones etc work properly
    ob_start();
    if (!isset($defaults['CardHolder'])) { 
        $defaults['CardHolder']=$defaults['firstname'].' '.$defaults['lastname'];
    }
    $startcardyears=array();
    $startcardyears['']='';
    for ($y=(date("Y")-10);$y<=date("Y");$y++) { 
        $startcardyears[$y-2000]=$y;
    }
    $expirycardyears=array();
    for ($y=date("Y");$y<(date("Y")+50);$y++) { 
        $expirycardyears[$y-2000]=$y;
    }
?>
    <div style="float: right; width:120px; height: 120px;" class="cardlogos"><img src="<?=WP_PLUGIN_URL.'/'.$hwcreseller_pluginfoldername.'/cardlogos.gif';?>" border="0" alt="Card Logos" width="120" height="120"></div>
<form method="post" action="<?php 
    if (get_option('HWCSecureComplete')=='enabled') { 
        echo hwc_reseller_secure_complete_permalink();
    } else {
        echo hwc_reseller_complete_permalink();
    } ?>" id="paymentform">
    <input type="hidden" name="submission" value="true" />
    <input type="hidden" name="payment_submission" value="true" />
<?php
    if ($failedcard) { 
        ?>
            <input type="hidden" name="failed_card" value="<?=$failedcard;?>" />
        <?php
    } else if (isset($_POST['failed_card'])) { 
        ?>
            <input type="hidden" name="failed_card" value="<?=$_POST['failed_card'];?>" />
            <?php
    }
?>
    <input type="hidden" name="username" value="<?=htmlentities($defaults['username'])?>">        
    <input type="hidden" name="password" value="<?=htmlentities($defaults['password']);?>">        
    <input type="hidden" name="confirm_password" value="<?=htmlentities($defaults['password']);?>">        
    <input type="hidden" name="firstname" id="el_firstname" value="<?=htmlentities($defaults['firstname'])?>">      
    <input type="hidden" name="lastname" id="el_lastname" value="<?=htmlentities($defaults['lastname'])?>">        
    <input type="hidden" name="email" id="el_email" value="<?=htmlentities($defaults['email'])?>">      
    <input type="hidden" name="addr1" id="el_addr1" value="<?=htmlentities($defaults['addr1'])?>">     
    <input type="hidden" name="addr2" id="el_addr2" value="<?=htmlentities($defaults['addr2'])?>">      
    <input type="hidden" name="town" id="el_town" value="<?=htmlentities($defaults['town'])?>">        
    <input type="hidden" name="county" id="el_county" value="<?=htmlentities($defaults['county'])?>">        
    <input type="hidden" name="postcode" id="el_postcode" value="<?=htmlentities($defaults['postcode'])?>">        
    <input type="hidden" name="phone" id="el_phone" value="<?=htmlentities($defaults['phone'])?>">      
    <input type="hidden" name="country" id="el_country" value="<?=htmlentities($defaults['country']);?>">
    <input type="hidden" value="<?=htmlentities($defaults['vatnumber'])?>" name="vatnumber" id="el_vatnumber">
<table><tr><td colspan="3">          <?=hwc_print_errors('CardHolder', $errors)?>    <p class="text" id="row_el_CardHolder">
            <label for="el_CardHolder"><span class="required-star">*</span> <?=__('Cardholder Name:','hwcreseller');?></label>
                            <input id="el_CardHolder" name="CardHolder" type="text" value="<?=htmlentities($defaults['CardHolder']);?>" />        </p>
        </td></tr><tr><td colspan="3">          <?=hwc_print_errors('CardType', $errors)?>    <p class="select" id="row_el_CardType">
            <label for="el_CardType"><span class="required-star">*</span> <?=__('Card Type:','hwcreseller');?></label>
            <select id="el_CardType" name="CardType">
            <option value="MC"<?php if ($defaults['CardType']=='MC') { echo ' selected="selected"'; } ?>>MasterCard</option>
            <option value="VISA"<?php if ($defaults['CardType']=='VISA') { echo ' selected="selected"'; }?>>Visa</option>
            <option value="UKE"<?php if ($defaults['CardType']=='UKE') { echo ' selected="selected"'; }?>>Visa Electron</option>
            <option value="DELTA"<?php if ($defaults['CardType']=='DELTA') { echo ' selected="selected"';} ?>>Visa Delta</option>
            <option value="SOLO"<?php if ($defaults['CardType']=='SOLO') { echo ' selected="selected"';} ?>>Solo</option>
            <option value="MAESTRO" <?php if ($defaults['CardType']=='MAESTRO') { echo ' selected="selected"'; }?>>Maestro</option>
</select>        </p>
        </td></tr><tr><td colspan="3">          <?=hwc_print_errors('CardNumber', $errors)?>    <p class="text" id="row_el_CardNumber">
            <label for="el_CardNumber"><span class="required-star">*</span> <?=__('Card Number:','hwcreseller');?></label>
                            <input id="el_CardNumber" name="CardNumber" type="text" value="<?=htmlentities($defaults['CardNumber']);?>" />        </p>
                            </td></tr><tr><td colspan="3"><?=hwc_print_errors('StartDate', $errors)?></td></tr><tr><td><label for="el_StartDateM"><?=__('Start Date:');?></label></td><td>              <p class="select" id="row_el_StartDateM">
            <select id="el_StartDateM" name="StartDateM">
    <option value=""></option>
    <option value="1"<?php if ($defaults['StartDateM']=='01') { echo ' selected="selected"'; } ?>>01</option>
    <option value="2"<?php if ($defaults['StartDateM']=='02') { echo ' selected="selected"'; } ?>>02</option>
    <option value="3"<?php if ($defaults['StartDateM']=='03') { echo ' selected="selected"'; } ?>>03</option>
    <option value="4"<?php if ($defaults['StartDateM']=='04') { echo ' selected="selected"'; } ?>>04</option>
    <option value="5"<?php if ($defaults['StartDateM']=='05') { echo ' selected="selected"'; } ?>>05</option>
    <option value="6"<?php if ($defaults['StartDateM']=='06') { echo ' selected="selected"'; } ?>>06</option>
    <option value="7"<?php if ($defaults['StartDateM']=='07') { echo ' selected="selected"'; } ?>>07</option>
    <option value="8"<?php if ($defaults['StartDateM']=='08') { echo ' selected="selected"'; } ?>>08</option>
    <option value="9"<?php if ($defaults['StartDateM']=='09') { echo ' selected="selected"'; } ?>>09</option>
    <option value="10"<?php if ($defaults['StartDateM']=='10') { echo ' selected="selected"'; } ?>>10</option>
    <option value="11"<?php if ($defaults['StartDateM']=='11') { echo ' selected="selected"'; } ?>>11</option>
    <option value="12"<?php if ($defaults['StartDateM']=='12') { echo ' selected="selected"'; } ?>>12</option>
</select>        </p>
        </td><td>            <p class="select" id="row_el_StartDateY">
            <select id="el_StartDateY" name="StartDateY">
<?php
    foreach ($startcardyears as $key => $val) { 
        ?>
        <option value="<?=$key;?>"<?php if ($defaults['StartDateY']==$key) { echo ' selected="selected"'; } ?>><?=$val;?></option>
        <?php
    }
?>
</select>        </p>
    </td></tr><tr><td colspan="3"><?=hwc_print_errors('ExpiryDate', $errors)?></td></tr><tr><td><label for="el_ExpiryDateM"><span class="required-star">*</span> <?=__('Expiry Date:','hwcreseller');?></label></td><td>            <p class="select" id="row_el_ExpiryDateM">
            <select id="el_ExpiryDateM" name="ExpiryDateM">
    <option value="1"<?php if ($defaults['ExpiryDateM']=='01') { echo ' selected="selected"'; } ?>>01</option>
    <option value="2"<?php if ($defaults['ExpiryDateM']=='02') { echo ' selected="selected"'; } ?>>02</option>
    <option value="3"<?php if ($defaults['ExpiryDateM']=='03') { echo ' selected="selected"'; } ?>>03</option>
    <option value="4"<?php if ($defaults['ExpiryDateM']=='04') { echo ' selected="selected"'; } ?>>04</option>
    <option value="5"<?php if ($defaults['ExpiryDateM']=='05') { echo ' selected="selected"'; } ?>>05</option>
    <option value="6"<?php if ($defaults['ExpiryDateM']=='06') { echo ' selected="selected"'; } ?>>06</option>
    <option value="7"<?php if ($defaults['ExpiryDateM']=='07') { echo ' selected="selected"'; } ?>>07</option>
    <option value="8"<?php if ($defaults['ExpiryDateM']=='08') { echo ' selected="selected"'; } ?>>08</option>
    <option value="9"<?php if ($defaults['ExpiryDateM']=='09') { echo ' selected="selected"'; } ?>>09</option>
    <option value="10"<?php if ($defaults['ExpiryDateM']=='10') { echo ' selected="selected"'; } ?>>10</option>
    <option value="11"<?php if ($defaults['ExpiryDateM']=='11') { echo ' selected="selected"'; } ?>>11</option>
    <option value="12"<?php if ($defaults['ExpiryDateM']=='12') { echo ' selected="selected"'; } ?>>12</option>
</select>        </p>
        </td><td>            <p class="select" id="row_el_ExpiryDateY">
            <select id="el_ExpiryDateY" name="ExpiryDateY">
<?php
    foreach ($expirycardyears as $key => $val) { 
        ?>
        <option value="<?=$key;?>"<?php if ($defaults['ExpiryDateY']==$key) { echo ' selected="selected"'; } ?>><?=$val;?></option>
        <?php
    }
?>
</select>        </p>
        </td></tr><tr><td colspan="3">              <?=hwc_print_errors('IssueNumber', $errors)?><p class="text" id="row_el_IssueNumber">
                            <label for="el_IssueNumber"> <?=__('Issue Number:','hwcreseller');?></label>
                            <input id="el_IssueNumber" name="IssueNumber" type="text" value="<?=htmlentities($defaults['IssueNumber']);?>" />        </p>
        </td></tr><tr><td colspan="3">          <?=hwc_print_errors('CV2', $errors)?>    <p class="text" id="row_el_CV2">
                            <label for="el_CV2"><span class="required-star">*</span> <?=__('CV2 Security Code:','hwcreseller');?></label>
                            <input size="10" id="el_CV2" name="CV2" type="text" value="<?=htmlentities($defaults['CV2']);?>" />        </p>
        </td></tr></table>

<a href="javascript:void(0);" onclick="jQuery('#paymentform').submit();" class="button" style="float:right; "><?=__('Pay','hwcreseller');?></a>
</form>

<?php
    $ret=ob_get_contents();
    ob_end_clean();
    return $ret;
}
function hwc_find_payment_errors() { 
    if (!isset($_POST['payment_submission'])) { 
        return true;
    }
    $errors=array();
    foreach (array('CardHolder','CardNumber','CV2') as $f) { 
        if (!strlen($_POST[$f])) { 
            $errors[$f]=__('This is a required field','hwcreseller');
        }
    }
    if (!strlen($_POST['ExpiryDateM']) || !strlen($_POST['ExpiryDateY'])) { 
        $errors['ExpiryDate']=__('This is a required field','hwcreseller');
    }
    return $errors;
}
function hwc_find_errors_simple() {
    $errors = array();
    foreach(array("email", "password", "confirm_password") as $f) {
        if(!strlen($_POST[$f])) {
            $errors[$f] = __("This is a required field",'hwcreseller');;
        }
    }
    if($_POST['password'] != $_POST['confirm_password']) {
        $errors['password'] = __("These must match",'hwcreseller');
    }
    if(!$errors['username'] && !hwc_check_username_availability($_POST['username'])) { 
        $errors['username'] = __("Username unavailable",'hwcreseller');
    }
    return $errors;
}
function hwc_find_errors() {
    $errors = array();
    foreach(array("username", "password", "confirm_password", "firstname", "lastname", "email", "addr1", "town", "postcode", "phone", "country") as $f) {
        if(!strlen($_POST[$f])) {
            $errors[$f] = __("This is a required field",'hwcreseller');;
        }
    }
    if($_POST['password'] != $_POST['confirm_password']) {
        $errors['password'] = __("These must match",'hwcreseller');
    }
    if(!$errors['username'] && !hwc_check_username_availability($_POST['username'])) { 
        $errors['username'] = __("Username unavailable",'hwcreseller');
    }
    return $errors;
}

function hwc_print_errors($field, $errors) {
    ob_start();
    if($errors[$field]) {
        ?><p style="color:red; margin-left:100px; font-weight:bold; display: block;">&darr; <?=$errors[$field]?> &darr;</p><?
    }
    return ob_get_clean();
}

function hwc_new_user_form($defaults=array(), $errors) {
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_session;
    ob_start();
?>
<form method="post" action="<?php 
if (get_option('HWCSecureComplete')=='enabled') { 
    echo hwc_reseller_secure_complete_permalink();
} else {
    echo hwc_reseller_complete_permalink();
} ?>" id="detailsform">
<input type="hidden" name="submission" value="true" />
        <?=hwc_print_errors('username', $errors)?>
        <div class="admin-edit-user-form"><div class="column">          <p id="row_el_username" class="username">
        <label for="el_username"><span class="required-star">*</span> <?=__('Requested Username:','hwcreseller');?></label>
                            <input type="text" name="username" id="el_username" class="username" value="<?=htmlentities($defaults['username'])?>">        </p>
        <?=hwc_print_errors('password', $errors)?>
                    <p id="row_el_password" class="password">
            <label for="el_password"><span class="required-star">*</span> <?=__('Password:','hwcreseller');?></label>
            <input type="password" class="password" name="password" id="el_password">        </p>
        <?=hwc_print_errors('confirm_password', $errors)?>
                    <p id="row_el_confirm_password" class="password">
            <label for="el_confirm_password"><span class="required-star">*</span> <?=__('Again (confirm):','hwcreseller');?></label>
            <input type="password" name="confirm_password" class="confirm_password" id="el_confirm_password">        </p>
        <?=hwc_print_errors('firstname', $errors)?>
                    <p id="row_el_firstname" class="text">
            <label for="el_firstname"><span class="required-star">*</span> <?=__('First name:','hwcreseller');?></label>
            <input type="text" name="firstname" id="el_firstname" value="<?=htmlentities($defaults['firstname'])?>">      </p>
        <?=hwc_print_errors('lastname', $errors)?>
                    <p id="row_el_lastname" class="text">
            <label for="el_lastname"><span class="required-star">*</span> <?=__('Last name:','hwcreseller');?></label>
            <input type="text" name="lastname" id="el_lastname" value="<?=htmlentities($defaults['lastname'])?>">        </p>
        <?=hwc_print_errors('email', $errors)?>
                    <p id="row_el_email" class="text">
            <label for="el_email"><span class="required-star">*</span> <?=__('E-mail address:','hwcreseller');?></label>
            <input type="text" name="email" id="el_email" value="<?=htmlentities($defaults['email'])?>">      </p>
        
        <?=hwc_print_errors('addr1', $errors)?>
</div><div class="column">          <p id="row_el_addr1" class="text">
    <label for="el_addr1"><span class="required-star">*</span> <?=__('Address 1:','hwcreseller');?></label>
            <input type="text" name="addr1" id="el_addr1" value="<?=htmlentities($defaults['addr1'])?>">      </p>
        <?=hwc_print_errors('addr2', $errors)?>
                    <p id="row_el_addr2" class="text">
            <label for="el_addr2"> <?=__('Address 2:','hwcreseller');?></label>
            <input type="text" name="addr2" id="el_addr2" value="<?=htmlentities($defaults['addr2'])?>">      </p>
        <?=hwc_print_errors('town', $errors)?>
                    <p id="row_el_town" class="text">
            <label for="el_town"><span class="required-star">*</span> <?=__('Town/City:','hwcreseller');?></label>
            <input type="text" name="town" id="el_town" value="<?=htmlentities($defaults['town'])?>">        </p>
        <?=hwc_print_errors('county', $errors)?>
                    <p id="row_el_county" class="text">
            <label for="el_county"><span class="required-star">*</span> <?=__('County/State:','hwcreseller');?></label>
        <input type="text" name="county" id="el_county" value="<?=htmlentities($defaults['county'])?>">        </p>
    <?=hwc_print_errors('state', $errors);?>
            <p id="row_el_state" class="text" style="display: none;">
            <label for="el_state"><span class="required-star">*</span> <?=__('State:','hwcreseller');?></label>
        <select name="state" id="el_state">
        <?php
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."us_states";
    $states=$wpdb->get_results("SELECT name,alpha2 FROM $table_name ORDER BY name ASC",OBJECT);
    foreach ($states as $state) {
        $selected="";
        if ($defaults['state']==$state->alpha2) {
            $selected=" selected=\"selected\"";
        }
        ?>
            <option value="<?=$state->alpha2;?>"<?=$selected;?>><?=htmlentities($state->name,ENT_COMPAT,'UTF-8');?></option>
        <?php
    }
            
            ?>
        </select>       </p>
        <?=hwc_print_errors('postcode', $errors)?>
                    <p id="row_el_postcode" class="text">
            <label for="el_postcode"><span class="required-star">*</span> <?=__('Post/Zipcode:','hwcreseller');?></label>
            <input type="text" name="postcode" id="el_postcode" value="<?=htmlentities($defaults['postcode'])?>">        </p>
        <?=hwc_print_errors('phone', $errors)?>
                    <p id="row_el_phone" class="text">
            <label for="el_phone"><span class="required-star">*</span> <?=__('Phone:','hwcreseller');?></label>
            <input type="text" name="phone" id="el_phone" value="<?=htmlentities($defaults['phone'])?>">      </p>
        <?=hwc_print_errors('country', $errors)?>
                    <p id="row_el_country" class="select">
            <label for="el_country"><span class="required-star">*</span> <?=__('Country:','hwcreseller');?></label>
        <select name="country" id="el_country">
        <?php
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."countries";
    $countries=$wpdb->get_results("SELECT name,alpha3,alpha2 FROM $table_name ORDER BY name ASC",OBJECT);
    foreach ($countries as $country) {
        $selected="";
        if ($defaults['country']==$country->alpha3 || !$defaults['country'] && $country->alpha2==$hwcreseller_session->Country) {
            $selected=" selected=\"selected\"";
        }
        ?>
            <option value="<?=$country->alpha3;?>"<?=$selected;?>><?=htmlentities($country->name,ENT_COMPAT,'UTF-8');?></option>
        <?php
    }
            
            ?>
        </select>       </p>
<script>
<?php
    if (get_option('HWCCollectVAT')=='enabled') { 
        echo 'var eucountries='.json_encode(hwc_reseller_integration_country_getEUCountries()).";\n";
        ?>
            hwcreseller_setup_collectors();
        <?php
    }
?>
</script>

        <?=hwc_print_errors('vatnumber', $errors)?>
       <p id="row_el_vatnumber" class="text" style="display: none;">
       <label for="el_vatnumber"> <?=__('VAT Number (if applicable):','hwcreseller');?></label>
            <input type="text" value="<?=htmlentities($defaults['vatnumber'])?>" name="vatnumber" id="el_vatnumber">
       </p>

</div></div>

<a href="javascript:void(0);" onclick="jQuery('#detailsform').submit();" class="button" style="float:right; font-size:13px;"><div style="position:relative; top:2px; left:-3px;"><?=__('Create Account','hwcreseller');?></div></a>
</form>
</div>
<?
    $ret=ob_get_contents();
    ob_end_clean();
    return $ret;
}

// This function gets the renewal sale price for a TLD
function hwc_reseller_integration_tldinfo_renewalSalePrice($tld,$numeric_value=false,$numyears=1) { 
    global $hwcreseller_session;
    $tld=hwc_reseller_integration_tldinfo_getTld($tld);
    return hwc_reseller_integration_currency_formatFrom($tld->Currency,$hwcreseller_session->Currency,$tld->RenewalSalePrice*$numyears,false,false,$numeric_value);
}
// This function gets the registration sale price for a TLD
function hwc_reseller_integration_tldinfo_registrationSalePrice($tld,$numeric_value=false,$numyears=1) { 
    global $hwcreseller_session;
    $tld=hwc_reseller_integration_tldinfo_getTld($tld);
    return hwc_reseller_integration_currency_formatFrom($tld->Currency,$hwcreseller_session->Currency,$tld->RegistrationSalePrice*$numyears,false,false,$numeric_value);
}
// This function gets the transfer sale price for a TLD
function hwc_reseller_integration_tldinfo_transferSalePrice($tld,$numeric_value=false,$numyears=1) { 
    global $hwcreseller_session;
    $tld=hwc_reseller_integration_tldinfo_getTld($tld);
    return hwc_reseller_integration_currency_formatFrom($tld->Currency,$hwcreseller_session->Currency,$tld->TransferSalePrice*$numyears,false,false,$numeric_value);
}

// These functions get the monthly sale price for an account type
function hwc_reseller_integration_accounttype_monthlySalePriceByName($accountname,$numeric_value=false) { 
    global $hwcreseller_session;
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return hwc_reseller_integration_currency_formatFrom($account->Currency,$hwcreseller_session->Currency,$account->MonthlySalePrice,false,false,$numeric_value);
}
function hwc_reseller_integration_accounttype_monthlySalePrice($AccountTypeID,$numeric_value=false) { 
    global $hwcreseller_session;
    $account=hwc_reseller_integration_accounttype_getAccount($AccountTypeID);
    return hwc_reseller_integration_currency_formatFrom($account->Currency,$hwcreseller_session->Currency,$account->MonthlySalePrice,false,false,$numeric_value);
}

// These functions get the yearly sale price for an account type
function hwc_reseller_integration_accounttype_yearlySalePriceByName($accountname,$numeric_value=false) { 
    global $hwcreseller_session;
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return hwc_reseller_integration_currency_formatFrom($account->Currency,$hwcreseller_session->Currency,$account->YearlySalePrice,false,false,$numeric_value);
}
function hwc_reseller_integration_accounttype_yearlySalePrice($AccountTypeID,$numeric_value=false) { 
    global $hwcreseller_session;
    $account=hwc_reseller_integration_accounttype_getAccount($AccountTypeID);
    return hwc_reseller_integration_currency_formatFrom($account->Currency,$hwcreseller_session->Currency,$account->YearlySalePrice,false,false,$numeric_value);
}

// This function returns whether a TLD requires a key for transfer
function hwc_reseller_integration_tldinfo_authInfo($tld) { 
    $tld=hwc_reseller_integration_tldinfo_getTld($tld);
    return ($tld->AuthInfo=='true') ? true : false;
}
// This function returns whether a TLD supports automated transfer
function hwc_reseller_integration_tldinfo_autoTrans($tld) { 
    $tld=hwc_reseller_integration_tldinfo_getTld($tld);
    return ($tld->AutoTrans=='true') ? true : false;
}
// This function returns true when a TLD is on the default TLDs list.
function hwc_reseller_integration_tldinfo_isDefault($tld) { 
    $tld=hwc_reseller_integration_tldinfo_getTld($tld);
    return ($tld->Default=='true') ? true : false;
}
// This function returns true when a TLD is on the default TLDs list and should also be checked by default
function hwc_reseller_integration_tldinfo_isChecked($tld) { 
    $tld=hwc_reseller_integration_tldinfo_getTld($tld);
    return ($tld->Checked=='true') ? true : false;
}

// These functions get the transfer quota in GB for an account type
function hwc_reseller_integration_accounttype_transferQuotaByName($accountname) { 
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return $account->TransferQuota;
}
function hwc_reseller_integration_accounttype_transferQuota($AccountTypeID) { 
    $account=hwc_reseller_integration_accounttype_getAccount($AccountTypeID);
    return $account->TransferQuota;
}

// These functions get the maximum number of websites for an account type
function hwc_reseller_integration_accounttype_websitesByName($accountname) { 
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return $account->Websites;
}
function hwc_reseller_integration_accounttype_websites($AccountTypeID) { 
    $account=hwc_reseller_integration_accounttype_getAccount($AccountTypeID);
    return $account->Websites;
}

// These functions get the maximum number of FTP accounts for an account type
function hwc_reseller_integration_accounttype_ftpAccountsByName($accountname) { 
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return $account->FTPAccounts;
}
function hwc_reseller_integration_accounttype_ftpAccounts($AccountTypeID) { 
    $account=hwc_reseller_integration_accounttype_getAccount($AccountTypeID);
    return $account->FTPAccounts;
}

// These functions get the disk quota in GB for an account type
function hwc_reseller_integration_accounttype_diskQuotaByName($accountname) { 
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return $account->DiskQuota;
}
function hwc_reseller_integration_accounttype_diskQuota($AccountTypeID) { 
    $account=hwc_reseller_integration_accounttype_getAccount($AccountTypeID);
    return $account->DiskQuota;
}

// These functions get the maximum number of databases for an account type
function hwc_reseller_integration_accounttype_databasesByName($accountname) { 
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return $account->Databases;
}
function hwc_reseller_integration_accounttype_databases($AccountTypeID) { 
    $account=hwc_reseller_integration_accounttype_getAccount($AccountTypeID);
    return $account->Databases;
}

// These functions get he maximum number of e-mail accounts for an account type
function hwc_reseller_integration_accounttype_emailAccountsByName($accountname) { 
    $account=hwc_reseller_integration_accounttype_getAccountByName($accountname);
    return $account->EmailAccounts;
}
function hwc_reseller_integration_accounttype_emailAccounts($AccountTypeID) { 
    $account=hwc_reseller_integration_accounttype_getAccount($AccountTypeID);
    return $account->EmailAccounts;
}

// This function returns the sale price for a costkey
function hwc_reseller_integration_costKeySalePrice($costkey,$numeric_value=false,$tosmallunits=false) {
       global $hwcreseller_session;    
    $key=hwc_reseller_integration_getCostKey($costkey);
    return hwc_reseller_integration_currency_formatFrom($key->costcurrency,$hwcreseller_session->Currency,(float)$key->costvalue,false,$tosmallunits,$numeric_value);
}



// This function converts from one currency to another and applies the appropriate symbols.
function hwc_reseller_integration_currency_formatFrom($fromcurrency,$tocurrency,$value,$fromsmallunits=false,$tosmallunits=false,$numeric_value) { 
    $result=hwc_reseller_integration_currency_convertFrom($fromcurrency,$tocurrency,$value,$fromsmallunits,$tosmallunits);
    if($numeric_value)
        return $result;
    else
        return hwc_reseller_integration_currency_format($tocurrency,$result,$tosmallunits);
}
// Gets the currency symbol
function hwc_reseller_integration_currency_getCurrencySymbol($currency) { 
    $c=hwc_reseller_integration_currency_getCurrency($currency);
    return array('symbol'=>$c->symbol,'symbolbefore'=>$c->symbolbefore);
}
// This function just wraps any number in the appropriate symbols
function hwc_reseller_integration_currency_format($currency,$value,$smallunits=false) {
    $c=hwc_reseller_integration_currency_getCurrency($currency);
    if ($smallunits) { 
        if ($c->smallunitsymbolbefore=='true') { 
            return $c->smallunitsymbol.sprintf("%01.2f",$value);
        } else {
            return sprintf("%01.2f",$value).$c->smallunitsymbol;
        }
    } else {
        if ($c->symbolbefore=='true') { 
            return $c->symbol.sprintf("%01.2f",$value);
        } else {
            return sprintf("%01.2f",$value).$c->symbol;
        }
    }    
}
// This function returns the numbers without symbols
function hwc_reseller_integration_currency_convertFrom($fromcurrency,$tocurrency,$value,$fromsmallunits=false,$tosmallunits=false) {
    $to=hwc_reseller_integration_currency_getCurrency($tocurrency);
    if ($fromcurrency==$to->alpha3 && $fromsmallunits==$tosmallunits) {
        return $value;
    }
    $from=hwc_reseller_integration_currency_getCurrency($fromcurrency);    
    if ($fromsmallunits) { 
        $value=$value / $from->smallunitmultiplier;
    }
    $basecurrencyvalue=($value * (1/$from->rate));
    $targetcurrencyvalue=$basecurrencyvalue * $to->rate;
    if ($tosmallunits) { 
        $targetcurrencyvalue=$targetcurrencyvalue * $to->smallunitmultiplier;
    }
    return $targetcurrencyvalue;
}

// This function returns a list of the countries in the EU
function hwc_reseller_integration_country_getEUCountries() { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_eucountrycache;
    if (sizeof($hwcreseller_eucountrycache)>0) { 
        return $hwcreseller_eucountrycache;
    }
    $table_name=$wpdb->prefix.$hwcreseller_table_prefix."countries";
    $result=$wpdb->get_results("select alpha3 FROM `$table_name` WHERE EUMember='true'",ARRAY_A);
    foreach ($result as $c) { 
        $hwcreseller_eucountrycache[]=$c['alpha3'];
    }
    return $hwcreseller_eucountrycache;
}

// This function caches currency lookups so we only do them once
function hwc_reseller_integration_currency_getCurrency($currency) { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_currencycache;
    if (isset($hwcreseller_currencycache[$currency])) { 
        return $hwcreseller_currencycache[$currency];
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."currencies";
    return $hwcreseller_currencycache[$currency]=$wpdb->get_row("SELECT * FROM `$table_name` WHERE alpha3='".$wpdb->escape($currency)."' LIMIT 1",OBJECT);
}
// This function caches tld lookups so we only do them once
function hwc_reseller_integration_tldinfo_getTld($tld) { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_tldcache;
    if (isset($hwcreseller_tldcache[$tld])) { 
        return $hwcreseller_tldcache[$tld];
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."tldinfo";
    return $hwcreseller_tldcache[$tld]=$wpdb->get_row("SELECT * FROM `$table_name` WHERE TLD='".$wpdb->escape($tld)."' LIMIT 1",OBJECT);
}
// This function does cost key lookups
function hwc_reseller_integration_getCostKey($costkey) { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_costkeycache;
    if (isset($hwcreseller_costkeycache[$costkey])) { 
        return $hwcreseller_costkeycache[$costkey];
    }
    $table_name=$wpdb->prefix.$hwcreseller_table_prefix."resourceprices";
    return $hwcreseller_costkeycache[$costkey]=$wpdb->get_row("SELECT * FROM `$table_name` WHERE costkey='".$wpdb->escape($costkey)."' LIMIT 1",OBJECT);
}
// This function accounttype name lookups so we only do them once
function hwc_reseller_integration_accounttype_getAccountByName($accountname) { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_accounttype_namecache;
    if (isset($hwcreseller_accounttype_namecache[$accountname])) { 
        return $hwcreseller_accounttype_namecache[$accountname];
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."accounttypes";
    return $hwcreseller_accounttype_namecache[$accountname]=$wpdb->get_row("SELECT * FROM `$table_name` WHERE Name='".$wpdb->escape($accountname)."' LIMIT 1",OBJECT);
}
// This function accounttype ID lookups so we only do them once
function hwc_reseller_integration_accounttype_getAccount($AccountTypeID) { 
    global $wpdb;
    global $hwcreseller_table_prefix;
    global $hwcreseller_accounttype_idcache;
    if (isset($hwcreseller_accounttype_idcache[$AccountTypeID])) { 
        return $hwcreseller_accounttype_idcache[$AccountTypeID];
    }
    $table_name = $wpdb->prefix . $hwcreseller_table_prefix."accounttypes";
    return $hwcreseller_accounttype_idcache[$AccountTypeID]=$wpdb->get_row("SELECT * FROM `$table_name` WHERE AccountTypeID='".$wpdb->escape($AccountTypeID)."' LIMIT 1",OBJECT);
}
