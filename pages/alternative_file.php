<?php
include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/resource_functions.php";
include "../include/image_processing.php";
include "../plugins/mediaapi/stdlib.php";

$ref=getvalescaped("ref","",true);

$search=getvalescaped("search","");
$offset=getvalescaped("offset","",true);
$order_by=getvalescaped("order_by","");
$archive=getvalescaped("archive","",true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}

$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);

$resource=getvalescaped("resource","",true);

# Fetch resource data.
$resourcedata=get_resource_data($resource);

# Not allowed to edit this resource?
if ((!get_edit_access($resource, $resourcedata["archive"],false,$resourcedata) || checkperm('A')) && $resource>0) {exit ("Permission denied.");}

hook("pageevaluation");

# Fetch alternative file data
$file=get_alternative_file($resource,$ref);if ($file===false) {exit("Alternative file not found.");}

if (getval("name","")!="")
	{
	hook("markmanualupload");
	# Save file data
	save_alternative_file($resource,$ref);
	hook ("savealternatefiledata");
	redirect ($baseurl_short."pages/alternative_files.php?ref=$resource&search=".urlencode($search)."&offset=$offset&order_by=$order_by&sort=$sort&archive=$archive");
	}

$mediaapi_derivatives = mediaapi_get_derivative_resources($ref);
$mediaapi_derivatives = !empty($mediaapi_derivatives) ? $mediaapi_derivatives[0] : null;

include "../include/header.php";
?>
<div class="BasicsBox">
<p>
<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/alternative_files.php?ref=<?php echo $resource?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>">&lt;&nbsp;<?php echo $lang["backtomanagealternativefiles"]?></a><br / >
<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo $resource?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>">&lt;&nbsp;<?php echo $lang["backtoeditresource"]?></a><br / >
<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $resource?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a>
</p>
<?php if ($alternative_file_resource_preview){
		$imgpath=get_resource_path($resourcedata['ref'],true,"col",false);
		if (file_exists($imgpath)){ ?><img src="<?php echo get_resource_path($resourcedata['ref'],false,"col",false);?>"/><?php }
	} ?>
	<?php if ($alternative_file_resource_title){
		echo "<h2>".i18n_get_translated($resourcedata['field'.$view_title_field])."</h2><br/>";
	}?>

<h1><?php echo $lang["editalternativefile"]?></h1>


<form method="post" class="form" id="fileform" enctype="multipart/form-data" action="<?php echo $baseurl_short?>pages/alternative_file.php?search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>">
<input type="hidden" name="MAX_FILE_SIZE" value="500000000">
<input type=hidden name=ref value="<?php echo htmlspecialchars($ref) ?>">
<input type=hidden name=resource value="<?php echo htmlspecialchars($resource) ?>">


<div class="Question">
<label><?php echo $lang["resourceid"]?></label><div class="Fixed"><?php echo htmlspecialchars($resource) ?></div>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label for="name"><?php echo $lang["name"]?></label><input type=text class="stdwidth" name="name" id="name" value="<?php echo htmlspecialchars($file["name"]) ?>" maxlength="100">
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label for="name"><?php echo $lang["description"]?></label><input type=text class="stdwidth" name="description" id="description" value="<?php echo htmlspecialchars($file["description"]) ?>" maxlength="200">
<div class="clearerleft"> </div>
</div>

<?php
	// if the system is configured to support a type selector for alt files, show it
	if (isset($alt_types) && count($alt_types) > 1){
		echo "<div class='Question'>\n<label for='alt_type'>".$lang["alternatetype"]."</label><select name='alt_type' id='alt_type'>";
		foreach($alt_types as $thealttype){
			//echo "thealttype:$thealttype: / filealttype:" . $file['alt_type'].":";
			if ($thealttype == $file['alt_type']){$alt_type_selected = " selected='selected'"; } else { $alt_type_selected = ''; }
			$thealttype = htmlspecialchars($thealttype,ENT_QUOTES);
			echo "\n   <option value='$thealttype' $alt_type_selected >$thealttype</option>";
		}
		echo "\n</select>\n<div class='clearerleft'> </div>\n</div>";
	}
?>


<div class="Question">
<label for="userfile"><?php echo $file["file_extension"]=="" ? $lang["file"] : $lang["uploadreplacementfile"] ?></label>
<input type=file name=userfile id=userfile size="80">
<div class="clearerleft"> </div>
</div>

<h1>Dirivative Specific</h1>

<div class="Question">
<label>Derivative ID</label><div class="Fixed">
    <?php $derivative_id = !empty($mediaapi_derivatives['derivative_id']) ? $mediaapi_derivatives['derivative_id'] : ''; ?>
    <input type="hidden" name="derivative" value="<?php echo $derivative_id; ?>">
    <?php echo htmlspecialchars($derivative_id); ?>
