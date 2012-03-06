<?php
/*
 * PHP QR Code encoder
 *
 * Config file, feel free to modify
 * 
 * Configuration is made in framework.php
 */
    if(!defined("QR_CACHEABLE")) define('QR_CACHEABLE', true);                                                               // use cache - more disk reads but less CPU power, masks and format templates are stored there
    if(!defined("QR_CACHE_DIR")) define('QR_CACHE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR);  // used when QR_CACHEABLE === true
    if(!defined("QR_LOG_DIR")) define('QR_LOG_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);                                // default error logs dir   
    
    if(!defined("QR_FIND_BEST_MASK")) define('QR_FIND_BEST_MASK', true);                                                          // if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code
    if(!defined("QR_FIND_FROM_RANDOM")) define('QR_FIND_FROM_RANDOM', false);                                                       // if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
    if(!defined("QR_DEFAULT_MASK")) define('QR_DEFAULT_MASK', 2);                                                               // when QR_FIND_BEST_MASK === false
                                                  
    if(!defined("QR_PNG_MAXIMUM_SIZE")) define('QR_PNG_MAXIMUM_SIZE',  1024);                                                       // maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images
                                                  