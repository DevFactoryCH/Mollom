#!/bin/bash
set -e

# WORKING DIRECTORIES
os=$(uname -a | awk '{print $1}')
if [ $os == "Darwin" ] || [ $os == "MINGW32_NT-6.2" ];
then
    currpwd=$(pwd);
    cwd=$(cd "$(dirname "$0")"; pwd);
    cd $currpwd;
else
    cwd=$(dirname $(readlink -e $0));
fi

# Go to the main directory
cd $cwd/..

# Run the composer
php composer.phar update
