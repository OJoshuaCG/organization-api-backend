-- ============================================================================
-- MIGRACIÓN: Estandarización de nombres de tablas y columnas
-- Fecha: 2026-02-07
-- 
-- Cambios:
-- 1. Renombrar 'organizations_groups' → 'organization'
-- 2. Renombrar 'organizations' → 'company'
-- 3. Renombrar 'organizations_modules' → 'company_modules'
-- 4. Renombrar 'organization_user_tags' → 'company_user_tags'
-- 5. Renombrar 'users_organizations_access' → 'users_company_access'
-- 6. Cambiar prefijos 'id_' por sufijos '_id' en columnas FK
-- 7. Actualizar todas las referencias de constraints
-- ============================================================================

-- ============================================================================
-- PASO 1: Eliminación de Foreign Keys existentes
-- ============================================================================

-- 1.1 Eliminar FK de organizations → organizations_groups
ALTER TABLE `organizations` DROP FOREIGN KEY IF EXISTS `fk_org_orggroup_id`;
ALTER TABLE `organizations` DROP KEY IF EXISTS `fk_org_orggroup_id`;

-- 1.2 Eliminar FK de users → organizations_groups
ALTER TABLE `users` DROP FOREIGN KEY IF EXISTS `fk_users_org_groupid`;
ALTER TABLE `users` DROP KEY IF EXISTS `fk_users_org_groupid`;

-- 1.3 Eliminar FK de organizations_user_tags → organizations
ALTER TABLE `organization_user_tags` DROP FOREIGN KEY IF EXISTS `fk_tag_org_id`;
ALTER TABLE `organization_user_tags` DROP KEY IF EXISTS `idx_organization_id`;
ALTER TABLE `organization_user_tags` DROP KEY IF EXISTS `unique_tag_per_organization`;

-- 1.4 Eliminar FK de organizations_modules → organizations
ALTER TABLE `organizations_modules` DROP FOREIGN KEY IF EXISTS `fk_org_modules_orgid`;
ALTER TABLE `organizations_modules` DROP KEY IF EXISTS `organization_id`;
ALTER TABLE `organizations_modules` DROP KEY IF EXISTS `fk_org_modules_moduleid`;

-- 1.5 Eliminar FK de users_organizations_access → organizations
ALTER TABLE `users_organizations_access` DROP FOREIGN KEY IF EXISTS `fk_user_org_access_orgid`;
ALTER TABLE `users_organizations_access` DROP KEY IF EXISTS `organization_id`;
ALTER TABLE `users_organizations_access` DROP KEY IF EXISTS `fk_user_org_access_userid`;

-- 1.6 Eliminar FK de user_tags → organization_user_tags
ALTER TABLE `user_tags` DROP FOREIGN KEY IF EXISTS `fk_user_tag_tag`;
ALTER TABLE `user_tags` DROP KEY IF EXISTS `idx_org_user_tag_id`;

-- ============================================================================
-- PASO 2: Renombramiento de Tablas
-- ============================================================================

-- 2.1 Renombrar organizations_groups → organization
ALTER TABLE `organizations_groups` RENAME TO `organization`;

-- 2.2 Renombrar organizations → company
ALTER TABLE `organizations` RENAME TO `company`;

-- 2.3 Renombrar organizations_modules → company_modules
ALTER TABLE `organizations_modules` RENAME TO `company_modules`;

-- 2.4 Renombrar organization_user_tags → company_user_tags
ALTER TABLE `organization_user_tags` RENAME TO `company_user_tags`;

-- 2.5 Renombrar users_organizations_access → users_company_access
ALTER TABLE `users_organizations_access` RENAME TO `users_company_access`;

-- ============================================================================
-- PASO 3: Modificación de Columnas y Nombres
-- ============================================================================

-- 3.1 organization (antes organizations_groups)
ALTER TABLE `organization` CHANGE COLUMN `id_whmcs` `whmcs_id` int(11) unsigned DEFAULT NULL;

-- 3.2 company (antes organizations)
ALTER TABLE `company` CHANGE COLUMN `org_group_id` `organization_id` int(10) unsigned NOT NULL;
ALTER TABLE `company` CHANGE COLUMN `idwhmcs` `whmcs_id` int(10) unsigned DEFAULT NULL;
ALTER TABLE `company` ADD KEY `fk_company_organization_id` (`organization_id`);

