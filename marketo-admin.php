<?php
class MarketoAdmin {
	private $marketoConnector;
	private $marketoEmbedTypes = array(
			'disable'	=>'Disable',
			'simple'	=>'Simple',
			'asynchronous'=>'Asynchronous',
			'asynchronousjquery'=>'Asynchronous Jquery',);
	
	public function __construct( $marketoConnector ){
		$this->marketoConnector = $marketoConnector;
		$this->init();	
	}
	
	private function init(){
		add_action( 'add_meta_boxes', array($this, 'add_shortcode_box') );
		add_action( 'admin_menu', array( $this,'admin_menu' ));
	}
	
	public function admin_menu(){
		$page = add_menu_page(
			__('Marketo Connector', 'marketoconnector-public'),
			__('Marketo Connector', 'marketoconnector-public'),
			'manage_options',
			'marketo_options',
			array(
				$this,
				'options_form'
			)
		);
	}
	
	public function options_form (){
		
		if(isset( $_POST['action'] )){
			if ( ! isset( $_POST['marketo-settings-nonce'] ) || ! wp_verify_nonce( $_POST['marketo-settings-nonce'], 'marketo-settings-form' )){
				$this->set_notification( __("Something wrong with the submitted data.", 'marketoconnector-public'), "error" );
			} else {
				//passed the nonce...
				switch ( $_POST['action'] ){
					case 'general_settings' :
						$options = array(
							'marketo_munchkin_code' => sanitize_text_field( $_POST['marketo_munchkin_code']),
							'marketo_instance_name' => sanitize_text_field( $_POST['marketo_instance_name']),
							'embed_munchkin_code'	=> sanitize_text_field( $_POST['embed_munchkin_code']) );
						//data validation...
						if ( !in_array($options['embed_munchkin_code'], array_flip( $this->marketoEmbedTypes )) ) {
							$this->set_notification( __("Embed Munchkin code value is illegal.", 'marketoconnector-public'), "error" );
						} else {
							//successful validation...
							if ( $this->marketoConnector->update_settings( $options ) )
								$this->set_notification( __("Successfully updated Marketo Settings.", 'marketoconnector-public'), "updated" ); else
								$this->set_notification( __("Error was found while updating Marketo Settings.", 'marketoconnector-public'), "error" );
						}
						break;
				}
			}
		}
		
		$tabs = array(
				'general' => __('General Settings', 'marketoconnector-public'),
				'campaign' => __('Blog Update', 'marketoconnector-public'),
				'subscriber_sync' => __('Wordpress Login', 'marketoconnector-public'),
				'custom_posts' => __('Marketo Based Custom Posts', 'marketoconnector-public')
		);
		$current_tab = ( isset($_GET['tab']) && isset( $tabs[$get_tab = strtolower($_GET['tab'] )]	) ) ? $get_tab : 'general';
		$current_tab = isset( $override_tab ) ? $override_tab : $current_tab;
		
		$settings = $this->marketoConnector->settings();
		?><div class="wrap">
<h2>Marketo Connector for WordPress Options
	<a href="http://hooshmarketing.com.au/internal/wordpress-free-plugin-documentation/" target="_blank" title="<?php echo __('Need Help? Click here.', 'marketoconnector-public'); ?>">
		<button style="font-size:12px;float:right;"><?php echo __('Help', 'marketoconnector-public'); ?></button>
	</a>
</h2><?php 
		if( isset($this->notification->message) ){
			echo '<div style="width:95%;padding:5px;margin-left:0px;" class="'.$this->notification->type.'"><p>' . $this->notification->message . '</p></div>';
			if( ! $this->notification->sticky)
				$this->notification = '';
		} ?><h2 class="nav-tab-wrapper">
			<?php foreach( $tabs as $tab => $name ): $class = ( $tab == $current_tab ) ? 'nav-tab-active' : ''; ?>
	        <a class="nav-tab <?php echo $class; ?>" href="?page=marketo_options&tab=<?php echo $tab; ?>"><?php echo $name; ?></a>
	        <?php endforeach; ?>
		</h2>
		<?php if( $current_tab == 'general'): ?>
		<form method="post">
			<input type="hidden" name="action" value="general_settings" />
			<?php wp_nonce_field('marketo-settings-form', 'marketo-settings-nonce'); ?>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="marketo_munchkin_code"><?php echo __('Munchkin Account ID', 'marketoconnector-public'); ?>:</label>
					</th>
					<td>
						<input type="text" class="regular-text" name="marketo_munchkin_code" id="marketo_munchkin_code" value="<?php echo $settings->marketo_munchkin_code; ?>"/>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="marketo_instance_name"><?php echo __('Marketo Instance Name', 'marketoconnector-public'); ?>:</label>
					</th>
					<td>
						<input type="text" class="regular-text" name="marketo_instance_name" id="marketo_instance_name" value="<?php echo $settings->marketo_instance_name; ?>"/>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="embed_munchkin_code"><?php echo __('Munchkin Embed Type', 'marketoconnector-public'); ?>:</label>
					</th>
					<td>
						<select name="embed_munchkin_code" id="embed_munchkin_code">
					<?php foreach ( $this->marketoEmbedTypes as $key=>$type ): ?>
							<option value="<?php echo $key; ?>" <?php echo ($key == $settings->embed_munchkin_code ? "selected" : ""); ?>><?php echo $type; ?></option>	
					<?php endforeach; ?></select>
					</td>
				</tr>
				<tr>
					<th scope="row">
					</th>
					<td>
						<input type="submit" class="button-primary" value="<?php echo __('Submit', 'marketoconnector-public'); ?>" />
					</td>
				</tr>
			</table>
		</form>
		<?php else: ?>
			<p><?php echo sprintf(__('Upgrade to the full version for this functionality %s.', 'marketoconnector-public'), '<a href="https://launchpoint.marketo.com/hoosh-marketing/1181-wordpress-integration-for-marketo/" target="_blank">' . __('here', 'marketoconnector-public') . '</a>'); ?>
		<?php endif; ?>		
</div><?php 
	}
	
