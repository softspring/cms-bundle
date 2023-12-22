<?php

namespace Softspring\CmsBundle;

class SfsCmsEvents
{
    // BLOCK LIST EVENTS
    public const ADMIN_BLOCKS_LIST_INITIALIZE = 'sfs_cms.admin.blocks.list_initialize';
    public const ADMIN_BLOCKS_LIST_FILTER = 'sfs_cms.admin.blocks.list_filter';
    public const ADMIN_BLOCKS_LIST_VIEW = 'sfs_cms.admin.blocks.list_view';
    // BLOCK CREATE EVENTS
    public const ADMIN_BLOCKS_CREATE_INITIALIZE = 'sfs_cms.admin.blocks.create_initialize';
    public const ADMIN_BLOCKS_CREATE_ENTITY = 'sfs_cms.admin.blocks.create_create_entity';
    public const ADMIN_BLOCKS_CREATE_FORM_PREPARE = 'sfs_cms.admin.blocks.create_form_prepare';
    public const ADMIN_BLOCKS_CREATE_FORM_INIT = 'sfs_cms.admin.blocks.create_form_init';
    public const ADMIN_BLOCKS_CREATE_FORM_VALID = 'sfs_cms.admin.blocks.create_form_valid';
    public const ADMIN_BLOCKS_CREATE_SUCCESS = 'sfs_cms.admin.blocks.create_success';
    public const ADMIN_BLOCKS_CREATE_FAILURE = 'sfs_cms.admin.blocks.create_failure';
    public const ADMIN_BLOCKS_CREATE_FORM_INVALID = 'sfs_cms.admin.blocks.create_form_invalid';
    public const ADMIN_BLOCKS_CREATE_VIEW = 'sfs_cms.admin.blocks.create_view';
    // BLOCK UPDATE EVENTS
    public const ADMIN_BLOCKS_UPDATE_INITIALIZE = 'sfs_cms.admin.blocks.update_initialize';
    public const ADMIN_BLOCKS_UPDATE_LOAD_ENTITY = 'sfs_cms.admin.blocks.update_load_entity';
    public const ADMIN_BLOCKS_UPDATE_NOT_FOUND = 'sfs_cms.admin.blocks.update_not_found';
    public const ADMIN_BLOCKS_UPDATE_FOUND = 'sfs_cms.admin.blocks.update_found';
    public const ADMIN_BLOCKS_UPDATE_FORM_PREPARE = 'sfs_cms.admin.blocks.update_form_prepare';
    public const ADMIN_BLOCKS_UPDATE_FORM_INIT = 'sfs_cms.admin.blocks.update_form_init';
    public const ADMIN_BLOCKS_UPDATE_FORM_VALID = 'sfs_cms.admin.blocks.update_form_valid';
    public const ADMIN_BLOCKS_UPDATE_SUCCESS = 'sfs_cms.admin.blocks.update_success';
    public const ADMIN_BLOCKS_UPDATE_FAILURE = 'sfs_cms.admin.blocks.update_failure';
    public const ADMIN_BLOCKS_UPDATE_FORM_INVALID = 'sfs_cms.admin.blocks.update_form_invalid';
    public const ADMIN_BLOCKS_UPDATE_VIEW = 'sfs_cms.admin.blocks.update_view';

