<?php
namespace PPGroup\Catalog\Plugin;

class Currency
{
    public function afterGetOutputFormat($subject, $result) 
    {
        $result = str_replace('%s', '%s ', $result);
        return $result;
    }
}
