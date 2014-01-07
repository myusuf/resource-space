<?php
/**
 * View resource page
 * 
 * @package ResourceSpace
 * @subpackage Pages
 */
include "../include/db.php";
include "../include/general.php";
# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if (($k=="") || (!check_access_key(getvalescaped("ref",""),$k))) {include "../include/authenticate.php";}
include "../include/search_functions.php";
include "../include/resource_functions.php";
include "../include/collections_functions.php";
include "../include/image_processing.php";

$ref=getvalescaped("ref","",true);

# Update hit count
update_hitcount($ref);
	
# fetch the current search (for finding similar matches)
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
$starsearch=getvalescaped("starsearch","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive",0,true);

$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);

# next / previous resource browsing
$go=getval("go","");
if ($go!="") 
	{
	$origref=$ref; # Store the reference of the resource before we move, in case we need to revert this.
	
	# Re-run the search and locate the next and previous records.
	$modified_result_set=hook("modifypagingresult"); 
	if ($modified_result_set){
		$result=$modified_result_set;
	} else {
		$result=do_search($search,$restypes,$order_by,$archive,-1,$sort,false,$starsearch,false,false,"", getvalescaped("go",""));
	}
	if (is_array($result))
		{
		# Locate this resource
		$pos=-1;
		for ($n=0;$n<count($result);$n++)
			{
			if ($result[$n]["ref"]==$ref) {$pos=$n;}
			}
		if ($pos!=-1)
			{
			if (($go=="previous") && ($pos>0)) {$ref=$result[$pos-1]["ref"];}
			if (($go=="next") && ($pos<($n-1))) {$ref=$result[$pos+1]["ref"];if (($pos+1)>=($offset+72)) {$offset=$pos+1;}} # move to next page if we've advanced far enough
			}
		else
			{
			?>
			<script type="text/javascript">
			alert('<?php echo $lang["resourcenotinresults"] ?>');
			</script>
			<?php
			}
		}
    # Option to replace the key via a plugin (used by resourceconnect plugin).
    $newkey = hook("nextpreviewregeneratekey");
    if (is_string($newkey)) {$k = $newkey;}

    # Check access permissions for this new resource, if an external user.
    if ($k!="" && !check_access_key($ref, $k)) {$ref = $origref;} # Cancel the move.
	}

hook("chgffmpegpreviewext", "", array($ref));

# Load resource data
$resource=get_resource_data($ref);
if ($resource===false) {exit("Resource not found.");}

// get mp3 paths if necessary and set $use_mp3_player switch
if (!(isset($resource['is_transcoding']) && $resource['is_transcoding']==1) && (in_array($resource["file_extension"],$ffmpeg_audio_extensions) || $resource["file_extension"]=="mp3") && $mp3_player){
		$use_mp3_player=true;
	} 
	else {
		$use_mp3_player=false;
	}
if ($use_mp3_player){
	$mp3realpath=get_resource_path($ref,true,"",false,"mp3");
	if (file_exists($mp3realpath)){
		$mp3path=get_resource_path($ref,false,"",false,"mp3");
	}
}	

# Dev feature - regenerate exif data.
if (getval("regenexif","")!="")
	{
	extract_exif_comment($ref,$resource["file_extension"]);
	$resource=get_resource_data($ref,false);
	}

# Load access level
$access=get_resource_access($ref);
hook("beforepermissionscheck");
# check permissions (error message is not pretty but they shouldn't ever arrive at this page unless entering a URL manually)
if ($access==2) 
		{
		exit("This is a confidential resource.");
		}
		
hook("afterpermissionscheck");
		
# Establish if this is a metadata template resource, so we can switch off certain unnecessary features
$is_template=(isset($metadata_template_resource_type) && $resource["resource_type"]==$metadata_template_resource_type);

$title_field=$view_title_field; 
# If this is a metadata template and we're using field data, change title_field to the metadata template title field
if (isset($metadata_template_resource_type) && ($resource["resource_type"]==$metadata_template_resource_type))
	{
	if (isset($metadata_template_title_field)){
		$title_field=$metadata_template_title_field;
		}
	else {$default_to_standard_title=true;}	
	}

if ($pending_review_visible_to_all && isset($userref) && $resource["created_by"]!=$userref && $resource["archive"]==-1 && !checkperm("e0"))
	{
	# When users can view resources in the 'User Contributed - Pending Review' state in the main search
	# via the $pending_review_visible_to_all option, set access to restricted.
	$access=1;
	}

# If requested, refresh the collection frame (for redirects from saves)
if (getval("refreshcollectionframe","")!="")
	{
	refresh_collection_frame();
	}

# Update the hitcounts for the search keywords (if search specified)
# (important we fetch directly from $_GET and not from a cookie
$usearch=@$_GET["search"];
if ((strpos($usearch,"!")===false) && ($usearch!="")) {update_resource_keyword_hitcount($ref,$usearch);}

# Log this activity
daily_stat("Resource view",$ref);
if ($log_resource_views) {resource_log($ref,'v',0);}

if ($direct_download && !$save_as){	
// check browser to see if forcing save_as 
if (!$direct_download_allow_opera  && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]),"opera")!==false) {$save_as=true;}
if (!$direct_download_allow_ie7 && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]),"msie 7.")!==false) {$save_as=true;}	
if (!$direct_download_allow_ie8 && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]),"msie 8.")!==false) {$save_as=true;}	
}

# Show the header/sidebar
include "../include/header.php";

if ($metadata_report && isset($exiftool_path))
	{
	?>
	<script src="<?php echo $baseurl_short?>lib/js/metadata_report.js" type="text/javascript"></script>
	<?php
	}


if ($direct_download && !$save_as){
?>
<iframe id="dlIFrm" frameborder=0 scrolling="auto" <?php if ($debug_direct_download){?>width="600" height="200"<?php } else { ?>width="0" height="0"<?php } ?>> This browser can not use IFRAME. </iframe>
<?php }

hook("pageevaluation");

# Load resource field data
$fields=get_resource_field_data($ref,false,!hook("customgetresourceperms"),-1,$k!="",$use_order_by_tab_view);

# Load edit access level (checking edit permissions - e0,e-1 etc. and also the group 'edit filter')
$edit_access=get_edit_access($ref,$resource["archive"],$fields,$resource);
if ($k!="") {$edit_access=0;}
?>

<!--Panel for record and details-->
<div class="RecordBox">
<div class="RecordPanel"> 

<div class="RecordHeader">
<?php if (!hook("renderinnerresourceheader")) { ?>


<?php 
# Check if actually coming from a search, but not if a numeric search and config_search_for_number is set or if this is a direct request e.g. ?r=1234.
if (isset($_GET["search"]) && !($config_search_for_number && is_numeric($usearch))) { ?>
<div class="backtoresults">
<a class="prevLink" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k) ?>&go=previous&<?php echo hook("nextpreviousextraurl") ?>" onClick="return CentralSpaceLoad(this);">&lt;&nbsp;<?php echo $lang["previousresult"]?></a>
<?php 
if (!hook("viewallresults")) {
?>
|
<a class="upLink" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&go=up&k=<?php echo urlencode($k)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["viewallresults"]?></a>
<?php } ?>
|
<a class="nextLink" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>&go=next&<?php echo hook("nextpreviousextraurl") ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["nextresult"]?>&nbsp;&gt;</a>
</div>
<?php } ?>


