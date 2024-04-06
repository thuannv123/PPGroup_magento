<?php

namespace PPGroup\ZipcodeSuggestion\Api;


interface DirectoryInterface
{
    /**
     * Get Data ZipCode.
     *
     * @param string $data
     * @return string
     */
    public function getDataZipCode($data);

    /**
     * Get Data ZipCode.
     *
     * @param string $data
     * @return string
     */
    public function getDataCheck($data);
}
