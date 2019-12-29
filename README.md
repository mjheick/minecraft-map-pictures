# minecraft-map-pictures

Creating near-photo-realistic images in minecraft by using maps.

For use with Minecraft 1.14.\* and later

# How to use

1. You need minecraft. If you don't have the lastest craze, then l2p
2. Get yourself a truecolor image, modify and reduce it to 128x128px
3. Upload it to the index.php and get a textbox full of data
4. Put that data in a .mcfunction in a datapack of your world (huh?)

## World notes for above script

This function puts blocks as y=4. Because of this, you'll need a superflat world for this to actually work. Alternatively, you can just replace all the "4"'s with a y-coordinate of your choice, or modify the script to delegate where you actually want it to summon in the blocks.

## datapack 101

Quick rundown of a datapack. Anything provided in quotes should not use the quotes when you use the information.

1. Open up your worlds data folder. This is the folder with the level.dat as well as many, many folders
2. Enter your datapacks folder
3. Create a folder, preferrably "lw". Enter that folder
4. Create a "pack.mcmeta" file in this. Make the contents be the following

    {
    	"pack": {
    		"pack_format": 4,
    		"description": "Stuff"
	    }
    }

5. Create a "data" folder in the "lw" folder. Enter that folder
6. Create a "lw" folder in the "data" folder. Enter that folder
7. Create a "functions" folder in the "lw" folder. Enter that folder (repetitive, sure)
8. Create your "derp.mcfunction" file. Put the contents of the text box in the above in this file. Save it
9. Launch Minecraft. Enter Your world. If you are already in Minecraft enter the "/reload" command
10. Enter "/function lw:derp" and the following should happen: You'll be teleported and stuff should show up. If stuff didn't show up, re-enter the "/function lw:derp" since the chunk hasn't loaded.

# Files

index.php - an uploader that gives enough output to be placed in an .mcfunction file

block2rgb.yml - the cross-reference between "minecraft tile" and RGB that would show up on a map
