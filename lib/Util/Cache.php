<?php

class Maestrano_Util_Cache extends \Gilbitron\Util\SimpleCache
{
    /**
     * Non-existing method in SimpleCache
     * Fetch the cache without regarding its expiry
     * @param $label
     * @return bool|string
     */
    public function get_cached_file($label)
    {
        $filename = $this->cache_path . $this->safe_filename($label) . $this->cache_extension;

        if(file_exists($filename))
            return file_get_contents($filename);

        return false;
    }

    //Helper function to validate filenames
    protected function safe_filename($filename)
    {
        return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
    }
}