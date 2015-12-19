<?php

/* Call the html code */
//add_action('admin_menu', 'sn_scoutnet_api_admin_menu');
/*
function sn_scoutnet_api_admin_menu() {
//global $sn_api_settings_page;
global $sn_api_group_settings_page;

//$sn_api_settings_page = add_options_page('Scoutnet API', 'Scoutnet API', 'administrator', 'scoutnet-api', 'sn_scoutnet_api_html_page');
$sn_api_group_settings_page = add_options_page('Scoutnet API group', 'Scoutnet API group', 'administrator', 'scoutnet-api-group', 'sn_scoutnet_api_group_html_page');

}


option sn_scoutnet_api -> info ivm. API + (?)enkele gegevens over de groep(?)
option sn_scoutnet_group -> beknopte info ivm. groep (info die niet dikwijls gaat wijzigen)

*/

function sn_api_load_js_and_css($hook) {

//global $sn_api_settings_page;
global $sn_api_group_settings_page;
global $sn_api_rent_settings_page;
global $sn_api_members_settings_page;
 
	//if( ( $hook == $sn_api_settings_page ) || ( $hook == $sn_api_group_settings_page ) || ( $hook == $sn_api_rent_settings_page )) {
	if( ( $hook == $sn_api_group_settings_page ) || ( $hook == $sn_api_rent_settings_page )) {

	//wp_enqueue_script('jquery');
	wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCwvKXGLUemghNxHVuYoiH8wEkoFfbVSgs&amp;sensor=false');
	wp_register_script('group.js', SN_API_PLUGIN_URL . 'js/group.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-autocomplete' ) );
	wp_enqueue_script('group.js');

	}else if ( $hook == $sn_api_members_settings_page ) {
	
	wp_enqueue_style('plugin_name-admin-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/black-tie/jquery-ui.css');
	wp_register_script('members.js', SN_API_PLUGIN_URL . 'js/members.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-autocomplete' ) );
	wp_enqueue_script('members.js');
	
	wp_register_script('json.js', SN_API_PLUGIN_URL . 'js/jquery.json-2.4.min.js' );
	wp_enqueue_script('json.js');
	wp_register_script('tablesorter.js', SN_API_PLUGIN_URL . 'js/jquery.tablesorter.min.js' );
	wp_enqueue_script('tablesorter.js');
	wp_register_script('tablesorter.pager.js', SN_API_PLUGIN_URL . 'js/jquery.tablesorter.pager.js' );
	wp_enqueue_script('tablesorter.pager.js');
	
	wp_register_style('members', SN_API_PLUGIN_URL . 'css/members.css', array(), '20140104', 'all' );
	wp_enqueue_style('members');
	
/*
<link type="text/css" rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/black-tie/jquery-ui.css" />
<script type="text/javascript" src="js/jquery-1.7.2.js"></script>
<script type="text/javascript" src="js/jquery.json-2.4.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
*/
	
	}else{
	
		return;
	}

}


function register_my_session() {
if( !session_id() ){ session_start();}
}

add_action('init', 'register_my_session');

add_action( 'admin_enqueue_scripts', 'sn_api_load_js_and_css' );

require_once dirname( __FILE__ ) . '/group.inc.php';
require_once dirname( __FILE__ ) . '/rent.inc.php';
require_once dirname( __FILE__ ) . '/members.inc.php';


$my_settings_page = new MySettingsPage();


class MySettingsPage {
	/**
	* Holds the values to be used in the fields callbacks
	*/
	private $options;
	private $pages;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
    
    global $sn_api_group_settings_page;
    global $sn_api_rent_settings_page;
    global $sn_api_members_settings_page;
    
