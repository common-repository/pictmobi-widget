<?php

/*
Plugin Name: Pict.Mobi Widget
Version: 1.2.6
Plugin URI: http://www.fauxzen.com
Description: Display your Pict.Mobi image stream on your blog. This is a modified version of <a href="http://www.pepijnkoning.nl/archief/wordpress-my-pictures-widget/">My Pictures Widget</a>.
Author: sdenike
Author URI: http://www.fauxzen.com
*/

include_once(ABSPATH . WPINC . '/rss.php');
define('PMWP_URL', WP_PLUGIN_URL . '/pictmobi-widget');
define('MAGPIE_CACHE_ON', 1); //2.7 Cache Bug
define('MAGPIE_CACHE_DIR', './cache/');

$pictmobi_options['widget_fields']['title'] = array('label'=>'Title:', 'type'=>'text', 'default'=>'Pict.Mobi');
$pictmobi_options['widget_fields']['username'] = array('label'=>'Username:', 'type'=>'text', 'default'=>'');
$pictmobi_options['widget_fields']['num'] = array('label'=>'Pics:', 'type'=>'text', 'default'=>'12');
$pictmobi_options['widget_fields']['size'] = array('label'=>'Size:', 'type'=>'text', 'default'=>'50');
$pictmobi_options['widget_fields']['margin'] = array('label'=>'Margin:', 'type'=>'text', 'default'=>'5');
$pictmobi_options['widget_fields']['border'] = array('label'=>'Border:', 'type'=>'text', 'default'=>'1');
$pictmobi_options['widget_fields']['bordercolor'] = array('label'=>'Border color:', 'type'=>'text', 'default'=>'#333');
$pictmobi_options['widget_fields']['linked'] = array('label'=>'Linked photos:', 'type'=>'checkbox', 'default'=>true);
$pictmobi_options['prefix'] = 'pictmobi';

function Showpictmobi($username = '', $num = 12, $linked = true, $size = 50, $margin = 5, $border = 1, $bordercolor = '#333') {
	$file = @file_get_contents("http://pict.mobi/feed/".$username);
	for($i = 1; $i <= $num; ++$i) {
		$pic = explode('"><img src="', $file);
		$pic = explode('"></a>]]></description>', $pic[$i]);
		$pic = trim($pic[0]);
		$url = explode('<guid>', $file);
		$url = explode('</guid>', $url[$i]);
		$url = trim($url[0]);
		if($linked == "true") {
			echo '<a href="'.$url.'" target="_new" /><img src=' . PMWP_URL . '/timthumb.php?src=' . $pic.'&h='.$size.'&w='.$size.' style="margin: '.$margin.'px; border: '.$border.'px solid '.$bordercolor.';" class="pictmobi" /></a>';
		} else {
			echo '<img src="'.$pic.'" width="'.$size.'" height="'.$size.'" style="margin: '.$margin.'px; border: '.$border.'px solid '.$bordercolor.';" class="pictmobi" />';
		}
	}
}
function PictMobi($username = '', $num = 4, $linked = true, $size = 70, $margin = 5, $border = 0, $bordercolor = '#FFFFFF', $service = 'pictmobi') {
		Showpictmobi($username, $num, $linked, $size, $margin, $border, $bordercolor);
}

