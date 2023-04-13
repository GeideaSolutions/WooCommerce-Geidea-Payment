<?php
// Register uninstall hook
register_uninstall_hook(__FILE__, 'geidea_plugin_uninstall');

// Uninstall function
function geidea_plugin_uninstall()
{
    // Get plugin directory path
    $plugin_path = plugin_dir_path(__FILE__);

    // Delete plugin files
    if (file_exists($plugin_path . 'wc-geidea.php')) {
        unlink($plugin_path . 'wc-geidea.php');
    }
    if (file_exists($plugin_path . 'woocommerce-geidea')) {
        delete_dir($plugin_path . 'woocommerce-geidea');
    }
}

// Function to recursively delete a directory and its contents 
function delete_dir($dir_path)
{
    if (is_dir($dir_path)) {
        $dir_handle = opendir($dir_path);
        if (!$dir_handle) {
            return false;
        }
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dir_path . "/" . $file)) {
                    unlink($dir_path . "/" . $file);
                } else {
                    delete_dir($dir_path . '/' . $file);
                }
            }
        }
        closedir($dir_handle);
        rmdir($dir_path);
        return true;
    }
    return false;
}
