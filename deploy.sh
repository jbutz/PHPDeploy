#!/bin/bash

#rsync -aC --exclude ".git" --exclude ".git/" WOTRK/ WOTRK2/
TMPFOLDER=/tmp/DEPLOY$RANDOM
#git clone http://dev.webapps.brake.local/git/WOTRK.git $TMPFOLDER

#########################################
usage()
{
	cat << EOF
usage: $0 [-o GIT] [-d OUTPUT_DIR] -i INPUT

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

EOF
}
###
#	Parse Arguments
###
ARGC=$#
OUTPUT='.'
INPUT=""
OPERATION="GIT"
DELETE=0
PROMPT=1
QUIET=0
VERBOSE=0

while getopts “o:i:d:eyqvh” OPTION
do
	case $OPTION in
		h)
			usage
			exit 1
			;;
		o)
			OPERATION=$OPTARG
			;;
		e)
			DELETE=1
			;;
		y)
			PROMPT=0
			;;
		q)
			QUIET=1
			;;
		v)
			VERBOSE=1
			;;
		i)
			INPUT=$OPTARG
			;;
		d)
			OUTPUT=$OPTARG
			;;
		?)
			usage
			exit 1
			;;
	esac
done

# Build the arguments for the operation.
COMMAND=""
DISP=""
case $OPERATION in
	GIT)
			i=0
			OPS=""
			DISP="\n\nUsing git the repo at \"$INPUT\" will be cloned to $TMPFOLDER.\n"
			DISP="${DISP}The files will then be moved to $OUTPUT.\n"
			if [ "$QUIET" = "1" ]; then
				OPS="$OPS -q"
				DISP="${DISP}This will be done quietly.\n"
			fi

			if [ "$VERBOSE" = "1" ]; then
				DISP="${DISP}This will be done verbosly.\n"
				OPS="$OPS -v"
			fi
			COMMAND[$i]="git clone $INPUT $TMPFOLDER"
			i=$i+1
			if [ "$DELETE" = "1" ]; then
				COMMAND[$i]="$COMMAND;rm -rf $OUTPUT"
				i=$i+1
				DISP="${DISP}**DANGER**\n$OUTPUT will be emptied.\n"
			fi
			COMMAND[$i]="$COMMAND && rsync -aC --exclude \".git\" --exclude \".git/\" $TMPFOLDER/ $OUTPUT"
			i=$i+1
			COMMAND[$i]="$COMMAND && rm -rf $TMPFOLDER"
			i=$i+1
		;;
	FILE)
		echo "Error: Not created yet"
		exit -1
		;;
	TAR)
		echo "Error: Not created yet"
		exit -1
		;;
esac

if [ "$PROMPT" -eq "1" ]; then
	echo -e $DISP
	echo -e "\nDo you want to do this? [y/N]"
	VALUE=""
	read VALUE

	if [ "$VALUE" != "y" -a "$VALUE" != "Y" ]; then
		exit
	fi
fi

echo -e "\nCommand:\n$COMMAND"
COMMAND=("${COMMAND[@]}")
for x in ${!COMMAND[*]}; do
	${COMMAND[$x]}
done
