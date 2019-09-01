<?php
/*
 * This takes a RGB color and returns back a string that closely matches what would be the nearest map color
 * @see https://minecraft.gamepedia.com/Wool
 */
function minecraft_block($red = 0, $green = 0, $blue = 0)
{
	// Load our block map data from yaml
	$block_map = "./block2rgb.yml";
	$block_map_data = yaml_parse(file_get_contents($block_map));
	$block = [];
	foreach ($block_map_data as $blk => $rgb)
	{
		$clrs = explode(",", $rgb);
		if (count($clrs) == 3)
		{
			$block[$blk] = ["r" => $clrs[0], "g" => $clrs[1], "b" => $clrs[2]];
		}
	}

	// 3D-Distance color mapping
	$prospective_block = "";
	$prospective_distance = "";
	foreach ($block as $color => $rgb)
	{
		// gonna do the 'distance' formula with all these colors, and pick the lowest number
		$distance = sqrt(pow($rgb['r'] - $red, 2) + pow($rgb['g'] - $green, 2) + pow($rgb['b'] - $blue, 2));
		if ($prospective_distance == "")
		{
			$prospective_block = $color;
			$prospective_distance = $distance;
		}
		if ($distance < $prospective_distance)
		{
			$prospective_block = $color;
			$prospective_distance = $distance;
		}
	}
	return $prospective_block;
}
$minecraft_function = null;
$point_x = null;
$point_z = null;
if (array_key_exists('x', $_GET))
{
	$point_x = intval($_GET['x']);
}
if (array_key_exists('z', $_GET))
{
	$point_z = intval($_GET['z']);
}

if (array_key_exists('x', $_POST) && array_key_exists('z', $_POST) && array_key_exists('logo', $_FILES))
{
	$error = "";
	$x = intval($_POST['x']);
	$point_x = $x;
	$z = intval($_POST['z']);
	$point_z = $z;
	$logo = $_FILES['logo'];

	// step 1, teleport to middle
	$minecraft_function = "tp " . ($x + 64) . " 32 " . ($z + 64) . "\n";

	// step 2, draw an image
	$good_file = false;
	$filename = $logo["tmp_name"];
	$img_width = 0;
	$img_height = 0;
	try
	{
		$imgdata = getimagesize($filename);
		if (!is_array($imgdata))
		{
			throw new Exception("Uploaded file is not an image");
		}
		$width = $imgdata[0];
		$height = $imgdata[1];
		if (($width != 128) || ($height != 128))
		{
			throw new Exception("Image dimensions are not 128x128 (${width}x${height})");
		}
		$i = imagecreatefromstring(file_get_contents($filename));
		if ($i === false)
		{
			throw new Exception("Cannot create image resource from file with imagecreatefromstring()");
		}
		$width = imagesx($i);
		$height = imagesy($i);
		if (($width != 128) || ($height != 128))
		{
			throw new Exception("Image dimensions from resource are not 128x128 (${width}x${height})");
		}
		for ($ix = 0; $ix < 128; $ix++)
		{
			for ($iz = 0; $iz < 128; $iz++)
			{
				$clr_rgb = imagecolorat($i, $ix, $iz);
				$clr_r = ($clr_rgb >> 16) & 0xFF;
				$clr_g = ($clr_rgb >> 8) & 0xFF;
				$clr_b = $clr_rgb & 0xFF;

				$wool = minecraft_block($clr_r, $clr_g, $clr_b);
				$minecraft_function .= "setblock " . ($x + $ix) . " 4 " . ($z + $iz) . " " . $wool . "\n";
			}
		}
	}
	catch (Exception $e)
	{
		header("Location: ./?e=" . urlencode($e->getMessage()) . '&x=' . urlencode($point_x) . '&z' . urlencode($point_z), 301);
		die();		
	}

	// step 3, give yourself a map
	$minecraft_function .= "give @s minecraft:map\n";
}
?><!DOCTYPE html>
<html>
<head>
	<title>logoworld logo -> function</title>
	<script>
function cta(element, value)
{
	document.getElementById(element).value = value;
	checkGrid();
}
function checkGrid()
{
	var o = document.getElementById('gridinfo');
	o.innerHTML="";

	var x = parseInt(document.getElementById('x').value);
	var z = parseInt(document.getElementById('z').value);

	// check X to see if it is within bounds
	var x_bounds = (x - 64) / 128;
	if (x_bounds != Math.floor(x_bounds))
	{
		var low_x = x;
		var high_x = x;

		do {
			low_x = low_x - 1;
			low_x_bounds = (low_x - 64) / 128;
		} while (low_x_bounds != Math.floor(low_x_bounds));
		do {
			high_x = high_x + 1;
			high_x_bounds = (high_x - 64) / 128;
		} while (high_x_bounds != Math.floor(high_x_bounds));
		o.innerHTML = o.innerHTML + "X is not within bounds (low:<a href='#' onclick='cta(\"x\", " + low_x + ");'>" + low_x + "</a>, high:<a href='#' onclick='cta(\"x\", " + high_x + ");'>" + high_x + "</a>)<br />";
	}

	var z_bounds = (z - 64) / 128;
	if (z_bounds != Math.floor(z_bounds))
	{
		var low_z = z;
		var high_z = z;

		do {
			low_z = low_z - 1;
			low_z_bounds = (low_z - 64) / 128;
		} while (low_z_bounds != Math.floor(low_z_bounds));
		do {
			high_z = high_z + 1;
			high_z_bounds = (high_z - 64) / 128;
		} while (high_z_bounds != Math.floor(high_z_bounds));
		o.innerHTML = o.innerHTML + "Z is not within bounds (low:<a href='#' onclick='cta(\"z\", " + low_z + ");'>" + low_z + "</a>, high:<a href='#' onclick='cta(\"z\", " + high_z + ");'>" + high_z + "</a>)<br />";
	}
}
	</script>
</head>
<body>
	<form action="index.php" method="post" enctype="multipart/form-data">
		<table style="border: 1px solid">
			<tr>
				<td>Top-Left pixel</td>
				<td>
					X:<input style="width: 50px;" type="text" placeholder="1088" id='x' name="x" value="<?php if (!is_null($point_x)) { echo $point_x; } else { echo "1088"; } ?>" onblur="checkGrid();" />,
					Z:<input style="width: 50px;" type="text" placeholder="1088" id='z' name="z" value="<?php if (!is_null($point_z)) { echo $point_z; } else { echo "1088"; } ?>" onblur="checkGrid();" />
				</td>
			</tr>
			<tr>
				<td colspan="2"><div id="gridinfo"></div></td>
			</tr>
			<tr>
				<td>Image:</td>
				<td><input name="logo" type="file" /></td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input type="submit" />
				</td>
			</tr>
		</table>
	</form>
<?php
if (array_key_exists('e', $_GET))
{
	$error = urldecode($_GET['e']);
	echo "<div>Error: $error</div>";
}
?><?php
if (!is_null($minecraft_function))
{
?>
	<textarea style="width:800px; height:500px;"><?php echo $minecraft_function; ?></textarea>
<?php
}
?>
</body>
</html>
