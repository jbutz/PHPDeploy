<?php
$ops = getopt("d:i:o:eyqvh");

$quiet = false;
$verbose = false;
$prompt = true;
$erase = false;
$output = ".";
$input = "";
$operation = "GIT";
$commands = array();
$display = array();

if(array_key_exists('h',$ops))
{
	usage();
	return 0;
}

if(array_key_exists('q',$ops))
	$quiet = true;

if(array_key_exists('v',$ops))
	$verbose = true;

if(array_key_exists('y',$ops))
	$prompt = true;

if(array_key_exists('e',$ops))
	$erase = true;

if(array_key_exists('d',$ops))
	$output = $ops['d'];

if(array_key_exists('o',$ops))
	$operation = $ops['o'];

if(array_key_exists('i',$ops))
	$input = $ops['i'];
else
{
	echo "Error: No input specified\n\n";
	usage();
	return -1;
}
prompt(array());
return;
// Validate operation and do stuff
switch($operation)
{
	case 'GIT':
		git();
		break;
	case 'FILE':
	case 'TAR':
		die("Error: Not created yet.");
		break;
	default:
		echo "Error: Not a valid operation.\n\n";
		usage();
		return -1;
		break;
}


function prompt($array)
{
	$txt = implode("\n",$array);
	$val = readline($txt."\n\nDo you want to continue? [yN]: ");
	if(trim(strtolower($val)) != "y")
	{
		exit(0);
	}
	else
	{
		return true;
	}
}

function usage()
{
	global $argv, $argc;
echo "
usage: ${argv[0]}  [-o GIT] [-d OUTPUT_DIR] -i INPUT

This script can be used to deploy code from a directory, tar file, or
git repository into a given directory.

OPTIONS:
-d      Output directory. Defaults to .
-i      Input for the givem operation
         Git URL, File Path, Tar location, etc.
-o      Defines operation used to get files.
         Valid options are GIT, FILE, and TAR
         GIT is the default
-e      Empty output directory before deploying *DANGEROUS*
-y      Bypass prompt to confirm operation
-q      Quiet
-v      Verbose
-h      Display this message
";
}
?>
