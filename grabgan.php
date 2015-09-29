<?php
/*
	Plugin Name: Katanya Plugin Grab Image
	Plugin URI: http://www.febripratama.com
	Description: Katanya sih buat ngegrab
	Author: Pratama, Febri
	Version: 1.0
	Author URI: http://www.febripratama.com

*/

class ngeGrabGan{

	function __construct(){

		add_action( 'init',  array(&$this, 'keyword'));
		add_action("admin_menu", array(&$this, 'add_theme_menu_item'));
		add_action("admin_init", array(&$this, 'display_grabGan_fields'));
		add_action( 'grabgan_cron',  array(&$this,'postCron' ) );

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
		  'post_status'   => 'private',
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

		$my_query = new WP_Query( array( 'post_type' => 'kw') );

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

		$my_query = new WP_Query( array( 'post_type' => 'post') );

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

		$my_query = new WP_Query( array( 'post_type' => 'kw') );

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
		                         'post_type' => 'attachment'
		                     ) );

		return count( $posts );
	}

	public static function percentagePosted(){

		$total = (int)ngeGrabGan::totalKw();
		$posted = (int)ngeGrabGan::totalPosted();

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
					<li>Isi bing api jgn lupa, bingung ? mbah google belum tidur</li>
					<li>Untuk auto posting menggunakan cron job menggunakan <a href="https://wordpress.org/plugins/easycron/" target="_BLANK">easycron plugin</a>. Kalau pingin pas ada visitor baru post copykan script ini ke header.php : <?php highlight_string('<?php ngeGrabGan::randomPostAuto(); ?>'); ?> (Akan posting pada detik tertentu secara random jadi jika ada 100 visitor dalam 1 detik maka dia akan posting sebanyak itu)</li>
					<li>Untuk link cron job : <pre>http://www.web-agan.com/wp-cron.php</pre></li>
				</ol>
			</blockquote>
			<hr>
			<H4>Plugin Status</H4>
			Total Keywords : <?php echo ngeGrabGan::totalKw(); ?><br>
			Total Posted Keywords : <?php echo ngeGrabGan::totalPosted(); ?><br>
			Total Posts : <?php echo ngeGrabGan::totalPost(); ?><br>
			Total Attachments : <?php echo ngeGrabGan::totalAttachment(); ?><br> 				

			<br>
			Percentage kw and posted : <?php echo ngeGrabGan::percentagePosted(); ?> %
			<hr>
		    <form method="post" action="options.php">
		        <?php
		            settings_fields("section");
		            do_settings_sections("grabgan-options");      
		            submit_button(); 
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
	    	<input type="text" value="<?php echo get_option('grabgan.bingkey'); ?>" name="bingkey" style="width: 50%;" placeholder="Bing/Azure Client Key(kalau gk tau googling gan)" required>
	    <?php
	}

	function display_cron_element()
	{
		
		$on = get_option('grabgan.cron') == 'on' ? 'selected' : '';
		$off =(get_option('grabgan.cron') == 'off') || get_option('grabgan.cron') == null ? 'selected' : '';

		?>
			<select name="cron">
				<option value="on" <?php echo $on; ?>>On</option>
				<option value="off" <?php echo $off; ?>>Off</option>
			</select>
	    <?php
	}

	function kw_masuk_sini(){


		if (isset($_POST['kwlist']) == true) {

			if (trim($_POST['kwlist']) !== '') {

				$parse = preg_split('/\r\n|\r|\n/', $_POST['kwlist']);

	            $tot = count($parse);

	            for($i=0;$i<$tot;$i++){

	                if(ngeGrabGan::storeKw($parse[$i])){

	                	$w=$i;

	                }

	            }

			}

		}

		if (isset($_POST['cron'])) {

			 update_option('grabgan.cron', $_POST['cron']);
		}

		if (isset($_POST['bingkey'])) {

			 update_option('grabgan.bingkey', $_POST['bingkey']);
		}
	}

	function display_grabGan_fields()
	{
		add_settings_section("section", "All Settings", null, "grabgan-options");
		
		add_settings_field("grabgan.kwlist", "Keyword List<small><br>&nbsp;*keyword perbaris gan</small>", array(&$this, 'display_kw_element'), "grabgan-options", "section");
		add_settings_field("grabgan.bingkey", "Bing/Azure Client Key", array(&$this, 'display_bing_key_element'), "grabgan-options", "section");
		add_settings_field("grabgan.cron", "Cron Job", array(&$this, 'display_cron_element'), "grabgan-options", "section");

	    register_setting("section", "grabgan.kwlist",array(&$this, 'kw_masuk_sini'));
	}

	function add_theme_menu_item()
	{

		add_menu_page("grabGan Setting", "grabGan Setting", "manage_options", "grabgan-setting", array(&$this, 'theme_settings_page'), null, 99);

	}

