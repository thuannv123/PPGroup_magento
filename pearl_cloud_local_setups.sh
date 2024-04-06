#!/bin/bash

#usage ./pearl_cloud_local_setups.sh

modules=(
    "CategoryPage"
    "CustomHeader"
    "FrontendOptions"
    "ProductPage"
    "CustomFooter"
);

## Define here all themes and locales that use Pearl
PearlThemes=(
    "Pearl/weltpixel_custom/th_TH"
    "Pearl/weltpixel_custom/en_US"
);

for i in "${PearlThemes[@]}"
do
    THEMEPATH=$i

    mkdir -p pub/media/css_weltpixel/static/$THEMEPATH/css
    touch pub/media/css_weltpixel/static/$THEMEPATH/css/.gitkeep

    for index in "${modules[@]}"
    do
        MODULENAME=$index
        mkdir -p pub/media/css_weltpixel/static/$THEMEPATH/$MODULENAME/css
        touch pub/media/css_weltpixel/static/$THEMEPATH/$MODULENAME/css/.gitkeep
    done
done

echo "Patch was successfully applied.";
