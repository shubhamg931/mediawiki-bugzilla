<?php
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this
# file, You can obtain one at http://mozilla.org/MPL/2.0/.

$dir = dirname(__FILE__);
require_once ($dir . '/BugzillaOutput.class.php');
require_once ($dir . '/cache/BugzillaCacheI.class.php');
require_once ($dir . '/cache/BugzillaCacheDummy.class.php');
require_once ($dir . '/cache/BugzillaCacheApc.class.php');
require_once ($dir . '/cache/BugzillaCacheMemcache.class.php');
require_once ($dir . '/cache/BugzillaCacheSql.class.php');

// Factory
class Bugzilla {

    public static function create($config=array(), $opts=array(), $title='') {
        // Default configuration
        // FIXME: This should be in the main configuration
        $theconfig = array(
            'type'    => 'bug',
            'display' => 'table',
            'stats'   => 'show',
        );

        // Overlay user's desired configuration
        foreach( $config as $key => $value ) {
            $theconfig[$key] = $value;
        }

        $classes = [
            'list'   => 'List',
            'number' => 'Number',
            'bar'    => 'BarGraph',
            'vbar'   => 'VerticalBarGraph',
            'pie'    => 'PieGraph',
            'inline' => 'Inline',
            'table'  => 'Table',
        ];
        if (!array_key_exists($theconfig['display'], $classes)) {
            $theconfig['display'] = 'table';
        }

        $class = 'Bugzilla'.$classes[$theconfig['display']];

        return new $class($theconfig, $opts, $title);
    }

    /**
     * Return the BugzillaCacheI extended class in charge
     * for the cache backend in use.
     *
     * @param string $type
     *
     * @return string
    */
    public static function getCacheClass( $type ) {

        $suffix = 'dummy';

        if ( in_array( $type, array( 'mysql', 'postgresql', 'sqlite' ) ) ) {
            $suffix = 'sql';
        } elseif ( in_array( $type, array( 'apc', 'memcache' ) ) ) {
            $suffix = $type;
        }

        return 'BugzillaCache' . ucwords( $suffix );
    }

    /**
     * Build and return a working cache, depending on config.
     *
     * @return BugzillaCacheI object
    */
    public static function getCache() {
        global $wgBugzillaCacheType;

        $object = self::getCacheClass( $wgBugzillaCacheType );

        return new $object();
    }
}