function widget_pictmobi_init() {
	if (!function_exists('register_sidebar_widget'))
		return;
	
		$check_options = get_option('widget_pictmobi');
  		if ($check_options['number']=='') {
    			$check_options['number'] = 1;
    			update_option('widget_pictmobi', $check_options);
  		}

	function widget_pictmobi($args, $number = 1) {	
	global $pictmobi_options;
		extract($args);
		include_once(ABSPATH . WPINC . '/rss.php');
		$options = get_option('widget_pictmobi');
		$item = $options[$number];
		foreach($pictmobi_options['widget_fields'] as $key => $field) {
			if (! isset($item[$key])) {
				$item[$key] = $field['default'];
			}
		}
		echo $before_widget . $before_title . $item['title'] . $after_title;
			echo '<ul style="align: left;">';
			PictMobi($item['username'], $item['num'], $item['linked'], $item['size'], $item['margin'], $item['border'], $item['bordercolor'], $item['service']);
			echo '</ul>';
		echo $after_widget;
	}
	function widget_pictmobi_control($number) {
		global $pictmobi_options;
		$options = get_option('widget_pictmobi');		
		if ( isset($_POST['pictmobi-submit']) ) {
			foreach($pictmobi_options['widget_fields'] as $key => $field) {
				$options[$number][$key] = $field['default'];
				$field_name = sprintf('%s_%s_%s', $pictmobi_options['prefix'], $key, $number);

				if ($field['type'] == 'text') {
					$options[$number][$key] = strip_tags(stripslashes($_POST[$field_name]));
				} elseif ($field['type'] == 'checkbox') {
					$options[$number][$key] = isset($_POST[$field_name]);
				} elseif ($field['type'] == 'radio') {
					if (! empty($options[$number][$key])) {
						$field_checked = 'checked="checked"';
					}
					$cssRadioGroup .= '<div class="audioboo_field_radio_row"><input type="radio" name="audiobooradiogroup" value="' . $field_name .'" '. $field_checked . '>' . $field['label'] . "</div>";
					if ($key == 'customCSS') {
						$cssRadioGroup .= '<span style="font-size: 9px;">(selected themes folder)</span>';
						$rt_field_value = htmlspecialchars($options[$number][$key . "_name"], ENT_QUOTES);
						$rt_field_name = $field_name . "_name";
						$cssRadioGroup .= sprintf('<br><input class="audiobooradiogroupinputtext" id="%s" name="%s" type="text" value="%s"/>', $rt_field_name, $rt_field_name, $rt_field_value);
					}
					continue;
				}
			}

			update_option('widget_pictmobi', $options);
		}

		foreach($pictmobi_options['widget_fields'] as $key => $field) {
			
			$field_name = sprintf('%s_%s_%s', $pictmobi_options['prefix'], $key, $number);
			$field_checked = '';
			if ($field['type'] == 'text') {
				$field_value = htmlspecialchars($options[$number][$key], ENT_QUOTES);
			} elseif ($field['type'] == 'checkbox') {
				$field_value = 1;
				if (! empty($options[$number][$key])) {
					$field_checked = 'checked="checked"';
				}
			}
			printf('<p style="text-align:right;" class="pictmobi_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
				$field_name, __($field['label']), $field_name, $field_name, $field['type'], $field_value, $field['type'], $field_checked);
		}
		echo '<input type="hidden" id="pictmobi-submit" name="pictmobi-submit" value="1" />';
	}
	function widget_pictmobi_setup() {
		$options = $newoptions = get_option('widget_pictmobi');
		
		if ( isset($_POST['pictmobi-number-submit']) ) {
			$number = (int) $_POST['pictmobi-number'];
			$newoptions['number'] = $number;
		}	
		if ( $options != $newoptions ) {
			update_option('widget_pictmobi', $newoptions);
			widget_pictmobi_register();
		}
	}
	function widget_pictmobi_register() {
		$options = get_option('widget_pictmobi');
		$dims = array('width' => 250, 'height' => 300);
		$class = array('classname' => 'widget_pictmobi');
		for ($i = 1; $i <= 9; $i++) {
			$name = sprintf(__('Pict.Mobi'), $i);
			$id = "pictmobi-$i"; // Never never never translate an id
			wp_register_sidebar_widget($id, $name, $i <= $options['number'] ? 'widget_pictmobi' : /* unregister */ '', $class, $i);
			wp_register_widget_control($id, $name, $i <= $options['number'] ? 'widget_pictmobi_control' : /* unregister */ '', $dims, $i);
		}
		add_action('sidebar_admin_setup', 'widget_pictmobi_setup');
	}
	widget_pictmobi_register();
}
add_action('widgets_init', 'widget_pictmobi_init');
?>