	private function set_notification($message, $type='updated', $sticky=TRUE){
		$this->notification = (object)array(
				'type'		=> $type,
				'message'	=> $message,
				'sticky'	=> $sticky);
	}
	
	public function add_shortcode_box(){
		$screens = array( 'post', 'page');
		$marketo_field_meta = array();
		
		foreach ( $screens as $screen ) {
			add_meta_box(
			'marketo_shortcode_meta',
			__( 'Marketo Connector for Wordpress Shortcode Generator', 'marketoconnector-public' ),
			array( $this, 'meta_box_callback' ),
			$screen
			);
			if (isset($marketo_field_meta[$screen])){
				//this is a custom post having a meta box...
				add_meta_box( 'marketo_meta_field',
				__( 'Marketo Connector for Wordpress Fields', 'marketoconnector-public' ),
				array( $this, 'marketo_field_box_callback'),
				$screen,
				'normal',
				'high',
				array( 'fields'=>$marketo_field_meta[$screen] )
				);
			}
		}
	}
	
	public function meta_box_callback(){
		$marketo_common_fieldnames = array( 'City', 'Country', 'Email', 'InferredCountry', 'FirstName', 'LastName', 'MobilePhone', 'Phone', 'State', 'Title', 'Website' );
		$known_field_actions = array( 'show'=>'Show', 'hidden'=>'Hide', 'readonly'=>'Set as Read-only')
		?>
<h2 class="marketo-nav-tab-wrapper nav-tab-wrapper">
	<a href="#" class="nav-tab nav-tab-active" data-id="mform">mForm</a>
	<a href="#" class="nav-tab" data-id="getlead">Get Lead</a>
	<a href="#"	class="nav-tab" data-id="mseg">mSeg</a>
	<a href="#"	class="nav-tab" data-id="getcustompost">getCustomPost</a>
	<a href="#"	class="nav-tab" data-id="custompostdata">customPostData</a>
</h2>
<div id="shortcode-content-mform"
	class="shortcode-content">
	<p><?php echo __('This shortcode is used to convert an HTML form in WordPress into a Marketo enabled form which will push/update leads in Marketo.', 'marketoconnector-public'); ?></p>
	<p><?php echo sprintf(__('There are other parameters that can be used (ie pre-filling and progressive profiling) and are present in the %s of this Plugin', 'marketoconnector-public'), '<a href="https://launchpoint.marketo.com/hoosh-marketing/1181-wordpress-integration-for-marketo/" target="_blank">Premium Edition</a>'); ?></p>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label for="marketo-shortcode-mform-formid"><?php echo __('Form ID', 'marketoconnector-public'); ?>:</label>
			</th>
			<td>
				<input type="text" class="regular-text"	id="marketo-shortcode-mform-formid" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php echo __('Closing Tag', 'marketoconnector-public'); ?>
			</th>
			<td>
				<label for="marketo-shortcode-mform-closing">
					<input type="checkbox" id="marketo-shortcode-mform-closing" checked />
					<?php echo __('Add Closing Tag', 'marketoconnector-public'); ?>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php echo __('Shortcode Preview', 'marketoconnector-public'); ?>
			</th>
			<td><pre id="shortcode-preview-mform">[mForm formid=""]
[/mForm]</pre>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"></th>
			<td>
				<button class="button button-large button-primary insert-marketo-shortcode" data-id="mform">
					<?php echo __('Insert Shortcode', 'marketoconnector-public'); ?>
				</button>
			</td>
		</tr>
	</table>
</div>
<div id="shortcode-content-getlead" class="shortcode-content" style="display:none;">
	<p><?php echo __('This is used to extract field values from the Lead record in Marketo. It functions the same way as lead tokens in Marketo', 'marketoconnector-public'); ?></p>
	<p><?php echo sprintf(__('This feature is only available in the %s.', 'marketoconnector-public'), '<a href="https://launchpoint.marketo.com/hoosh-marketing/1181-wordpress-integration-for-marketo/" target="_blank">Premium Version</a>'); ?></p> 
</div>
<div style="display: none;" id="shortcode-content-mseg"
	class="shortcode-content">
	<p><?php echo __('This shortcode is used to show/hide content based on the values of Marketo Fields. It works similarly to dynamic content based on segmentation in Marketo.', 'marketoconnector-public'); ?></p>
	<p><?php echo sprintf(__('This feature is only available in the %s.', 'marketoconnector-public'), '<a href="https://launchpoint.marketo.com/hoosh-marketing/1181-wordpress-integration-for-marketo/" target="_blank">Premium Version</a>'); ?></p>
</div>
<?php 
	
?>
<div style="display: none;" id="shortcode-content-getcustompost" class="shortcode-content">
	<p><?php echo __('This shortcode is used in conjunction with Marketo Based Custom Posts or Types Plugin to show or hide "Custom Posts" based on values of Marketo Fields.', 'marketoconnector-public')?></p>
	<p><?php echo sprintf(__( 'This feature is only available in the %s.', 'marketoconnector-public'), '<a href="https://launchpoint.marketo.com/hoosh-marketing/1181-wordpress-integration-for-marketo/" target="_blank">Premium Version</a>'); ?></p>
</div>
<div style="display: none;" id="shortcode-content-custompostdata" class="shortcode-content">
	<p><?php echo __('This shortcode outputs the custom post information as picked by the getCustomPost Shortcode. This should be placed inside the getCustomPost shortcode block.', 'marketoconnector-public'); ?></p>
	<p><?php echo sprintf(__('This feature is only available in the %s.', 'marketoconnector-public'), '<a href="https://launchpoint.marketo.com/hoosh-marketing/1181-wordpress-integration-for-marketo/" target="_blank">Premium Version</a>'); ?></p>
</div>
	<style>
	.marketo-nav-tab-wrapper {
		padding: 0 0 0px !important;
	}
	</style>
	<script>
			var shortcode_generator = {
				init: function(){

					jQuery('#marketo-shortcode-mform-formid').keyup(function(){
						jQuery('#shortcode-preview-mform').text(shortcode_generator.generate_mform());
					});
					jQuery('#marketo-shortcode-mform-closing').on('change', function(){
						jQuery('#shortcode-preview-mform').text(shortcode_generator.generate_mform());
					});
				},
				generate_mform: function(){
					var content = '[mForm formid="%formid%"]';
					content = content.replace(/%formid%/i, jQuery('#marketo-shortcode-mform-formid').val());
					content += jQuery('#marketo-shortcode-mform-closing').prop('checked') ? "\n" + "[/mForm]" : "";
					return content; 
				},
				generate_mseg: function(){
					
				},
				generate_getcustompost: function(){
					
				},
				generate_custompostdata: function(){
					
				},
			};
			jQuery(document).ready(function(){
				shortcode_generator.init();
				jQuery('.nav-tab').on('click', function(e){
					e.preventDefault();
					jQuery('.shortcode-content').hide();
					jQuery('#shortcode-content-'+jQuery(this).data('id')).show();
					jQuery('.nav-tab').removeClass('nav-tab-active');
					jQuery(this).addClass('nav-tab-active');
					return false;
				});
				jQuery('.insert-marketo-shortcode').on('click', function(e){
					e.preventDefault();
					content = jQuery('#shortcode-preview-'+jQuery(this).data('id')).text();
					window.parent.send_to_editor(content);
					return false;
				});
			});
			</script>
	<?php 
	}
}