<h1><?php switch ($resource["archive"])
	{
	case -2:
	?><span class="ResourcePendingSubmissionTitle"><?php echo $lang["status-2"]?>:</span>&nbsp;<?php
	break;
	case -1:
	?><span class="ResourcePendingReviewTitle"><?php echo $lang["status-1"]?>:</span>&nbsp;<?php
	break;
	case 1:
	?><span class="ArchiveResourceTitle"><?php echo $lang["status1"]?>:</span>&nbsp;<?php
	break;
	case 2:
	?><span class="ArchiveResourceTitle"><?php echo $lang["status2"]?>:</span>&nbsp;<?php
	break;
	case 3:
	?><span class="DeletedResourceTitle"><?php echo $lang["status3"]?>:</span>&nbsp;<?php
	break;
	}
if (!hook("replaceviewtitle")){ echo highlightkeywords(htmlspecialchars(i18n_get_translated(get_data_by_field($resource['ref'],$title_field))),$search); } /* end hook replaceviewtitle */  
?>&nbsp;</h1>
<?php } /* End of renderinnerresourceheader hook */ ?>
</div>

<?php if (isset($resource['is_transcoding']) && $resource['is_transcoding']==1) { ?><div class="PageInformal"><?php echo $lang['resourceistranscoding']?></div><?php } ?>

<?php hook("renderbeforeresourceview"); ?>

<div class="RecordResource">
<?php if (!hook("renderinnerresourceview")) { ?>
<?php if (!hook("replacerenderinnerresourcepreview")) { ?>
<?php if (!hook("renderinnerresourcepreview")) { ?>
<?php
$download_multisize=true;

$flvfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension);
if (!file_exists($flvfile)) {$flvfile=get_resource_path($ref,true,"",false,$ffmpeg_preview_extension);}
if (file_exists("../players/type" . $resource["resource_type"] . ".php"))
	{
	include "../players/type" . $resource["resource_type"] . ".php";
	}
elseif (!(isset($resource['is_transcoding']) && $resource['is_transcoding']==1) && file_exists($flvfile) && (strpos(strtolower($flvfile),".".$ffmpeg_preview_extension)!==false))
	{
	# Include the Flash player if an FLV file exists for this resource.
	$download_multisize=false;
      if(!hook("customflvplay"))
	      {
          include "flv_play.php";
	      }
	
	# If configured, and if the resource itself is not an FLV file (in which case the FLV can already be downloaded), then allow the FLV file to be downloaded.
	if ($flv_preview_downloadable && $resource["file_extension"]!="flv") {$flv_download=true;}
	}
elseif ($use_mp3_player && file_exists($mp3realpath) && hook("custommp3player")){}	
elseif ($resource['file_extension']=="swf" && $display_swf){
	$swffile=get_resource_path($ref,true,"",false,"swf");
	if (file_exists($swffile)) { include "swf_play.php";}	
	}
elseif ($resource["has_image"]==1)
	{
	$use_watermark=check_use_watermark();
	$imagepath=get_resource_path($ref,true,"pre",false,$resource["preview_extension"],-1,1,$use_watermark);
	if (!file_exists($imagepath))
		{
		$imageurl=get_resource_path($ref,false,"thm",false,$resource["preview_extension"],-1,1,$use_watermark);
		}
	else
		{
		$imageurl=get_resource_path($ref,false,"pre",false,$resource["preview_extension"],-1,1,$use_watermark);
		}
	
	?>
	<div id="previewimagewrapper"><a class="enterLink" id="previewimagelink" href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref)?>&ext=<?php echo $resource["preview_extension"]?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&<?php echo hook("previewextraurl") ?>" title="<?php echo $lang["fullscreenpreview"]?>">
	<?php
	if (file_exists($imagepath))
		{ 
		?><img src="<?php echo $imageurl?>" alt="<?php echo $lang["fullscreenpreview"]?>" class="Picture" GALLERYIMG="no" id="previewimage" /><?php 
		} 
	?></a></div><?php 
	if ($image_preview_zoom)
		{ 
		$previewurl=get_resource_path($ref,false,"scr",false,$resource["preview_extension"],-1,1,$use_watermark);		
		?>
		<script>
		jQuery(document).ready(function(){
			jQuery('#previewimage')
			        .wrap('<span style="display:inline-block"></span>')
			        .css('display', 'block')
			        .parent()
			        .zoom({url: '<?php echo $previewurl ?>' });
			});
		</script>
		<?php
		}
	}
else
	{
	?>
	<img src="<?php echo $baseurl ?>/gfx/<?php echo get_nopreview_icon($resource["resource_type"],$resource["file_extension"],false)?>" alt="" class="Picture" style="border:none;" id="previewimage" />
	<?php
	}

?>
<?php } /* End of renderinnerresourcepreview hook */ ?>
<?php } /* End of replacerenderinnerresourcepreview hook */ ?>
<?php hook("renderbeforerecorddownload");

