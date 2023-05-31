<?php
/* 
Plugin Name: UKM Stimuleringsmiddeladmin
Plugin URI: http://www.ukm-norge.no
Description: Plugin som benytter API til Jotform for og behandle søknader.
Author: UKM Norge / J Nordbø
Version: 1.0 
Author URI: http://jardar.net
*/

use UKMNorge\Nettverk\Administrator;
use UKMNorge\Wordpress\Modul;

require_once('UKM/Autoloader.php');
require_once('JotForm-api.php');


class UKMstimuleringsadmin extends Modul{
    public static $action = 'stimuleringsadmin';
    public static $path_plugin = null;
    public static $current_admin;


    public static function hook() {
        add_action('user_admin_menu', ['UKMstimuleringsadmin','user_meny']);
        add_action('network_admin_menu', ['UKMstimuleringsadmin','meny']);
        

    }

    /**
     * Sjekker om pålogget admin er admin på fylkesnivå
     *
     * @return void
     */
    public static function erCurrentAdminFylkeAdmin()
    {
        return static::getCurrentAdmin()->erAdmin('fylke');
    }

    /**
     * Lager et Administrator objekt på pålogget bruker
     *
     * @return void
     */
    public static function getCurrentAdmin()
    {
        if (is_null(static::$current_admin)) {
            static::$current_admin = new Administrator(get_current_user_id());
        }
        return static::$current_admin;
    }

    public static function user_meny() {
        if (static::erCurrentAdminFylkeAdmin()) {
            $userpage[] = add_menu_page(
                'UKM Norge Stimuleringsmidler', 
                'Stimuleringsmidler Administrasjon', 
                'subscriber', 
                'UKMstimuleringsadmin_user',
                ['UKMstimuleringsadmin','renderAdmin'], 
                'dashicons-archive',
                401
            );
            $userpage[] = add_submenu_page( 
                'UKMstimuleringsadmin',
                'Tilskuddssøknader',
                'Tilskuddssøknader',
                'subscriber',
                'UKMstimuleringsadmin_soknader',
                ['UKMstimuleringsadmin','renderSoknader']
            );
            foreach( $userpage as $scripthook ) {
                add_action(
                    'admin_print_styles-' . $scripthook, 
                    ['UKMstimuleringsadmin','scripts_and_styles']
                );
            }

        }
    }

    /**
     * Add menu
     */
    public static function meny() {
        $page[] = add_menu_page(
            'UKM Norge Stimuleringsmidler', 
            'Stimuleringsmidler Administrasjon', 
            'superadmin', 
            'UKMstimuleringsadmin',
            ['UKMstimuleringsadmin','renderAdmin'], 
            'dashicons-archive',
            401
        );
        $page[] = add_submenu_page( 
            'UKMstimuleringsadmin',
            'Tilskuddssøknader',
            'Tilskuddssøknader',
            'subscriber',
            'UKMstimuleringsadmin_soknader',
            ['UKMstimuleringsadmin','renderSoknader']
        );
        $page[] = add_submenu_page( 
            'UKMstimuleringsadmin',
            'Rapporter',
            'Rapporter',
            'subscriber',
            'UKMstimuleringsadmin_rapporter',
            ['UKMstimuleringsadmin','renderRapporter']
        );
        $page[] = add_submenu_page( 
            'UKMstimuleringsadmin',
            'Konfigurering',
            'Konfigurering',
            'superadmin',
            'UKMstimuleringsadmin_konfigurering',
            ['UKMstimuleringsadmin','renderKonfigurering']
        );
        foreach( $page as $scripthook ) {
            add_action( 
                'admin_print_styles-' . $scripthook, 
                ['UKMstimuleringsadmin', 'scripts_and_styles']
            );
        }
    }
    

    /**
     * Register hooks
     */
    public static function scripts_and_styles() {
        $path = str_replace('http:','https:', WP_PLUGIN_URL);
        wp_enqueue_style('UKMwp_dashboard_css');
        wp_enqueue_style('https://fonts.googleapis.com/icon?family=Material+Icons');
        wp_enqueue_style('UKMstimuleringsadminjquery_js', plugin_dir_url( __FILE__ ) .'jquery-3.6.4.min.js' );
        wp_enqueue_style('UKMstimuleringsadminbootstrap_css', plugin_dir_url( __FILE__ ) .'bootstrap.min.css' );
        wp_enqueue_script('UKMstimuleringsadminbootstrap_js', plugin_dir_url( __FILE__ ) .'bootstrap.bundle.min.js' );
        wp_enqueue_style('UKMstimuleringsadmindatatables_css', plugin_dir_url( __FILE__ ) .'datatables.min.css' );
        wp_enqueue_script('UKMstimuleringsadmindatatables_js', plugin_dir_url( __FILE__ ) .'datatables.min.js' );
        wp_enqueue_style('UKMstimuleringsadmin_css', plugin_dir_url( __FILE__ ) .'ukmstimuleringsadmin.css' );
        wp_enqueue_script('UKMstimuleringsadmin_js', plugin_dir_url( __FILE__ ) .'ukmstimuleringsadmin.js' );
    }

    /**
     * Render av soknader-action
     *
     * @return void
     */
    public static function renderSoknader() {
        static::setAction('soknader');
        return static::renderAdmin();
    }
    public static function renderRapporter() {
        static::setAction('rapporter');
        return static::renderAdmin();
    }
    public static function renderKonfigurering() {
        static::setAction('konfigurering');
        return static::renderAdmin();
    }
}

## HOOK MENU AND SCRIPTS
UKMstimuleringsadmin::init(__DIR__);
UKMstimuleringsadmin::hook();