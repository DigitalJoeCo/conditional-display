<?php

// $vendorDir = $GLOBALS['composer']->getConfig()->get('vendor-dir');
// $pluginPath = dirname($vendorDir);
$pluginPath = __DIR__;
putenv("CONDITIONAL_DISPLAY_INSTALL_PATH=$pluginPath");
echo "CONDITIONAL_DISPLAY_INSTALL_PATH set to: $pluginPath\n";
