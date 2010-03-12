<?php
/*
Plugin Name: ShowIKRSS
Plugin URI: http://www.niftygaloot.com/showikrss-wordpress-plugin/
Description: ShowIKRSS uses RSS to display Imagekind Galleries. Insert &quot;[rsspics:IMGSIZE,SASID,TRKCODE,URL]&quot; into a post or page.  Replace &quot;IMGSIZE&quot; with the size image you want to show... 100, 150, 200, 350, 450, or 650.  Replace &quot;SASID&quot; with your ShareASale ID number.  Replace &quot;TRKCODE&quot; with a Tracking code. Replace &quot;URL&quot; with the URL of the RSS feed starting with &quot;http://&quot;.
Author: Kent Lorentzen
Author URI: http://www.niftygaloot.com/showikrss-wordpress-plugin/
Version: 0.47
*/

function showikrss($content) {

	// --- Initialize ---
	require_once (ABSPATH . WPINC . '/rss-functions.php');

	// --- Arrays ---
	$find[] = "";
	$replace[] = "";
		
	// --- Search ---
	// Find all "[rsspics:*]"
	preg_match_all('/\[rss(list|pics):(\S+)\]/', $content, $matches, PREG_SET_ORDER);

	// For each one you find...
	foreach ($matches as $val) {
		$ikinfo = explode(",",$val[2]);

		$ikimagesize = $ikinfo[0];				
		$sasid = $ikinfo[1];
		$sasafftrack = $ikinfo[2];		
		$url = html_entity_decode($ikinfo[3]);
		$ikzoomtext = "view larger";
		
		if ($ikimagesize == "IMGSIZE") {
			$ikimagesize = get_option('showikrss_imagesize');
		}
		
		if ($sasid == "SASID") {
			$sasid = get_option('showikrss_sasid');
		} 

		if ($sasafftrack == "TRKCODE") {
			$sasafftrack = get_option('showikrss_afftrack');
		} 


		$sasaffcode = "http://www.shareasale.com/r.cfm?u=" . $sasid . "&b=63370&m=10782&afftrack=" . $sasafftrack . "&urllink=www.imagekind.com/";
		
		// --- Syntax Display ---
		if (strtoupper($url) == "URL") {
			$disp = $val[0];

		// --- Get RSS ---
		} elseif ($rss = fetch_rss($url)) {

				// --- pics format ---

					// Set Variables
						if ($ikimagesize < "100") {
						$ikimagesize = "100";
						}
						
						if ($ikimagesize > "650") {
						$ikimagesize = "650";
						}
						 
						$ikimagesize = "uploadedartwork/" . $ikimagesize . "X" . $ikimagesize;
					
					// Image
					if ($rss->image[url] != '') {
						$title = $rss->image[title];
						$link = htmlentities($rss->image[link]);
						$url = $rss->image[url];
						$disp .= "\t<a href='$link'><img src='$url' alt='$title' /></a><br />\n";
					}
					
					// Title, copyright, and description
					$title = $rss->channel[title];
					$copy = $rss->channel[copyright];
					$link = htmlentities($rss->channel[link]);					
					$link = str_replace("http://www.imagekind.com/",$sasaffcode,$link );
					if (get_option('showikrss_ikwindow') == "new") {
						$link = $link . "' TARGET='_blank'";
					}

					$desc = $rss->channel[description];

					$disp .= "\t<big><a href='$link'>$title</a></big><br />\n";
					if ($copy != '') $disp .= "\t<small>$copy</small><br />\n";
					if ($desc != '') $disp .= "\t$desc<br />\n";
					$disp .= "\n\t<br />\n";

					// For each item...
					foreach($rss->items as $item) {
						$title = $item[title];
						$link = htmlentities($item[link]);
						$templink = strchr($link,"?IMID=");
						$link = str_replace("http://www.imagekind.com/",$sasaffcode,$link );
						$templink = $sasaffcode . '/FrameShop.aspx'. $templink;
						$framedlink = $templink . '%26byartist=1';
						$unframedlink = $templink . '%26frame=0%26isprint=1';
						$canvaslink = $templink . '%26canvas=1';
						
						if (get_option('showikrss_ikwindow') == "new") {						
							$link = $link . "' TARGET='_blank'";
							$framedlink = $framedlink . "' TARGET='_blank'";
							$unframedlink = $unframedlink . "' TARGET='_blank'";
							$canvaslink = $canvaslink . "' TARGET='_blank'";
						}
						$desc = $item[description];
						
						$ikdesc = explode("<BR/>", $desc, 2);
						
						$ikdescimg = $ikdesc[0];
						$ikdesctext = $ikdesc[1];
						$ikzoomimg = $ikdescimg;
						$ikzoomimg = str_replace("uploadedartwork/100X100","uploadedartwork/650X650",$ikzoomimg );
						$ikzoomimg = substr($ikzoomimg,10);
						$ikzoomimg = strtok($ikzoomimg, "'");
						$ikzoomimg = $ikzoomimg . "' rel='_lightbox'";
						$ikdescimg = str_replace("uploadedartwork/100X100",$ikimagesize,$ikdescimg );
						
						if ($title != '') {
						$disp .= "\t<div  class='showikrss_float;' >\n";
						$disp .= "\t<div  style='text-align: center;'>\n";
							$disp .= "\t<b><a href='$link'>$title</a></b>\n<br />";
							$disp .= "\t<a href='$link'>$ikdescimg</a>\n<br />";						
							if (get_option('showikrss_zoomimg') == "yes") {						
								$disp .= "\t<a href='$ikzoomimg'> [+ Zoom Image]</a>\n<br />";
								}
						$disp .= "\t</div>\n";
						if (get_option('showikrss_orderline') == "yes") {
							$disp .= "\tClick to Order-> <a href='$framedlink'>Framed Giclee Print</a>\n";
							$disp .= "\t - <a href='$unframedlink'>Unframed Print</a>\n";
							$disp .= "\t - <a href='$canvaslink'>Canvas Print</a>.\n<br />";
							}
						$disp .= "\t<div class='showikrss_scroll'>\n";
							$disp .= "\t<p>$ikdesctext\n</p><br />";
						$disp .= "\t</div>\n";
						$disp .= "\t</div>\n";
						}
					}

		// --- Not Found ---
		} else {
			$disp = "\nShowIKRSS ERROR: &quot;$url&quot; NOT FOUND!<br />\n";
		}

		// --- Replace ---
		$find = $val[0];
		$replace = $disp;
	}

	// --- Return ---
	return str_replace($find, $replace, $content) . $appendage;
}

