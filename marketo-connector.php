<?php
class MarketoConnector {
	const MARKETO_CONNECTOR_VERSION = '1.0.0';
	const MARKETO_MFORM_ACTIONURL_PREFIX = 'http://';
	const MARKETO_MFORM_ACTIONURL_SUFFIX = '.mktoweb.com/index.php/leadCapture/save';
	private $cookie=null;
	
	public function __construct(){
		if (isset($_COOKIE['_mkto_trk'])){
			$this->cookie = $_COOKIE['_mkto_trk'];
		}
		$this->init();
	}
	private function init(){
		add_action( 'wp_footer', array($this, 'footer') );
		add_shortcode( 'mForm', array($this,'shortcode_mform'));
	}
	
	public function footer(){

		$settings = $this->settings();
		if ( ! isset($settings->embed_munchkin_code) ){
			return;
		}
		if ($settings->embed_munchkin_code==='disabled') {
			return;
		}
		if ( ! isset($settings->marketo_munchkin_code) || $settings->marketo_munchkin_code == ""){
			return;
		}
		$munchkin_code='';
		$munchkin_code2='';
		if ($settings->embed_munchkin_code === 'simple') {
			$munchkin_code = "document.write(unescape(\"%3Cscript src='//munchkin.marketo.net/munchkin.js' type='text/javascript'%3E%3C/script%3E\"));";
			$munchkin_code2 = "Munchkin.init('{$settings->marketo_munchkin_code}', {\"wsInfo\":\"j1RQ\"});"; 
		} elseif ( $settings->embed_munchkin_code === 'asynchronous' ) {
			$munchkin_code = "
(function() {
	var didInit = false;
	function initMunchkin() {
		if(didInit === false) {
			didInit = true;
			Munchkin.init('{$settings->marketo_munchkin_code}', {\"wsInfo\":\"j1RQ\"});
		}
	}
	var s = document.createElement('script');
	s.type = 'text/javascript';
	s.async = true;
	s.src = '//munchkin.marketo.net/munchkin.js';
	s.onreadystatechange = function() {
		if (this.readyState == 'complete' || this.readyState == 'loaded') {
			initMunchkin();
		}
	};
		s.onload = initMunchkin;
		document.getElementsByTagName('head')[0].appendChild(s);
		})();
";
		} elseif ($this->embed_munchkin_code === 'asynchronousjquery') {
			$munchkin_code = "
jQuery().ajax({
	url: '//munchkin.marketo.net/munchkin.js',
	dataType: 'script',
	cache: true,
	success: function() {
		Munchkin.init('{$settings->marketo_munchkin_code}', {\"wsInfo\":\"j1RQ\"});
	}
});					
";	
		}?><script type="text/javascript"><?php echo $munchkin_code; ?></script>
		<script><?php echo $munchkin_code2; ?></script>
		<?php
	}
	
	public function activate(){
		if( $settings = $this->settings() ){
			//perform upgrade functions here
			$settings->version = self::MARKETO_CONNECTOR_VERSION;
			update_option ( 'marketo_conn_settings', $settings );
		} else {
			$settings = (object)array(
					'version' 				=> self::MARKETO_CONNECTOR_VERSION,
					'embed_munchkin_code'	=> 'simple',
					'marketo_munchkin_code'	=> '',
					'marketo_instance_name' => ''
					);
			add_option ( 'marketo_conn_settings', $settings );
		}
	}
	
	public function deactivate(){
		
	}
	
	public static function settings( $key=NULL ){
		$settings = get_option ( 'marketo_conn_settings' );
		if ( !is_null($key) ){
			if( isset($settings->$key) )
				return $settings->$key; else
				return null;
		}
		return $settings;	
	}
	
	public static function update_settings ( $options ){
		$settings = get_option ( 'marketo_conn_settings' );
		foreach ( $options as $key => $option ){
			$settings->$key = $option;
		}
		return update_option ( 'marketo_conn_settings', $settings );
	}
	
	public static function map_marketo_name($name, &$matched = false){
	
		$matchedName = $name;
		$patterns['FirstName']='@^(first)?[ _\-]*name$@i';
		$patterns['LastName']='@^last[ _\-]*name$@i';
		$patterns['Email']='@^e(-)?mail[ _\-]*(address)?$@i';
		$patterns['Phone']='@(tele)?phone@i';
		$patterns['MobilePhone']='@^(Mobile)|(mob)|(cell[ _\-]*phone)$@i';
		$patterns['Country']='@^country$@i';
		$patterns['Company']='@^company[ _\-]*(name)?$@i';
		$patterns['City']='@^city$@i';
		$patterns['State']='@^state$@i';
		$patterns['Title']='@^(job)?[ _\-]*(title|role)$@i';
		$patterns['Website']='@^web[ _\-]*site[ _-]*(address)?$@i';
		foreach($patterns as $patternName=>$pattern){
			if (preg_match($pattern,trim($name))===1){
				$matchedName = $patternName;
				$matched = 1;
				break;
			}
		}
		return $matchedName;
	}
	
	public function get_marketo_tracking_cookie(){
		if ( !is_null($this->cookie) && is_string($this->cookie)){
			return $this->cookie;
		}
		return false;
	}
	
	public function shortcode_mform($args, $content, $processNonMappedNodes = true){
		$settings = $this->settings();
		$valid_args = array(
				'formid',
		);
		foreach (array_keys($args) as $arg){
			if (!in_array( strtolower($arg),$valid_args )){
				return __( sprintf( "Parameter %s is not supported for mForm shortcode", $arg) );
			}
		}
		
		if ( $settings->marketo_instance_name == '' )
			return $content;
		
		$previous_value = libxml_use_internal_errors(TRUE);
		$doc = new DOMDocument();
		$doc->loadHTML( do_shortcode($content) );
		$selector = new DOMXPath($doc);
	
		$fieldnodelist = $selector->query('//input | //textarea | //select');
		$neverPrefill = array();
        $nodearray = array();
		
		for ($i=0;$i<$fieldnodelist->length;$i++){
			$nodearray[]=$fieldnodelist->item($i);
		}
		
		$labels = array();
		$labels_tags = $selector->query('//label');
		libxml_clear_errors();
		libxml_use_internal_errors($previous_value);
		
		foreach( $nodearray as $node ){
			$matched = false;
			$node->setAttribute(
				'name',
				$name = $this->map_marketo_name(
					$node->getAttribute('name'),
					$matched
				)
			);
		}	
		$forms = $doc->getElementsByTagName('form');
		$mform = $forms->item(0);
		$this->mform_add_hidden_inputs( $mform,	$args['formid'], $settings->marketo_munchkin_code, $doc );
		if (isset($args['formid'])) {
			$mform_action_url =	self::MARKETO_MFORM_ACTIONURL_PREFIX . $settings->marketo_instance_name . self::MARKETO_MFORM_ACTIONURL_SUFFIX;
			$mform->setAttribute( 'action', $mform_action_url);
		}
		return $mform->C14N(false,true);
	}
										
	private function mform_add_hidden_inputs( &$mform, $formId, $munchkinAccountId, $doc ){
		$cookie = $this->get_marketo_tracking_cookie();
		$hiddeninputs = array(
				array(
						'name' => 'formid',
						'value' => $formId,
				),
				array(
						'name' => 'munchkinId',
						'value' => $munchkinAccountId,
				),
				array(
						'name' => '_mkt_trk',
						'value' => is_string($cookie)?$cookie:'',
				),
				array(
						'name' => 'returnURL',
						'value' => $mform->getAttribute('action'),
				)
		);
		foreach ($hiddeninputs as $hiddeninput){
			$hiddeninputnode = $doc->createElement('input');
			$hiddeninputnode->setAttribute('name', $hiddeninput['name']);
			$hiddeninputnode->setAttribute('type', 'hidden');
			$hiddeninputnode->setAttribute('value', $hiddeninput['value']);
			$mform->appendChild($hiddeninputnode);
		}
	
	}
}