if ($download_summary) {include "../include/download_summary.php";}
?>
<?php if (!hook("renderresourcedownloadspace")) { ?>
<div class="RecordDownload" id="RecordDownload">
<div class="RecordDownloadSpace">
<?php if (!hook("renderinnerresourcedownloadspace")) { ?>
<h2 id="resourcetools"><?php echo $lang["resourcetools"]?></h2>

<?php 

# DPI calculations
function compute_dpi($size, &$dpi, &$dpi_unit, &$dpi_w, &$dpi_h)
	{
	global $lang, $imperial_measurements;
	if (isset($size['resolution'])&& $size['resolution']!=0) { $dpi=$size['resolution']; }
	else if (!isset($dpi) || $dpi==0) { $dpi=300; }

	if ((isset($size['unit']) && trim(strtolower($size['unit']))=="inches") || $imperial_measurements)
		{
		# Imperial measurements
		$dpi_unit=$lang["inch-short"];
		$dpi_w=round(($size["width"]/$dpi),1);
		$dpi_h=round(($size["height"]/$dpi),1);
		}
	else
		{
		$dpi_unit=$lang["centimetre-short"];
		$dpi_w=round(($size["width"]/$dpi)*2.54,1);
		$dpi_h=round(($size["height"]/$dpi)*2.54,1);
		}
	}

# MP calculation
function compute_megapixel($size)
	{
	return round(($size["width"]*$size["height"])/1000000,1);
	}

function get_size_info($size)
{
	global $lang;
	$output='<p>' . $size["width"] . " x " . $size["height"] . " " . $lang["pixels"];

	$mp=compute_megapixel($size);
	if ($mp>=1)
		{
		$output.=" (" . $mp . " " . $lang["megapixel-short"] . ")";
		}

	compute_dpi($size, $dpi, $dpi_unit, $dpi_w, $dpi_h);

	$output.='</p><p>';
	$output.=$dpi_w . " " . $dpi_unit . " x " . $dpi_h . " " . $dpi_unit . " " . $lang["at-resolution"]
			. " " . $dpi ." " . $lang["ppi"] . '</p>';
	return $output;
}

# Get display price for basket request modes
function get_display_price($ref, $size)
{
	global $pricing, $currency_symbol;

	$price_id=$size["id"];
	if ($price_id=="") { $price_id="hpr"; }

	$price=999; # If price cannot be found
	if (array_key_exists($price_id,$pricing)) { $price=$pricing[$price_id]; }

	# Pricing adjustment hook (for discounts or other price adjustments plugin).
	$priceadjust=hook("adjust_item_price","",array($price,$ref,$size["id"]));
	if ($priceadjust!==false) { $price=$priceadjust; }

	return $currency_symbol . " " . number_format($price,2);
}

function make_download_preview_link($ref, $size, $label)
	{
	global $direct_link_previews_filestore, $baseurl_short;

	if ($direct_link_previews_filestore)
		$direct_link="" . get_resource_path($ref,false,$size['id'],false,$size['extension']);
	else
		$direct_link=$baseurl_short."pages/download.php?direct=1&ref=$ref&size=" . $size['id'] . "&ext=" . $size['extension'];

	return "<a href='$direct_link' target='dl_window_$ref'>$label</a>";
	}

function add_download_column($ref, $size, $downloadthissize)
	{
	global $save_as, $direct_download, $order_by, $lang, $baseurl_short, $baseurl, $k, $search,
			$request_adds_to_collection, $offset, $archive, $sort;
	if ($downloadthissize)
		{
		?><td class="DownloadButton"><?php
		if (!$direct_download || $save_as)
			{
			if(!hook("downloadbuttonreplace"))
				{
				?><a id="downloadlink" <?php
				if (!hook("downloadlink","",array("ref=" . $ref . "&k=" . $k . "&size=" . $size["id"]
						. "&ext=" . $size["extension"])))
					{
					?>href="<?php echo $baseurl ?>/pages/terms.php?ref=<?php echo urlencode($ref)?>&search=<?php
							echo urlencode($search) ?>&k=<?php echo urlencode($k)?>&url=<?php
							echo urlencode("pages/download_progress.php?ref=" . $ref . "&size=" . $size["id"]
									. "&ext=" . $size["extension"] . "&k=" . $k . "&search=" . urlencode($search)
									. "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by="
									. urlencode($order_by))?>"<?php
					}
					?> onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-download"]?></a><?php
				}
			}
		else
			{
			?><a id="downloadlink" href="#" onclick="directDownload('<?php
					echo $baseurl_short?>pages/download_progress.php?ref=<?php echo urlencode($ref) ?>&size=<?php
					echo $size['id']?>&ext=<?php echo $size['extension']?>&k=<?php
					echo urlencode($k)?>')"><?php echo $lang["action-download"]?></a><?php
			}
			?></td><?php
		}
	else if (checkperm("q"))
		{
		if (!hook("resourcerequest"))
			{
			?><td class="DownloadButton"><?php
			if ($request_adds_to_collection)
				{
				echo add_to_collection_link($ref,$search,"alert('" . $lang["requestaddedtocollection"] . "');",$size["id"]);
				}
			else
				{
				?><a href="<?php echo $baseurl_short?>pages/resource_request.php?ref=<?php echo urlencode($ref)?>&k=<?php echo getval("k","")?>" onClick="return CentralSpaceLoad(this,true);"><?php
				}
			echo $lang["action-request"]?></a></td><?php
			}
		}
	else
		{
		# No access to this size, and the request functionality has been disabled. Show just 'restricted'.
		?><td class="DownloadButton DownloadDisabled"><?php echo $lang["access1"]?></td><?php
		}
	}

# Look for a viewer to handle the right hand panel. If not, display the standard photo download / file download boxes.
if (file_exists("../viewers/type" . $resource["resource_type"] . ".php"))
	{
	include "../viewers/type" . $resource["resource_type"] . ".php";
	}
elseif (hook("replacedownloadoptions"))
	{
	}
elseif ($is_template)
	{
	
	}
else
	{ 
	?>
<table cellpadding="0" cellspacing="0">
<tr>
<?php
$table_headers_drawn=false;
$nodownloads=false;$counter=0;$fulldownload=false;
$showprice=$userrequestmode==2 || $userrequestmode==3;
hook("additionalresourcetools");
if ($resource["has_image"]==1 && $download_multisize)
	{
	# Restricted access? Show the request link.

	# List all sizes and allow the user to download them
	$sizes=get_image_sizes($ref,false,$resource["file_extension"]);
	for ($n=0;$n<count($sizes);$n++)
		{
		# Is this the original file? Set that the user can download the original file
		# so the request box does not appear.
		$fulldownload=false;
		if ($sizes[$n]["id"]=="") {$fulldownload=true;}
		
		$counter++;

		# Should we allow this download?
		# If the download is allowed, show a download button, otherwise show a request button.
		$downloadthissize=resource_download_allowed($ref,$sizes[$n]["id"],$resource["resource_type"]);

		$headline=$sizes[$n]['id']=='' ? str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["originalfileoftype"])
				: $sizes[$n]["name"];
		if ($direct_link_previews && $downloadthissize)
			$headline=make_download_preview_link($ref, $sizes[$n],$headline);
		if ($hide_restricted_download_sizes && !$downloadthissize && !checkperm("q"))
			continue;

		if ($table_headers_drawn==false) { ?>
			<td><?php echo $lang["fileinformation"]?></td>
			<td><?php echo $lang["filesize"]?></td>
			<?php if ($showprice) { ?><td><?php echo $lang["price"] ?></td><?php } ?>
			<td class="textcenter"><?php echo $lang["options"]?></td>
			</tr>
 			<?php
			$table_headers_drawn=true;} ?>
		<tr class="DownloadDBlend" id="DownloadBox<?php echo $n?>">
		<td><h2><?php echo $headline?></h2><?php
		if (is_numeric($sizes[$n]["width"]))
			{
			echo get_size_info($sizes[$n]);
			}
		?></td><td><?php echo $sizes[$n]["filesize"]?></td>

		<?php if ($showprice) {
			?><td><?php echo get_display_price($ref, $sizes[$n]) ?></td>
		<?php } ?>

		<?php

		add_download_column($ref, $sizes[$n], $downloadthissize);
		?>
		</tr>
		<?php
		if (!hook("previewlinkbar")){
			if ($downloadthissize && $sizes[$n]["allow_preview"]==1)
				{ 
				# Add an extra line for previewing
				?> 
				<tr class="DownloadDBlend"><td><h2><?php echo $lang["preview"]?></h2><p><?php echo $lang["fullscreenpreview"]?></p></td><td><?php echo $sizes[$n]["filesize"]?></td>
				<?php if ($userrequestmode==2 || $userrequestmode==3) { ?><td></td><?php } # Blank spacer column if displaying a price above (basket mode).
				?>
				<td class="DownloadButton">
				<a class="enterLink" id="previewlink" href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref)?>&ext=<?php echo $resource["file_extension"]?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&<?php echo hook("previewextraurl") ?>"><?php echo $lang["action-view"]?></a>
				</td>
				</tr>
				<?php
				} 
			}
		} /* end hook previewlinkbar */
	}
elseif (strlen($resource["file_extension"])>0 && !($access==1 && $restricted_full_download==false))
	{
	# Files without multiple download sizes (i.e. no alternative previews generated).
	$counter++;
	$path=get_resource_path($ref,true,"",false,$resource["file_extension"]);
	if (file_exists($path))
		{
			if(!hook("origdownloadlink")):
		?>
		<tr class="DownloadDBlend">
		<td><h2><?php echo (isset($original_download_name)) ? str_replace_formatted_placeholder("%extension", $resource["file_extension"], $original_download_name, true) : str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["originalfileoftype"]); ?></h2></td>
		<td><?php echo formatfilesize(filesize_unlimited($path))?></td>
		<td class="DownloadButton">
		<?php if (!$direct_download || $save_as){ ?>
			<a <?php if (!hook("downloadlink","",array("ref=" . $ref . "&k=" . $k . "&ext=" . $resource["file_extension"] ))) { ?>href="<?php echo $baseurl_short?>pages/terms.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k)?>&search=<?php echo $search ?>&url=<?php echo urlencode("pages/download_progress.php?ref=" . $ref . "&ext=" . $resource["file_extension"] . "&k=" . $k . "&search=" . urlencode($search) . "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by=" . urlencode($order_by))?>"<?php } ?> onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-download"] ?></a>
		<?php } else { ?>
			<a href="#" onclick="directDownload('<?php echo $baseurl_short?>pages/download_progress.php?ref=<?php echo urlencode($ref)?>&ext=<?php echo $resource['file_extension']?>&k=<?php echo urlencode($k)?>')"><?php echo $lang["action-download"]?></a>
		<?php } // end if direct_download ?>
		</td>
		</tr>
		<?php
			endif; # hook origdownloadlink
		}
	} 
