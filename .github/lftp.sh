#!/bin/sh

echo " --delete --ignore-time -X .git*/ -X .git* --skip-noaccess --use-cache -v
 -X wp-admin/ 
 -X wp-content/cache 
 -X wp-content/languages 
 -X wp-content/mu-plugins 
 -X wp-content/plugins 
 -X wp-content/themes/storefront 
 -X wp-content/themes/twentynineteen 
 -X wp-content/themes/twentytwenty 
 -X wp-content/upgrade 
 -X wp-content/uploads 
 -X wp-content/advanced-cache.php 
 -X wp-content/index.php 
 -X wp-includes/ 
 -X error_log 
 -X index.php 
 -X wp-* 
 -X *.ini 
 -X *.yaml 
 -X xmlrpc.php 
" |  tr '\n' ' '