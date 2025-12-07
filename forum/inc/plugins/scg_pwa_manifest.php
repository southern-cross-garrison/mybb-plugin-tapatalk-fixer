<?php
/**
 * SCG Custom PWA Manifest Plugin
 * 
 * Overrides Tapatalk's manifest with SCG branding for PWA installations.
 * This ensures users who install the forum as a Progressive Web App
 * see the SCG logo instead of Tapatalk's default icons.
 *
 * @author Kevin Brown (TK-33151)
 * @version 1.0
 */

// Disallow direct access to this file for security reasons
if(!defined('IN_MYBB'))
{
    die('This file cannot be accessed directly.');
}

// Hook into global_end to modify output after Tapatalk has added its manifest
$plugins->add_hook('global_end', 'scg_manifest_fix');

/**
 * Plugin information for the MyBB plugin manager
 */
function scg_manifest_info()
{
    return array(
        'name'          => 'SCG PWA Manifest',
        'description'   => 'Provides custom PWA manifest with SCG branding instead of Tapatalk icons. This ensures users who install the forum as a Progressive Web App see the correct SCG logo.',
        'website'       => 'https://501scg.org/',
        'author'        => 'SCG Web Team - Kevin Brown (TK-33151)',
        'authorsite'    => 'https://501scg.org/',
        'version'       => '1.0',
        'compatibility' => '18*',
        'codename'      => 'scg_manifest'
    );
}

/**
 * Called when the plugin is activated
 */
function scg_manifest_activate()
{
    // Nothing special needed on activation
}

/**
 * Called when the plugin is deactivated
 */
function scg_manifest_deactivate()
{
    // Nothing special needed on deactivation
}

/**
 * Main function that removes Tapatalk's manifest and adds our own
 * Runs at the global_end hook, after Tapatalk has injected its manifest
 */
function scg_manifest_fix()
{
    global $headerinclude;
    
    // Remove Tapatalk's manifest link (handles both attribute orders)
    // Pattern 1: href before rel
    $headerinclude = preg_replace(
        '/<link[^>]*href=["\'][^"\']*tapatalk-cdn\.com[^"\']*["\'][^>]*rel=["\']manifest["\'][^>]*>/i',
        '',
        $headerinclude
    );
    
    // Pattern 2: rel before href
    $headerinclude = preg_replace(
        '/<link[^>]*rel=["\']manifest["\'][^>]*href=["\'][^"\']*tapatalk-cdn\.com[^"\']*["\'][^>]*>/i',
        '',
        $headerinclude
    );
    
    // Also catch any manifest link pointing to groups.tapatalk-cdn.com specifically
    $headerinclude = preg_replace(
        '/<link[^>]*href=["\']https:\/\/groups\.tapatalk-cdn\.com\/static\/manifest\/[^"\']*["\'][^>]*>/i',
        '',
        $headerinclude
    );
    
    // Add our custom manifest link
    // The manifest.json file should be placed in the forum root directory
    $headerinclude .= "\n" . '<link rel="manifest" href="/forum/manifest.json">' . "\n";
}


