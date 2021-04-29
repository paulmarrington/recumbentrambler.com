<?php define('CLONER_KEY', 'ad89ac32892aa8840884a9ab72283459'); define('CLONER_STATE', 'https://orion.managewp.com/api/v1/sync-state/605c343e805cf96690643f43/ad89ac32892aa8840884a9ab72283459');




/**
 * Walks through the PHP serialized structure and replaces strings using
 * preg_replace_callback with the provided callback. It returns the new
 * structure with replacements done and optionally sets the count to
 * total number of done replacements. Returns empty string if the provided
 * argument is an empty string or not a string at all.
 *
 * @param string   $search   Regex to search for in strings.
 * @param string   $data     PHP serialized data.
 * @param callable $callback Callback to use in preg_replace_callback call.
 * @param int      $count    Number of replacements done.
 *
 * @return string
 * @throws ClonerSerializedReaderException
 */
function cloner_serialized_replace($search, $data, $callback, &$count = null)
{
    if ($count === null) {
        $count = 0;
    }
    if (!is_string($data) || strlen($data) === 0) {
        return "";
    }
    return cloner_serialized_replace_internal(new ClonerSerializedReader($data), $search, $callback, $count);
}

/**
 * @throws ClonerSerializedReaderException
 */
function cloner_serialized_replace_internal(ClonerSerializedReader $r, $search, $callback, &$count)
{
    $start = $r->cursor;
    $type  = $r->readByte();
    switch ($type) {
        case 'R':
            // R:1;
        case 'r':
            // r:1;
        case 'b':
            // b:0;
            // b:1;
            /** @noinspection PhpMissingBreakStatementInspection */
        case 'i':
            // i:0;
            $r->readExpect(':');
            $r->readInt();
        case 'N':
            // N;
            $r->readExpect(';');
            return substr($r->data, $start, $r->cursor - $start);
        case 'd':
            // d:1;
            // d:0.1;
            // d:9.223372036854776E+19;
            // d:INF;
            // d:-INF;
            // d:NAN;
            $r->readExpect(':');
            $r->readFloat();
            $r->readExpect(';');
            return substr($r->data, $start, $r->cursor - $start);
        case 'C':
            // C:5:"Test2":6:{foobar}
            $r->readExpect(':');
            $classNameLen = $r->readInt();
            $r->readExpect(':"');
            $r->read($classNameLen);
            $r->readExpect('":');
            $len = $r->readInt();
            $r->readExpect(':{');
            $r->read($len);
            $r->readExpect('}');
            return substr($r->data, $start, $r->cursor - $start);
        /** @noinspection PhpMissingBreakStatementInspection */
        case 'O':
            // O:3:"foo":1:{s:4:"test";s:3:"foo";}
            $r->readExpect(':');
            $classNameLen = $r->readInt();
            $r->readExpect(':"');
            $r->read($classNameLen);
            $r->readExpect('"');
        case 'a':
            // a:1:{i:1;s:3:"foo";}
            $r->readExpect(':');
            $fieldsLen = $r->readInt();
            $r->readExpect(':{');
            $serialized = substr($r->data, $start, $r->cursor - $start);
            $oldCount   = $count;
            for ($i = 0; $i < $fieldsLen; $i++) {
                $serialized .= cloner_serialized_replace_internal($r, $search, null, $count);
                $serialized .= cloner_serialized_replace_internal($r, $search, $callback, $count);
            }
            $r->readExpect('}');
            $serialized .= '}';
            if ($oldCount === $count) {
                // No replacements made, return original substring.
                return substr($r->data, $start, $r->cursor - $start);
            }
            return $serialized;
        default:
            throw new ClonerSerializedReaderException($r->cursor, "unexpected token: $type");
        case 's':
            // s:4:"test";
            $r->readExpect(':');
            $len = $r->readInt();
            $r->readExpect(':"');
            $value = $r->read($len);
            break;
        case 'S':
            // S:3:"\61 b";
            $r->readExpect(':');
            $len = $r->readInt();
            $r->readExpect(':"');
            $value = '';
            while (strlen($value) < $len) {
                $byte = $r->readByte();
                if ($byte === '\\') {
                    $value .= chr(intval($r->read(2), 16));
                    continue;
                }
                $value .= $byte;
            }
            break;
    }
    // Fallthrough 's' and 'S' handling.
    $countReplace = 0;
    if ($callback !== null) {
        $value = preg_replace_callback($search, $callback, $value, -1, $countReplace);
        if ($value === null) {
            $err = error_get_last();
            throw new ClonerSerializedReaderException($r->cursor, $err['message']);
        }
        $count += $countReplace;
    }
    $r->readExpect('";');
    if ($countReplace === 0) {
        return substr($r->data, $start, $r->cursor - $start);
    }
    return sprintf('s:%d:"%s";', strlen($value), $value);
}

class ClonerSerializedReader
{
    public $cursor = 0;
    public $data = '';

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function read($len)
    {
        if ($this->cursor + $len > strlen($this->data)) {
            throw new ClonerSerializedReaderException($this->cursor, sprintf('expected to read %d bytes, only %d remain', $len, strlen($this->data) - $this->cursor));
        }
        $value        = substr($this->data, $this->cursor, $len);
        $this->cursor += $len;
        return $value;
    }

    public function readByte()
    {
        if ($this->cursor >= strlen($this->data)) {
            throw new ClonerSerializedReaderException($this->cursor, 'reached end of stream');
        }
        $byte = $this->data[$this->cursor];
        $this->cursor++;
        return $byte;
    }

    public function readInt()
    {
        // preg_match's $offset option ignores ^, so we use a substring.
        if (!preg_match('{^([+-]?[0-9]+)}', substr($this->data, $this->cursor), $matches)) {
            throw new ClonerSerializedReaderException($this->cursor, 'expected number');
        }
        $this->cursor += strlen($matches[0]);
        return intval($matches[0]);
    }

    public function readFloat()
    {
        // preg_match's $offset option ignores ^, so we use a substring.
        if (!preg_match('{^(?:NAN|-?INF|[+-]?(?:[0-9]+\.[0-9]*|[0-9]*\.[0-9]+|[0-9]+)(?:[eE][+-]?[0-9]+)?)}', substr($this->data, $this->cursor), $matches)) {
            throw new ClonerSerializedReaderException($this->cursor, 'expected number');
        }
        $this->cursor += strlen($matches[0]);
        switch ($matches[0]) {
            case 'INF':
                return INF;
            case '-INF':
                return -INF;
            case 'NAN';
                return NAN;
            default:
                return floatval($matches[0]);
        }
    }

    public function readExpect($expect)
    {
        $got = $this->read(strlen($expect));
        if ($got !== $expect) {
            throw new ClonerSerializedReaderException($this->cursor, sprintf('expected "%s", got "%s"', $expect, $got));
        }
    }
}

class ClonerSerializedReaderException extends ClonerException
{
    public $offset;

    public function __construct($offset, $message)
    {
        $this->offset = $offset;
        parent::__construct(sprintf("cloner_serialized_replace error at offset %d: %s", $offset, $message));
    }
}


function set_vBulletin_config($db, $prefix, array $options)
{
    $conn = cloner_db_conn($db);
    foreach ($options as $key => $value) {
        $conn->query("UPDATE {$prefix}setting SET value = :config_value WHERE varname = :config_name", array(
            'config_name'  => $key,
            'config_value' => $value,
        ));
    }
    return array();
}


function set_phpBB_config($db, $prefix, array $options)
{
    $conn = cloner_db_conn($db);
    foreach ($options as $key => $value) {
        $conn->query("UPDATE {$prefix}config SET config_value = :config_value WHERE config_name = :config_name", array(
            'config_name'  => $key,
            'config_value' => $value,
        ));
    }
    return array();
}


/**
 * @param ClonerDBConn|array $db
 * @param string             $id
 * @param string             $prefix
 * @param bool               $activateWorker
 * @param int                $timeout
 * @param bool               $isOriginalHtaccess
 *
 * @return array
 *
 * @throws ClonerException
 */
function cloner_action_flush_rewrite_rules($db, $id, $prefix, $activateWorker, $timeout, $isOriginalHtaccess)
{
    $conn = cloner_db_conn($db);
    if (function_exists('w3tc_pgcache_flush')) {
        w3tc_pgcache_flush();
    }
    if (function_exists('w3tc_dbcache_flush')) {
        w3tc_dbcache_flush();
    }
    if (function_exists('w3tc_objectcache_flush')) {
        w3tc_objectcache_flush();
    }
    if (isset($_SERVER['GD_PHP_HANDLER'], $_SERVER['GD_ERROR_DOC'])) {
        // GoDaddy managed wordpress hosting, manually flush cache.
        do_action('flush_cache', array('ban' => 1, 'urls' => array()));
    }
    if (class_exists('ESSBDynamicCache') && is_callable(array('ESSBDynamicCache', 'flush'))) {
        /** @noinspection PhpUndefinedClassInspection */
        ESSBDynamicCache::flush();
    }
    if (function_exists('purge_essb_cache_static_cache')) {
        purge_essb_cache_static_cache();
    }
    if (is_multisite()) {
        /** @noinspection PhpUndefinedFunctionInspection */
        add_filter('mod_rewrite_rules', array(__CLASS__, 'msRewriteRules'));
    }
    $ok = false;
    if (!$isOriginalHtaccess){
        /** @noinspection PhpUndefinedFunctionInspection */
        flush_rewrite_rules(true);
        if ($timeout) {
            $done = time();
            while ($timeout) {
                $row       = $conn->query("SELECT option_value FROM {$prefix}options WHERE option_name = 'clone_heartbeat_{$id}'")->fetch();
                $heartbeat = is_array($row) ? (int)end($row) : 0;
                if ($heartbeat - 1 > $done) {
                    // We hit PHP after the creation of rewrite rules - everything looks ok.
                    $ok = true;
                    break;
                }
                $timeout--;
                sleep(1);
            }
            $conn->query("DELETE FROM {$prefix}options WHERE option_name = 'clone_heartbeat_{$id}'");
            if (!$ok) {
                //postoji mogucnost da obrisemo pogresan htaccess
                foreach (array(ABSPATH.'.htaccess', ABSPATH.'../.htaccess') as $path) {
                    unlink($path);
                }
            }
        }
    }

    // From wp-admin/admin.php
    global $wp_db_version;
    /** @noinspection PhpUndefinedFunctionInspection */
    if ($ok && get_option('db_version') != $wp_db_version) {
        if (!function_exists('wp_upgrade')) {
            /** @noinspection PhpIncludeInspection */
            require_once cloner_constant('ABSPATH').'wp-admin/includes/upgrade.php';
        }
        ob_start();
        /** @noinspection PhpUndefinedFunctionInspection */
        wp_upgrade();
        do_action('after_db_upgrade');
        ob_end_clean();
    }
    if ($activateWorker) {
        $pluginsOption = 'active_plugins';
        $multisite     = (defined('MULTISITE') && MULTISITE) || (defined('SUBDOMAIN_INSTALL') || defined('VHOST') || defined('SUNRISE'));
        if ($multisite) {
            $pluginsOption = 'active_sitewide_plugins';
        }
        $activePluginsSerialized = $conn->query("SELECT option_value FROM {$prefix}options WHERE option_name = '{$pluginsOption}' LIMIT 1")->fetch();
        $activePluginsSerialized = (string)@end($activePluginsSerialized);
        $activePlugins           = @unserialize($activePluginsSerialized);
        $workerPlugin            = 'worker/init.php';
        if (is_array($activePlugins) && $multisite && !array_key_exists($workerPlugin, $activePlugins)) {
            $activePlugins[$workerPlugin] = true;
            $conn->query("UPDATE {$prefix}options SET option_value = :val WHERE option_name = '{$pluginsOption}' LIMIT 1", array(
                'val' => serialize($activePlugins),
            ));
        } elseif (is_array($activePlugins) && !$multisite && !in_array($workerPlugin, $activePlugins, true)) {
            $activePlugins[] = $workerPlugin;
            $conn->query("UPDATE {$prefix}options SET option_value = :val WHERE option_name = '{$pluginsOption}' LIMIT 1", array(
                'val' => serialize($activePlugins),
            ));
        }
    }
    return array('ok' => $ok);
}

/**
 * @param ClonerDBConn|array $db
 * @param string             $prefix
 * @param array              $options
 *
 * @return array
 *
 * @throws ClonerException
 */
function cloner_set_wordpress_options($db, $prefix, array $options)
{
    $conn = cloner_db_conn($db);
    foreach ($options as $key => $value) {
        if ($value === null) {
            $conn->query("DELETE FROM {$prefix}options WHERE option_name = :option_name", array(
                'option_name' => $key,
            ));
        } else {
            $conn->query("INSERT INTO {$prefix}options SET option_name = :option_name, option_value = :option_value ON DUPLICATE KEY UPDATE option_value = :option_value", array(
                'option_name'  => $key,
                'option_value' => $value,
            ));
        }
    }
    return array();
}


function set_magento_config($db, $prefix, array $options)
{
    $conn = cloner_db_conn($db);
    foreach ($options as $key => $value) {
        $conn->query("UPDATE {$prefix}core_config_data SET value = :config_value WHERE path = :config_path", array(
            'config_path'  => $key,
            'config_value' => $value,
        ));
    }
    return array();
}




function cloner_migrate_site_url(ClonerDBConn $conn, $timeout, array $args, $state, $cms)
{
    switch ($cms) {
        case 'wordpress':
            return migrate_wordpress_site_url($conn, $timeout, $args, $state);
        case 'vbulletin':
            return migrate_vbulletin_site_url($conn, $timeout, $args, $state);
        default:
            return array($state, true);
    }
}

function migrate_wordpress_site_url(ClonerDBConn $conn, $timeout, array $args, $state)
{
    $deadline = time() + $timeout;
    list($prefix, $oldUrl, $newUrl) = $args;
    if (empty($state)) {
        $state       = array(
            'migrations' => array(),
            'multisite'  => false,
            'done'       => array(),
            'count'      => array(),
            'cursor'     => array(),
        );
        $isMultisite = (bool)$conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = :table_schema AND table_name = :table_name", array(
            'table_name'   => $prefix.'blogs',
            'table_schema' => $conn->getConfiguration()->name,
        ))->fetch();
        $migrations  = array(array(0, $oldUrl, $newUrl));
        if ($isMultisite) {
            $oldRootHost = parse_url($oldUrl, PHP_URL_HOST);
            $oldRootPath = rtrim(parse_url($oldUrl, PHP_URL_PATH), '/').'/';
            $newRootHost = parse_url($newUrl, PHP_URL_HOST);
            $newRootPath = rtrim(parse_url($newUrl, PHP_URL_PATH), '/').'/';
            $conn->query("UPDATE {$prefix}site SET domain = :domain, path = :path ORDER BY id ASC LIMIT 1", array(
                'domain' => $newRootHost,
                'path'   => $newRootPath,
            ));
            // Skip master site in the query.
            $query = $conn->query("SELECT blog_id, site_id, domain, path FROM {$prefix}blogs WHERE deleted=0");
            while ($row = $query->fetch()) {
                // Scheme is not important here, since we migrate both of them.
                $oldBlogURL = 'http://'.$row['domain'].$row['path'];
                list($newHost, $newPath, $replaced) = cloner_update_multisite_url($oldRootHost, $oldRootPath, $newRootHost, $newRootPath, $row['domain'], $row['path']);
                $newBlogURL = 'http://'.$newHost.$newPath;
                if ($replaced && ($row['blog_id'] !== $row['site_id'])) {
                    $migrations[] = array((int)$row['blog_id'], $oldBlogURL, $newBlogURL);
                    // Also migrate root URLs on every child blog.
                    $migrations[] = array((int)$row['blog_id'], $oldUrl, $newUrl);
                }
                if (!$replaced) {
                    continue;
                }
                $conn->query("UPDATE {$prefix}blogs SET domain = :domain, path = :path WHERE blog_id = :blog_id", array(
                    'domain'  => parse_url($newBlogURL, PHP_URL_HOST),
                    'path'    => parse_url($newBlogURL, PHP_URL_PATH),
                    'blog_id' => $row['blog_id'],
                ));
            }
            $state['multisite'] = true;
        }
        $state['migrations'] = $migrations;
    }
    $migrateColumns = array(
        array('postmeta', 'meta_id', 'meta_value'),
        array('options', 'option_id', 'option_value'),
        array('posts', 'ID', 'post_content'),
        array('comments', 'comment_ID', 'comment_content'),
    );
    $updateLimit    = 100;
    foreach ($state['migrations'] as $migration) {
        list($id, $oldUrl, $newUrl) = $migration;
        $sitePrefix       = $prefix;
        $oldDomainAndPath = cloner_url_slug($oldUrl);
        if ($id) {
            $sitePrefix .= "{$id}_";
        }
        $newURLPrefix = rtrim($newUrl, '/').'/';
        foreach (array('http://'.$oldDomainAndPath.'/', 'https://'.$oldDomainAndPath.'/', 'http://www.'.$oldDomainAndPath.'/', 'https://www.'.$oldDomainAndPath.'/') as $oldURLPrefix) {
            if (time() >= $deadline) {
                // Timeout.
                return array($state, false);
            }
            $key = $oldURLPrefix.'#posts.guid';
            if (!empty($state['done'][$key])) {
                continue;
            }
            // Migrate posts (GUID)
            // WordPress initially uses options without the leading slash, but always has them in GUID fields.
            while ($count = cloner_update_field_prefix($conn, $sitePrefix.'posts', 'guid', $oldURLPrefix, $newURLPrefix, $updateLimit)) {
                if (!isset($state['count'][$key])) {
                    $state['count'][$key] = 0;
                }
                $state['count'][$key] += $count;
                if ($count < $updateLimit) {
                    // We didn't reach the update count limit, meaning we're done with this migration.
                    break;
                }
                if (time() >= $deadline) {
                    // Timeout.
                    return array($state, false);
                }
            }
            $state['done'][$key] = true;
        }
        $search                   = '{(https?:)?//(?:www\.)?'.preg_quote($oldDomainAndPath).'(/?)}i';
        $replacer                 = new ClonerURLReplacer(rtrim($newUrl, '/'));
        $replace                  = array($replacer, 'replace');
        $likeOldDomainAndPath     = $conn->escape('%'.cloner_escape_like($oldDomainAndPath).'%');
        $likeOldDomainAndPathJSON = $conn->escape('%'.cloner_escape_like(substr(json_encode($oldDomainAndPath), 1, -1)).'%');
        foreach ($migrateColumns as $migrationData) {
            if (time() >= $deadline) {
                // Timeout.
                return array($state, false);
            }
            list($table, $identifier, $field) = $migrationData;
            $key = $oldUrl.'#'.$table.'.'.$field;
            if (!empty($state['done'][$key])) {
                continue;
            }
            $where  = sprintf('(`%s` LIKE %s OR `%s` LIKE %s)', $field, $likeOldDomainAndPath, $field, $likeOldDomainAndPathJSON);
            $cursor = @$state['cursor'][$key];
            list($count, $cursor, $finished) = cloner_update_serialized_field($conn, $sitePrefix.$table, $identifier, $field, $search, $replace, $deadline, $cursor, $where);
            $state['cursor'][$key] = $cursor;
            if (!isset($state['count'][$key])) {
                $state['count'][$key] = 0;
            }
            $state['count'][$key] += $count;
            if (!$finished) {
                // We hit deadline.
                return array($state, false);
            }
            $state['done'][$key] = true;
        }
    }
    return array($state, true);
}

function migrate_vbulletin_site_url(ClonerDBConn $conn, $timeout, array $args, $state)
{
    list($prefix) = $args;
    $conn->query("UPDATE {$prefix}datastore SET data = :data WHERE title = :options OR title = :publicoptions" , array(
        'data'            => "",
        'options'         => "options",
        'publicoptions'   => "publicoptions",
    ));
    return array($state, true);
}

function cloner_migrate_table_prefix(ClonerDBConn $conn, $timeout, array $args, $state, $cms)
{
    switch ($cms){
        case 'wordpress':
            return migrate_wordpress_table_prefix($conn, $timeout, $args, $state);
        default:
            return array($state, true);
        }
}

function migrate_wordpress_table_prefix(ClonerDBConn $conn, $timeout, array $args, $state)
{
    $deadline = time() + $timeout;
    list($oldPrefix, $newPrefix) = $args;
    $userMetaKeys = array(
        'capabilities',
        'dashboard_quick_press_last_post_id',
        'user-settings',
        'user-settings-time',
        'user_level',
    );

    if (empty($state)) {
        $state      = array(
            'update_roles'          => false,
            'update_roles_count'    => 0,
            'delete_usermeta'       => false,
            'update_usermeta'       => false,
            'update_usermeta_count' => false,
        );
        $cleanQuery = <<<SQL
DELETE FROM `{$newPrefix}options`
  WHERE option_name = '{$newPrefix}user_roles'
SQL;
        $conn->query($cleanQuery);

        $roleQuery                   = <<<SQL
UPDATE `{$newPrefix}options`
  SET option_name = '{$newPrefix}user_roles'
  WHERE option_name = '{$oldPrefix}user_roles'
SQL;
        $state['update_roles_count'] = $conn->query($roleQuery)->getNumRows();
        $state['update_roles']       = true;

        // First make room for the updated meta keys. This should be a no-op for most
        // installations and is here only to be safe.
        $newKeys  = "'{$newPrefix}".implode("', '{$newPrefix}", $userMetaKeys)."'";
        $cleanSQL = <<<SQL
DELETE FROM `{$newPrefix}usermeta`
  WHERE `meta_key` IN ({$newKeys})
SQL;
        $conn->query($cleanSQL);
        $state['delete_usermeta'] = true;
    }
    $updateLimit = 100;
    while ($count = cloner_update_usermeta_prefix($conn, $userMetaKeys, $oldPrefix, $newPrefix, $updateLimit)) {
        if (!isset($state['update_usermeta'])) {
            $state['update_usermeta'] = 0;
        }
        $state['update_usermeta'] += $count;
        if ($count < $updateLimit) {
            break;
        }
        if (time() >= $deadline) {
            // Timeout.
            return array($state, false);
        }
    }
    return array($state, true);
}

/**
 * @param callable $function
 * @param mixed    $structure
 * @param string[] $walkedRefs
 *   SPL object hash map of objects that have been walked through.
 * @param mixed    ...$args
 *
 * @return int Number of updated occurrences.
 * @throws Exception
 */
function cloner_structure_walk_recursive($function, &$structure, &$walkedRefs = array(), $args = null)
{
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    array_shift($args);

    switch ($type = gettype($structure)) {
        case 'integer':
        case 'boolean':
        case 'float':
        case 'double':
        case 'NULL':
            return 0;
        case 'string':
            return call_user_func_array($function, array_merge(array(&$structure), $args));
        /** @noinspection PhpMissingBreakStatementInspection */
        case 'object':
            if ($structure instanceof Iterator) {
                // PHP error: iterator cannot be used with foreach by reference
                return 0;
            }
            // Handle recursion.
            // __PHP_Incomplete_Class will return false on is_object() call. Luckily, we can still get its object hash.
            $objectHash = spl_object_hash($structure);
            if (isset($walkedRefs[$objectHash])) {
                return 0;
            }
            $walkedRefs[$objectHash] = true;
        // Fall through.
        case 'array':
            $updated = 0;
            // Object and array are by default traversable.
            foreach ($structure as &$value) {
                $updated += call_user_func_array(__FUNCTION__, array_merge(array($function, &$value, &$walkedRefs), $args));
            }

            return $updated;
        default:
            throw new ClonerException('Unsupported structure passed: '.$type, 'unsupported_type');
    }
}

function cloner_maybe_json_decode(&$value)
{
    if (!is_string($value)) {
        return false;
    }

    $startsWith = substr($value, 0, 1);

    if (in_array($startsWith, array('[', '{'), true)) {
        $newValue = json_decode($value, true);
        if ($newValue !== null || $value === 'null') {
            $value = $newValue;
            return true;
        }
    }
    return false;
}

function cloner_preg_replace(&$value, $search, $replace)
{
    if (!is_string($value)) {
        return 0;
    }
    $value = preg_replace_callback($search, $replace, $value, -1, $count);
    return $count;
}

class ClonerDBCharsetFixer
{
    private $conn;
    private $info;

    public function __construct(ClonerDBConn $conn)
    {
        $this->conn = $conn;
    }

    private function loadInfo()
    {
        if ($this->info !== null) {
            return;
        }
        $this->info = cloner_db_info($this->conn);
    }

    public function replaceCharsetOrCollation(array $matches)
    {
        $name = $matches[0];
        $this->loadInfo();
        if (strpos($name, '_') !== false) {
            // Collation
            if (!empty($this->info['collation'][$name])) {
                return $name;
            }
            // utf8mb4_unicode_520_ci => utf8mb4_unicode_520_ci
            $try = str_replace('_520_', '_', $name, $count);
            if ($count && !empty($this->info['collation'][$try])) {
                return $try;
            }
            // utf8mb4_unicode_520_ci => utf8_unicode_520_ci
            $try = str_replace('utf8mb4', 'utf8', $name, $count);
            if ($count && !empty($this->info['collation'][$try])) {
                return $try;
            }
            // utf8mb4_unicode_520_ci => utf8_unicode_ci
            $try = str_replace(array('utf8mb4', '_520_'), array('utf8', '_'), $name, $count);
            if ($count && !empty($this->info['collation'][$try])) {
                return $try;
            }
        } else {
            // Encoding
            if (!empty($this->info['charset'][$name])) {
                return $name;
            }
            $try = str_replace('utf8mb4', 'utf8', $name, $count);
            if ($count && !empty($this->info['charset'][$try])) {
                return $try;
            }
        }
        return $name;
    }
}

/**
 * @param ClonerDBConn $conn       Connection to use.
 * @param string       $table      Table name to update.
 * @param string       $identifier Identifier column name.
 * @param string       $field      Field name to update.
 * @param string       $search     Search regexp that gets passed to preg_replace_callback.
 * @param callable     $replace    Replacement callback (for preg_replace_callback), result of matching $search.
 * @param int          $deadline   Deadline or 0 to disable.
 * @param string|null  $cursor     Last updated ID, adds "WHERE $identifier > $cursor".
 * @param string|null  $where      Additional "WHERE" clause.
 *
 * @return array Triple of [count:int, cursor:string, done:bool].
 */
function cloner_update_serialized_field(ClonerDBConn $conn, $table, $identifier, $field, $search, $replace, $deadline = 0, $cursor = null, $where = null)
{
    $realWhere = null;
    if ($cursor !== null) {
        $realWhere = sprintf('WHERE `%s` > %s', $identifier, $cursor);
    }
    if ($where !== null) {
        $realWhere = strlen($realWhere) ? $realWhere.' AND '.$where : 'WHERE '.$where;
    }
    $sql    = <<<SQL
SELECT `{$identifier}` AS __id, `{$field}` AS __field
  FROM `{$table}`
  {$realWhere}
ORDER BY `{$identifier}` ASC
SQL;
    $result = $conn->query($sql);
    $count  = 0;
    while ($row = $result->fetch()) {
        $cursor = $row['__id'];
        if ($deadline && time() >= $deadline) {
            return array($count, $cursor, false);
        }
        $fieldValue   = $row['__field'];
        $isSerialized = false;
        if (in_array(substr($fieldValue, 0, 2), array('S:', 's:', 'a:', 'O:'), true)) {
            try {
                $updatedValue = cloner_serialized_replace($search, $fieldValue, $replace, $count);
                if (!$count) {
                    continue;
                }
                $isSerialized = true;
            } catch (ClonerSerializedReaderException $e) {
            }
        }
        $isJSON = false;
        if (!$isSerialized && cloner_maybe_json_decode($fieldValue)) {
            $refs    = array();
            $updated = cloner_structure_walk_recursive('cloner_preg_replace', $fieldValue, $refs, $search, $replace);
            if (!$updated) {
                continue;
            }
            $updatedValue = json_encode($fieldValue);
            $isJSON       = true;
        }
        if (!$isJSON && !$isSerialized) {
            if (!cloner_preg_replace($fieldValue, $search, $replace)) {
                continue;
            }
            $updatedValue = $fieldValue;
        }
        $updateSql    = sprintf("UPDATE `{$table}` SET `{$field}` = %s WHERE `{$identifier}` = %s", $conn->escape($updatedValue), $conn->escape($row['__id']));
        $updateResult = $conn->query($updateSql);
        $count        += $updateResult->getNumRows();
        $updateResult->free();
    }
    $result->free();
    return array($count, $cursor, true);
}

function cloner_escape_like($value)
{
    return str_replace(array('\\', '_', '%'), array('\\\\', '\_', '\%'), $value);
}







function cloner_script()
{
    // This will be replaced with full script source at application startup.
    return base64_decode('__CLONER_SCRIPT__');
}

/**
 * Returns all available sources that can be detected by the system.
 *
 * @return ClonerSiteSource[]
 */
function cloner_sources()
{
    return array(
        new ClonerWordPressSiteSource(),
        new ClonerDrupalSiteSource(),
        new ClonerJoomlaSiteSource(),
        new ClonerMagentoSiteSource(),
        new ClonerMagentoOneSiteSource(),
        new ClonerPhpBBSiteSource(),
        new ClonerVBulletinSiteSource(),
        new ClonerStaticSiteSource(),
    );
}

/**
 * Returns setup information for first detected CMS, or only specific CMS if $forceCMS is set.
 *
 * @param $root          string Filesystem root to start search on.
 * @param $url           string Filesystem root to start search on.
 * @param $forceCMS      string If this value is set fetch only this CMS specific info.
 * @param $tablePrefix   string Table prefix to hint. If not available, parse info from source config.
 * @param $db            array|null DB connection hint. If not available, parse info from source config.
 * @param $configContent string Content of this site's configuration, eg. wp-config.php file.
 * @param $readOnly      boolean Allows to complete setup when config file doesn't exit on server
 *
 * @return ClonerSetupResult
 * @throws ClonerException If CMS info could not be fetched.
 */
function cloner_setup($root, $url, $forceCMS, $tablePrefix = '', $db = null, $configContent = '', $readOnly = false)
{
    $e = new ClonerException('No sources available to setup', 'no_sources');
    foreach (cloner_sources() as $source) {
        if (strlen($forceCMS) && $forceCMS !== $source->getCMS()) {
            continue;
        }
        try {
            return $source->setup($root, $url, $db, $tablePrefix, $configContent, $readOnly);
        } catch (Exception $e) {
            trigger_error(sprintf('Could not setup %s: %s', $source->getCMS(), $e->getMessage()));
        }
    }
    throw $e;
}

/**
 *
 */
interface ClonerSiteSource
{
    /**
     * @return string CMS type, eg. "wordpress", "drupal", "static".
     */
    public function getCMS();

    /**
     * @param string     $root
     * @param string     $url
     * @param array|null $db            DB info from source, might be empty.
     * @param string     $tablePrefix   Table prefix from source, might be empty.
     * @param string     $configContent wp-config.php from source, might be empty.
     * @param string     $readOnly      Allows to complete setup when config file doesn't exit on server
     *
     * @return ClonerSetupResult
     * @throws ClonerException
     */
    public function setup($root, $url, $db, $tablePrefix, $configContent, $readOnly);
}

class ClonerWordPressSiteSource implements ClonerSiteSource
{
    public function getCMS()
    {
        return 'wordpress';
    }

    /**
     * @param string     $root
     * @param string     $url
     * @param array|null $db            DB info from source, might be empty.
     * @param string     $tablePrefix   Table prefix from source, might be empty.
     * @param string     $configContent wp-config.php from source, might be empty.
     * @param string     $readOnly      Allows to complete setup when config file doesn't exit on server
     *
     * @return ClonerSetupResult
     * @throws ClonerException
     */
    public function setup($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        global $wpdb;
        if (!empty($wpdb)) {
            return $this->setupWorker($url, $db);
        }
        return $this->setupStatic($root, $url, $db, $tablePrefix, $configContent, $readOnly);
    }

    /**
     * @param string     $urlOverride
     * @param array|null $dbOverrides
     * @return ClonerSetupResult
     * @throws ClonerException
     */
    private function setupWorker($urlOverride, array $dbOverrides = null)
    {
        $absPath          = cloner_constant('ABSPATH');
        $result           = new ClonerSetupResult(array(cloner_db_info_from_worker($dbOverrides)), cloner_wp_info_from_worker($urlOverride), cloner_env_info($absPath), cloner_get_site_defining_options());
        $append           = sprintf("\nfunction __cloner_get_state() {\n    return %s;\n}\n", var_export($result->toArray(), true));
        $clonerScript     = cloner_script();
        $result->workerOK = true;
        if (strlen($clonerScript)) {
            if ($error = cloner_create_file("$absPath/cloner.php", $clonerScript.$append, 0444)) {
                $result->noRelay = true;
                return $result;
            }
        } else {
            $result->noRelay = true;
        }
        cloner_create_file("$absPath/cloner_error_log", '', 0666);
        return $result;
    }

    /**
     * @param string     $root
     * @param string     $url
     * @param array|null $db            DB info from source, might be empty.
     * @param string     $tablePrefix   Table prefix from source, might be empty.
     * @param string     $configContent wp-config.php from source, might be empty.
     * @param string     $readOnly      Allows to complete setup when config file doesn't exit on server
     *
     * @return ClonerSetupResult
     * @throws ClonerException
     */
    private function setupStatic($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        $wpConfigPath = 'wp-config.php';
        try {
            if (empty($configContent)) {
                list($wpConfigPath, $configContent) = cloner_env_read_wp_config($root, false);
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        try {
            if (strlen($configContent) === 0) {
                // Find this website's wp-config.php.
                list($wpConfigPath, $configContent) = cloner_env_read_wp_config($root, false);
            }
            $wpConfigInfo = cloner_env_parse_wp_config($configContent);
            if (empty($db[0]['dbName'])) {
                $dbHost = $wpConfigInfo->dbHost;
                if (!empty($db[0]['dbHost'])) {
                    // Allow host override in database credentials.
                    $dbHost = $db[0]['dbHost'];
                }
                $db = array(new ClonerDBInfo($wpConfigInfo->dbUser, $wpConfigInfo->dbPassword, $dbHost, $wpConfigInfo->dbName));
            }
            $tablePrefix = $wpConfigInfo->wpTablePrefix;
        } catch (Exception $exception) {
            if (!$readOnly) {
                throw $e;
            }
        }
        // Verify connection info.
        cloner_db_conn($db)->ping();
        $htaccessContent = @file_get_contents($root.'/.htaccess');
        $clonerWpInfo    = new ClonerWPInfo($url, $root, $tablePrefix, $wpConfigPath, $configContent,
            '', 'wp-content', 'wp-content/plugins', 'wp-content/mu-plugins', 'wp-content/uploads', $htaccessContent);
        return new ClonerSetupResult($db, $clonerWpInfo, cloner_env_info($root));
    }
}

class ClonerStaticSiteSource implements ClonerSiteSource
{
    public function getCMS()
    {
        return 'static';
    }

    public function setup($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        $dbInfo   = array();
        $hasDB    = false;
        $dbErrors = array();
        if (!empty($db[0]['dbName'])) {
            $hasDB = true;
            $conn  = cloner_db_conn($db);
            foreach ($conn->getConnectionIDs() as $connID) {
                $conn->useConnection($connID);
                try {
                    $conn->ping();
                    $dbInfo[] = $conn->getConfiguration()->toArray();
                } catch (ClonerException $e) {
                    $conf       = $conn->getConfiguration();
                    $dbErrors[] = array(
                        'source' => $conf->getID(),
                        'code'   => $e->getErrorCode(),
                        'error'  => $e->getMessage(),
                    );
                }
                try {
                    $conn->close();
                } catch (Exception $e) {
                }
            }
        }
        $htaccessContent = @file_get_contents($root.'/.htaccess');
        return new ClonerSetupResult($dbInfo, new ClonerStaticInfo($url, $root, $hasDB, $dbErrors, $htaccessContent), cloner_env_info($root));
    }
}

class ClonerDrupalSiteSource implements ClonerSiteSource
{
    public function getCMS()
    {
        return 'drupal';
    }

    public function setup($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        return $this->setupDrupal($root, $url, $db, $tablePrefix, $configContent, $readOnly);
    }

    private function setupDrupal($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        $configPath = "sites/default/settings.php";
        $path       = "$root/sites/default/settings.php";
        try {
            if (empty($configContent)) {
                $configContent = cloner_get_contents($path);
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        try {
            $databases = array();
            if (!file_exists($path)) {
                throw new ClonerException('No drupal default configuration found', 'no_drupal_config');
            }
            /** @noinspection PhpIncludeInspection */
            require_once $path;
            $dbInfo      = $databases['default']['default'];
            $tablePrefix = $dbInfo['prefix'];
            if (empty($db[0]['dbName'])) {
                $db = array(new ClonerDBInfo($dbInfo['username'], $dbInfo['password'], $dbInfo['host'], $dbInfo['database']));
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        cloner_db_conn($db)->ping();
        $htaccessContent = @file_get_contents($root.'/.htaccess');
        $drupalInfo      = new ClonerDrupalInfo($url, $root, $configPath, $tablePrefix, $configContent, $htaccessContent);
        return new ClonerSetupResult($db, $drupalInfo, cloner_env_info($root));
    }
}

class ClonerJoomlaSiteSource implements ClonerSiteSource
{
    public function getCMS()
    {
        return 'joomla';
    }

    public function setup($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        return $this->setupJoomla($root, $url, $db, $tablePrefix, $configContent, $readOnly);
    }

    private function setupJoomla($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        $configPath = '/configuration.php';
        $path       = $root."/configuration.php";
        if (empty($configContent)) {
            try {
                $configContent = cloner_get_contents($path);
            } catch (Exception $e) {
                if (!$readOnly) {
                    throw $e;
                }
            }
        }
        try {
            if (!file_exists($path)) {
                throw new ClonerException('No joomla configuration found', 'no_joomla_config');
            }
            /** @noinspection PhpIncludeInspection */
            require_once $path;
            $siteInfo    = new JConfig();
            $tablePrefix = $siteInfo->dbprefix;
            if (!empty($db[0]['dbName'])) {
                $db = array(new ClonerDBInfo($siteInfo->user, $siteInfo->password, $siteInfo->host, $siteInfo->db));
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        cloner_db_conn($db)->ping();
        $htaccessContent = @file_get_contents($root.'/.htaccess');
        $drupalInfo      = new ClonerJoomlaInfo($url, $root, $configPath, $tablePrefix, $configContent, $htaccessContent);
        return new ClonerSetupResult($db, $drupalInfo, cloner_env_info($root));
    }
}

class ClonerMagentoSiteSource implements ClonerSiteSource
{
    public function getCMS()
    {
        return 'magento';
    }

    public function setup($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        return $this->setupMagento($root, $url, $db, $tablePrefix, $configContent, $readOnly);
    }

    private function setupMagento($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        $configPath = '/app/etc/env.php';
        $path       = $root."/app/etc/env.php";
        try {
            if (empty($configContent)) {
                $configContent = cloner_get_contents($path);
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        try {
            if (!file_exists($path)) {
                throw new ClonerException('No magento configuration found', 'no_magento_config');
            }
            /** @noinspection PhpIncludeInspection */
            $magentoInfo   = require_once $path;
            $dbInformation = $magentoInfo['db'];
            $tablePrefix   = $dbInformation['table_prefix'];
            $dbConnection  = $dbInformation['connection']['default'];
            if (empty($db[0]['dbName'])) {
                $db = array(new ClonerDBInfo($dbConnection['username'], $dbConnection['password'], $dbConnection['host'], $dbConnection['dbname']));
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        cloner_db_conn($db)->ping();
        $htaccessContent = @file_get_contents($root.'/.htaccess');
        $magentoInfo     = new ClonerMagentoInfo($url, $root, $configPath, $tablePrefix, $configContent, $htaccessContent);
        return new ClonerSetupResult($db, $magentoInfo, cloner_env_info($root));
    }
}

class ClonerMagentoOneSiteSource implements ClonerSiteSource
{
    public function getCMS()
    {
        return 'magento_one';
    }

    public function setup($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        return $this->setupMagentoOne($root, $url, $db, $tablePrefix, $configContent, $readOnly);
    }

    private function setupMagentoOne($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        $configPath = 'app/etc/local.xml';
        $localPath  = $root."/app/etc/local.xml";

        try {
            if (empty($configContent)) {
                $configContent = cloner_get_contents($localPath);
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        try {
            if (!file_exists($localPath)) {
                throw new ClonerException('Could not load magento configuration', 'no_magento_config');
            }
            if (!extension_loaded('simplexml')) {
                throw new ClonerException('Could not load magento, no simplexml extension', 'magento_no_simplexml');
            }
            $magentoLocalXml       = simplexml_load_file($localPath, "SimpleXMLElement", LIBXML_NOCDATA);
            $magentoLocalJson      = json_encode($magentoLocalXml);
            $magentoLocal          = json_decode($magentoLocalJson, true);
            $magentoLocalResources = $magentoLocal['global']['resources'];
            $dbInfo                = $magentoLocalResources['default_setup']['connection'];
            $tablePrefix           = $magentoLocalResources['db']['table_prefix'];
            if (empty($db)) {
                $db = array(new ClonerDBInfo($dbInfo['username'], $dbInfo['password'], $dbInfo['host'], $dbInfo['dbname']));
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        cloner_db_conn($db)->ping();
        $htaccessContent = @file_get_contents($root.'/.htaccess');
        $magentoInfo     = new ClonerMagentoOneInfo($url, $root, $configPath, $tablePrefix, $configContent, $htaccessContent);
        return new ClonerSetupResult($db, $magentoInfo, cloner_env_info($root));
    }
}

class ClonerPhpBBSiteSource implements ClonerSiteSource
{
    public function getCMS()
    {
        return 'phpBB';
    }

    public function setup($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        return $this->setupBBPress($root, $url, $db, $tablePrefix, $configContent, $readOnly);
    }

    private function setupBBPress($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        $path       = $root.'/config.php';
        $configPath = 'config.php';
        try {
            if (empty($configContent)) {
                $configContent = cloner_get_contents($path);
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        try {
            $dbuser       = "";
            $dbpasswd     = "";
            $dbhost       = "";
            $dbname       = "";
            $table_prefix = "";
            if (!file_exists($path)) {
                throw new ClonerException('No phpbb configuration found', 'no_phpbb_config');
            }
            if (!is_dir(dirname($path).'/phpb')) {
                throw new ClonerException('No phpbb directory found', 'no_phpbb_dir');
            }
            /** @noinspection PhpIncludeInspection */
            require_once $path;
            $tablePrefix = $table_prefix;
            if (empty($db[0]['dbName'])) {
                $db = array(new ClonerDBInfo($dbuser, $dbpasswd, $dbhost, $dbname));
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        $htaccessContent = @file_get_contents($root.'/.htaccess');
        $siteInfo        = new ClonerPhpBBInfo($url, $root, $configPath, $tablePrefix, $configContent, $htaccessContent);
        return new ClonerSetupResult($db, $siteInfo, cloner_env_info($root));
    }
}

class ClonerVBulletinSiteSource implements ClonerSiteSource
{
    public function getCMS()
    {
        return 'vbulletin';
    }

    public function setup($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        return $this->setupVBulletin($root, $url, $db, $tablePrefix, $configContent, $readOnly);
    }

    private function setupVBulletin($root, $url, $db, $tablePrefix, $configContent, $readOnly)
    {
        $configPath = 'core/includes/config.php';
        $path       = $root.'/'.$configPath;
        try {
            if (empty($configContent)) {
                $configContent = cloner_get_contents($path);
            }
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        try {
            $config = array();
            if (!file_exists($path)) {
                throw new ClonerException('No vbulletin configuration found', 'no_vbulletin_config');
            }
            /** @noinspection PhpIncludeInspection */
            require_once $path;
            $tablePrefix  = $config['Database']['tableprefix'];
            $connInfo     = $config['MasterServer'];
            $clonerDbInfo = array(new ClonerDBInfo($connInfo['username'], $connInfo['password'], $connInfo['servername'], $config['Database']['dbname']));
        } catch (Exception $e) {
            if (!$readOnly) {
                throw $e;
            }
        }
        cloner_db_conn($clonerDbInfo)->ping();
        $htaccessContent = @file_get_contents($root.'/.htaccess');
        $vBulletinInfo   = new ClonerVBulletinInfo($url, $root, $configPath, $tablePrefix, $configContent, $htaccessContent);
        return new ClonerSetupResult($clonerDbInfo, $vBulletinInfo, cloner_env_info($root));
    }
}

interface ClonerSiteInfo
{
    public function toArray();
}

class ClonerStaticInfo implements ClonerSiteInfo
{
    private $url = '';
    private $root = '';
    private $hasDB = false;
    private $dbErrors = array();
    private $htaccessConfig = '';

    /**
     * @param string $url
     * @param string $root
     * @param bool   $hasDB
     * @param array  $dbErrors
     * @param string $htaccessConfig
     */
    public function __construct($url, $root, $hasDB, array $dbErrors, $htaccessConfig)
    {
        $this->url            = $url;
        $this->root           = $root;
        $this->hasDB          = $hasDB;
        $this->dbErrors       = $dbErrors;
        $this->htaccessConfig = $htaccessConfig;
    }

    public function toArray()
    {
        return array(
            'url'            => $this->url,
            'root'           => $this->root,
            'hasDB'          => $this->hasDB,
            'dbErrors'       => $this->dbErrors,
            'htaccessConfig' => base64_encode($this->htaccessConfig)
        );
    }

    public function getCMS()
    {
        return 'static';
    }
}

class ClonerDrupalInfo implements ClonerSiteInfo
{
    private $url = '';
    private $root = '';
    private $configPath = '';
    private $tablePrefix = '';
    private $config = '';
    private $htaccessConfig = '';

    /**
     * @param string $url
     * @param string $root
     * @param string $configPath
     * @param string $tablePrefix
     * @param string $config
     * @param string $htaccessConfig
     */
    public function __construct($url, $root, $configPath, $tablePrefix, $config, $htaccessConfig)
    {
        $this->url         = $url;
        $this->root        = $root;
        $this->configPath  = $configPath;
        $this->tablePrefix = $tablePrefix;
        $this->config      = $config;
        $this->htaccessConfig = $htaccessConfig;
    }

    public function toArray()
    {
        return array(
            'url'            => $this->url,
            'root'           => $this->root,
            'configPath'     => $this->configPath,
            'tablePrefix'    => $this->tablePrefix,
            'drupalConfig'   => base64_encode($this->config),
            'htaccessConfig' => base64_encode($this->htaccessConfig)
        );
    }

    public function getCMS()
    {
        return 'drupal';
    }
}

class ClonerJoomlaInfo implements ClonerSiteInfo
{
    private $url = '';
    private $root = '';
    private $configPath = '';
    private $tablePrefix = '';
    private $config = '';
    private $htaccessConfig = '';

    public function toArray()
    {
        return array(
            'url'            => $this->url,
            'root'           => $this->root,
            'configPath'     => $this->configPath,
            'tablePrefix'    => $this->tablePrefix,
            'joomlaConfig'   => base64_encode($this->config),
            'htaccessConfig' => base64_encode($this->htaccessConfig)
        );
    }

    /**
     * @param string $url
     * @param string $root
     * @param string $configPath
     * @param string $tablePrefix
     * @param string $config
     * @param string $htaccessConfig
     */
    public function __construct($url, $root, $configPath, $tablePrefix, $config, $htaccessConfig)
    {
        $this->url            = $url;
        $this->root           = $root;
        $this->configPath     = $configPath;
        $this->tablePrefix    = $tablePrefix;
        $this->config         = $config;
        $this->htaccessConfig = $htaccessConfig;
    }

    public function getCMS()
    {
        return 'joomla';
    }

}

class ClonerMagentoInfo implements ClonerSiteInfo
{
    private $url = '';
    private $root = '';
    private $configPath = '';
    private $tablePrefix = '';
    private $config = '';
    private $htaccessConfig = '';

    public function toArray()
    {
        return array(
            'url'            => $this->url,
            'root'           => $this->root,
            'configPath'     => $this->configPath,
            'tablePrefix'    => $this->tablePrefix,
            'magentoConfig'  => base64_encode($this->config),
            'htaccessConfig' => base64_encode($this->htaccessConfig),
        );
    }

    /**
     * @param string $url
     * @param string $root
     * @param string $configPath
     * @param string $tablePrefix
     * @param string $config
     * @param string $htaccessConfig
     */
    public function __construct($url, $root, $configPath, $tablePrefix, $config, $htaccessConfig)
    {
        $this->url            = $url;
        $this->root           = $root;
        $this->configPath     = $configPath;
        $this->tablePrefix    = $tablePrefix;
        $this->config         = $config;
        $this->htaccessConfig = $htaccessConfig;
    }

    public function getCMS()
    {
        return 'magento';
    }

}

class ClonerMagentoOneInfo implements ClonerSiteInfo
{
    private $url = '';
    private $root = '';
    private $configPath = '';
    private $tablePrefix = '';
    private $config = '';
    private $htaccessConfig = '';

    public function toArray()
    {
        return array(
            'url'            => $this->url,
            'root'           => $this->root,
            'configPath'     => $this->configPath,
            'tablePrefix'    => $this->tablePrefix,
            'magentoConfig'  => base64_encode($this->config),
            'htaccessConfig' => base64_encode($this->htaccessConfig),
        );
    }

    /**
     * @param string $url
     * @param string $root
     * @param string $configPath
     * @param string $tablePrefix
     * @param string $config
     * @param string $htaccessConfig
     */
    public function __construct($url, $root, $configPath, $tablePrefix, $config, $htaccessConfig)
    {
        $this->url            = $url;
        $this->root           = $root;
        $this->configPath     = $configPath;
        $this->tablePrefix    = $tablePrefix;
        $this->config         = $config;
        $this->htaccessConfig = htaccessConfig;
    }

    public function getCMS()
    {
        return 'magento_one';
    }

}

class ClonerPhpBBInfo implements ClonerSiteInfo
{
    private $url = '';
    private $root = '';
    private $configPath = '';
    private $tablePrefix = '';
    private $config = '';
    private $htaccessConfig = '';

    public function toArray()
    {
        return array(
            'url'            => $this->url,
            'root'           => $this->root,
            'configPath'     => $this->configPath,
            'tablePrefix'    => $this->tablePrefix,
            'phpBBConfig'    => base64_encode($this->config),
            'htaccessConfig' => base64_encode($this->htaccessConfig),
        );
    }

    /**
     * @param string $url
     * @param string $root
     * @param string $configPath
     * @param string $tablePrefix
     * @param string $config
     * @param string $htaccessConfig
     */
    public function __construct($url, $root, $configPath, $tablePrefix, $config, $htaccessConfig)
    {
        $this->url            = $url;
        $this->root           = $root;
        $this->configPath     = $configPath;
        $this->tablePrefix    = $tablePrefix;
        $this->config         = $config;
        $this->htaccessConfig = $htaccessConfig;

    }

    public function getCMS()
    {
        return 'phpBB';
    }

}

class ClonerVBulletinInfo implements ClonerSiteInfo
{
    private $url = '';
    private $root = '';
    private $configPath = '';
    private $tablePrefix = '';
    private $config = '';
    private $htaccessConfig = '';

    public function toArray()
    {
        return array(
            'url'             => $this->url,
            'root'            => $this->root,
            'configPath'      => $this->configPath,
            'tablePrefix'     => $this->tablePrefix,
            'vBulletinConfig' => base64_encode($this->config),
            'htaccessConfig'  => base64_encode($this->htaccessConfig),
        );
    }

    /**
     * @param string $url
     * @param string $root
     * @param string $configPath
     * @param string $tablePrefix
     * @param string $config
     * @param string $htaccessConfig
     */
    public function __construct($url, $root, $configPath, $tablePrefix, $config, $htaccessConfig)
    {
        $this->url            = $url;
        $this->root           = $root;
        $this->configPath     = $configPath;
        $this->tablePrefix    = $tablePrefix;
        $this->config         = $config;
        $this->htaccessConfig = $htaccessConfig;
    }

    public function getCMS()
    {
        return 'vbulletin';
    }

}

class ClonerWPInfo implements ClonerSiteInfo
{
    public $url = '';
    public $absPath = '';
    /** @var ClonerTable[] */
    public $tablePrefix = '';
    public $configPath = '';
    /** @var string Base64-encoded wp-config.php content. */
    public $config = '';
    /** @var string Installation directory, eg 'wp'. */
    public $installDir = '';
    public $contentPath = '';
    public $pluginsPath = '';
    public $muPluginsPath = '';
    public $uploadsPath = '';
    public $htaccessConfig = '';

    /**
     * @param string $url
     * @param string $absPath
     * @param string $tablePrefix
     * @param string $configPath
     * @param string $config
     * @param string $installDir
     * @param string $contentPath
     * @param string $pluginsPath
     * @param string $muPluginsPath
     * @param string $uploadsPath
     * @param string $htaccessConfig
     */
    public function __construct($url, $absPath, $tablePrefix, $configPath, $config, $installDir, $contentPath, $pluginsPath, $muPluginsPath, $uploadsPath, $htaccessConfig)
    {
        if (empty($absPath)) {
            $absPath = '/';
        }
        $this->url            = $url;
        $this->absPath        = rtrim(strtr($absPath, '\\', '/'), '/');
        $this->tablePrefix    = $tablePrefix;
        $this->configPath     = trim(strtr($configPath, '\\', '/'), '/');
        $this->config         = $config;
        $this->installDir     = $installDir;
        $this->contentPath    = trim(strtr($contentPath, '\\', '/'), '/');
        $this->pluginsPath    = trim(strtr($pluginsPath, '\\', '/'), '/');
        $this->muPluginsPath  = trim(strtr($muPluginsPath, '\\', '/'), '/');
        $this->uploadsPath    = trim(strtr($uploadsPath, '\\', '/'), '/');
        $this->htaccessConfig = $htaccessConfig;
    }

    public function toArray()
    {
        return array(
            'wpURL'           => $this->url,
            'wpAbsPath'       => $this->absPath,
            'wpContentPath'   => $this->contentPath,
            'wpPluginsPath'   => $this->pluginsPath,
            'wpMuPluginsPath' => $this->muPluginsPath,
            'wpUploadsPath'   => $this->uploadsPath,
            'wpConfigPath'    => $this->configPath,
            'wpConfig'        => base64_encode($this->config),
            'wpTablePrefix'   => $this->tablePrefix,
            'wpInstallDir'    => $this->installDir,
            'htaccessConfig'  => base64_encode($this->htaccessConfig),
        );
    }

    public function getCMS()
    {
        return 'wordpress';
    }
}


/**
 * Parses $tokens and for each define('NAME', VALUE); returns the value of eval(VALUE).
 *
 * @param array $tokens Result of token_get_all.
 *
 * @return array All constants in CONSTANT_NAME => EVALUATED_CONSTANT_VALUE format.
 */
function cloner_get_constants_from_tokens(array $tokens)
{
    $definitions    = array();
    $phase          = 0;
    $lastDefinition = '';
    $lastValue      = '';
    $indent         = 0;
    foreach ($tokens as $token) {
        if (is_array($token) && ($token[0] === T_WHITESPACE || $token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT)) {
            // Skip whitespace and comment tokens.
            continue;
        }
        if ($phase === 0) {
            // Look for a 'define' function call.
            if (is_array($token) && $token[0] === T_STRING && strtolower($token[1]) === 'define') {
                // This is a 'define' call, move to next phase.
                $phase = 1;
            }
        } elseif ($phase === 1 && $token === '(') {
            // Open parentheses found, move to next phase.
            $phase = 2;
        } elseif ($phase === 2 && is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
            // Constant string found, save it for later
            $lastDefinition = trim($token[1], '"\'');
            $phase          = 3;
        } elseif ($phase === 3 && $token === ',') {
            // Comma found.
            $phase = 4;
        } elseif ($phase === 4) {
            if ($token === '(') {
                $indent++;
            } elseif ($token === ')') {
                if ($indent === 0) {
                    $definitions[$lastDefinition] = eval(sprintf('return %s;', $lastValue));
                    $phase                        = 0;
                    $lastValue                    = '';
                    continue;
                } else {
                    $indent--;
                }
            }
            $lastValue .= is_array($token) ? $token[1] : $token;
        } else {
            // Unsupported token found, reset the parser phase.
            $phase     = 0;
            $lastValue = '';
        }
    }
    return $definitions;
}

/**
 * Parses $tokens and looks for $table_prefix = 'string'; declaration, and returns the value of eval('string').
 *
 * @param array $tokens Result of token_get_all.
 *
 * @return string|null Table prefix, or null if one could not be found.
 */
function cloner_get_wp_config_table_prefix(array $tokens)
{
    $phase = 0;
    foreach ($tokens as $token) {
        if (is_array($token) && ($token[0] === T_WHITESPACE || $token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT)) {
            // Skip whitespace and comment tokens.
            continue;
        }
        if ($phase === 0) {
            if (is_array($token) && $token[0] === T_VARIABLE && strtolower($token[1]) === '$table_prefix') {
                $phase = 1;
            }
        } elseif ($phase === 1 && $token === '=') {
            $phase = 2;
        } elseif ($phase === 2 && is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
            return eval(sprintf('return %s;', $token[1]));
        } else {
            $phase = 0;
        }
    }
    return null;
}





function cloner_create_file($path, $contents, $mode = null)
{
    if (@file_put_contents($path, $contents) !== false) {
        if ($mode !== null) {
            @chmod($path, $mode);
        }
        return null;
    }
    $error = cloner_last_error_for('file_put_contents');
    if (@chmod($path, 0666) !== false && @file_put_contents($path, $contents) !== false) {
        if ($mode !== null) {
            @chmod($path, $mode);
        }
        return null;
    }
    if (defined('ABSPATH') && is_file(ABSPATH.'wp-admin/includes/file.php')) {
        require_once ABSPATH.'wp-admin/includes/file.php';
        WP_Filesystem();
        /** @var WP_Filesystem $wp_filesystem */
        global $wp_filesystem;
        if (!$wp_filesystem) {
            return sprintf('could not load WP_Filesystem; PHP write error: %s', $error);
        }
        $ok = $wp_filesystem->put_contents(ABSPATH.'cloner.php', $contents);
        if (!$ok) {
            $vars = get_object_vars($wp_filesystem);
            if (!empty($vars['errors']) && $vars['errors'] instanceof WP_Error) {
                $wpError = implode('; ', $vars['errors']->get_error_messages());
            } else {
                $wpError   = 'unknown error';
                $lastError = error_get_last();
                if (!empty($lastError['message'])) {
                    $wpError = $lastError['message'];
                }
            }
            return sprintf('could not write via %s: %s; PHP write error: %s', get_class($wp_filesystem), $wpError, $error);
        }
        return null;
    }
    return $error;
}

class ClonerSetupResult
{
    /** @var array|null */
    public $db;
    /** @var ClonerSiteInfo|null */
    public $site;
    /** @var ClonerEnvInfo|null */
    public $env;
    /** @var array setupError:string, setupErrorCode:string */
    /** @var array Map in the format optionName:string => optionValue:string */
    public $keepOptions = array();
    /** @var bool */
    public $noRelay = false;
    /** @var bool */
    public $workerOK = false;

    /**
     * ClonerSetupResult constructor.
     * @param array|ClonerDBInfo[]|null $dbInfo
     * @param ClonerSiteInfo            $siteInfo
     * @param ClonerEnvInfo             $envInfo
     * @param array                     $keepOptions
     */
    public function __construct(array $dbInfo = null, ClonerSiteInfo $siteInfo, ClonerEnvInfo $envInfo, array $keepOptions = array())
    {
        $this->db          = $dbInfo;
        $this->site        = $siteInfo;
        $this->env         = $envInfo;
        $this->keepOptions = $keepOptions;
    }

    public function toArray()
    {
        $siteInfo        = $this->site->toArray();
        $siteInfo['cms'] = $this->site->getCMS();
        foreach ($this->db as $k => $v) {
            if ($v instanceof ClonerDBInfo) {
                $this->db[$k] = $v->toArray();
            }
        }
        return array(
            'ok'          => true,
            'site'        => $siteInfo,
            'db'          => $this->db,
            'env'         => $this->env->toArray(),
            'keepOptions' => $this->keepOptions ? $this->keepOptions : null,
            'clonerOK'    => !$this->noRelay,
            'workerOK'    => $this->workerOK,
        );
    }
}

/**
 * @return array Map of options to keep for normal site operation, in optionName:string => optionValue:mixed format.
 */
function cloner_get_site_defining_options()
{
    global $wpdb;
    $backupOptions = array('_worker_public_key', 'mwp_worker_configuration', 'mmb_worker_activation_time', 'mwp_service_key',
        'mwp_communication_key', 'mwp_potential_key', 'mwp_potential_key_time', 'mwp_container_site_parameters', 'mwp_container_parameters',
        'mwp_communication_keys', 'mwp_public_keys', 'mwp_public_keys_refresh_time', 'mwp_worker_brand');
    $keepOptions   = array();
    foreach ($backupOptions as $option) {
        $value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = %s", $option));
        if ($value === null) {
            continue;
        }
        $keepOptions[$option] = $value;
    }
    return $keepOptions;
}

/** @noinspection SqlNoDataSourceInspection */




interface ClonerImportFilter
{
    /**
     * @param $statement
     *
     * @return string
     *
     * @throws ClonerException
     */
    public function filter($statement);
}

class ClonerDBStorageFilter implements ClonerImportFilter
{
    public function filter($statement)
    {
        if (strncmp($statement, 'CREATE TABLE ', 13) !== 0) {
            return $statement;
        }
        return preg_replace('{engine=\s*[^\s]+}i', 'ENGINE=InnoDB', $statement);
    }
}

class ClonerPrefixFilter implements ClonerImportFilter
{
    private $oldPrefix = '';
    private $newPrefix = '';
    private $regex = '';
    private $replacement = '';

    /**
     * @param string $oldPrefix
     * @param string $newPrefix
     */
    public function __construct($oldPrefix, $newPrefix)
    {
        $this->oldPrefix = $oldPrefix;
        $this->newPrefix = $newPrefix;
        // Create a regex that handles all these cases:
        //
        //  /*!40000 ALTER TABLE `wp_...` DISABLE KEYS */;
        //  CREATE TABLE `wp_...` (
        // 	LOCK TABLES `wp_...` WRITE;
        // 	DROP TABLE IF EXISTS `wp_...`;
        // 	ALTER TABLE `wp_...`
        // 	  ADD CONSTRAINT `some_constraint` FOREIGN KEY (...) REFERENCES `wp_...`
        // 	INSERT INTO `wp_...` VALUES (...);
        //
        // We also always go from the line start, as we really don't want any false positives.
        // The replacement pattern is:
        //  $1 `${newPrefix}$2`
        $this->regex       = sprintf("{^((?:\\/\\*!\\d+ )?(?:LOCK TABLES|DROP (?:TABLE|VIEW)(?: IF EXISTS)?|ALTER (?:TABLE|VIEW)|INSERT(?: IGNORE) INTO|CREATE (?:TABLE|VIEW)(?: IF NOT EXISTS)?|  (?:ADD )?CONSTRAINT `[^`]+` FOREIGN KEY \\([^\\)]+\\) REFERENCES)) `%s([^`]+)`}", $oldPrefix);
        $this->replacement = sprintf('$1 `%s$2`', $this->newPrefix);
    }

    public function filter($statement)
    {
        $return = @preg_replace($this->regex, $this->replacement, $statement);
        if ($return === null) {
            throw new ClonerException(preg_last_error());
        }
        return $return;
    }
}

/**
 * @param string               $root
 * @param ClonerDBAdapter      $conn
 * @param int                  $timeout
 * @param ClonerDBImportState  $state
 * @param int                  $maxCount
 * @param ClonerImportFilter[] $filters
 *
 * @return ClonerDBImportState New state.
 *
 * @throws ClonerException
 * @throws ClonerFSFunctionException
 */
function cloner_import_database($root, ClonerDBAdapter $conn, $timeout, ClonerDBImportState $state, $maxCount, array $filters)
{
    clearstatcache();
    $maxPacket = $realMaxPacket = 0;
    $firstRun  = true;
    foreach ($state->files as $file) {
        if ($file->processed > 0) {
            $firstRun = false;
            break;
        }
    }
    if ($firstRun) {
        try {
            cloner_kill_database_processlist($conn);
        } catch (ClonerException $e) {
            trigger_error($e->getMessage());
        }
    }
    if (is_array($maxPacketResult = $conn->query("SHOW VARIABLES LIKE 'max_allowed_packet'")->fetch())) {
        $maxPacket = $realMaxPacket = (int)end($maxPacketResult);
    }
    if (!$maxPacket) {
        $maxPacket = 128 << 10;
    } elseif ($maxPacket > 512 << 10) {
        $maxPacket = 512 << 10;
    }
    $deadline = new ClonerDeadline($timeout);
    $shifts   = 0;
    while (($dump = $state->next()) !== null) {
        $conn->useConnection($dump->source);
        if (strlen($dump->encoding)) {
            $conn->execute("SET NAMES {$dump->encoding}");
        }
        $stat = cloner_fs_stat("$root/$dump->path");
        if ($stat->getSize() !== $dump->size) {
            throw new ClonerException(sprintf("Inconsistent table dump file size, file %s transferred %d bytes, but on the disk it's %d bytes", $dump->path, $dump->size, $stat->getSize()), "different_size");
        }
        $scanner = new ClonerDBDumpScanner("$root/$dump->path");
        if ($dump->processed !== 0) {
            $scanner->seek($dump->processed);
        }
        $charsetFixer = new ClonerDBCharsetFixer($conn);
        while (strlen($statements = $scanner->scan($maxCount, $maxPacket))) {
            if ($realMaxPacket && strlen($statements) + 20 > $realMaxPacket) {
                throw new ClonerException(sprintf("A query in the backup (%d bytes) is too big for the SQL server to process (max %d bytes); please set the server's variable 'max_allowed_packet' to at least %d and retry the process", strlen($statements), $realMaxPacket, strlen($statements) + 20), 'db_max_packet_size_reached', strlen($statements));
            }
            if (preg_match('{^\s*(?:/\\*!\d+\s*)?set\s+(?:character_set_client\s*=|names\s+)}i', $statements)) {
                // Skip all the /*!40101 SET character_set_client=*** */; statements.
                continue;
            }
            try {
                $statements = cloner_filter_statement($statements, $filters);
                $conn->execute($statements);
                $shifts = 0;
                if (strncmp($statements, 'DROP TABLE IF EXISTS ', 21) === 0) {
                    $state->pushNextToEnd();
                    // We just dropped a table; switch to next file if available.
                    // This way we will drop all tables before importing new data.
                    // That helps with foreign key constraints.
                    break;
                }
            } catch (ClonerException $e) {
                // Super-powerful recovery switch, un-document it to secure your job.
                switch ($e->getInternalError()) {
                    case "1005": // SQLSTATE[HY000]: General error: 1005 Can't create table 'dbname.wp_wlm_email_queue' (errno: 150)
                        // This looks like an issue specific to InnoDB storage engine.
                    case "1451": // SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails
                        // For "DROP TABLE IF EXISTS..." queries. Sometimes they DO exist.
                    case "1217": // Cannot delete or update a parent row: a foreign key constraint fails
                        // @todo we could drop keys before dropping the database, but we would have to parse SQL :/
                    case "1146": // Table '%s' doesn't exist
                    case "1215": // Cannot add foreign key constraint
                        // Possible table reference error, we should suspend this import and go to next file.
                        // Push the currently imported file to end if and only if we're certain that the number of pushes
                        // without a successful statement execution doesn't exceed the number of files being imported;
                        // that would mean that we rotated all the files and would enter an infinite loop.
                        if ($shifts + 1 < count($state->files)) {
                            // Switch to next file.
                            $state->pushNextToEnd();
                            $scanner->close();
                            $shifts++;
                            continue 3;
                        }
                        throw new ClonerException(cloner_format_query_error($e->getMessage(), $statements, $dump->path, $dump->processed, $scanner->tell(), $dump->size), 'db_query_error', $e->getInternalError());
                    case "1115":
                    case "1273":
                        $newStatements = preg_replace_callback('{utf8mb4[a-z0-9_]*}', array($charsetFixer, 'replaceCharsetOrCollation'), $statements, -1, $count);
                        if ($count) {
                            try {
                                $conn->execute($newStatements);
                                break;
                            } catch (ClonerException $e2) {
                            }
                        }
                        throw new ClonerException(cloner_format_query_error($e->getMessage(), $statements, $dump->path, $dump->processed, $scanner->tell(), $dump->size), 'db_query_error', $e->getInternalError());
                    case "2013":
                        // 2013 Lost connection to MySQL server during query
                    case "2006":
                        // 2006 MySQL server has gone away
                    case "1153":
                        // SQLSTATE[08S01]: Communication link failure: 1153 Got a packet bigger than 'max_allowed_packet' bytes
                        $attempt     = 1;
                        $maxAttempts = 4;
                        while (++$attempt <= $maxAttempts) {
                            usleep(100000 * pow($attempt, 2));
                            try {
                                $conn->close();
                                if ($realMaxPacket && (strlen($statements) * 1.2) > $realMaxPacket) {
                                    // We are certain that the packet size is too big.
                                    $conn->execute(sprintf("SET GLOBAL max_allowed_packet=%d", strlen($statements) + 1024 * 1024));
                                }
                                $conn->execute($statements);
                                break 2;
                            } catch (Exception $e2) {
                                trigger_error(sprintf('Could not increase max_allowed_packet: %s for file %s at offset %d', $e2->getMessage(), $dump->path, $scanner->tell()));
                            }
                        }
                        // We aren't certain of what happened here. Maybe reconnect once?
                        throw new ClonerException(cloner_format_query_error($e->getMessage(), $statements, $dump->path, $dump->processed, $scanner->tell(), $dump->size), 'db_query_error', $e->getInternalError());
                    case "1231":
                        // Ignore errors like this:
                        // SQLSTATE[42000]: Syntax error or access violation: 1231 Variable 'character_set_client' can't be set to the value of 'NULL'
                        // We don't save the SQL variable state between imports since we only care about the relevant ones (encoding, timezone).
                        break;
                    //case 1065:
                    // Ignore error "[1065] Query was empty"
                    //  break;
                    case "1067": // SQLSTATE[42000]: Syntax error or access violation: 1067 Invalid default value for 'access_granted'
                        // Most probably NO_ZERO_DATE is ON and the default value is something like 0000-00-00.
                        $currentMode = $conn->query("SELECT @@sql_mode")->fetch();
                        $currentMode = @end($currentMode);
                        if (strlen($currentMode)) {
                            $modes       = explode(',', $currentMode);
                            $removeModes = array('NO_ZERO_DATE', 'NO_ZERO_IN_DATE');
                            foreach ($modes as $i => $mode) {
                                if (!in_array($mode, $removeModes)) {
                                    continue;
                                }
                                unset($modes[$i]);
                            }
                            $newMode = implode(',', $modes);
                            try {
                                $conn->execute("SET SESSION sql_mode = '$newMode'");
                                $conn->execute($statements);
                                // Recovered.
                                break;
                            } catch (Exception $e2) {
                                trigger_error($e2->getMessage());
                            }
                        }
                        throw new ClonerException(cloner_format_query_error($e->getMessage(), $statements, $dump->path, $dump->processed, $scanner->tell(), $dump->size), 'db_query_error', $e->getInternalError());
                    case "1064":
                        // MariaDB compatibility cases.
                        // This is regarding the PAGE_CHECKSUM property.
                    case "1286":
                        // ... and this is regarding the unknown storage engine, e.g.:
                        // CREATE TABLE `name` ( ... ) ENGINE=Aria  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci PAGE_CHECKSUM=1;
                        // results in
                        // SQLSTATE[42000]: Syntax error or access violation: 1286 Unknown storage engine 'Aria'
                        if (strpos($statements, 'PAGE_CHECKSUM') !== false) {
                            // MariaDB's CREATE TABLE statement has some options
                            // that MySQL doesn't recognize.
                            $conn->query(strtr($statements, array(
                                ' ENGINE=Aria '    => ' ENGINE=MyISAM ',
                                ' PAGE_CHECKSUM=1' => '',
                                ' PAGE_CHECKSUM=0' => '',
                            )));
                            break;
                        }
                        throw new ClonerException(cloner_format_query_error($e->getMessage(), $statements, $dump->path, $dump->processed, $scanner->tell(), $dump->size), 'db_query_error', $e->getInternalError());
                    case "1298":
                        // 1298 Unknown or incorrect time zone
                        break;
                    case "1419":
                        // Triggers require super-user permissions.
                        //
                        //   Query:
                        //   /*!50003 CREATE*/ /*!50003 TRIGGER wp_hmenu_mega_list BEFORE UPDATE ON wp_hmenu_mega_list FOR EACH ROW SET NEW.lastModified = NOW() */;
                        //
                        //   Error:
                        //   SQLSTATE[HY000]: General error: 1419 You do not have the SUPER privilege and binary logging is enabled (you *might* want to use the less safe log_bin_trust_function_creators variable)
                        $state->skipStatement($statements);
                        break;
                    case "1227":
                        if (strncmp($statements, 'SET @@SESSION.', 14) === 0 || strncmp($statements, 'SET @@GLOBAL.', 13) === 0) {
                            // SET @@SESSION.SQL_LOG_BIN= 0;
                            // SET @@GLOBAL.GTID_PURGED='';
                            break;
                        }
                        // Remove strings like DEFINER=`user`@`localhost`, because they generate errors like this:
                        // "[1227] Access denied; you need (at least one of) the SUPER privilege(s) for this operation"
                        // Example of a problematic query:
                        //
                        //  /*!50003 CREATE*/ /*!50017 DEFINER=`user`@`localhost`*/ /*!50003 TRIGGER `wp_hlogin_default_storage_table` BEFORE UPDATE ON `wp_hlogin_default_storage_table`
                        $newStatements = preg_replace('{(/\*!\d+) DEFINER=`[^`]+`@`[^`]+`(\*/ )}', '', $statements, 1, $count);
                        if ($count) {
                            try {
                                $conn->execute($newStatements);
                                break;
                            } catch (ClonerException $e) {
                            }
                        }
                        throw new ClonerException(cloner_format_query_error($e->getMessage(), $statements, $dump->path, $dump->processed, $scanner->tell(), $dump->size), 'db_query_error', $e->getInternalError());
                    case "3167":
                        if (strpos($statements, '@is_rocksdb_supported') !== false) {
                            // RocksDB support handling for the following case:
                            //
                            // /*!50112 SELECT COUNT(*) INTO @is_rocksdb_supported FROM INFORMATION_SCHEMA.SESSION_VARIABLES WHERE VARIABLE_NAME='rocksdb_bulk_load' */;
                            // /*!50112 SET @save_old_rocksdb_bulk_load = IF (@is_rocksdb_supported, 'SET @old_rocksdb_bulk_load = @@rocksdb_bulk_load', 'SET @dummy_old_rocksdb_bulk_load = 0') */;
                            // /*!50112 PREPARE s FROM @save_old_rocksdb_bulk_load */;
                            // /*!50112 EXECUTE s */;
                            // /*!50112 SET @enable_bulk_load = IF (@is_rocksdb_supported, 'SET SESSION rocksdb_bulk_load = 1', 'SET @dummy_rocksdb_bulk_load = 0') */;
                            // /*!50112 PREPARE s FROM @enable_bulk_load */;
                            // /*!50112 EXECUTE s */;
                            // /*!50112 DEALLOCATE PREPARE s */;
                            // ... table creation and insert statements ...
                            // /*!50112 SET @disable_bulk_load = IF (@is_rocksdb_supported, 'SET SESSION rocksdb_bulk_load = @old_rocksdb_bulk_load', 'SET @dummy_rocksdb_bulk_load = 0') */;
                            // /*!50112 PREPARE s FROM @disable_bulk_load */;
                            // /*!50112 EXECUTE s */;
                            // /*!50112 DEALLOCATE PREPARE s */;
                            //
                            // Error on the first statement:
                            //   #3167 - The 'INFORMATION_SCHEMA.SESSION_VARIABLES' feature is disabled; see the documentation for 'show_compatibility_56'
                            try {
                                $conn->execute('SET @is_rocksdb_supported = 0');
                            } catch (ClonerException $e2) {
                                throw new ClonerException(cloner_format_query_error('Could not recover from RocksDB support patch: '.$e2->getMessage(), $statements, $dump->path, $dump->processed, $scanner->tell(), $dump->size), 'db_query_error', $e2->getInternalError());
                            }
                            break;
                        }
                        throw new ClonerException(cloner_format_query_error($e->getMessage(), $statements, $dump->path, $dump->processed, $scanner->tell(), $dump->size), 'db_query_error', $e->getInternalError());
                    default:
                        throw new ClonerException(cloner_format_query_error($e->getMessage(), $statements, $dump->path, $dump->processed, $scanner->tell(), $dump->size), 'db_query_error', $e->getInternalError());
                }
            }
            $dump->processed = $scanner->tell();
            if ($deadline->done()) {
                // If there are any locked tables we might hang forever with the next query, unlock them.
                $conn->execute("UNLOCK TABLES");
                // We're cutting the import here - remember the encoding!!!
                $charset        = $conn->query("SHOW VARIABLES LIKE 'character_set_client'")->fetch();
                $dump->encoding = (string)end($charset);
                break 2;
            }
        }
        $dump->processed = $scanner->tell();
        $scanner->close();
    }

    return $state;
}

/**
 * @param string               $statement
 * @param ClonerImportFilter[] $filters
 *
 * @return string
 *
 * @throws ClonerException
 */
function cloner_filter_statement($statement, array $filters)
{
    foreach ($filters as $filter) {
        $statement = $filter->filter($statement);
    }
    return $statement;
}





class ClonerAction
{
    public $id = '';
    public $action = '';
    public $params;

    /**
     * @param string $name
     * @param mixed  $params
     *
     * @see cloner_execute_action
     */
    public function __construct($name, $params)
    {
        $this->id     = md5(uniqid('', true));
        $this->action = $name;
        $this->params = $params;
    }
}

/**
 * @param ClonerURL    $url
 * @param ClonerAction $action
 *
 * @return array
 *
 * @throws ClonerActionException
 */
function cloner_send_action(ClonerURL $url, ClonerAction $action)
{
    if (isset($_SERVER['HTTP_USER_AGENT']) && is_string($_SERVER['HTTP_USER_AGENT'])) {
        $ua = $_SERVER['HTTP_USER_AGENT'];
    } else {
        $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36';
    }
    $retried = false;
    while (true) {
        try {
            $payload = cloner_base64_rotate(json_encode($action));
            $req     = cloner_http_open_request('POST', $url, array(
                'Content-Type'    => 'application/json',
                'Content-Length'  => strlen($payload),
                'Connection'      => 'close',
                'Host'            => $url->getHTTPHost(),
                // Imitate a standard browser request.
                'User-Agent'      => $ua,
                'Referer'         => $url->__toString(),
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9,sr;q=0.8,bs;q=0.7',
            ), 10);
            socket_set_timeout($req, 10);
            if (@fwrite($req, $payload) === false) {
                throw new ClonerNetSocketException('fwrite', $req);
            }
            $res    = cloner_http_get_response_headers($req, 120);
            $result = null;
            $body   = $res->read(120);
            $offset = 0;
            while ($offset < strlen($body)) {
                $lineEnd = strpos($body, "\n", $offset);
                if ($lineEnd === false) {
                    $lineEnd = strlen($body);
                } else {
                    // Capture \n
                    $lineEnd++;
                }
                $line   = substr($body, $offset, $lineEnd - $offset);
                $offset += $lineEnd;
                if (strncmp('{"', $line, 2) !== 0) {
                    continue;
                }
                $result = json_decode($line, true);
                if (empty($result['id']) || $result['id'] !== $action->id) {
                    $result = null;
                    continue;
                }
                break;
            }
            @fclose($res->body);
            if (!isset($result)) {
                throw new ClonerActionResultNotFoundException($res->statusCode, $res->status, $res->headers, $body);
            }

            // See cloner_send_success_response/cloner_send_error_response for expected structure.
            if (isset($result['error']['error'], $result['error']['file'], $result['error']['line'])) {
                $message = sprintf('%s in %s:%d', $result['error']['message'], $result['error']['file'], $result['error']['line']);
                throw new ClonerRemoteErrorException($message, $result['error']['error'], $result['error']['internalError']);
            }
        } catch (ClonerException $e) {
            if (!$retried) {
                $retried = true;
                try {
                    cloner_http_do('GET', $url->__toString(), '', '', 20);
                } catch (Exception $e2) {
                    trigger_error('GET request after failed POST action failed: '.$e2->getMessage());
                    throw new ClonerActionException($action->action, $url->__toString(), $e);
                }
                // Retry initial request.
                continue;
            }
            throw new ClonerActionException($action->action, $url->__toString(), $e);
        }
        break;
    }
    /** @noinspection PhpUndefinedVariableInspection */
    return $result['result'];
}

class ClonerRemoteErrorException extends ClonerException
{
    public function __construct($message, $code, $internalError)
    {
        if (strlen($internalError)) {
            $internalError = '; internal error: '.$internalError;
        }
        parent::__construct(sprintf('error code %s: %s%s', $code, $message, $internalError), 'remote_fatal_error');
    }
}

class ClonerActionException extends ClonerException
{
    public $action = '';
    public $target = '';
    public $error;

    public function __construct($action, $target, Exception $exception)
    {
        $this->action = $action;
        $this->target = $target;
        $this->error  = $exception->getMessage();
        parent::__construct(sprintf('action %s->%s failed: %s', $target, $action, $exception->getMessage()), 'action_error');
    }
}

class ClonerActionResultNotFoundException extends ClonerException
{
    public $action = '';
    public $url = '';
    public $statusCode = 0;
    public $status = '';
    public $headers = array();
    public $body = '';

    /**
     * @param int    $statusCode
     * @param string $status
     * @param array  $headers
     * @param string $body
     */
    public function __construct($statusCode, $status, array $headers, $body)
    {
        $this->statusCode = $statusCode;
        $this->status     = $status;
        $this->headers    = $headers;
        $this->body       = $body;
        $excerpt          = trim(substr(preg_replace('{\s+}', ' ', strip_tags($body)), 0, 1024));
        $message          = sprintf('result not found, got status "%d %s"; excerpt: "%s"', $statusCode, $status, $excerpt);
        parent::__construct($message, 'reaction_not_found');
    }
}




class ClonerNetException extends ClonerException
{
}

/**
 * @param string $peerName
 * @param string $cert
 *
 * @return array
 * @throws ClonerNoTransportStreamsException
 * @throws ClonerFSFunctionException
 */
function cloner_tls_transport_self_signed($peerName, $cert)
{
    static $transport, $certPath;

    $available = stream_get_transports();
    $attempted = array('ssl', 'tls', 'tlsv1.2', 'tlsv1.1', 'tlsv1.0');
    if (!$transport) {
        foreach ($attempted as $attempt) {
            $index = array_search($attempt, $available);
            if ($index !== false) {
                $transport = $available[$index];
                break;
            }
        }
    }
    if (!$transport) {
        throw new ClonerNoTransportStreamsException($available, $attempted);
    }
    if (!$certPath) {
        $certHash = md5($cert);
        $tempPath = sys_get_temp_dir().'/cloner-cert-'.$certHash;
        if (!file_exists($tempPath) || @md5_file($tempPath) !== $certHash) {
            if (!file_put_contents($tempPath, $cert)) {
                throw new ClonerFSFunctionException('file_put_contents', $tempPath);
            }
        }
        $certPath = $tempPath;
    }

    // Temporarily disable SSL peer check.
    $ctx = stream_context_create(array('ssl' => array(
        'allow_self_signed' => true,
        'CN_match'          => $peerName,
        'verify_peer'       => true,
        'SNI_enabled'       => true,
        'SNI_server_name'   => $peerName,
        'peer_name'         => $peerName,
        'cafile'            => $certPath,
    )));
    return array($transport, $ctx);
}

/**
 * @param string $peerName Peer name to verify.
 *
 * @return array Transport stream to use and initialized context.
 *
 * @throws ClonerNoTransportStreamsException
 */
function cloner_tls_transport($peerName = '')
{
    static $transport;

    $available = stream_get_transports();
    $attempted = array('ssl', 'tls', 'tlsv1.2', 'tlsv1.1', 'tlsv1.0');
    foreach ($attempted as $attempt) {
        $index = array_search($attempt, $available);
        if ($index !== false) {
            $transport = $available[$index];
            break;
        }
    }
    if ($transport === null) {
        throw new ClonerNoTransportStreamsException($available, $attempted);
    }

    // Temporarily disable SSL peer check.
    $ctx = stream_context_create(array('ssl' => array(
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    )));
    return array($transport, $ctx);

    $cachedCertsPath = sys_get_temp_dir().'/managewp-worker-v2.crt';
    $tlsOptions      = array(
        'verify_peer'       => true,
        'verify_peer_name'  => true,
        'allow_self_signed' => false,
        // Attempt system's CAFILE.
    );
    if (is_file($cachedCertsPath)) {
        $tlsOptions['cafile'] = $cachedCertsPath;
    }
    if (strlen($peerName)) {
        if (PHP_VERSION_ID >= 50600) {
            $tlsOptions['peer_name'] = $peerName;
        } else {
            $tlsOptions['CN_match'] = $peerName;
        }
    }

    $ctx = stream_context_create(array('ssl' => $tlsOptions));

    if ($transport !== null) {
        return array($transport, $ctx);
    }
}

/**
 * Certificates used to fetch latest certificates from https://curl.haxx.se/ca/cacert.pem
 * when the system is missing them.
 *
 * @return resource
 * @throws Exception
 */
function cloner_tls_transport_context_curl()
{
    // Respectively:
    // - From curl.haxx.se:
    //   /C=US/ST=California/L=San Francisco/O=Fastly, Inc./CN=c.sni.fastly.net
    //   /C=BE/O=GlobalSign nv-sa/CN=GlobalSign Organization Validation CA - SHA256 - G2
    // - From the cacert.pem itself:
    //   /CN=GlobalSign Root CA
    $certs = <<<CRT
-----BEGIN CERTIFICATE-----
MIIFOzCCBCOgAwIBAgIMO4pgQymgER+m0k6OMA0GCSqGSIb3DQEBCwUAMGYxCzAJ
BgNVBAYTAkJFMRkwFwYDVQQKExBHbG9iYWxTaWduIG52LXNhMTwwOgYDVQQDEzNH
bG9iYWxTaWduIE9yZ2FuaXphdGlvbiBWYWxpZGF0aW9uIENBIC0gU0hBMjU2IC0g
RzIwHhcNMTcwMjA3MjI0MTA0WhcNMTkwMjA4MjI0MTA0WjBsMQswCQYDVQQGEwJV
UzETMBEGA1UECBMKQ2FsaWZvcm5pYTEWMBQGA1UEBxMNU2FuIEZyYW5jaXNjbzEV
MBMGA1UEChMMRmFzdGx5LCBJbmMuMRkwFwYDVQQDExBjLnNuaS5mYXN0bHkubmV0
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApbUevfFREAfvUH18oW27
BVLbkWJnbZ69dQCCchcuaXJ8Jq/I6plgKwW2yWUG/ynp7dp+0BwoWnzbiQHqZTsW
6Pqf0le2uENc8sSxLyILATG2Ct/s36XxXNfuH8388uOfiVvwoEAoDBD1VEXcI/4r
ei2KGwVx8PGLb60jitLDPLYOXW/kMu+WNg/+btjJ4khs30UeHh10UUrXjuRO3iga
6hOgKpvbkX03nfHH/+zc+sDfJerH0bmTwvZLwWupRW5x65hx2O2voUVbb27nnbqZ
zR57FZATEHyvqQghHsHjI8bwBI1azDuCz7vXIIKyYkXNO1eRrXzT46Tx7mHtanOL
bwIDAQABo4IB4TCCAd0wDgYDVR0PAQH/BAQDAgWgMIGgBggrBgEFBQcBAQSBkzCB
kDBNBggrBgEFBQcwAoZBaHR0cDovL3NlY3VyZS5nbG9iYWxzaWduLmNvbS9jYWNl
cnQvZ3Nvcmdhbml6YXRpb252YWxzaGEyZzJyMS5jcnQwPwYIKwYBBQUHMAGGM2h0
dHA6Ly9vY3NwMi5nbG9iYWxzaWduLmNvbS9nc29yZ2FuaXphdGlvbnZhbHNoYTJn
MjBWBgNVHSAETzBNMEEGCSsGAQQBoDIBFDA0MDIGCCsGAQUFBwIBFiZodHRwczov
L3d3dy5nbG9iYWxzaWduLmNvbS9yZXBvc2l0b3J5LzAIBgZngQwBAgIwCQYDVR0T
BAIwADBJBgNVHR8EQjBAMD6gPKA6hjhodHRwOi8vY3JsLmdsb2JhbHNpZ24uY29t
L2dzL2dzb3JnYW5pemF0aW9udmFsc2hhMmcyLmNybDAbBgNVHREEFDASghBjLnNu
aS5mYXN0bHkubmV0MB0GA1UdJQQWMBQGCCsGAQUFBwMBBggrBgEFBQcDAjAdBgNV
HQ4EFgQUtKi05Nur72AEab/ueagsP+smrGAwHwYDVR0jBBgwFoAUlt5h8b0cFilT
HMDMfTuDAEDmGnwwDQYJKoZIhvcNAQELBQADggEBAIO4QcnKsWyMvfjZj4QMg1ao
31XuY7jRiG2/a+S39JYEIS+16GXiTRfKJMk5dNKK30kRU+uPxBal5HS/i43ZRmY2
0iQG/tMLoVoTPUzxbgiIvgFIvjNG6vefiza+C83AY1Vz8HOcAAE3AM7efqYo0XdV
xlvOkdinqGDwERkZyKQ4mIDqEeU6wPHLTKf+wLnqcYxyeA4DK6Cd7v0NHMBm02L2
ZMf8iW1OZSy+uKswqSIedmmyko/tuO6gNA7Zs/pS5rjs6VH0OE6TlMIxvH/w0dj7
n8F/e1mhjp73CMV77MAIyxnnorM/Z58reWF/VGgOU89y4OdUugHIZ4F7fDTfpTU=
-----END CERTIFICATE-----
-----BEGIN CERTIFICATE-----
MIIEaTCCA1GgAwIBAgILBAAAAAABRE7wQkcwDQYJKoZIhvcNAQELBQAwVzELMAkG
A1UEBhMCQkUxGTAXBgNVBAoTEEdsb2JhbFNpZ24gbnYtc2ExEDAOBgNVBAsTB1Jv
b3QgQ0ExGzAZBgNVBAMTEkdsb2JhbFNpZ24gUm9vdCBDQTAeFw0xNDAyMjAxMDAw
MDBaFw0yNDAyMjAxMDAwMDBaMGYxCzAJBgNVBAYTAkJFMRkwFwYDVQQKExBHbG9i
YWxTaWduIG52LXNhMTwwOgYDVQQDEzNHbG9iYWxTaWduIE9yZ2FuaXphdGlvbiBW
YWxpZGF0aW9uIENBIC0gU0hBMjU2IC0gRzIwggEiMA0GCSqGSIb3DQEBAQUAA4IB
DwAwggEKAoIBAQDHDmw/I5N/zHClnSDDDlM/fsBOwphJykfVI+8DNIV0yKMCLkZc
C33JiJ1Pi/D4nGyMVTXbv/Kz6vvjVudKRtkTIso21ZvBqOOWQ5PyDLzm+ebomchj
SHh/VzZpGhkdWtHUfcKc1H/hgBKueuqI6lfYygoKOhJJomIZeg0k9zfrtHOSewUj
mxK1zusp36QUArkBpdSmnENkiN74fv7j9R7l/tyjqORmMdlMJekYuYlZCa7pnRxt
Nw9KHjUgKOKv1CGLAcRFrW4rY6uSa2EKTSDtc7p8zv4WtdufgPDWi2zZCHlKT3hl
2pK8vjX5s8T5J4BO/5ZS5gIg4Qdz6V0rvbLxAgMBAAGjggElMIIBITAOBgNVHQ8B
Af8EBAMCAQYwEgYDVR0TAQH/BAgwBgEB/wIBADAdBgNVHQ4EFgQUlt5h8b0cFilT
HMDMfTuDAEDmGnwwRwYDVR0gBEAwPjA8BgRVHSAAMDQwMgYIKwYBBQUHAgEWJmh0
dHBzOi8vd3d3Lmdsb2JhbHNpZ24uY29tL3JlcG9zaXRvcnkvMDMGA1UdHwQsMCow
KKAmoCSGImh0dHA6Ly9jcmwuZ2xvYmFsc2lnbi5uZXQvcm9vdC5jcmwwPQYIKwYB
BQUHAQEEMTAvMC0GCCsGAQUFBzABhiFodHRwOi8vb2NzcC5nbG9iYWxzaWduLmNv
bS9yb290cjEwHwYDVR0jBBgwFoAUYHtmGkUNl8qJUC99BM00qP/8/UswDQYJKoZI
hvcNAQELBQADggEBAEYq7l69rgFgNzERhnF0tkZJyBAW/i9iIxerH4f4gu3K3w4s
32R1juUYcqeMOovJrKV3UPfvnqTgoI8UV6MqX+x+bRDmuo2wCId2Dkyy2VG7EQLy
XN0cvfNVlg/UBsD84iOKJHDTu/B5GqdhcIOKrwbFINihY9Bsrk8y1658GEV1BSl3
30JAZGSGvip2CTFvHST0mdCF/vIhCPnG9vHQWe3WVjwIKANnuvD58ZAWR65n5ryA
SOlCdjSXVWkkDoPWoC209fN5ikkodBpBocLTJIg1MGCUF7ThBCIxPTsvFwayuJ2G
K1pp74P1S8SqtCr4fKGxhZSM9AyHDPSsQPhZSZg=
-----END CERTIFICATE-----
-----BEGIN CERTIFICATE-----
MIIDdTCCAl2gAwIBAgILBAAAAAABFUtaw5QwDQYJKoZIhvcNAQEFBQAwVzELMAkG
A1UEBhMCQkUxGTAXBgNVBAoTEEdsb2JhbFNpZ24gbnYtc2ExEDAOBgNVBAsTB1Jv
b3QgQ0ExGzAZBgNVBAMTEkdsb2JhbFNpZ24gUm9vdCBDQTAeFw05ODA5MDExMjAw
MDBaFw0yODAxMjgxMjAwMDBaMFcxCzAJBgNVBAYTAkJFMRkwFwYDVQQKExBHbG9i
YWxTaWduIG52LXNhMRAwDgYDVQQLEwdSb290IENBMRswGQYDVQQDExJHbG9iYWxT
aWduIFJvb3QgQ0EwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDaDuaZ
jc6j40+Kfvvxi4Mla+pIH/EqsLmVEQS98GPR4mdmzxzdzxtIK+6NiY6arymAZavp
xy0Sy6scTHAHoT0KMM0VjU/43dSMUBUc71DuxC73/OlS8pF94G3VNTCOXkNz8kHp
1Wrjsok6Vjk4bwY8iGlbKk3Fp1S4bInMm/k8yuX9ifUSPJJ4ltbcdG6TRGHRjcdG
snUOhugZitVtbNV4FpWi6cgKOOvyJBNPc1STE4U6G7weNLWLBYy5d4ux2x8gkasJ
U26Qzns3dLlwR5EiUWMWea6xrkEmCMgZK9FGqkjWZCrXgzT/LCrBbBlDSgeF59N8
9iFo7+ryUp9/k5DPAgMBAAGjQjBAMA4GA1UdDwEB/wQEAwIBBjAPBgNVHRMBAf8E
BTADAQH/MB0GA1UdDgQWBBRge2YaRQ2XyolQL30EzTSo//z9SzANBgkqhkiG9w0B
AQUFAAOCAQEA1nPnfE920I2/7LqivjTFKDK1fPxsnCwrvQmeU79rXqoRSLblCKOz
yj1hTdNGCbM+w6DjY1Ub8rrvrTnhQ7k4o+YviiY776BQVvnGCv04zcQLcFGUl5gE
38NflNUVyRRBnMRddWQVDf9VMOyGj/8N7yy5Y0b2qvzfvGn9LhJIZJrglfCm7ymP
AbEVtQwdpf5pLGkkeB6zpxxxYu7KyJesF12KwvhHhm4qxFYxldBniYUr+WymXUad
DKqC5JlR3XC321Y9YeRq4VzW9v493kHMB65jUr9TU/Qr6cf9tveCX4XSQRjbgbME
HMUfpIBvFSDJ3gyICh3WZlXi/EjJKSZp4A==
-----END CERTIFICATE-----
CRT;

    $certsPath = sys_get_temp_dir().'/managewp-curl.crt';
    if (@filesize($certsPath) !== strlen($certs)) {
        if (@file_put_contents($certsPath, $certs) === false) {
            throw new ClonerFSFunctionException('file_put_contents', $certsPath);
        }
    }

    return stream_context_create(array(
        'ssl' => array(
            'verify_peer'       => true,
            'verify_peer_name'  => true,
            'allow_self_signed' => false,
            'cafile'            => $certsPath,
        ),
    ));
}

/**
 * Certificates used to contact managewp.com, godaddy.com, managewp.test
 *
 * @param string $peerName Peer name to verify.
 *
 * @return resource
 * @throws ClonerFSFunctionException
 * @throws ClonerNetException
 * @throws ClonerURLException
 */
function cloner_tls_transport_context_fallback($peerName = '')
{
    $certsPath = sys_get_temp_dir().'/managewp-worker-v2.crt';
    if (!file_exists($certsPath)) {
        $certs = cloner_http_do('GET', ClonerURL::fromString('https://curl.haxx.se/ca/cacert.pem'));

        // Append managewp.test certificate:
        //   /C=RS/ST=Serbia/L=Belgrade/O=GoDaddy LLC/OU=ManageWP/CN=managewp.test/emailAddress=devops@managewp.test
        $certs .= <<<CRT

-----BEGIN CERTIFICATE-----
MIIDrDCCApQCCQD3rCnOu1cdeTANBgkqhkiG9w0BAQUFADCBlzELMAkGA1UEBhMC
UlMxDzANBgNVBAgMBlNlcmJpYTERMA8GA1UEBwwIQmVsZ3JhZGUxFDASBgNVBAoM
C0dvRGFkZHkgTExDMREwDwYDVQQLDAhNYW5hZ2VXUDEWMBQGA1UEAwwNbWFuYWdl
d3AudGVzdDEjMCEGCSqGSIb3DQEJARYUZGV2b3BzQG1hbmFnZXdwLnRlc3QwHhcN
MTgwMTA5MDk1NjI4WhcNMjgwMTA3MDk1NjI4WjCBlzELMAkGA1UEBhMCUlMxDzAN
BgNVBAgMBlNlcmJpYTERMA8GA1UEBwwIQmVsZ3JhZGUxFDASBgNVBAoMC0dvRGFk
ZHkgTExDMREwDwYDVQQLDAhNYW5hZ2VXUDEWMBQGA1UEAwwNbWFuYWdld3AudGVz
dDEjMCEGCSqGSIb3DQEJARYUZGV2b3BzQG1hbmFnZXdwLnRlc3QwggEiMA0GCSqG
SIb3DQEBAQUAA4IBDwAwggEKAoIBAQDj8dWERZXoFV2uzQodgAwj5yCfR6fK6gAU
hc86TYHyFIBAqq5GEsUW48svmjKAlg2PydTu5/Uld1Q73VYR3eX5dDxRGwIVwfnI
TdCsEmseCFidr24BLZzdxO3cc0m/iGGLlcQSF47d4kD9Qcu6F+hzkv4zTRSH6aY+
kSD5i1aIzapUiQOroD5sfQZP1fe1N0CLuqKvpT5LDPqnz6/RaItqmsJL6sZaS01d
wrBNLvU3M4flZzkILJ7t97Xamdwjr9qzyEJZTaSKBR7dhy5kHa8jZoJzvm2ym02j
SvmyXI9og7v63PjRCYQOZdnohR8/y/aDX1nyuRnSNOGB+Y2dwXrXAgMBAAEwDQYJ
KoZIhvcNAQEFBQADggEBAAqDHAUZXgYci3h9sUNwDcTnHPEWmcY+oC+vBnZBWhhM
ZAYR1nRCf70GZBJ3hLzepN8cGCkE6EZQoDS7uT57F1/A8mDcHbYjOu1CwLSzwyKT
U20WYLTcgp+unegAqQTDGw92sFohj7UFxU1n+jO1ygKENiUp3KVcgbjgFZqAbv4B
gELCoRGJRBPBjwCrDXMCS8pfIQNSTWMByj03W4ZXDk6SDPWUhTcGxlfvpdampMI9
Fi3CNNkU3AdKj4uuNxE8ymTpoDFmI35FY4lleQE71VZhoAH/wg0r8aXMEuOhB6j6
t3/3q0NiQH8BiH+ZXxHTPLc7hRfwOiv/wkIU2ZmqDkA=
-----END CERTIFICATE-----

CRT;
        if (@file_put_contents($certsPath, $certs) === false) {
            throw new ClonerFSFunctionException('file_put_contents', $certsPath);
        }
    }

    $tlsOptions = array(
        'verify_peer'       => true,
        'verify_peer_name'  => true,
        'allow_self_signed' => false,
        'cafile'            => $certsPath,
    );
    if (strlen($peerName)) {
        if (PHP_VERSION_ID >= 50600) {
            $tlsOptions['peer_name'] = $peerName;
        } else {
            $tlsOptions['CN_match'] = $peerName;
        }
    }
    return stream_context_create(array(
        'ssl' => $tlsOptions,
    ));
}

class ClonerNetSocketException extends ClonerNetException
{
    public $fn = '';
    public $error = '';
    public $timeout = false;
    public $eof = false;

    /**
     * @param string   $fn
     * @param resource $sock
     */
    public function __construct($fn, $sock)
    {
        $this->fn    = $fn;
        $this->error = cloner_last_error_for($fn);
        $meta        = @stream_get_meta_data($sock);
        if ($meta !== false) {
            $this->timeout = $meta['timed_out'];
            $this->eof     = $meta['eof'];
        }
        if ($this->timeout) {
            parent::__construct(sprintf('%s socket timeout: %s', $fn, $this->error));
            return;
        } elseif ($this->eof) {
            parent::__construct(sprintf('%s socket eof: %s', $fn, $this->error));
            return;
        }
        parent::__construct(sprintf('%s socket error: %s', $fn, $this->error));
    }
}

class ClonerClonerNetFunctionException extends ClonerNetException
{
    public $fn = '';
    public $host = '';
    public $error = '';

    /**
     * @param string      $fn    One of stream_socket_client, fread (on socket), etc.
     * @param string      $host  Remote host address.
     * @param string|null $error Error message, will automatically fetch from error_get_last() if null.
     */
    public function __construct($fn, $host, $error = null)
    {
        $this->fn   = $fn;
        $this->host = $host;
        if ($error === null) {
            $error = cloner_last_error_for($fn);
        }
        $this->error = $error;
        parent::__construct(sprintf('%s error for host %s: %s', $fn, $host, $this->error));
    }
}

class ClonerSocketClientException extends ClonerNetException
{
    public $fn = 'stream_socket_client';
    public $transport = '';
    public $host = '';
    public $error = '';
    public $errno = 0;
    public $errstr = '';

    /**
     * @param string $transport
     * @param string $host
     * @param int    $errno
     * @param string $errstr
     */
    public function __construct($transport, $host, $errno, $errstr)
    {
        $this->host   = $host;
        $this->error  = cloner_last_error_for($this->fn);
        $this->errno  = $errno;
        $this->errstr = $errstr;
        parent::__construct(sprintf('%s error for host %s://%s: %s; errno: %d; errstr: %s', $this->fn, $transport, $host, $this->error, $errno, $errstr));
    }
}

/**
 * @param string $host
 * @param int    $timeout
 * @param bool   $secure
 * @param string $peerName Peer name to verify when $tls is true.
 * @param string $cert     Certificate to use when $tls is true.
 *
 * @return resource Stream socket resources ready for response reading.
 *
 * @throws ClonerSocketClientException
 * @throws ClonerNoTransportStreamsException
 * @throws ClonerFSFunctionException
 */
function cloner_tcp_socket_dial($host, $timeout = 10, $secure = false, $peerName = '', $cert = '')
{
    $transport = 'tcp';
    // Null is not allowed in stream_socket_client.
    $ctx = stream_context_create();
    if ($secure) {
        if (strlen($cert)) {
            list($transport, $ctx) = cloner_tls_transport_self_signed($peerName, $cert);
        } else {
            list($transport, $ctx) = cloner_tls_transport($peerName);
        }
    }
    $fallback = false;
    while (true) {
        $sock = @stream_socket_client($transport.'://'.$host, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
        if ($sock === false) {
            $e = new ClonerSocketClientException($transport, $host, $errno, $errstr);
            // Temporarily disable SSL peer check.
            if (false && $errno === 0 && $secure && !$fallback) {
                try {
                    $ctx      = ($host === 'curl.haxx.se:443') ? cloner_tls_transport_context_curl() : cloner_tls_transport_context_fallback($peerName);
                    $fallback = true;
                    continue;
                } catch (Exception $e2) {
                    trigger_error(sprintf('Fallback TLS context error: %s', $e2->getMessage()));
                }
            }
            throw $e;
        }
        break;
    }
    return $sock;
}

/**
 * @param resource $sock
 *
 * @return bool
 */
function cloner_socket_is_timeout($sock)
{
    $data = @stream_get_meta_data($sock);
    return !empty($data['timed_out']);
}

function cloner_socket_is_eof($sock)
{
    $data = @stream_get_meta_data($sock);
    if ($data === false) {
        return false;
    }
    return empty($data['unread_bytes']) && !empty($data['eof']);
}

/** @noinspection PhpDeprecationInspection */



class ClonerMySQLStmt implements ClonerDBStmt
{
    private $result;

    private $numRows = null;

    /**
     * @param resource|null $result
     * @param int|null      $numRows
     *
     * @throws ClonerException
     */
    public function __construct($result = null, $numRows = null)
    {
        if ($result === null && $numRows === null) {
            throw new ClonerException("Either MySQL result or number of affected rows must be provided.", 'db_query_error');
        }
        $this->result  = $result;
        $this->numRows = $numRows;
    }

    public function fetch()
    {
        if (!is_resource($this->result)) {
            throw new ClonerException("Only read-only queries can yield results.", 'db_query_error');
        }
        $row = @mysql_fetch_assoc($this->result);
        if ($row === false) {
            return null;
        } else {
            return $row;
        }
    }

    public function fetchAll()
    {
        if (!is_resource($this->result)) {
            throw new ClonerException("Only read-only queries can yield results.", 'db_query_error');
        }
        $rows = array();
        while ($row = $this->fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getNumRows()
    {
        if ($this->numRows !== null) {
            return $this->numRows;
        }
        return mysql_num_rows($this->result);
    }

    public function free()
    {
        if (!is_resource($this->result)) {
            return true;
        }
        return mysql_free_result($this->result);
    }
}

class ClonerMySQLConn implements ClonerDBConn
{
    private $conn;

    /**
     * @param ClonerDBInfo $conf
     *
     * @throws ClonerException
     */
    public function __construct(ClonerDBInfo $conf)
    {
        if (!extension_loaded('mysql')) {
            throw new ClonerException("Mysql extension is not loaded.", 'mysql_disabled');
        }

        $this->conn = @mysql_connect($conf->host, $conf->user, $conf->password);
        if (!is_resource($this->conn)) {
            // Attempt to recover from "[2002] No such file or directory" error.
            $errno = mysql_errno();
            if ($errno !== 2002 || strtolower($conf->getHostname()) !== 'localhost' || !is_resource($this->conn = @mysql_connect('127.0.0.1', $conf->user, $conf->password))) {
                throw new ClonerException(mysql_error(), 'db_connect_error', (string)$errno);
            }
        }
        if (mysql_select_db($conf->name, $this->conn) === false) {
            throw new ClonerException(mysql_error($this->conn), 'db_connect_error', (string)mysql_errno($this->conn));
        }
        if (!@mysql_set_charset(cloner_db_charset($this), $this->conn)) {
            throw new ClonerException(mysql_error($this->conn), 'db_connect_error', (string)mysql_errno($this->conn));
        }
    }

    public function query($query, array $parameters = array(), $unbuffered = false)
    {
        $query = cloner_bind_query_params($this, $query, $parameters);

        if ($unbuffered) {
            $result = mysql_unbuffered_query($query, $this->conn);
        } else {
            $result = mysql_query($query, $this->conn);
        }

        if ($result === false) {
            throw new ClonerException(mysql_error($this->conn), 'db_query_error', (string)mysql_errno($this->conn));
        } elseif ($result === true) {
            // This is one of INSERT, UPDATE, DELETE, DROP statements.
            return new ClonerMySQLStmt(null, mysql_affected_rows($this->conn));
        } else {
            // This is one of SELECT, SHOW, DESCRIBE, EXPLAIN statements.
            return new ClonerMySQLStmt($result);
        }
    }

    public function execute($query)
    {
        $this->query($query);
    }

    public function escape($value)
    {
        return $value === null ? 'null' : "'".mysql_real_escape_string($value, $this->conn)."'";
    }

    public function close()
    {
        if (empty($this->conn)) {
            return;
        }
        mysql_close($this->conn);
        $this->conn = null;
    }
}




class ClonerMySQLiStmt implements ClonerDBStmt
{
    private $result;

    /**
     * @param mysqli_result|bool $result
     */
    public function __construct($result)
    {
        $this->result = $result;
    }

    /**
     * @return array|null
     */
    public function fetch()
    {
        if (is_bool($this->result)) {
            return null;
        }
        return $this->result->fetch_assoc();
    }

    /**
     * @return array|null
     */
    public function fetchAll()
    {
        if (is_bool($this->result)) {
            return null;
        }
        $rows = array();
        while ($row = $this->fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @return int
     */
    public function getNumRows()
    {
        if (is_bool($this->result)) {
            return 0;
        }
        return $this->result->num_rows;
    }

    /**
     * @return bool
     */
    public function free()
    {
        if (is_bool($this->result)) {
            return false;
        }
        mysqli_free_result($this->result);
        return true;
    }
}

class ClonerMySQLiConn implements ClonerDBConn
{
    private $conn;

    /**
     * @param ClonerDBInfo $conf
     *
     * @throws ClonerException
     */
    public function __construct(ClonerDBInfo $conf)
    {
        if (!extension_loaded('mysqli')) {
            throw new ClonerException("Mysqli extension is not enabled.", 'mysqli_disabled');
        }

        // Silence possible warnings thrown by mysqli
        // e.g. Warning: mysqli::mysqli(): Headers and client library minor version mismatch. Headers:50540 Library:50623
        $this->conn = @new mysqli($conf->getHostname(), $conf->user, $conf->password, $conf->name, $conf->getPort());

        if ($this->conn->connect_errno === 2002 && strtolower($conf->getHost()) === 'localhost') {
            // Attempt to recover from "[2002] No such file or directory" error.
            $this->conn = @new mysqli('127.0.0.1', $conf->getUsername(), $conf->getPassword(), $conf->getDatabase(), $conf->getPort());
        }
        if (!$this->conn->ping()) {
            throw new ClonerException($this->conn->connect_error, 'db_connect_error', $this->conn->connect_errno);
        }
        $this->conn->set_charset(cloner_db_charset($this));
    }

    public function query($query, array $parameters = array(), $unbuffered = false)
    {
        $query = cloner_bind_query_params($this, $query, $parameters);

        $resultMode = $unbuffered ? MYSQLI_USE_RESULT : 0;
        $result     = $this->conn->query($query, $resultMode);

        // There are certain warnings that result in $result being false, eg. PHP Warning:  mysqli::query(): Empty query,
        // but the error number is 0.
        if ($result === false && $this->conn->errno !== 0) {
            throw new ClonerException($this->conn->error, 'db_query_error', $this->conn->errno);
        }

        return new ClonerMySQLiStmt($result);
    }

    public function execute($query)
    {
        $this->query($query);
    }

    public function escape($value)
    {
        return $value === null ? 'null' : "'".$this->conn->real_escape_string($value)."'";
    }

    public function close()
    {
        if (empty($this->conn)) {
            return;
        }
        $this->conn->close();
        $this->conn = null;
    }
}




class ClonerPDOStmt implements ClonerDBStmt
{
    private $statement;

    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function fetch()
    {
        return $this->statement->fetch();
    }

    public function fetchAll()
    {
        return $this->statement->fetchAll();
    }

    public function getNumRows()
    {
        return $this->statement->rowCount();
    }

    public function free()
    {
        return $this->statement->closeCursor();
    }
}

class ClonerPDOConn implements ClonerDBConn
{
    /**
     * @param bool $attEmulatePrepares
     */
    public function setAttEmulatePrepares($attEmulatePrepares)
    {
        $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, $attEmulatePrepares);
    }

    private $conn;
    private $unbuffered = false;

    /**
     * @param ClonerDBInfo $conf
     *
     * @throws ClonerException
     */
    public function __construct(ClonerDBInfo $conf)
    {
        $options = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        );
        try {
            $this->conn = new PDO(self::getDsn($conf), $conf->user, $conf->password, $options);
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 2002 && strtolower($conf->getHostname()) === 'localhost') {
                try {
                    $conf       = clone $conf;
                    $conf->host = '127.0.0.1';
                    $this->conn = new PDO(self::getDsn($conf), $conf->user, $conf->password, $options);
                } catch (PDOException $e2) {
                    throw new ClonerException($e->getMessage(), 'db_connect_error', (string)$e2->getCode());
                }
            } else {
                throw new ClonerException($e->getMessage(), 'db_connect_error', (string)$e->getCode());
            }
        }
        $this->conn->exec(sprintf('SET NAMES %s', cloner_db_charset($this)));
    }

    public function query($query, array $parameters = array(), $unbuffered = false)
    {
        if ($this->unbuffered !== $unbuffered) {
            $this->unbuffered = $unbuffered;
            $this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, !$unbuffered);
        }

        try {
            $statement = $this->conn->prepare($query);
            $statement->execute($parameters);
            return new ClonerPDOStmt($statement);
        } catch (PDOException $e) {
            $internalErrorCode = isset($e->errorInfo[1]) ? (string)$e->errorInfo[1] : '';
            throw new ClonerException($e->getMessage(), 'db_query_error', $internalErrorCode);
        }
    }

    public function execute($query)
    {
        try {
            $this->conn->exec($query);
        } catch (PDOException $e) {
            $internalErrorCode = isset($e->errorInfo[1]) ? (string)$e->errorInfo[1] : '';
            throw new ClonerException($e->getMessage(), 'db_query_error', $internalErrorCode);
        }
    }

    public function escape($value)
    {
        return $value === null ? 'null' : $this->conn->quote($value);
    }

    public function close()
    {
        $this->conn = null;
    }

    public static function getDsn(ClonerDBInfo $conf)
    {
        $pdoParameters = array(
            'dbname'  => $conf->name,
            'charset' => 'utf8',
        );
        $socket        = $conf->getSocket();
        if ($socket !== '') {
            $pdoParameters['host']        = $conf->getHostname();
            $pdoParameters['unix_socket'] = $socket;
        } else {
            $pdoParameters['host'] = $conf->getHostname();
            $pdoParameters['port'] = $conf->getPort();
        }
        $parameters = array();
        foreach ($pdoParameters as $name => $value) {
            $parameters[] = $name.'='.$value;
        }
        $dsn = sprintf('mysql:%s', implode(';', $parameters));
        return $dsn;
    }
}


class ClonerDBInfo
{
    public $user = '';
    public $password = '';
    /** @var string https://codex.wordpress.org/Editing_wp-config.php#Possible_DB_HOST_values */
    public $host = '';
    public $name = '';

    public function __construct($user, $password, $host, $name)
    {
        $this->user     = $user;
        $this->password = $password;
        $this->host     = $host;
        $this->name     = $name;
    }

    public static function fromArray($info)
    {
        if (empty($info)) {
            return self::createEmpty();
        } elseif ($info instanceof self) {
            return $info;
        }
        return new self($info['dbUser'], $info['dbPassword'], $info['dbHost'], $info['dbName']);
    }

    public function getHostname()
    {
        $parts = explode(':', $this->host, 2);
        if ($parts[0] === '') {
            return 'localhost';
        }
        return $parts[0];
    }

    public function getPort()
    {
        if (strpos($this->host, '/') !== false) {
            return 0;
        }
        $parts = explode(':', $this->host, 2);
        if (count($parts) === 2) {
            return (int)$parts[1];
        }
        return 0;
    }

    public function getSocket()
    {
        if (strpos($this->host, '/') === false) {
            return '';
        }
        $parts = explode(':', $this->host, 2);
        if (count($parts) === 2) {
            return $parts[1];
        }
        return '';
    }

    public static function createEmpty()
    {
        return new self('', '', '', '');
    }

    public function toArray()
    {
        return array(
            'dbUser'     => $this->user,
            'dbPassword' => $this->password,
            'dbName'     => $this->name,
            'dbHost'     => $this->host,
        );
    }

    public function getID()
    {
        if (strlen($this->host) === 0 && strlen($this->name) === 0) {
            return '';
        }
        return $this->host.'/'.$this->name;
    }
}







class ClonerEnvInfo
{
    public $goDaddyPro = 0;
    public $openshift = false;
    public $flywheel = false;
    public $phpVersionID = 0;

    /**
     * @param int  $goDaddyPro
     * @param bool $openshift
     * @param bool $flywheel
     * @param int  $phpVersionID
     */
    public function __construct($goDaddyPro, $openshift, $flywheel, $phpVersionID)
    {
        $this->goDaddyPro   = $goDaddyPro;
        $this->openshift    = $openshift;
        $this->flywheel     = $flywheel;
        $this->phpVersionID = $phpVersionID;
    }

    public function toArray()
    {
        return array(
            'goDaddyPro'   => $this->goDaddyPro,
            'openshift'    => $this->openshift,
            'flywheel'     => $this->flywheel,
            'phpVersionID' => $this->phpVersionID,
        );
    }
}

/**
 * @param string $root
 *
 * @return ClonerEnvInfo Environment info that can be reused during the request scope.
 */
function cloner_env_info($root)
{
    $gdVersion = 0;
    if (isset($_SERVER['GD_PHP_HANDLER'])) {
        $gdVersion = 1;
    } elseif (isset($_SERVER['MWP2_VERSION_ID'])) {
        $gdVersion = 2;
    }

    $openshift = (bool)strlen(getenv('OPENSHIFT_APP_UUID'));

    $fwConfig = "$root/.fw-config.php";
    $flywheel = is_file($fwConfig) && fileowner($fwConfig) === 0 && filegroup($fwConfig) === 0;

    $phpVersionID = defined('PHP_VERSION_ID') ? PHP_VERSION_ID : 0;

    return new ClonerEnvInfo($gdVersion, $openshift, $flywheel, $phpVersionID);
}

/**
 * @param array|null $dbOverrides
 *
 * @return ClonerDBInfo
 *
 * @throws ClonerNoConstantException
 */
function cloner_db_info_from_worker(array $dbOverrides = null)
{
    foreach (array('DB_USER', 'DB_PASSWORD', 'DB_HOST', 'DB_NAME') as $constant) {
        if (!defined($constant)) {
            throw new ClonerNoConstantException($constant);
        }
    }
    $dbHost = DB_HOST;
    if (isset($dbOverrides[0]['dbHost'])) {
        $dbHost = $dbOverrides[0]['dbHost'];
    }
    /** @noinspection PhpUndefinedConstantInspection */
    return new ClonerDBInfo(DB_USER, DB_PASSWORD, $dbHost, DB_NAME);
}

class ClonerTable
{
    public $name = '';
    public $size = 0;
    public $noData = false;
    public $source = '';

    /**
     * @param string $name
     * @param int    $size
     * @param bool   $noData
     * @param string $source
     */
    public function __construct($name, $size, $noData, $source)
    {
        $this->name   = $name;
        $this->size   = (int)$size;
        $this->noData = (bool)$noData;
        $this->source = (string)$source;
    }

    public static function fromArray(array $data)
    {
        return new self($data['name'], $data['size'], $data['noData'], $data['source']);
    }
}

/**
 * @param string $name
 *
 * @return int|string|bool
 *
 * @throws ClonerNoConstantException
 */
function cloner_constant($name)
{
    if (!defined($name)) {
        throw new ClonerNoConstantException($name);
    }
    return constant($name);
}

/**
 * @param string $path
 *
 * @return string Resolved path on the filesystem.
 *
 * @throws ClonerRealPathException
 */
function cloner_realpath($path)
{
    $real = realpath($path);
    if ($real === false) {
        if (cloner_is_path_absolute($path)) {
            return $path;
        }
        throw new ClonerRealPathException($path);
    }
    return $real;
}

/**
 * @param string $name
 *
 * @return string|int|float
 *
 * @throws ClonerException
 */
function cloner_server_constant($name)
{
    if (!array_key_exists($name, $_SERVER)) {
        throw new ClonerException(sprintf('The variable $_SERVER["%s"] is not defined', $name), 'server_constant_empty');
    }
    return $_SERVER[$name];
}

/**
 * @param string $overrideURL Don't trust site_url()'functions output, and use this URL instead.
 * @return ClonerWPInfo
 *
 * @throws ClonerException
 */
function cloner_wp_info_from_worker($overrideURL = '')
{
    $absPath = cloner_realpath(cloner_constant('ABSPATH'));

    global $wpdb, $table_prefix;

    $prefix = $table_prefix;
    if (!empty($wpdb->base_prefix)) {
        $prefix = $wpdb->base_prefix;
    }

    list($configPath, $config) = cloner_env_read_wp_config($absPath, true);

    $rawContentPath = cloner_constant('WP_CONTENT_DIR');
    $contentPath    = realpath($rawContentPath);
    if ($contentPath === false) {
        // Common mistake, WP_CONTENT_DIR relative to ABSPATH.
        $contentPath = realpath("$absPath/$rawContentPath");
        if ($contentPath === false) {
            throw new ClonerException(sprintf('Could not determine location of WP_CONTENT_DIR, %s', $rawContentPath), 'content_dir_not_found');
        }
    }
    $contentPath = cloner_make_path_relative($contentPath, $absPath);
    $pluginsPath = cloner_realpath(cloner_constant('WP_PLUGIN_DIR'));
    $pluginsPath = cloner_make_path_relative($pluginsPath, $absPath);

    $uploadInfo = wp_upload_dir(null, false);
    if (!isset($uploadInfo['basedir'])) {
        throw new ClonerException('Invalid upload directory.', 'upload_basedir_empty');
    }

    $uploadsPath   = cloner_realpath($uploadInfo['basedir']);
    $uploadsPath   = cloner_make_path_relative($uploadsPath, $absPath);
    $muPluginsPath = cloner_constant('WPMU_PLUGIN_DIR');
    if (!cloner_is_path_absolute($muPluginsPath)) {
        $muPluginsPath = $absPath.'/'.$muPluginsPath;
    }
    $muPluginsPath = cloner_make_path_relative($muPluginsPath, $absPath);

    $url = $overrideURL;
    if (empty($url)) {
        $url = site_url();
    }

    $htaccessContent = @file_get_contents($absPath.'/.htaccess');
    return new ClonerWPInfo($url, $absPath, $prefix, $configPath, $config, '', $contentPath, $pluginsPath, $muPluginsPath, $uploadsPath, $htaccessContent);
}

/**
 * @param string $endPath   Absolute path, eg. /a/b/c/d.
 * @param string $startPath Absolute path, eg. /a/b.
 *
 * @return string How to navigate from d to b, eg. c/d.
 *
 * @throws ClonerException If any of the paths is not absolute.
 * @link https://github.com/symfony/filesystem/blob/70337563d9da65b39fdedd90bc5b221015c26803/Filesystem.php#L447
 *
 */
function cloner_make_path_relative($endPath, $startPath)
{
    if (!cloner_is_path_absolute($startPath)) {
        throw new ClonerException(sprintf('The start path "%s" is not absolute.', $startPath));
    }
    if (!cloner_is_path_absolute($endPath)) {
        throw new ClonerException(sprintf('The end path "%s" is not absolute.', $endPath));
    }

    // Normalize separators on Windows
    if ('\\' === DIRECTORY_SEPARATOR) {
        $endPath   = str_replace('\\', '/', $endPath);
        $startPath = str_replace('\\', '/', $startPath);
    }
    $endPath   = cloner_strip_path_drive_letter($endPath);
    $startPath = cloner_strip_path_drive_letter($startPath);
    // Split the paths into arrays
    $startPathArr = explode('/', trim($startPath, '/'));
    $endPathArr   = explode('/', trim($endPath, '/'));
    $startPathArr = cloner_normalize_path_array($startPathArr);
    $endPathArr   = cloner_normalize_path_array($endPathArr);
    // Find for which directory the common path stops
    $index = 0;
    while (isset($startPathArr[$index]) && isset($endPathArr[$index]) && $startPathArr[$index] === $endPathArr[$index]) {
        ++$index;
    }
    // Determine how deep the start path is relative to the common path (ie, "web/bundles" = 2 levels)
    if (1 === count($startPathArr) && '' === $startPathArr[0]) {
        $depth = 0;
    } else {
        $depth = count($startPathArr) - $index;
    }
    // Repeated "../" for each level need to reach the common path
    $traverser        = str_repeat('../', $depth);
    $endPathRemainder = implode('/', array_slice($endPathArr, $index));
    // Construct $endPath from traversing to the common path, then to the remaining $endPath
    $relativePath = $traverser.('' !== $endPathRemainder ? $endPathRemainder.'/' : '');
    return '' === $relativePath ? './' : $relativePath;
}

function cloner_strip_path_drive_letter($path)
{
    if (strlen($path) > 2 && ':' === $path[1] && '/' === $path[2] && ctype_alpha($path[0])) {
        return substr($path, 2);
    }
    return $path;
}

function cloner_normalize_path_array($pathSegments)
{
    $result = array();
    foreach ($pathSegments as $segment) {
        if ('..' === $segment) {
            array_pop($result);
        } elseif ('.' !== $segment) {
            $result[] = $segment;
        }
    }
    return $result;
}

/**
 * @param string $file
 *
 * @return bool
 */
function cloner_is_path_absolute($file)
{
    return strspn($file, '/\\', 0, 1)
        || (strlen($file) > 3 && ctype_alpha($file[0])
            && ':' === $file[1]
            && strspn($file, '/\\', 2, 1)
        )
        || null !== parse_url($file, PHP_URL_SCHEME);
}

/**
 * @param string $root
 * @param bool   $searchIncluded
 *
 * @return array Tuple, first element is the "wp-config.php" path (most common other option is "../wp-config.php"),
 *               Second element is the config content.
 *
 * @throws ClonerException
 */
function cloner_env_read_wp_config($root, $searchIncluded)
{
    if (!is_file($path = "$root/wp-config.php")) {
        if (!is_file($path = "$root/../wp-config.php") || is_file("$root/../wp-settings.php")) {
            if (!$searchIncluded || ($path = cloner_env_find_wp_config()) === null) {
                throw new ClonerException('File wp-config.php could not be found', 'wp_config_not_found');
            }
        }
    }
    return array(cloner_make_path_relative($path, $root), cloner_get_contents($path));
}

/**
 * @param string $path
 * @param int    $offset
 *
 * @param null   $maxLen
 *
 * @return string
 * @throws ClonerFSFunctionException
 */
function cloner_get_contents($path, $offset = 0, $maxLen = null)
{
    if ($maxLen === null) {
        $content = @file_get_contents($path, false, null, $offset);
    } else {
        $content = @file_get_contents($path, false, null, $offset, $maxLen);
    }
    if ($content === false) {
        throw new ClonerFSFunctionException('file_get_contents', $path);
    }
    return $content;
}

/**
 * Find /wp-config.php among all the currently included PHP files.
 *
 * @return string|null
 */
function cloner_env_find_wp_config()
{
    $tail = DIRECTORY_SEPARATOR.'wp-config.php';
    $len  = strlen($tail);
    foreach (get_included_files() as $file) {
        // https://bugs.php.net/bug.php?id=67043
        if (substr_compare($file, $tail, -$len, $len)) {
            return $file;
        }
    }
    return null;
}

class ClonerParsedWPConfigInfo
{
    public $dbUser = '';
    public $dbPassword = '';
    public $dbHost = '';
    public $dbName = '';
    public $wpTablePrefix = '';
}

/**
 * @param string $config
 *
 * @return ClonerParsedWPConfigInfo
 *
 * @throws ClonerException
 */
function cloner_env_parse_wp_config($config)
{
    $tokens    = token_get_all($config);
    $constants = cloner_get_constants_from_tokens($tokens);
    foreach (array('DB_USER', 'DB_PASSWORD', 'DB_HOST', 'DB_NAME') as $constant) {
        if (!isset($constants[$constant])) {
            throw new ClonerException("Constant $constant not found inside wp-config.php");
        }
    }
    $info             = new ClonerParsedWPConfigInfo();
    $info->dbUser     = $constants['DB_USER'];
    $info->dbPassword = $constants['DB_PASSWORD'];
    $info->dbHost     = $constants['DB_HOST'];
    $info->dbName     = $constants['DB_NAME'];
    $tablePrefix      = cloner_get_wp_config_table_prefix($tokens);
    if ($tablePrefix === null) {
        throw new ClonerException('Variable $table_prefix could not be parsed from wp-config.php');
    }
    $info->wpTablePrefix = $tablePrefix;
    return $info;
}

/**
 * @param $table  string
 *
 * @return bool
 */
function cloner_is_schema_only($table)
{
    $tableLen = strlen($table);
    $ignored  = array(
        'wysija_user_history',
        '_wsd_plugin_alerts',
        '_wsd_plugin_live_traffic',
        'adrotate_tracker',
        'aiowps_events',
        'ak_404_log',
        'bad_behavior',
        'cn_track_post',
        'nginxchampuru',
        'popover_ip_cache',
        'redirection_404',
        'spynot_systems_log',
        'statify',
        'statistics_useronline',
        'tcb_api_error_log',
        'useronline',
        'wbz404_logs',
        'wfHits',
        'wfLeechers',
        'who_is_online',
        'simple_history',
        'simple_history_contexts',
        'wfHoover',
        'et_bloom_stats',
        'itsec_log',
        'itsec_logs',
        'itsec_temp',
        'cpd_counter',
        'session',
        //phpBB
        'moderator_cache',
        //drupal
        'watchdog',
        'cache_bootstrap',
        'drup_cache_config',
        'cache_container',
        'cache_data',
        'cache_default',
        'cache_discovery',
        'cache_dynamic_page_cache',
        'cache_entity',
        'cache_menu',
        'cache_page',
        'cache_render',

        //magento
        'log_visitor_info',

        //vBulletin
        'cache',
        'cacheevent'

    );
    foreach ($ignored as $ignore) {
        $ignoreLen = strlen($ignore);
        if ($ignoreLen > $tableLen) {
            continue;
        }
        $suffix = substr($table, -$ignoreLen);
        if (strncasecmp($suffix, $ignore, $ignoreLen) === 0) {
            return true;
        }
    }
    return false;
}





class ClonerDeadline
{
    private $deadline = 0;

    /**
     * @param $timeout int Timeout in seconds; 0 to never time out; -1 to time out immediately.
     */
    public function __construct($timeout)
    {
        if ($timeout === 0 || $timeout === -1) {
            $this->deadline = $timeout;
            return;
        }
        $this->deadline = microtime(true) + (float)$timeout;
    }

    /**
     * @return bool True if deadline is reached.
     */
    public function done()
    {
        if ($this->deadline === 0) {
            return false;
        } elseif ($this->deadline === -1) {
            return true;
        }
        return microtime(true) > $this->deadline;
    }
}

class ClonerURLReplacer
{
    private $fullURL;
    private $shortURL;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->fullURL  = $url;
        $this->shortURL = preg_replace('{^https?:}', '', $url);
    }

    /**
     * @param array $matches First match is http: or https:, second match is trailing slash.
     *
     * @return string
     */
    public function replace(array $matches)
    {
        if (strlen($matches[1])) {
            // Scheme is present.
            return $this->fullURL.$matches[2];
        }
        // Empty scheme.
        return $this->shortURL.$matches[2];
    }
}

class ClonerErrorHandler
{
    private $logFile;
    private $reservedMemory;
    private static $lastError;
    private $requestID;

    public function __construct($logFile)
    {
        $this->logFile = $logFile;
    }

    public function setRequestID($requestID)
    {
        $this->requestID = $requestID;
    }

    public function register()
    {
        $this->reservedMemory = str_repeat('x', 10240);
        register_shutdown_function(array($this, 'handleFatalError'));
        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'handleException'));
    }

    public function refresh()
    {
        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'handleException'));
    }

    /**
     * @return array
     */
    public static function lastError()
    {
        return self::$lastError;
    }

    public function handleError($type, $message, $file, $line)
    {
        self::$lastError = compact('message', 'type', 'file', 'line');
        if (error_reporting() === 0) {
            // Muted error.
            return;
        }
        if (!strlen($message)) {
            $message = 'empty error message';
        }
        $args = func_get_args();
        if (count($args) >= 6 && $args[5] !== null && $type & E_ERROR) {
            // 6th argument is backtrace.
            // E_ERROR fatal errors are triggered on HHVM when
            // hhvm.error_handling.call_user_handler_on_fatals=1
            // which is the way to get their backtrace.
            $this->handleFatalError(compact('type', 'message', 'file', 'line'));

            return;
        }
        list($file, $line) = self::getFileLine($file, $line);
        $this->log(sprintf("%s: %s in %s on line %d", self::codeToString($type), $message, $file, $line));
    }

    private static function getFileLine($file, $line)
    {
        if (!function_exists('__bundler_sourcemap')) {
            return array($file, $line);
        }
        if (__FILE__ !== $file) {
            return array($file, $line);
        }
        $globalOffset = 0;
        foreach (__bundler_sourcemap() as $offsetPath) {
            list($offset, $path) = $offsetPath;
            if ($line <= $offset) {
                return array($path, $line - $globalOffset + 1);
            }
            $globalOffset = $offset;
        }
        return array($file, $line);
    }

    /**
     * @param Exception|Error $e
     */
    public function handleException($e)
    {
        $errorCode     = 'exception';
        $internalError = '';
        $context       = array();
        if ($e instanceof ClonerException && strlen($e->getErrorCode())) {
            $errorCode     = $e->getErrorCode();
            $internalError = $e->getInternalError();
            foreach (get_object_vars($e) as $key => $val) {
                if (!is_scalar($val)) {
                    continue;
                }
                $context[$key] = (string)$val;
            }
        }
        $message = sprintf("%s in file %s on line %d", $e->getMessage(), $e->getFile(), $e->getLine());
        list($file, $line) = self::getFileLine($e->getFile(), $e->getLine());
        cloner_send_error_response($this->requestID, $message, $errorCode, $internalError, $file, $line, $e->getTraceAsString(), $context);
        exit;
    }

    public function handleFatalError(array $error = null)
    {
        $this->reservedMemory = null;
        if ($error === null) {
            // Since default PHP implementation doesn't call error handlers on fatal errors, the self::$lastError
            // variable won't be updated. That's why this is the only place where we call error_get_last() directly.
            $error = error_get_last();
        }
        if (!$error) {
            return;
        }
        if (!in_array($error['type'], array(E_PARSE, E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR))) {
            return;
        }
        $message = sprintf("%s: %s in %s on line %d", self::codeToString($error['type']), $error['message'], $error['file'], $error['line']);
        $this->log($message);
        list($file, $line) = self::getFileLine($error['file'], $error['line']);

        cloner_send_error_response($this->requestID, $message, 'fatal_error', $error['message'], $file, $line);
        exit;
    }

    private function log($message)
    {
        if (($fp = fopen($this->logFile, 'a')) === false) {
            return;
        }
        if (flock($fp, LOCK_EX) === false) {
            return;
        }
        if (fwrite($fp, sprintf("[%s] %s\n", date("Y-m-d H:i:s"), $message)) === false) {
            return;
        }
        fclose($fp);
    }

    private static function codeToString($code)
    {
        switch ($code) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
        }
        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 50300) {
            switch ($code) {
                case E_DEPRECATED:
                    return 'E_DEPRECATED';
                case E_USER_DEPRECATED:
                    return 'E_USER_DEPRECATED';
            }
        }
        return 'E_UNKNOWN';
    }
}

class ClonerLoader
{
    private $hookedAdmin = false;

    private $hookedAdminWpdb = false;

    private $errorHandler;

    public function __construct(ClonerErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function hook()
    {
        global $wp_filter;
        $wp_filter['all'][-999999][0]                  = array('function' => array($this, 'hookTick'), 'accepted_args' => 0);
        $wp_filter['set_auth_cookie'][-999999][0]      = array('function' => array($this, 'setAuthCookie'), 'accepted_args' => 5);
        $wp_filter['set_logged_in_cookie'][-999999][0] = array('function' => array($this, 'setAuthCookie'), 'accepted_args' => 5);
    }

    public function setAuthCookie($cookie, $expire, $expiration, $userId, $scheme)
    {
        switch ($scheme) {
            case 'auth':
                if (!defined('AUTH_COOKIE')) {
                    return;
                }
                $_COOKIE[AUTH_COOKIE] = $cookie;
                break;
            case 'secure_auth':
                if (!defined('SECURE_AUTH_COOKIE')) {
                    return;
                }
                $_COOKIE[SECURE_AUTH_COOKIE] = $cookie;
                break;
            case 'logged_in':
                if (!defined('LOGGED_IN_COOKIE')) {
                    return;
                }
                $_COOKIE[LOGGED_IN_COOKIE] = $cookie;
                break;
        }
    }

    /**
     * @throws ClonerException
     */
    public function hookTick()
    {
        global $wpdb, $pagenow;
        $this->errorHandler->refresh();
        if ($this->hookedAdmin || !function_exists('admin_url')) {
            if ($wpdb) {
                $this->adminHookWpdb($wpdb);
            }

            return;
        }
        $this->hookedAdmin = true;

        @ini_set('memory_limit', '512M');
        define('DOING_AJAX', true);
        $pagenow                   = 'options-general.php';
        $_SERVER['PHP_SELF']       = parse_url(admin_url('options-general.php'), PHP_URL_PATH);
        $_COOKIE['redirect_count'] = 10; // hack for the WordPress HTTPS plugin, so it doesn't redirect us
        if (force_ssl_admin()) {
            $_SERVER['HTTPS']       = 'on';
            $_SERVER['SERVER_PORT'] = '443';
        }
    }

    /**
     * @param wpdb $wpdb
     *
     * @throws ClonerException
     */
    private function adminHookWpdb($wpdb)
    {
        if ($this->hookedAdminWpdb || !function_exists('wp_set_current_user')) {
            return;
        }
        $this->hookedAdminWpdb = true;
        $users                 = get_users(array('role' => 'administrator', 'number' => 1, 'orderby' => 'ID'));
        $user                  = reset($users);
        if (!$user) {
            throw new ClonerException('Could not find an administrator user to use.', 'no_admin_user');
        }
        wp_set_current_user($user);
        wp_set_auth_cookie($user->ID);
        update_user_meta($user->ID, 'last_login_time', current_time('mysql'));
        do_action('wp_login', $user->user_login, $user);
    }
}

function cloner_url_slug($url)
{
    $parts = parse_url($url);
    return strtolower(rtrim(sprintf('%s%s%s', preg_replace('/^www\./i', '', $parts['host']), isset($parts['port']) ? ':'.$parts['port'] : '', isset($parts['path']) ? $parts['path'] : ''), '/'));
}

function cloner_update_multisite_url($oldRootHost, $oldRootPath, $newRootHost, $newRootPath, $host, $path)
{
    $replaced = false;
    if (substr($host, -strlen('.'.$oldRootHost)) === '.'.$oldRootHost) {
        // Hosts starts like domain.old-url.com; migrate it to domain.new-url.com
        $host     = substr($host, 0, strlen($host) - strlen('.'.$oldRootHost)).'.'.$newRootHost;
        $replaced = true;
    } elseif ($oldRootHost === $host) {
        // Host is the same as the root host; use the new one.
        $host     = $newRootHost;
        $replaced = true;
    }
    if (strlen($oldRootPath) > 1 && substr($path, 0, strlen($oldRootPath)) === $oldRootPath) {
        // Path starts like /old-root/blog-name/, strip the old-root prefix.
        $path     = '/'.ltrim(substr($path, strlen($oldRootPath)), '/');
        $replaced = true;
    }
    $path = rtrim($newRootPath, '/').'/'.ltrim($path, '/');
    return array($host, $path, $replaced);
}

/**
 * Update {$newPrefix}usermeta table by prefixing each meta_key in $keys with $oldPrefix
 * and changing its prefix to $newPrefix. Intended to be used as an atomic operation, so
 * pass $limit for tighter control over migration.
 *
 * @param ClonerDBConn $conn
 * @param array        $keys      Meta keys to migrate, without prefix.
 * @param string       $oldPrefix Old table prefix.
 * @param string       $newPrefix New table prefix.
 * @param int          $limit     Update limit for keys with old prefix, must be positive.
 *
 * @return int Number of updated fields.
 *
 * @throws ClonerException If database query fails.
 */
function cloner_update_usermeta_prefix(ClonerDBConn $conn, array $keys, $oldPrefix, $newPrefix, $limit)
{
    $oldPrefixLength = strlen($oldPrefix) + 1;
    $oldKeys         = "'{$oldPrefix}".implode("', '{$oldPrefix}", $keys)."'";

    $sql = <<<SQL
UPDATE `{$newPrefix}usermeta`
  SET `meta_key` = CONCAT({$conn->escape($newPrefix)}, SUBSTR(`meta_key`, {$oldPrefixLength}))
  WHERE `meta_key` IN ({$oldKeys})
  LIMIT {$limit}
SQL;
    return $conn->query($sql)->getNumRows();
}

function cloner_update_field_prefix(ClonerDBConn $conn, $table, $field, $oldPrefix, $newPrefix, $limit, $where = null)
{
    if ($where !== null) {
        $where = 'AND '.$where;
    }
    $escapedOldPrefix = cloner_escape_like($oldPrefix);
    // +1 is intentional.
    // https://dev.mysql.com/doc/refman/5.0/en/string-functions.html#function_substring
    $oldPrefixLength = strlen($oldPrefix) + 1;
    $sql             = <<<SQL
UPDATE `{$table}`
  SET `{$field}` = CONCAT({$conn->escape($newPrefix)}, SUBSTR(`{$field}`, {$oldPrefixLength}))
  WHERE `{$field}` LIKE '{$escapedOldPrefix}%'
  {$where}
LIMIT {$limit}
SQL;
    /** @var TYPE_NAME $conn */
    $result = $conn->query($sql);
    $count  = $result->getNumRows();
    $result->free();
    return $count;
}

// Functions that are present in more recent WP versions, but not in earlier ones.
// Keep them here for BC reasons.
function cloner_wp_polyfill()
{
    if (!function_exists('is_multisite')) {
        function is_multisite()
        {
            if (defined('MULTISITE')) {
                return MULTISITE;
            }

            if (defined('SUBDOMAIN_INSTALL') || defined('VHOST') || defined('SUNRISE')) {
                return true;
            }
            return false;
        }
    }
}

/**
 * @param string $id
 * @param mixed  $data
 */
function cloner_send_success_response($id, $data)
{
    $response = json_encode($data);
    if ($response === false) {
        cloner_send_error_response($id, "Could not JSON-encode result.", "json_encode_error");
    } else {
        echo "\n", '{"id":"', (string)$id, '","result":', $response, '}', "\n";
    }
}

function cloner_send_error_response($id, $errorMessage, $errorCode = null, $internalError = null, $file = null, $line = null, $trace = null, array $context = null)
{
    if ($context) {
        foreach ($context as $key => &$val) {
            if (!is_scalar($val)) {
                unset($context[$key]);
                continue;
            }
            $val = (string)$val;
        }
    }
    if (!$context) {
        $context = null;
    }
    $data     = array(
        'error'         => (string)$errorCode,
        'message'       => (string)$errorMessage,
        'internalError' => (string)$internalError,
        'file'          => (string)$file,
        'line'          => (int)$line,
        'trace'         => (string)$trace,
        'context'       => $context,
    );
    $response = json_encode($data);
    if ($response === false) {
        echo "\n", '{"id":"', (string)$id, '","error":{"error":"json_encode_error","message":"Could not JSON-encode error."}}', "\n";
    } else {
        echo "\n", '{"id":"', (string)$id, '","error":', $response, '}', "\n";
    }
}

/**
 * @param ClonerDBConn $conn
 * @param string       $prefix
 * @param string       $username
 *
 * @return int|null
 *
 * @throws ClonerException
 */
function cloner_get_user_id_by_username(ClonerDBConn $conn, $prefix, $username)
{
    $query = <<<SQL
SELECT u.ID
  FROM {$prefix}users u
  WHERE
    u.user_login = :user_login
  ORDER BY ID ASC
  LIMIT 1
SQL;

    $existingUser = $conn->query($query, array('user_login' => $username))->fetch();

    if ($existingUser) {
        return (int)$existingUser['ID'];
    }

    return null;
}

/**
 * @param string $username
 * @param bool   $strict
 *
 * @return mixed|string
 *
 * @see sanitize_user() from WordPress core.
 */
function cloner_sanitize_user($username, $strict = false)
{
    $username = strip_tags($username);
    // Kill octets
    $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
    $username = preg_replace('/&.+?;/', '', $username); // Kill entities

    // If strict, reduce to ASCII for max portability.
    if ($strict) {
        $username = preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);
    }

    $username = trim($username);
    // Consolidate contiguous whitespace
    $username = preg_replace('|\s+|', ' ', $username);

    return $username;
}

/**
 * @param string|null $path File path for which to clear the cache.
 *
 * @see clearstatcache()
 */
function cloner_clear_stat_cache($path = null)
{
    if (PHP_VERSION_ID < 50300 || $path === null) {
        clearstatcache();
        return;
    }
    clearstatcache(true, $path);
}

/**
 * Creates a directory similarly to mkdir -p, but calls chmod 0777 to each directory in $path before creating
 * it if the parent directory (starting with $root) is not writable.
 *
 * @param string $root Absolute path to the root directory, should already exist and will not be checked.
 * @param string $path Relative path of directory to create.
 *
 * @return string Error message; empty message means "no error".
 */
function cloner_make_dir($root, $path)
{
    $dir = strtok($path, '/');
    do {
        $root .= '/'.$dir;
        if ($dir === '..') {
            continue;
        }
        if (is_dir($root)) {
            if (!is_writable($root)) {
                @chmod($root, 0777);
            }
            continue;
        }
        $dirMade = @mkdir($root, 0777, true);
        // Verify that the dir was not made by another process in a race condition.
        if ($dirMade === false) {
            $lastError = cloner_last_error_for('mkdir');
            cloner_clear_stat_cache($root);
            if (!is_dir($root)) {
                return $lastError;
            }
        }
    } while (is_string($dir = strtok('/')));

    if (!is_writable($root)) {
        return "directory $root is not writable";
    }

    return '';
}

/**
 * Writes the file $content located on $path at $offset. If $offset does not match file size at $path,
 * the function will fail. Uses retries with exponential back-off.
 *
 * @param string $path    Path to file to create and/or write content to.
 * @param int    $offset  File offset, must match the existing file size if greater than 0.
 * @param string $content Content to write to the file at $offset.
 *
 * @return string Error message, empty message means "no error".
 */
function cloner_write_file($path, $offset, $content)
{
    $attempt = 0;
    $length  = strlen($content);
    $total   = $offset + $length;
    $err     = '';
    $fp      = null;
    do {
        cloner_clear_stat_cache($path);
        if ($attempt > 0) {
            // Sleep for 200ms, 400ms, 800s, 1.6s etc.
            usleep(100000 * pow(2, $attempt));
            trigger_error("$err (file: $path, attempt: $attempt, offset: $offset, length: $length)");
        }
        // Check file size if appending content.
        if ($offset && (($size = filesize($path)) < $offset)) {
            if ($size === false) {
                $err = cloner_last_error_for('filesize');
            } else {
                $err = "corrupt file; wrote $offset bytes, but file is $size bytes";
            }
            continue;
        }
        $err = '';
        if (is_resource($fp)) {
            @fclose($fp);
        }
        if (is_dir($path)) {
            if (strlen($err = cloner_remove_file_or_dir($path))) {
                break;
            }
        }
        $fp = @fopen($path, $offset ? 'cb' : 'wb');
        if ($fp === false) {
            $err = cloner_last_error_for('fopen');
            continue;
        }
        if ($offset) {
            if (@fseek($fp, $offset) !== 0) {
                $err = cloner_last_error_for('fseek');
                continue;
            }
        }
        if (@fwrite($fp, $content) === false) {
            $err = cloner_last_error_for('fwrite');
            continue;
        }
        if (@fclose($fp) === false) {
            $err = cloner_last_error('fclose');
            continue;
        }
        $fp = null;
        // This is mandatory before stat-ing the file.
        cloner_clear_stat_cache($path);
        if (($size = @filesize($path)) !== $total) {
            if ($size === false) {
                $err = cloner_last_error_for('filesize');
                continue;
            }
            $err = "file size after write is $size; expected $offset+$length=$total";
            continue;
        }
        break;
    } while (strlen($err) && ++$attempt < 3);

    return $err;
}

/**
 * Checks to see if a string is utf8 encoded.
 * This function checks for 5-Byte sequences, UTF8
 * has Bytes Sequences with a maximum length of 4.
 *
 * @param string $str The string to be checked
 *
 * @return bool True if $str fits a UTF-8 model, false otherwise.
 */
function cloner_seems_utf8($p)
{
    static $first;
    if ($first === null) {
        $xx    = 0xF1; // invalid: size 1
        $as    = 0xF0; // ASCII: size 1
        $s1    = 0x02; // accept 0, size 2
        $s2    = 0x13; // accept 1, size 3
        $s3    = 0x03; // accept 0, size 3
        $s4    = 0x23; // accept 2, size 3
        $s5    = 0x34; // accept 3, size 4
        $s6    = 0x04; // accept 0, size 4
        $s7    = 0x44; // accept 4, size 4
        $first = array(
            //   1    2    3    4    5    6    7    8    9    A    B    C    D    E    F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x00-0x0F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x10-0x1F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x20-0x2F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x30-0x3F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x40-0x4F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x50-0x5F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x60-0x6F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x70-0x7F
            //   1    2    3    4    5    6    7    8    9    A    B    C    D    E    F
            $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0x80-0x8F
            $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0x90-0x9F
            $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0xA0-0xAF
            $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0xB0-0xBF
            $xx, $xx, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, // 0xC0-0xCF
            $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, // 0xD0-0xDF
            $s2, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s4, $s3, $s3, // 0xE0-0xEF
            $s5, $s6, $s6, $s6, $s7, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0xF0-0xFF
        );
    }
    static $xx = 0xF1;
    static $locb = 0x80;
    static $hicb = 0xBF;
    static $acceptRanges;
    if ($acceptRanges === null) {
        $acceptRanges = array(
            0 => array($locb, $hicb),
            1 => array(0xA0, $hicb),
            2 => array($locb, 0x9F),
            3 => array(0x90, $hicb),
            4 => array($locb, 0x8F),
        );
    }
    $n = strlen($p);
    for ($i = 0; $i < $n;) {
        $pi = ord($p[$i]);
        if ($pi < 0x80) {
            $i++;
            continue;
        }
        $x = $first[$pi];
        if ($x === $xx) {
            return false; // Illegal starter byte.
        }
        $size = $x & 7;
        if ($i + $size > $n) {
            return false; // Short or invalid.
        }
        $accept = $acceptRanges[$x >> 4];
        if ((($c = ord($p[$i + 1])) < $accept[0]) || ($accept[1] < $c)) {
            return false;
        } elseif ($size === 2) {
        } elseif ((($c = ord($p[$i + 2])) < $locb) || ($hicb < $c)) {
            return false;
        } elseif ($size === 3) {
        } elseif ((($c = ord($p[$i + 3])) < $locb) || ($hicb < $c)) {
            return false;
        }
        $i += $size;
    }
    return true;
}

function cloner_encode_non_utf8($p)
{
    static $first;
    if ($first === null) {
        $xx    = 0xF1; // invalid: size 1
        $as    = 0xF0; // ASCII: size 1
        $s1    = 0x02; // accept 0, size 2
        $s2    = 0x13; // accept 1, size 3
        $s3    = 0x03; // accept 0, size 3
        $s4    = 0x23; // accept 2, size 3
        $s5    = 0x34; // accept 3, size 4
        $s6    = 0x04; // accept 0, size 4
        $s7    = 0x44; // accept 4, size 4
        $first = array(
            //   1   2   3   4   5   6   7   8   9   A   B   C   D   E   F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x00-0x0F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x10-0x1F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x20-0x2F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x30-0x3F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x40-0x4F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x50-0x5F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x60-0x6F
            $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, $as, // 0x70-0x7F
            //   1   2   3   4   5   6   7   8   9   A   B   C   D   E   F
            $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0x80-0x8F
            $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0x90-0x9F
            $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0xA0-0xAF
            $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0xB0-0xBF
            $xx, $xx, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, // 0xC0-0xCF
            $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, $s1, // 0xD0-0xDF
            $s2, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s3, $s4, $s3, $s3, // 0xE0-0xEF
            $s5, $s6, $s6, $s6, $s7, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, $xx, // 0xF0-0xFF
        );
    }
    static $xx = 0xF1;
    static $locb = 0x80;
    static $hicb = 0xBF;
    static $acceptRanges;
    if ($acceptRanges === null) {
        $acceptRanges = array(
            0 => array($locb, $hicb),
            1 => array(0xA0, $hicb),
            2 => array($locb, 0x9F),
            3 => array(0x90, $hicb),
            4 => array($locb, 0x8F),
        );
    }
    $percent = ord('%');
    $plus    = ord('+');
    $encoded = false;
    $fixed   = '';
    $n       = strlen($p);
    $invalid = false;
    for ($i = 0; $i < $n;) {
        if ($invalid) {
            if (!$encoded) {
                // Make sure that "urldecode" call transforms the string to its original form.
                // We don't encode printable characters, only invalid UTF-8; but these characters
                // will always be processed by URL-decoder.
                $fixed = strtr($fixed, array('%' => '%25', '+' => '%2B'));
            }
            $encoded = true;
            $fixed   .= urlencode($p[$i]);
            $invalid = false;
            $i++;
            continue;
        }
        $pi = ord($p[$i]);
        if ($pi < 0x80) {
            if ($encoded && $pi === $percent) {
                $fixed .= '%25';
            } elseif ($encoded && $pi === $plus) {
                $fixed .= '%2B';
            } else {
                $fixed .= $p[$i];
            }
            $i++;
            continue;
        }
        $x = $first[$pi];
        if ($x === $xx) {
            $invalid = true;
            continue;
        }
        $size = $x & 7;
        if ($i + $size > $n) {
            $invalid = true;
            continue;
        }
        $accept = $acceptRanges[$x >> 4];
        if ((($c = ord($p[$i + 1])) < $accept[0]) || ($accept[1] < $c)) {
            $invalid = true;
            continue;
        } elseif ($size === 2) {
        } elseif ((($c = ord($p[$i + 2])) < $locb) || ($hicb < $c)) {
            $invalid = true;
            continue;
        } elseif ($size === 3) {
        } elseif ((($c = ord($p[$i + 3])) < $locb) || ($hicb < $c)) {
            $invalid = true;
            continue;
        }
        $fixed .= substr($p, $i, $size);
        $i     += $size;
    }
    return $fixed;
}

function cloner_format_query_error($message, $statements, $path, $processed, $cursor, $size)
{
    $max = 2 * 1024;
    $len = strlen($statements);
    if ($len > $max) {
        $statements = substr($statements, 0, $max / 2).sprintf('[truncated %d bytes]', strlen($statements) - $max).substr($statements, -$max / 2);
    }
    if (!cloner_seems_utf8($statements)) {
        if (function_exists('mb_convert_encoding')) {
            // http://php.net/manual/en/function.iconv.php#108643
            ini_set('mbstring.substitute_character', 'none');
            $statements = mb_convert_encoding($statements, 'UTF-8', 'UTF-8');
        } else {
            $statements = 'base64:'.base64_encode($statements);
        }
    }
    return sprintf('%s; query: %s (file %s at %d-%d out of %d bytes)', $message, $statements, $path, $processed, $cursor, $size);
}

// Actions.

class ClonerNoTransportStreamsException extends ClonerException
{
    public function __construct(array $attempted, array $available)
    {
        parent::__construct(sprintf(
            "could not find available transport stream to use; attempted: %s; available: %s",
            implode(',', $attempted),
            implode(',', $available)
        ), 'no_tls');
    }
}

class ClonerSyncState
{
    /**
     * @var string Address to connect to in host:port format.
     */
    public $host = '';

    /**
     * @var string[] Established connection IDs.
     */
    public $haveConns = array();

    /**
     * @var string[] Connection IDs that we need to create.
     */
    public $wantConns = array();

    /**
     * @var int
     */
    public $timestamp;

    public static function fromArray(array $data)
    {
        $state            = new self();
        $state->host      = isset($data['host']) ? $data['host'] : '';
        $state->haveConns = isset($data['haveConns']) ? $data['haveConns'] : array();
        $state->wantConns = isset($data['wantConns']) ? $data['wantConns'] : array();
        $state->timestamp = time();
        return $state;
    }
}

/**
 * @param string $code    Error code.
 * @param string $message Error message.
 *
 * @return string JSON-encoded error result.
 */
function cloner_error_result($code, $message)
{
    if (!strlen($code)) {
        $code = 'unexpected_error';
    }
    return json_encode(array(
        'ok'      => false,
        'error'   => $code,
        'message' => $message,
    ));
}

function cloner_ok_result(array $props = array())
{
    return json_encode(array_merge(array('ok' => true), $props));
}

function cloner_page_state_poll()
{
    try {
        $state = cloner_state_poll(cloner_constant('CLONER_STATE'));
    } catch (ClonerException $e) {
        trigger_error($e->getMessage());
        exit(cloner_error_result($e->getErrorCode(), $e->getMessage()));
    } catch (Exception $e) {
        trigger_error($e->getMessage());
        exit(cloner_error_result('', $e->getMessage()));
    }

    exit(cloner_ok_result(array('state' => $state)));
}

function cloner_page_connect($host, $connID)
{
    try {
        cloner_action_connect_back($host, '/api/ws', $connID, 5, 120, '');
    } catch (ClonerException $e) {
        trigger_error($e->getMessage());
        exit(cloner_error_result($e->getErrorCode(), $e->getMessage()));
    } catch (Exception $e) {
        trigger_error($e->getMessage());
        exit(cloner_error_result('', $e->getMessage()));
    }
}

class ClonerURLException extends ClonerException
{
    public $url = '';
    public $error = '';

    /**
     * @param string $url
     * @param string $error
     */
    public function __construct($url, $error)
    {
        $this->url   = $url;
        $this->error = $error;
        parent::__construct(sprintf('url %s is not valid: %s', $url, $error));
    }
}

/**
 * @param string $url
 *
 * @return ClonerSyncState
 *
 * @throws ClonerNetException
 * @throws ClonerURLException
 * @throws Exception
 */
function cloner_state_poll($url)
{
    $json = cloner_http_do('GET', $url);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        throw new ClonerException("non-json get-state response: $json");
    }
    if (!empty($data['error'])) {
        throw new ClonerException('Poll error', $data['error']);
    }
    return ClonerSyncState::fromArray($data);
}

/**
 * Runs appropriate route and outputs result.
 */
function cloner_sync_main()
{
    $root = dirname(__FILE__);
    $uri  = '';
    if (isset($_GET['q']) && is_string($_GET['q']) && strlen($_GET['q'])) {
        $uri = $_GET['q'];
    }
    switch ($uri) {
        case '':
            cloner_page_index();
            return;
        case 'state_poll':
            cloner_page_state_poll();
            return;
        case 'connect':
            cloner_page_connect((string)@$_GET['host'], (string)@$_GET['conn_id']);
            return;
        case 'cleanup':
            echo json_encode(cloner_action_delete_files($root, array('cloner.php')));
            return;
        default:
            cloner_page_404();
            return;
    }
}

/**
 * Compiles an array of glob-like patterns into a single regular expression that
 * matches any of them in case-insensitive mode. Supported syntax:
 *  * matches zero or more non-/ characters
 *  + matches one or more non-/ characters
 *  ? matches any single non-/ character
 *  ** / (without the space) matches zero characters or any number of characters that ends with /
 *  [abc] matches one character given in the bracket
 *  [abc*] matches zero or more characters given in the bracket
 *  [abc+] matches one or more characters given in the bracket
 *  [a-z] matches one character from the range given in the bracket
 *  [a-z*] matches zero or more characters from the range given in the bracket
 *  [a-z+] matches one or more characters from the range given in the bracket
 *  [a-z*2] matches exactly 2 characters from the range given in the bracket
 *  [a-z*,2] matches up to 2 characters from the range given in the bracket
 *  [a-z*2,] matches 2 or more characters from the range given in the bracket
 *  [a-z*2,6] matches 2 to 6 characters from the range given in the bracket
 *  {foo,bar} matches any of the given literal strings
 *
 * @param string   $prefix    Prefix for all matches.
 * @param string[] $globs     Glob patterns.
 * @param string   $delimiter Delimiter to use when preg-quoting.
 *
 * @return string Regexp that matches any of the glob patterns from $globs. It always starts
 *                with `$prefix(?:` and ends with `)` and does not include ^ nor $ nor delimiters.
 */
function cloner_globs_to_regexp($prefix, array $globs, $delimiter)
{
    $regexps = array();
    foreach ($globs as $glob) {
        $regexp = '';
        $i      = 0;
        $len    = strlen($glob);
        while ($i < $len) {
            switch ($glob[$i]) {
                case '*':
                    if (substr($glob, $i+1, 2) === '*/') {
                        // Match "**/example.php" or "dir/**/example.php"
                        $regexp .= '(?:|.*/)';
                        $i+=2;
                        break;
                    }
                    $regexp .= '[^/]*';
                    break;
                case '+':
                    $regexp .= '[^/]+';
                    break;
                case '?':
                    $regexp .= '[^/]';
                    break;
                case '[':
                    $end = strpos(substr($glob, $i + 1), ']');
                    if ($end === false || $end === 0) {
                        // No enclosing ], ignore the opening [.
                        $regexp .= preg_quote($glob[$i], $delimiter);
                        break;
                    }
                    $match = substr($glob, $i + 1, $end);
                    $flag  = '';
                    if (strlen($match) > 1) {
                        preg_match('{(\\+|\\*|\\*\\d+|\\*\\d+,\\d*|\\*\\d+,|\\*,\\d+)$}', $match, $flags);
                        // One of "+", "*", "*32", "*16,32", "*,32", "*32,"
                        if (count($flags)) {
                            $match = substr($match, 0, -strlen($flags[0]));
                            $flag  = substr($flags[0], 0, 1);
                            if (strlen($flags[0]) > 1) {
                                $flag = '{'.substr($flags[0], 1).'}';
                            }
                        }
                    }
                    $regexp .= '['.$match.']'.$flag;
                    $i      += $end + 1;
                    break;
                case '{':
                    $end = strpos(substr($glob, $i + 1), '}');
                    if ($end === false || $end === 0) {
                        // No enclosing }, ignore the opening {.
                        $regexp .= preg_quote($glob[$i], $delimiter);
                        break;
                    }
                    $matches = substr($glob, $i + 1, $end);
                    $flag    = '';
                    $regexp  .= '(?:'.strtr($matches, ',', '|').')'.$flag;
                    $i       += $end + 1;
                    break;
                default:
                    $regexp .= preg_quote($glob[$i], $delimiter);
                    break;
            }
            $i++;
        }
        $regexps[] = $regexp;
    }
    return preg_quote($prefix, $delimiter).'(?:'.implode('|', $regexps).')';
}

abstract class ClonerVisitor implements ClonerFSVisitor
{
    /** @var ClonerStatResult */
    public $result;
    protected $maxCount = 0;
    protected $maxPayload = 0;
    protected $deadline = 0;
    protected $skipPaths = array();
    protected $transferAll = false;

    protected $payload = 0;

    const ANY_DIR = '';
    const ONLY_FILE = 'file';
    const ONLY_DIR = 'dir';

    /** @var string[] Core files are matched by their full path and are invisible to the process. */
    protected static $ignoreFiles = array('cloner.php', 'cloner_error_log', 'mwp_db');

    /** @var string */
    protected $compiledIncludeList = '';
    /** @var string */
    protected $compiledSystemExcludeList = '';
    /** @var string */
    protected $compiledUserExcludeList = '';
    /** @var string */
    protected $compiledFileExcludeList = '';
    /** @var string */
    protected $compiledDirExcludeList = '';

    public $prefix = '';

    public function visit($path, ClonerStatInfo $stat, Exception $e = null)
    {
        // Core files should be invisible to the collector.
        if (in_array($path, self::$ignoreFiles)) {
            throw new ClonerSkipVisitException();
        }

        if (strlen($path) === 0) {
            $fullPath = $this->prefix;
            if (strlen($fullPath) === 0) {
                $fullPath = '.';
            }
        } elseif (strlen($this->prefix) > 0) {
            $fullPath = $this->prefix."/".$path;
        } else {
            $fullPath = $path;
        }
        $encoded     = false;
        $encodedPath = cloner_encode_non_utf8($fullPath);
        if ($fullPath !== $encodedPath) {
            $fullPath = $encodedPath;
            $encoded  = true;
        }

        if (empty($e)) {
            $status = $this->includePath($fullPath, $stat->isDir());
        } else {
            $status = ClonerStatus::ERROR;
        }

        $len = 0;
        if ($this->maxPayload) {
            $len = $this->payloadLen($path, $status, $stat, $e);
        }

        if (count($this->result->stats)) {
            if ($this->deadline && $this->deadline <= time()) {
                return false;
            }
            if ($this->maxCount && count($this->result->stats) >= $this->maxCount) {
                return false;
            }
            if ($this->maxPayload && $this->payload + $len >= $this->maxPayload) {
                return false;
            }
        }

        $this->payload += $len;
        if ($e !== null) {
            $this->result->appendError($fullPath, $encoded, ClonerStatus::ERROR, $e->getMessage());
        } elseif ($status) {
            $this->result->appendError($fullPath, $encoded, $status);
            throw new ClonerSkipVisitException();
        } else {
            if ($stat->isDir()) {
                $this->result->appendDir($fullPath, $encoded, $stat->getMTime(), $stat->getPermissions());
            } elseif ($stat->isLink()) {
                $this->result->appendLink($fullPath, $encoded, $stat->link, $stat->getPermissions());
            } else {
                $this->result->appendFile($fullPath, $encoded, $stat->getMTime(), $stat->getSize(), $stat->getPermissions());
            }
        }

        return true;
    }

    private function includePath($path, $isDir)
    {
        if ($path === '.') {
            return ClonerStatus::OK;
        }
        if (!preg_match($this->compiledIncludeList, $path)) {
            return ClonerStatus::SKIPPED;
        }
        if (preg_match($this->compiledSystemExcludeList, $path, $m)) {
            return ClonerStatus::SKIPPED;
        }
        if ($this->compiledUserExcludeList && preg_match($this->compiledUserExcludeList, $path)) {
            return ClonerStatus::USER_SKIPPED;
        }
        if ($this->compiledFileExcludeList && !$isDir && preg_match($this->compiledFileExcludeList, $path)) {
            return ClonerStatus::SKIPPED;
        }
        if ($this->compiledDirExcludeList && $isDir && preg_match($this->compiledDirExcludeList, $path)) {
            return ClonerStatus::SKIPPED;
        }
        return ClonerStatus::OK;
    }

    private function payloadLen($path, $status, ClonerStatInfo $stat = null, Exception $e = null)
    {
        if ($status) {
            // {"path":"","status":1}
            return 8 + strlen($path) + 13;
        }
        if ($e !== null) {
            // {"path:"","status":1,"error":""}
            return 8 + strlen($path) + 22 + strlen($e->getMessage()) + 2;
        }
        // {"path":"","mtime":0,"size":0,"dir":0},
        return 9 + strlen($path) + 10 + cloner_int_len($stat->getMTime()) + 8 + cloner_int_len($stat->getSize()) + 7 + 1 + 2;
    }
}

class ClonerStaticVisitor extends ClonerVisitor {
    /**
     * @see cloner_globs_to_regexp for available patterns.
     * @var string[]
     */
    public $includeList = array('*');

    /**
     * Filters used to skip files in their respected locations.
     * @see cloner_globs_to_regexp for available patterns.
     * @var array[]
     */
    private $excludeLists = array(
        // File name filters, check only files by name, the rest of the path is ignored.
        self::ANY_DIR        => array('.svn', '.cvs', '.idea', '.DS_Store', '.git', '.hg', '*.hprof', '*.pyc',
            '404.log.txt', '.ftpquota', '.listing', '.mt_backup*', 'timthumb.txt', '.tweetcache', '.wpress', '.tmp',
            '.lwbak', '.X1-unix', 'process.log', 'errors.log', '.pf_debug_output.txt', 'php-errors.log',
            '.sass-cache', 'cgi-bin', 'debug.log', 'error.log', 'php-cgi.core', 'core.[0-9+]',
            'log.txt', 'php_errorlog', 'php_mail.log', 'sess[0-9a-z*32]', 'timthumb[0-9a-z*]',
            'vp-uploaded-restore-*', 'wc-logs', 'WS_FTP.LOG', '*-wprbackups', '_private', '_sucuribackup*',
            '_vti_[a-z*]', 'node_modules', '__MACOSX',
            '{backup,snapshot,restore,akeebabackupwp}.{tar,gz,zip,rar,7z,jpa,sql}', 'pclzip-*'),
        self::ONLY_FILE      => array('error_log'),
        self::ONLY_DIR       => array(),
    );

    /**
     * ClonerStatCollector constructor.
     *
     * @param ClonerStatResult $result
     * @param int              $maxCount
     * @param int              $maxPayload
     * @param int              $timeout
     * @param string[]         $skipPaths
     * @param bool             $transferAll
     *
     * @throws ClonerException
     */
    public function __construct(ClonerStatResult $result, $maxCount, $maxPayload, $timeout, array $skipPaths, $transferAll)
    {
        $this->result       = $result;
        $this->maxCount     = $maxCount;
        $this->maxPayload   = $maxPayload;
        $this->deadline     = $timeout ? time() + $timeout : 0;
        $this->skipPaths    = $skipPaths;
        $this->transferAll  = $transferAll;
        $this->build();
    }

    private function build()
    {
        $delimiter                       = '{}';
        $this->compiledIncludeList       = '{^'.cloner_globs_to_regexp('', $this->includeList, $delimiter).'(?:$|/)}i';
        if ($this->skipPaths) {
            $this->compiledUserExcludeList = '{^'.cloner_globs_to_regexp('', $this->skipPaths, $delimiter).'(?:$|/)}i';
        }
        if ($this->transferAll) {
            return;
        }
        $this->compiledSystemExcludeList = '{(?:'
            .'(?:^|/)'.cloner_globs_to_regexp('', $this->excludeLists[self::ANY_DIR], $delimiter).'(?:$|/))}i';

        if ($this->excludeLists[self::ONLY_FILE]) {
            $this->compiledFileExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_FILE], $delimiter).'$}i';
        }
        if ($this->excludeLists[self::ONLY_DIR]) {
            $this->compiledDirExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_DIR], $delimiter).'(?:$|/)}i';
        }
    }
}

class ClonerWordPressVisitor implements ClonerFSVisitor
{
    public $result;
    private $maxCount = 0;
    private $maxPayload = 0;
    private $deadline = 0;
    private $contentDir = '';
    private $pluginsDir = '';
    private $muPluginsDir = '';
    private $uploadsDir = '';
    private $skipPaths = array();
    private $transferAll = false;

    private $payload = 0;

    const ANY_DIR = '';
    const ONLY_FILE = 'file';
    const ONLY_DIR = 'dir';
    const CONTENT_DIR = 'wp-content';
    const PLUGINS_DIR = 'wp-content/plugins';
    const MU_PLUGINS_DIR = 'wp-content/mu-plugins';
    const UPLOADS_DIR = 'wp-content/uploads';

    /** @var string[] Core files are matched by their full path and are invisible to the process. */
    private static $ignoreFiles = array('cloner.php', 'cloner_error_log', 'mwp_db');

    /**
     * @see cloner_globs_to_regexp for available patterns.
     * @var string[]
     */
    private $includeList = array(
        // Core WP files.
        //'wp-admin', 'wp-admin/*', 'wp-includes', 'wp-includes/*', 'index.php', 'license.txt', 'readme.html',
        //'wp-activate.php', 'wp-blog-header.php', 'wp-comments-post.php', 'wp-config.php', 'wp-config-sample.php', 'wp-cron.php', 'wp-links-opml.php',
        //'wp-load.php', 'wp-login.php', 'wp-mail.php', 'wp-settings.php', 'wp-signup.php', 'wp-trackback.php', 'xmlrpc.php',
        // Extra files.
        '*.ico', 'robots.txt', 'gd-config.php', '.htaccess',
        // Backup v1 rule:
        'wp-*', 'index.php', 'license.txt', 'readme.html', 'xmlrpc.php',
    );

    /**
     * Filters used to skip files in their respected locations.
     * @see cloner_globs_to_regexp for available patterns.
     * @var array[]
     */
    private $excludeLists = array(
        // File name filters, check only files by name, the rest of the path is ignored.
        self::ANY_DIR        => array('.svn', '.cvs', '.idea', '.DS_Store', '.git', '.hg', '*.hprof', '*.pyc',
            '404.log.txt', '.ftpquota', '.listing', '.mt_backup*', 'timthumb.txt', '.tweetcache', '.wpress', '.tmp',
            '.lwbak', '.X1-unix', 'process.log', 'errors.log', '.pf_debug_output.txt', 'php-errors.log',
            '.sass-cache', '__wpe_admin_ajax.log', 'cgi-bin', 'debug.log', 'error.log', 'php-cgi.core', 'core.[0-9+]',
            'wp-retina-2x.log', 'log.txt', 'php_errorlog', 'php_mail.log', 'sess[0-9a-z*32]', 'timthumb[0-9a-z*]',
            'vp-uploaded-restore-*', 'wc-logs', 'WS_FTP.LOG', '*-wprbackups', '_private', '_sucuribackup*',
            '_vti_[a-z*]', 'node_modules', '__MACOSX', '.sucuriquarantine',
            '{backup,snapshot,restore,akeebabackupwp}.{tar,gz,zip,rar,7z,jpa,sql}', 'pclzip-*'),
        self::ONLY_FILE      => array('error_log'),
        self::ONLY_DIR       => array(),
        // Full path filter for descendents of "wp-content/".
        self::CONTENT_DIR    => array('backupbuddy_backups', 'cmscommander/backups', 'wflogs', 'ithemes-security/backups',
            'mainwp/backup', 'managewp/backups', 'mgm/exports', 'mwpbackups', 'updraft/*.zip', 'LocalGiant_WPF/session_store',
            'security.log', 'bte-wb', 'dml-logs', 'nfwlog', 'mysql.sql', 'backup-[0-9a-z*]', 'backups.*', 'backupwordpress',
            'bps-backup', 'codeguard_backups', 'infinitewp', 'old-cache', 'updraft', 'tCapsule/backups', 'sedlex/backup-scheduler',
            'uploads.zip', 's3bubblebackups', 'w3tc', 'wfcache', 'upgrade', 'wpbackitup_backups', 'wishlist-backup', 'cache-remove',
            'pep-vn', '[0-9a-z*]-backups', 'cache', 'wp-snapshots', 'ics-importer-cache', 'gt-cache', 'backwpup', 'backwpup-*', 'backwpups',
            'CRSbackup', 'backups-dup-pro', 'backupwordpress-[0-9a-f*10]-backups', 'mn-logs'),
        // Full path filter for descendents of "wp-content/plugins/".
        self::PLUGINS_DIR    => array('wordfence/tmp', 'worker/log.html', 'all-in-one-wp-migration/storage', 'easywplocalhost_migrator/temp',
            'wp-rss-aggregator/log-[0-9+].txt', 'wponlinebackup/tmp', 'wptouch-data/infinity-cache', 'si-captcha-for-wordpress/captcha/cache',
            'wp-content/mu-plugins/gd-system-plugin*' /*dir and .php file*/),
        // Full path filter for descendents of "wp-content/mu-plugins/".
        self::MU_PLUGINS_DIR => array('wpe-wp-sign-on-plugin*', 'wpengine-common', 'endurance-page-cache'), // PTGS-512
        // Full path filter for descendents of "wp-content/uploads/".
        self::UPLOADS_DIR    => array('aiowps_backups', '*-backups', 'broken-link-checker', 'mainwp', 'backupbuddy_temp',
            'snapshots', 'wp-clone', 'sucuri', 'wp_system', 'wpcf7_captcha', 'wpallimport/uploads', 'bfi_thumb', 'wp-clone', 'essb_cache', 'ewpt_cache',
            'wp-migrate-db', 'wp-backup-plus', 'tCapsule/backups', 'awb', 'essb_cache', 'abovethefold', 'ninja-forms/tmp', 'backupwordpress.*',
            'backupbuddy*', 'pb_backupbuddy', 'backwpup', 'backwpup-*', 'backwpups', 'pp/cache', 'cache', 'elementor/tmp'),
    );

    /** @var string */
    private $compiledIncludeList = '';
    /** @var string */
    private $compiledSystemExcludeList = '';
    /** @var string */
    private $compiledUserExcludeList = '';
    /** @var string */
    private $compiledFileExcludeList = '';
    /** @var string */
    private $compiledDirExcludeList = '';

    public $prefix = '';

    /**
     * ClonerStatCollector constructor.
     *
     * @param ClonerStatResult $result
     * @param int              $maxCount
     * @param int              $maxPayload
     * @param int              $timeout
     * @param string           $contentDir
     * @param string           $pluginsDir
     * @param string           $muPluginsDir
     * @param string           $uploadsDir
     * @param string[]         $addPaths
     * @param string[]         $skipPaths
     * @param bool             $transferAll
     *
     * @throws ClonerException
     */
    public function __construct(ClonerStatResult $result, $maxCount, $maxPayload, $timeout, $contentDir, $pluginsDir, $muPluginsDir, $uploadsDir, array $addPaths, array $skipPaths, $transferAll)
    {
        if (strlen($contentDir) === 0 || strlen($pluginsDir) === 0 || strlen($muPluginsDir) === 0 || strlen($uploadsDir) === 0 ||
            cloner_is_path_absolute($contentDir) || cloner_is_path_absolute($pluginsDir) || cloner_is_path_absolute($muPluginsDir) || cloner_is_path_absolute($uploadsDir)) {
            throw new ClonerException('WordPress dir is not relative to root', 'stat_error');
        }
        foreach ($addPaths as $path) {
            $this->includeList[] = $path;
        }
        $this->result       = $result;
        $this->maxCount     = $maxCount;
        $this->maxPayload   = $maxPayload;
        $this->deadline     = $timeout ? time() + $timeout : 0;
        $this->contentDir   = $contentDir;
        $this->pluginsDir   = $pluginsDir;
        $this->muPluginsDir = $muPluginsDir;
        $this->uploadsDir   = $uploadsDir;
        $this->skipPaths    = $skipPaths;
        $this->transferAll  = $transferAll;
        $this->build();
    }

    private function build()
    {
        $delimiter                       = '{}';
        $this->compiledIncludeList       = '{^'.cloner_globs_to_regexp('', array_merge($this->includeList, array(
                $this->contentDir, $this->pluginsDir, $this->muPluginsDir, $this->uploadsDir)), $delimiter).'(?:$|/)}i';
        if ($this->skipPaths) {
            $this->compiledUserExcludeList = '{^'.cloner_globs_to_regexp('', $this->skipPaths, $delimiter).'(?:$|/)}i';
        }
        if ($this->transferAll) {
            return;
        }
        $this->compiledSystemExcludeList = '{(?:'
            .'(?:^|/)'.cloner_globs_to_regexp('', $this->excludeLists[self::ANY_DIR], $delimiter).'$|'
            .'^'.cloner_globs_to_regexp($this->contentDir.'/', $this->excludeLists[self::CONTENT_DIR], $delimiter).'(?:$|/)|'
            .'^'.cloner_globs_to_regexp($this->pluginsDir.'/', $this->excludeLists[self::PLUGINS_DIR], $delimiter).'(?:$|/)|'
            .'^'.cloner_globs_to_regexp($this->muPluginsDir.'/', $this->excludeLists[self::MU_PLUGINS_DIR], $delimiter).'(?:$|/)|'
            .'^'.cloner_globs_to_regexp($this->uploadsDir.'/', $this->excludeLists[self::UPLOADS_DIR], $delimiter).'(?:$|/))}i';

        if ($this->excludeLists[self::ONLY_FILE]) {
            $this->compiledFileExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_FILE], $delimiter).'$}i';
        }
        if ($this->excludeLists[self::ONLY_DIR]) {
            $this->compiledDirExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_DIR], $delimiter).'(?:$|/)}i';
        }
    }

    public function visit($path, ClonerStatInfo $stat, Exception $e = null)
    {
        // Core files should be invisible to the collector.
        if (in_array($path, self::$ignoreFiles)) {
            throw new ClonerSkipVisitException();
        }

        if (strlen($path) === 0) {
            $fullPath = $this->prefix;
            if (strlen($fullPath) === 0) {
                $fullPath = '.';
            }
        } elseif (strlen($this->prefix) > 0) {
            $fullPath = $this->prefix."/".$path;
        } else {
            $fullPath = $path;
        }
        $encoded     = false;
        $encodedPath = cloner_encode_non_utf8($fullPath);
        if ($fullPath !== $encodedPath) {
            $fullPath = $encodedPath;
            $encoded  = true;
        }

        if (empty($e)) {
            $status = $this->includePath($fullPath, $stat->isDir());
        } else {
            $status = ClonerStatus::ERROR;
        }

        $len = 0;
        if ($this->maxPayload) {
            $len = $this->payloadLen($path, $status, $stat, $e);
        }

        if (count($this->result->stats)) {
            if ($this->deadline && $this->deadline <= time()) {
                return false;
            }
            if ($this->maxCount && count($this->result->stats) >= $this->maxCount) {
                return false;
            }
            if ($this->maxPayload && $this->payload + $len >= $this->maxPayload) {
                return false;
            }
        }

        $this->payload += $len;
        if ($e !== null) {
            $this->result->appendError($fullPath, $encoded, ClonerStatus::ERROR, $e->getMessage());
        } elseif ($status) {
            $this->result->appendError($fullPath, $encoded, $status);
            throw new ClonerSkipVisitException();
        } else {
            if ($stat->isDir()) {
                $this->result->appendDir($fullPath, $encoded, $stat->getMTime(), $stat->getPermissions());
            } elseif ($stat->isLink()) {
                $this->result->appendLink($fullPath, $encoded, $stat->link, $stat->getPermissions());
            } else {
                $this->result->appendFile($fullPath, $encoded, $stat->getMTime(), $stat->getSize(), $stat->getPermissions());
            }
        }

        return true;
    }

    private function includePath($path, $isDir)
    {
        if ($path === '.') {
            return ClonerStatus::OK;
        }
        if (!preg_match($this->compiledIncludeList, $path)) {
            return ClonerStatus::SKIPPED;
        }
        if (preg_match($this->compiledSystemExcludeList, $path, $m)) {
            return ClonerStatus::SKIPPED;
        }
        if ($this->compiledUserExcludeList && preg_match($this->compiledUserExcludeList, $path)) {
            return ClonerStatus::USER_SKIPPED;
        }
        if ($this->compiledFileExcludeList && !$isDir && preg_match($this->compiledFileExcludeList, $path)) {
            return ClonerStatus::SKIPPED;
        }
        if ($this->compiledDirExcludeList && $isDir && preg_match($this->compiledDirExcludeList, $path)) {
            return ClonerStatus::SKIPPED;
        }
        return ClonerStatus::OK;
    }

    private function payloadLen($path, $status, ClonerStatInfo $stat = null, Exception $e = null)
    {
        if ($status) {
            // {"path":"","status":1}
            return 8 + strlen($path) + 13;
        }
        if ($e !== null) {
            // {"path:"","status":1,"error":""}
            return 8 + strlen($path) + 22 + strlen($e->getMessage()) + 2;
        }
        // {"path":"","mtime":0,"size":0,"dir":0},
        return 9 + strlen($path) + 10 + cloner_int_len($stat->getMTime()) + 8 + cloner_int_len($stat->getSize()) + 7 + 1 + 2;
    }
}

class ClonerDrupalVisitor extends ClonerVisitor
{
    /**
     * @see cloner_globs_to_regexp for available patterns.
     * @var string[]
     */
     public $includeList = array(
        '*.ico', 'robots.txt', 'gd-config.php', '.htaccess', 'index.php', '.ht.router.php', '.editorconfig', '.htaccess.preinstall', 'authorize.php',
        'cron.php',

        'core', 'core/*', 'index.php', 'license.txt', 'readme.html', 'xmlrpc.php', 'modules', 'modules/*', 'profiles', 'profiles/*',
        'sites', 'sites/*', 'themes', 'themes/*', 'vendor', 'vendor/*', 'update.php', 'web.config', 'autoload.php', 'composer.json', 'composer.lock',
        'includes', 'includes/*', 'misc', 'misc/*', 'scripts', 'scripts/*',
    );

    /**
     * Filters used to skip files in their respected locations.
     * @see cloner_globs_to_regexp for available patterns.
     * @var array[]
     */
    private $excludeLists = array(
        // File name filters, check only files by name, the rest of the path is ignored.
        self::ANY_DIR        => array('.svn', '.cvs', '.idea', '.DS_Store', '.git', '.hg', '*.hprof', '*.pyc',
            '404.log.txt', '.ftpquota', '.listing', '.mt_backup*', 'timthumb.txt', '.tweetcache', '.wpress', '.tmp',
            '.lwbak', '.X1-unix', 'process.log', 'errors.log', '.pf_debug_output.txt', 'php-errors.log',
            '.sass-cache', 'cgi-bin', 'debug.log', 'error.log', 'php-cgi.core', 'core.[0-9+]',
            'log.txt', 'php_errorlog', 'php_mail.log', 'sess[0-9a-z*32]', 'timthumb[0-9a-z*]',
            'vp-uploaded-restore-*', 'wc-logs', 'WS_FTP.LOG', '*-wprbackups', '_private', '_sucuribackup*',
            '_vti_[a-z*]', 'node_modules', '__MACOSX',
            '{backup,snapshot,restore,akeebabackupwp}.{tar,gz,zip,rar,7z,jpa,sql}', 'pclzip-*'),
        self::ONLY_FILE      => array('error_log'),
        self::ONLY_DIR       => array(),
    );

    /**
     * ClonerStatCollector constructor.
     *
     * @param ClonerStatResult $result
     * @param int              $maxCount
     * @param int              $maxPayload
     * @param int              $timeout
     * @param string[]         $addPaths
     * @param string[]         $skipPaths
     * @param bool             $transferAll
     *
     * @throws ClonerException
     */
    public function __construct(ClonerStatResult $result, $maxCount, $maxPayload, $timeout, array $addPaths, array $skipPaths, $transferAll)
    {
        foreach ($addPaths as $path) {
            $this->includeList[] = $path;
        }
        $this->result       = $result;
        $this->maxCount     = $maxCount;
        $this->maxPayload   = $maxPayload;
        $this->deadline     = $timeout ? time() + $timeout : 0;
        $this->skipPaths    = $skipPaths;
        $this->transferAll  = $transferAll;
        $this->build();
    }

    private function build()
    {
        $delimiter                       = '{}';
        $this->compiledIncludeList       = '{^'.cloner_globs_to_regexp('', $this->includeList, $delimiter).'(?:$|/)}i';
        if ($this->skipPaths) {
            $this->compiledUserExcludeList = '{^'.cloner_globs_to_regexp('', $this->skipPaths, $delimiter).'(?:$|/)}i';
        }
        if ($this->transferAll) {
            return;
        }
        $this->compiledSystemExcludeList = '{(?:'
            .'(?:^|/)'.cloner_globs_to_regexp('', $this->excludeLists[self::ANY_DIR], $delimiter).'(?:$|/))}i';
        if ($this->excludeLists[self::ONLY_FILE]) {
            $this->compiledFileExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_FILE], $delimiter).'$}i';
        }
        if ($this->excludeLists[self::ONLY_DIR]) {
            $this->compiledDirExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_DIR], $delimiter).'(?:$|/)}i';
        }
    }
}

class ClonerJoomlaVisitor extends ClonerVisitor
{
    /**
     * @see cloner_globs_to_regexp for available patterns.
     * @var string[]
     */
    public $includeList = array(
        '*.ico', 'robots.txt', 'gd-config.php', '.htaccess',

        'administrator', '*administrator', 'bin', '*cli', 'components', '*components', 'images', '*images', 'includes', '*includes', 'language', '*language',
        'layouts', '*layouts', 'libraries', '*libraries', 'media', '*media', 'modules', '*modules', 'plugins', '*plugins', 'templates', '*templates',
        'tmp', '*tmp',

        'configuration.php', 'web.config.txt', 'index.php', '.htaccess.preinstall',
    );

    /**
     * Filters used to skip files in their respected locations.
     * @see cloner_globs_to_regexp for available patterns.
     * @var array[]
     */
    private $excludeLists = array(
        // File name filters, check only files by name, the rest of the path is ignored.
        self::ANY_DIR        => array('.svn', '.cvs', '.idea', '.DS_Store', '.git', '.hg', '*.hprof', '*.pyc',
            '404.log.txt', '.ftpquota', '.listing', '.mt_backup*', 'timthumb.txt', '.tweetcache', '.tmp',
            '.lwbak', '.X1-unix', 'process.log', 'errors.log', '.pf_debug_output.txt', 'php-errors.log',
            '.sass-cache', 'cgi-bin', 'debug.log', 'error.log', 'php-cgi.core', 'core.[0-9+]',
            'log.txt', 'php_errorlog', 'php_mail.log', 'sess[0-9a-z*32]', 'timthumb[0-9a-z*]',
            'vp-uploaded-restore-*', 'wc-logs', 'WS_FTP.LOG', '*-wprbackups', '_private', '_sucuribackup*',
            '_vti_[a-z*]', 'node_modules', '__MACOSX',
            '{backup,snapshot,restore,akeebabackupwp}.{tar,gz,zip,rar,7z,jpa,sql}', 'pclzip-*'),
        self::ONLY_FILE      => array('error_log'),
        self::ONLY_DIR       => array(),
    );

    /**
     * ClonerStatCollector constructor.
     *
     * @param ClonerStatResult $result
     * @param int              $maxCount
     * @param int              $maxPayload
     * @param int              $timeout
     * @param string[]         $addPaths
     * @param string[]         $skipPaths
     * @param bool             $transferAll
     *
     * @throws ClonerException
     */
    public function __construct(ClonerStatResult $result, $maxCount, $maxPayload, $timeout, array $addPaths, array $skipPaths, $transferAll)
    {
        foreach ($addPaths as $path) {
            $this->includeList[] = $path;
        }
        $this->result       = $result;
        $this->maxCount     = $maxCount;
        $this->maxPayload   = $maxPayload;
        $this->deadline     = $timeout ? time() + $timeout : 0;
        $this->skipPaths    = $skipPaths;
        $this->transferAll  = $transferAll;
        $this->build();
    }

    private function build()
    {
        $delimiter                       = '{}';
        $this->compiledIncludeList       = '{^'.cloner_globs_to_regexp('', $this->includeList, $delimiter).'(?:$|/)}i';
        if ($this->skipPaths) {
            $this->compiledUserExcludeList = '{^'.cloner_globs_to_regexp('', $this->skipPaths, $delimiter).'(?:$|/)}i';
        }
        if ($this->transferAll) {
            return;
        }
        $this->compiledSystemExcludeList = '{(?:'
            .'(?:^|/)'.cloner_globs_to_regexp('', $this->excludeLists[self::ANY_DIR], $delimiter).'(?:$|/))}i';
        if ($this->excludeLists[self::ONLY_FILE]) {
            $this->compiledFileExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_FILE], $delimiter).'$}i';
        }
        if ($this->excludeLists[self::ONLY_DIR]) {
            $this->compiledDirExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_DIR], $delimiter).'(?:$|/)}i';
        }
    }
}

class ClonerMagentoVisitor extends ClonerVisitor
{
    const VAR_DIR = 'var';

    /**
     * @see cloner_globs_to_regexp for available patterns.
     * @var string[]
     */
    public $includeList = array(
        '*.ico', 'robots.txt', 'gd-config.php', '.htaccess', 'composer.json', 'composer.lock', 'CHANGELOG.md', '*.sample',
        '.htaccess.preinstall', '.editorconfig', 'get.php', 'install.php', 'cron.sh', 'crone.php', 'mage', 'api.php',

        'app', 'app/*', 'bin', 'bin/*', 'dev', 'dev/*', 'generated', 'generated/*', 'lib', 'lib/*', 'phpserver', 'phpserver/*',
        'pub', 'pub/*', 'setup', 'setup/*', 'update', 'update/*', 'var', 'var/*', 'vendor', 'vendor/*', 'downloader', 'downloader/*',
        'includes', 'includes/*', 'js', 'js/*', 'media', 'media/*', 'shell', 'shell/*', 'skin', 'skin/*', 'errors', 'errors/*',
        'index.php', 'security.md', 'LICENSE.txt', '.user.ini',
    );

    /**
     * Filters used to skip files in their respected locations.
     * @see cloner_globs_to_regexp for available patterns.
     * @var array[]
     */
    private $excludeLists = array(
        // File name filters, check only files by name, the rest of the path is ignored.
        self::ANY_DIR        => array('.svn', '.cvs', '.idea', '.DS_Store', '.git', '.hg', '*.hprof', '*.pyc',
            '404.log.txt', '.ftpquota', '.listing', '.mt_backup*', 'timthumb.txt', '.tweetcache', '.tmp',
            '.lwbak', '.X1-unix', 'process.log', 'errors.log', '.pf_debug_output.txt', 'php-errors.log',
            '.sass-cache', 'cgi-bin', 'debug.log', 'error.log', 'php-cgi.core', 'core.[0-9+]',
            'log.txt', 'php_errorlog', 'php_mail.log', 'sess[0-9a-z*32]', 'timthumb[0-9a-z*]',
            'vp-uploaded-restore-*', 'wc-logs', 'WS_FTP.LOG', '*-wprbackups', '_private', '_sucuribackup*',
            '_vti_[a-z*]', 'node_modules', '__MACOSX',
            '{backup,snapshot,restore,akeebabackupwp}.{tar,gz,zip,rar,7z,jpa,sql}', 'pclzip-*'),
        self::ONLY_FILE      => array('error_log'),
        self::ONLY_DIR       => array(),
        // Full path filter for descendents of "var/".
        self::VAR_DIR       => array('cache', 'report', 'log', 'page_cache', 'session'),
    );

    /**
     * ClonerStatCollector constructor.
     *
     * @param ClonerStatResult $result
     * @param int              $maxCount
     * @param int              $maxPayload
     * @param int              $timeout
     * @param string[]         $addPaths
     * @param string[]         $skipPaths
     * @param bool             $transferAll
     *
     * @throws ClonerException
     */
    public function __construct(ClonerStatResult $result, $maxCount, $maxPayload, $timeout, array $addPaths, array $skipPaths, $transferAll)
    {
        foreach ($addPaths as $path) {
            $this->includeList[] = $path;
        }
        $this->result       = $result;
        $this->maxCount     = $maxCount;
        $this->maxPayload   = $maxPayload;
        $this->deadline     = $timeout ? time() + $timeout : 0;
        $this->skipPaths    = $skipPaths;
        $this->transferAll  = $transferAll;
        $this->build();
    }

    private function build()
    {
        $delimiter                       = '{}';
        $this->compiledIncludeList       = '{^'.cloner_globs_to_regexp('', $this->includeList, $delimiter).'(?:$|/)}i';
        if ($this->skipPaths) {
            $this->compiledUserExcludeList = '{^'.cloner_globs_to_regexp('', $this->skipPaths, $delimiter).'(?:$|/)}i';
        }
        if ($this->transferAll) {
            return;
        }
        $this->compiledSystemExcludeList = '{(?:'
            .'(?:^|/)'.cloner_globs_to_regexp('', $this->excludeLists[self::ANY_DIR], $delimiter).'(?:$|/))}i';
        if ($this->excludeLists[self::ONLY_FILE]) {
            $this->compiledFileExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_FILE], $delimiter).'$}i';
        }
        if ($this->excludeLists[self::ONLY_DIR]) {
            $this->compiledDirExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_DIR], $delimiter).'(?:$|/)}i';
        }
    }
}

class ClonerPhpBBVisitor extends ClonerVisitor
{
    /**
     * @see cloner_globs_to_regexp for available patterns.
     * @var string[]
     */
    public $includeList = array(
        '*.ico', 'robots.txt', 'gd-config.php', '.htaccess',

        'adm', 'adm/*', 'assets', 'assests/*', 'bin', 'bin/*', 'dev', 'dev/*', 'config', 'config/*', 'docs', 'docs/*', 'download', 'download/*',
        'ext', 'ext/*', 'files', 'files/*', 'images', 'images/*', 'includes', 'includes/*', 'language', 'language/*',
        'phpbb', 'phpbb/*', 'store', 'store/*', 'styles', 'styles/*', 'vendor', 'vendor/*',

        'app.php', 'common.php', 'config.php', 'cron.php', 'faq.php', 'feed.php', 'index.php', 'layout-styles.css', 'mcp.php',
        'memberlist.php', 'posting.php', 'report.php', 'search.php', 'ucp.php', 'viewforum.php', 'viewonline.php', 'viewtopic.php',
        'web.config',
        );

    /**
     * Filters used to skip files in their respected locations.
     * @see cloner_globs_to_regexp for available patterns.
     * @var array[]
     */
    private $excludeLists = array(
        // File name filters, check only files by name, the rest of the path is ignored.
        self::ANY_DIR        => array('.svn', '.cvs', '.idea', '.DS_Store', '.git', '.hg', '*.hprof', '*.pyc',
            '404.log.txt', '.ftpquota', '.listing', '.mt_backup*', 'timthumb.txt', '.tweetcache', '.tmp',
            '.lwbak', '.X1-unix', 'process.log', 'errors.log', '.pf_debug_output.txt', 'php-errors.log',
            '.sass-cache', 'cgi-bin', 'debug.log', 'error.log', 'php-cgi.core', 'core.[0-9+]',
             'log.txt', 'php_errorlog', 'php_mail.log', 'sess[0-9a-z*32]', 'timthumb[0-9a-z*]',
            'vp-uploaded-restore-*', 'wc-logs', 'WS_FTP.LOG', '*-wprbackups', '_private', '_sucuribackup*',
            '_vti_[a-z*]', 'node_modules', '__MACOSX',
            '{backup,snapshot,restore,akeebabackupwp}.{tar,gz,zip,rar,7z,jpa,sql}', 'pclzip-*'),
        self::ONLY_FILE      => array('error_log'),
        self::ONLY_DIR       => array(),
    );

    /**
     * ClonerStatCollector constructor.
     *
     * @param ClonerStatResult $result
     * @param int              $maxCount
     * @param int              $maxPayload
     * @param int              $timeout
     * @param string[]         $addPaths
     * @param string[]         $skipPaths
     * @param bool             $transferAll
     *
     * @throws ClonerException
     */
    public function __construct(ClonerStatResult $result, $maxCount, $maxPayload, $timeout, array $addPaths, array $skipPaths, $transferAll)
    {
        foreach ($addPaths as $path) {
            $this->includeList[] = $path;
        }
        $this->result       = $result;
        $this->maxCount     = $maxCount;
        $this->maxPayload   = $maxPayload;
        $this->deadline     = $timeout ? time() + $timeout : 0;
        $this->skipPaths    = $skipPaths;
        $this->transferAll  = $transferAll;
        $this->build();
    }

    private function build()
    {
        $delimiter                       = '{}';
        $this->compiledIncludeList       = '{^'.cloner_globs_to_regexp('', $this->includeList, $delimiter).'(?:$|/)}i';
        if ($this->skipPaths) {
            $this->compiledUserExcludeList = '{^'.cloner_globs_to_regexp('', $this->skipPaths, $delimiter).'(?:$|/)}i';
        }
        if ($this->transferAll) {
            return;

        }
        $this->compiledSystemExcludeList = '{(?:'
            .'(?:^|/)'.cloner_globs_to_regexp('', $this->excludeLists[self::ANY_DIR], $delimiter).'(?:$|/))}i';
        if ($this->excludeLists[self::ONLY_FILE]) {
            $this->compiledFileExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_FILE], $delimiter).'$}i';
        }
        if ($this->excludeLists[self::ONLY_DIR]) {
            $this->compiledDirExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_DIR], $delimiter).'(?:$|/)}i';
        }
    }
}

class ClonerVBulletinVisitor extends ClonerVisitor
{
    /**
     * @see cloner_globs_to_regexp for available patterns.
     * @var string[]
     */
    public $includeList = array(
        '*.ico', 'robots.txt', 'gd-config.php', '.htaccess', 'index.php', 'config.php', 'web.config',

        'admincp', 'admincp/*', 'core', 'core/*', 'css', 'css/*', 'fonts', 'fonts/*', 'forumrunner', 'forumrunner/*',
        'images', 'images/*', 'includes', 'includes/*', 'js', 'js/*',
    );

    /**
     * Filters used to skip files in their respected locations.
     * @see cloner_globs_to_regexp for available patterns.
     * @var array[]
     */
    private $excludeLists = array(
        // File name filters, check only files by name, the rest of the path is ignored.
        self::ANY_DIR        => array('.svn', '.cvs', '.idea', '.DS_Store', '.git', '.hg', '*.hprof', '*.pyc',
            '404.log.txt', '.ftpquota', '.listing', '.mt_backup*', 'timthumb.txt', '.tweetcache', '.tmp',
            '.lwbak', '.X1-unix', 'process.log', 'errors.log', '.pf_debug_output.txt', 'php-errors.log',
            '.sass-cache', 'cgi-bin', 'debug.log', 'error.log', 'php-cgi.core', 'core.[0-9+]',
            'log.txt', 'php_errorlog', 'php_mail.log', 'sess[0-9a-z*32]', 'timthumb[0-9a-z*]',
            'vp-uploaded-restore-*', 'wc-logs', 'WS_FTP.LOG', '_private', '_sucuribackup*',
            '_vti_[a-z*]', 'node_modules', '__MACOSX',
            '{backup,snapshot,restore,akeebabackupwp}.{tar,gz,zip,rar,7z,jpa,sql}', 'pclzip-*'),
        self::ONLY_FILE      => array('error_log'),
        self::ONLY_DIR       => array(),
    );

    /**
     * ClonerStatCollector constructor.
     *
     * @param ClonerStatResult $result
     * @param int              $maxCount
     * @param int              $maxPayload
     * @param int              $timeout
     * @param string[]         $addPaths
     * @param string[]         $skipPaths
     * @param bool             $transferAll
     *
     * @throws ClonerException
     */
    public function __construct(ClonerStatResult $result, $maxCount, $maxPayload, $timeout, array $addPaths, array $skipPaths, $transferAll)
    {
        foreach ($addPaths as $path) {
            $this->includeList[] = $path;
        }
        $this->result       = $result;
        $this->maxCount     = $maxCount;
        $this->maxPayload   = $maxPayload;
        $this->deadline     = $timeout ? time() + $timeout : 0;
        $this->skipPaths    = $skipPaths;
        $this->transferAll  = $transferAll;
        $this->build();
    }

    private function build()
    {
        $delimiter                       = '{}';
        $this->compiledIncludeList       = '{^'.cloner_globs_to_regexp('', $this->includeList, $delimiter).'(?:$|/)}i';
        if ($this->skipPaths) {
            $this->compiledUserExcludeList = '{^'.cloner_globs_to_regexp('', $this->skipPaths, $delimiter).'(?:$|/)}i';
        }
        if ($this->transferAll) {
            return;
        }
        $this->compiledSystemExcludeList = '{(?:'
            .'(?:^|/)'.cloner_globs_to_regexp('', $this->excludeLists[self::ANY_DIR], $delimiter).'(?:$|/))}i';
        if ($this->excludeLists[self::ONLY_FILE]) {
            $this->compiledFileExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_FILE], $delimiter).'$}i';
        }
        if ($this->excludeLists[self::ONLY_DIR]) {
            $this->compiledDirExcludeList = '{^'.cloner_globs_to_regexp('', $this->excludeLists[self::ONLY_DIR], $delimiter).'(?:$|/)}i';
        }
    }
}

/**
 * @param int $int
 *
 * @return int
 */
function cloner_int_len($int)
{
    return (int)floor(log10($int)) + 1;
}

/**
 * @property string $path        File path, may not be UTF-8.
 * @property string $path64      Base64-encoded path.
 * @property int    $size        File size.
 * @property int    $dir         Directory if $dir===1.
 * @property int    $mtime       File modification time.
 * @property int    $offset      Offset for r/w ops.
 * @property string $hash        Full file content hash.
 * @property string $hashes      Transient hashes.
 * @property int    $status      Op status, defaults to ClonerStatus::OK.
 * @property string $error       Op error, when $status === ClonerStatus::ERROR.
 * @property string $data        Op data.
 * @property string $data64      Base64-encoded op data.
 * @property bool   $eof         End-of-file if true.
 * @property array  $result      Underlying data structure.
 * @property int    $written     Upstream/downstream report.
 * @property bool   $isLink      Symlink if $isLink===1.
 * @property string $link        Symlink path reference.
 * @property int    $perms       Permissions.
 */
class ClonerFileInfo
{
    private $file;

    public function __construct(array $file)
    {
        $this->file = $file;
    }

    /**
     * @param $name
     *
     * @return int|string|array
     *
     * @throws ClonerException
     */
    public function __get($name)
    {
        switch ($name) {
            case 'path':
                if (!empty($this->file['encoded'])) {
                    return urldecode(base64_decode($this->file['path']));
                }
                return base64_decode($this->file['path']);
            case 'path64':
                if (!empty($this->file['encoded'])) {
                    return urldecode($this->file['path']);
                }
                return $this->file['path'];
            case 'size':
                return $this->file['size'];
            case 'dir':
                return $this->file['type'] === 1;
            case 'isLink':
                return $this->file['type'] === 2;
            case 'link':
                return isset($this->file['link64']) ? base64_decode($this->file['link64']) : '';
            case 'mtime':
                return $this->file['mtime'];
            case 'offset':
                return isset($this->file['offset']) ? $this->file['offset'] : 0;
            case 'hash':
                return isset($this->file['hash']) ? $this->file['hash'] : '';
            case 'hashes':
                return isset($this->file['hashes']) ? $this->file['hashes'] : '';
            case 'status':
                return isset($this->file['status']) ? $this->file['status'] : 0;
            case 'error':
                return isset($this->file['error']) ? $this->file['error'] : '';
            case 'data':
                return isset($this->file['data64']) ? base64_decode($this->file['data64']) : '';
            case 'data64':
                return isset($this->file['data64']) ? $this->file['data64'] : '';
            case 'written':
                return isset($this->file['written']) ? $this->file['written'] : 0;
            case 'eof':
                return isset($this->file['eof']) ? $this->file['eof'] : false;
            case 'result':
                return $this->file;
            default:
                throw new ClonerException("Unrecognized file property: $name");
        }
    }
}

/**
 * @param string $action Action name.
 * @param array  $params Action parameters.
 *
 * @return mixed Whatever is returned by the action called.
 *
 * @throws ClonerException
 */
function cloner_execute_action($action, array $params)
{
    switch ($action = (string)$action) {
        case 'ping':
            return cloner_action_ping();
        case 'stat':
            return cloner_action_stat($params['root'], $params['cursor'], $params['cursorEncoded'], $params['maxCount'], $params['maxPayload'], $params['cms'], $params['options'], $params['timeout'], $params['addPaths'], $params['skipPaths'], $params['transferAll']);
        case 'hash':
            return cloner_action_hash($params['root'], $params['files'], $params['tempHashes'], $params['timeout'], $params['maxHashSize'], $params['chunkSize'], $params['hashBufSize']);
        case 'touch':
            return cloner_action_touch($params['root'], $params['files'], $params['timeout']);
        case 'read':
            return cloner_action_read($params['root'], $params['id'], $params['files'], $params['lastOffset'], $params['limit']);
        case 'write':
            return cloner_action_write($params['root'], $params['files'], $params['lastOffset']);
        case 'push':
            return cloner_action_push($params['root'], $params['remoteRoot'], $params['id'], $params['remoteID'], $params['files'], $params['url'], $params['lastOffset'], $params['limit']);
        case 'pull':
            return cloner_action_pull($params['root'], $params['remoteRoot'], $params['remoteID'], $params['files'], $params['url'], $params['lastOffset'], $params['limit']);
        case 'list_tables':
            return cloner_action_list_tables($params['db']);
        case 'hash_tables':
            return cloner_action_hash_tables($params['db'], $params['tables'], $params['timeout']);
        case 'dump_tables':
            return cloner_action_dump_tables($params['root'], $params['id'], $params['db'], $params['state'], $params['timeout'], $params['stream']);
        case 'delete_files':
            return cloner_action_delete_files($params['root'], $params['files'], $params['id'], $params['errorLogSize']);
        case 'flush_rewrite_rules':
            return cloner_action_flush_rewrite_rules($params['db'], $params['id'], $params['prefix'], $params['activateWorker'], $params['timeout'], $params['isOriginal']);
        case 'heartbeat':
            return cloner_action_heartbeat($params['db'], $params['prefix'], $params['id']);
        case 'import_database':
            return cloner_action_import_database($params['root'], $params['db'], $params['state'], $params['oldPrefix'], $params['newPrefix'], $params['maxCount'], $params['timeout']);
        case 'set_admin':
            return cloner_action_set_admin($params['db'], $params['prefix'], $params['username'], $params['password'], $params['email']);
        case 'migrate_database':
            return cloner_action_migrate_database($params['db'], $params['cms'], $params['timeout'], $params['state']);
        case 'connect_back':
            return cloner_action_connect_back($params['addr'], $params['path'], $params['origin'], $params['connTimeout'], $params['rwTimeout'], $params['cert']);
        case 'cleanup':
            return cloner_action_cleanup($params['root']);
        case 'get_env':
            return cloner_action_get_env($params['url'], $params['forceCMS'], $params['tablePrefix'], $params['db'], $params['configContent'], $params['readOnly']);
        case 'set_config_options':
            return cloner_action_set_config_options($params['db'], $params['prefix'], $params['options'], $params['cms']);
        case 'write_htaccess':
            return cloner_action_write_htaccess($params['fileName'], $params['root'], $params['content'], $params['performCleanup']);
        default:
            throw new ClonerException("Action \"$action\" not found", 'action_not_found');
    }
}

/** @noinspection SqlNoDataSourceInspection */






function cloner_action_ping()
{
    return "pong";
}

function cloner_action_delete_files($root, array $files, $id, $errorLogSize = 0)
{
    $ok       = true;
    $errs     = "Could not remove the following files:";
    $errorLog = '';
    if ($errorLogSize !== 0) {
        $errorLog = @file_get_contents('cloner_error_log', false, null, 0, $errorLogSize);
        if ($errorLog === false) {
            $errorLog = sprintf('unable to read error log: %s', cloner_last_error_for('file_get_contents'));
        }
    }
    foreach ($files as $file) {
        $err = cloner_remove_file_or_dir($root.'/'.$file);
        if (strlen($err)) {
            $errs .= " $file ($err);";
            $ok   = false;
        }
    }
    if (strlen($id)) {
        $temp = sys_get_temp_dir()."/mwp_db$id";
        $err  = cloner_remove_file_or_dir($temp);
        if (strlen($err)) {
            $errs .= " $temp ($err);";
            $ok   = false;
        }
    }
    return array(
        'ok'       => $ok,
        'error'    => $ok ? null : $errs,
        'errorLog' => base64_encode($errorLog),
    );
}

/**
 * @param string             $root
 * @param ClonerDBConn|array $db
 * @param array              $state
 * @param string             $oldPrefix
 * @param string             $newPrefix
 * @param int                $maxCount
 * @param int                $timeout
 *
 * @return ClonerDBImportState
 *
 * @throws ClonerException
 * @throws ClonerFSFunctionException
 */
function cloner_action_import_database($root, $db, array $state, $oldPrefix, $newPrefix, $maxCount, $timeout)
{
    $conn        = cloner_db_conn($db);
    $state       = ClonerDBImportState::fromArray($state, 10 << 10);
    $filters     = array();
    $lowerPrefix = strtolower($newPrefix);
    if ($oldPrefix !== $newPrefix) {
        $filters[] = new ClonerPrefixFilter($oldPrefix, $newPrefix);
    } elseif ($lowerPrefix !== $newPrefix) {
        // Prefix contains uppercase characters, meaning that if the origin is Windows the tables may actually have
        // lowercase names, since on Windows MySQL internally normalizes them all to lowercase.
        // To be safe, transform lowercase versions of table names (if any exist) to uppercase.
        $filters[] = new ClonerPrefixFilter($lowerPrefix, $newPrefix);
    }
    $env = cloner_env_info($root);
    if ($env->goDaddyPro === 2) {
        $filters[] = new ClonerDBStorageFilter();
    }
    return cloner_import_database($root, $conn, $timeout, $state, $maxCount, $filters);
}

/**
 * @param ClonerDBConn|array $db
 * @param string             $prefix
 * @param string             $id
 *
 * @return array
 * @throws ClonerException
 */
function cloner_action_heartbeat($db, $prefix, $id)
{
    $conn = cloner_db_conn($db);
    $conn->query("INSERT INTO {$prefix}options SET option_name = 'clone_heartbeat_{$id}', option_value = :value ON DUPLICATE KEY UPDATE option_value = :value", array(
        'value' => time(),
    ));

    return array('ok' => true);
}

/**
 * @param ClonerDBConn|array $db
 * @param string             $prefix
 * @param string             $username
 * @param string             $password
 * @param string             $email
 *
 * @return array
 * @throws ClonerException
 */
function cloner_action_set_admin($db, $prefix, $username, $password, $email)
{
    $conn    = cloner_db_conn($db);
    $adminID = cloner_get_user_id_by_username($conn, $prefix, $username);
    $conn->query("UPDATE {$prefix}options SET option_value = :email WHERE option_name = 'admin_email'", array(
        'email' => $email,
    ));
    if ($adminID) {
        $conn->query("UPDATE {$prefix}users SET user_pass = :password, user_email = :email WHERE ID = :user_id", array(
            'user_id'  => $adminID,
            'password' => $password,
            'email'    => $email,
        ));
    } else {
        /** @noinspection SqlDialectInspection */
        $conn->query("INSERT INTO {$prefix}users (user_login, user_pass, user_email, user_nicename, user_registered, display_name)
VALUES (:username, :password, :email, :slug, :now, :display_name)", array(
            'username'     => $username,
            'password'     => $password,
            'email'        => $email,
            'slug'         => cloner_sanitize_user($username),
            'now'          => date('Y-m-d H:i:s'),
            'display_name' => $username,
        ));
        $newID = cloner_get_user_id_by_username($conn, $prefix, $username);
        if (!$newID) {
            throw new ClonerException('Admin user could not be saved to the database.', 'admin_not_saved');
        }
        $options = array(
            $prefix.'capabilities' => serialize(array('administrator' => true)),
            'rich_editing'         => 'true',
            'show_admin_bar_front' => true,
        );
        foreach ($options as $name => $value) {
            /** @noinspection SqlDialectInspection */
            $conn->query("INSERT INTO {$prefix}usermeta SET user_id = :user_id, meta_key = :meta_key, meta_value = :meta_value", array(
                'user_id'    => $newID,
                'meta_key'   => $name,
                'meta_value' => $value,
            ));
        }
    }
    return array();
}

/**
 * @param array|null $db
 * @param string     $prefix
 * @param array      $options
 * @param string     $cms
 * @return array
 * @throws ClonerException
 */
function cloner_action_set_config_options($db, $prefix, array $options, $cms)
{
    switch ($cms) {
        case 'wordpress':
            return cloner_set_wordpress_options($db, $prefix, $options);
        case 'magento':
        case 'magento_one':
            return set_magento_config($db, $prefix, $options);
        case 'phpBB':
            return set_phpBB_config($db, $prefix, $options);
        case 'vbulletin':
            return set_vbulletin_config($db, $prefix, $options);
        default:
            throw new ClonerException("Couldn't change config option. Undefined cms.");
    }
}

/**
 *
 * @param string $fileName
 * @param string $root
 * @param string $content
 * @param bool $performCleanup
 * @return string
 *
 */
function cloner_action_write_htaccess($fileName, $root, $content, $performCleanup)
{
    $error = cloner_write_file($root."/".$fileName, 0, base64_decode($content));
    if (!empty($error)) {
        return false;
    }
    if ($performCleanup) {
        foreach (array($root.'/cloner.php', $root.'/cloner_error_log.php', $root."/mwp_db") as $path) {
            unlink($path);
        }
    }
    return true;
}

/**
 * @param ClonerDBConn|array $db
 * @param string             $cms
 * @param float              $timeout
 * @param array              $state
 *
 * @return array
 * @throws Exception
 */
function cloner_action_migrate_database($db, $cms, $timeout, array $state)
{
    $conn = cloner_db_conn($db);
    foreach ($state as $key => &$migration) {
        if ($migration['done']) {
            continue;
        }
        switch ($migration['fn']) {
            case 'cloner_migrate_table_prefix':
                list($migration['state'], $migration['done']) = cloner_migrate_table_prefix($conn, $timeout, (array)@$migration['args'], @$migration['state'], $cms);
                break;
            case 'cloner_migrate_site_url':
                list($migration['state'], $migration['done']) = cloner_migrate_site_url($conn, $timeout, (array)@$migration['args'], @$migration['state'], $cms);
                break;
            default:
                throw new ClonerException(sprintf('Unknown migration function: %s', $migration['fn']));
        }
        if ($migration['done']) {
            // Timeout reached on a migration.
            break;
        }
    }
    return array('state' => $state);
}

function cloner_return_false()
{
    return false;
}

/**
 * @param string $addr        Address in host:port format.
 * @param string $path        HTTP handshake path.
 * @param string $origin      HTTP handshake "Origin" header value.
 * @param string $connTimeout HTTP connect+handshake timeout.
 * @param string $rwTimeout   Read/write timeout for single actions.
 * @param string $cert        Certificate to use for TLS.
 *
 * @return array Nothing of use.
 *
 * @throws ClonerException
 */
function cloner_action_connect_back($addr, $path, $origin, $connTimeout, $rwTimeout, $cert)
{
    set_time_limit(12 * 3600);
    set_error_handler('cloner_return_false');
    $ws = new ClonerWebSocket($addr, $path, $connTimeout, $rwTimeout, 'localhost', $origin, $cert);
    $ws->connect();
    while (cloner_run_ws_transaction($ws)) {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
    return array();
}

/**
 * Read message from the WebSocket, execute the action, write result.
 * If there was a "2006 MySQL server has gone away" it will additionally clear MySQL connection cache
 * before sending the error.
 *
 * @param ClonerWebSocket $ws
 *
 * @return bool
 *
 * @throws ClonerException
 */
function cloner_run_ws_transaction(ClonerWebSocket $ws)
{
    list($message, $eof) = $ws->readMessage();
    if ($eof) {
        return false;
    }
    $data = json_decode($message, true);
    if ($data === null || $data === false) {
        throw new ClonerException(sprintf("Invalid JSON payload: %s", base64_encode($message)), 'ws_invalid_json', function_exists('json_last_error') ? json_last_error() : 0);
    }
    unset($message);
    $id = $data['id'];
    try {
        $result = cloner_execute_action($data['action'], (array)@$data['params']);
        unset($data);
        $send = json_encode(array('id' => $id, 'result' => $result));
        if ($send === null || $send === false) {
            throw new ClonerException(sprintf("JSON encode error: %s", base64_encode(serialize($result))), 'json_encode_error', function_exists('json_last_error') ? json_last_error() : 0);
        }
    } catch (ClonerException $e) {
        unset($send);
        if ($e->getInternalError() === "2006") {
            // MySQL server has gone away; use hacky new way to clear cache.
            ClonerDBAdapter::closeAll();
        }
        $context = null;
        foreach (get_object_vars($e) as $key => $val) {
            if (!is_scalar($val)) {
                continue;
            }
            if ($context === null) {
                $context = array();
            }
            $context[$key] = (string)$val;
        }
        $send = json_encode(array(
            'id'    => $id,
            'error' => array(
                'error'         => $e->getErrorCode(),
                'message'       => $e->getMessage(),
                'internalError' => $e->getInternalError(),
                'file'          => $e->getFile(),
                'line'          => $e->getLine(),
                'context'       => $context,
            ),
        ));
    }
    $ws->writeMessage($send);
    return true;
}

/**
 * Callback for filtering out dot-files from results of scandir and such.
 *
 * @param string $value
 *
 * @return bool True if the file is NOT a dot-file.
 */
function cloner_is_not_dot($value)
{
    return $value !== '.' && $value !== '..';
}

/**
 * Remove file or directory at $path recursively.
 *
 * @param string $path Path to the file or directory.
 *
 * @return string Error string, or empty string if there was no error.
 */
function cloner_remove_file_or_dir($path)
{
    $attempt     = 0;
    $maxAttempts = 3;
    $err         = '';
    while (true) {
        $attempt++;
        if ($attempt > $maxAttempts) {
            break;
        }
        $err = '';
        if ($attempt > 1) {
            usleep(100000 * pow(2, $attempt));
            cloner_clear_stat_cache($path);
        }
        if (!file_exists($path)) {
            break;
        }
        if (!is_writable($path)) {
            @chmod($path, 0777);
        }
        if (is_dir($path)) {
            foreach (@scandir($path) as $child) {
                if ($child === '.' || $child === '..') {
                    continue;
                }
                if (strlen($err = cloner_remove_file_or_dir("$path/$child"))) {
                    return $err;
                }
            }
            if (@rmdir($path) === false) {
                $err = cloner_last_error_for('rmdir');
                continue;
            }
        } else {
            if (@unlink($path) === false) {
                $err = cloner_last_error_for('unlink');
                continue;
            }
        }
        break;
    }
    return $err;
}

/**
 * @param string $root Absolute path to the website root, where cloner.php (this file) resides.
 *
 * @return array
 */
function cloner_action_cleanup($root)
{
    $errs     = array();
    $errorLog = @file_get_contents("$root/cloner_error_log", false, null, 0, 1 << 20);
    if ($errorLog === false) {
        $errorLog = "could not fetch cloner_error_log: ".cloner_last_error_for('file_get_contents');
    }
    $dumpDir = $root.'/mwp_db';
    $dumps   = is_dir($dumpDir) ? @scandir($dumpDir) : array();
    if (!is_array($dumps)) {
        $dumps  = array();
        $errs[] = array('mwp_db', cloner_last_error_for('scandir'));
    } else {
        $dumps = array_values(array_filter($dumps, 'cloner_is_not_dot'));
        foreach ($dumps as $i => $dump) {
            $dumps[$i] = "mwp_db/$dump";
        }
        // Remove the directory itself.
        $dumps[] = 'mwp_db';
    }
    $files = array_merge($dumps, array('cloner_error_log', 'cloner.php'));
    foreach ($files as $file) {
        $err = cloner_remove_file_or_dir($root.'/'.$file);
        if (strlen($err)) {
            $errs[] = array($file, $err);
        }
    }
    return array(
        'ok'       => empty($errs),
        'errors'   => $errs,
        'errorLog' => $errorLog,
    );
}

class ClonerStatResult
{
    public $stats = array();

    public function appendFile($path, $encoded, $modTime, $size, $permissions)
    {
        $this->stats[] = array('p' => base64_encode($path), 'n' => $encoded, 's' => $size, 'm' => $modTime, 'i' => $permissions);
    }

    public function appendDir($path, $encoded, $modTime, $permissions)
    {
        $this->stats[] = array('p' => base64_encode($path), 'n' => $encoded, 'd' => 1, 'm' => $modTime, 'i' => $permissions);
    }

    public function appendLink($path, $encoded, $reference, $permissions)
    {
        $this->stats[] = array('p' => base64_encode($path), 'n' => $encoded, 'd' => 2, 'k' => base64_encode($reference), 'i' => $permissions);
    }

    public function appendError($path, $encoded, $status, $error = '')
    {
        $this->stats[] = array('p' => base64_encode($path), 'n' => $encoded, 'o' => $status, 'e' => cloner_encode_non_utf8($error));
    }
}

/**
 * Eliminate paths that are located under any other path. For example:
 * - "wp-content" and "changed-content-dir/uploads" are located under the "" (empty, root) path; remove them as they will be traversed
 * - "../wp-content/mu-plugins" and "../../plugins" are located above the "" path; they will be traversed separately
 * - if content dir is "../wp-content" and plugins dir is "../wp-content/plugins", only the first one will be traversed, as the second
 *   one is located under the first one.
 * This will make sure that all WordPress files (including directory roots) are traversed exactly once.
 *
 * @param string   $cursor
 * @param string[] $paths
 *
 * @return string[] Unique paths that should be traversed.
 */
function cloner_remove_nested_paths($cursor, array $paths)
{
    foreach ($paths as $i => $path) {
        if ($path === '') {
            continue;
        }
        foreach ($paths as $checkPath) {
            if ($checkPath === '') {
                if (strncmp($path, '../', 3) !== 0) {
                    // Directory is not going up, hence is located under ''.
                    unset($paths[$i]);
                    break;
                }
                continue;
            }
            if (strncmp($path, $checkPath, strlen($checkPath)) === 0 && substr($path, strlen($checkPath), 1) === '/') {
                unset($paths[$i]);
                break;
            }
        }
    }
    $paths = array_values(array_unique($paths));
    foreach ($paths as $i => $path) {
        $paths[$i] = strtr($path, array('../' => chr(127)));
    }
    sort($paths);
    foreach ($paths as $i => $path) {
        $paths[$i] = strtr($path, array(chr(127) => '../'));
    }

    for ($i = count($paths) - 1; $i > 0; $i--) {
        if (strlen($cursor) && strncmp($paths[$i], $cursor, strlen($paths[$i])) === 0) {
            break;
        }
    }
    $paths = array_slice($paths, $i);

    return $paths;
}

/**
 * @param string   $root
 * @param string   $cursor
 * @param bool     $cursorEncoded
 * @param int      $maxCount
 * @param int      $maxPayload
 * @param string   $cms
 * @param array    $options CMS-specific list options.
 * @param int      $timeout
 * @param string[] $addPaths
 * @param string[] $skipPaths
 * @param bool     $transferAll
 *
 * @return array
 *
 * @throws ClonerFSException
 * @throws ClonerException
 */
function cloner_action_stat($root, $cursor, $cursorEncoded, $maxCount, $maxPayload, $cms, $options, $timeout, array $addPaths = null, array $skipPaths = null, $transferAll = false)
{
    if ($cursorEncoded) {
        // TODO(mcolakovic): fix utf-8 issues on protocol level
        $cursor = urldecode($cursor);
    }
    $addPaths  = (array)$addPaths;
    $skipPaths = (array)$skipPaths;
    // We will always traverse these directories, even if they are above our rooted path.
    // If we are continuing traversal, cursor will start with the next path to be traversed.


    $result = new ClonerStatResult();
    switch ($cms) {
        case 'wordpress':
            $addPaths[] = $options['configPath'];
            $traverse   = cloner_remove_nested_paths($cursor, array('', $options['configPath'], $options['contentDir'], $options['pluginsDir'], $options['muPluginsDir'], $options['uploadsDir']));
            if (isset($traverse[0])) {
                $cursor = substr($cursor, strlen($traverse[0]));
            }
            $visitor = new ClonerWordPressVisitor($result, $maxCount, $maxPayload, $timeout, $options['contentDir'], $options['pluginsDir'], $options['muPluginsDir'], $options['uploadsDir'], $addPaths, $skipPaths, $transferAll);
            break;
        case 'static':
            $traverse = cloner_remove_nested_paths($cursor, array(''));
            if (isset($traverse[0])) {
                $cursor = substr($cursor, strlen($traverse[0]));
            }
            $visitor = new ClonerStaticVisitor($result, $maxCount, $maxPayload, $timeout, $skipPaths, $transferAll);
            break;
        case 'drupal':
            $addPaths[] = $options['configPath'];
            $traverse   = cloner_remove_nested_paths($cursor, array('', $options['configPath']));
            if (isset($traverse[0])) {
                $cursor = substr($cursor, strlen($traverse[0]));
            }
            $visitor = new ClonerDrupalVisitor($result, $maxCount, $maxPayload, $timeout, $addPaths, $skipPaths, $transferAll);
            break;
        case 'joomla':
            $addPaths[] = $options['configPath'];
            $traverse   = cloner_remove_nested_paths($cursor, array('', $options['configPath']));
            if (isset($traverse[0])) {
                $cursor = substr($cursor, strlen($traverse[0]));
            }
            $visitor = new ClonerJoomlaVisitor($result, $maxCount, $maxPayload, $timeout, $addPaths, $skipPaths, $transferAll);
            break;
        case 'magento':
        case 'magento_one':
            $addPaths[] = $options['configPath'];
            $traverse   = cloner_remove_nested_paths($cursor, array('', $options['configPath']));
            if (isset($traverse[0])) {
                $cursor = substr($cursor, strlen($traverse[0]));
            }
            $visitor = new ClonerMagentoVisitor($result, $maxCount, $maxPayload, $timeout, $addPaths, $skipPaths, $transferAll);
            break;
        case 'phpBB':
            $addPaths[] = $options['configPath'];
            $traverse   = cloner_remove_nested_paths($cursor, array('', $options['configPath']));
            if (isset($traverse[0])) {
                $cursor = substr($cursor, strlen($traverse[0]));
            }
            $visitor = new ClonerPhpBBVisitor($result, $maxCount, $maxPayload, $timeout, $addPaths, $skipPaths, $transferAll);
            break;
        case 'vbulletin':
            $addPaths[] = $options['configPath'];
            $traverse   = cloner_remove_nested_paths($cursor, array('', $options['configPath']));
            if (isset($traverse[0])) {
                $cursor = substr($cursor, strlen($traverse[0]));
            }
            $visitor = new ClonerVBulletinVisitor($result, $maxCount, $maxPayload, $timeout, $addPaths, $skipPaths, $transferAll);
            break;
        default:
            throw new ClonerException(sprintf('Unregistered CMS "%s", file listing not available', $cms));
    }
    $newCursor = '';
    foreach ($traverse as $path) {
        $visitor->prefix = $path;
        $rootPath        = rtrim("$root/$path", '/');
        if (strlen($rootPath) === 0) {
            $rootPath = '/';
        }
        $newCursor = cloner_fs_walk($rootPath, $visitor, $cursor, true);
        if (strlen($newCursor) !== 0) {
            // We hit a deadline.
            $newCursor = ltrim("$path/$newCursor", '/');
            break;
        }
        $cursor = '';
        // Cursor is empty, go to next path, or return.
    }
    $newCursorEncoded = cloner_encode_non_utf8($newCursor);
    $cursorEncoded    = false;
    if ($newCursorEncoded !== $newCursor) {
        $newCursor     = $newCursorEncoded;
        $cursorEncoded = true;
    }
    return array(
        'stats'         => $result->stats,
        'cursor'        => $newCursor,
        'cursorEncoded' => $cursorEncoded,
    );
}

class ClonerStatus
{
    const OK = 0;
    const IN_PROGRESS = 1;
    const NO_PARENT = 2;
    const IS_DIR = 3;
    const IS_FILE = 4;
    const SKIPPED = 5;
    const ERROR = 6;
    const REMOTE_ERROR = 7;
    const NOT_UTF8 = 8;
    const NO_FILE = 9;
    const HASH_MISSING = 10;
    const USER_SKIPPED = 11;
}

class ClonerTouchResult
{
    public $files = array();

    public function appendOK()
    {
        // Force default status so it doesn't json_encode into an array.
        $this->files[] = array('s' => 0);
    }

    public function appendError($error)
    {
        $this->files[] = array('e' => $error);
    }
}

function cloner_action_touch($root, array $files, $timeout)
{
    $result   = new ClonerTouchResult();
    $deadline = new ClonerDeadline($timeout);
    foreach ($files as $i => $file) {
        if (count($result->files) !== 0 && $deadline->done()) {
            break;
        }
        $file     = new ClonerFileInfo($file);
        $fullPath = "$root/$file->path";
        if (@touch($fullPath, $file->mtime) === false) {
            $result->appendError(cloner_last_error_for('touch'));
            continue;
        }
        $result->appendOK();
    }
    return $result;
}

class ClonerHashResult
{
    public $hashes = array();
    public $tempHashes = '';

    public function appendOK($hash)
    {
        $this->hashes[] = array('h' => $hash);
    }

    public function appendTempHashes($hashes)
    {
        $this->hashes[]   = array('t' => true);
        $this->tempHashes = $hashes;
    }

    public function appendError($error)
    {
        $this->hashes[] = array('o' => ClonerStatus::ERROR, 'e' => $error);
    }
}

function cloner_action_hash($root, array $files, $tempHashes, $timeout, $maxHashSize, $chunkSize, $hashBufSize)
{
    $hashLen  = 32;
    $result   = new ClonerHashResult();
    $deadline = new ClonerDeadline($timeout);

    foreach ($files as $file) {
        $file = new ClonerFileInfo($file);
        if ($file->dir) {
            $result->appendError('cannot hash dir');
            continue;
        }
        if (count($result->hashes) !== 0 && $deadline->done()) {
            break;
        }
        $hashes     = $tempHashes;
        $tempHashes = '';
        try {
            $filePath = "$root/$file->path";
            $stat     = cloner_fs_stat($filePath);
            if ($stat->getSize() !== $file->size) {
                throw new ClonerException(sprintf("size changed, was %d, now is %d", $file->size, $stat->getSize()));
            }
            $hash = 'd41d8cd98f00b204e9800998ecf8427e'; // md5('')
            if ($file->size === 0) {
                // Zero-length file.
                $result->appendOK($hash);
                continue;
            } elseif ($file->size <= $maxHashSize) {
                // Single-chunk file.
                $hash = md5_file($filePath);
                if ($hash === false) {
                    $result->appendError(cloner_last_error_for('md5_file'));
                    continue;
                }
                $result->appendOK($hash);
                continue;
            }
            // Chunk hash.
            $parts = (int)ceil($stat->getSize() / $chunkSize);
            for ($i = strlen($hashes) / $hashLen; $i < $parts; $i++) {
                if (($fh = @fopen($filePath, 'rb')) === false) {
                    throw new ClonerFSFunctionException('fopen', $filePath);
                }
                $limit = ($parts === 1) ? $file->size : min($chunkSize, $file->size - $i * $chunkSize);
                if ($i !== 0 && (@fseek($fh, $i * $chunkSize) === false)) {
                    throw new ClonerFSFunctionException('fseek', $filePath);
                }

                if (($ctx = @hash_init('md5')) === false) {
                    throw new ClonerFunctionException('hash_init');
                }
                while ($limit > 0) {
                    // Limit chunk size to either our remaining chunk or max chunk size
                    $read  = min($limit, $hashBufSize);
                    $limit -= $read;
                    if (($chunk = @fread($fh, $read)) === false) {
                        throw new ClonerFSException('fread', $filePath);
                    }
                    if (@hash_update($ctx, $chunk) === false) {
                        throw new ClonerFunctionException('hash_update');
                    }
                }
                @fclose($fh);
                if (($hash = @hash_final($ctx)) === false) {
                    throw new ClonerFunctionException('hash_final');
                }

                if ($i + 1 === $parts) {
                    // Last (and maybe only) part.
                    if (strlen($hashes) !== 0) {
                        // End of multipart hash.
                        $hash = md5($hashes.$hash);
                    }
                    // Break will happen here.
                } else {
                    $hashes .= $hash;
                    // Need to hash more parts.
                    if ($deadline->done()) {
                        $result->appendTempHashes($hashes);
                        return $result;
                    }
                }
            }
            $result->appendOK($hash);
        } catch (Exception $e) {
            if (isset($fh) && is_resource($fh)) {
                @fclose($fh);
            }
            $result->appendError($e->getMessage());
        }
    }
    return $result;
}

class ClonerReadResult
{
    public $files = array();
    public $lastOffset = 0;

    public function appendEOF($data)
    {
        $this->files[] = array('b' => base64_encode($data), 'f' => true);
    }

    public function appendChunk($data)
    {
        $this->files[] = array('b' => base64_encode($data));
    }

    public function appendError($status, $error)
    {
        $this->files[] = array('o' => $status, 'e' => $error);
    }
}

/**
 * @param string $root
 * @param string $id
 * @param array  $files
 * @param int    $lastOffset
 * @param int    $limit
 *
 * @return ClonerReadResult
 *
 * @throws ClonerException
 */
function cloner_action_read($root, $id, array $files, $lastOffset, $limit)
{
    $result = new ClonerReadResult();
    $cursor = 0;
    if ($limit <= 0) {
        throw new ClonerException('Limit must be greater than zero');
    }
    foreach ($files as $file) {
        $offset     = $lastOffset;
        $lastOffset = 0;
        $file       = new ClonerFileInfo($file);
        if ($cursor >= $limit && count($result->files) !== 0) {
            break;
        }
        if ($file->dir) {
            $result->appendEOF('');
            continue;
        }
        $fullPath = rtrim($root, '/') . "/$file->path";
        if (strncmp($file->path, 'mwp_db/', 7) === 0) {
            $tryFullPath = sys_get_temp_dir()."/mwp_db$id/".substr($file->path, 7);
            if (@filesize($tryFullPath) === $file->size) {
                $fullPath = $tryFullPath;
            }
        }
        if (($size = @filesize($fullPath)) === false) {
            $result->appendError(ClonerStatus::ERROR, cloner_last_error_for('filesize'));
            continue;
        }
        if ($size !== $file->size) {
            $result->appendError(ClonerStatus::ERROR, "file size changed to $size bytes, expected $file->size bytes");
            continue;
        }
        $maxLen = min($limit - $cursor, $file->size);
        list($content, $eof, $err) = cloner_get_file_chunk($fullPath, $offset, $maxLen);
        if (strlen($err)) {
            $result->appendError(ClonerStatus::ERROR, $err);
            continue;
        }
        if ($eof) {
            if (strlen($content) + $offset !== $file->size) {
                $error = sprintf('expected to read %d bytes at offset %d, but got only %d', $file->size - $offset, $offset, strlen($content));
                $result->appendError(ClonerStatus::ERROR, $error);
                continue;
            }
            $result->appendEOF($content);
            $cursor += strlen($content);
            continue;
        }
        if ($file->size <= $offset + strlen($content)) {
            $result->appendError(ClonerStatus::ERROR, sprintf('file size was %d bytes, but %d bytes were read', $file->size, $offset + strlen($content)));
            continue;
        }
        $result->appendChunk($content);
        $result->lastOffset = $offset + strlen($content);
        break;
    }
    return $result;
}

/**
 * @param string $path
 * @param int    $offset
 * @param int    $limit
 *
 * @return array Three elements, chunk (string), eof (bool), error (string).
 * @link https://www.ibm.com/developerworks/library/os-php-readfiles/index.html
 *
 */
function cloner_get_file_chunk($path, $offset, $limit)
{
    $fp = @fopen($path, 'rb');
    if ($fp === false) {
        return array('', false, cloner_last_error_for('fopen'));
    }
    if ($offset) {
        if (@fseek($fp, $offset) !== 0) {
            return array('', false, cloner_last_error_for('fseek'));
        }
    }

    $content = '';
    $need    = $limit;
    $eof     = false;
    while (!$eof && $need > 0) {
        $chunk = @fread($fp, $need);
        if ($chunk === false) {
            $err = cloner_last_error_for('fread');
            @fclose($fp);
            return array('', false, $err);
        }
        $content .= $chunk;
        $eof     = @feof($fp);
        $need    -= strlen($chunk);
    }
    if (!$eof) {
        // Buffer full; peek 1 byte to see if we reached eof.
        @fread($fp, 1);
        $eof = @feof($fp);
    }
    @fclose($fp);

    return array($content, $eof, '');
}

class ClonerWriteResult
{
    public $files = array();
    public $lastOffset = 0;

    public function appendOK($written)
    {
        $this->files[] = array('w' => $written);
    }

    public function appendError($status, $error)
    {
        $this->files[] = array('o' => $status, 'e' => $error);
    }
}

/**
 * @param string $root
 * @param array  $files
 * @param int    $lastOffset
 *
 * @return ClonerWriteResult
 */
function cloner_action_write($root, array $files, $lastOffset)
{
    $result = new ClonerWriteResult();
    foreach ($files as $file) {
        $file       = new ClonerFileInfo($file);
        $offset     = $lastOffset;
        $lastOffset = 0;
        if ($file->dir) {
            if (strlen($error = cloner_make_dir($root, $file->path))) {
                $result->appendError(ClonerStatus::ERROR, $error);
                continue;
            }
            $result->appendOK(0);
            continue;
        }
        if (strlen($error = cloner_make_dir($root, dirname($file->path)))) {
            $result->appendError(ClonerStatus::ERROR, $error);
            continue;
        }
        $filePath = "$root/$file->path";
        if ($file->isLink) {
            @unlink($filePath);
            if (@symlink($file->link, $filePath) === false) {
                $result->appendError(ClonerStatus::ERROR, cloner_last_error_for('symlink'));
            } else {
                $result->appendOK(0);
            }
            continue;
        }
        $data  = $file->data;
        $error = cloner_write_file($filePath, $offset, $data);
        if (strlen($error)) {
            $result->appendError(ClonerStatus::ERROR, $error);
            continue;
        }
        $result->appendOk(strlen($data));
        if (!$file->eof) {
            $lastOffset = $offset + strlen($data);
            continue;
        }
        if ($file->mtime) {
            @touch($filePath, $file->mtime);
        }
    }
    $result->lastOffset = $lastOffset;
    return $result;
}

/**
 * @param string $root
 * @param string $remoteRoot
 * @param string $id
 * @param string $remoteID
 * @param array  $files
 * @param string $url
 * @param int    $lastOffset
 * @param int    $limit
 *
 * @return array Result of remote cloner_action_write call.
 *
 * @throws ClonerActionException
 * @throws ClonerException
 * @throws ClonerURLException
 */
function cloner_action_push($root, $remoteRoot, $id, $remoteID, array $files, $url, $lastOffset, $limit)
{
    $results    = array();
    $payload    = array();
    $sent       = array();
    $readResult = cloner_action_read($root, $id, $files, $lastOffset, $limit);
    foreach ($readResult->files as $i => $readOp) {
        if (!empty($readOp['o'])) {
            $results[] = $readOp;
            continue;
        }
        $writeOp   = $files[$i] + array(
                'data64' => $readOp['b'],
                'eof'    => empty($readOp['f']) ? false : true,
            );
        $payload[] = $writeOp;
        $sent[]    = $i;
        $results[] = null;
    }
    $action = new ClonerAction('write', array('files' => $payload, 'lastOffset' => $lastOffset, 'root' => $remoteRoot, 'id' => $remoteID));
    $result = cloner_send_action(ClonerURL::fromString($url), $action);
    foreach ($result['files'] as $i => $writeOpResult) {
        $results[$sent[$i]] = $writeOpResult;
    }
    return array(
        'files'      => $results,
        'lastOffset' => $result['lastOffset'],
    );
}

/**
 * @param string $root
 * @param string $remoteRoot
 * @param string $remoteID
 * @param array  $files
 * @param string $url
 * @param int    $lastOffset
 * @param int    $limit
 *
 * @return array Appends status, error.
 *
 * @throws ClonerActionException
 * @throws ClonerURLException
 */
function cloner_action_pull($root, $remoteRoot, $remoteID, array $files, $url, $lastOffset, $limit)
{
    $results = array();
    $payload = array();
    $sent    = array();
    $action  = new ClonerAction('read', array('files' => $files, 'lastOffset' => $lastOffset, 'limit' => $limit, 'root' => $remoteRoot, 'id' => $remoteID));
    /** @var ClonerReadResult $reaction */
    $reaction = cloner_send_action(ClonerURL::fromString($url), $action);
    foreach ($reaction['files'] as $i => $readOp) {
        // See ClonerReadResult structure.
        if (!empty($readOp['o'])) {
            $results[] = $readOp;
            continue;
        }
        $writeOp   = $files[$i] + array(
                'data64' => $readOp['b'],
                'eof'    => empty($readOp['f']) ? false : true,
            );
        $payload[] = $writeOp;
        $sent[]    = $i;
        $results[] = null;
    }
    $result = cloner_action_write($root, $payload, $lastOffset);
    foreach ($result->files as $i => $writeOpResult) {
        $results[$sent[$i]] = $writeOpResult;
    }
    return array(
        'files'      => $results,
        'lastOffset' => $result->lastOffset,
    );
}

/**
 * @param string     $url
 * @param string     $forceCMS
 * @param string     $tablePrefix
 * @param array|null $db
 * @param string     $configContent
 * @param boolean    $readOnly
 *
 * @return array
 *
 * @throws ClonerException
 */
function cloner_action_get_env($url, $forceCMS, $tablePrefix, $db, $configContent, $readOnly)
{
    if (function_exists('__cloner_get_state')) {
        // This may be injected just before script write during setup while in Worker plugin context, eg. if when using
        // local-sync or regular Worker setup. This is done because we have reliable info while in Worker plugin context
        // (eg. database credentials), so we don't parse configuration statically.
        return __cloner_get_state();
    }
    $root          = dirname(__FILE__);
    $configContent = base64_decode($configContent);
    return cloner_setup($root, $url, $forceCMS, $tablePrefix, $db, $configContent, $readOnly)->toArray();
}

/**
 * @param array|null $db
 *
 * @return array
 *
 * @throws ClonerException
 */
function cloner_action_list_tables($db)
{
    if (!count($db) || (count($db) === 1 && empty($db[0]['dbName']))) {
        return array();
    }
    $conn      = cloner_db_conn($db);
    $result    = array();
    $succeeded = false;
    foreach ($conn->getConnectionIDs() as $connectionID) {
        $conn->useConnection($connectionID);
        try {
            $tables = $conn->query('SELECT `table_name` AS `name`, `data_length` AS `dataSize`
FROM information_schema.TABLES WHERE table_schema = :db_name AND table_type = :table_type AND engine IS NOT NULL', array(
                // The NULL `engine` tables usually have `table_comment` == "Table 'forrestl_wrdp1.wp_wpgmza_categories' doesn't exist in engine".
                'db_name'    => $conn->getConfiguration()->name,
                'table_type' => 'BASE TABLE', // as opposed to VIEW
            ))->fetchAll();
        } catch (Exception $e) {
            $result[] = array(
                'name'  => $conn->getConfiguration()->getID(),
                'error' => $e->getMessage(),
            );
            continue;
        }
        $succeeded = true;
        foreach ($tables as &$table) {
            $result[] = array(
                'name'     => $table['name'],
                'dataSize' => (int)$table['dataSize'],
                'noData'   => cloner_is_schema_only($table['name']),
                'source'   => $connectionID,
            );
        }
        $conn->close();
    }
    if (!$succeeded && isset($e)) {
        throw $e;
    }
    return $result;
}

/**
 * @param ClonerDBConn|array $db
 * @param array              $tables
 * @param float              $timeout
 *
 * @return array
 *
 * @throws ClonerException
 */
function cloner_action_hash_tables($db, array $tables, $timeout)
{
    $conn     = cloner_db_conn($db);
    $deadline = new ClonerDeadline($timeout);
    $result   = new ClonerHashResult();
    foreach ($tables as $tableData) {
        $table = ClonerTable::fromArray($tableData);
        if (!$conn->useConnection($table->source)) {
            throw new ClonerException(sprintf('Could not use connection %s', $table->source));
        }
        try {
            if ($table->noData) {
                $row         = $conn->query("SHOW CREATE TABLE `{$table->name}`")->fetch();
                $createTable = $row['Create Table'];
                if (empty($createTable)) {
                    throw new ClonerException(sprintf('SHOW CREATE TABLE did not return expected result for table %s', $table->name));
                }
                $result->appendOK(md5($createTable));
            } else {
                $rows = $conn->query("CHECKSUM TABLE `{$table->name}`")->fetchAll();
                if (count($rows) !== 1) {
                    throw new ClonerException(sprintf('Expected exactly one CHECKSUM TABLE result, got %d', count($rows)));
                }
                $result->appendOK(md5($rows[0]['Checksum']));
            }
        } catch (Exception $e) {
            $result->appendError($e->getMessage());
        }
        if ($deadline->done()) {
            break;
        }
    }
    return $result->hashes;
}

/**
 * @param string                  $root
 * @param string                  $id
 * @param ClonerDBConn|array      $db
 * @param ClonerDBDumpState|array $state
 * @param float                   $timeout
 * @param bool                    $stream
 *
 * @return ClonerDBDumpState
 *
 * @throws ClonerException
 * @throws ClonerFSFunctionException
 */
function cloner_action_dump_tables($root, $id, $db, $state, $timeout, $stream)
{
    set_time_limit(max($timeout * 5, 900));
    $conn     = cloner_db_conn($db);
    $state    = ClonerDBDumpState::fromArray($state);
    $deadline = new ClonerDeadline($timeout);
    $suffix   = '/mwp_db';
    if (strlen($err = cloner_make_dir($root, 'mwp_db')) || strlen($err = cloner_write_file("$root$suffix/index.php", 0, ''))) {
        $root   = sys_get_temp_dir();
        $suffix = "/mwp_db$id";
        if (strlen(cloner_make_dir($root, "mwp_db$id")) || strlen(cloner_write_file("$root$suffix/index.php", 0, ''))) {
            throw new ClonerException($err);
        }
    }
    $count = 0;
    foreach ($state->list as $table) {
        if ($count > 0 && $deadline->done()) {
            return $state;
        }
        if ($table->done) {
            continue;
        }
        if (!$conn->useConnection($table->source)) {
            throw new ClonerException(sprintf('Connection %s for table %s not found', $table->source, $table->name));
        }
        if (!$table->listed) {
            $table->columns = cloner_get_table_columns($conn, $table->name);
            $table->listed  = true;
        }
        $source = '';
        if (strlen($table->source)) {
            $source = '_'.md5($table->source);
        }
        $table->path = "mwp_db/$table->name{$source}.sql.php";
        $tablePath   = "$root$suffix/{$table->name}{$source}.sql.php";
        if (!$stream) {
            if (($fp = @fopen($tablePath, 'wb')) === false) {
                throw new ClonerFSFunctionException('fopen', $tablePath);
            }
            $table->size = cloner_dump_table($conn, $table->name, $table->columns, $tablePath, $table->noData, $fp);
            if (($size = @filesize($tablePath)) !== $table->size) {
                if ($size === false) {
                    throw new ClonerFSFunctionException('filesize', $tablePath);
                }
                throw new ClonerException(sprintf('table %s dumped %d bytes, but on the disk is %d bytes', $table->name, $table->size, $size));
            }
        } else {
            $table->path = "php://memory/$table->name.sql.php";
            if (($fp = @fopen("php://memory", 'wb')) === false) {
                throw new ClonerFSFunctionException('fopen', "php://memory");
            }
            $table->size = cloner_dump_table($conn, $table->name, $table->columns, "php://memory", $table->noData, $fp);
            @rewind($fp);
            $content = @stream_get_contents($fp);
            if (!empty($content)) {
                $table->content = (string)$content;
            }
        }
        if (@fclose($fp) === false) {
            throw new ClonerFSFunctionException('fclose', $tablePath);
        }
        $table->done = true;
        $count++;
    }
    $state->done = true;
    return $state;
}

/** @noinspection SqlDialectInspection */

/** @noinspection SqlNoDataSourceInspection */



class ClonerDBDumpScanner
{
    const INSERT_REPLACEMENT_PATTERN = '#^INSERT\\s+INTO\\s+(`?)[^\\s`]+\\1\\s+(?:\([^)]+\)\\s+)?VALUES\\s*#';
    // File handle.
    private $handle;
    // 0 - unknown ending
    // 1 - \n ending
    // 2 - \r\n ending
    private $rn = 0;
    private $cursor = 0;
    // Buffer that holds up to one statement.
    private $buffer = "";

    /**
     * @param string $path
     *
     * @throws ClonerException
     */
    public function __construct($path)
    {
        $this->handle = @fopen($path, 'rb');
        if (!is_resource($this->handle)) {
            throw new ClonerException("Could not open database dump file", "db_dump_open", cloner_last_error_for('fopen'));
        }
    }

    /**
     * @param int $maxCount
     * @param int $maxSize
     *
     * @return string Up to $maxCount statements or until half of $maxSize (in bytes) is reached.
     *
     * @throws ClonerException
     */
    public function scan($maxCount, $maxSize)
    {
        $lineBuffer = "";
        $buffer     = "";
        $delimited  = false;
        $count      = 0;
        $inserts    = false;
        while (true) {
            if (strlen($this->buffer)) {
                $line         = $this->buffer;
                $this->buffer = "";
            } else {
                $line = fgets($this->handle);
                if ($line === false) {
                    $error = cloner_last_error_for('fgets');
                    if (feof($this->handle)) {
                        // So, this is needed...
                        break;
                    }
                    throw new ClonerException("Could not read database dump line", "db_dump_read_line", $error);
                }
                $this->cursor += strlen($line);
            }
            $len = strlen($line);
            if ($this->rn === 0) {
                // Run only once - detect line ending.
                if (substr_compare($line, "\r\n", $len - 2) === 0) {
                    $this->rn = 2;
                } else {
                    $this->rn = 1;
                }
            }

            if (strlen($lineBuffer) === 0) {
                // Detect comments.
                if ($len <= 2 + $this->rn) {
                    if ($this->rn === 2) {
                        if ($line === "--\r\n" || $line === "\r\n") {
                            continue;
                        }
                    } else {
                        if ($line === "--\n" || $line === "\n") {
                            continue;
                        }
                    }
                }
                if (strncasecmp($line, '-- ', 3) === 0) {
                    continue;
                }
                if (preg_match('{^\s*$}', $line)) {
                    continue;
                }
            }

            if (($len >= 2 && $this->rn === 1 && substr_compare($line, ";\n", $len - 2) === 0)
                || ($len >= 3 && $this->rn === 2 && substr_compare($line, ";\r\n", $len - 3) === 0)
            ) {
                // Statement did end - fallthrough. This logic just makes more sense to write.
            } else {
                $lineBuffer .= $line;
                continue;
            }
            if (strlen($lineBuffer)) {
                $line       = $lineBuffer.$line;
                $lineBuffer = "";
            }
            // Hack, but it's all for the greater good. The mysqldump command dumps statements
            // like "/*!50013 DEFINER=`user`@`localhost` SQL SECURITY DEFINER */" which require
            // super-privileges. That's way too troublesome, so just skip those statements.
            if (strncmp($line, '/*!50013 DEFINER=`', 18) === 0) {
                continue;
            }
            // /*!50003 CREATE*/ /*!50017 DEFINER=`foo`@`localhost`*/ /*!50003 TRIGGER `wp_hplugin_root` BEFORE UPDATE ON `wp_hplugin_root` FOR EACH ROW SET NEW.last_modified = NOW() */;
            if (strncmp($line, '/*!50003 CREATE*/ /*!50017 DEFINER=', 35) === 0) {
                $line = preg_replace('{/\*!50017 DEFINER=.*?(\*/)}', '', $line, 1);
            }
            if (strncmp($line, '/*!50001 CREATE ALGORITHM=', 26) === 0) {
                continue;
            }
            if (strncmp($line, '/*!50001 VIEW', 13) === 0) {
                continue;
            }
            $count++;
            if ($delimited) {
                // We're inside a block that looks like this:
                //
                //  DELIMITER ;;
                //  /*!50003 CREATE*/ /*!50017 DEFINER=`user`@`localhost`*/ /*!50003 TRIGGER `wp_hlogin_default_storage_table` BEFORE UPDATE ON `wp_hlogin_default_storage_table`
                //  FOR EACH ROW SET NEW.last_modified = NOW() */;;
                //  DELIMITER ;
                //
                // Since the DELIMITER statement does nothing when not in the CLI context, we need to merge the delimited statements
                // manually into a single statement.
                if (strncmp($line, 'DELIMITER ;', 11) === 0) {
                    break;
                }
                // Replace the new delimiter with the default one (remove one semicolon).
                if (($this->rn === 1 && substr_compare($line, ";;\n", -3, 3) === 0)
                    || ($this->rn === 2 && substr_compare($line, ";;\r\n", -4, 4) === 0)
                ) {
                    $line = substr($line, 0, -($this->rn + 1)); // strip ";\n" or ";\r\n" at the end.
                }
                $buffer .= $line."\n";
                continue;
            } elseif (strncmp($line, 'DELIMITER ;;', 12) === 0) {
                $delimited = true;
                continue;
            }
            if (strncmp($line, 'INSERT INTO ', 12) === 0) {
                $inserts = true;
                if (strlen($buffer) === 0) {
                    $buffer = 'INSERT IGNORE INTO '.substr($line, strlen('INSERT INTO '), -(1 + $this->rn)); // Strip the ";\n" or ";\r\n" at the end
                } else {
                    if (strlen($buffer) + strlen($line) >= max(1, $maxSize / 2)) {
                        $this->buffer = $line;
                        break;
                    }
                    $newLine = preg_replace(self::INSERT_REPLACEMENT_PATTERN, ', ', $line, 1, $c);
                    $newLine = substr($newLine, 0, -(1 + $this->rn));
                    if ($c !== 1) {
                        throw new ClonerException(sprintf("Could not parse INSERT line: %s", $line), "parse_insert_line");
                    }
                    $buffer .= $newLine;
                }
                if ($count >= $maxCount) {
                    break;
                }
                continue;
            } elseif ($inserts) {
                // $buffer is not empty and we aren't inserting anything - break.
                $this->buffer = $line;
            } else {
                $buffer = $line;
            }
            break;
        }
        if ($inserts) {
            $buffer .= ';';
        }
        return $buffer;
    }

    /**
     * @param int $offset
     *
     * @throws ClonerException
     */
    public function seek($offset)
    {
        if (@fseek($this->handle, $offset) === false) {
            throw new ClonerException("Could not seek database dump file", "seek_file", cloner_last_error_for('fseek'));
        }
        $this->cursor = $offset;
    }

    public function tell()
    {
        return $this->cursor - strlen($this->buffer);
    }

    public function close()
    {
        fclose($this->handle);
    }
}

class ClonerDBImportState
{
    /** @var string Collects skipped statements up to a certain buffer length. */
    public $skip = "";
    /** @var int Counts skipped statements. */
    public $skipCount = 0;
    /** @var int Keeps skipped statements' total size. */
    public $skipSize = 0;
    /** @var ClonerImportDump[] File dumps that should be imported. */
    public $files = array();

    /** @var int Maximum buffer size for skipped statements. */
    private $skipBuffer = 0;

    /**
     * @param array $data       State array; empty state means there's nothing to process. Every file that should be imported
     *                          must contain the props $state['files'][$i]['path'] and $state['files'][$i]['size'].
     * @param int   $skipBuffer Maximum buffer size for skipped statement logging.
     *
     * @return ClonerDBImportState
     */
    public static function fromArray(array $data, $skipBuffer = 0)
    {
        $state             = new self;
        $state->skipBuffer = $skipBuffer;
        foreach ((array)@$data['files'] as $i => $dump) {
            $state->files[$i] = new ClonerImportDump($dump['size'], $dump['processed'], $dump['path'], $dump['encoding'], (string)@$dump['source']);
        }
        $state->skip      = (string)@$data['skip'];
        $state->skipCount = (int)@$data['skipCount'];
        $state->skipSize  = (int)@$data['skipSize'];
        return $state;
    }

    /**
     * @return ClonerImportDump|null The next dump in the queue, or null if there are none left.
     */
    public function next()
    {
        foreach ($this->files as $file) {
            if ($file->processed < $file->size) {
                return $file;
            }
        }
        return null;
    }

    /**
     * Pushes the first available file dump to the end of the queue.
     */
    public function pushNextToEnd()
    {
        $carry = null;
        foreach ($this->files as $i => $file) {
            if ($file->size === $file->processed) {
                continue;
            }
            $carry = $file;
            unset($this->files[$i]);
            $this->files = array_values($this->files);
            break;
        }

        if ($carry === null) {
            return;
        }

        $this->files[] = $carry;
    }

    /**
     * Add a "skipped statement" to the state if there's any place left in state's "skipped statement" buffer.
     * Also updates state's "skipped statement" count and size.
     *
     * @param string $statements Statements that were skipped.
     */
    public function skipStatement($statements)
    {
        $length = strlen($statements);
        if (strlen($this->skip) + $length <= $this->skipBuffer / 2) {
            // Only write full statements to the buffer if it won't exceed half the buffer.
            $this->skip .= $statements;
        } elseif ($length + 200 <= $this->skipBuffer) {
            // We have enough space in the buffer to log the excerpt, but don't overflow the buffer, skip logging
            // when we reach its limit.
            $this->skip .= sprintf('/* query too big (%d bytes), excerpt: %s */;', $length, substr($statements, 0, 100));
        }

        $this->skipCount++;
        $this->skipSize += $length;
    }
}

class ClonerImportDump
{
    public $size = 0;
    public $processed = 0;
    public $path = "";
    public $encoding = "";
    public $source = "";

    public function __construct($size, $processed, $path, $encoding, $source)
    {
        $this->size      = (int)$size;
        $this->processed = (int)$processed;
        $this->path      = (string)$path;
        $this->encoding  = (string)$encoding;
        $this->source    = (string)$source;
    }
}

class ClonerDBColumn
{
    public $name = '';
    public $type = '';

    public static function fromArray(array $data)
    {
        $column = new self;
        if (isset($data['name'])) {
            $column->name = $data['name'];
        }
        if (isset($data['type'])) {
            $column->type = $data['type'];
        }
        return $column;
    }
}

class ClonerDBTable
{
    public $name = '';
    public $size = 0;
    public $dataSize = 0;
    public $storage = '';
    public $done = false;
    public $listed = false;
    /** @var ClonerDBColumn[] */
    public $columns = array();
    public $path = '';
    public $noData = false;
    public $content = '';
    public $hash = '';
    public $source = '';

    public static function fromArray(array $data)
    {
        $table = new self;
        if (isset($data['name'])) {
            $table->name = $data['name'];
        }
        if (isset($data['size'])) {
            $table->size = $data['size'];
        }
        if (isset($data['dataSize'])) {
            $table->dataSize = $data['dataSize'];
        }
        if (isset($data['storage'])) {
            $table->storage = $data['storage'];
        }
        if (isset($data['done'])) {
            $table->done = $data['done'];
        }
        if (isset($data['listed'])) {
            $table->listed = $data['listed'];
        }
        if (isset($data['columns'])) {
            foreach ($data['columns'] as $column) {
                $table->columns[] = ClonerDBColumn::fromArray($column);
            }
        }
        if (isset($data['path'])) {
            $table->path = $data['path'];
        }
        if (isset($data['noData'])) {
            $table->noData = $data['noData'];
        }
        if (isset($data['content'])) {
            $table->content = base64_encode($data['content']);
        }
        if (isset($data['source'])) {
            $table->source = $data['source'];
        }
        if (isset($data['hash'])) {
            $table->hash = $data['hash'];
        }
        return $table;
    }
}

class ClonerDBDumpState
{
    public $listed = false;
    /** @var ClonerDBTable[] */
    public $list = array();
    public $done = false;

    public static function fromArray($data)
    {
        if ($data instanceof self) {
            return $data;
        }
        $state = new self;
        if (isset($data['listed'])) {
            $state->listed = $data['listed'];
        }
        if (isset($data['list'])) {
            foreach ($data['list'] as $item) {
                $state->list[] = ClonerDBTable::fromArray($item);
            }
        }
        if (isset($data['done'])) {
            $state->done = $data['done'];
        }
        return $state;
    }
}

/**
 * @param ClonerDBConn $conn
 * @param string       $table
 *
 * @return array
 *
 * @throws ClonerException
 */
function cloner_get_table_columns(ClonerDBConn $conn, $table)
{
    $columnList = $conn->query("SHOW COLUMNS IN `$table`")->fetchAll();

    $columns = array();
    foreach ($columnList as $columnData) {
        $column       = new ClonerDBColumn();
        $column->name = $columnData['Field'];
        $type         = strtolower($columnData['Type']);
        if (($openParen = strpos($type, '(')) !== false) {
            // Transform "int(11)" to "int", etc.
            $type = substr($type, 0, $openParen);
        }
        $column->type = $type;
        $columns[]    = $column;

        if ($conn instanceof ClonerPDOConn && strpos($column->name, '?') !== false) {
            $conn->setAttEmulatePrepares(false);
        }
    }

    return $columns;
}

/**
 * @param ClonerDBConn     $conn
 * @param string           $tableName
 * @param ClonerDBColumn[] $columns
 * @param string           $tablePath
 * @param bool             $noData
 * @param resource|bool    $fp
 *
 * @return int Number of bytes written.
 *
 * @throws ClonerException
 * @throws ClonerFSFunctionException
 */
function cloner_dump_table(ClonerDBConn $conn, $tableName, array $columns, $tablePath, $noData, $fp)
{
    $written     = 0;
    $result      = $conn->query("SHOW CREATE TABLE `$tableName`")->fetch();
    $createTable = $result['Create Table'];
    if (empty($createTable)) {
        throw new ClonerException(sprintf('SHOW CREATE TABLE did not return expected result for table %s', $tableName), 'no_create_table');
    }

    $time          = date('c');
    $fetchAllQuery = cloner_create_select_query($tableName, $columns);
    $haltCompiler  = "<?php exit; __halt_compiler();";
    $dumper        = get_class($conn);
    $phpVersion    = phpversion();
    $header        = <<<SQL
-- $haltCompiler // Protect the file from being visited via web
-- Orion backup format
-- Generated at: $time by $dumper; PHP v$phpVersion
-- Selected via: $fetchAllQuery
    
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP TABLE IF EXISTS `$tableName`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

$createTable;

/*!40101 SET character_set_client = @saved_cs_client */;

SQL;
    if (!$noData) {
        $header .= <<<SQL
LOCK TABLES `$tableName` WRITE;
/*!40000 ALTER TABLE `$tableName` DISABLE KEYS */;

SQL;
    }
    if (($w = @fwrite($fp, $header)) === false) {
        @fclose($fp);
        throw new ClonerFSFunctionException('fwrite', $tablePath);
    }
    $written += $w;

    if (!$noData) {
        $flushSize = 8 << 20;
        $buf       = '';
        $fetchAll  = $conn->query($fetchAllQuery, array(), true);
        while ($row = $fetchAll->fetch()) {
            $buf .= cloner_create_insert_query($conn, $tableName, $columns, $row);
            if (strlen($buf) < $flushSize) {
                continue;
            }
            if (($w = @fwrite($fp, $buf)) === false) {
                $e = new ClonerFSFunctionException('fwrite', $tablePath);
                @fclose($fp);
                throw $e;
            }
            $buf     = '';
            $written += $w;
        }
        if (strlen($buf)) {
            if (($w = @fwrite($fp, $buf)) === false) {
                $e = new ClonerFSFunctionException('fwrite', $tablePath);
                @fclose($fp);
                throw $e;
            }
            unset($buf);
            $written += $w;
        }
        $fetchAll->free();
    }

    $footer = <<<SQL

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SQL;
    if (!$noData) {
        $footer = <<<SQL

/*!40000 ALTER TABLE `$tableName` ENABLE KEYS */;
UNLOCK TABLES;
SQL
            .$footer;
    }
    if (($w = @fwrite($fp, $footer)) === false) {
        $e = new ClonerFSFunctionException('fwrite', $tablePath);
        @fclose($fp);
        throw $e;
    }
    $written += $w;

    cloner_clear_stat_cache($tablePath);
    return $written;
}

/**
 * @param string           $tableName
 * @param ClonerDBColumn[] $columns
 *
 * @return string
 */
function cloner_create_select_query($tableName, array $columns)
{
    $select = 'SELECT ';
    foreach ($columns as $i => $column) {
        if ($i > 0) {
            $select .= ', ';
        }
        switch ($column->type) {
            case 'tinyblob':
            case 'mediumblob':
            case 'blob':
            case 'longblob':
            case 'binary':
            case 'varbinary':
                $select .= "HEX(`$column->name`)";
                break;
            default:
                $select .= "`$column->name`";
                break;
        }
    }
    $select .= " FROM `$tableName`;";

    return $select;
}

/**
 * @param ClonerDBConn     $conn
 * @param string           $tableName
 * @param ClonerDBColumn[] $columns
 * @param array            $row
 *
 * @return string
 *
 * @throws ClonerException
 */
function cloner_create_insert_query(ClonerDBConn $conn, $tableName, array $columns, array $row)
{
    $insert = "INSERT INTO `$tableName` VALUES (";
    $i      = 0;
    foreach ($row as $value) {
        $column = $columns[$i];
        if ($i > 0) {
            $insert .= ',';
        }
        $i++;
        if ($value === null) {
            $insert .= 'null';
            continue;
        }
        switch ($column->type) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
            case 'decimal':
            case 'float':
            case 'double':
                $insert .= $value;
                break;
            case 'tinyblob':
            case 'mediumblob':
            case 'blob':
            case 'longblob':
            case 'binary':
            case 'varbinary':
                if (strlen($value) === 0) {
                    $insert .= "''";
                } else {
                    $insert .= "0x$value";
                }
                break;
            case 'bit':
                $insert .= $value ? "b'1'" : "b'0'";
                break;
            default:
                $insert .= $conn->escape($value);
                break;
        }
    }
    $insert .= ");\n";

    return $insert;
}




class ClonerURL
{
    public $secure = false;
    public $host = '';
    public $port = 0;
    public $scheme = '';
    public $path = '';
    public $query = '';
    public $fragment = '';
    public $user = '';
    public $pass = '';

    public function __toString()
    {
        return sprintf("http%s://%s%s%s", $this->secure ? 's' : '', $this->host, $this->port ? ":$this->port" : '', strlen($this->path) ? $this->path : '/');
    }

    public function getHTTPHost()
    {
        if (!$this->port) {
            return $this->host;
        }
        if ($this->secure && $this->port === 443) {
            return $this->host;
        }
        if (!$this->secure && $this->scheme === 80) {
            return $this->host;
        }
        return "$this->host:$this->port";
    }

    /**
     * @return string In host:port format. Applies default port numbers where none are set.
     */
    public function getHostPort()
    {
        $port = $this->port;
        if ($this->port === 0) {
            $port = $this->secure ? 443 : 80;
        }
        return "$this->host:$port";
    }

    /**
     * @param string $url
     *
     * @return ClonerURL
     *
     * @throws ClonerURLException If the URL is not valid.
     */
    public static function fromString($url)
    {
        $u     = new ClonerURL();
        $parts = parse_url($url);
        if ($parts === false) {
            throw new ClonerURLException($url, 'url_invalid');
        }
        if (!array_key_exists('host', $parts)) {
            throw new ClonerURLException($url, 'missing_host');
        }
        $u->host = strtolower($parts['host']);
        if (array_key_exists('scheme', $parts) && strtolower($parts['scheme']) === 'https') {
            $u->secure = true;
        }
        if (array_key_exists('port', $parts)) {
            $u->port = $parts['port'];
        }
        if (array_key_exists('path', $parts) && strlen($parts['path'])) {
            $u->path = $parts['path'];
        } else {
            $u->path = '/';
        }
        if (array_key_exists('query', $parts)) {
            $u->query = $parts['query'];
        }
        if (array_key_exists('fragment', $parts)) {
            $u->fragment = $parts['fragment'];
        }
        if (array_key_exists('user', $parts)) {
            $u->user = $parts['user'];
        }
        if (array_key_exists('pass', $parts)) {
            $u->pass = $parts['pass'];
        }
        return $u;
    }
}

/**
 * Sends HTTP request to $url and leaves the connection open for reading and/or writing.
 * It is caller's responsibility to use the connection properly. No default headers are
 * sent in this method, set "host" and "content-length" manually if desired.
 *
 * @param string    $method
 * @param ClonerURL $url    As a special case, if the URL contains the hash fragment it will be used as the resolved IP.
 *                          User and password are also automatically added as part of the authorization header.
 * @param array     $header Additional headers to send beside "Host", that is inferred from the URL.
 * @param int       $timeout
 * @param string    $cert   Custom certificate to use for TLS.
 *
 * @return resource
 *
 * @throws ClonerNetSocketException
 * @throws ClonerSocketClientException
 * @throws ClonerNoTransportStreamsException
 * @throws ClonerFSFunctionException
 */
function cloner_http_open_request($method, ClonerURL $url, array $header = array(), $timeout = 60, $cert = '')
{
    $hostPort = $url->getHostPort();
    if (strlen($url->fragment)) {
        $port     = empty($url->port) ? ($url->secure ? 443 : 80) : $url->port;
        $hostPort = "$url->fragment:$port";
    }
    $sock    = cloner_tcp_socket_dial($hostPort, $timeout, $url->secure, $url->host, $cert);
    $request = array(
        sprintf("%s %s%s HTTP/1.1", $method, $url->path, strlen($url->query) ? "?$url->query" : ''),
    );
    if (strlen($url->user)) {
        $header['authorization'] = sprintf('Basic %s', base64_encode("$url->user:$url->pass"));
    }
    foreach ($header as $key => $value) {
        $request[] = sprintf('%s: %s', $key, $value);
    }
    array_push($request, '', ''); // Output \r\n\r\n at the end after implode.
    stream_set_timeout($sock, $timeout);
    if (@fwrite($sock, implode("\r\n", $request)) === false) {
        throw new ClonerNetSocketException('fwrite', $sock);
    }
    return $sock;
}

class ClonerHTTPResponse
{
    public $statusCode = 0;
    public $status = '';

    /**
     * @var string[] In key => value format.
     */
    public $headers = array();
    /**
     * @var resource
     */
    public $body;

    public static function fromParts($statusCode, $status, array $headers, $body)
    {
        $self             = new self();
        $self->statusCode = $statusCode;
        $self->status     = $status;
        $self->headers    = $headers;
        $self->body       = $body;
        return $self;
    }

    /**
     * @param int $timeout
     *
     * @return string
     *
     * @throws ClonerException
     * @throws ClonerNetException
     */
    public function read($timeout)
    {
        if (isset($this->headers['transfer-encoding']) && strtolower($this->headers['transfer-encoding']) === 'chunked') {
            return cloner_chunked_read($this->body, $timeout);
        }

        if (isset($this->headers['connection']) && strtolower($this->headers['connection']) === 'close') {
            $data = @stream_get_contents($this->body);
            if ($data === false) {
                throw new ClonerNetSocketException('stream_get_contents', $this->body);
            }
            return $data;
        }

        if (isset($this->headers['content-length']) || ctype_digit($this->headers['content-length'])) {
            $length = (int)$this->headers['content-length'];
            return cloner_limit_read($this->body, $length, $timeout);
        }

        throw new ClonerException("got unrecognized HTTP response format");
    }
}

/**
 * Reads HTTP response headers from $sock that's expecting them.
 * The response body is then ready to be read, after which features
 * like keep-alive or web sockets can be utilized.
 *
 * @param resource $sock Usually a result of cloner_http_request_open.
 * @param int      $timeout
 *
 * @return ClonerHTTPResponse With headers and status present, and unread body.
 *
 * @throws ClonerNetException
 * @throws ClonerException If HTTP response is not valid.
 */
function cloner_http_get_response_headers($sock, $timeout = 60)
{
    $res       = new ClonerHTTPResponse();
    $res->body = $sock;
    while (true) {
        stream_set_timeout($sock, $timeout);
        if (($line = @fgets($sock)) === false) {
            throw new ClonerNetSocketException('fgets', $sock);
        }
        if ($line === "\n" || $line === "\r\n") {
            if ($res->statusCode === 0) {
                throw new ClonerNetException('newline encountered before HTTP response');
            }
            break;
        }
        if ($res->statusCode === 0) {
            if (!preg_match('{^HTTP/\d\.\d (\d{3}) (.*)$}', $line, $matches)) {
                throw new ClonerException(sprintf('invalid first response line: %s', $line));
            }
            $res->statusCode = (int)$matches[1];
            $res->status     = trim($matches[2]);
            continue;
        }
        $parts = explode(':', $line, 2);;
        if (count($parts) !== 2) {
            throw new ClonerException(sprintf('invalid header line: %s', $line));
        }
        $res->headers[strtolower(trim($parts[0]))] = trim($parts[1]);
    }
    return $res;
}

/**
 * @param string $method
 * @param string $url
 * @param string $contentType
 * @param string $body
 * @param int    $timeout In seconds.
 *
 * @return string Raw (and dechunked) response body.
 *
 * @throws ClonerNetException
 * @throws ClonerURLException
 * @throws Exception
 */
function cloner_http_do($method, $url, $contentType = '', $body = '', $timeout = 60)
{
    $deadline = time() + $timeout;
    $url      = ClonerURL::fromString($url);
    $headers  = array(
        'content-type'   => $contentType,
        'connection'     => 'close',
        'content-length' => (string)strlen($body),
        'host'           => $url->getHTTPHost(),
    );
    $sock     = cloner_http_open_request($method, $url, $headers, $timeout);
    if (strlen($body)) {
        stream_set_timeout($sock, max(1, $deadline - time()));
        $n = @fwrite($sock, $body);
        if ($n === false) {
            @fclose($sock);
            throw new ClonerClonerNetFunctionException('fwrite', $url);
        }
    }
    try {
        $response = cloner_http_get_response_headers($sock, max(1, $deadline - time()))->read(max(1, $deadline - time()));
    } catch (Exception $e) {
        @fclose($sock);
        throw $e;
    }
    @fclose($sock);
    return $response;
}

class ClonerHTTPResponseLine
{
    public $protocol = ''; // 1.0 or 1.1
    public $statusCode = 0; // Whatever server returns.
    public $status = ''; // Whatever server returns.

    /**
     * @param string $protocol
     * @param int    $statusCode
     * @param string $status
     *
     * @return ClonerHTTPResponseLine
     */
    public static function create($protocol, $statusCode, $status)
    {
        $self             = new self();
        $self->protocol   = $protocol;
        $self->statusCode = $statusCode;
        $self->status     = $status;
        return $self;
    }
}

class ClonerHTTPParseException extends ClonerException
{
    public function __construct($message)
    {
        parent::__construct($message, 'http_parse');
    }
}

/**
 * Waits for $timeout seconds on $sock to optionally receive an HTTP error response, eg. "413 Request Entity Too Large".
 * This should only be used on HTTP requests that are waiting for the body to be written.
 *
 * @param resource $sock    Stream with HTTP request headers already written to it, and ready to accept the body.
 * @param float    $timeout Timeout in seconds.
 *
 * @return ClonerHTTPResponseLine Response status code.
 *
 * @throws ClonerHTTPParseException
 * @throws ClonerNetSocketException
 */
function cloner_http_get_response_line($sock, $timeout)
{
    $bufferLimit = 256;
    list($sec, $usec) = cloner_split_usec($timeout);
    stream_set_timeout($sock, $sec, $usec);
    $line = @fgets($sock, $bufferLimit);
    if ($line === false) {
        throw new ClonerNetSocketException('fgets', $sock);
    }
    if (!preg_match('{^HTTP/(\d\.\d) (\d{3}) ([^$]+)$}', $line, $matches)) {
        throw new ClonerHTTPParseException(sprintf('invalid HTTP response first line: %s', $line));
    }
    return ClonerHTTPResponseLine::create($matches[1], (int)$matches[2], $matches[3]);
}

/**
 * @param resource $sock    Stream waiting for HTTP header reading, meaning it should go after http_get_response_line.
 * @param float    $timeout Timeout in seconds.
 *
 * @return array Map of lowercase-header-name => header value, both strings.
 *
 * @throws Exception If HTTP response could not be parsed.
 * @throws ClonerNetSocketException
 */
function cloner_http_get_headers($sock, $timeout)
{
    $bufferLimit = 8 * (1 << 10);
    $headers     = array();
    while (true) {
        list($sec, $usec) = cloner_split_usec($timeout);
        stream_set_timeout($sock, $sec, $usec);
        $line = @fgets($sock, $bufferLimit);
        if ($line === false) {
            throw new ClonerNetSocketException('fgets', $sock);
        }
        if ($line === "\n" || $line === "\r\n") {
            break;
        }
        $parts = explode(':', $line, 2);;
        if (count($parts) !== 2) {
            throw new ClonerHTTPParseException(sprintf('invalid HTTP header line: %s', $line));
        }
        $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
    }
    return $headers;
}

/**
 * Splits seconds and microseconds to two integers, to be used in system calls that require them.
 *
 * @param float|int $time
 *
 * @return int[] Array of two int elements, $seconds and $microseconds.
 */
function cloner_split_usec($time)
{
    $sec  = floor($time);
    $usec = ($time - $sec) * 1000000;
    return array($sec, $usec);
}

/**
 * @param $sock
 * @param $limit
 * @param $timeout
 *
 * @return string Read result until $limit is reached.
 *
 * @throws ClonerNetSocketException If reading from stream fails.
 */
function cloner_limit_read($sock, $limit, $timeout)
{
    stream_set_timeout($sock, $timeout);
    $body = '';
    while (strlen($body) < $limit) {
        $chunk = @fread($sock, $limit - strlen($body));
        if ($chunk === false) {
            throw new ClonerNetSocketException('fread', $this->body);
        }
        $body .= $chunk;
    }
    return $body;
}

/**
 * @param resource $sock
 * @param int      $timeout
 *
 * @return string Read result until terminating chunk (0\r\n).
 *
 * @throws Exception If chunked encoding is not valid.
 * @throws ClonerNetSocketException If reading from stream fails.
 */
function cloner_chunked_read($sock, $timeout)
{
    stream_set_timeout($sock, $timeout);
    $body = '';
    while (true) {
        $length = @fgets($sock);
        if ($length === false) {
            throw new ClonerNetSocketException('fgets', $sock);
        }
        $length = rtrim($length, "\r\n");
        if (!ctype_xdigit($length)) {
            throw new ClonerException(sprintf('Did not get hex chunk length: %s', $length));
        }
        $length = hexdec($length);
        $got    = 0;
        while ($got < $length) {
            $chunk = @fread($sock, $length - $got);
            if ($chunk === false) {
                throw new ClonerNetSocketException('fread', $sock);
            }
            $got  += strlen($chunk);
            $body .= $chunk;
        }
        // Every chunk (including final) is followed up by an additional \r\n.
        if (($tmp = @fgets($sock, 3)) === false) {
            throw new ClonerNetSocketException('fgets', $sock);
        }
        if ($tmp !== "\r\n") {
            throw new ClonerException('Did not get expected CRLF');
        }
        if ($length === 0) {
            break;
        }
    }
    return $body;
}




/**
 * Throw to skip file in ClonerFSVisitor implementation.
 */
class ClonerSkipVisitException extends ClonerException
{
    public function __construct()
    {
        parent::__construct("Internal exception, skip file");
    }
}

interface ClonerFSVisitor
{
    /**
     * @param string         $path Path relative to root.
     * @param ClonerStatInfo $stat Stat result of path.
     * @param Exception|null $e    Error during stat or readdir of $path.
     *
     * @return bool True to continue iteration, false to stop and return the file's path as cursor to potentially continue from.
     *
     * @throws ClonerSkipVisitException If the directory should not be traversed. No real effect if visiting a file, since its sibling comes next.
     * @throws Exception To abort execution and propagate the exception.
     */
    public function visit($path, ClonerStatInfo $stat, Exception $e = null);
}

class ClonerFSFileInfo
{
    /** @var string */
    private $path;
    /** @var ClonerStatInfo */
    private $stat;
    /** @var string[]|null */
    private $children;

    /**
     * @param string         $relPath
     * @param ClonerStatInfo $stat
     * @param string[]|null  $children
     */
    public function __construct($relPath, ClonerStatInfo $stat, array $children = null)
    {
        $this->path     = $relPath;
        $this->stat     = $stat;
        $this->children = $children;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return ClonerStatInfo
     */
    public function getStat()
    {
        return $this->stat;
    }

    /**
     * @param string[]|null $children
     */
    public function setChildren(array $children = null)
    {
        $this->children = $children;
    }

    /**
     * @return string[]|null
     */
    public function getChildren()
    {
        return $this->children;
    }
}

/**
 * Creates stack for file iteration from cursor.
 * The cursor is normalized and split to directory nodes, for example:
 *  ''              => ['']
 *  'foo'           => ['','foo']
 *  '/foo\\/bar///' => ['','foo','bar']
 *
 * Each node holds its own name and names of children that alphabetically go after the node that is next in path.
 * For example, the path 'foo/bar', will be split to the following nodes:
 *  1. '' - Directory and its children that come after 'foo'.
 *  2. 'foo' - Directory and its children that come after 'bar'.
 *  3. 'bar' - If and only if bar is a directory.
 *
 * Note that 'bar' is not in the stack, since the last node is always skipped.
 * Cursor always points to the next processed file, so the iteration should continue from
 * 'bar', which is known in the 'foo' node (info 2. above).
 *
 * @param string $root   Root on the filesystem.
 * @param string $cursor Cursor path that points to the next file to be processed.
 *
 * @return ClonerFSFileInfo[] Stack of file info with at least one (root) element.
 *
 * @throws ClonerFSException Only if the root cannot be stat-ed, or is not a directory.
 */
function cloner_fs_make_stack($root, $cursor = '')
{
    /** @var ClonerFSFileInfo[] $stack */
    $stack = array();
    // Split cursor to paths
    $paths = explode('/', preg_replace('{[\\\\/]+}', '/', trim($cursor, '\\/')));
    if ($paths[0] === '.') {
        $paths[0] = '';
    }
    if ($paths[0] !== '') {
        array_unshift($paths, '');
    }
    for ($i = 0, $pathCount = count($paths); $i < $pathCount; $i++) {
        $current = $paths[$i];
        // $current[$i+1] holds path-to-skip-to in current directory.
        // First time $current is an empty string.
        $path      = isset($path) ? $path.'/'.$current : $current;
        $nextChild = isset($paths[$i + 1]) ? $paths[$i + 1] : null;
        $children  = null;
        try {
            $stat = cloner_fs_stat($root.$path);
            if (!$stat->isDir()) {
                if (count($stack) === 0) {
                    return array(new ClonerFSFileInfo($path, $stat));
                }
                return $stack;
            }
            if ($nextChild !== null) {
                $children = cloner_fs_list_children($root.$path, $nextChild);
            }
            if (isset($stack[$i - 1])) {
                $parent   = $stack[$i - 1];
                $siblings = $parent->getChildren();
                if (isset($siblings[0]) && $siblings[0] === $current) {
                    array_shift($siblings);
                    $parent->setChildren($siblings);
                }
            }
            $stack[] = new ClonerFSFileInfo(ltrim($path, '/'), $stat, $children);
        } catch (ClonerFSException $e) {
            if (count($stack) > 0) {
                // We have root at least.
                break;
            }
            throw $e;
        }
    }
    return $stack;
}

/**
 * Iterate through all $root paths (including $root itself) after $cursor and call $visitor for each file path
 * encountered. Directory traversal is done in depth-first mode (meaning directory children are visited recursively
 * before their siblings). The flag $parentsFirst decides whether parent directories themselves are visited before
 * their children (useful for reading) or after (useful for deleting).
 *
 * @param string          $root         Root path to traverse.
 * @param ClonerFSVisitor $visitor      Visitor to invoke for each file encountered.
 * @param string          $cursor       Cursor points to the next node to be processed, defaults to working directory.
 * @param bool            $parentsFirst True to visit parents right before their children, false to visit them right after.
 *
 * @return string $cursor Cursor path (relative to root) to continue from when visitor stops the traversal. Empty string signifies end.
 *
 * @throws Exception Propagated from $visitor.
 * @throws ClonerFSException If $root is not a directory.
 */
function cloner_fs_walk($root, ClonerFSVisitor $visitor, $cursor = '', $parentsFirst = false)
{
    try {
        $stack = cloner_fs_make_stack($root, $cursor);
    } catch (Exception $e) {
        $visitor->visit($cursor, ClonerStatInfo::makeEmpty(), $e);
        return '';
    }
    /** @var ClonerFSFileInfo $current */
    $current = array_pop($stack);
    if (!$current->getStat()->isDir()) {
        $visitor->visit($current->getPath(), $current->getStat());
        return '';
    }
    // Flag that is set to true every time we traverse down into a new directory, and false when going up.
    // If last node in cursor is a directory with un-stat-ed children, that means the cursor ended up on it,
    // and that directory was the last one to get visited.
    // If last part of cursor is a file, $current will have its siblings as children.
    $goDown = true;
    if ($parentsFirst) {
        if ($current->getChildren() !== null) {
            $goDown = null;
        }
    } else {
        if ($current->getChildren() === null) {
            $goDown = false;
        }
    }

    while (true) {
        $e        = null;
        $children = $current->getChildren();
        if ($children === null) {
            try {
                $children = cloner_fs_list_children($root.'/'.$current->getPath());
            } catch (Exception $e) {
                // Capture $e for call below.
                $children = array();
            }
            $current->setChildren($children);
        }
        if ($goDown === $parentsFirst) {
            try {
                if (!$visitor->visit($current->getPath(), $current->getStat(), $e)) {
                    return $current->getPath();
                }
            } catch (ClonerSkipVisitException $e) {
                $goDown = false;
            }
        }
        if ($goDown === false) {
            $current = array_pop($stack);
            if ($current === null) {
                break;
            }
        }
        foreach ($current->getChildren() as $i => $child) {
            $childPath = ltrim($current->getPath().'/'.$child, '/');
            try {
                $stat = cloner_fs_stat($root.'/'.$childPath);
                if ($stat->isDir()) {
                    $current->setChildren(array_slice($current->getChildren(), $i + 1));
                    $stack[] = $current;
                    $current = new ClonerFSFileInfo($childPath, $stat);
                    $goDown  = true;
                    continue 2;
                }
            } catch (Exception $e) {
                if (!$visitor->visit($childPath, ClonerStatInfo::makeEmpty(), $e)) {
                    return $childPath;
                }
                continue;
            }
            try {
                if (!$visitor->visit($childPath, $stat)) {
                    return $childPath;
                }
            } catch (ClonerSkipVisitException $e) {
                // Go to next sibling.
                continue;
            }
        }
        $goDown = false;
    }
    return '';
}

/**
 * Attempts to run lstat or stat syscall and return results.
 *
 * @param string $path File path to stat.
 *
 * @return ClonerStatInfo
 *
 * @throws ClonerFSFunctionException If both lstat and stat fail.
 * @throws ClonerNoFileException
 */
function cloner_fs_stat($path)
{
    if (function_exists('lstat')) {
        $stat = @lstat($path);
        if ($stat) {
            $info = ClonerStatInfo::fromArray($stat);
            if ($info->isLink()) {
                $link = readlink($path);
                if (!is_string($link)) {
                    throw new ClonerFSFunctionException('readlink', $path);
                }
                $info->link = $link;
            }
            return $info;
        }
        $error = error_get_last();
        if (empty($error['message']) || strncmp($error['message'], 'lstat(', 0) !== 0) {
            throw new ClonerNoFileException($path);
        }
    }

    if (function_exists('stat')) {
        $stat = @stat($path);
        if ($stat) {
            $info = ClonerStatInfo::fromArray($stat);;
            if (@is_link($path)) {
                $link = $link = readlink($path);
                if ($link === false) {
                    throw new ClonerFSFunctionException('readlink', $path);
                }
                $info->link = $link;
            }
            return $info;
        }
        throw new ClonerFSFunctionException('stat', $path);
    } else {
        throw new ClonerFSFunctionException('lstat', $path);
    }
}

class ClonerStatInfo
{
    // https://unix.superglobalmegacorp.com/Net2/newsrc/sys/stat.h.html
    const S_IFMT = 0170000;   /* type of file */
    const S_IFIFO = 0010000;  /* named pipe (fifo) */
    const S_IFCHR = 0020000;  /* character special */
    const S_IFDIR = 0040000;  /* directory */
    const S_IFBLK = 0060000;  /* block special */
    const S_IFREG = 0100000;  /* regular */
    const S_IFLNK = 0120000;  /* symbolic link */
    const S_IFSOCK = 0140000; /* socket */

    private $stat;
    public $link = '';

    private function __construct(array $stat)
    {
        $this->stat = $stat;
    }

    /**
     * @return bool
     */
    public function isDir()
    {
        return ($this->stat['mode'] & self::S_IFDIR) === self::S_IFDIR;
    }

    public function isLink()
    {
        return ($this->stat['mode'] & self::S_IFLNK) === self::S_IFLNK;
    }

    public function getPermissions()
    {
        return ($this->stat['mode'] & 0777);
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->isDir() ? 0 : $this->stat['size'];
    }

    /**
     * @return int
     */
    public function getMTime()
    {
        return $this->stat['mtime'];
    }

    /**
     * @param array $stat Result of lstat() or stat() function call.
     *
     * @return ClonerStatInfo
     */
    public static function fromArray(array $stat)
    {
        return new self($stat);
    }

    public static function makeEmpty()
    {
        return new self(array('size' => 0, 'mode' => 0, 'mtime' => 0));
    }
}

/**
 * As a special case, this function URL-encodes non-valid-UTF-8 strings
 * before sorting them, to maintain consistency between platforms.
 * The file names themselves are left intact, it only affects sorting.
 *
 * @param string $path
 * @param string $offset
 *
 * @return string[] Children of $path that do not come before $offset.
 *
 * @throws ClonerFSFunctionException
 */
function cloner_fs_list_children($path, $offset = '')
{
    $files = @scandir($path);
    if ($files === false) {
        throw new ClonerFSFunctionException('scandir', $path);
    }
    $children = array();
    foreach ($files as $file) {
        $encoded = false;
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (!cloner_seems_utf8($file)) {
            $encoded = true;
            $file    = cloner_encode_non_utf8($file);
        }
        if (strlen($offset) && (strcmp($file, $offset) < 0)) {
            continue;
        }
        $children[] = array($encoded, $file);
    }
    if (PHP_VERSION_ID < 0) {
        // Hack to include usort function during build.
        cloner_sort_encoded_files('', '');
    }
    usort($children, 'cloner_sort_encoded_files');
    $result = array();
    foreach ($children as $file) {
        if ($file[0]) {
            $result[] = urldecode($file[1]);
            continue;
        }
        $result[] = $file[1];
    }
    return $result;
}

function cloner_sort_encoded_files($f1, $f2)
{
    return strcmp($f1[1], $f2[1]);
}

class ClonerFSException extends ClonerException
{
}

class ClonerNoFileException extends ClonerFSException
{
    public $path = '';

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
        parent::__construct("File $path does not exist");
    }
}

class ClonerFSFunctionException extends ClonerFSException
{
    public $fn = '';
    public $path = '';
    public $error = '';

    /**
     * @param string $fn   One of fopen, fread, flock, etc.
     * @param string $path Path on the filesystem.
     */
    public function __construct($fn, $path)
    {
        $this->fn    = $fn;
        $this->path  = $path;
        $this->error = cloner_last_error_for($fn);
        parent::__construct(sprintf('%s error for path %s: %s', $fn, $path, $this->error));
    }
}

class ClonerFSNotDirFunctionException extends ClonerFSException
{
    public function __construct($path)
    {
        parent::__construct(sprintf('%s is not a directory', $path));
    }
}


/**
 * PHP's error_get_last() returns last error whose message is prefixed with the called function name.
 *
 * @param string $fnName Prefix that will be looked up.
 *
 * @return string Error message with the function name, or '$fnName(): unknown error' if it cannot be determined.
 */
function cloner_last_error_for($fnName)
{
    $error = error_get_last();
    if (!is_array($error) || !isset($error['message']) || !is_string($error['message'])) {
        return $fnName.'(): unknown error';
    }
    $message = $error['message'];
    if (strncmp($message, $fnName.'(', strlen($fnName) + 1)) {
        // Message not prefixed with $fnName.
        return $fnName.'(): unknown error';
    }
    if (PHP_VERSION_ID >= 70000) {
        error_clear_last();
    }
    return $message;
}

class ClonerNoConstantException extends ClonerException
{
    public $constant = '';

    public function __construct($constant, $code = self::ERROR_UNEXPECTED)
    {
        $this->constant = $constant;
        parent::__construct("The required constant $constant is not defined", $code);
    }
}

class ClonerRealPathException extends ClonerException
{
    public $path;

    public function __construct($path)
    {
        $this->path = $path;
        parent::__construct("The path $path could not be resolved on the filesystem", 'realpath_empty');
    }
}

class ClonerException extends Exception
{
    private $error = '';
    private $errorCode = '';
    private $internalError = '';

    const ERROR_UNEXPECTED = 'error_unexpected';

    /**
     * @param string $error
     * @param string $code
     * @param string $internalError
     */
    public function __construct($error, $code = self::ERROR_UNEXPECTED, $internalError = '')
    {
        $this->message = sprintf('[%s]: %s', $code, $error);
        if (strlen($internalError)) {
            $this->message .= ": $internalError";
        }
        $this->error         = $error;
        $this->errorCode     = $code;
        $this->internalError = $internalError;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getInternalError()
    {
        return $this->internalError;
    }
}

class ClonerFunctionException extends ClonerException
{
    private $fn = '';

    public function __construct($fnName, $code = self::ERROR_UNEXPECTED)
    {
        $this->fn = $fnName;
        parent::__construct("Error calling $fnName", $code, cloner_last_error_for($fnName));
    }
}




/**
 * WebSocket/Hybi v13 implementation, RFC6455.
 *
 * @link https://tools.ietf.org/html/rfc6455#section-5.2
 */
class ClonerWebSocket
{
    private $addr = "";
    private $path = "";
    private $connTimeout = 10;
    private $rwTimeout = 30;
    private $host = "";
    private $cert = "";
    private $origin = "";
    private $proto = "";
    private $mask = true;
    /** @var resource|null */
    private $conn;
    private $maxPayload = 134217728; // 128 << 20
    private $wsVersion = 13;

    static $opContinuation = 0x0;
    static $opText = 0x1;
    static $opBinary = 0x2;
    static $opClose = 0x8;
    static $opPing = 0x9;
    static $opPong = 0xA;

    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    public function __construct($addr, $path = "", $connTimeout = 10, $rwTimeout = 30, $host = "localhost", $origin = "localhost", $cert = "", $proto = "", $mask = true)
    {
        $this->addr        = $addr;
        $this->path        = $path;
        $this->connTimeout = $connTimeout;
        $this->rwTimeout   = $rwTimeout;
        $this->host        = $host;
        $this->origin      = $origin;
        $this->cert        = $cert;
        $this->proto       = $proto;
        $this->mask        = $mask;
    }

    /**
     * @throws ClonerException
     * @throws ClonerNetException
     * @throws ClonerURLException
     */
    public function connect()
    {
        if ($this->conn !== null) {
            return;
        }
        $key        = base64_encode(md5(uniqid("", true), true));
        $expectKey  = base64_encode(sha1($key.self::GUID, true));
        $path       = $this->path ? $this->path : "/";
        $headers    = array(
            'Host'                   => $this->host,
            'Connection'             => 'upgrade',
            'Upgrade'                => 'WebSocket',
            'Origin'                 => $this->origin,
            'Sec-WebSocket-Key'      => $key,
            'Sec-WebSocket-Version'  => $this->wsVersion,
            'Sec-WebSocket-Protocol' => $this->proto,
        );
        $this->conn = cloner_http_open_request('GET', ClonerURL::fromString($this->addr.$path), $headers, $this->connTimeout, $this->cert);
        $res        = cloner_http_get_response_headers($this->conn, 10);
        if ($res->headers["sec-websocket-accept"] !== $expectKey) {
            throw new ClonerException(sprintf("Got WS key %s, expected %s", $res->headers["sec-websocket-accept"], $expectKey), 'invalid_ws_key', $res->headers["sec-websocket-accept"]);
        }
        if (strlen($this->proto)) {
            $protos = array_map('trim', explode(",", $res->headers["sec-websocket-protocol"]));
            if (!in_array($this->proto, $protos)) {
                throw new ClonerException(sprintf("Need protocol %s, got %s", $this->proto, $res->headers["sec-websocket-protocol"]), 'invalid_ws_key', $res->headers["sec-websocket-protocol"]);
            }
        }
    }

    /**
     * @param int    $code
     * @param string $reason
     *
     * @throws ClonerException
     */
    public function disconnect($code, $reason)
    {
        if ($this->conn === null) {
            return;
        }
        $this->writeFrame(true, self::$opClose, pack("n", $code).$reason);
        if (@fclose($this->conn) === false) {
            throw new ClonerClonerNetFunctionException('fclose', $this->host);
        }
        $this->conn = null;
    }

    /**
     * @return array 1st element is null if pong, string otherwise. Second is true if the connection is closed.
     *
     * @throws ClonerException
     */
    public function readMessage()
    {
        if ($this->conn === null) {
            throw new ClonerException("Socket not connected");
        }
        $message   = null;
        $fin       = false;
        $messageOp = 0x0;
        while (!$fin) {
            list($fin, $op, $frame) = $this->readFrame();
            switch ($op) {
                case self::$opContinuation:
                    if (!$messageOp) {
                        throw new ClonerException("Continuation frame sent before initial frame", 'ws_protocol_error');
                    }
                    if (strlen($message) + strlen($frame) > $this->maxPayload) {
                        throw new ClonerException(sprintf("Read buffer full, message length: %d", strlen($message) + strlen($frame)), 'ws_read_buffer_full');
                    }
                    $message .= $frame;
                    break;
                case self::$opClose:
                    return array($frame, true);
                case self::$opBinary:
                case self::$opText:
                    $messageOp = $op;
                    $message   = $frame;
                    break;
                case self::$opPong:
                    break;
                default:
                    throw new ClonerException(sprintf("Read failed, invalid op: %d", $op), 'ws_protocol_error');
            }
        }
        return array($message, false);
    }

    /**
     * @return array Triplet of fin:bool, op:int, message:string.
     *
     * @throws ClonerException
     */
    private function readFrame()
    {
        stream_set_timeout($this->conn, $this->rwTimeout);
        // $b1 = | FIN |RSV1 |RSV2 |RSV3 | OP1 | OP2 | OP3 | OP4 |
        //       | 0/1 |  0  |  0  |  0  |  n1 |  n2 |  n3 |  n4 |
        if (($b1 = @fread($this->conn, 1)) === false) {
            throw new ClonerNetSocketException('fread', $this->conn);
        }
        $meta = stream_get_meta_data($this->conn);
        if (!empty($meta["timed_out"])) {
            throw new ClonerException("First byte read timeout", 'ws_read_timeout');
        }
        if (!empty($meta["eof"])) {
            throw new ClonerException("Connection closed", 'ws_closed');
        }
        $b1  = ord($b1);
        $fin = (bool)($b1 & 0x80 /*10000000*/);
        if (($b1 & 0x70 /*01110000*/) !== 0) {
            throw new ClonerException("Reserved bits present", 'ws_protocol_error');
        }
        $op = $b1 & 0xF; // 00001111
        // $b2 = |MASK | Payload length (7 bits)                 |
        //       | 0/1 |  n1 |  n2 |  n3 |  n4 |  n5 |  n6 | n7  |
        if (($b2 = @fread($this->conn, 1)) === false) {
            throw new ClonerNetSocketException('fread', $this->conn);
        }
        $b2     = ord($b2);
        $masked = $b2 & 0x80; // 10000000
        $len    = $b2 & 0x7F; // 01111111
        if ($len === 126 /*01111110*/) {
            if (($payloadLen = @fread($this->conn, 2)) === false) {
                throw new ClonerNetSocketException('fread', $this->conn);
            }
            $unpacked = unpack("n", $payloadLen);
            $len      = end($unpacked);
        } elseif ($len === 127 /*01111111*/) {
            if (($payloadLen = @fread($this->conn, 8)) === false) {
                throw new ClonerNetSocketException('fread', $this->conn);
            }
            $len = $this->unmarshalUInt64($payloadLen);
        }
        if ($len > $this->maxPayload) {
            throw new ClonerException(sprintf("Read buffer full, frame length: %d", $len), 'ws_read_buffer_full');
        }
        $mask = "";
        if ($masked && (($mask = @fread($this->conn, 4)) === false)) {
            throw new ClonerNetSocketException('fread', $this->conn);
        }
        $message = "";
        $toRead  = $len;
        while ($toRead > 0) {
            $chunk = @fread($this->conn, $toRead);
            if ($chunk === false) {
                throw new ClonerNetSocketException('fread', $this->conn);
            }
            if ($mask !== "") {
                for ($i = 0; $i < strlen($chunk); $i++) {
                    $chunk[$i] ^= $mask[$i % 4];
                }
            }
            $message .= $chunk;
            $toRead  -= strlen($chunk);
        }
        $meta = stream_get_meta_data($this->conn);
        if (!empty($meta["timed_out"])) {
            throw new ClonerException("Chunk read timeout", 'ws_read_timeout');
        }
        if (!empty($meta["eof"])) {
            throw new ClonerException("Connection closed", 'ws_closed');
        }
        return array($fin, $op, $message);
    }

    private function marshalUInt64($value)
    {
        if (strlen(PHP_INT_MAX) === 19) {
            $higher = ($value & 0xffffffff00000000) >> 32;
            $lower  = $value & 0x00000000ffffffff;
        } else {
            $higher = 0;
            $lower  = $value;
        }
        return pack('NN', $higher, $lower);
    }

    /**
     * @param int $packed
     *
     * @return int
     *
     * @throws ClonerException
     */
    private function unmarshalUInt64($packed)
    {
        list($higher, $lower) = array_values(unpack('N2', $packed));
        if ($higher !== 0 && strlen(PHP_INT_MAX) !== 19) {
            throw new ClonerException("Payload too big for 32bit architecture", 'no_64bit_support');
        }
        $value = $higher << 32 | $lower;
        if ($value < 0) {
            throw new ClonerException('no_uint64_support');
        }
        return $value;
    }

    /**
     * @param string $message
     *
     * @throws ClonerException
     */
    public function writeMessage($message)
    {
        if ($this->conn === null) {
            throw new ClonerException("Socket not connected");
        }
        $offset = 0;
        $len    = strlen($message);
        while ($offset < $len) {
            $frame  = substr($message, $offset, min($len - $offset, 1 << 20));
            $op     = $offset === 0 ? 0x1 : 0x0;
            $offset += strlen($frame);
            $fin    = $offset >= $len;
            $this->writeFrame($fin, $op, $frame);
        }
    }

    /**
     * @param bool   $fin
     * @param int    $op
     * @param string $frame
     *
     * @throws ClonerException
     */
    private function writeFrame($fin, $op, $frame)
    {
        $mask = $lenLen = "";
        $b1   = ($fin ? 0x80 : 0x00) | $op;
        $b2   = $this->mask ? 0x80 : 0x00;
        $len  = strlen($frame);
        if ($len > 65535) {
            $b2     |= 0x7f;
            $lenLen = $this->marshalUInt64($len);
        } elseif ($len >= 126) {
            $b2     |= 0x7e;
            $lenLen = pack("n", $len);
        } else {
            $b2 |= $len;
        }
        if ($this->mask) {
            $mask = pack("nn", mt_rand(0, 0xffff), mt_rand(0, 0xffff));
            for ($i = 0; $i < strlen($frame); $i++) {
                $frame[$i] = $frame[$i] ^ $mask[$i % 4];
            }
        }
        $send = pack("CC", $b1, $b2).$lenLen.$mask.$frame;
        unset($frame);
        stream_set_timeout($this->conn, $this->rwTimeout);
        if (($written = @fwrite($this->conn, $send)) === false) {
            throw new ClonerNetSocketException('fwrite', $this->conn);
        }
    }

    public function __destruct()
    {
        if ($this->conn === null) {
            return;
        }
        try {
            $code   = 1000;
            $reason = 'disconnected by client';
            $error  = error_get_last();
            if (!empty($error['message']) && in_array($error['type'], array(E_PARSE, E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR))) {
                $code   = 1001;
                $reason = $error['message'];
            }
            $this->disconnect($code, $reason);
        } catch (ClonerException $e) {
        }
    }
}


/**
 * Prints a 404 page.
 */
function cloner_page_404() {
?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>404 Page Not Found</title>
</head>
<body>
404 page not found.
</body>
</html><?php
}

/**
 * Prints main page HTML content to STDOUT.
 */
function cloner_page_index() {
?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sync</title>
    <style>
        body {
            color: #333;
            margin: 0;
            height: 100vh;
            background-color: #2C454C;
            font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        .logo {
            height: 150px;
            background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0ZWQgYnkgSWNvTW9vbi5pbyAtLT4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj4KPHBhdGggZmlsbD0iI2ZmZiIgZD0iTTE2LjA2My0wLjAwNWMtOC44MzYgMC0xNiA3LjE2NC0xNiAxNS45OTkgMCA4LjgzOSA3LjE2NCAxNi4wMDEgMTYgMTYuMDAxIDguODM5IDAgMTYtNy4xNjMgMTYtMTYuMDAxIDAtOC44MzUtNy4xNjEtMTUuOTk5LTE2LTE1Ljk5OXpNMTYuMDYzIDMwLjUyMWMtOC4wMjMgMC0xNC41MjctNi41MDUtMTQuNTI3LTE0LjUyOCAwLTguMDIwIDYuNTA0LTE0LjUyNSAxNC41MjctMTQuNTI1czE0LjUyNSA2LjUwNSAxNC41MjUgMTQuNTI1YzAgOC4wMjMtNi41MDMgMTQuNTI4LTE0LjUyNSAxNC41Mjh6Ij48L3BhdGg+CjxwYXRoIGZpbGw9IiNmZmYiIGQ9Ik0yNi42NTIgNy45NDNsLTYuMjg5IDYuNzQ1LTMuMTI1LTUuNDMyLTQuODU2IDUuODc2LTIuNTEzLTMuODA3LTYuMjQzIDkuMDgzYzEuODYxIDUuMDQ5IDYuNzE3IDguNjUyIDEyLjQxMyA4LjY1MiA3LjMwNSAwIDEzLjIyNC01LjkyMSAxMy4yMjQtMTMuMjI3IDAtMi45NTctMC45NzEtNS42ODgtMi42MTEtNy44OTF6Ij48L3BhdGg+Cjwvc3ZnPgo=) no-repeat center center;
            background-size: 100px 100px;
        }

        .content {
            line-height: 1.4;
            background: #fff;
            border-radius: 15px;
            padding: 15px;
            max-width: 650px;
            margin: 0 auto;
        }

        #feedback {
            font-family: Monaco, Consolas, "Andale Mono", "DejaVu Sans Mono", monospace;
            word-wrap: break-word;
            max-height: 200px;
            color: #666;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="logo"></div>

<div class="content">
    <p>
        This window closes automatically when the synchronization finishes. Please, do not close it.
    </p>
    <p>
        Debug info:
    </p>
    <div id="feedback"></div>
</div>

<script>
    /**
     * Creates response for XMLHttpRequest. Safe to call only from onerror and onload, when the request is already finished.
     *
     * @param {XMLHttpRequest} xhr
     * @return {{status: number, response: string|null, headers: string|null}}
     */
    function createXHRResult(xhr) {
        return {
            status: xhr.status,
            response: xhr.responseText,
            headers: xhr.getAllResponseHeaders()
        };
    }

    function request(method, url, callback, payload) {
        var xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.onerror = function(e) {
            callback(e, createXHRResult(xhr))
        };
        xhr.onload = function() {
            var result = createXHRResult(xhr);
            if (xhr.status !== 200) {
                callback(new Error("Non-200 response status code"), result);
                return;
            }
            callback(null, result);
        };
        xhr.send(payload);
        return xhr.abort;
    }

    function DivFeedback(container) {
        this.feedbackContainer = container;
        this.lastFeedback = '';
        this.lastFeedbackLen = 0;
        this.lastFeedbackCount = 0;
        this.maxFeedbackLen = 10 << 10;
    }

    DivFeedback.prototype.send = function(text) {
        var prepend = '', cut = 0;
        var prefix = '[' + new Date().toJSON() + '] ';
        if (this.lastFeedback === '') {
            prepend = prefix + text;
        } else if (this.lastFeedback === text) {
            this.lastFeedbackCount++;
            cut = this.lastFeedbackLen;
            prepend = prefix + text + Array(this.lastFeedbackCount + 1).join(' .') + "\n";
        } else {
            this.lastFeedbackCount = 1;
            prepend = prefix + text + "\n";
        }
        this.lastFeedback = text;
        this.lastFeedbackLen = prepend.length;
        this.feedbackContainer.innerText = prepend + this.feedbackContainer.innerText.substr(cut, Math.max(this.maxFeedbackLen - prepend.length - cut, 0));
    };

    var feedback = new DivFeedback(document.getElementById('feedback'));

    /**
     * @param {number} id
     * @param {function} abort Connection cancelation function.
     */
    function RemoteConn(id, abort) {
        this.id = id;
        this.close = abort;
        this.createdAt = new Date();
    }

    /**
     * @param {Array<T>} a
     *
     * @return {Array<T>}
     */
    Array.prototype.diff = function(a) {
        return this.filter(function(i) {
            return a.indexOf(i) === -1;
        });
    };

    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/find#Polyfill
    if (!Array.prototype.find) {
        Object.defineProperty(Array.prototype, 'find', {
            value: function(predicate) {
                if (this == null) {
                    throw new TypeError('"this" is null or not defined');
                }
                var o = Object(this);
                var len = o.length >>> 0;
                if (typeof predicate !== 'function') {
                    throw new TypeError('predicate must be a function');
                }
                var thisArg = arguments[1];
                var k = 0;
                while (k < len) {
                    var kValue = o[k];
                    if (predicate.call(thisArg, kValue, k, o)) {
                        return kValue;
                    }
                    k++;
                }
                return undefined;
            },
            configurable: true,
            writable: true
        });
    }

    var errorCounter = 0;

    function finish() {
        window.close();
    }

    function cleanup() {
        request("GET", location.pathname + "?q=cleanup", finish);
    }

    function main() {
        var haveConns = [];
        var poll = function() {
            var url = location.pathname + "?q=state_poll";
            request("GET", url, pollResult)
        };
        var pollResult = function(e, result) {
            if (e) {
                if (result && result.status === 404) {
                    feedback.send("Closing this window");
                    finish();
                    return;
                }
                feedback.send("Error polling endpoint: " + JSON.stringify(e) + "; result: " + JSON.stringify(result));
                return;
            }
            /** @var {{ok: boolean, error: string, state: {host: string, haveConns: Array<string>, wantConns: Array<string>, cached: boolean, age: number} }} pollData */
            try {
                var pollData = JSON.parse(result.response);
            } catch (e) {
                feedback.send("Poll error: " + JSON.stringify(e));
                setTimeout(poll, 2000);
                return;
            }
            if (!pollData.ok) {
                if (pollData.error === 'not_found') {
                    feedback.send("Task not found");
                    cleanup();
                    return;
                } else if (pollData.error === 'done') {
                    feedback.send("Task completed");
                    cleanup();
                    return;
                }

                errorCounter++;
                var message = pollData.error;
                if (pollData.message) {
                    message += ': ' + pollData.message;
                }
                feedback.send(message);
                if (errorCounter < 30) {
                    setTimeout(poll, 2000);
                    return
                }
                feedback.send("Aborting after too many retries");
                return;
            }
            errorCounter = 0;
            var state = pollData.state;
            var createConns = state.wantConns.diff(state.haveConns);
            var closeConns = state.haveConns.diff(state.wantConns);
            createConns.map(function(connID) {
                feedback.send("Opening connection " + connID);
                spawn(state.host, connID);
            });
            closeConns.map(function(connID) {
                var conn = haveConns.find(function(conn) {
                    return conn.id === connID;
                });
                if (!conn) {
                    return;
                }
                feedback.send("Closing connection " + connID);
                conn.close();
            });
            setTimeout(poll, 3000);
        };
        var spawn = function(host, id) {
            var url = location.pathname + "?q=connect&host=" + encodeURIComponent(host) + "&conn_id=" + encodeURIComponent(id);
            var abort = request("GET", url, connClosed);
            var conn = new RemoteConn(id, abort);
            haveConns.push(conn);
        };
        var connClosed = function(e, result) {
            if (e) {
                feedback.send("Error spawning connection: " + JSON.stringify(e));
                return;
            }
            try {
                /** @var {{ok: boolean, error: string}} connData */
                var connData = JSON.parse(result.response);
            } catch (e) {
                feedback.send("Invalid response: " + JSON.stringify(result.response));
                return;
            }
            if (connData.error) {
                feedback.send("Connection closed: " + JSON.stringify(connData.error));
                return;
            }
            feedback.send("Connection closed")
        };
        poll();
    }

    main();
</script>

</body>
</html><?php
}







interface ClonerDBStmt
{
    /**
     * @return int
     */
    public function getNumRows();

    /**
     * @return array|null
     *
     * @throws ClonerException
     */
    public function fetch();

    /**
     * @return array|null
     *
     * @throws ClonerException
     */
    public function fetchAll();

    /**
     * @return bool
     */
    public function free();
}

interface ClonerDBConn
{
    /**
     * @param string $query
     * @param array  $parameters
     * @param bool   $unbuffered Set to true to not fetch all results into memory and to incrementally read from SQL server.
     *                           See http://php.net/manual/en/mysqlinfo.concepts.buffering.php
     *
     * @return ClonerDBStmt
     * @throws ClonerException
     *
     */
    public function query($query, array $parameters = array(), $unbuffered = false);

    /**
     * No-return-value version of the query() method. Allows adapters
     * to optionally optimize the operation.
     *
     * @param string $query
     *
     * @throws ClonerException
     */
    public function execute($query);

    /**
     * Escapes string for safe use in statements; quotes are included.
     *
     * @param string $value
     *
     * @return string
     *
     * @throws ClonerException
     */
    public function escape($value);

    /**
     * Closes the connection.
     */
    public function close();
}

/**
 * Kill all SQL processes run by the current user on the current database.
 *
 * @param ClonerDBAdapter $conn
 *
 * @throws ClonerException
 */
function cloner_kill_database_processlist(ClonerDBAdapter $conn)
{
    // Use a random identifier so we don't pick up the current process.
    $rand = md5(uniqid('', true));
    /** @noinspection SqlDialectInspection */
    /** @noinspection SqlNoDataSourceInspection */
    $list = $conn->query("SELECT ID, INFO FROM information_schema.PROCESSLIST WHERE `USER` = :user AND `DB` = :db AND `INFO` NOT LIKE '%{$rand}%'", array(
        'user' => $conn->getConfiguration()->user,
        'db'   => $conn->getConfiguration()->name,
    ))->fetchAll();
    foreach ($list as $process) {
        $conn->execute("KILL {$process['ID']}");
    }
    $conn->execute('UNLOCK TABLES');
}

/**
 * @param ClonerDBConn $conn
 *
 * @return array
 *
 * @throws ClonerException
 */
function cloner_db_info(ClonerDBConn $conn)
{
    $info = array(
        'collation' => array(),
        'charset'   => array(),
    );
    $list = $conn->query("SHOW COLLATION")->fetchAll();
    foreach ($list as $row) {
        $info['collation'][$row['Collation']] = true;
        $info['charset'][$row['Charset']]     = true;
    }
    return $info;
}

/**
 * @param ClonerDBConn $conn
 * @return string Returns "utf8mb4" if available, defaults to "utf8".
 * @throws ClonerException
 */
function cloner_db_charset(ClonerDBConn $conn)
{
    $info = cloner_db_info($conn);
    $try  = 'utf8mb4';
    foreach ($info['charset'] as $charset => $true) {
        if (strpos($charset, $try) === false) {
            continue;
        }
        return $try;
    }
    return 'utf8';
}

class ClonerDBAdapter implements ClonerDBConn
{
    /** @var string */
    private $id;

    /** @var string[] */
    private $idList = array();

    /** @var ClonerDBInfo[][]|ClonerDBConn[][] */
    static $handles = array();

    /**
     * @param array|null $db
     *
     * @return ClonerDBAdapter
     */
    public static function fromArray(array $db = null)
    {
        $adapter = new self();
        foreach ($db as $credentials) {
            $info = ClonerDBInfo::fromArray($credentials);
            $id   = $info->getID();
            if (empty(self::$handles[$id])) {
                self::$handles[$id] = array($info, null);
            }
            $adapter->idList[] = $id;
            if ($adapter->id === null) {
                $adapter->id = $id;
            }
        }
        return $adapter;
    }

    /**
     * @return ClonerDBConn
     *
     * @throws ClonerException
     */
    private function conn()
    {
        if (empty(self::$handles[$this->id])) {
            throw new ClonerException('No database configuration available');
        }
        $handle = &self::$handles[$this->id];
        if (empty($handle[1])) {
            $handle[1] = cloner_db_conn_init($handle[0]);
        }
        return $handle[1];
    }

    public function useConnection($id)
    {
        if (empty($id) || count($this->idList) <= 1) {
            return true;
        }
        if (!in_array($id, $this->idList, true)) {
            return false;
        }
        $this->id = $id;
        return true;
    }

    public function getConnectionIDs()
    {
        if (count($this->idList) === 1) {
            return array('');
        }
        return $this->idList;
    }

    public function getConfiguration()
    {
        if (!isset(self::$handles[$this->id])) {
            throw new ClonerException('No database configuration available');
        }
        return self::$handles[$this->id][0];
    }

    public function query($query, array $parameters = array(), $unbuffered = false)
    {
        return $this->conn()->query($query, $parameters, $unbuffered);
    }

    public function execute($query)
    {
        $this->conn()->execute($query);
    }

    public function escape($value)
    {
        return $this->conn()->escape($value);
    }

    public function close()
    {
        if (empty(self::$handles[$this->id][1])) {
            return;
        }
        self::$handles[$this->id][1]->close();
        self::$handles[$this->id][1] = null;
    }

    public static function closeAll()
    {
        foreach (self::$handles as $id => &$handle) {
            if ($handle[1]) {
                $handle[1]->close();
                $handle[1] = null;
            }
        }
    }

    public function ping()
    {
        $query = $this->conn()->query('SELECT 1');
        $query->fetchAll();
        $query->free();
    }
}

/**
 * @param array|null $db Gets passed to ClonerDBAdapter::fromArray.
 *
 * @return ClonerDBAdapter
 *
 * @throws ClonerException
 */
function cloner_db_conn(array $db = null)
{
    return ClonerDBAdapter::fromArray($db);
}

function cloner_db_conn_init(ClonerDBInfo $conf)
{
    if (extension_loaded('pdo_mysql') && PHP_VERSION_ID > 50206) {
        // We need PHP 5.2.6 because of this nasty PDO bug: https://bugs.php.net/bug.php?id=44251
        return new ClonerPDOConn($conf);
    } elseif (extension_loaded('mysqli')) {
        return new ClonerMySQLiConn($conf);
    } elseif (extension_loaded('mysql')) {
        return new ClonerMySQLConn($conf);
    } else {
        throw new ClonerException("No drivers available for php mysql connection.", 'no_db_drivers');
    }
}

/**
 * @param ClonerDBInfo $expect Expected configuration.
 * @param ClonerDBInfo $got    Gotten configuration.
 *
 * @throws ClonerException If the configuration mismatches.
 */
function cloner_verify_db_credentials(ClonerDBInfo $expect, ClonerDBInfo $got)
{
    if ($expect->name !== $got->name) {
        throw cloner_exception_db_credentials_differ('name', $expect->name, $got->name);
    }
    if ($expect->user !== $got->user) {
        throw cloner_exception_db_credentials_differ('user', $expect->user, $got->name);
    }
    if ($expect->getSocket() !== $got->getSocket()
        || $expect->getHostname() !== $got->getHostname()
        || $expect->getPort() !== $got->getPort()) {
        throw cloner_exception_db_credentials_differ('host', $expect->host, $got->host);
    }
}

function cloner_exception_db_credentials_differ($field, $expect, $got)
{
    return new ClonerException(sprintf('Database %s differs and wp-config.php is read-only; user-provided %s is "%s", but wp-config.php contains the value "%s". Please, either update the wp-config.php file with the right credentials, or make the file writable.', $field, $field, $expect, $got), 'wp_config_readonly_diff');
}

/**
 * @param ClonerDBConn $conn   Adapter is used for value escaping.
 * @param string       $query  Query optionally containing :placeholders.
 * @param array        $params Parameters in format ['placeholder' => "any value to escape"].
 *
 * @return string Compiled and escaped query.
 * @throws ClonerException
 */
function cloner_bind_query_params(ClonerDBConn $conn, $query, array $params)
{
    if (count($params) === 0) {
        return $query;
    }
    $replacements = array();
    foreach ($params as $name => $value) {
        $replacements[":$name"] = $conn->escape($value);
    }
    return strtr($query, $replacements);
}

/**
 * @param ClonerDBConn $conn
 * @param string       $prefix WordPress table prefix.
 * @param string       $option Option name to fetch.
 *
 * @return string The 'siteurl' option value.
 * @throws ClonerException
 */
function cloner_get_option(ClonerDBConn $conn, $prefix, $option)
{
    $option = $conn->query(sprintf('SELECT option_value FROM %soptions WHERE option_name=%s', $prefix, $conn->escape($option)))->fetch();
    if (!isset($option['option_value'])) {
        throw new ClonerException(sprintf('The "%s" option could not be found', $option), "no_option_$option");
    }
    return $option['option_value'];
}













// Entry point.

if (PHP_SAPI === 'cli') {
    return;
}

$clonerRoot   = dirname(__FILE__);
$errorHandler = new ClonerErrorHandler($clonerRoot.'/cloner_error_log');
$errorHandler->register();
if (strlen(session_id())) {
    session_write_close();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 0);
date_default_timezone_set('UTC');
ini_set('memory_limit', '512M');
set_time_limit(1800);

$obLevel = ob_get_level();
while ($obLevel) {
    $obLevel--;
    ob_end_clean();
}

if (defined('CLONER_STATE')) {
    cloner_sync_main();
    return;
}

$requestBody = file_get_contents('php://input');
if (defined('CLONER_KEY') && strlen(CLONER_KEY)) {
    if (!isset($_GET['key']) || $_GET['key'] !== CLONER_KEY) {
        $request = json_decode($requestBody, true);
        $message = "Key mismatches.";
        if (isset($request['id']) && is_string($request['id'])) {
            cloner_send_error_response($request['id'], $message, 'key_mismatch');
            return;
        }
        echo $message;
        return;
    }
}

function cloner_base64_rotate($encoded)
{
    $encode  = '][ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/{}';
    $reverse = strrev($encode);
    return strtr($encoded, $encode, $reverse);
}

if (strlen($requestBody) !== 0 && strncmp($requestBody, '{', 1) !== 0) {
    $requestBody = cloner_base64_rotate($requestBody);
}
$request = json_decode($requestBody, true);
if (empty($request['action']) || !is_string($request['action'])) {
    cloner_send_error_response(@$request['id'], "Action name not provided", 'no_action', '', __FILE__, __LINE__);
    return;
}
$errorHandler->setRequestID(@$request['id']);

if ($request['action'] === 'flush_rewrite_rules') {
    // It's important to bootstrap WordPress while in the global scope!
    define('WP_DEBUG', false);
    define('MWP_SKIP_BOOTSTRAP', true);
    define('AUTOMATIC_UPDATER_DISABLED', true);
    define('WP_MEMORY_LIMIT', '256M');
    define('WP_MAX_MEMORY_LIMIT', '256M');
    define('DISABLE_WP_CRON', true);
    $loader = new ClonerLoader($errorHandler);
    $loader->hook();
    // /wp-admin/admin.php file cuts us off and redirects us to /wp-admin/upgrade.php
    // if a database update has to be performed AND $_POST is empty. Avoid that.
    if (is_array($_POST)) {
        // Avoid E_COMPILE_ERROR: Cannot re-assign auto-global variable _POST
        $_POST['foo'] = 'bar';
    } else {
        $_POST = array('foo' => 'bar');
    }

    $adminScript = $clonerRoot.'/wp-admin/admin.php';
    if (!is_file($adminScript)) {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new ClonerException('Could not find the /wp-admin/admin.php file required to bootstrap WordPress.', 'wp_admin_not_found');
    }
    /** @noinspection PhpIncludeInspection */
    require $adminScript;

    if (!defined('ABSPATH') || !defined('WPINC')) {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new ClonerException('ABSPATH and WPINC must be defined after initialization of admin context.');
    }

    $absPath   = cloner_constant('ABSPATH');
    $wpInc     = cloner_constant('WPINC');
    $pluggable = $absPath.$wpInc.'/pluggable.php';
    if (is_file($pluggable)) {
        /** @noinspection PhpIncludeInspection */
        require_once $pluggable;
    }
    cloner_wp_polyfill();
}

// Don't allow error handling, since we're relying on error_get_last().
// This is a built-in WP function.
if (!function_exists('__return_false')) {
    function __return_false()
    {
        return false;
    }
}
set_error_handler('__return_false');
$result = cloner_execute_action($request['action'], (array)@$request['params'], $clonerRoot);
cloner_send_success_response((string)@$request['id'], $result);

function __bundler_sourcemap() { return array(array(225,'cloner/serializer.php'),array(238,'cloner/cms_vbulletin.php'),array(251,'cloner/cms_phpbb.php'),array(384,'cloner/cms_wordpress.php'),array(397,'cloner/cms_magento.php'),array(833,'cloner/db_migration.php'),array(1755,'cloner/source.php'),array(1844,'cloner/parser.php'),array(1962,'cloner/setup.php'),array(2313,'cloner/db_importer.php'),array(2478,'cloner/client.php'),array(2917,'cloner/net.php'),array(3056,'cloner/db_mysql.php'),array(3186,'cloner/db_mysqli.php'),array(3321,'cloner/db_pdo.php'),array(3405,'cloner/db_info.php'),array(3879,'cloner/env.php'),array(6074,'cloner/common.php'),array(7369,'cloner/actions.php'),array(8044,'cloner/db_scanner.php'),array(8494,'cloner/http.php'),array(8973,'cloner/fs.php'),array(9071,'cloner/errors.php'),array(9397,'cloner/websocket.php'),array(9698,'cloner/sync.php'),array(10043,'cloner/db.php'),array(10171,'cloner/cloner.php'),); }

function __cloner_get_state() {
    return array (
  'ok' => true,
  'site' => 
  array (
    'wpURL' => 'http://localhost/recumbentrambler',
    'wpAbsPath' => '/Users/paulmarrington/Sites/recumbentrambler',
    'wpContentPath' => 'wp-content',
    'wpPluginsPath' => 'wp-content/plugins',
    'wpMuPluginsPath' => 'wp-content/mu-plugins',
    'wpUploadsPath' => 'wp-content/uploads',
    'wpConfigPath' => 'wp-config.php',
    'wpConfig' => 'PD9waHANCi8qKg0KICogVGhlIGJhc2UgY29uZmlndXJhdGlvbiBmb3IgV29yZFByZXNzDQogKg0KICogVGhlIHdwLWNvbmZpZy5waHAgY3JlYXRpb24gc2NyaXB0IHVzZXMgdGhpcyBmaWxlIGR1cmluZyB0aGUNCiAqIGluc3RhbGxhdGlvbi4gWW91IGRvbid0IGhhdmUgdG8gdXNlIHRoZSB3ZWIgc2l0ZSwgeW91IGNhbg0KICogY29weSB0aGlzIGZpbGUgdG8gIndwLWNvbmZpZy5waHAiIGFuZCBmaWxsIGluIHRoZSB2YWx1ZXMuDQogKg0KICogVGhpcyBmaWxlIGNvbnRhaW5zIHRoZSBmb2xsb3dpbmcgY29uZmlndXJhdGlvbnM6DQogKg0KICogKiBNeVNRTCBzZXR0aW5ncw0KICogKiBTZWNyZXQga2V5cw0KICogKiBEYXRhYmFzZSB0YWJsZSBwcmVmaXgNCiAqICogQUJTUEFUSA0KICoNCiAqIEBsaW5rIGh0dHBzOi8vd29yZHByZXNzLm9yZy9zdXBwb3J0L2FydGljbGUvZWRpdGluZy13cC1jb25maWctcGhwLw0KICoNCiAqIEBwYWNrYWdlIFdvcmRQcmVzcw0KICovDQoNCi8vICoqIE15U1FMIHNldHRpbmdzIC0gWW91IGNhbiBnZXQgdGhpcyBpbmZvIGZyb20geW91ciB3ZWIgaG9zdCAqKiAvLw0KLyoqIFRoZSBuYW1lIG9mIHRoZSBkYXRhYmFzZSBmb3IgV29yZFByZXNzICovDQpkZWZpbmUoJ0RCX05BTUUnLCAndGhlYWdlaW5fV1A1SUInKTsKDQovKiogTXlTUUwgZGF0YWJhc2UgdXNlcm5hbWUgKi8NCmRlZmluZSgnREJfVVNFUicsICd0aGVhZ2Vpbl9XUDVJQicpOwoNCi8qKiBNeVNRTCBkYXRhYmFzZSBwYXNzd29yZCAqLw0KZGVmaW5lKCdEQl9QQVNTV09SRCcsICc/UUh9UmI6ak4+cll7ezJjSycpOwoNCi8qKiBNeVNRTCBob3N0bmFtZSAqLw0KZGVmaW5lKCdEQl9IT1NUJywgJ2xvY2FsaG9zdCcpOwoNCi8qKiBEYXRhYmFzZSBDaGFyc2V0IHRvIHVzZSBpbiBjcmVhdGluZyBkYXRhYmFzZSB0YWJsZXMuICovDQpkZWZpbmUoICdEQl9DSEFSU0VUJywgJ3V0ZjgnICk7DQoNCi8qKiBUaGUgRGF0YWJhc2UgQ29sbGF0ZSB0eXBlLiBEb24ndCBjaGFuZ2UgdGhpcyBpZiBpbiBkb3VidC4gKi8NCmRlZmluZSggJ0RCX0NPTExBVEUnLCAnJyApOw0KDQovKiojQCsNCiAqIEF1dGhlbnRpY2F0aW9uIFVuaXF1ZSBLZXlzIGFuZCBTYWx0cy4NCiAqDQogKiBDaGFuZ2UgdGhlc2UgdG8gZGlmZmVyZW50IHVuaXF1ZSBwaHJhc2VzIQ0KICogWW91IGNhbiBnZW5lcmF0ZSB0aGVzZSB1c2luZyB0aGUge0BsaW5rIGh0dHBzOi8vYXBpLndvcmRwcmVzcy5vcmcvc2VjcmV0LWtleS8xLjEvc2FsdC8gV29yZFByZXNzLm9yZyBzZWNyZXQta2V5IHNlcnZpY2V9DQogKiBZb3UgY2FuIGNoYW5nZSB0aGVzZSBhdCBhbnkgcG9pbnQgaW4gdGltZSB0byBpbnZhbGlkYXRlIGFsbCBleGlzdGluZyBjb29raWVzLiBUaGlzIHdpbGwgZm9yY2UgYWxsIHVzZXJzIHRvIGhhdmUgdG8gbG9nIGluIGFnYWluLg0KICoNCiAqIEBzaW5jZSAyLjYuMA0KICovDQpkZWZpbmUoJ0FVVEhfS0VZJywgJzQwMGFiNTNjMDc1ZGI5MjhjYmU5NDZiMzZmY2U0NjMxYjJmYmFlYTE1NzA5ZGM0MWZmZmVlZmI4Y2M2MjAzM2EnKTsKZGVmaW5lKCdTRUNVUkVfQVVUSF9LRVknLCAnOWI2ODQwYzk5ZTllZWI0NTBmNTFhODkzZTBiNDU2Y2M0MWVlMjE4YTczMjcyM2MwYzdkZmZhMDIyOTJiM2E2ZCcpOwpkZWZpbmUoJ0xPR0dFRF9JTl9LRVknLCAnZjRlZjE3MGIwYzU3YzUzODhlNGMyN2ZjNzVjYWU5YjU5ZmUzMjYwZDY2NjQyYTNhNDY2MTI5OWNjNmRjNTIzMScpOwpkZWZpbmUoJ05PTkNFX0tFWScsICdhNmExNThmZGNiYWVhOGQ4OWFmODA1MWU3NjJkYjUxOWVlZmJlYjBmOTg2MjBmOTQ5MGQ1YzljOGQ3OTNiODFlJyk7CmRlZmluZSgnQVVUSF9TQUxUJywgJzcxMjdjYTMzOWM4MWEwMzVlZGRmYjg3ZmJlODNiNjhlODcxZTk4YTM5M2JiMTg2M2VhMjI5ZTU5NjNmMTg1ZWYnKTsKZGVmaW5lKCdTRUNVUkVfQVVUSF9TQUxUJywgJzk3NzkyZTdkODQ5YjI3ZjA4YTU1Nzg2ZWZiZmE0MDkyMTMyMzc5OWYxZTc5OWI1MjRhNDcxOTYxYmIzMTFlMWYnKTsKZGVmaW5lKCdMT0dHRURfSU5fU0FMVCcsICdmMTlkNTEzMzIwNDVmZjdmNWM5ZjQ5NWU0NjEzMjVkYTA3Y2FhZWNiZTA4OTg1OGJjZDMxZjBkYjRmMzU2YjBmJyk7CmRlZmluZSgnTk9OQ0VfU0FMVCcsICc2MzAyNTc5ZDc3YWJmZmY2OWQwNTgxNjc3MzlhODZlZTJhMzY1Y2NiMWQ4ZDJhMjIwMTkzODM5YzY0ZDg3ODYwJyk7Cg0KLyoqI0AtKi8NCg0KLyoqDQogKiBXb3JkUHJlc3MgRGF0YWJhc2UgVGFibGUgcHJlZml4Lg0KICoNCiAqIFlvdSBjYW4gaGF2ZSBtdWx0aXBsZSBpbnN0YWxsYXRpb25zIGluIG9uZSBkYXRhYmFzZSBpZiB5b3UgZ2l2ZSBlYWNoDQogKiBhIHVuaXF1ZSBwcmVmaXguIE9ubHkgbnVtYmVycywgbGV0dGVycywgYW5kIHVuZGVyc2NvcmVzIHBsZWFzZSENCiAqLw0KJHRhYmxlX3ByZWZpeCA9ICdJNnpfJzsKZGVmaW5lKCdXUF9DUk9OX0xPQ0tfVElNRU9VVCcsIDEyMCk7CmRlZmluZSgnQVVUT1NBVkVfSU5URVJWQUwnLCAzMDApOwpkZWZpbmUoJ1dQX1BPU1RfUkVWSVNJT05TJywgNSk7CmRlZmluZSgnRU1QVFlfVFJBU0hfREFZUycsIDcpOwpkZWZpbmUoJ1dQX0FVVE9fVVBEQVRFX0NPUkUnLCB0cnVlKTsKDQovKioNCiAqIEZvciBkZXZlbG9wZXJzOiBXb3JkUHJlc3MgZGVidWdnaW5nIG1vZGUuDQogKg0KICogQ2hhbmdlIHRoaXMgdG8gdHJ1ZSB0byBlbmFibGUgdGhlIGRpc3BsYXkgb2Ygbm90aWNlcyBkdXJpbmcgZGV2ZWxvcG1lbnQuDQogKiBJdCBpcyBzdHJvbmdseSByZWNvbW1lbmRlZCB0aGF0IHBsdWdpbiBhbmQgdGhlbWUgZGV2ZWxvcGVycyB1c2UgV1BfREVCVUcNCiAqIGluIHRoZWlyIGRldmVsb3BtZW50IGVudmlyb25tZW50cy4NCiAqDQogKiBGb3IgaW5mb3JtYXRpb24gb24gb3RoZXIgY29uc3RhbnRzIHRoYXQgY2FuIGJlIHVzZWQgZm9yIGRlYnVnZ2luZywNCiAqIHZpc2l0IHRoZSBkb2N1bWVudGF0aW9uLg0KICoNCiAqIEBsaW5rIGh0dHBzOi8vd29yZHByZXNzLm9yZy9zdXBwb3J0L2FydGljbGUvZGVidWdnaW5nLWluLXdvcmRwcmVzcy8NCiAqLw0KZGVmaW5lKCAnV1BfREVCVUcnLCBmYWxzZSApOw0KDQovKiBUaGF0J3MgYWxsLCBzdG9wIGVkaXRpbmchIEhhcHB5IHB1Ymxpc2hpbmcuICovDQoNCi8qKiBBYnNvbHV0ZSBwYXRoIHRvIHRoZSBXb3JkUHJlc3MgZGlyZWN0b3J5LiAqLw0KaWYgKCAhIGRlZmluZWQoICdBQlNQQVRIJyApICkgew0KCWRlZmluZSggJ0FCU1BBVEgnLCBfX0RJUl9fIC4gJy8nICk7DQp9DQoNCi8qKiBTZXRzIHVwIFdvcmRQcmVzcyB2YXJzIGFuZCBpbmNsdWRlZCBmaWxlcy4gKi8NCnJlcXVpcmVfb25jZSBBQlNQQVRIIC4gJ3dwLXNldHRpbmdzLnBocCc7DQo=',
    'wpTablePrefix' => 'I6z_',
    'wpInstallDir' => '',
    'htaccessConfig' => 'IyBUaGlzIGZpbGUgd2FzIHVwZGF0ZWQgYnkgRHVwbGljYXRvciBQcm8gb24gMjAyMS0wMy0yNSAwNDozNjo1Ni4KIyBTZWUgdGhlIG9yaWdpbmFsX2ZpbGVzXyBmb2xkZXIgZm9yIHRoZSBvcmlnaW5hbCBzb3VyY2Vfc2l0ZV9odGFjY2VzcyBmaWxlLgojIEJFR0lOIFdvcmRQcmVzcwojIFRoZSBkaXJlY3RpdmVzIChsaW5lcykgYmV0d2VlbiAiQkVHSU4gV29yZFByZXNzIiBhbmQgIkVORCBXb3JkUHJlc3MiIGFyZQojIGR5bmFtaWNhbGx5IGdlbmVyYXRlZCwgYW5kIHNob3VsZCBvbmx5IGJlIG1vZGlmaWVkIHZpYSBXb3JkUHJlc3MgZmlsdGVycy4KIyBBbnkgY2hhbmdlcyB0byB0aGUgZGlyZWN0aXZlcyBiZXR3ZWVuIHRoZXNlIG1hcmtlcnMgd2lsbCBiZSBvdmVyd3JpdHRlbi4KCiMgRU5EIFdvcmRQcmVzcw==',
    'cms' => 'wordpress',
  ),
  'db' => 
  array (
    0 => 
    array (
      'dbUser' => 'theagein_WP5IB',
      'dbPassword' => '?QH}Rb:jN>rY{{2cK',
      'dbName' => 'theagein_WP5IB',
      'dbHost' => 'localhost',
    ),
  ),
  'env' => 
  array (
    'goDaddyPro' => 0,
    'openshift' => false,
    'flywheel' => false,
    'phpVersionID' => 70412,
  ),
  'keepOptions' => 
  array (
    '_worker_public_key' => 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUE1S2NhU3ZqdjFuMlFHcUdKL3UzcwpUMXRQWmRoZWZrT3A4MXFQZnJpaXhMZmxzSzFiOUZNMmVEZmZEN20xSmkrOHN3STVCWmEzTU15alpiWXk4QytlCk54eFgraEgwZFB3VElxMDdjeE5YQ3FhMjU5Z2EzQzZGNm91ZWZPbHYzWnA2enV5eGNwdEdrZHZoczN1THdsNlIKYmtUczNyWXQxUWdQcFNRLzBQNllLaGw4N3VtenVTR2FuZ1FGek5vS3VoM3NacHZVNG9zdHdPTndCY0gvMkpRYwpaK2hjRjQrNGZKeUNGZExpYXB1cE5yRGgvaUlJcXZGVUx2cENQVUpsZDhCS3IxdmgrQUxUVm5RWlArbmVRME9kCnFkb3hjT3Q0Rnk3cUQ1NE1PWDd5bEZqK2xKVGxHOTBBRmNuU0lvM3FuSDJKdVdlNzI1YmNsbTc3TUFSZ0I0VGcKM1FJREFRQUIKLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0tCg==',
    'mwp_worker_configuration' => 'a:9:{s:10:"master_url";s:21:"https://managewp.com/";s:15:"master_cron_url";s:75:"https://managewp.com/wp-content/plugins/master/mwp-notifications-handle.php";s:20:"noti_cache_life_time";s:5:"86400";s:27:"noti_treshold_spam_comments";s:2:"10";s:30:"noti_treshold_pending_comments";s:1:"0";s:31:"noti_treshold_approved_comments";s:1:"0";s:19:"noti_treshold_posts";s:1:"0";s:20:"noti_treshold_drafts";s:1:"0";s:8:"key_name";s:8:"managewp";}',
    'mwp_service_key' => 'efe4d5bc-a182-4d84-b80b-6fc48bb0e9e9',
    'mwp_potential_key' => '04c1a250-fc00-4953-96d9-d2331a4a35bd',
    'mwp_potential_key_time' => '1616573475',
    'mwp_container_site_parameters' => 'a:0:{}',
    'mwp_container_parameters' => 'a:0:{}',
    'mwp_communication_keys' => 'a:1:{i:8214984;a:2:{s:3:"key";s:36:"9d7c2ae3-4a49-40e9-9dca-92b60060e3b3";s:5:"added";i:1615966929;}}',
    'mwp_public_keys' => 'a:9:{i:0;a:6:{s:2:"id";s:19:"managewp_1614045901";s:7:"service";s:8:"managewp";s:9:"validFrom";s:19:"2021-03-10 01:26:15";s:7:"validTo";s:19:"2021-04-11 01:26:15";s:9:"publicKey";s:451:"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAz6/CSLvSxUx/9DEm8KCm
q5Hsw1eDwMvrNmGP4MfVStZ2QebV9yHvLZojlzDdZlc4mx9bYf46VlaPh3UoNd52
0q1fT/Mo3eMltiUbBVe1KxW2HuKbmhrbJ3vQaP0CHTVMB1vd8do9p0rQEfO+awk+
1AbM1qoETRGI7EJFerSesDVrxXhUOMcnjxj+Aer7ZnEpg97utbSZVYH2G6+6QbZ1
e6/yYd1VqhBpgm8h/J8tme444NAZzFJavNrIn4Erz2NYUdXH+1VozkknWs+dA967
zFogmjyrZ1B8Ie/4J7WzyC0f/lV2VHOhq+Bp1NSzswUZr0/aUddEG3FcOSLyvUvI
1wIDAQAB
-----END PUBLIC KEY-----
";s:13:"useServiceKey";b:0;}i:1;a:6:{s:2:"id";s:23:"managewp_dev_1614045901";s:7:"service";s:12:"managewp_dev";s:9:"validFrom";s:19:"2021-03-10 01:26:15";s:7:"validTo";s:19:"2021-04-11 01:26:15";s:9:"publicKey";s:451:"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtGLyHu2DgKLsijGM8ld/
8fIoC8KDvIfDL0oSP3OEHFtCRoKb+kxiRH8yaI3mTgOp4WPsYB4oVKrjnoSpAKvo
lLhUhSr1qmXwlK4Zjd5G6dfp5rZs+Hs93+Y2CzAm6VZ91gXOkvjxdf0+RQaFtMIM
M2UXuHTJUYj/QyTh5SOU2G74AMhWF1lfq7ktcTnwOt0jxyY5mzNZAnLxdcS8m/GZ
dtTH333EyeRcL14hEZpiFjU0n1CjsEPk39QW1z+YFQlzBrjtgduA7LlU669gNavt
Ez1EhIyLkaJXdpB31Vxlhe56Mt9DICkR4USGwxS4kNy4iUKK/gjW5oG8qRa5clzj
dwIDAQAB
-----END PUBLIC KEY-----
";s:13:"useServiceKey";b:0;}i:2;a:6:{s:2:"id";s:16:"mwp20_1614045901";s:7:"service";s:5:"mwp20";s:9:"validFrom";s:19:"2021-03-10 01:26:15";s:7:"validTo";s:19:"2021-04-11 01:26:15";s:9:"publicKey";s:451:"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmG32eJ6Nxum7qB5KF0QI
+7nWgV6Lu/NJKd997XiOWSpw3zLiRDnTZ7chQuoVIDVbLktwYGDzNTZJSgVPM5d+
p4nC4X48USbvtaITsRvSfjIDpPmVzRo3yZ9muyE2rbbG7BReHce49cixiNHtYaIW
pa0v1lqOTiW/hDvuAli6/i5FZYaoH8TJUBL0CTaT0zJW7wcDGLFVXU3zJKxjvSxe
0X8H7D8K8HP0xAXXhJfrxP0RdV/DvhQqiy9Doj1whCxoTrex1x9Owpb4NUsTYMl4
XQXi614+xMB2+m2wZvRSYxbwJSiF9HwY4gYfOwZvkjCgFO1GsxZrOY42pU1sqEVm
8wIDAQAB
-----END PUBLIC KEY-----
";s:13:"useServiceKey";b:1;}i:3;a:6:{s:2:"id";s:15:"wpps_1613653501";s:7:"service";s:4:"wpps";s:9:"validFrom";s:19:"2021-03-05 13:05:01";s:7:"validTo";s:19:"2021-04-06 13:05:01";s:9:"publicKey";s:451:"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwTXCNIKzR15bokJ4/DQd
TuoRvj1DNqykzvWm1Y6ehRj7MgDVJp300pmUBs5dkEztcoFaZTd7mcj8gczfUJX/
4LCV7f10Ulz3Eppi4V/JbhAzx2OKCSbsZIwolbpxDwfWHb0IX4jNbpjlzbAs3oR4
UM2MIYHB6HKBrk8yjGli06IbV9oHA+SS8zpvwmHmDJZ3FzNbrKsWnRQzBmrMrh80
JThB6R8yCNxh5/C+/4w+m7pNnMejrHc4n4qoy2ih6Qvi2JYXGNDbpzljTwxcbfOL
GiTRyi4aQLqkff67rpwgI3t0ZE+R//ADEZQLYq7pYq06o2HVIgmm45WjCuhfIm0G
5wIDAQAB
-----END PUBLIC KEY-----
";s:13:"useServiceKey";b:1;}i:4;a:6:{s:2:"id";s:15:"wpps_1616245502";s:7:"service";s:4:"wpps";s:9:"validFrom";s:19:"2021-04-04 13:05:01";s:7:"validTo";s:19:"2021-05-06 13:05:01";s:9:"publicKey";s:451:"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAw5161fA0dXIAxurydwdz
iFCxV/2MrsGTmKYTgOkhnH/k9zFAzn4mgv0CLugNjliyfpjjzu/gZTxqiR3wStZb
rTvmlRO6+ZVnH8WtYkeuZqYCSBxPD94qOwNIXueB8BMki+DmVFCUAAifvQActyt4
UgnEJAJGYaToPzIZLT0WC+Pe5G5Umy6DUpHYso+ZBG6ZX9eMe5Rx4Iz5y8TlmYeK
X9BiXPvatE69EWowR6ucB7zooLB1ProoBYqq8P4nXlo2dYkrfx1r8fZQy+ea+FIU
hioV8ZUg30ftQUB+BkSo7m+FLZHYe0ee5sUvf0SgHYmo0+VGksIa/W/5/jt7va0o
hQIDAQAB
-----END PUBLIC KEY-----
";s:13:"useServiceKey";b:1;}i:5;a:6:{s:2:"id";s:25:"cookie_service_1614877501";s:7:"service";s:14:"cookie_service";s:9:"validFrom";s:19:"2021-03-19 16:27:25";s:7:"validTo";s:19:"2021-04-20 16:27:25";s:9:"publicKey";s:451:"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsEXclgMLIaSdrhA7uU37
W/A6cr7UwTXI6OGtqYjdDkXxYHVqYnwe7iDt6YEdjiJXSp2r5h+VsxJ5iMFhy/ch
Gf2xPYUeBXyNGmvkkbVr3RQ+GPgreRTwr2TKIvM12oomPOvtlql859cS5tVzbBjh
XiJVYmeNcWjhoPA+gv7xuxlRAinQRrixveykE/QxOZOy4lttIwZz/lCa3MdgBXJw
KuU1yxc+mRBkKnfSDpdo+wAAPfUmz21myB9UZR/uZQHIo2ECfjOe2pLFcnvsTeHI
J9rVnkNVcrOmqAPY5kI4GbSz9UIwGLhfQ0qBA2MqjPaR8SN7Fihko4hdf5n3StKj
bQIDAQAB
-----END PUBLIC KEY-----
";s:13:"useServiceKey";b:1;}i:6;a:6:{s:2:"id";s:15:"mwp1_1613743502";s:7:"service";s:4:"mwp1";s:9:"validFrom";s:19:"2021-03-06 14:05:01";s:7:"validTo";s:19:"2021-04-07 14:05:01";s:9:"publicKey";s:451:"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA/FNadrDuSMfP7OxnG/Z0
qSPZVfLHnS3ge0oo53tT+emsQxQOncf098GRft+zun5vbWkhEkpDL1kHJEBlR16P
mCvJ1kGsIwA2gdcNeoRhIk4yjokRsUU48/brPrPomBFOXANaKcDeRPzUFG+T1iwL
vv3QJP6xH/WQdUocza5hpS19OzHpJL85/xZOEuaRoLb7eOcwBioEJK0IpH65GDOQ
/J84b+pktu6UHFPLs2lm20ag/IbmF4Wyujx1a19u3GjYT7aVBe1kNKDn6tBcEvA6
dVxdwAiuPORcEQqkD5X2DKcyHUOAWs1hp/U8eF+1rGC0yTnzE4xwO7u9qS8dTe74
vQIDAQAB
-----END PUBLIC KEY-----
";s:13:"useServiceKey";b:1;}i:7;a:6:{s:2:"id";s:15:"mwp1_1616335502";s:7:"service";s:4:"mwp1";s:9:"validFrom";s:19:"2021-04-05 14:05:01";s:7:"validTo";s:19:"2021-05-07 14:05:01";s:9:"publicKey";s:451:"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1xP26SAUQUdXTYuBVq8r
YqlTBKPZfPGOu4Nlt71TAw4k1vV/Fxn0mdhA4NWaSbLGcIiUwL8rTRCwtAkHIzhx
d3LGcWp4Qpx1PPViN9Yty1skhkuLTW8jt9RWDnUHDYgHVWhTIHSch2yuOl6PGmeE
JiMvntNHPQjh0081FLk5hnHNH77zd0e5Ui2gtRkVaFeITkypn6L5L8D+d/hjwgZ2
tDdHpFB5g6hT8mki1esX331wSWUzS8z8GgkvFSGYyfMmJWwOeu6GNlEE9ORU/pzg
TPSh9A9WYOe8pptkNdkDd5/IzB1xnYllA2+Rka+XhiLgr0bxN/JrTVgiTeZC66+h
kQIDAQAB
-----END PUBLIC KEY-----
";s:13:"useServiceKey";b:1;}i:8;a:6:{s:2:"id";s:20:"migration_1613999102";s:7:"service";s:9:"migration";s:9:"validFrom";s:19:"2021-03-09 13:05:02";s:7:"validTo";s:19:"2021-04-10 13:05:02";s:9:"publicKey";s:451:"-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoXcKvFmOPbVWOjHoyxzs
zcSi13imUtl/KyNHmyvBXTRc7NVA0tWtQjJh2kvaZl2vBt9LKbSXnPR/s3+wBia6
65tSXW1y923av4Bs5EyV6zzwQOZDLFVKtWmqRR2SbctkFS0ulnT8z9P20jdd9hlU
4NDOwoTlqRduPdgMNdyD58hGch7NgTf/qH8xYxDB8OVY66bFTBXnwDGEpN1yPTE9
45A+If3O9SW+HrnoWETga6nN7LWjkFPvJ3EKNbxcBLwh/wR0N9PBB24EFbfdunQf
GJw/sRAImaUZxYIpa1Ffw3pqgr1XOFVwV4m1ivbu3p9TjAX/mQ9nZ2IkckmQ0R89
tQIDAQAB
-----END PUBLIC KEY-----
";s:13:"useServiceKey";b:1;}}',
    'mwp_public_keys_refresh_time' => '1616572128',
  ),
  'clonerOK' => true,
  'workerOK' => false,
);
}
