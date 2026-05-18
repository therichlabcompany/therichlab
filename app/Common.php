<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

use Config\Database;


if (! function_exists('db_conn')) {
    /**
     * 공용 데이터베이스 연결 함수
     *
     * 사용:
     *   $db = db_conn();
     *   $db2 = db_conn('reporting');
     *
     * @param string $group 데이터베이스 그룹명 (기본값: default)
     * @return \CodeIgniter\Database\BaseConnection
     */
    function db_conn(string $group = 'default')
    {
        static $connections = [];

        // 같은 그룹은 한 번만 연결하고 재사용
        if (! isset($connections[$group])) {
            $connections[$group] = Database::connect($group);
        }

        return $connections[$group];
    }
}