-- 3.3 users
ALTER TABLE `users` CHANGE COLUMN `org_group_id` `organization_id` int(10) unsigned DEFAULT NULL;
ALTER TABLE `users` CHANGE COLUMN `id_whmcs` `whmcs_id` int(11) DEFAULT NULL;
ALTER TABLE `users` ADD KEY `fk_users_organization_id` (`organization_id`);

-- 3.4 company_user_tags (antes organization_user_tags)
ALTER TABLE `company_user_tags` CHANGE COLUMN `organization_id` `company_id` int(10) unsigned NOT NULL;
ALTER TABLE `company_user_tags` ADD UNIQUE KEY `unique_tag_per_company` (`company_id`, `name`);
ALTER TABLE `company_user_tags` ADD KEY `idx_company_id` (`company_id`);

-- 3.5 company_modules (antes organizations_modules)
ALTER TABLE `company_modules` CHANGE COLUMN `organization_id` `company_id` int(10) unsigned NOT NULL;
ALTER TABLE `company_modules` ADD UNIQUE KEY `company_id` (`company_id`, `module_id`);
ALTER TABLE `company_modules` ADD KEY `fk_company_modules_moduleid` (`module_id`);

-- 3.6 users_company_access (antes users_organizations_access)
ALTER TABLE `users_company_access` CHANGE COLUMN `organization_id` `company_id` int(10) unsigned NOT NULL;
ALTER TABLE `users_company_access` ADD UNIQUE KEY `company_id` (`company_id`, `user_id`);
ALTER TABLE `users_company_access` ADD KEY `fk_user_company_access_userid` (`user_id`);

-- 3.7 user_tags
ALTER TABLE `user_tags` CHANGE COLUMN `org_user_tag_id` `company_user_tag_id` int(10) unsigned NOT NULL;
ALTER TABLE `user_tags` DROP PRIMARY KEY;
ALTER TABLE `user_tags` ADD PRIMARY KEY (`user_id`, `company_user_tag_id`);
ALTER TABLE `user_tags` ADD KEY `idx_company_user_tag_id` (`company_user_tag_id`);

-- ============================================================================
-- PASO 4: Recreación de Foreign Keys
-- ============================================================================

-- 4.1 FK de company → organization
ALTER TABLE `company` 
  ADD CONSTRAINT `fk_company_organization_id` 
  FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`) 
  ON DELETE CASCADE ON UPDATE NO ACTION;

-- 4.2 FK de users → organization
ALTER TABLE `users` 
  ADD CONSTRAINT `fk_users_organization_id` 
  FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`) 
  ON DELETE CASCADE ON UPDATE NO ACTION;

-- 4.3 FK de company_user_tags → company
ALTER TABLE `company_user_tags` 
  ADD CONSTRAINT `fk_tag_company_id` 
  FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) 
  ON DELETE CASCADE;

-- 4.4 FK de company_modules → company
ALTER TABLE `company_modules` 
  ADD CONSTRAINT `fk_company_modules_companyid` 
  FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) 
  ON DELETE CASCADE ON UPDATE NO ACTION;

-- 4.5 FK de company_modules → modules
ALTER TABLE `company_modules` 
  ADD CONSTRAINT `fk_company_modules_moduleid` 
  FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) 
  ON UPDATE NO ACTION;

-- 4.6 FK de users_company_access → company
ALTER TABLE `users_company_access` 
  ADD CONSTRAINT `fk_user_company_access_companyid` 
  FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) 
  ON DELETE CASCADE ON UPDATE NO ACTION;

-- 4.7 FK de users_company_access → users
ALTER TABLE `users_company_access` 
  ADD CONSTRAINT `fk_user_company_access_userid` 
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
  ON DELETE CASCADE ON UPDATE NO ACTION;

-- 4.8 FK de user_tags → company_user_tags
ALTER TABLE `user_tags` 
  ADD CONSTRAINT `fk_user_tag_tag` 
  FOREIGN KEY (`company_user_tag_id`) REFERENCES `company_user_tags` (`id`) 
  ON DELETE CASCADE;

-- ============================================================================
-- FIN DE MIGRACIÓN
-- ============================================================================