// --- Filter ---
add_filter('the_content', 'showikrss');

// --- Options Menu ---
function set_showikrss_defaults() {
	add_option('showikrss_imagesize','200','Image size');
	add_option('showikrss_sasid','202591','Plugin author ID');
	add_option('showikrss_afftrack','ShowIKRSS','Tracking code');
	add_option('showikrss_ikwindow','same','Open links');
	add_option('showikrss_orderline','no','Order Line');
	add_option('showikrss_zoomimg','no','Show Zoom');
}

function unset_showikrss_defaults() {
	delete_option('showikrss_imagesize');
	delete_option('showikrss_sasid');
	delete_option('showikrss_afftrack');
	delete_option('showikrss_ikwindow');
	delete_option('showikrss_orderline');
	delete_option('showikrss_zoomimg');
}

register_activation_hook(__FILE__,'set_showikrss_defaults');

function admin_showikrss_options() {
	?><div class="wrap"><h2>ShowIKRSS Options</h2><?php
	
	if ($_REQUEST['submit']) {
		update_showikrss_options();
	}
	print_showikrss_form();
	
	?></div><?php

}

function update_showikrss_options() {
		$ok = false;
		
		//  You probably want some input validation in here
		if ($_REQUEST['showikrss_imagesize']) {
			 update_option('showikrss_imagesize',$_REQUEST['showikrss_imagesize']);
			 $ok = true;
		}

		//  You probably want some input validation in here
		if ($_REQUEST['showikrss_sasid']) {
			 update_option('showikrss_sasid',$_REQUEST['showikrss_sasid']);
			 $ok = true;
		}

		//  You probably want some input validation in here
		if ($_REQUEST['showikrss_afftrack']) {
			 update_option('showikrss_afftrack',$_REQUEST['showikrss_afftrack']);
			 $ok = true;

		}
		//  You probably want some input validation in here
		if ($_REQUEST['showikrss_ikwindow']) {
			 update_option('showikrss_ikwindow',$_REQUEST['showikrss_ikwindow']);
			 $ok = true;
		}
		
		//  You probably want some input validation in here
		if ($_REQUEST['showikrss_orderline']) {
			 update_option('showikrss_orderline',$_REQUEST['showikrss_orderline']);
			 $ok = true;
		}
		
		//  You probably want some input validation in here
		if ($_REQUEST['showikrss_zoomimg']) {
			 update_option('showikrss_zoomimg',$_REQUEST['showikrss_zoomimg']);
			 $ok = true;
		}
		
		if ($ok) {
			?><div id="message" class="updated fade">
				<p>Options saved.</p>
			</div><?php
		}
		else {
			?><div id="message" class="error fade">
				<p>Failed to save options.</p>
			</div><?php
		}
}

