<?php

/*
Plugin Name: Scoutnet - shortcodes
Plugin URI: http://www.scoutnet.be/
Description: Leiding en andere contacten. Na activatie ga naar Settings/Instellingen -> Scoutnet API
Author: Scoutnet
Version: 0.1
Author URI: http://www.scoutnet.be
*/

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define('SN_API_VERSION', '1.0');
define('SN_API_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('SN_API_THEME_URL', get_template_directory_uri( __FILE__ ));

require_once dirname( __FILE__ ) . '/myfunctions.php';


function sn_common_scripts() {

wp_register_script('sn_public.js', SN_API_PLUGIN_URL . 'js/sn_public.js');
wp_enqueue_script('sn_public.js');

}

function sn_hello_scoutnet($content) {

return $content;

}


add_action('wp_enqueue_scripts', 'sn_common_scripts');
//add_filter('the_content', 'sn_hello_scoutnet');

add_filter( 'the_content', 'featured_image_before_content' ); 
 
 function featured_image_before_content( $content ) { 
    if ( is_singular('post') && has_post_thumbnail()) {
        $thumbnail = get_the_post_thumbnail();

        $content = $thumbnail . $content;
		
		}

    return $content;
}



function my_plugin_install() {

	//global $wpdb;
	
	delete_option("sn_scoutnet_pages");
	add_option("sn_scoutnet_pages", array(), '', 'yes');


}


require_once dirname( __FILE__ ) . '/api_call.php';

if ( is_admin() ){
require_once dirname( __FILE__ ) . '/admin.php';
}




function my_plugin_remove() {

	//global $wpdb;
	
	// verplaats de pagina's naar de trash. Of meteen volledig verwijderen ??
	
	$pages = get_option( 'sn_scoutnet_pages' );
	foreach($pages as $key => $page){
		if( $page['id'] ) {
			wp_delete_post( $page['id'] ); // this will trash, not delete. To also empty the trash use : wp_delete_post ($page_id, true)
		}
	}
	
	delete_option( 'sn_scoutnet_api' );
	//delete_option( 'sn_scoutnet_pages' );
	delete_option( 'sn_scoutnet_group' );
	

}




//Shortcodes
function display_group_callback($atts,$content,$tag){
	
	
wp_register_script('map_public.js', SN_API_PLUGIN_URL . 'js/map_public.js' );
wp_enqueue_script('map_public.js');


$call = sn_getGroup();

	 
	if ($call['decoded']['head']['status']==1){
		$group = $call['decoded']['body']['data'];

		$tmp = explode(':',$group['latlng']);
		$group['lat']=$tmp[0];
		$group['lng']=$tmp[1];
		unset($tmp);


$content = "
	<script type=\"text/javascript\">
		var templateDir = \"".SN_API_PLUGIN_URL."\";
	</script>
	<div class=\"vereniging-titel\">
		<div class=\"left\">
			<h1>{$group['groupname']}</h1>
			<p class=\"ondertitel\">{$group['groupname2']}<br />{$group['groupID']} [{$group['section']}]</p>
";

if ($group['hasaddress']=='y'){
$content .= "<p>{$group['street']}<br />{$group['postcode']} {$group['city']}</p>";
}

if ($group['tel']!=''){
$content .= "<p>{$group['tel']}</p>";
}

if ($group['mail']!=''){
$content .= "<p>".sn_scramble($group['mail'])."</p>";
}

//TODO is de pagina "leiding" wel geactiveerd ? zoniet, geen link voorzien

if (count($group['contact']>0)){
$groepsleiding_str = '';
$content .= "<p><b>Groepsleiding:</b> ";
foreach ($group['contact'] as $key => $value){
$groepsleiding_str .= "<a href=\"../leiding/{$value['slug']}/\">{$value['name']}</a>, ";
}

$groepsleiding_str = substr($groepsleiding_str,0,-2);

$content .= "$groepsleiding_str</p>";
}


if ($group['extra'][1]['zee']=='y'){
$content .= "<p><img alt=\"zeescouts\" width=\"60\" height=\"80\" src=\"".SN_API_THEME_URL."/images/zeescouts.jpg\" title=\"zeescouts werking\" class=\"size-full alignleft\" /></p>";
}

if ($group['extra'][2]['akabe']=='y'){
$content .= "<p><img alt=\"akabe\" width=\"60\" height=\"80\" src=\"".SN_API_THEME_URL."/images/akabe.png\" title=\"akabe werking\" class=\"size-full alignleft\" /></p>";
}

if ($group['extra'][8]['das']!=''){
$das = json_decode($group['extra'][8]['das']);
$das_url = $das->{'url'};
$content .= "<p><img alt=\"groepsdas\" width=\"196\" height=\"88\" src=\"$das_url\" title=\"groepsdas\" class=\"size-full alignleft\" /></p>";
}else{
$content .= "<p><img alt=\"groepsdas\" width=\"196\" height=\"88\" src=\"http://images.scoutnet.be/dassen/blanco.png\" title=\"groepsdas - kleuren en formaat nog niet toegekend\" class=\"size-full alignleft\" /></p>";
}


$content .= "
		</div>
		<form>
		<input type=\"hidden\" id=\"lat\" value=\"{$group['lat']}\" />
		<input type=\"hidden\" id=\"lng\" value=\"{$group['lng']}\" />
		</form>
		
		<div class=\"map\" id=\"map\" style=\"background-image:url(http://maps.google.com/maps/api/staticmap?center={$group['lat']},{$group['lng']}&amp;zoom=13&amp;markers=icon:".SN_API_PLUGIN_URL."/img/m_red.png|{$group['lat']},{$group['lng']}&amp;size=400x300&amp;sensor=false);\">
		<br/><br/><br/><div style=\"text-align:center; padding: 20px; cursor:pointer; cursor:hand; filter: alpha(opacity=55); -moz-opacity: 0.55; opacity: 0.55; background-color:#eeeeee\" onclick=\"activateMap();\">Klik om de google map te activeren</div>
		</div>
	
		</div>
";
if ($group['extra'][4]['promo']!=''){
$content .= "
		<h2>Wat ?</h2>
		<p>{$group['extra'][4]['promo']}</p>
";
}
if ($group['extra'][5]['waar']!=''){
$content .= "
		<h2>Waar ?</h2>
		<p>{$group['extra'][5]['waar']}</p>
";
}
if ($group['extra'][6]['wanneer']!=''){
$content .= "
		<h2>Wanneer ?</h2>
		<p>{$group['extra'][6]['wanneer']}</p>
";
}
if ($group['extra'][7]['lidgeld']!=''){
$content .= "
		<h2>Lidgeld ?</h2>
		<p>{$group['extra'][7]['lidgeld']}</p>
";
}

if ($group['extra'][9]['groepsfoto']!=''){
	$content .= "<p><img alt=\"groepsfoto\" width=\"700\" height=\"350\" src=\"{$group['extra'][9]['groepsfoto']}\" title=\"groepsfoto\" class=\"aligncenter\" /></p>";
}


$content .= "
		<br /><br />
";
	
	}else{
	$content = "<div class=\"error\">error</div>";	
	}
	
	
	return $content;
	
}


function display_members_callback($atts,$content,$tag){
     
	$values = shortcode_atts(array(
		'id' => 0,
		'slug' => '',
		'section' => 'null',
		'type' => '',
		'style' => '1',
		'link_slug' => '0',
		'groupby' => 'section'
	),$atts);  
	
	$options = get_option('sn_scoutnet_api');
	
	$mysections = array();
	$mytypes = array();
	
	$styles = array(1,2,3);
	$groupby = array('section','type','none');
	$values['hide'] = array();
	
	if (isset($options['option1'])){if ($options['option1']=='y'){$values['hide'][] = 'birthday';}}
	if (isset($options['option2'])){if ($options['option2']=='y'){$values['hide'][] = 'phone';}}
	
	if (!in_array($values['style'],$styles)){$values['style']=1;}
	if (!in_array($values['groupby'],$groupby)){$values['groupby']='section';}
	
//return print_r($values,true);

    $output = '';
	
	
	
     
	if($values['id'] > 0){
 	$call = sn_getMember($values['id'],$options['accountid']);
	
		if ($call['decoded']['head']['status']==1){
		$member = $call['decoded']['body']['data'];
	
		//$output = print_r($member,true);
		return display_member_style($member,$values);
	
		}else{
		$output = "<div class=\"error\">error</div>";
		}
	
	//return $output;
	
	}
	

	if($values['slug'] != ''){
 	$call = sn_getMember($values['slug'],$options['accountid']);
	
		if ($call['decoded']['head']['status']==1){
		$member = $call['decoded']['body']['data'];
	
		//$output = print_r($member,true);
		return display_member_style($member,$values);
	
		}else{
		$output = "<div class=\"error\">error</div>";
		}
	
	//return $output;
	
	}

	
	
	
	if (!isset($_SESSION['sections'][$options['accountid']])){

	$call = sn_getAllSections($options['accountid']);
	
	if (isset($call['decoded']['head']['status']) && ($call['decoded']['head']['status'] === "1")){
		if ($call['decoded']['body']['num']!=0){
			$all_sections = $call['decoded']['body']['data'];
		}else{
			$all_sections = array();
		}
	} else {
		echo "<div class=\"error settings-error\"><p><strong>Sections failure</strong></p></div>";
	}

	$_SESSION['sections'][$options['accountid']]=$all_sections;
	
	}else{
	$all_sections = $_SESSION['sections'][$options['accountid']];
	}
	
	foreach($all_sections as $section){$mysections[]=$section['code'];}
	
	if (!isset($_SESSION['types'][$options['accountid']])){

	$call = sn_getAllTypes($options['accountid']);
	
	if (isset($call['decoded']['head']['status']) && ($call['decoded']['head']['status'] === "1")){
		if ($call['decoded']['body']['num']!=0){
			$all_types = $call['decoded']['body']['data'];
		}else{
			$all_types = array();
		}
	} else {
		echo "<div class=\"error settings-error\"><p><strong>Types failure</strong></p></div>";
	}

	$_SESSION['types'][$options['accountid']]=$all_types;
	
	}else{
	$all_types = $_SESSION['types'][$options['accountid']];
	}

	foreach($all_types as $type){$mytypes[]=strtolower($type['name']);}
	
	
	
	
	
	if((in_array($values['section'],$mysections)) || (in_array($values['type'],$mytypes))){

		$call = sn_getSectionMembers($values['section'],$values['type']);
		
		if ($call['decoded']['head']['status']==1){
		$members = $call['decoded']['body']['data'];
	
	
		//$output = print_r($members,true);
	
		}else{
		$output = "<div class=\"error settings-error\">error</div>";
		}
	
	 
	//return $output;
	return display_members_style($members,$values);
		
	}
	
//return print_r($_SESSION['sections'],true);
//return print_r($mytypes,true);
//return $values['type']."ee";

     
}

add_shortcode('scoutnet_members','display_members_callback');

add_shortcode('scoutnet_group','display_group_callback');


/* Runs when plugin is activated */
register_activation_hook(__FILE__,'my_plugin_install'); 

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'my_plugin_remove' );


/* End of File */