<?php
/*-------------------------------------------------------+
| CiviCRM Entity Construction Kit                        |
| Copyright (C) 2021 SYSTOPIA                            |
| Author: J. Schuppe (schuppe@systopia.de)               |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Eck_ExtensionUtil as E;

class CRM_Eck_BAO_EckEntityType extends CRM_Eck_DAO_EckEntityType {

  /**
   * @return array[]
   */
  public static function getEntityTypes(): array {
    if (!isset(Civi::$statics['EckEntityTypes'])) {
      Civi::$statics['EckEntityTypes'] = CRM_Core_DAO::executeQuery(
        'SELECT *, CONCAT("Eck", name) AS entity_name FROM `civicrm_eck_entity_type`;'
      )->fetchAll('id');
    }
    return Civi::$statics['EckEntityTypes'];
  }

  /**
   * @return string[]
   */
  public static function getEntityTypeNames(): array {
    return array_column(self::getEntityTypes(), 'name');
  }

  /**
   * Given an ECKEntityType, make sure data structures are set-up correctly:
   * - the corresponding schema table
   * - the entry in the "cg_extend_objects" option group
   *
   * @param array $entity_type
   *
   * @throws \API_Exception
   */
  public static function ensureEntityType($entity_type) {
    $table_name = 'civicrm_eck_' . strtolower($entity_type['name']);

    // Ensure table exists.
    CRM_Core_DAO::executeQuery("
      CREATE TABLE IF NOT EXISTS `{$table_name}` (
          `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Eck{$entity_type['name']} ID',
          `title` text NOT NULL   COMMENT 'The entity title.',
          `subtype` text NOT NULL   COMMENT 'The entity subtype.',
          PRIMARY KEY (`id`)
      )
      ENGINE=InnoDB
      DEFAULT CHARSET=utf8
      COLLATE=utf8_unicode_ci;
    ");

    // Synchronize cg_extend_objects option values.
    \Civi\Api4\OptionValue::save(FALSE)
      ->addRecord([
        'option_group_id:name' => 'cg_extend_objects',
        'label' => $entity_type['label'],
        'value' => 'Eck' . $entity_type['name'],
        /**
         * Call a "virtual" static method on EckEntityType, which is being
         * resolved using a __callStatic() implementation for retrieving a
         * list of subtypes.
         * @see \CRM_Eck_Utils_EckEntityType::__callStatic()
         * @see \CRM_Core_BAO_CustomGroup::getExtendedObjectTypes()
         */
        'description' => "CRM_Eck_Utils_EckEntityType::{$entity_type['name']}.getSubTypes;",
        'name' => 'civicrm_eck_' . strtolower($entity_type['name']),
        'is_reserved' => TRUE,
      ])
      ->setMatch(['option_group_id', 'value'])
      ->execute();
  }

  /**
   * Retrieves custom groups extending this entity type.
   *
   * @param $entity_type_name
   *   The name of the entity type to retrieve custom groups for.
   *
   * @return array
   */
  public static function getCustomGroups($entity_type_name):array {
    return (array) civicrm_api4('CustomGroup', 'get', [
      'checkPermissions' => FALSE,
      'where' => [['extends', '=', 'Eck' . $entity_type_name]],
    ]);
  }

  /**
   * Retrieves a list of sub types for the given entity type.
   *
   * @param string $entity_type_name
   *   The name of the entity type to retrieve a list of sub types for.
   * @param bool $as_mapping
   * @return array
   *   A list of sub types for the given entity type.
   */
  public static function getSubTypes($entity_type_name, $as_mapping = TRUE):array {
    $result = civicrm_api4('OptionValue', 'get', [
      'checkPermissions' => FALSE,
      'where' => [
        ['option_group_id:name', '=', 'eck_sub_types'],
        ['grouping', '=', $entity_type_name],
      ],
    ]);
    return $as_mapping ?
      $result->indexBy('value')->column('label') :
      (array) $result;
  }

  /**
   * Deletes a subtype, which involves:
   * - deleting all entities of this subtype
   * - deleting all custom fields in custom groups attached to this subtype
   * - deleting all custom groups attached to this subtype
   * - deleting the subtype option value from the "eck_sub_types" option group
   *
   * @param $sub_type_value
   *   The value of the subtype in the "eck_sub_types" option group.
   *
   * @throws \Exception
   */
  public static function deleteSubType($sub_type_value) {
    $sub_type = civicrm_api4('OptionValue', 'get', [
      'checkPermissions' => FALSE,
      'where' => [
        ['option_group_id:name', '=', 'eck_sub_types'],
        ['value', '=', $sub_type_value],
      ],
    ]);

    // Delete entities of subtype.
    civicrm_api4($sub_type['grouping'], 'delete', [
      'checkPermissions' => FALSE,
      'where' => [['id', 'IS NOT NULL']],
    ]);

    // TODO: Delete CustomFields in CustomGroup attached to subtype.

    // Delete CustomGroups attached to subtype.
    $custom_groups = array_filter(
      CRM_Eck_BAO_EckEntityType::getCustomGroups($sub_type['grouping']),
      function ($custom_group) use ($sub_type_value) {
        return
          isset($custom_group['extends_entity_column_value'])
          && is_array($custom_group['extends_entity_column_value'])
          && in_array(
            $sub_type_value,
            $custom_group['extends_entity_column_value']
          );
      }
    );
    foreach (CRM_Eck_BAO_EckEntityType::getCustomGroups($sub_type['grouping']) as $custom_group) {
      if (
        isset($custom_group['extends_entity_column_value'])
        && is_array($custom_group['extends_entity_column_value'])
        && in_array(
          $sub_type_value,
          $custom_group['extends_entity_column_value']
        )
      ) {
        civicrm_api4('CustomGroup', 'delete', [
          'checkPermissions' => FALSE,
          'where' => [['id', '=', $custom_group['id']]],
        ]);
      }
    }

    // Delete subtype.
    civicrm_api4('OptionValue', 'delete', [
      'checkPermissions' => FALSE,
      'where' => [['id', '=', $sub_type['id']]],
    ]);
  }

}
