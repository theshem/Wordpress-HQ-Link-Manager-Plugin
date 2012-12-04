<?php
/*  
Copyright (c) 2012 Hashem Qolami. (http://qolami.com)

Plugin Name: HQ Link Manager
Plugin URI: http://wordpress.org/extend/plugins/
Description: Adding desirable attributes to the external links.

Version: 1.0 rc1
Author: Hashem Qolami
Author URI: http://kava.ir/

License: MIT license
License URI: http://opensource.org/licenses/MIT
*/

class HQ_LinkManager {

	protected $lang;

	private function get_path($subdir=NULL) {
		return plugin_dir_path(__FILE__). isset($subdir) ? $subdir.'/' : '';
	}

	protected function get_lang($dir=TRUE) {
		$dir = !!$dir ? '/' : '';
		return str_replace('-', '_', get_bloginfo('language')) . $dir;
	}

	protected function lang() {
		require_once(
			$this->get_path('language') . $this->get_lang() . 'lang.php'
		);
		$this->lang = $lang;
	}

	public function initialize() {
		if(get_option('HQLM_excerpt'))	add_filter('the_excerpt',	array(&$this, 'parse_url') , 100);
		if(get_option('HQLM_content'))	add_filter('the_content',	array(&$this, 'parse_url') , 100);
		if(get_option('HQLM_comments'))	add_filter('comment_text',	array(&$this, 'parse_url') , 100);
	}

	public function __construct() {
		$this->lang();
		$this->initialize();
		add_action('admin_menu', array(&$this, 'HQLM_admin_menu'));
	}

	public function HQLM_admin_menu() {
		add_options_page(
			$this->lang['page_title'],	// Page title
			$this->lang['menu_title'],	// Menu title
			'manage_options',			// Capability: (http://codex.wordpress.org/Roles_and_Capabilities#manage_options)
			'HQLM_menu',						// The slug name (Unique)
			array(&$this, 'HQLM_admin_page')	// Callback function
		); 
		add_action('admin_init', array(&$this, 'HQLM_setup'));
	}

	public function HQLM_setup() {
		add_option('HQLM_attr',		'');
		add_option('HQLM_content',	'');
		add_option('HQLM_comments',	'');
		add_option('HQLM_excerpt',	'');

		register_setting('HQLM_settings', 'HQLM_attr');
		register_setting('HQLM_settings', 'HQLM_excerpt');
		register_setting('HQLM_settings', 'HQLM_content');
		register_setting('HQLM_settings', 'HQLM_comments');
	}

	protected static function get_host($url) {
		preg_match("#^(https?://)?([^/]+)#i", $url, $matches);
		preg_match("#[^\./]+\.[^\./]+$#", $matches[2], $matches);
		return $matches[0];
	}

	public static function is_external($url) {
		return self::get_host($url) !== self::get_host($_SERVER["HTTP_HOST"]);
	}

	public function parse_url($text) {
		$pattern = '/<a (.*?)href=[\'\"](.*?)\/\/(.*?)[\'\"](.*?)>(.*?)<\/a>/i';
		return preg_replace_callback($pattern, array(&$this, 'parse_matches'), $text);	 
	}

	public function parse_matches($matches) { 

		if ( get_option('HQLM_attr') && self::is_external($matches[3]) ) {

				$attr = self::get_attr();
				return "<a {$matches[1]}href='{$matches[2]}//{$matches[3]}'{$matches[4]}{$attr}>{$matches[5]}</a>";
		}
		
		return $matches[0];
	}

	public static function get_attr(){
		$arr = get_option('HQLM_attr');
		$attr = '';

		foreach ($arr as $key => $value) {
			$attr .= " $key='$value'";
		}

		return $attr;
	}

