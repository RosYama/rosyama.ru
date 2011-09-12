<?php

$dir = $argv[1];
if(!$dir)
{
	die("No dirname\n");
}
$wdir = opendir($dir);
while($yama = readdir($wdir))
{
	if($yama != '.' && $yama != '..' && $yama != 'dummy')
	{
		$odir = opendir($dir.''.$yama);
		while($photo = readdir($odir))
		{
			if($photo != '.' && $photo != '..')
			{
				$_image_info = getimagesize($dir.''.$yama.'/'.$photo);
				$image = imagecreatefromjpeg($dir.''.$yama.'/'.$photo);
				$aspect = max($_image_info[0] / 1024, $_image_info[1] / 1024);
				if($aspect > 1)
				{
					$new_x    = floor($_image_info[0] / $aspect);
					$new_y    = floor($_image_info[1] / $aspect);
					$newimage = imagecreatetruecolor($new_x, $new_y);
					imagecopyresampled($newimage, $image, 0, 0, 0, 0, $new_x, $new_y, $_image_info[0], $_image_info[1]);
					imagejpeg($newimage, $dir.''.$yama.'/'.$photo);
				}
				echo $dir.''.$yama.'/'.$photo."\n";
			}
		}
		closedir($odir);
	}
}
closedir($wdir);

?>