function print_showikrss_form() {
		$default_imagesize = get_option('showikrss_imagesize');
		$default_sasid = get_option('showikrss_sasid');
		$default_afftrack = get_option('showikrss_afftrack');
		$default_ikwindow = get_option('showikrss_ikwindow');
		$default_orderline = get_option('showikrss_orderline');
		$default_zoomimg = get_option('showikrss_zoomimg');
		?>
		<form method="post">
				<label for="showikrss_imagesize">Select a default Image Size:<BR>

					<p>
					<INPUT NAME="showikrss_imagesize" TYPE="radio" VALUE="100" <?php if ($default_imagesize == "100") echo "checked"; ?>>100
					<INPUT NAME="showikrss_imagesize" TYPE="radio" VALUE="150" <?php if ($default_imagesize == "150") echo "checked"; ?>>150
					<INPUT NAME="showikrss_imagesize" TYPE="radio" VALUE="200" <?php if ($default_imagesize == "200") echo "checked"; ?>>200
					<INPUT NAME="showikrss_imagesize" TYPE="radio" VALUE="350" <?php if ($default_imagesize == "350") echo "checked"; ?>>350
					<INPUT NAME="showikrss_imagesize" TYPE="radio" VALUE="450" <?php if ($default_imagesize == "450") echo "checked"; ?>>450
					<INPUT NAME="showikrss_imagesize" TYPE="radio" VALUE="650" <?php if ($default_imagesize == "650") echo "checked"; ?>>650
</P> 
				</label>
				<br />
				<label for="showikrss_sasid">Set the default <B>SASID</B>:
						<input type="text" name="showikrss_sasid" value="<?php echo $default_sasid; ?>"
				</label> 
				<br />
				When first activated the default ShareASale ID is the plugin authors ID.<BR>
				Make sure you change it to your ID to get credit for affiliate sales.
				<br />
				<br />
				<label for="showikrss_afftrack">Default Tracking code:
						<input type="text" name="showikrss_afftrack" value="<?php echo $default_afftrack; ?>"
				</label> Letters and Numbers only - No spaces.
				<br />
				<br />
				<label for="showikrss_ikwindow">When a link is clicked:<BR>
						<P>
						<INPUT NAME="showikrss_ikwindow" TYPE="radio" VALUE="same" <?php if ($default_ikwindow == "same") echo "checked"; ?>> Open links in Same Window<BR>
						<INPUT NAME="showikrss_ikwindow" TYPE="radio" VALUE="new" <?php if ($default_ikwindow == "new") echo "checked"; ?>> Open links in New Window</P>

				</label>
				<br />
				<label for="showikrss_zoomimg">Display<B> [+ zoom image]</B>  {uses Lightbox}:<BR>
						<P>
						<INPUT NAME="showikrss_zoomimg" TYPE="radio" VALUE="yes" <?php if ($default_zoomimg == "yes") echo "checked"; ?>> Yes<BR>
						<INPUT NAME="showikrss_zoomimg" TYPE="radio" VALUE="no" <?php if ($default_zoomimg == "no") echo "checked"; ?>> No</P>

				</label>
				<label for="showikrss_orderline">Show Order line - '<B>Click to Order -> Framed Giclee Print - Unframed Print - Canvas Prin</B>t:<BR>
						<P>
						<INPUT NAME="showikrss_orderline" TYPE="radio" VALUE="yes" <?php if ($default_orderline == "yes") echo "checked"; ?>> Yes<BR>
						<INPUT NAME="showikrss_orderline" TYPE="radio" VALUE="no" <?php if ($default_orderline == "no") echo "checked"; ?>> No</P>

				</label>
				<br />
				<input type="submit" name="submit" value="Submit" />
		</form>
		<HR>
		<BR>
		To display an Imagekind Gallery paste this code on a new line in a post or page:<BR><BR>
		<B>[rsspics:IMGSIZE,SASID,TRKCODE,URL]</B><BR><BR>
		Be sure to include the starting and ending brackets.<BR><BR>
		Replace 'URL' with the URL of the RSS feed starting with 'http://'.<BR>
		(The URL for the feed is an Orange symbol and words that say 'RSS for this gallery' on the left side and below the pictures  when you are looking at a gallery.)<BR><BR>
		The gallery will be shown using the default settings.  You can change IMGSIZE,SASID,or TRKCODE if you want to use a different setting for that post.<BR><BR>
		<BR>
		<B>If you find this plugin useful...</B>  A link to anything in these <A HREF="http://www.imagekind.com/MemberProfileGalleries.aspx?MID=90d8524f-f324-45a3-b939-3b19dba2dfc7" TARGET="_blank" TITLE="Kent Lorentzen Galleries">galleries</A> would be appreciated.
		
		<?php
}				

function modify_menu() {
	add_options_page(
						 	'ShowIKRSS',				// page title
							'ShowIKRSS',				// sub-menu name
							'manage_options',			// access/ca
							__FILE__,					// file
							'admin_showikrss_options'	// function
						);
}

add_action('admin_menu','modify_menu');

?>