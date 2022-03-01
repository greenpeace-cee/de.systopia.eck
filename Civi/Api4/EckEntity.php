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

namespace Civi\Api4;

use CRM_Eck_ExtensionUtil as E;
use Civi\Api4\Generic\BasicReplaceAction;
use Civi\Api4\Generic\CheckAccessAction;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Api4\Action\GetActions;

/**
 * Virtual ECK Entity.
 *
 * Provides an API entity for every EckEntityType.
 * Provided by the Entity Construction Kit extension.
 *
 * @package Civi\Api4
 */
class EckEntity {

  /**
   * @param string $entity_type
   * @param bool $checkPermissions
   * @return DAOGetFieldsAction
   */
  public static function getFields(string $entity_type, $checkPermissions = TRUE) {
    return (new DAOGetFieldsAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param string $entity_type
   * @param bool $checkPermissions
   * @return DAOGetAction
   * @throws \API_Exception
   */
  public static function get(string $entity_type, $checkPermissions = TRUE) {
    return (new DAOGetAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param string $entity_type
   * @param bool $checkPermissions
   * @return EckDAOSaveAction
   * @throws \API_Exception
   */
  public static function save(string $entity_type, $checkPermissions = TRUE) {
    return (new EckDAOSaveAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param string $entity_type
   * @param bool $checkPermissions
   * @return EckDAOCreateAction
   * @throws \API_Exception
   */
  public static function create(string $entity_type, $checkPermissions = TRUE) {
    return (new EckDAOCreateAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param string $entity_type
   * @param bool $checkPermissions
   * @return EckDAOUpdateAction
   * @throws \API_Exception
   */
  public static function update(string $entity_type, $checkPermissions = TRUE) {
    return (new EckDAOUpdateAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param string $entity_type
   * @param bool $checkPermissions
   * @return EckDAODeleteAction
   * @throws \API_Exception
   */
  public static function delete(string $entity_type, $checkPermissions = TRUE) {
    return (new EckDAODeleteAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param string $entity_type
   * @param bool $checkPermissions
   * @return BasicReplaceAction
   * @throws \API_Exception
   */
  public static function replace(string $entity_type, $checkPermissions = TRUE) {
    return (new BasicReplaceAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param string $entity_type
   * @param bool $checkPermissions
   * @return GetActions
   */
  public static function getActions(string $entity_type, $checkPermissions = TRUE) {
    return (new GetActions('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param string $entity_type
   * @return CheckAccessAction
   * @throws \API_Exception
   */
  public static function checkAccess(string $entity_type) {
    return new CheckAccessAction('Eck' . $entity_type, __FUNCTION__);
  }

  /**
   * @return array
   */
  public static function permissions() {
    return []; // FIXME: Add per-entity-type permissions
  }

}