</div>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label>Media Object ID</label><div class="Fixed"><?php echo !empty($mediaapi_derivatives['media_object_id']) ? $mediaapi_derivatives['media_object_id'] : ''; ?></div>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label>Media Server ID</label><div class="Fixed"><?php echo !empty($mediaapi_derivatives['media_server_id']) ? $mediaapi_derivatives['media_server_id'] : ''; ?></div>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<?php $short_name = !empty($mediaapi_derivatives['short_name']) ? $mediaapi_derivatives['short_name'] : ''; ?>
<label for="name">Short name</label><input type=text class="stdwidth" name="short_name" id="short_name" value="<?php echo htmlspecialchars($short_name); ?>" maxlength="100">
<div class="clearerleft"> </div>
</div>

<div class="Question">
<?php $prefix = !empty($mediaapi_derivatives['prefix']) ? $mediaapi_derivatives['prefix'] : ''; ?>
<label for="name">Prefix</label><input type=text class="stdwidth" name="prefix" id="prefix" value="<?php echo htmlspecialchars($prefix) ?>" maxlength="100">
<div class="clearerleft"> </div>
</div>

<div class="Question">
<?php $file_path = !empty($mediaapi_derivatives['file_path']) ? $mediaapi_derivatives['file_path'] : ''; ?>
<label for="name">File path</label><input type=text class="stdwidth" name="file_path" id="file_path" value="<?php echo htmlspecialchars($file_path) ?>" maxlength="100">
<div class="clearerleft"> </div>
</div>

<div class="Question">
<?php $file_name = !empty($mediaapi_derivatives['file_name']) ? $mediaapi_derivatives['file_name'] : ''; ?>
<label for="name">File name</label><input type=text class="stdwidth" name="file_name" id="file_name" value="<?php echo htmlspecialchars($file_name) ?>" maxlength="100">
<div class="clearerleft"> </div>
</div>

<div class="Question">
<?php $file_extension = !empty($mediaapi_derivatives['file_extension']) ? $mediaapi_derivatives['file_extension'] : ''; ?>
<label for="name">File extension</label><input type=text class="stdwidth" name="file_extension" id="file_extension" value="<?php echo htmlspecialchars($file_extension) ?>" maxlength="100">
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label for="name">Use extension</label>
    <?php $use_extension = !empty($mediaapi_derivatives['use_extension']) ? $mediaapi_derivatives['use_extension'] : ''; ?>
    <select class="stdwidth" name="use_extension" id="use_extension">
       <option <?php echo ($use_extension == '')  ? 'selected="selected"' : ''; ?> value=""></option>
       <option <?php echo ($use_extension == 'n') ? 'selected="selected"' : ''; ?> value="n">No</option>
	   <option <?php echo ($use_extension == 'y') ? 'selected="selected"' : ''; ?> value="y">Yes</option>
	</select>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label for="name">Is downloadable</label>
    <?php $is_downloadable = !empty($mediaapi_derivatives['is_downloadable']) ? $mediaapi_derivatives['is_downloadable'] : ''; ?>
    <select class="stdwidth" name="is_downloadable" id="is_downloadable">
       <option <?php echo ($is_downloadable == '')   ? 'selected="selected"' : ''; ?> value=""></option>
       <option <?php echo ($is_downloadable == 'n')  ? 'selected="selected"' : ''; ?> value="n">No</option>
	   <option <?php echo ($is_downloadable == 'y')  ? 'selected="selected"' : ''; ?> value="y">Yes</option>
	</select>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label for="name">Is streamable</label>
    <?php $is_streamable = !empty($mediaapi_derivatives['is_streamable']) ? $mediaapi_derivatives['is_streamable'] : ''; ?>
    <select class="stdwidth" name="is_streamable" id="is_streamable">
       <option <?php echo ($is_streamable == '')   ? 'selected="selected"' : ''; ?> value=""></option>
       <option <?php echo ($is_streamable == 'n')  ? 'selected="selected"' : ''; ?> value="n">No</option>
	   <option <?php echo ($is_streamable == 'y')  ? 'selected="selected"' : ''; ?> value="y">Yes</option>
	</select>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label for="name">Is primary</label>
    <?php $is_primary = !empty($mediaapi_derivatives['is_primary']) ? $mediaapi_derivatives['is_primary'] : ''; ?>
    <select class="stdwidth" name="is_primary" id="is_primary">
       <option <?php echo ($is_primary == '')   ? 'selected="selected"' : ''; ?> value=""></option>
       <option <?php echo ($is_primary == 'n')  ? 'selected="selected"' : ''; ?> value="n">No</option>
	   <option <?php echo ($is_primary == 'y')  ? 'selected="selected"' : ''; ?> value="y">Yes</option>
	</select>
<div class="clearerleft"> </div>
</div>

<div class="QuestionSubmit">
<label for="buttons"> </label>
<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" />
</div>
</form>
</div>

<?php
include "../include/footer.php";
?>
