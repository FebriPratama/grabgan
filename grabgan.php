<?php
/*
	Plugin Name: Katanya Plugin Grab Image Untuk wordpress
	Plugin URI: http://www.febripratama.com
	Description: Katanya sih buat ngegrab
	Author: Pratama, Febri
	Version: 3.0
	Author URI: http://www.febripratama.com

*/
require_once("vendor/autoload.php");
require_once("bing/src/Bing.php");
require_once("bing/src/Image.php");

use Buchin\Bing\Image;

class ngeGrabGan2{

	function __construct(){

		add_action( 'init',  array(&$this, 'keyword'));
		add_action("admin_menu", array(&$this, 'add_theme_menu_item'));
		add_action("admin_init", array(&$this, 'display_grabGan_fields'));
		add_action( 'grabgan_cron',  array(&$this,'postCron' ) );
		
		get_option('grabgan.total_post') == null ? update_option('grabgan.total_post', '15') : null;
		get_option('grabgan.total_tag') == null ? update_option('grabgan.total_tag', '5') : null;

		if((get_option('grabgan.cron') == 'off') || (get_option('grabgan.cron') == null)){
			
			self::cron_deactivate();
		
		}else{

			if( !wp_next_scheduled( 'grabgan_cron' ) ) {

				self::cron_activate();

			}

		}

	}

/*

REGISTER KW

*/

