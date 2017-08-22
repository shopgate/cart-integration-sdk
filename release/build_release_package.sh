#!/bin/sh

mkdir release/shopgate-cart-integration-sdk
rm-rf vendor
composer install -vvv --no-dev
rsync -av --exclude-from './release/exclude-filelist.txt' ./ release/shopgate-cart-integration-sdk
cd release
zip -r ../shopgate-cart-integration-sdk.zip shopgate-cart-integration-sdk
