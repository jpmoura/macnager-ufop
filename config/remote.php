<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Remote Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default connection that will be used for SSH
    | operations. This name should correspond to a connection name below
    | in the server list. Each connection will be manually accessible.
    |
    */

    'default' => 'pfsenseLAN',

    /*
    |--------------------------------------------------------------------------
    | Remote Server Connections
    |--------------------------------------------------------------------------
    |
    | These are the servers that will be accessible via the SSH task runner
    | facilities of Laravel. This feature radically simplifies executing
    | tasks on your servers, such as deploying out these applications.
    |
    */

    'connections' => [
        'firewall' => [
            'host'      => env('FIREWALL_HOST'),
            'username'  => env('FIREWALL_USER'),
            'password'  => env('FIREWALL_PASS'),
            'key'       => env('FIREWALL_PATH', ''),
            'keyphrase' => '',
        ],
        'dhcp' => [
            'host'      => env('DHCP_HOST'),
            'username'  => env('DHCP_USER'),
            'password'  => env('DHCP_PASS'),
            'key'       => env('DHCP_PATH', ''),
            'keyphrase' => '',
        ],
        'pfsenseLAN' => [
            'host'      => env('PFSENSE_LAN_HOST'),
            'username'  => env('PFSENSE_LAN_USER'),
            'password'  => env('PFSENSE_LAN_PASS'),
            'key'       => env('PFSENSE_LAN_PATH', ''),
            'keyphrase' => '',
        ],
        'pfsenseNAT' => [
            'host'      => env('PFSENSE_NAT_HOST'),
            'username'  => env('PFSENSE_NAT_USER'),
            'password'  => env('PFSENSE_NAT_PASS'),
            'key'       => env('PFSENSE_NAT_PATH', ''),
            'keyphrase' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Remote Server Groups
    |--------------------------------------------------------------------------
    |
    | Here you may list connections under a single group name, which allows
    | you to easily access all of the servers at once using a short name
    | that is extremely easy to remember, such as "web" or "database".
    |
    */

    'groups' => [
        'web' => ['production'],
    ],

];
