<?php


namespace OpenEMR\Cqm;


use FontLib\Table\DirectoryEntry;
use OpenEMR\Common\System\System;

class CqmServiceManager
{
    public static function makeCqmClient()
    {
        $servicePath = $GLOBALS['fileroot'] . DIRECTORY_SEPARATOR .
            'node_modules' . DIRECTORY_SEPARATOR .
            'cqm-service' . DIRECTORY_SEPARATOR .
            'server.js';
        $client = new CqmClient(
            new System(),
            '/Users/kchapple/Dev/www/openemr/master/node_modules/cqm-service/server.js',
            'http://localhost',
            '8089'
        );

        return $client;
    }
}
