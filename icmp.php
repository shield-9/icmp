<?php
/*
 * Plugin Name: Member Post Type for I-CAS
 * Plugin URI: http://www.extendwings.com/contact/
 * Description: This plugin is developed by Extend Wings
 * Version: 0.1
 * Author: Daisuke Takahashi(Extend Wings)
 * Author URI: http://www.extendwings.com
 * License: Limited to internal use only. Not GPL!
 * Text Domain: icmp
 * Domain Path: /languages/
*/

if(!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

add_action('init', array('IC_member_page', 'init'));
class IC_member_page {
	static $instance = null;

	function init() {
		if(!self::$instance) {
			/*
			if(did_action('plugins_loaded'))
				self::plugin_textdomain();
			else
				add_action('plugins_loaded', array(__CLASS__, 'plugin_textdomain'));
			*/

			self::$instance = new IC_member_page;
		}
		return self::$instance;
	}

	function __construct() {
		$this->register_post_types();
		add_action('save_post',			array(&$this, 'save_meta_box_data'), 10, 2);
		//add_action('add_meta_boxes',		array(&$this, 'add_meta_boxes'));
		add_action('admin_print_scripts',	array(&$this, 'admin_enqueue_scripts'));
		
		add_filter('manage_icmp_member_posts_columns',	array(&$this, 'manage_post_types_columns'), 9);
		add_action('manage_posts_custom_column',	array($this, 'manage_post_types_columns_output'), 10, 2);

		add_shortcode('members',		array(&$this, 'shortcode_members'));
		add_action('wp_enqueue_scripts',	array(&$this, 'shortcode_assets'));
	}

	function register_post_types() {
		$labels = array(
			'name'			=> __('Members', 'icmp'),
			'singular_name'		=> __('Member', 'icmp'),
			'add_new'		=> __('Add New', 'icmp'),
			'add_new_item'		=> __('Create New Member', 'icmp'),
			'edit'			=> __('Edit', 'icmp'),
			'edit_item'		=> __('Edit Member', 'icmp'),
			'new_item'		=> __('New Member', 'icmp'),
			'view'			=> __('View Member', 'icmp'),
			'view_item'		=> __('View Member', 'icmp'),
			'search_items'		=> __('Search Members', 'icmp'),
			'not_found'		=> __('No members found', 'icmp'),
			'not_found_in_trash'	=> __('No members found in Trash', 'icmp'),
			'parent_item_colon'	=> __('Parent Member:', 'icmp')
		);
		register_post_type('icmp_member', array(
			'labels'		=> $labels,
			'rewrite'		=> array('slug' => 'member', 'with_front' => false),
			'supports'		=> array('title'),
			'menu_position'		=> 20,
			'public'		=> false,
			'show_ui'		=> true,
			'can_export'		=> true,
			'capability_type'	=> 'post',
			'hierarchical'		=> false,
			'query_var'		=> true,
			'menu_icon'		=> 'dashicons-businessman',
			'register_meta_box_cb'	=> array(&$this, 'add_meta_boxes'),
		));
	}

	function admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script('icmp_media-uploader', plugins_url('media-uploader.js', __FILE__), array('jquery'), '0.1', false);
		wp_localize_script('icmp_media-uploader', 'icmp_l10n', array(
			'title'		=> __('Choose Profile Image', 'icmp'),
			'button'	=> __('Profile Image', 'icmp'),
		));
	}

	function add_meta_boxes() {
		add_meta_box('member-info', __('Member Info', 'icmp'), array(&$this, 'meta_box_member_info'), 'icmp_member', 'normal');
		add_meta_box('modal-switcher', __('Modal', 'icmp'), array(&$this, 'meta_box_modal_switcher'), 'icmp_member', 'side');
	}

	function meta_box_member_info() {
		global $post;
		
		$value = get_post_meta($post->ID, '_icmp_member_info', true);
		
		if(WP_DEBUG && WP_DEBUG_DISPLAY):
		?>
		<pre><?php print_r($value); ?></pre>
		<?php endif; ?>
		<?php wp_nonce_field('icmp_meta_box', 'icmp_meta_box_nonce'); ?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="icmp[romanized-name]"><?php _e('Romanized Name', 'icmp'); ?></label>
				</th>
				<td>
					<input type="text" id="icmp[romanized-name]" name="icmp[romanized-name]" value="<?php echo esc_attr($value['romanized-name']); ?>" size="16" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[avatar-url]"><?php _e('Profile Image', 'icmp'); ?></label>
				</th>
				<td>
					<input id="icmp[avatar-url]" name="icmp[avatar-url]" value="<?php echo esc_attr($value['avatar-url']); ?>" class="widefat" size="100" />
					<button id="icmp[avatar-select]"><?php _e('Select Profile Image', 'icmp'); ?></button>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[position]"><?php _e('Position', 'icmp'); ?></label>
				</th>
				<td>
					<input type="text" id="icmp[position]" name="icmp[position]" value="<?php echo esc_attr($value['position']); ?>" size="16" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[type]"><?php _e('Type', 'icmp'); ?></label>
				</th>
				<td>
					<select id="icmp[type]" name="icmp[type]">
						<option value="trustee"<?php selected($value['type'], 'trustee'); ?>><?php _e('Trustee', 'icmp'); ?></option>
						<option value="steering"<?php selected($value['type'], 'steering'); ?>><?php _e('Steering Committee', 'icmp'); ?></option>
						<option value="others"<?php selected($value['type'], 'others'); ?>>Others</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[belonging]"><?php _e('Belonging', 'icmp'); ?></label>
				</th>
				<td>
					<input type="text" id="icmp[belonging]" name="icmp[belonging]" value="<?php echo esc_attr($value['belonging']); ?>" size="16" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[joined]"><?php _e('Joined Period', 'icmp'); ?></label>
				</th>
				<td>
					<input type="text" id="icmp[joined]" name="icmp[joined]" value="<?php echo esc_attr($value['joined']); ?>" size="16" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[catch-phrase]"><?php _e('Catch Phrase', 'icmp'); ?></label>
				</th>
				<td>
					<input type="text" id="icmp[catch-phrase]" name="icmp[catch-phrase]" value="<?php echo esc_attr($value['catch-phrase']); ?>" size="52" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[appeal]"><?php _e('I-CAS Appeal', 'icmp'); ?></label>
				</th>
				<td>
					<textarea id="icmp[appeal]" name="icmp[appeal]" class="large-text" rows="5" cols="50"><?php echo esc_attr($value['appeal']); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[rewards]"><?php _e('Rewards', 'icmp'); ?></label>
				</th>
				<td>
					<textarea id="icmp[rewards]" name="icmp[rewards]" class="large-text" rows="5" cols="50"><?php echo esc_attr($value['rewards']); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[comment][title]"><?php _e('Comment Title', 'icmp'); ?></label>
				</th>
				<td>
					<input type="text" id="icmp[comment][title]" name="icmp[comment][title]" value="<?php echo esc_attr($value['comment']['title']); ?>" size="16" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="icmp[comment][body]"><?php _e('Comment Body', 'icmp'); ?></label>
				</th>
				<td>
					<textarea id="icmp[comment][body]" name="icmp[comment][body]" class="large-text" rows="5" cols="50"><?php echo esc_attr($value['comment']['body']); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	function meta_box_modal_switcher() {
		global $post;
		
		$value = get_post_meta($post->ID, '_icmp_member_info', true);
		?>
		<p>
			<label for="icmp[modal]">
				<input type="checkbox" class="widefat" id="icmp[modal]" name="icmp[modal]" value="1"<?php if($value['comment']['title']) echo ' checked'; ?>> <?php _e('Turn on Modal', 'icmp'); ?>
			</label>
		</p>
		<?php
	}

	function save_meta_box_data($post_id, $post) {
		/* Security Checkpoint */
		/*
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;
		*/

		if(wp_is_post_revision($post_id) || $post->post_type != 'icmp_member' || !current_user_can( 'edit_post', $post_id))
			return;

		if(!isset($_POST['icmp_meta_box_nonce']) || !wp_verify_nonce($_POST['icmp_meta_box_nonce'], 'icmp_meta_box'))
			return;

		/* Update/Insert Post Meta */
		if(empty($_POST['icmp'])) {
			delete_post_meta($post_id, '_icmp_member_info');
			return;
		}

		// Sanitize user input.
		//$my_data = sanitize_text_field($_POST['icmp']);
		$_POST['icmp']['romanized-name']	= esc_html($_POST['icmp']['romanized-name']);
		$_POST['icmp']['avatar-url']		= esc_url_raw($_POST['icmp']['avatar-url'], array('http', 'https', 'ftp', 'ftps'));
		$securite = $_POST['icmp'];

		// Update the meta field in the database.
		update_post_meta($post_id, '_icmp_member_info', $securite);
		delete_post_meta($post_id, '_icmp_member_type');
	}

	function manage_post_types_columns($columns) {
		$columns = array_slice( $columns, 0, 2, true ) + array( 'icmp_member_type' => __( 'Type', 'icmp' ) ) + array_slice( $columns, 2, null, true );
		return $columns;
	}

	function manage_post_types_columns_output($column, $post_id) {
		switch($column) {
			case 'icmp_member_type':
				$meta = get_post_meta(get_the_ID(), '_icmp_member_info', true);
				$labels = array(
					'trustee'	=> __('Trustee', 'icmp'),
					'steering'	=> __('Steering Committee', 'icmp'),
					'others'	=> __('Others', 'icmp'),
				);
				echo $labels[$meta['type']];
				break;
		}
	}

	static function shortcode_members($attr, $content) {
		ob_start();
		$trustees = new WP_Query(array(
			'post_type'		=> 'icmp_member',
			'posts_per_page'	=> -1,
			'orderby'		=> 'ID',
			'order'			=> 'ASC',
			'meta_query'		=> array(
				array(
					'key'		=> '_icmp_member_info',
					'value'		=> 's:4:"type";s:7:"trustee";',
					'compare'	=> 'LIKE',
				),
			),
		));

		if($trustees->have_posts()):
			?>
			<h2><?php _e('Trustees', 'icmp'); ?></h2>
			<div class="gallery-style clearfix">
			<?php
				while($trustees->have_posts()) : $trustees->the_post();
					self::item_box(get_the_ID());
				endwhile;
			?>
			</div>
			<?php
		endif;

		$steerings = new WP_Query(array(
			'post_type'		=> 'icmp_member',
			'posts_per_page'	=> -1,
			'orderby'		=> 'ID',
			'order'			=> 'ASC',
			'meta_query'		=> array(
				array(
					'key'		=> '_icmp_member_info',
					'value'		=> ';s:8:"steering";',
					'compare'	=> 'LIKE',
				),
			),
		));

		if($steerings->have_posts()):
			?>
			<h2><?php _e('Steerings', 'icmp'); ?></h2>
			<div class="gallery-style clearfix">
			<?php
				while($steerings->have_posts()) : $steerings->the_post();
					self::item_box(get_the_ID());
				endwhile;
			?>
			</div>
			<?php
		endif;

		$others = new WP_Query(array(
			'post_type'		=> 'icmp_member',
			'posts_per_page'	=> -1,
			'orderby'		=> 'ID',
			'order'			=> 'ASC',
			'meta_query'		=> array(
				array(
					'key'		=> '_icmp_member_info',
					'value'		=> ';s:6:"others";',
					'compare'	=> 'LIKE',
				),
			),
		));

		if($others->have_posts()):
			?>
			<h2><?php _e('Others', 'icmp'); ?></h2>
			<div class="gallery-style clearfix">
			<?php
				while($others->have_posts()) : $others->the_post();
					self::item_box(get_the_ID());
				endwhile;
			?>
			</div>
			<?php
		endif;

		$members = new WP_Query(array(
			'post_type'		=> 'icmp_member',
			'posts_per_page'	=> -1,
			'orderby'		=> 'ID',
			'order'			=> 'ASC',
			'meta_query'		=> array(
				array(
					'key'		=> '_icmp_member_info',
					'value'		=> 's:5:"modal";s:1:"1";',
					'compare'	=> 'LIKE',
				),
			),
		));

		if($members->have_posts()):
			while($members->have_posts()) : $members->the_post();
				self::print_modal(get_the_ID());
			endwhile;
		endif;

		wp_reset_postdata();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function item_box($post_id) {
		$meta = get_post_meta($post_id, '_icmp_member_info', true);
		?>
		<div class="g_item clearfix">
			<a href="javascript:void(0)" data-toggle="modal" data-target="#member-modal-<?php echo $post_id; ?>">
				<img alt="Avatar" src="<?php echo self::photon_url($meta['avatar-url'], array('w' => '198px', 'crop' => '0,0,100,198px')); ?>">
			</a>
			<p class="name"><?php the_title(); ?></p>
			<?php if($meta['position']): ?>
				<p class="position"><?php echo $meta['position']; ?></p>
			<?php endif; ?>
			<?php if($meta['belonging']): ?>
				<p class="belonging"><?php echo $meta['belonging']; ?></p>
			<?php endif; ?>
			<?php if($meta['catch-phrase']): ?>
				<p class="motto"><?php echo $meta['catch-phrase']; ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
	function print_modal($post_id) {
		$meta = get_post_meta($post_id, '_icmp_member_info', true);
		?>
		<div class="modal fade" id="member-modal-<?php echo $post_id; ?>">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">
							<?php the_title(); ?>
							<?php if($meta['romanized-name']): ?>
								- <?php echo $meta['romanized-name']; ?>
							<?php endif; ?>
						</h4>
					</div>
					<div class="modal-body">
						<img alt="Avatar" src="<?php echo self::photon_url($meta['avatar-url'], array('w' => '198px')); ?>">
						<div class="desc">
							<dl>
								<?php if($meta['position']): ?>
									<dt>役職</dt>
										<dd><?php echo $meta['position']; ?></dd>
								<?php endif; ?>
								<?php if($meta['belonging']): ?>
									<dt>大学</dt>
										<dd><?php echo $meta['belonging']; ?></dd>
								<?php endif; ?>
								<?php if($meta['joined']): ?>
									<dt>加入時期</dt>
										<dd><?php echo $meta['joined']; ?></dd>
								<?php endif; ?>
								<?php if($meta['catch-phrase']): ?>
									<dt>意気込み</dt>
										<dd class="catch-phrase"><?php echo $meta['catch-phrase']; ?></dd>
								<?php endif; ?>
								<?php if($meta['appeal']): ?>
									<dt>I-CASの魅力</dt>
										<dd><?php echo $meta['appeal']; ?></dd>
								<?php endif; ?>
								<?php if($meta['rewards']): ?>
									<dt>やりがいを感じるとき</dt>
										<dd><?php echo $meta['rewards']; ?></dd>
								<?php endif; ?>
								<?php if($meta['comment']['title'] && $meta['comment']['body']): ?>
									<dt><?php echo $meta['comment']['title']; ?></dt>
										<dd><?php echo $meta['comment']['body']; ?></dd>
								<?php endif; ?>
							</dl>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	static function shortcode_assets($hook) {
		wp_enqueue_script('icmp_modal', plugins_url('modal.js', __FILE__), array('jquery'), '0.1', true);
		wp_enqueue_style('icmp_modal', plugins_url('modal.css', __FILE__), null, '0.1');
		wp_enqueue_style('icmp', plugins_url('icmp.css', __FILE__), array('icmp_modal'), '0.1');
	}

	static function photon_url($image_url, $args) {
		$image_url = trim($image_url);

		if(empty($image_url))
			return $image_url;

		$image_url_parts = @parse_url($image_url);

		// Unable to parse
		if(!is_array($image_url_parts) || empty($image_url_parts['host']) || empty($image_url_parts['path']))
			return $image_url;

		if(is_array($args)){
			foreach($args as $arg => $value) {
				if(is_array($value))
					$args[$arg] = implode(',', $value);
			}

			$args = rawurlencode_deep($args);
		}

		if(in_array($image_url_parts['host'], array('i0.wp.com', 'i1.wp.com', 'i2.wp.com'))) {
			$photon_url = add_query_arg($args, $image_url);

			return self::photon_url_scheme($photon_url, $scheme);
		}


		$image_host_path = $image_url_parts['host'] . $image_url_parts['path'];

		srand(crc32($image_host_path));
		$subdomain = rand(0, 2);
		srand();

		$photon_url = "http://i{$subdomain}.wp.com/$image_host_path";

		if(isset($image_url_parts['query'])) {
			$photon_url .= '?q=' . rawurlencode($image_url_parts['query']);
		}

		if($args) {
			if(is_array($args)) {
				$photon_url = add_query_arg($args, $photon_url);
			} else {
				$photon_url .= '?' . $args;
			}
		}

		return self::photon_url_scheme($photon_url, $scheme);
	}

	static function photon_url_scheme($url, $scheme) {
		if(!in_array($scheme, array('http', 'https', 'network_path'))) {
			$scheme = is_ssl() ? 'https' : 'http';
		}

		if('network_path' == $scheme) {
			$scheme_slashes = '//';
		} else {
			$scheme_slashes = "$scheme://";
		}

		return preg_replace('#^[a-z:]+//#i', $scheme_slashes, $url);
	}
}