/*

	bing search

*/
    public function httpCall($url = '', $referer = '', $post_call = FALSE, $postdata = '', $include_header = FALSE, $arr_curl_option = array()) {

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

    private function bingSearch($q, $filter = NULL, $top = 40, $skip = NULL) {

        $accountKey = get_option('grabgan.bingkey');

        $request = 'https://api.datamarket.azure.com/Bing/Search/Image?$format=json&$top=' . (int)$top . '&Query=' . urlencode('\'' . $q . '\'');
        if ($filter) $request = $request . '&ImageFilters=' . urlencode('\'' . $filter . '\'');
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

    public function generatePostTitle($kw){

		$blakang=array("Decorating Ideas", "Design Ideas","Decoration Ideas","Decor","Ideas","Design", "Gallery");

		return $title = $kw.' '.$blakang[rand ( 0 , count($blakang)-1 )];
    	
    }

    public function generateAttachtmentTitle($kw){

		$awal=array("New", "Cool", "Unique", "Nice", "Luxury", "Modest", "Awesome", "Amazing", "Fresh", "Popular", "Awesome", "Custom", "Modern", "Inspiring" , "Simple", "Classic", "Excellent", "Perfect", "Cute", "Cheap", "Impressive", "Best", "Trend", "Innovative", "Great" , "Wonderful", "Impressive", "Contemporary" );

		$tengah=array("Picture of", "Photo of", "Photos of", "Image of", "Images of");

		$akhir=array("Design", "Interior", "Exterior", "Ideas", "Remodelling", "Decoration", "Collection", "Style", "Concept", "Photography", "Creative", "Plans Free", "Decoration", "Minimalist", "Set", "Model", "Painting", "Property" );

		$blakang=array("Decorating Ideas", "Design Ideas","Decoration Ideas","Decor","Ideas","Design", "Gallery");

		$sambung=array("At","In","On","New On","Fresh at","New in","Fresh In","Fresh On","New At");

		return $title = $awal[rand ( 0 , count($awal)-1 )].' '.$tengah[rand ( 0 , count($tengah)-1 )].' '.$kw.' '.$akhir[rand ( 0 , count($akhir)-1 )].' '.$blakang[rand ( 0 , count($blakang)-1 )];
    	
    }

    public function generateTag($kw,$id){

		$payload='https://suggestqueries.google.com/complete/search?client=firefox&q='.urlencode($kw);

		$xmls = @file_get_contents($payload);
		
		$xmls = json_decode($xmls,true);

			$tags = array();

			for($i=0;$i<count($xmls[1]);$i++){

				$tags[$i] = $xmls[1][$i];

			}

    	wp_set_post_tags( $id, $tags, false );

    }

    public function newAuto($kw){

		//create new post
		$args = array(
		        'orderby'   => 'name',
		        'order'     => 'ASC',
		        'hide_empty'    => '0',
		  );

		$categories = get_categories($args);

    	$cat_id = self::getCat($categories[array_rand($categories)]->term_id);

    	$status = get_option('grabgan.status') !== null ? 'publish' : get_option('grabgan.status');

    	$data = array(

    		'post_title' => self::generatePostTitle($kw), 
    		'post_status' => $status

    	);

    	$post_id = wp_insert_post($data);

    	//set category
		$cat_ids = array( (int)$cat_id );

		$term_taxonomy_ids = wp_set_object_terms( $post_id, $cat_ids, 'category' );

    	//generate tag
    	self::generateTag($kw,$post_id);

    	//fetch bing api
		$results = self::bingSearch($kw.' ideas');

		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$i=0;
		$max=10;

		if(is_null($results)){
			
			return 2;

		}

		foreach ($results->d->results as $id => $value){

			$url = $value->MediaUrl;

			$uploads = wp_upload_dir();
			
			$title = self::generateAttachtmentTitle($kw);

			$filename = wp_unique_filename($uploads['path'], $url, $unique_filename_callback = NULL);

			$parent_post_id = $post_id;

			$wp_filetype = wp_check_filetype($filename, NULL);

			if (substr_count($wp_filetype['type'], "image")) {

	            $fullpathfilename = $uploads['path'] . "/" . $filename;

				$image_string = self::fetch_image($url);	

	            $fileSaved = @file_put_contents($uploads['path'] . "/" . $filename, $image_string);

				if ($fileSaved) {

		            $attachment = array(
		            	'post_mime_type' => $wp_filetype['type'], 
		            	'post_title' => $title, 
		            	'post_content' => '', 
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

        	return $error;

        }

        return 1;
    }

    public function postCron(){

		//ambil post type kw
		if(get_option('grabgan.bingkey') !== null){

			$my_query = new WP_Query( array( 'post_type' => 'kw') );

			if( $my_query->have_posts() ) {
			  while ($my_query->have_posts()) : $my_query->the_post(); 

			  		if(get_the_content() == 'true'){

			  			$startAuto = self::newAuto(get_the_title());

			  			if($startAuto == 1){

		  					    $my_post = array(
								    'ID'           => get_the_ID(),
								    'post_content' => 'false'
								);
								  
								wp_update_post( $my_post );

			  				return 'kw '.get_the_title().' : sukses, updating status';

			  			}else if($startAuto == 2){

			  				return 'Bing return null : check token gan';

			  			}else{

			  				return 'pokoknya error gan, bisa karna gambar code errornya :'.$startAuto;

			  			}

			  		}
			  		
			  		break;

			  endwhile;
			}

			wp_reset_query();    	

		}else{

			return 'Bing key is required';


		}

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
    
    public static function manualCron(){

    	return self::postCron();
    
    }

    private function fetch_image($url) {
        if (function_exists("curl_init")) {
            return self::curl_fetch_image($url);
        } elseif (ini_get("allow_url_fopen")) {
            return self::fopen_fetch_image($url);
        }
    }

    private function curl_fetch_image($url) {
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

$grabGan = new ngeGrabGan();