else
	{
	$nodownloads=true;
	}
	
if (($nodownloads || $counter==0) && !checkperm("T" . $resource["resource_type"] . "_"))
	{
	# No file. Link to request form.
	?>
	<tr class="DownloadDBlend">
	<td><h2><?php echo ($counter==0)?$lang["offlineresource"]:$lang["access1"]?></h2></td>
	<td><?php echo $lang["notavailableshort"]?></td>

	<?php if (checkperm("q"))
		{
		?>
		<?php if(!hook("resourcerequest")){?>
		<td class="DownloadButton"><a href="<?php echo $baseurl_short?>pages/resource_request.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k) ?>"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-request"]?></a></td>
		<?php } ?>
		<?php
		}
	else
		{
		?>
		<td class="DownloadButton DownloadDisabled"><?php echo $lang["access1"]?></td>
		<?php
		}
	?>
	</tr>
	<?php
	}
	
if (isset($flv_download) && $flv_download)
	{
	# Allow the FLV preview to be downloaded. $flv_download is set when showing the FLV preview video above.
	?>
	<tr class="DownloadDBlend">
	<td><h2><?php echo (isset($ffmpeg_preview_download_name)) ? $ffmpeg_preview_download_name : str_replace_formatted_placeholder("%extension", $ffmpeg_preview_extension, $lang["cell-fileoftype"]); ?></h2></td>
	<td><?php echo formatfilesize(filesize_unlimited($flvfile))?></td>
	<td class="DownloadButton">
	<?php if (!$direct_download || $save_as){?>
		<a href="<?php echo $baseurl_short?>pages/terms.php?ref=<?php echo urlencode($ref)?>&search=<?php echo $search ?>&k=<?php echo urlencode($k)?>&url=<?php echo urlencode("pages/download_progress.php?ref=" . $ref . "&ext=" . $ffmpeg_preview_extension . "&size=pre&k=" . $k . "&search=" . urlencode($search) . "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by=" . urlencode($order_by))?>"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-download"] ?></a>
	<?php } else { ?>
		<a href="#" onclick="directDownload('<?php echo $baseurl_short?>pages/download_progress.php?ref=<?php echo urlencode($ref)?>&ext=<?php echo $ffmpeg_preview_extension?>&size=pre&k=<?php echo urlencode($k)?>')"><?php echo $lang["action-download"]?></a>
	<?php } // end if direct_download ?></td>
	</tr>
	<?php
	}

hook("additionalresourcetools2");
	
# Alternative files listing
$alt_access=hook("altfilesaccess");
if ($access==0) $alt_access=true; # open access (not restricted)
if ($alt_access) 
	{
	$alt_order_by="";$alt_sort="";
	if ($alt_types_organize){$alt_order_by="alt_type";$alt_sort="asc";} 
	$altfiles=get_alternative_files($ref,$alt_order_by,$alt_sort);
	hook("processaltfiles");
	$last_alt_type="-";
	for ($n=0;$n<count($altfiles);$n++)
		{
		$alt_type=$altfiles[$n]['alt_type'];
		if ($alt_types_organize){
			if ($alt_type!=$last_alt_type){
				$alt_type_header=$alt_type;
				if ($alt_type_header==""){$alt_type_header=$lang["alternativefiles"];}
				?>
				<tr class="DownloadDBlend">
				<td colspan="3"><h2><?php echo $alt_type_header?></h2></td>
				</tr>
				<?php
			}
			$last_alt_type=$alt_type;
		}	
		else if ($n==0)
			{
			?>
			<tr>
			<td colspan="3"><?php echo $lang["alternativefiles"]?></td>
			</tr>
			<?php
			}	
		$alt_thm="";$alt_pre="";
		if ($alternative_file_previews)
			{
			$alt_thm_file=get_resource_path($ref,true,"col",false,"jpg",-1,1,false,"",$altfiles[$n]["ref"]);
			if (file_exists($alt_thm_file))
				{
				# Get web path for thumb (pass creation date to help cache refresh)
				$alt_thm=get_resource_path($ref,false,"col",false,"jpg",-1,1,false,$altfiles[$n]["creation_date"],$altfiles[$n]["ref"]);
				}
			$alt_pre_file=get_resource_path($ref,true,"pre",false,"jpg",-1,1,false,"",$altfiles[$n]["ref"]);
			if (file_exists($alt_pre_file))
				{
				# Get web path for preview (pass creation date to help cache refresh)
				$alt_pre=get_resource_path($ref,false,"pre",false,"jpg",-1,1,false,$altfiles[$n]["creation_date"],$altfiles[$n]["ref"]);
				}
			}
		?>
		<tr class="DownloadDBlend" <?php if ($alt_pre!="" && $alternative_file_previews_mouseover) { ?>onMouseOver="orig_preview=jQuery('#previewimage').attr('src');orig_width=jQuery('#previewimage').width();jQuery('#previewimage').attr('src','<?php echo $alt_pre ?>');jQuery('#previewimage').width(orig_width);" onMouseOut="jQuery('#previewimage').attr('src',orig_preview);"<?php } ?>>
		<td>
		<?php if(!hook("renderaltthumb")): ?>
		<?php if ($alt_thm!="") { ?><a href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref)?>&alternative=<?php echo $altfiles[$n]["ref"]?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&<?php echo hook("previewextraurl") ?>"><img src="<?php echo $alt_thm?>" class="AltThumb"></a><?php } ?>
		<?php endif; ?>
		<h2 class="breakall"><?php echo htmlspecialchars($altfiles[$n]["name"])?></h2>
		<p><?php echo htmlspecialchars($altfiles[$n]["description"])?></p>
		</td>
		<td><?php echo formatfilesize($altfiles[$n]["file_size"])?></td>
		
		<?php if ($userrequestmode==2 || $userrequestmode==3) { ?><td></td><?php } # Blank spacer column if displaying a price above (basket mode).
		?>
		
		<?php if ($access==0){?>
		<td class="DownloadButton">
		<?php 		
		if (!$direct_download || $save_as)
			{
			if(!hook("downloadbuttonreplace"))
				{
				?><a <?php if (!hook("downloadlink","",array("ref=" . $ref . "&alternative=" . $altfiles[$n]["ref"] . "&k=" . $k . "&ext=" . $altfiles[$n]["file_extension"]))) { ?>href="<?php echo $baseurl_short?>pages/terms.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search) ?>&url=<?php echo urlencode("pages/download_progress.php?ref=" . $ref . "&ext=" . $altfiles[$n]["file_extension"] . "&k=" . $k . "&alternative=" . $altfiles[$n]["ref"] . "&search=" . urlencode($search) . "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by=" . urlencode($order_by))?>"<?php } ?> onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-download"] ?></a><?php 
				}
			}
		else { ?>
			<a href="#" onclick="directDownload('<?php echo $baseurl_short?>pages/download_progress.php?ref=<?php echo urlencode($ref)?>&ext=<?php echo $altfiles[$n]["file_extension"]?>&k=<?php echo urlencode($k)?>&alternative=<?php echo $altfiles[$n]["ref"]?>')"><?php echo $lang["action-download"]?></a>
		<?php } // end if direct_download ?></td></td>
		<?php } else { ?>
		<td class="DownloadButton DownloadDisabled"><?php echo $lang["access1"]?></td>
		<?php } ?>
		</tr>
		<?php	
		}
        hook("morealtdownload");
	}