	function keyword() {

		$labels = array(
			'name'                => _x( 'keyword', 'Post Type General Name', 'text_domain' ),
			'singular_name'       => _x( 'keyword', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'           => __( 'Keyword', 'text_domain' ),
			'name_admin_bar'      => __( 'Keyword', 'text_domain' ),
			'parent_item_colon'   => __( 'Parent Item:', 'text_domain' ),
			'all_items'           => __( 'All Items', 'text_domain' ),
			'add_new_item'        => __( 'Add New Item', 'text_domain' ),
			'add_new'             => __( 'Add New', 'text_domain' ),
			'new_item'            => __( 'New Item', 'text_domain' ),
			'edit_item'           => __( 'Edit Item', 'text_domain' ),
			'update_item'         => __( 'Update Item', 'text_domain' ),
			'view_item'           => __( 'View Item', 'text_domain' ),
			'search_items'        => __( 'Search Item', 'text_domain' ),
			'not_found'           => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
		);
		$args = array(
			'label'               => __( 'keyword', 'text_domain' ),
			'description'         => __( 'Post Type Description', 'text_domain' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', ),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-admin-network',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,		
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => false,
			'capability_type'     => 'page',
		);
		
		register_post_type( 'kw', $args );

	}

/*

POST KW

*/

	public static function storeKw($kw){

		$my_post = array(
		  'post_title'    => $kw,
		  'post_content'  => 'true',
		  'post_status'   => 'publish',
		  'post_type' 	  => 'kw'
		);

		if(wp_insert_post( $my_post )){

			return 1;

		}

		return 0;
	}

	public static function updateKw($id){

		  $my_post = array(
		      'ID'           => $id,
		      'post_content' => 'true',
		  );

		if(wp_update_post( $my_post )){

			return 1;

		}

		return 0;

	}
/*

Menu Stat

*/
	public static function totalKw(){

		$my_query = new WP_Query( array( 'post_type' => 'kw','posts_per_page' => -1) );

		$i = 0;
		
		if( $my_query->have_posts() ) {
		  while ($my_query->have_posts()) : $my_query->the_post(); 

		  	$i++;

		  endwhile;
		}

		wp_reset_query();  

		return $i;
	}

	public static function totalPost(){

		$my_query = new WP_Query( array( 'post_type' => 'post','posts_per_page' => -1) );

		$i = 0;
		
		if( $my_query->have_posts() ) {
		  while ($my_query->have_posts()) : $my_query->the_post(); 

		  	$i++;

		  endwhile;
		}

		wp_reset_query();  

		return $i;
	}

	public static function totalPosted(){

		$my_query = new WP_Query( array( 'post_type' => 'kw','posts_per_page' => -1) );

		$i = 0;

		if( $my_query->have_posts() ) {
		  while ($my_query->have_posts()) : $my_query->the_post(); 

		  		if(get_the_content() == 'false'){

		  			$i++;

		  		}

		  endwhile;
		}

		wp_reset_query();  

		return $i;
	}

	public static function totalAttachment(){

		$posts = get_posts( array(
		                         'post_type' => 'attachment','numberposts' => -1
		                     ) );

		return count( $posts );
	}

	public static function percentagePosted(){

		$total = (int)ngeGrabGan2::totalKw();
		$posted = (int)ngeGrabGan2::totalPosted();

		return (int)($posted/$total * 100) ;
	}

/*

Cron setting

*/

	public static function cron_activate() {

		wp_schedule_event( time(), 'hourly', 'grabgan_cron');

	} 

	public static function cron_deactivate() {

		wp_clear_scheduled_hook('grabgan_cron');

	}

/*

Menu setting

*/

	function theme_settings_page(){

    	?>

		    <div class="wrap">
		    <h1>grabGan Setting</h1>
		    <hr>

			<?php if( isset($_GET['settings-updated']) ) { ?>
			    <div id="message" class="updated">
			        <p><strong><?php _e('Settings saved and keywords posted succesfully(jika ada).'); ?></strong></p>
			    </div>
			<?php } ?>

			<blockquote>
				<h3>Readme !</h3>
				<ol>
					<li>Harus ada category dulu selain uncategorized kalau gk ada category dia akan mengulang(rekursif) sampai dapat. Jadi bisa bikin memory jebol</li>
					<li>Untuk test posting harus input keyword -> plugin settingnya terus isi kwnya perbaris,</li>
					<li>Untuk cron pasang script php ini di header.php : <pre>if(isset($_GET['cron'])) echo ngeGrabGan2::manualCron($_GET['cron']);</pre></li>
					<li>Link cron : http://www.web-agan.com/?cron=goyanggan</li>
					<li>Perintah cron : wget -O - http://www.web-agan.com/?cron=goyanggan >/dev/null 2>&1</li>
				</ol>
			</blockquote>
			<hr>
			<H4>Plugin Status</H4>
			Total Keywords : <?php echo ngeGrabGan2::totalKw(); ?><br>
			Total Posted Keywords : <?php echo ngeGrabGan2::totalPosted(); ?><br>
			Total Posts : <?php echo ngeGrabGan2::totalPost(); ?><br>
			Total Attachments : <?php //echo ngeGrabGan2::totalAttachment(); ?>{{ FOR FIX LATER(WP API UPDATE 1.20 ) }}<br> 				

			<br>
			Percentage kw and posted : <?php echo @ngeGrabGan2::percentagePosted(); ?> %
			<hr>
		    <form method="post" action="options.php">
		        <?php
		            settings_fields("section");
		            do_settings_sections("grabgan-options");      
		            submit_button(); 
		            submit_button( "Auto Post", "submit", "auto" );

		        ?>          
		    </form>
			</div>

		<?php

	}

	function display_kw_element()
	{
		?>
	    	<textarea name="kwlist" style="width: 100%;min-height: 200px;"></textarea>
	    <?php
	}

	function display_bing_key_element()
	{
		?>
	    	<input type="hidden" value="<?php echo get_option('grabgan.bingkey'); ?>" name="bingkey" style="width: 50%;" placeholder="Bing/Azure Client Key(kalau gk tau googling gan)" required>
	    <?php
	}

	function display_cron_element()
	{
		
		$on = get_option('grabgan.cron') == 'on' ? 'selected' : '';
		$off = (get_option('grabgan.cron') == 'off') || get_option('grabgan.cron') == null ? 'selected' : '';

		?>
			<select name="cron">
				<option value="on" <?php echo $on; ?>>On</option>
				<option value="off" <?php echo $off; ?>>Off</option>
			</select>
	    <?php

	}

	function display_set_category_opt()
	{

		$on = get_option('grabgan.cat_opt') == 'on' ? 'selected' : '';
		$off = (get_option('grabgan.cat_opt') == 'off') || get_option('grabgan.cat_opt') == null ? 'selected' : '';

		?>
			<select name="grabcategoryopt">
				<option value="on" <?php echo $on; ?>>On</option>
				<option value="off" <?php echo $off; ?>>Off</option>
			</select>
	    <?php

	}

	function display_set_fafifu_opt()
	{

		$on = get_option('grabgan.fafifu_opt') == 'on' ? 'selected' : '';
		$off = (get_option('grabgan.fafifu_opt') == 'off') || get_option('grabgan.fafifu_opt') == null ? 'selected' : '';

		?>
			<select name="fafifuopt">
				<option value="on" <?php echo $on; ?>>On</option>
				<option value="off" <?php echo $off; ?>>Off</option>
			</select>
	    <?php

	}

	function display_set_backdate_opt()
	{

		$on = get_option('grabgan.backdate_opt') == 'on' ? 'selected' : '';
		$off = (get_option('grabgan.backdate_opt') == 'off') || get_option('grabgan.backdate_opt') == null ? 'selected' : '';

		?>
			<select name="backdateopt">
				<option value="on" <?php echo $on; ?>>On</option>
				<option value="off" <?php echo $off; ?>>Off</option>
			</select>
	    <?php

	}

	function display_set_backdate()
	{

		?>

	    	<input type="number" value="<?php echo (int)get_option('grabgan.backdate') == null ? 0 : (int)get_option('grabgan.backdate'); ?>" name="backdate" style="width: 50%;">
	   
	    <?php
	    
	}

	function display_set_category()
	{

		$args = array(
		        'orderby'   => 'name',
		        'order'     => 'ASC',
		        'hide_empty'    => '0',
		  );

		$categories = get_categories($args);

		?>
			<select name="grabcategory">
				<?php

				if (trim(get_option('grabgan.cat_default')) !== '') {

				  	$option = '<option value="'.get_option('grabgan.cat_default').'" selected>';
					$option .= get_cat_name(get_option('grabgan.cat_default'));
					$option .= '</option>';

					echo $option;

				}

				foreach ($categories as $category) {

				  	$option = '<option value="'.$category->term_id.'">';
					$option .= $category->cat_name;
					$option .= '</option>';

					echo $option;

				  }

			  ?>
			</select>
	    <?php

	}

	function display_post_total()
	{

		?>

	    	<input type="number" value="<?php echo (int)get_option('grabgan.total_post'); ?>" name="totalpost" style="width: 50%;" required>
	   
	    <?php
	    
	}

	function display_tag_total()
	{

		?>

	    	<input type="number" value="<?php echo (int)get_option('grabgan.total_tag'); ?>" name="totaltag" style="width: 50%;" required>
	   
	    <?php
	    
	}

	function kw_masuk_sini(){


		if (isset($_POST['kwlist']) == true) {

			if (trim($_POST['kwlist']) !== '') {

				$parse = preg_split('/\r\n|\r|\n/', $_POST['kwlist']);

	            $tot = count($parse);

	            for($i=0;$i<$tot;$i++){

	                if(ngeGrabGan2::storeKw($parse[$i])){

	                	$w=$i;

	                }

	            }

			}

		}

		if (isset($_POST['cron'])) {

			 update_option('grabgan.cron', $_POST['cron']);
		}

		if (isset($_POST['totaltag'])) {

			 update_option('grabgan.total_tag', $_POST['totaltag']);
		}

		if (isset($_POST['totalpost'])) {

			 update_option('grabgan.total_post', $_POST['totalpost']);
		}

		if (isset($_POST['fafifuopt'])) {

			 update_option('grabgan.fafifu_opt', $_POST['fafifuopt']);
		}

		if (isset($_POST['grabcategory'])) {

			 update_option('grabgan.cat_default', $_POST['grabcategory']);
		}

		if (isset($_POST['grabcategoryopt'])) {

			 update_option('grabgan.cat_opt', $_POST['grabcategoryopt']);
		}

		if (isset($_POST['backdateopt'])) {

			 update_option('grabgan.backdate_opt', $_POST['backdateopt']);
		}

		if (isset($_POST['backdate'])) {

			 update_option('grabgan.backdate', $_POST['backdate']);
		}

		if (isset($_POST['bingkey'])) {

			 update_option('grabgan.bingkey', $_POST['bingkey']);
		}

		if (isset($_POST['auto'])) {

			 self::postCron();
		}

	}

	function display_grabGan_fields()
	{
		add_settings_section("section", "All Settings", null, "grabgan-options");
		
		add_settings_field("grabgan.kwlist", "Keyword List<small><br>&nbsp;*keyword perbaris gan</small>", array(&$this, 'display_kw_element'), "grabgan-options", "section");
		//add_settings_field("grabgan.bingkey", "Bing/Azure Client Key", array(&$this, 'display_bing_key_element'), "grabgan-options", "section");
		add_settings_field("grabgan.cron", "Cron Job", array(&$this, 'display_cron_element'), "grabgan-options", "section");
		add_settings_field("grabgan.total_post", "Max Total Attachments", array(&$this, 'display_post_total'), "grabgan-options", "section");
		add_settings_field("grabgan.total_tag", "Max Total Tags", array(&$this, 'display_tag_total'), "grabgan-options", "section");
		add_settings_field("grabgan.cat_opt", "Category Manual(off untuk otomatis)", array(&$this, 'display_set_category_opt'), "grabgan-options", "section");
		add_settings_field("grabgan.cat_default", "Category default setiap post", array(&$this, 'display_set_category'), "grabgan-options", "section");

		add_settings_field("grabgan.backdate_opt", "Backdate Status", array(&$this, 'display_set_backdate_opt'), "grabgan-options", "section");
		add_settings_field("grabgan.backdate", "Nilai Backdate (Bulan)", array(&$this, 'display_set_backdate'), "grabgan-options", "section");
		add_settings_field("grabgan.fafifu_opt", "Fafifu Status", array(&$this, 'display_set_fafifu_opt'), "grabgan-options", "section");

	    register_setting("section", "grabgan.kwlist",array(&$this, 'kw_masuk_sini'));
	}

	function add_theme_menu_item()
	{

		add_menu_page("grabGan Setting", "grabGan Setting", "manage_options", "grabgan-setting", array(&$this, 'theme_settings_page'), null, 99);

	}

/*

	bing search

*/
    public static function httpCall($url = '', $referer = '', $post_call = FALSE, $postdata = '', $include_header = FALSE, $arr_curl_option = array()) {

        $arr_result = array();
        $cookie_file = plugin_dir_path('index.php') . "grabgan.cookie";
        $ch = curl_init();
        if ($include_header !== FALSE) curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_COOKIESESSION, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        /*
        if (self::use_proxy !== FALSE) {
            curl_setopt($ch, CURLOPT_PROXY, self::proxy);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, self::proxy_username . ':' . self::proxy_password);
        }
        */
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        if ($post_call !== FALSE) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        }
        if (!empty($referer)) curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:25.0) Gecko/20100101 Firefox/25.0');
        if (!empty($arr_curl_option)) {
            foreach ($arr_curl_option as $key_option => $value_option) {
                curl_setopt($ch, $key_option, $value_option);
            }
        }
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        $err_no = curl_errno($ch);
        curl_close($ch);
        unset($ch);
        $arr_result['response'] = $response;
        $arr_result['error'] = $error;
        $arr_result['info'] = $info;
        $arr_result['err_no'] = $err_no;
        return $arr_result;

    }

