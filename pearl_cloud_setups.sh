#!/bin/bash

#usage ./pearl_cloud_setups.sh

modules=(
    "CategoryPage"
    "CustomHeader"
    "FrontendOptions"
    "ProductPage"
    "CustomFooter"
);


for index in "${modules[@]}"
do
    MODULENAME=$index;
    cd app/code/WeltPixel/$MODULENAME/view/frontend/web/;
    if [ -L css ]
    then
        unlink css;
    fi
    if [ -d css ]
    then
        rm -rf  css;
    fi
    ln -s ../../../../../../../pub/media/css_weltpixel/$MODULENAME/css/ css;
    cd ../../../../../../../;
    echo "Symlink was successfully applied for $MODULENAME module.";
done


## Define here all themes and locales that use Pearl
PearlThemes=(
    "Pearl/weltpixel_custom/th_TH"
    "Pearl/weltpixel_custom/en_US"
);

for i in "${PearlThemes[@]}"
do
    THEMEPATH=$i

    if [ -d ~/pub/media/css_weltpixel/static/$THEMEPATH ]
    then
        rm -rf  ~/pub/media/css_weltpixel/static/$THEMEPATH;
    fi

    mkdir -p ~/pub/media/css_weltpixel/static/$THEMEPATH/css
    mv ~/init/pub/static/frontend/$THEMEPATH/css ~/pub/media/css_weltpixel/static/$THEMEPATH

    ln -s ~/pub/media/css_weltpixel/static/$THEMEPATH/css ~/init/pub/static/frontend/$THEMEPATH/css

    for index in "${modules[@]}"
    do
        MODULENAME=$index
        mkdir -p ~/pub/media/css_weltpixel/static/$THEMEPATH/$MODULENAME/css
        mkdir -p ~/init/pub/static/frontend/$THEMEPATH/WeltPixel_$MODULENAME/css
        mv ~/init/pub/static/frontend/$THEMEPATH/WeltPixel_$MODULENAME/css ~/pub/media/css_weltpixel/static/$THEMEPATH/$MODULENAME
        ln -s ~/pub/media/css_weltpixel/static/$THEMEPATH/$MODULENAME/css ~/init/pub/static/frontend/$THEMEPATH/WeltPixel_$MODULENAME/css
    done
done

echo "Patch was successfully applied.";
