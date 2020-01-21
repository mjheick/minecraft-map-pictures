#!/usr/bin/perl
use strict;
use warnings;
use Getopt::Long; # cause we like standard command line arguments
use Image::Magick; # to read pixels in an image, https://imagemagick.org/script/perl-magick.php
use YAML::XS qw(LoadFile); # for loading mapping yaml
use Data::Dumper; # debugging cause my perl is shit

my ($image_filename, $startxz);
GetOptions(
	"image=s" => \$image_filename,
	"xz=s" => \$startxz,
) or die("parameters not right"); # https://perldoc.perl.org/Getopt/Long.html
if ( ! -e $image_filename ) { die ('image ' . $image_filename . ' not found'); }

# Get starting coordinates squared away. They are in x,z format
my ($startx, $startz);
$startx = 0;
$startz = 0;

# Load up our block => rgb reference tables
my ($block2rgb, $blockname, $blockrgb, $br, $bg, $bb, $bdistance);
my ($tblockname, $tdistance, @tmprgb);
$block2rgb = YAML::XS::LoadFile('./block2rgb.yml');

# Load up the entire image into an array
my ($image, $width, $height, $depth, @pixels);
$image = Image::Magick->new;
$image->Read($image_filename);
$width = $image->Get('width');
$height = $image->Get('height');
$depth = $image->Get('depth');
@pixels = $image->GetPixels(map => "RGB", x => 0, y => 0, width => $width, height => $height);
print "Bit Depth: " . $depth . "\n";
print "# .mcfunction maker\n";
print "# Source Image Dimensions: ${width}x${height}\n";
# Pre-requisite stuff for this to execute
print "tp " . ($startx + 64) . " 32 " . ($startz + 64) . "\n";

my ($x, $z); # coordinates that we add to $startx and $startz
my ($r, $g, $b); # colors that we're popping
for ($z = 0; $z < $height; $z++)
{
	for ($x = 0; $x < $height; $x++)
	{
		$r = shift(@pixels);
		$g = shift(@pixels);
		$b = shift(@pixels);
		# clean up rgb
		$r &= 0xff;
		$g &= 0xff;
		$b &= 0xff;
		# find nearest matching block
		$blockname = "minecraft:air";
		$bdistance = 1000; # initialize to the highest
		foreach (keys %{$block2rgb})
		{
			$tblockname = $_;
			$blockrgb = $block2rgb->{$tblockname}; # This is in r,g,b, break apart for maths
			@tmprgb = split(/,/, $blockrgb);
			$br = $tmprgb[0];
			$bg = $tmprgb[1];
			$bb = $tmprgb[2];
			$tdistance = sqrt( ($br - $r)**2 + ($bg - $g)**2 + ($bb - $b)**2 );
			if ($tdistance < $bdistance)
			{
				$blockname = $tblockname;
				$bdistance = $tdistance;
			}
		}
		print "setblock " . ($startx + $x) . " 4 " . ($startz + $z) . " " . $blockname . "\n";
	}
}

undef $image; #unkaboom properly per documentation