# --- end of alternative files listing

if ($use_mp3_player && file_exists($mp3realpath) && $access==0){
		include "mp3_play.php";
}

?>



</table>
<?php } ?>
<br />
<ul>
<?php 



# ----------------------------- Resource Actions -------------------------------------
hook ("resourceactions") ?>
<?php if ($k=="") { ?>
<?php if (!hook("replaceresourceactions")) {?>
	
	
	<?php if ((!checkperm("b"))
	&&
	
	(!(($userrequestmode==2 || $userrequestmode==3) && $basket_stores_size))
	
	) { ?>
	<li><?php echo add_to_collection_link($ref,$search)?>&gt; <?php echo $lang["action-addtocollection"]?></a></li>
	<?php if ($search=="!collection" . $usercollection) { ?><li><?php echo remove_from_collection_link($ref,$search)?>&gt; <?php echo $lang["action-removefromcollection"]?></a></li><?php } ?>
	<?php } ?>
	
	
	<?php if ($allow_share && ($access==0 || ($access==1 && $restricted_share))) { ?>
		<li><a href="<?php echo $baseurl_short?>pages/resource_email.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"  onClick="return CentralSpaceLoad(this,true);">&gt; <?php echo $lang["emailresource"]?></a></li>	
	<?php if (!$hide_resource_share_link) { ?>
		<li><a href="<?php echo $baseurl_short?>pages/resource_share.php?ref=<?php echo urlencode($ref) ?>" onClick="return CentralSpaceLoad(this,true);" >&gt; <?php echo $lang["share"]?></a></li>
   <?php } } ?>
	<?php if ($edit_access) { ?>
		<li><a href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"    onClick="return CentralSpaceLoad(this,true);">&gt; <?php echo $lang["action-edit"]?></a></li>
		<?php if ($metadata_download)	{ ?>
		<li><a href="<?php echo $baseurl_short?>pages/metadata_download.php?ref=<?php echo urlencode($ref)?>" onClick="return CentralSpaceLoad(this,true);" >&gt; <?php echo $lang["downloadmetadata"]?></a></li>
	<?php } ?>
	<?php if ((!checkperm("D") || hook('check_single_delete')) && !(isset($allow_resource_deletion) && !$allow_resource_deletion)){?><li><a href="<?php echo $baseurl_short?>pages/delete.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>" onClick="return CentralSpaceLoad(this,true);">&gt; <?php if ($resource["archive"]==3){echo $lang["action-delete_permanently"];} else {echo $lang["action-delete"];}?></a><?php } ?></li>
	<?php if (!$disable_alternative_files && !checkperm('A')) { ?>
	<li><a href="<?php echo $baseurl_short?>pages/alternative_files.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["managealternativefiles"]?></a></li><?php } ?>

	<?php } ?>
	<?php if (checkperm("e" . $resource["archive"])) { ?><li><a href="<?php echo $baseurl_short?>pages/log.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&search_offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>" onClick="return CentralSpaceLoad(this,true);">&gt; <?php echo $lang["log"]?></a></li><?php } ?>
	<?php if (checkperm("R") && $display_request_log_link) { ?><li><a href="<?php echo $baseurl_short?>pages/request_log.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>" onClick="return CentralSpaceLoad(this,true);">&gt; <?php echo $lang["requestlog"]?></a></li><?php } ?><?php
    } /* End replaceresourceactions */ 
hook("afterresourceactions");
hook("afterresourceactions2");
?>
<?php } /* End if ($k!="")*/ 
hook("resourceactions_anonymous");
?>
<?php } /* End of renderinnerresourcedownloadspace hook */ ?>
</ul>
<div class="clearerleft"> </div>

<?php
if (!hook("replaceuserratingsbox")){
# Include user rating box, if enabled and the user is not external.
if ($user_rating && $k=="") { include "../include/user_rating.php"; }
} /* end hook replaceuserratingsbox */


?>


</div>
<?php } /* End of renderresourcedownloadspace hook */ ?>
<?php } /* End of renderinnerresourceview hook */ ?>
</div>

<?php hook("renderbeforeresourcedetails"); ?>

<div class="Title"><?php if (!hook("customdetailstitle")) echo $lang["resourcedetails"]?></div>

<?php
$extra="";

#  -----------------------------  Draw tabs ---------------------------
$tabname="";
$tabcount=0;
if (count($fields)>0 && $fields[0]["tab_name"]!="")
	{ 
	?>
	<div class="TabBar">
	<?php
	$extra="";
	$tabname="";
	$tabcount=0;
	for ($n=0;$n<count($fields);$n++)
		{	
		$value=$fields[$n]["value"];

		# draw new tab?
		if (($tabname!=$fields[$n]["tab_name"]) && ($value!="") && ($value!=",") && ($fields[$n]["display_field"]==1))
			{
			?><div id="tabswitch<?php echo $tabcount?>" class="Tab<?php if ($tabcount==0) { ?> TabSelected<?php } ?>"><a href="#" onclick="SelectTab(<?php echo $tabcount?>);return false;"><?php echo i18n_get_translated($fields[$n]["tab_name"])?></a></div><?php
			$tabcount++;
			$tabname=$fields[$n]["tab_name"];
			}
		}
	?>
	</div>
	<script type="text/javascript">
	function SelectTab(tab)
		{
		// Deselect all tabs
		<?php for ($n=0;$n<$tabcount;$n++) { ?>
		document.getElementById("tab<?php echo $n?>").style.display="none";
		document.getElementById("tabswitch<?php echo $n?>").className="Tab";
		<?php } ?>
		document.getElementById("tab" + tab).style.display="block";
		document.getElementById("tabswitch" + tab).className="Tab TabSelected";
		}
	</script>
	<?php
	}
	
	
	
