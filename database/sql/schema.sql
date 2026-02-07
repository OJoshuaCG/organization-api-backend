CREATE TABLE IF NOT EXISTS `cat_user_roles` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `whmcs_login` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `modules_endpoints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int(10) unsigned DEFAULT NULL,
  `required_permission_id` int(10) unsigned DEFAULT NULL,
  `prefix` varchar(50) DEFAULT NULL,
  `route` varchar(100) NOT NULL,
  `http_method` enum('GET','POST','PUT','DELETE','PATCH') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `description` varchar(150) DEFAULT NULL,
  `tenancy_mode_group_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `route` (`route`,`http_method`),
  KEY `fk_module_endpoint_moduleid` (`module_id`),
  KEY `fk_module_endpoint_permissionid` (`required_permission_id`),
  KEY `fk_tenancy_mode_group_id` (`tenancy_mode_group_id`),
  CONSTRAINT `fk_module_endpoint_moduleid` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_module_endpoint_permissionid` FOREIGN KEY (`required_permission_id`) REFERENCES `cat_permission_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_tenancy_mode_group_id` FOREIGN KEY (`tenancy_mode_group_id`) REFERENCES `tenancy_mode_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=364 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Si un endpoint pertenece a un módulo, el usuario necesitará tener acceso a este. Si su valor es null no solicitará que el usuario tenga acceso al módulo. \r\nSi el tenancy_mode_group_id es NULL, se utilizará la DB por defecto para resolver las operaciones en el endpoint';

CREATE TABLE IF NOT EXISTS `modules_endpoints_required_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_endpoint_id` int(10) unsigned NOT NULL,
  `user_role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_endpoint_id` (`module_endpoint_id`,`user_role_id`),
  KEY `fk_module_endpoints_roles_user_roleid` (`user_role_id`),
  CONSTRAINT `fk_module_endpoints_roles_mend_id` FOREIGN KEY (`module_endpoint_id`) REFERENCES `modules_endpoints` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_module_endpoints_roles_user_roleid` FOREIGN KEY (`user_role_id`) REFERENCES `cat_user_roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `organization` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `whmcs_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `company` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) unsigned NOT NULL,
  `whmcs_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_company_organization_id` (`organization_id`),
  CONSTRAINT `fk_company_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `company_user_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tag_per_company` (`company_id`,`name`),
  KEY `idx_company_id` (`company_id`),
  CONSTRAINT `fk_tag_company_id` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `company_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `module_id` int(10) unsigned NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_id` (`company_id`,`module_id`),
  KEY `fk_company_modules_moduleid` (`module_id`),
  CONSTRAINT `fk_company_modules_moduleid` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_company_modules_companyid` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Modulos a nivel organizacion que va adquiriendo. Modulos tipo PARENT';

CREATE TABLE IF NOT EXISTS `user_tags` (
  `user_id` int(10) unsigned NOT NULL,
  `company_user_tag_id` int(10) unsigned NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`company_user_tag_id`),
  KEY `idx_company_user_tag_id` (`company_user_tag_id`),
  CONSTRAINT `fk_user_tag_tag` FOREIGN KEY (`company_user_tag_id`) REFERENCES `company_user_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_tag_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int(10) unsigned DEFAULT NULL,
  `user_role_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(60) DEFAULT NULL,
  `last_name` varchar(60) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `whmcs_id` int(11) DEFAULT NULL,
  `phone_extension` int(11) DEFAULT NULL,
  `is_2fa_enabled` tinyint(1) DEFAULT 0,
  `topt_secret` varchar(32) DEFAULT NULL,
  `user_tag_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_organization_id` (`organization_id`),
  KEY `fk_users_user_roleid` (`user_role_id`),
  CONSTRAINT `fk_users_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_user_roleid` FOREIGN KEY (`user_role_id`) REFERENCES `cat_user_roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `users_modules_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `module_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_user_module_permission_userid` (`user_id`),
  KEY `fk_user_module_permission_permissionid` (`permission_id`),
  KEY `fk_user_module_permission_moduleid` (`module_id`),
  CONSTRAINT `fk_user_module_permission_moduleid` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_module_permission_permissionid` FOREIGN KEY (`permission_id`) REFERENCES `cat_permission_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_module_permission_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `users_company_access` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_id` (`company_id`,`user_id`),
  KEY `fk_user_company_access_userid` (`user_id`),
  CONSTRAINT `fk_user_company_access_companyid` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_company_access_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='A que empresas tiene acceso el usuario';


CREATE TABLE IF NOT EXISTS `cat_permission_types` (
  `id` int(10) unsigned NOT NULL,
  `permission` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `db_dedicated_connections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tenancy_mode_group_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `db_host` varchar(512) NOT NULL,
  `db_name` varchar(512) NOT NULL,
  `db_user` varchar(512) NOT NULL,
  `db_pass` varchar(512) NOT NULL,
  `db_port` smallint(10) NOT NULL DEFAULT 3306,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `dek_encrypted` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tenancy_mode_group_dedicated_connection` (`tenancy_mode_group_id`),
  KEY `fk_company_id_dedicated_connection` (`company_id`),
  CONSTRAINT `fk_company_id_dedicated_connection` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_tenancy_mode_group_dedicated_connection` FOREIGN KEY (`tenancy_mode_group_id`) REFERENCES `tenancy_mode_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='multi-tenancy, dependiendo del modulo';

CREATE TABLE IF NOT EXISTS `db_shared_connections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tenancy_mode_group_id` int(10) unsigned DEFAULT NULL,
  `env` enum('development','production') NOT NULL,
  `db_host` varchar(250) NOT NULL,
  `db_name` varchar(250) NOT NULL,
  `db_user` varchar(250) NOT NULL,
  `db_pass` varchar(250) NOT NULL,
  `db_port` smallint(6) NOT NULL DEFAULT 3306,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_tenancy_group_sharedconnect` (`tenancy_mode_group_id`),
  CONSTRAINT `fk_tenancy_group_sharedconnect` FOREIGN KEY (`tenancy_mode_group_id`) REFERENCES `tenancy_mode_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='modulos que utilizan la misma DB sin tenencia';

CREATE TABLE IF NOT EXISTS `session_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token_name` varchar(80) DEFAULT NULL,
  `token` varchar(512) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `created_by_ip` varchar(20) DEFAULT NULL,
  `type` enum('session','api','2fa') DEFAULT NULL,
  `locked` tinyint(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_session_tokens_userid` (`user_id`),
  CONSTRAINT `fk_session_tokens_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3803 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tenancy_mode_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `tenancy_mode` enum('shared','dedicated') NOT NULL DEFAULT 'shared',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='indica como se resuelve una conexión, posteriormente en cada module endpoint se asigna al grupo de conexión al que pertenece para intentar resolver la conexión';
