<?php

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\ScriptEvents;
use Composer\Installer\PackageEvent;

class CarbonFieldsInstaller implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->io->write("CarbonFieldsInstaller activated");
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_INSTALL_CMD => 'setInstallPath',
            ScriptEvents::PRE_UPDATE_CMD => 'setInstallPath',
        ];
    }

    public function setInstallPath()
    {
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        $pluginPath = dirname($vendorDir);
        
        // Set the environment variable
        putenv("CONDITIONAL_DISPLAY_INSTALL_PATH=$pluginPath");
        
        $this->io->write("CONDITIONAL_DISPLAY_INSTALL_PATH set to: $pluginPath");
        $this->io->write("Current working directory: " . getcwd());
        $this->io->write("Current DIR: " . __DIR__);
    }

    public function deactivate(Composer $composer, IOInterface $io) {
        $io->write("ConditionalDisplayInstaller deactivated");
    }

    public function uninstall(Composer $composer, IOInterface $io) {
        $io->write("ConditionalDisplayInstaller uninstalled");
    }
}
