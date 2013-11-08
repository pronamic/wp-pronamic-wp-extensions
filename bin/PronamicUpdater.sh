#!/bin/bash
# Pronamic Updater
# chmod +x ./PronamicUpdater.sh

if [ $# -lt 3 ]; then
	echo 'usage: ./PronamicUpdater.sh https://bitbucket.org/Pronamic/wp-pronamic-updater-test remcotolsma pronamic-updater-test 1.0.0'
	exit
fi

repoUrl=$1
username=$2
slug=$3
version=$4

# @see http://www.tldp.org/LDP/Bash-Beginners-Guide/html/Bash-Beginners-Guide.html
# @see http://stackoverflow.com/questions/4301786/unzip-zip-file-and-extract-unknown-folder-names-content
# @see http://www.cyberciti.biz/tips/linux-unix-pause-command.html
# @see http://stackoverflow.com/questions/4632028/how-to-create-a-temporary-directory
# @see http://stackoverflow.com/questions/10982911/creating-temporary-files-in-bash

# Temp ZIP file
tempZipFile=$(mktemp -t $slug)

echo "Temp ZIP file: $tempZipFile"

read -p "Press [Enter] key to continue..."

# Temp dir
tempDir=$(mktemp -d -t $slug)

echo "Temp dir: $tempDir"

read -p "Press [Enter] key to continue..."

# Download URL
downloadUrl="$repoUrl/get/$version.zip"

echo "Download URL $downloadUrl"

read -p "Press [Enter] key to continue..."

# CURL commando
curlCmd="curl --digest --user $username $downloadUrl -o $tempZipFile"

echo "CURL Command: $curlCmd"

read -p "Press [Enter] key to continue..."

# Download file
$($curlCmd)

# Unzip
unzip $tempZipFile -d $tempDir

# Remove temp ZIP file
rm $tempZipFile

# Enter temp DIR
cd $tempDir

# Find dir
unknownDir=*

echo "Unknown Dir: $unknownDir"

read -p "Press [Enter] key to continue..."

# New dir
newDir=$slug

echo "New Dir: $newDir"

read -p "Press [Enter] key to continue..."

# New ZIP
newZip="$slug.$version.zip"

echo "New ZIP: $newZip"

read -p "Press [Enter] key to continue..."

# Rename dir
mv $unknownDir $newDir

# Zip
zip -r $newZip $newDir

# Upload
curl -v -T $newZip ftp://localhost/public_html/plugins/$slug/$slug.$version.zip

# Remove temp dir
rm -r $tempDir



# Download file
# // @see https://bitbucket.org/site/master/issue/7899/allow-use-of-token-for-http-authentication
# curl --digest https://bitbucket.org/Pronamic/gravityforms/get/1.7.8.zip -o $zipFile
# curl https://github.com/pronamic/wp-pronamic-ideal/archive/2.0.6.zip -o $zipFile
# see https://help.github.com/articles/downloading-files-from-the-command-line
# curl -H "Authorization: token 1234" -L -o foo.tar.gz \
#	https://github.com/pronamic/wp-pronamic-ideal/archive/2.0.6.zip
# zipUrl="$repoUrl/archive"
# curl -L -o download.zip https://github.com/pronamic/wp-pronamic-ideal/archive/2.0.6.zip