?>

<div id="tab0" class="TabbedPanel<?php if ($tabcount>0) { ?> StyledTabbedPanel<?php } ?>">
<div class="clearerleft"> </div>
<div>
<?php 
#  ----------------------------- Draw standard fields ------------------------
?>
<?php if ($show_resourceid) { ?><div class="itemNarrow"><h3><?php echo $lang["resourceid"]?></h3><p><?php echo htmlspecialchars($ref)?></p></div><?php } ?>
<?php if ($show_access_field) { ?><div class="itemNarrow"><h3><?php echo $lang["access"]?></h3><p><?php echo @$lang["access" . $resource["access"]]?></p></div><?php } ?>
<?php if ($show_resource_type) { ?><div class="itemNarrow"><h3><?php echo $lang["resourcetype"]?></h3><p><?php echo  get_resource_type_name($resource["resource_type"])?></p></div><?php } ?>
<?php if ($show_hitcount){ ?><div class="itemNarrow"><h3><?php echo $resource_hit_count_on_downloads?$lang["downloads"]:$lang["hitcount"]?></h3><p><?php echo $resource["hit_count"]+$resource["new_hit_count"]?></p></div><?php } ?>
<?php hook("extrafields");?>
<?php
# contributed by field
if (!hook("replacecontributedbyfield")){
$udata=get_user($resource["created_by"]);
if ($udata!==false)
	{
	?>
<?php if ($show_contributed_by){?>	<div class="itemNarrow"><h3><?php echo $lang["contributedby"]?></h3><p><?php if (checkperm("u")) { ?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/team/team_user_edit.php?ref=<?php echo $udata["ref"]?>"><?php } ?><?php echo highlightkeywords(htmlspecialchars($udata["fullname"]),$search)?><?php if (checkperm("u")) { ?></a><?php } ?></p></div><?php } ?>
	<?php
	}
} // end hook replacecontributedby

# Show field data
$tabname="";
$tabcount=0;
$fieldcount=0;
$extra="";
$tmp = hook("tweakfielddisp", "", array($ref, $fields)); if($tmp) $fields = $tmp;
for ($n=0;$n<count($fields);$n++)
	{
	
	#Check if field has a display condition set
	$displaycondition=true;
	if ($fields[$n]["display_condition"]!="")
		{
		//echo $fields[$n]["display_condition"] . "<br>";
		$fieldstocheck=array(); #' Set up array to use in jQuery script function
		$s=explode(";",$fields[$n]["display_condition"]);
		$condref=0;
		foreach ($s as $condition) # Check each condition
			{
			$displayconditioncheck=false;
			$s=explode("=",$condition);
			for ($cf=0;$cf<count($fields);$cf++) # Check each field to see if needs to be checked
				{
				if ($s[0]==$fields[$cf]["name"]) # this field needs to be checked
					{					
					$checkvalues=$s[1];
					$validvalues=explode("|",strtoupper($checkvalues));
					$v=trim_array(explode(",",strtoupper($fields[$cf]["value"])));
					foreach ($validvalues as $validvalue)
						{
						if (in_array($validvalue,$v)) {$displayconditioncheck=true;} # this is  a valid value						
						}
					if (!$displayconditioncheck) {$displaycondition=false;}					
					}
					
				} # see if next field needs to be checked
							
			$condref++;
			} # check next condition	
		
		}	
	
	
	
	
	
	
	if ($displaycondition)
		{
		if (!hook("renderfield")) {
			$value=$fields[$n]["value"];
			
			# Handle expiry fields
			if ($fields[$n]["type"]==6 && $value!="" && $value<=date("Y-m-d H:i") && $show_expiry_warning) 
				{
				$extra.="<div class=\"RecordStory\"> <h1>" . $lang["warningexpired"] . "</h1><p>" . $lang["warningexpiredtext"] . "</p><p id=\"WarningOK\"><a href=\"#\" onClick=\"document.getElementById('RecordDownload').style.display='block';document.getElementById('WarningOK').style.display='none';\">" . $lang["warningexpiredok"] . "</a></p></div><style>#RecordDownload {display:none;}</style>";
				}
			
			if (($value!="") && ($value!=",") && ($fields[$n]["display_field"]==1) && ($access==0 || ($access==1 && !$fields[$n]["hide_when_restricted"])))
				{
				$title=htmlspecialchars(str_replace("Keywords - ","",$fields[$n]["title"]));
				//if ($fields[$n]["type"]==4 || $fields[$n]["type"]==6) {$value=NiceDate($value,false,true);}

				# Value formatting
				if (($fields[$n]["type"]==2) || ($fields[$n]["type"]==7) || ($fields[$n]["type"]==9))
					{$i18n_split_keywords =true;}
				else 	{$i18n_split_keywords =false;}
				$value=i18n_get_translated($value,$i18n_split_keywords );
				if (($fields[$n]["type"]==2) || ($fields[$n]["type"]==3) || ($fields[$n]["type"]==7) || ($fields[$n]["type"]==9)) {$value=TidyList($value);}
				$value_unformatted=$value; # store unformatted value for replacement also

				if ($fields[$n]["type"]!=8) # Do not convert HTML formatted fields (that are already HTML) to HTML.
					{
					$value=nl2br(htmlspecialchars($value));
					}
				
				# draw new tab panel?
				if (($tabname!=$fields[$n]["tab_name"]) && ($fieldcount>0))
					{
					$tabcount++;
					# Also display the custom formatted data $extra at the bottom of this tab panel.
					?><div class="clearerleft"> </div><?php echo $extra?></div></div><div class="TabbedPanel StyledTabbedPanel" style="display:none;" id="tab<?php echo $tabcount?>"><div><?php	
					$extra="";
					}
				$tabname=$fields[$n]["tab_name"];
				$fieldcount++;		

				if (trim($fields[$n]["display_template"])!="")
					{
					# Process the value using a plugin
					$plugin="../plugins/value_filter_" . $fields[$n]["name"] . ".php";
					if ($fields[$n]['value_filter']!=""){
						eval($fields[$n]['value_filter']);
					}
					else if (file_exists($plugin)) {include $plugin;}
					else if ($fields[$n]["type"]==4 || $fields[$n]["type"]==6) { 
						$value=NiceDate($value,false,true);
					}
					
					# Highlight keywords
					$value=highlightkeywords($value,$search,$fields[$n]["partial_index"],$fields[$n]["name"],$fields[$n]["keywords_index"]);

					# Use a display template to render this field
					$template=$fields[$n]["display_template"];
					$template=str_replace("[title]",$title,$template);
					$template=str_replace("[value]",$value,$template);
					$template=str_replace("[value_unformatted]",$value_unformatted,$template);
					$template=str_replace("[ref]",$ref,$template);
					$extra.=$template;
					}
				else
					{
					#There is a value in this field, but we also need to check again for a current-language value after the i18n_get_translated() function was called, to avoid drawing empty fields
					if ($value!=""){
						# Draw this field normally.
						
						
							# value filter plugin should be used regardless of whether a display template is used.
							$plugin="../plugins/value_filter_" . $fields[$n]["name"] . ".php";
							if ($fields[$n]['value_filter']!=""){
								eval($fields[$n]['value_filter']);
							}
							else if (file_exists($plugin)) {include $plugin;}
							else if ($fields[$n]["type"]==4 || $fields[$n]["type"]==6) { 
								$value=NiceDate($value,false,true);
							}
						
						# Highlight keywords
						$value=highlightkeywords($value,$search,$fields[$n]["partial_index"],$fields[$n]["name"],$fields[$n]["keywords_index"]);
						?><div class="itemNarrow"><h3><?php echo $title?></h3><p><?php echo $value?></p></div><?php
						}
					}
				}
			}
		}
	}
?><?php hook("extrafields2");?><div class="clearerleft"></div>
<?php echo $extra?>
</div>
</div>
<?php hook("renderafterresourcedetails"); ?>
<!-- end of tabbed panel-->
</div></div>
<div class="PanelShadow"></div>
</div>

<?php hook("custompanels"); //For custom panels immediately below resource display area ?>

<?php 
if (!$disable_geocoding) { 
  // only show this section if the resource is geocoded OR they have permission to do it themselves
  if ($edit_access||($resource["geo_lat"]!="" && $resource["geo_long"]!=""))
  		{ 
		include "../include/geocoding_view.php";
	  	} 
 	} 
?>

<?php 
	if ($comments_resource_enable && $k=="") include_once ("../include/comment_resources.php");
?>
	  	  
<?php hook("w2pspawn");?>

<?php 
// include collections listing
if ($view_resource_collections){ ?>
	<div id="resourcecollections"></div>
	<script type="text/javascript">
	jQuery("#resourcecollections").load('<?php echo $baseurl_short?>pages/resource_collection_list.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k)?>'); 
	</script>
	<?php }

// include optional ajax metadata report
if ($metadata_report && isset($exiftool_path) && $k==""){?>
        <div class="RecordBox">
        <div class="RecordPanel">  
        <div class="Title"><?php echo $lang['metadata-report']?></div>
        <div id="metadata_report"><a onclick="metadataReport(<?php echo htmlspecialchars($ref)?>);document.getElementById('metadata_report').innerHTML='<?php echo $lang['pleasewait']?>';return false;" class="itemNarrow" href="#">&gt; <?php echo $lang['viewreport'];?></a><br></div>
        </div>
        <div class="PanelShadow"></div>
        </div>

<?php } ?>

<?php hook("customrelations"); //For future template/spawned relations in Web to Print plugin ?>

<?php
# -------- Related Resources (must be able to search for this to work)
if ($enable_related_resources && checkperm("s") && ($k=="")) {
$result=do_search("!related" . $ref);
if (count($result)>0) 
	{
	# -------- Related Resources by File Extension
	if($sort_relations_by_filetype){	
		#build array of related resources' file extensions
		for ($n=0;$n<count($result);$n++){
			$related_file_extension=$result[$n]["file_extension"];
			$related_file_extensions[]=$related_file_extension;
			}
		#reduce extensions array to unique values
		$related_file_extensions=array_unique($related_file_extensions);
		$count_extensions=0;
		foreach($related_file_extensions as $rext){
		?><!--Panel for related resources-->
		<div class="RecordBox">
		<div class="RecordPanel">  

		<div class="RecordResouce">
		<div class="Title"><?php echo str_replace_formatted_placeholder("%extension", $rext, $lang["relatedresources-filename_extension"]); ?></div>
		<?php
		# loop and display the results by file extension
		for ($n=0;$n<count($result);$n++)			
			{	
			if ($result[$n]["file_extension"]==$rext){
				$rref=$result[$n]["ref"];
				$title=$result[$n]["field".$view_title_field];

				# swap title fields if necessary

				if (isset($metadata_template_title_field) && isset($metadata_template_resource_type))
					{
					if ($result[$n]['resource_type']==$metadata_template_resource_type)
						{
						$title=$result[$n]["field".$metadata_template_title_field];
						}	
					}	
						
				?>
				
				<!--Resource Panel-->
				<div class="CollectionPanelShell">
				<table border="0" class="CollectionResourceAlign"><tr><td>
				<a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $rref?>&search=<?php echo urlencode("!related" . $ref)?>" onClick="return CentralSpaceLoad(this,true);"><?php if ($result[$n]["has_image"]==1) { ?><img border=0 src="<?php echo get_resource_path($rref,false,"col",false,$result[$n]["preview_extension"],-1,1,checkperm("w"),$result[$n]["file_modified"])?>" class="CollectImageBorder"/><?php } else { ?><img border=0 src="../gfx/<?php echo get_nopreview_icon($result[$n]["resource_type"],$result[$n]["file_extension"],true)?>"/><?php } ?></a></td>
				</tr></table>
				<div class="CollectionPanelInfo"><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $rref?>" onClick="return CentralSpaceLoad(this,true);"><?php echo tidy_trim(i18n_get_translated($title),15)?></a>&nbsp;</div>
				<?php hook("relatedresourceaddlink");?>
				</div>
				<?php		
				}
			}
		?>
		<div class="clearerleft"> </div>
		<?php $count_extensions++; if ($count_extensions==count($related_file_extensions)){?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!related" . $ref) ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["clicktoviewasresultset"]?></a><?php }?>
		</div>
		</div>
		<div class="PanelShadow"></div>
		</div><?php
		} #end of display loop by resource extension
	} #end of IF sorted relations
	
	elseif($sort_relations_by_restype){	
		#build array of related resources' file extensions
		for ($n=0;$n<count($result);$n++){
			$related_restype=$result[$n]["resource_type"];
			$related_restypes[]=$related_restype;
			}
		#reduce extensions array to unique values
		$related_restypes=array_unique($related_restypes);
		$count_restypes=0;
		foreach($related_restypes as $rtype){
		$restypename=sql_value("select name as value from resource_type where ref = '$rtype'","");
        $restypename = lang_or_i18n_get_translated($restypename, "resourcetype-", "-2");
		?><!--Panel for related resources-->
		<div class="RecordBox">
		<div class="RecordPanel">  

		<div class="RecordResouce">
		<div class="Title"><?php echo str_replace_formatted_placeholder("%restype%", $restypename, $lang["relatedresources-restype"]); ?></div>
		<?php
		# loop and display the results by file extension
		for ($n=0;$n<count($result);$n++)			
			{	
			if ($result[$n]["resource_type"]==$rtype){
				$rref=$result[$n]["ref"];
				$title=$result[$n]["field".$view_title_field];

				# swap title fields if necessary

				if (isset($metadata_template_title_field) && isset($metadata_template_resource_type))
					{
					if ($result[$n]['resource_type']==$metadata_template_resource_type)
						{
						$title=$result[$n]["field".$metadata_template_title_field];
						}	
					}	
						
				?>
				
				<!--Resource Panel-->
				<div class="CollectionPanelShell">
				<table border="0" class="CollectionResourceAlign"><tr><td>
				<a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $rref?>&search=<?php echo urlencode("!related" . $ref)?>" onClick="return CentralSpaceLoad(this,true);"><?php if ($result[$n]["has_image"]==1) { ?><img border=0 src="<?php echo get_resource_path($rref,false,"col",false,$result[$n]["preview_extension"],-1,1,checkperm("w"),$result[$n]["file_modified"])?>" class="CollectImageBorder"/><?php } else { ?><img border=0 src="../gfx/<?php echo get_nopreview_icon($result[$n]["resource_type"],$result[$n]["file_extension"],true)?>"/><?php } ?></a></td>
				</tr></table>
				<div class="CollectionPanelInfo"><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $rref?>" onClick="return CentralSpaceLoad(this,true);"><?php echo tidy_trim(i18n_get_translated($title),15)?></a>&nbsp;</div>
				<?php hook("relatedresourceaddlink");?>
				</div>
				<?php		
				}
			}
		?>
		<div class="clearerleft"> </div>
		<?php $count_restypes++; if ($count_restypes==count($related_restypes)){?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!related" . $ref) ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["clicktoviewasresultset"]?></a><?php }?>
		</div>
		</div>
		<div class="PanelShadow"></div>
		</div><?php
		} #end of display loop by resource extension
	} #end of IF sorted relations	
	
	
	# -------- Related Resources (Default)
	else { 
		 ?><!--Panel for related resources-->
		<div class="RecordBox">
		<div class="RecordPanel">  

		<div class="RecordResouce">
		<div class="Title"><?php echo $lang["relatedresources"]?></div>
		<?php
    	# loop and display the results
    	for ($n=0;$n<count($result);$n++)            
        	{
        	$rref=$result[$n]["ref"];
			$title=$result[$n]["field".$view_title_field];

			# swap title fields if necessary

			if (isset($metadata_template_title_field) && isset($metadata_template_resource_type))
				{
				if ($result[$n]["resource_type"]==$metadata_template_resource_type)
					{
					$title=$result[$n]["field".$metadata_template_title_field];
					}	
				}	
	

			?>
        	<!--Resource Panel-->
        	<div class="CollectionPanelShell">
            <table border="0" class="CollectionResourceAlign"><tr><td>
            <a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $rref?>&search=<?php echo urlencode("!related" . $ref)?>" onClick="return CentralSpaceLoad(this,true);"><?php if ($result[$n]["has_image"]==1) { ?><img border=0 src="<?php echo get_resource_path($rref,false,"col",false,$result[$n]["preview_extension"],-1,1,checkperm("w"),$result[$n]["file_modified"])?>" class="CollectImageBorder"/><?php } else { ?><img border=0 src="../gfx/<?php echo get_nopreview_icon($result[$n]["resource_type"],$result[$n]["file_extension"],true)?>"/><?php } ?></a></td>
            </tr></table>
            <div class="CollectionPanelInfo"><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $rref?>" onClick="return CentralSpaceLoad(this,true);"><?php echo tidy_trim(i18n_get_translated($title),15)?></a>&nbsp;</div>
				<?php hook("relatedresourceaddlink");?>       
       </div>
        <?php        
        }
    ?>
    <div class="clearerleft"> </div>
        <a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!related" . $ref) ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["clicktoviewasresultset"]?></a>

    </div>
    </div>
    <div class="PanelShadow"></div>
    </div><?php
		}# end related resources display
	} 
	# -------- End Related Resources
	
	

if ($show_related_themes==true ){
# -------- Public Collections / Themes
$result=get_themes_by_resource($ref);
if (count($result)>0) 
	{
	?><!--Panel for related themes / collections -->
	<div class="RecordBox">
	<div class="RecordPanel">  
	
	<div class="RecordResouce BasicsBox nopadding">
	<div class="Title"><?php echo $lang["collectionsthemes"]?></div>

	<?php
		# loop and display the results
		for ($n=0;$n<count($result);$n++)			
			{
			?>
			<a href="<?php echo $baseurl_short?>pages/search.php?search=!collection<?php echo $result[$n]["ref"]?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo (strlen($result[$n]["theme"])>0)?htmlspecialchars(str_replace("*","",i18n_get_translated($result[$n]["theme"])) . " / "):$lang["public"] . " : "; ?><?php if (!$collection_public_hide_owner) {echo htmlspecialchars($result[$n]["fullname"] . " / ");} ?><?php echo i18n_get_collection_name($result[$n]); ?></a><br />
			<?php		
			}
		?>
	
	</div>
	</div>
	<div class="PanelShadow"></div>
	</div><?php
	}} 
?>



<?php if ($enable_find_similar) { ?>
<!--Panel for search for similar resources-->
<div class="RecordBox">
<div class="RecordPanel"> 


<div class="RecordResouce">
<div class="Title"><?php echo $lang["searchforsimilarresources"]?></div>
<?php if ($resource["has_image"]==1) { ?>

<!--
<p>Find resources with a <a href="search.php?search=<?php echo urlencode("!rgb:" . $resource["image_red"] . "," . $resource["image_green"] . "," . $resource["image_blue"])?>">similar colour theme</a>.</p>
<p>Find resources with a <a href="search.php?search=<?php echo urlencode("!colourkey" . $resource["colour_key"]) ?>">similar colour theme (2)</a>.</p>
-->

<?php } ?>
<script type="text/javascript">
function UpdateFSResultCount()
	{
	// set the target of the form to be the result count iframe and submit

	// some pages are erroneously calling this function because it exists in unexpected
	// places due to dynamic page loading. So only do it if it seems likely to work.
	if(jQuery('#findsimilar').length > 0)
		{
		document.getElementById("findsimilar").target="resultcount";
		document.getElementById("countonly").value="yes";
		document.getElementById("findsimilar").submit();
		document.getElementById("findsimilar").target="";
		document.getElementById("countonly").value="";
		}
	}
</script>

<form method="post" action="<?php echo $baseurl_short?>pages/find_similar.php" id="findsimilar">
<input type="hidden" name="resource_type" value="<?php echo $resource["resource_type"]?>">
<input type="hidden" name="countonly" id="countonly" value="">
<?php
$keywords=get_resource_top_keywords($ref,30);
$keywords = array_values(array_unique($keywords));
$searchwords=split_keywords($search);
for ($n=0;$n<count($keywords);$n++)
	{
	?>
	<div class="SearchSimilar"><input type=checkbox name="keyword_<?php echo urlencode($keywords[$n])?>" value="yes"
	<?php if (in_array($keywords[$n],$searchwords)) {?>checked<?php } ?> onClick="UpdateFSResultCount();">&nbsp;<?php echo htmlspecialchars($keywords[$n])?></div>
	<?php
	}
?>
<div class="clearerleft"> </div>
<br />
<input name="search" type="submit" value="&nbsp;&nbsp;<?php echo $lang["searchbutton"]?>&nbsp;&nbsp;" id="dosearch"/>
<iframe src="<?php echo $baseurl_short?>pages/blank.html" frameborder=0 scrolling=no width=1 height=1 style="visibility:hidden;" name="resultcount" id="resultcount"></iframe>
</form>
<div class="clearerleft"> </div>
</div>
</div>
<div class="PanelShadow"></div>
</div>
<?php } ?>



<?php } # end of block that requires search permissions


include "../include/footer.php";
?>