    // MENU LIST EVENTS
    public const ADMIN_MENUS_LIST_INITIALIZE = 'sfs_cms.admin.menus.list_initialize';
    public const ADMIN_MENUS_LIST_FILTER = 'sfs_cms.admin.menus.list_filter';
    public const ADMIN_MENUS_LIST_VIEW = 'sfs_cms.admin.menus.list_view';
    // MENU CREATE EVENTS
    public const ADMIN_MENUS_CREATE_INITIALIZE = 'sfs_cms.admin.menus.create_initialize';
    public const ADMIN_MENUS_CREATE_ENTITY = 'sfs_cms.admin.menus.create_create_entity';
    public const ADMIN_MENUS_CREATE_FORM_PREPARE = 'sfs_cms.admin.menus.create_form_prepare';
    public const ADMIN_MENUS_CREATE_FORM_INIT = 'sfs_cms.admin.menus.create_form_init';
    public const ADMIN_MENUS_CREATE_FORM_VALID = 'sfs_cms.admin.menus.create_form_valid';
    public const ADMIN_MENUS_CREATE_SUCCESS = 'sfs_cms.admin.menus.create_success';
    public const ADMIN_MENUS_CREATE_FAILURE = 'sfs_cms.admin.menus.create_failure';
    public const ADMIN_MENUS_CREATE_FORM_INVALID = 'sfs_cms.admin.menus.create_form_invalid';
    public const ADMIN_MENUS_CREATE_VIEW = 'sfs_cms.admin.menus.create_view';
    // MENU UPDATE EVENTS
    public const ADMIN_MENUS_UPDATE_INITIALIZE = 'sfs_cms.admin.menus.update_initialize';
    public const ADMIN_MENUS_UPDATE_LOAD_ENTITY = 'sfs_cms.admin.menus.update_load_entity';
    public const ADMIN_MENUS_UPDATE_NOT_FOUND = 'sfs_cms.admin.menus.update_not_found';
    public const ADMIN_MENUS_UPDATE_FOUND = 'sfs_cms.admin.menus.update_found';
    public const ADMIN_MENUS_UPDATE_FORM_PREPARE = 'sfs_cms.admin.menus.update_form_prepare';
    public const ADMIN_MENUS_UPDATE_FORM_INIT = 'sfs_cms.admin.menus.update_form_init';
    public const ADMIN_MENUS_UPDATE_FORM_VALID = 'sfs_cms.admin.menus.update_form_valid';
    public const ADMIN_MENUS_UPDATE_SUCCESS = 'sfs_cms.admin.menus.update_success';
    public const ADMIN_MENUS_UPDATE_FAILURE = 'sfs_cms.admin.menus.update_failure';
    public const ADMIN_MENUS_UPDATE_FORM_INVALID = 'sfs_cms.admin.menus.update_form_invalid';
    public const ADMIN_MENUS_UPDATE_VIEW = 'sfs_cms.admin.menus.update_view';

    public const ADMIN_ROUTES_LIST_INITIALIZE = 'sfs_cms.admin.routes.list_initialize';
    public const ADMIN_ROUTES_LIST_VIEW = 'sfs_cms.admin.routes.list_view';
    public const ADMIN_ROUTES_CREATE_INITIALIZE = 'sfs_cms.admin.routes.create_initialize';
    public const ADMIN_ROUTES_CREATE_FORM_VALID = 'sfs_cms.admin.routes.create_form_valid';
    public const ADMIN_ROUTES_CREATE_SUCCESS = 'sfs_cms.admin.routes.create_success';
    public const ADMIN_ROUTES_CREATE_FORM_INVALID = 'sfs_cms.admin.routes.create_form_invalid';
    public const ADMIN_ROUTES_CREATE_VIEW = 'sfs_cms.admin.routes.create_view';
    public const ADMIN_ROUTES_READ_INITIALIZE = 'sfs_cms.admin.routes.read_initialize';
    public const ADMIN_ROUTES_READ_VIEW = 'sfs_cms.admin.routes.read_view';
    public const ADMIN_ROUTES_UPDATE_INITIALIZE = 'sfs_cms.admin.routes.update_initialize';
    public const ADMIN_ROUTES_UPDATE_FORM_VALID = 'sfs_cms.admin.routes.update_form_valid';
    public const ADMIN_ROUTES_UPDATE_SUCCESS = 'sfs_cms.admin.routes.update_success';
    public const ADMIN_ROUTES_UPDATE_FORM_INVALID = 'sfs_cms.admin.routes.update_form_invalid';
    public const ADMIN_ROUTES_UPDATE_VIEW = 'sfs_cms.admin.routes.update_view';
    public const ADMIN_ROUTES_DELETE_INITIALIZE = 'sfs_cms.admin.routes.delete_initialize';
    public const ADMIN_ROUTES_DELETE_FORM_VALID = 'sfs_cms.admin.routes.delete_form_valid';
    public const ADMIN_ROUTES_DELETE_SUCCESS = 'sfs_cms.admin.routes.delete_success';
    public const ADMIN_ROUTES_DELETE_FORM_INVALID = 'sfs_cms.admin.routes.delete_form_invalid';
    public const ADMIN_ROUTES_DELETE_VIEW = 'sfs_cms.admin.routes.delete_view';
}
