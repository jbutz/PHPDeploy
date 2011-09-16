#!/usr/bin/php
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
$display = array();
$commands = array();
// Validate operation and do stuff
switch($operation)
{
	case 'GIT':
		git($quiet,$verbose,$erase,$output,$input,$display,$commands);
		break;
	case 'FILE':
		fileop($quiet,$verbose,$erase,$output,$input,$display,$commands);
		break;
	case 'TAR':
		die("Error: Not created yet.\n\n");
		break;
	default:
		echo "Error: Not a valid operation.\n\n";
		usage();
		return -1;
		break;
}

prompt($display);
run($commands);
return;
function git($quiet,$verbose,$erase,$dir,$input,&$display,&$commands)
{
	$commands = array();
	$display = array();
	$outdir = "/tmp/phpdeploy".(time() + rand());
	$display[] = "A git clone will be performed on $input.";
	$display[] = "The output will be placed into $dir.";
	$ops = "";
	if($quiet)
	{
		$ops .= " -q";
		$display[] = "This will be done quietly.";
	}
	if($verbose)
	{
		$ops .= " -v";
		$display[] = "This will be done verbosely.";
	}
	$ops = trim($ops);
	$commands[] = "git clone $ops $input $outdir";
	if($erase)
	{
		$display[] = "\033[0;31m*** DANGER ***";
		$display[] = "The contents of $dir will be erased.\033[0m";
		$commands[] = "rm -rf $dir";
	}
	$ops = "";
	if($quiet)
		$ops .= "q";
	if($verbose)
		$ops .= "v";
	$commands[] = "rsync -aC$ops --exclude \".git\" --exclude \".git/\" $outdir/ $dir";
	$commands[] = "rm -rf $outdir";
}

function fileop($quiet,$verbose,$erase,$dir,$input,&$display,&$commands)
{
	$commands = array();
	$display = array();
	$display[] = "A rsync will be performed from $input to $dir.";
	$ops = "";
	if($quiet)
	{
		$ops .= "q";
		$display[] = "This will be done quietly.";
	}
	if($verbose)
	{
		$ops .= "v";
		$display[] = "This will be done verbosely.";
	}
	if($erase)
	{
		$display[] = "\033[0;31m*** DANGER ***";
		$display[] = "The contents of $dir will be erased.\033[0m";
		$commands[] = "rm -rf $dir";
	}
	$commands[] = "rsync -aC$ops --exclude \".git\" --exclude \".git/\" $input $dir";
}

function run($commands)
{
	global $verbose;
	$junk = array();
	$val = 0;
	foreach($commands as $command)
	{
		if($verbose)
			echo $command."\n";
		passthru($command,$val);
		if($val != 0)
		{
			echo "\n\n\033[0;31mAn error occured while running the following command:\n";
			echo "\t$command\033[0m\n\n";
			return -1;
		}
	}
	echo "\033[0;32mOperations Successful\033[0m\n";
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
