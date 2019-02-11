<?php

define('DOCROOT', rtrim(realpath(__DIR__ . '/../../../'), '/'));

require_once DOCROOT . '/vendor/autoload.php';
require_once DOCROOT . '/symphony/lib/boot/bundle.php';

$imageFolder = Symphony::Configuration()->get('imagepath', 'tinymce');
General::realiseDirectory($imageFolder);

reset($_FILES);
$temp = current($_FILES);
if (is_uploaded_file($temp['tmp_name'])) {

	if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
		header("HTTP/1.1 400 Invalid file name.");
		return;
	}

	// Verify extension
	if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))) {
		header("HTTP/1.1 400 Invalid extension.");
		return;
	}

	$file_name_exploaded = explode(".", $temp['name']);
	$file_name = array_shift($file_name_exploaded);
	$file_extension = implode(".", $file_name_exploaded);
	$file_name_pattern = "$file_name*";

	$files = glob($imageFolder . "/" . $file_name_pattern);
	if (count($files) > 0) {
		$file_name .= "(" . count($files) . ")";
	}

	$file_name .= "." . $file_extension;

	// Accept upload if there was no origin, or if it is an accepted origin
	$filetowrite = $imageFolder . $file_name;
	move_uploaded_file($temp['tmp_name'], $filetowrite);

	$url = str_replace(DOCROOT, '', $imageFolder) . $file_name;
	// Respond to the successful upload with JSON.
	// Use a location key to specify the path to the saved image resource.
	// { location : '/your/uploaded/image/file'}
	echo json_encode(array('location' => $url));
} else {
	// Notify editor that the upload failed
	header("HTTP/1.1 500 Server Error");
}
?>