    private static function bingSearch($q, $filter = NULL, $top = 40, $skip = NULL) {

        $accountKey = get_option('grabgan.bingkey');
        
        $filters = "&ImageFilters='Size:Large'";

        $request = 'https://api.datamarket.azure.com/Bing/Search/Image?$format=json&$top=' . (int)$top . '&Query=' . urlencode('\'' . $q . '\'');
        if ($filter) $request = $request . $filters;
        if ($skip) $request = $request . '&$skip=' . urlencode($skip);
        $gacookie = plugin_dir_path('index.php') . "bing.cookie";
        $arr_curl_option = array(CURLOPT_USERPWD => "ignored:" . $accountKey, CURLOPT_COOKIEFILE => $gacookie, CURLOPT_COOKIEJAR => $gacookie);
        $referer = '';
        $url = $request;
        $arr_http_call_result = self::httpCall($url, $referer, FALSE, '', FALSE, $arr_curl_option);
        $response = $arr_http_call_result['response'];
        $error = $arr_http_call_result['error'];
        $info = $arr_http_call_result['info'];
        $err_no = $arr_http_call_result['err_no'];
        if (empty($response)) {
            $jsonobj = array('d' => array('results' => '', '__next' => '', '__previous' => ''));
            $json_bing_image_search = json_encode($jsonobj);
            $jsonobj = json_decode($json_bing_image_search);
            return $jsonobj;
        } else $jsonobj = json_decode($response);

        return $jsonobj;

    }

