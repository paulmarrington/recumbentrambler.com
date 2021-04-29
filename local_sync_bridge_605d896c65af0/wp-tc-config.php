
		<?php
		/** The name of the database for WordPress */
		if(!defined('DB_NAME'))
		define('DB_NAME', 'theagein_WP5IB');

		/** MySQL database username */
		if(!defined('DB_USER'))
		define('DB_USER', 'theagein_WP5IB');

		/** MySQL database password */
		if(!defined('DB_PASSWORD'))
		define('DB_PASSWORD', '?QH}Rb:jN>rY{{2cK');

		/** MySQL hostname */
		if(!defined('DB_HOST'))
		define('DB_HOST', 'localhost');

		/** Database Charset to use in creating database tables. */
		if(!defined('DB_CHARSET'))
		define('DB_CHARSET', 'utf8');

		/** The Database Collate type. Don't change this if in doubt. */
		if(!defined('DB_COLLATE'))
		define('DB_COLLATE', '');

		if(!defined('DB_PREFIX_LOCAL_SYNC'))
		define('DB_PREFIX_LOCAL_SYNC', 'I6z_');

		if(!defined('LOCAL_SYNC_UPLOADS_DIR'))
		define('LOCAL_SYNC_UPLOADS_DIR', '/Users/paulmarrington/Sites/recumbentrambler/wp-content/uploads');

		if(!defined('LOCAL_SYNC_RELATIVE_UPLOADS_DIR'))
		define('LOCAL_SYNC_RELATIVE_UPLOADS_DIR', '/uploads');

		if(!defined('BRIDGE_NAME_LOCAL_SYNC'))
		define('BRIDGE_NAME_LOCAL_SYNC', 'local_sync_bridge_605d896c65af0');

		if (!defined('WP_MAX_MEMORY_LIMIT')) {
			define('WP_MAX_MEMORY_LIMIT', '256M');
		}

		if(!defined('WP_DEBUG'))
		define('WP_DEBUG', false);

		if(!defined('WP_DEBUG_DISPLAY'))
		define('WP_DEBUG_DISPLAY', false);

		if ( !defined('MINUTE_IN_SECONDS') )
		define('MINUTE_IN_SECONDS', 60);
		if ( !defined('HOUR_IN_SECONDS') )
		define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
		if ( !defined('DAY_IN_SECONDS') )
		define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
		if ( !defined('WEEK_IN_SECONDS') )
		define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
		if ( !defined('YEAR_IN_SECONDS') )
		define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);



		/** Absolute path to the WordPress directory. */
		if ( !defined('ABSPATH') )
		define('ABSPATH',  wp_normalize_path(dirname(dirname(__FILE__)) . '/'));

		if ( !defined('WP_CONTENT_DIR') )
		define('WP_CONTENT_DIR',  wp_normalize_path('/Users/paulmarrington/Sites/recumbentrambler/wp-content'));

		if ( !defined('WP_LANG_DIR') )
		define('WP_LANG_DIR',  wp_normalize_path('/Users/paulmarrington/Sites/recumbentrambler/wp-content/languages'));

		if(!defined('WP_PLUGIN_DIR'))
		define('WP_PLUGIN_DIR', '/Users/paulmarrington/Sites/recumbentrambler/wp-content/plugins/local-sync/');

			  