	public function HQLM_admin_page() { ?>
		<div class="wrap">
			<div id="icon-link-manager" class="icon32"></div> 
			<h2><?php echo $this->lang['menu_title']; ?></h2>  
			
				<div class="metabox-holder"> 
					<div class="postbox gdrgrid frontleft"> 
						<h3 class="hndle"><span><?php echo $this->lang['opt_title']; ?></span></h3>
						<div class="gdsrclear"></div>

						<div class="inside">
							  
							   <form method="post" action="options.php" onsubmit="send_attr()"> 
								<?php
									settings_fields('HQLM_settings'); 

									$ch_content		= get_option("HQLM_content")	? 'checked="checked"' : '';
									$ch_comments	= get_option("HQLM_comments")	? 'checked="checked"' : '';
									$ch_excerpt		= get_option("HQLM_excerpt")	? 'checked="checked"' : '';
								?>
								<?php 	echo $this->lang['attr_body']; ?>
									<div id="attr_container" style="text-align: left; margin: 10px auto 20px auto; direction: ltr; position: relative; width:350px;">
										<input style="padding: 3px 5px; position: absolute; right: 0; top: 0;" type="button" value="<?php echo $this->lang['add_more_input']; ?>" onclick="addInput()" /> 
									<?php	if( $attr_array = get_option("HQLM_attr") ) {
												$i = 0;
												foreach ($attr_array as $key => $value) { 
													$i++;
									?>
													<?php if($i>1) { ?><br /> <?php } ?><input id="attr_<?php echo $i; ?>" type="text" value="<?php echo $key; ?>" placeholder="Attribute" /> : <input id="val_<?php echo $i; ?>" type="text" value="<?php echo $value; ?>" placeholder="Value" />
									<?php		}
											} else { ?>
												<input id="attr_1" type="text" value="" placeholder="Attribute" /> : <input id="val_1" type="text" value="" placeholder="Value" />
									<?php	}
										?>
									</div>

									<table class="form-table">
										<tr>
											<td valign="top">
												<input id="HQLM_content" type="checkbox" name="HQLM_content" <?php echo $ch_content; ?>/>
											</td>
											<td width="100%">
												<strong><label for="HQLM_content"><?php echo $this->lang['checkbox_content_title']; ?></label></strong><br />
												<?php echo $this->lang['checkbox_content_desc']; ?>
											</td>
										</tr>

										<tr>
											<td valign="top">
												<input id="HQLM_comments" type="checkbox" name="HQLM_comments" <?php echo $ch_comments; ?>/>
											</td>
											<td nowrap>
												<strong><label for="HQLM_comments"><?php echo $this->lang['checkbox_comments_title']; ?></label></strong><br />
												<?php echo $this->lang['checkbox_comments_desc']; ?>
											</td>
										</tr>

										<tr>
											<td valign="top">
												<input id="HQLM_excerpt" type="checkbox" name="HQLM_excerpt" <?php echo $ch_excerpt; ?>/>
											</td>
											<td nowrap>
												<strong><label for="HQLM_excerpt"><?php echo $this->lang['checkbox_excerpt_title']; ?></label></strong><br />
												<?php echo $this->lang['checkbox_excerpt_desc']; ?>
											</td>
										</tr>

										<tr>
											<td  colspan="2">
												<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
											</td>
										</tr>
									</table>
								</form>
							  
						</div>
					</div>
				</div>
			</div>
			<script type="text/javascript">
			//<![CDATA[
				var box = document.getElementById('attr_container'), id = (box.getElementsByTagName('input').length - 1) / 2, attr, val;
				function addInput(){
					id++;
					attr = document.createElement('input');
					val = document.createElement('input');

					attr.setAttribute('type', 'text');
					val.setAttribute('type', 'text');
					
					attr.setAttribute('id', 'attr_'+ id);
					val.setAttribute('id', 'val_'+ id);

					attr.setAttribute('placeholder', 'Attribute');
					val.setAttribute('placeholder', 'Value');

					box.innerHTML +="<br />\n";
					box.appendChild(attr);
					box.innerHTML +=' : ';
					box.appendChild(val);
				}

				function send_attr(){
					for (var i=1; i <= id; i++) {
						a = document.getElementById('attr_'+i);
						p = document.getElementById('val_'+i);

						if ( a.value.length > 0 && p.value.length > 0) {
							 p.setAttribute('name', 'HQLM_attr['+ a.value +']');
						};
					}
				}
			//]]>
			</script>
<?php
	}
}

new HQ_LinkManager();
?>