	private static function doMagicBing($kw){

	  $imageScraper = new Image;

	  $data = [];

		  $niche = array(

		    'default' => array(
		      'keygrab' => array("Design Ideas","Ideas","Design","Plan","Idea")
		      )

		    );

	      foreach($niche['default']['keygrab'] as $b){
	        
	          $q = (strpos($kw, $b) !== false) ? $kw : $kw .' '.$b;
	          $resuls = $imageScraper->scrape($q);

	          foreach($resuls as $fook){
	            
	            $data[] = array(

	              'url' => $fook['mediaurl'],
	              'title' => $fook['title'],
	              'thumb' => $fook['thumburl'],
	              'width' => explode(' ', $fook['size'])[0],
	              'height' => explode(' ', $fook['size'])[2],
	              'type' => explode(' ', $fook['size'])[3]

	              );

	          }

	      }

	  	return $data;
	  
	}

	private static function doMagicGoogle($q){

	    $curl = curl_init();

	    $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
	    $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	    $header[] = "Cache-Control: max-age=0";
	    $header[] = "Connection: keep-alive";
	    $header[] = "Keep-Alive: 300";
	    $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	    $header[] = "Accept-Language: en-us,en;q=0.5";
	    $header[] = "Pragma: ";

	    $url = 'https://www.google.com/search?q='.urlencode($q).'&source=lnms&tbm=isch';

	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:7.0.1) Gecko/20100101 Firefox/7.0.12011-10-16 20:23:00");
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	    curl_setopt($curl, CURLOPT_REFERER, "https://www.facebook.com");
	    curl_setopt($curl, CURLOPT_ENCODING, "gzip,deflate");
	    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION,true);

	    $html = curl_exec($curl);
	    //echo 'Curl error: '. curl_error($curl);
	    curl_close($curl);

		$hub = [];

		$dom = NEW DOMDOcument();
		@$dom->loadHTML($html);

		$link = $dom->getElementsByTagName('div');

		foreach($link as $a) {

			if($a->getAttribute('class') == 'rg_meta') {

				$string = json_decode($a->textContent);
				
				//Log::info($string->murl);
				$hub[] = ['url' => $string->ou, 'title' => $string->s, 'height' => $string->oh, 'width' => $string->ow];

			}
		}

		return array('status' => 1, 'data' => $hub,'message' => 'oce','total' => count($hub));

	}

    public function getCat($id){

		$args = array(
		        'orderby'   => 'name',
		        'order'     => 'ASC',
		        'hide_empty'    => '0',
		  );

		$categories = get_categories($args);

    	if($id == 1){

			self::getCat($categories[array_rand($categories)]->term_id);

    	}
    	
    	return $id;

    }

    public static function generatePostTitle($kw){

		$blakang=array("Decoration", "Plan", "Concept", "Property", "Design", "Pict", "Ideas", "Image", "Decor", "Style", "Collection", "Set", "Paint", "Model", "Minimalist", "Photos", "Gallery");
		return $title = $kw.' '.$blakang[rand ( 0 , count($blakang)-1 )];
    	
    }

    public static function generateAttachtmentTitle($title,$kw){

    	$title = preg_replace('/\.[^.\s]{3,4}$/', '', $title);
    	
		$awal=array("New", "Cool", "Unique", "Nice", "Luxury", "Modest", "Awesome", "Amazing", "Popular", "Awesome", "Custom", "Modern", "Inspiring" , "Classic", "Excellent", "Perfect", "Cute", "Cheap", "Impressive", "Best", "Trend", "Innovative", "Great" , "Wonderful", "Impressive", "Contemporary", "Beauteous", "Winsome", "Attractive", "Prepossessing", "Captivating", "Enchanting", "Magnificent", "Fair", "Tasty", "Ravishing", "Remarkable", "Comely", "Appalling", "Wonderful", "Interesting", "Alluring", "Surprising", "Astonishing", "Astounding", "Pleasant", "Delectable", "Heavenly", "Fascinating", "Entrancing", "Adorable", "Endearing", "Exciting", "Divine", "Charming", "Stunning", "Prepossessing", "Splendid", "Engaging", "Personable", "Mesmerizing", "Heavenly", "Lovely", "Terrific", "Exquisite", "Glamorous", "Extraordinary", "Marvelous", "Picturesque", "Amusing", "Good Looking", "Agreeable", "Inspiring", "Winning", "Gorgeous", "Ravishing", "Catchy");

		$tengah=array("Kitchen", "Bathroom", "Bedroom", "Sofa", "Fireplace", "Dining Room", "Wall Ideas", "Curtain", "Backyard", "Living Room", "Backyard", "Landscape", "Apartment", "Exterior", "Furniture", "Interior", "Kids Room", "Office", "Pool", "Dining Table", "Architecture", "Family Room", "Garden", "Outdoor Room", "Home Office", "Study Room", "Software", "Lighting", "Bathroom Accessories", "Home Security", "Home Tips", "Stair Railings", "Paint Color", "Window", "Storage", "Laundry Room", "Fireplace", "Patio");

		$akhir=array("Design", "Interior", "Exterior", "Ideas", "Remodelling", "Decoration", "Collection", "Style", "Concept", "Photography", "Creative", "Plans Free", "Decoration", "Minimalist", "Set", "Model", "Painting", "Property", "Charming", "Interior Home Design", "Picture", "Small Room", "Modern", "Decor Ideas");

		$blakang=array("Decorating Ideas", "Design Ideas","Decoration Ideas","Decor","Ideas","Design", "View", "Gallery","set");

		$sambung=array("At", "Fresh at","New in","Fresh In","Fresh On","New At", "By", "For", "And", "With", "In", "Of", "Or Other", "A", "Is Like", "On");

		return $title = $awal[rand ( 0 , count($awal)-1 )].' '.$title.' '.$tengah[rand ( 0 , count($tengah)-1 )].' '.$akhir[rand ( 0 , count($akhir)-1 )].' '.$sambung[rand ( 0 , count($sambung)-1 )].' '.$kw.' '.$blakang[rand ( 0 , count($blakang)-1 )];
    	
    }

    public static function generateTag($kw,$id){

		$payload='https://suggestqueries.google.com/complete/search?client=firefox&q='.urlencode($kw);

		$xmls = @file_get_contents($payload);
		
		$xmls = json_decode($xmls,true);

			$tags = array();

			for($i=0;$i<count($xmls[1]);$i++){

				$tags[$i] = $xmls[1][$i];

				if($i > (int)get_option('grabgan.total_tag')){

					break;

				}

			}

		if(count($tags)<0){

			$tags = explode(" ", get_the_title( $id ));

		}

    	wp_set_post_tags( $id, $tags, false );

    }

    public static function generateBackdate(){
    	
    	if( (get_option('grabgan.backdate_opt') == 'on') ){
			
			$total = get_option('grabgan.backdate') !== null ? (int)get_option('grabgan.backdate') : 0;

    		return date('Y-m-d H:i:s', strtotime('-'.$total.' month', strtotime(date('Y-m-d H:i:s'))));

    	}

    	return false;
    }

    public static function generateFafifu($postid,$type){

    	if( (get_option('grabgan.fafifu_opt') == 'on') ){

			$p = get_post($postid);

			$c = get_the_category($postid);

			$tag = wp_get_post_tags($postid);

				$name = '';

				$i=0;

				foreach($tag as $t){

					$name = $i == 0 ? $t->name : $name.','.$t->name;

					$i++;

				}

			$fafifu = $p->post_title.', Category '. $c->name .' With Resolution '.rand(450,1200).' x '.rand(450,1200).' pixel, Size Decorating Ideas is '. rand(500,1000).' kb, Published By '. the_author_meta( 'user_nicename' , $p->post_author ) .', Tagged of '. $name ;

			if($type=='post'){

				$my_post = array(
				    'ID'           => $postid,
				    'post_content' => $fafifu
				);
				  
				wp_update_post( $my_post );

				return true;

			}


			return $fafifu;

		}

		return false;
    }

    public static function newAuto($kw,$kw_id){
    	
    	set_time_limit(-1);

		//create new post
		$args = array(
		        'orderby'   => 'name',
		        'order'     => 'ASC',
		        'hide_empty'    => '0',
		  );

		$categories = get_categories($args);

    	$cat_id = (get_option('grabgan.cat_opt') == 'off') || get_option('grabgan.cat_opt') == null ? self::getCat($categories[array_rand($categories)]->term_id) : get_option('grabgan.cat_default');

    	$status = get_option('grabgan.status') !== null ? 'publish' : get_option('grabgan.status');

    	//backdate
    	$date = self::generateBackdate();    	
    	
    	$data = array(

    		'post_title' => self::generatePostTitle($kw), 
    		'post_date' => $date,
    		'post_date_gmt' => $date,
    		'post_status' => $status

    	);

    	$post_id = wp_insert_post($data);

    	//generate fafifu
    	$fafifu = self::generateFafifu($post_id,'post');

    	//set category
		$cat_ids = array( (int)$cat_id );

		$term_taxonomy_ids = wp_set_object_terms( $post_id, $cat_ids, 'category' );

    	//generate tag
    	self::generateTag($kw,$post_id);

    	//fetch bing api
		$results = self::doMagicBing($kw);

		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$i=0;

		$max= get_option('grabgan.total_post') ? (int)get_option('grabgan.total_post') : 15;

	      $imgs = array();

	        foreach($results as $a) 
	        {

	          $title = trim($a['title']) == '' || ( count(explode(' ', $a['title'])) < 2 ) ? $q : $a['title'];

	            $sql_fields = array(

	                'term'  => $title,
	                'url'  => $a['url'],
	                'height' => array_key_exists('height', $a) ? $a['height'] : 200,
	                'width' => array_key_exists('width', $a) ? $a['width'] : 200,
	                'thumb' => $a['thumb'],
	                'type'    => array_key_exists('type', $a) ? $a['type'] : 'jpeg'

	                ); 

	            $imgs[] = $sql_fields;  

	        }

		if(count($imgs) < 1){
			
			return 2;

		}

		foreach ($imgs as $value){

			$url = $value['url'];

			$uploads = wp_upload_dir();
			
			$fileTitle = pathinfo($url);
			
			$title = self::generateAttachtmentTitle(str_replace('-',' ',$fileTitle['filename']),$kw);

			$filename = wp_unique_filename($uploads['path'], $title.'.'.$fileTitle['extension']);

			$parent_post_id = $post_id;

			$wp_filetype = wp_check_filetype($filename, NULL);

			if (substr_count($wp_filetype['type'], "image")) {

	            $fullpathfilename = $uploads['path'] . "/" . $filename;

				$image_string = self::makeCurlCall($url);	

				if ($image_string) {

	            	$fileSaved = file_put_contents($uploads['path'] . "/" . $filename, $image_string);

			    	//generate fafifu
			    	$fafifu = self::generateFafifu($post_id,'attachment');

		            $attachment = array(
		            	'post_mime_type' => $wp_filetype['type'], 
		            	'post_title' => $title, 
			    		'post_date' => $date,
			    		'post_date_gmt' => $date,
		            	'post_content' => $fafifu, 
		            	'guid' => $uploads['url'] . "/" . $filename
		            );

		            $attach_id = wp_insert_attachment($attachment, $fullpathfilename, $post_id);

	                $attach_data = wp_generate_attachment_metadata($attach_id, $fullpathfilename);

	                wp_update_attachment_metadata($attach_id, $attach_data);

	                update_post_meta($attach_id, '_wp_attachment_image_alt', $title);

		            //ifmaxpost

		            if($i>=$max){

		            	break;

		            }

		            $i++;

                }else{

                	$error = 300;

                }



            }else{

            	$error = 400;

            }


        }

        //gagal ngambil gambar
        if($i==0){

        	wp_delete_post( $post_id, true );

			$my_post = array(
			    'ID'           => $kw_id,
			    'post_content' => 'banned'
			);
			  
			wp_update_post( $my_post );

        	return trim($error) == null ? 'Kw Banned' : $error;

        }

        return 1;
    }

    public static function postCron(){

		//ambil post type kw
		if(get_option('grabgan.bingkey') !== null){

			$total = wp_count_posts( 'kw' );

			$queryCron = new WP_Query( array( 'post_type' => 'kw', 'posts_per_page' => -1,"s" => "true") );
			
			$title = '';
			$i=1;
			
			//var_dump($queryCron);

			if( $queryCron->have_posts() ) {
			  while ($queryCron->have_posts()) : $queryCron->the_post(); 
			  		
			  		$title = $title.'<br>'.$i.'.'.get_the_title() .' status :'. get_the_content() .' total scan : '.$i;

			  		if(get_the_content() == 'true'){

			  			$startAuto = self::newAuto(get_the_title(),get_the_ID());

			  			if($startAuto == 1){

		  					    $my_post = array(
								    'ID'           => get_the_ID(),
								    'post_content' => 'false'
								);
								  
								wp_update_post( $my_post );

							wp_reset_query();
			  				return 'Successfully';

			  			}else if($startAuto == 2){

			  				wp_reset_query();
			  				return 'Kosong, respone : '.$startAuto;

			  			}else{

			  				wp_reset_query();
			  				return 'Mbuh, respone : '.$startAuto;

			  			}

			  			break;
						
			  		}  	

			  		$i++;

			  endwhile;

			  wp_reset_query();

			  return 'Gak Ketemu kw yang aktif, log : '.$title;

			}

			wp_reset_query();  

			return 'Post Kosong';
			
		}else{

			return 'No bing Key';


		}

		return 'Not Found';
    }

    public static function randomPostAuto(){

    	$menit = date('i');
    	$menit = (int)$menit * 60;

    	if( $menit < 1000 ) {
    		$random = 100;
    	}else if( $menit > 1000 && $menit < 2000){
    		$random = 200;
    	}else if( $menit > 2000 && $menit < 3600){
    		$random = 300;
    	}

    	$message = $menit %  $random;

    	if( (int)$message == 0){

    		$message = self::postCron();

    	}

    	return $message;
    }
    
    public static function manualCron($get){

		if((get_option('grabgan.cron') == 'on') || (get_option('grabgan.cron') !== null)){

			if($get=='goyanggan'){

	           return $message = self::postCron();
	        
	        }
        	
        	return false;
        }

        return 'cron mati gan, aktifin dulu';
    }

    private static function fetch_image($url) {
        if (function_exists("curl_init")) {
            return self::curl_fetch_image($url);
        } elseif (ini_get("allow_url_fopen")) {
            return self::fopen_fetch_image($url);
        }
    }

    private static function makeCurlCall($url){

	  $curl = curl_init();
	  $timeout = 60;
	  curl_setopt($curl,CURLOPT_URL,$url);
	  curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
	  curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,$timeout);
	  curl_setopt($curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

	  curl_setopt($curl, CURLOPT_ENCODING,"");   
	  curl_setopt($curl, CURLOPT_TIMEOUT,60);
	  curl_setopt($curl, CURLOPT_FAILONERROR,true);
	  curl_setopt($curl, CURLOPT_VERBOSE, true);

	  $output = curl_exec($curl);
	  
	  if (curl_errno($curl)){

	    curl_close($curl);
	      return false;

	  }
	  else {

	    curl_close($curl);
	    return $output;

	  }  

	  return false;
	}

    private static function curl_fetch_image($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $image = curl_exec($ch);
        curl_close($ch);
        return $image;
    }

    private function fopen_fetch_image($url) {
        $image = file_get_contents($url, FALSE, $context);
        return $image;
    }

    public function media_process() {

                $filename = $post['filename'];
                $title = $post['title'];
                $alt = $post['alt'];
                $caption = $post['caption'];
                $image_url = $post['image_url'];
                $uploads = wp_upload_dir();
                $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
                $filename = wp_unique_filename($uploads['path'], $filename, $unique_filename_callback = NULL);
                $wp_filetype = wp_check_filetype($filename, NULL);
                $fullpathfilename = $uploads['path'] . "/" . $filename;
                try {
                    if (!substr_count($wp_filetype['type'], "image")) {
                        throw new Exception($filename . ' is not a valid image. ' . $wp_filetype['type'] . '');
                    }
                    $image_string = self::fetch_image($image_url);

                    $fileSaved = file_put_contents($uploads['path'] . "/" . $filename, $image_string);

                    if (!$fileSaved) {
                        throw new Exception("The file cannot be saved.");
                    }

                    $attachment = array('post_mime_type' => $wp_filetype['type'], 'post_title' => $title, 'post_content' => '', 'post_excerpt' => $caption, 'post_status' => 'inherit', 'guid' => $uploads['url'] . "/" . $filename);
                    $attach_id = wp_insert_attachment($attachment, $fullpathfilename, $post_id);

                    if (!$attach_id) {
                        throw new Exception("Failed to save record into database.");
                    }
                    $attach_ids[] = $attach_id;
                    require_once (ABSPATH . "wp-admin" . '/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $fullpathfilename);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    update_post_meta($attach_id, '_wp_attachment_image_alt', $alt);
                }
                catch(Exception $e) {
                    $error = '<div id="message" class="error"><p>' . $e->getMessage() . '</p></div>';
                }


    }

}

$grabGan = new ngeGrabGan2();