    //$sn_api_settings_page = add_options_page('Scoutnet API', 'Scoutnet API', 'administrator', 'scoutnet-api', 'sn_scoutnet_api_html_page');
        // This page will be under "Settings"
		add_options_page('Scoutnet API', 'Scoutnet API', 'administrator', 'scoutnet-api', array( $this, 'create_admin_page' ) ); // manage_options
		$sn_api_group_settings_page = add_options_page('Scoutnet groep', 'Scoutnet groep', 'administrator', 'scoutnet-api-group', 'sn_scoutnet_api_group_html_page');
		$sn_api_rent_settings_page = add_options_page('Scoutnet verhuur', 'Scoutnet verhuur', 'administrator', 'scoutnet-api-rent', 'sn_scoutnet_api_rent_html_page');
		$sn_api_members_settings_page = add_options_page('Scoutnet leden', 'Scoutnet leden', 'administrator', 'scoutnet-api-members', 'sn_scoutnet_api_members_html_page');
		
/*
// wat als de CMS API reeds in gebruik is ?
// http://wordpress.stackexchange.com/questions/6311/how-to-check-if-an-admin-submenu-already-exists
global $submenu;
$main_menu = 'Scoutnet groep';
if (isset( $submenu[ $main_menu ] ) && in_array( 'my_submenu_slug', wp_list_pluck( $submenu[ $main_menu ], 2 ) ) ) {
    // Submenu exists.
} else {
    // Submenu doesn't exist.
}		
*/	
		
		
		
    }

	/**
	 * Options page callback
	 */
	public function create_admin_page() {

	
		// Set class property
		//$this->options = delete_option( 'my_option_name' );
		$this->options = get_option( 'sn_scoutnet_api' );
		$this->pages = get_option( 'sn_scoutnet_pages' );
		
		
		//print_r($this->options);
		
		// call get api
		
		// use caching NOT on admin site ONLY on frontend, take values from database or api
		
		// how to cron ?
		
		?>
		<div class="wrap">
			<?php //get_screen_icon(); ?>
			<h2>Scoutnet API Settings</h2>
			<?php //settings_errors(); ?>
			
			<?php
			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';
			?>
         
         <h2 class="nav-tab-wrapper">
            <a href="?page=scoutnet-api&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
            <a href="?page=scoutnet-api&tab=help" class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>">Help</a>
        </h2>
          
			<?php
			if( $active_tab == 'settings' ) {
			
			$site_url = get_site_url();
			
			?>
			<form method="post" action="options.php">
			<?php 
	            settings_fields( 'my_option_group' ); // This prints out all hidden setting fields
    	        do_settings_sections( 'scoutnet-api' );
    	        submit_button();
    	    ?>
			</form>
			De API keys en je accountid vind je op het Scoutnet control panel https://my.scoutnet.be/?api<br /><br />
			<form method="post" action="">
			
			<?php if ($this->options['accountid']){?>
			
			<?php if ($this->options['apigroupkey']){?>
			
			<?php
			
			//if (is_admin()){
	

			//}
			
			?>


			
			
			
			
			<?php }else{ ?>
			<input type="submit" name="group" value="Group API key missing" class="button" disabled="disabled" title="group API key missing" /><br /><br />
			<?php }?> 
			<?php if ($this->options['apimemberkey']){?>
			

			
			<?php }else{ ?>
			<input type="submit" name="members" value="Member API key missing" class="button" disabled="disabled" title="member API key missing" /><br /><br />
			<?php }?>
			
			
	
			<?php }else{ ?>
			<input type="submit" name="accountid" value="accountid is missing" class="button" disabled="disabled" title="accountid missing" /><br /><br />
			<?php }?>
			
			
			</form>
    	    <?php 
    	        
    	        
        	} else {
				
				$mysections = get_mysections($this->options['accountid']);
				
				if (is_array($mysections)){
					$comma_separated = implode("| ", $mysections);
					echo "<br /><br /><b>section</b> = $comma_separated";
				}else{
					echo "<div class=\"error settings-error\"><p><strong>Sections failure</strong></p></div>";
				}
				
				$mytypes = get_mytypes($this->options['accountid']);
				
				if (is_array($mytypes)){
					$comma_separated = implode("| ", $mytypes);
					echo "<br /><br /><b>type</b> = $comma_separated";
				}else{
					echo "<div class=\"error settings-error\"><p><strong>Types failure</strong></p></div>";
				}
				
            	echo "<br /><br /><b>style</b> = 1 | 2 | 3 (default = 1)";
				echo "<br /><br /><b>groupby</b> = section | type (default = section)";
				
				echo "<br /><br /><br />Toevoegen/verwijderen van \"sections\" en \"types\" doe je via https://my.scoutnet.be/?config";
        	} 
			?>
				

		</div>
		<?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {        
        register_setting(
            'my_option_group', // Option group
            'sn_scoutnet_api', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

		add_settings_section(
            'setting_section_shortcodes', // ID
            'Shortcodes',
            array( $this, 'print_section_info_shortcodes' ), 
            'scoutnet-api' // Page
        );  

		
        add_settings_section(
            'setting_section_id', // ID
            'Keys',
            array( $this, 'print_section_info_keys' ), 
            'scoutnet-api' // Page
        );
		
		
		add_settings_section(
            'options_section_shortcodes', // ID
            'Options',
            array( $this, 'print_section_info_options' ), 
            'scoutnet-api' // Page
        );  

		
        add_settings_field(
            'apigroupkey', // ID
            'Group API key', // Title 
            array( $this, 'apigroupkey_callback' ), // Callback
            'scoutnet-api', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'apimemberkey', 
            'Member API key', 
            array( $this, 'apimemberkey_callback' ), 
            'scoutnet-api', 
            'setting_section_id'
        );

        add_settings_field(
            'accountid', 
            'Accountid', 
            array( $this, 'accountid_callback' ), 
            'scoutnet-api', 
            'setting_section_id'
        );
		
		
		add_settings_field(
            'option1', 
            'Hide birthday', 
            array( $this, 'option1_callback' ), 
            'scoutnet-api', 
            'options_section_shortcodes'
        );
        
		add_settings_field(
            'option2', 
            'Hide phone numbers', 
            array( $this, 'option2_callback' ), 
            'scoutnet-api', 
            'options_section_shortcodes'
        );
        
    }

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	*/
    
    
    // TODO accountid niet aanpassen 
    
	public function sanitize( $input ) {
		$new_input = array();
		$old = get_option('sn_scoutnet_api');
		$update_option = false;
		$new_input = $old;
		
		if( isset( $input['accountid'] ) ) {
			$new_input['accountid'] = absint( $input['accountid'] );
			
		}
		
		if( isset( $input['apigroupkey'] ) ) {

			$new_input['apigroupkey'] = sanitize_text_field( $input['apigroupkey'] );
			
			if ($old['apigroupkey']!=$new_input['apigroupkey']){
				$update_option = true;
			}
			
		}

		if( isset( $input['apimemberkey'] ) ) {
			$new_input['apimemberkey'] = sanitize_text_field( $input['apimemberkey'] );
			
			if ($old['apimemberkey']!=$new_input['apimemberkey']){
				$update_option = true;
				//TODO ping member service
				
			}
			
		}
		
		
		if( isset( $input['option1'] ) ) {$new_input['option1'] = 'y';}else{$new_input['option1'] = 'n';}
		if( isset( $input['option2'] ) ) {$new_input['option2'] = 'y';}else{$new_input['option2'] = 'n';}

		if ($update_option){
		
		try{

$devkey = "jorisp@scoutnet.be";
$secret = $new_input['apigroupkey'];
$appkey = substr($secret,0,6);
$apicall = new Scoutnet_API_Call('group', $devkey, $appkey, $secret, false);
$method = 'GET';
$endpoint = "lists/";
$call = $apicall->run($endpoint, $method, null);

if ($call['decoded']['head']['status']==1){
$group = $call['decoded']['body']['data'];
$new_input['accountid'] = $group[0]['accountid'];
$new_input['groupname'] = $group[0]['groupname'];
$new_input['orgname'] = $group[0]['orgname'];
$new_input['depname'] = $group[0]['depname'];
$new_input['depmark'] = $group[0]['depmark'];
$new_input['groupID'] = $group[0]['groupID'];
}else{
add_settings_error( 'myUniqueIdentifyer', esc_attr( 'settings_updated' ), 'Is de secret key correct? Contacteer info@scoutnet.be', 'error' );
$new_input['accountid'] = null;
}

}catch(Exception $e){
add_settings_error( 'myUniqueIdentifyer', esc_attr( 'settings_updated' ), $e->getMessage(), 'error' );
}
		
		}
		
		
		return $new_input;
	}

    public function print_section_info_keys() {
        echo 'Om de shortcodes te kunnen gebruiken geef je hier de API keys:';
    }

	public function print_section_info_shortcodes() {
        echo '[scoutnet_members type="leiding" section="welpen" style="1" groupby="section"]';
    }
	
	public function print_section_info_options() {
		//echo 'wat info';
    }
	
	
    /** 
     * Get the settings option array and print one of its values
     */
    public function apigroupkey_callback() {
    
    
        printf('<input type="text" id="apigroupkey" name="sn_scoutnet_api[apigroupkey]" value="%s" />', isset( $this->options['apigroupkey'] ) ? esc_attr( $this->options['apigroupkey']) : '');
    	//$test = get_option('sn_scoutnet_api');    
        //var_dump($test);
        
        echo " (omvat de groepgegevens en de lokaalverhuur data)";
        
        
    }

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function apimemberkey_callback() {
		printf('<input type="text" id="apimemberkey" name="sn_scoutnet_api[apimemberkey]" value="%s" />',isset( $this->options['apimemberkey'] ) ? esc_attr( $this->options['apimemberkey']) : '');
		
		echo " (contacten en ledenbeheer)";
	}


	/** 
	 * Get the settings option array and print one of its values
	 */
	public function accountid_callback() {
		printf('<input type="text" id="accountid" name="sn_scoutnet_api[accountid]" value="%s" />',isset( $this->options['accountid'] ) ? esc_attr( $this->options['accountid']) : '');
		//var_dump($this->options);
		//echo " (TODO dit kan waarschijnlijk weg. Enkel voor demo SGV.)";
	}
	
	
	public function option1_callback() {
		if ($this->options['option1']=='y'){
        echo '<input type="checkbox" id="option1" name="sn_scoutnet_api[option1]" checked="checked "/>';
		}else{
		echo '<input type="checkbox" id="option1" name="sn_scoutnet_api[option1]" />';	
		}
		echo 'Show birthday only to logged-in users';
	}
	
	public function option2_callback() {
		if ($this->options['option2']=='y'){
        echo '<input type="checkbox" id="option2" name="sn_scoutnet_api[option2]" checked="checked "/>';
		}else{
		echo '<input type="checkbox" id="option2" name="sn_scoutnet_api[option2]" />';	
		}
		echo 'Show phone numbers only to logged-in users';
	}
	
	private function make_my_page($_p) {

	//global $wpdb;
	
	if (!isset($_p['post_name'])){$_p['post_name'] = 'error-page';}
	if (!isset($_p['post_title'])){$_p['post_title'] = 'Error page';}
	if (!isset($_p['post_content'])){$_p['post_content'] = 'Please contact Scoutnet';}
	
	
	unset($this->pages[$_p['post_name']]);
	update_option('sn_scoutnet_pages', $this->pages);
		
	// the slug...
	//delete_option("sn_page_{$key}_name");
	//add_option("sn_page_{$key}_name", $key, '', 'yes');
		
	// the id...
	//delete_option("sn_page_{$key}_id");
	//delete_option($this->pages[$_p['post_name']]['id']);
	$this->pages[$_p['post_name']]['id']=0;
	$this->pages[$_p['post_name']]['title']=$_p['post_title'];
	update_option('sn_scoutnet_pages', $this->pages);
	//add_option($this->pages[$_p['post_name']]['id'], '0', '', 'yes');
		
	$mypage = get_page_by_title( $_p['post_title'] );

	if ( ! $mypage ) { 

		// Default values
		$_p['post_status'] = 'publish';
		$_p['post_type'] = 'page';
		$_p['comment_status'] = 'closed';
		$_p['ping_status'] = 'closed';
		$_p['post_category'] = array(1); // the default 'Uncategorised'
		
		$new_page_template = $_p['page_template']; 
		unset($_p['page_template']);
		
		//$_p['page_template'] = 'Takken';  
		/* indien template niet bestaat gaat er blijkbaar iets fout
		 * De pagina wordt aangemaakt, maar we krijgen geen ID -> wp_insert_post() geeft geen ID
		 * De pagina koppelen aan de template doen in de theme functions.php (?)
		 */
		
		
		//Insert the post into the database
		$page_id = wp_insert_post( $_p );
		
		if(!empty($new_page_template)){
			update_post_meta($page_id, '_wp_page_template', $new_page_template);
		}

function scotts_set_nav_menu($menu_id,$location){
    $locations = get_theme_mod('nav_menu_locations');
    $locations[$location] = $menu_id;
    set_theme_mod( 'nav_menu_locations', $locations );
}
		
//echo "building menu";
$menu_name= 'footer_menu';

$locations = get_nav_menu_locations();
//var_dump($locations);

//if ( !is_nav_menu( 'footer_menu' )) {
//$run_once = get_option('menu_check');
//if (!$run_once){
    $name = 'footer_menu';
    if ( !is_nav_menu( $name )) {
    $menu_id = wp_create_nav_menu($name);
    }
    $menu = get_term_by( 'name', $name, 'nav_menu' );
 /*   wp_update_nav_menu_item($menu->term_id, 0, array(
        'menu-item-title' => 'First Menu Item', 
        'menu-item-url' => 'http://mysite.com', 
        'menu-item-status' => 'publish'));*/
    
    $itemData =  array(
    'menu-item-object-id' => $page_id,
    'menu-item-parent-id' => 0,
    'menu-item-position'  => 2,
    'menu-item-object' => 'page',
    'menu-item-type'      => 'post_type',
    'menu-item-status'    => 'publish'
  );

  
  
wp_update_nav_menu_item($menu->term_id, 0, $itemData);
scotts_set_nav_menu($menu->term_id,'footer_menu');



	} else {
	
	// the plugin may have been previously active and the page may just be trashed...

		$page_id = $mypage->ID;

		//make sure the page is not trashed...
		$mypage->post_content = "Recovered from trash.<br /><br />De inhoud van deze pagina wordt automatisch aangemaakt. Je dient deze dus niet te editeren.<br /><br />De Scoutnet API secret key moet ingevuld zijn. Zie Instellingen -> Scoutnet API group";
		$mypage->post_status = 'publish';
		$page_id = wp_update_post( $mypage );

	}
		
	
	$this->pages[$_p['post_name']]['id']=$page_id;
	update_option('sn_scoutnet_pages', $this->pages);



	

}	
	
	
	

}






