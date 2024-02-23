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

    // CONTENT LIST EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENTS_LIST_INITIALIZE = 'sfs_cms.admin.contents.list_initialize';
    public const ADMIN_CONTENTS_LIST_FILTER_FORM_PREPARE = 'sfs_cms.admin.contents.filter_form_prepare';
    public const ADMIN_CONTENTS_LIST_FILTER_FORM_INIT = 'sfs_cms.admin.contents.filter_form_init';
    public const ADMIN_CONTENTS_LIST_FILTER = 'sfs_cms.admin.contents.list_filter';
    public const ADMIN_CONTENTS_LIST_VIEW = 'sfs_cms.admin.contents.list_view';
    public const ADMIN_CONTENTS_LIST_EXCEPTION = 'sfs_cms.admin.contents.list_exception';
    // CONTENT CREATE EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENTS_CREATE_INITIALIZE = 'sfs_cms.admin.contents.create_initialize';
    public const ADMIN_CONTENTS_CREATE_ENTITY = 'sfs_cms.admin.contents.create_create_entity';
    public const ADMIN_CONTENTS_CREATE_FORM_PREPARE = 'sfs_cms.admin.contents.create_form_prepare';
    public const ADMIN_CONTENTS_CREATE_FORM_INIT = 'sfs_cms.admin.contents.create_form_init';
    public const ADMIN_CONTENTS_CREATE_FORM_VALID = 'sfs_cms.admin.contents.create_form_valid';
    public const ADMIN_CONTENTS_CREATE_APPLY = 'sfs_cms.admin.contents.create_apply';
    public const ADMIN_CONTENTS_CREATE_SUCCESS = 'sfs_cms.admin.contents.create_success';
    public const ADMIN_CONTENTS_CREATE_FAILURE = 'sfs_cms.admin.contents.create_failure';
    public const ADMIN_CONTENTS_CREATE_FORM_INVALID = 'sfs_cms.admin.contents.create_form_invalid';
    public const ADMIN_CONTENTS_CREATE_VIEW = 'sfs_cms.admin.contents.create_view';
    public const ADMIN_CONTENTS_CREATE_EXCEPTION = 'sfs_cms.admin.contents.create_exception';
    // CONTENT IMPORT EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENTS_IMPORT_INITIALIZE = 'sfs_cms.admin.contents.import_initialize';
    public const ADMIN_CONTENTS_IMPORT_ENTITY = 'sfs_cms.admin.contents.import_import_entity';
    public const ADMIN_CONTENTS_IMPORT_FORM_PREPARE = 'sfs_cms.admin.contents.import_form_prepare';
    public const ADMIN_CONTENTS_IMPORT_FORM_INIT = 'sfs_cms.admin.contents.import_form_init';
    public const ADMIN_CONTENTS_IMPORT_FORM_VALID = 'sfs_cms.admin.contents.import_form_valid';
    public const ADMIN_CONTENTS_IMPORT_APPLY = 'sfs_cms.admin.contents.import_apply';
    public const ADMIN_CONTENTS_IMPORT_SUCCESS = 'sfs_cms.admin.contents.import_success';
    public const ADMIN_CONTENTS_IMPORT_FAILURE = 'sfs_cms.admin.contents.import_failure';
    public const ADMIN_CONTENTS_IMPORT_FORM_INVALID = 'sfs_cms.admin.contents.import_form_invalid';
    public const ADMIN_CONTENTS_IMPORT_VIEW = 'sfs_cms.admin.contents.import_view';
    public const ADMIN_CONTENTS_IMPORT_EXCEPTION = 'sfs_cms.admin.contents.import_exception';
    // CONTENT READ EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENTS_READ_INITIALIZE = 'sfs_cms.admin.contents.read_initialize';
    public const ADMIN_CONTENTS_READ_LOAD_ENTITY = 'sfs_cms.admin.contents.read_load_entity';
    public const ADMIN_CONTENTS_READ_NOT_FOUND = 'sfs_cms.admin.contents.read_not_found';
    public const ADMIN_CONTENTS_READ_FOUND = 'sfs_cms.admin.contents.read_found';
    public const ADMIN_CONTENTS_READ_VIEW = 'sfs_cms.admin.contents.read_view';
    public const ADMIN_CONTENTS_READ_EXCEPTION = 'sfs_cms.admin.contents.read_exception';
    // CONTENT UPDATE EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENTS_UPDATE_INITIALIZE = 'sfs_cms.admin.contents.update_initialize';
    public const ADMIN_CONTENTS_UPDATE_LOAD_ENTITY = 'sfs_cms.admin.contents.update_load_entity';
    public const ADMIN_CONTENTS_UPDATE_NOT_FOUND = 'sfs_cms.admin.contents.update_not_found';
    public const ADMIN_CONTENTS_UPDATE_FOUND = 'sfs_cms.admin.contents.update_found';
    public const ADMIN_CONTENTS_UPDATE_FORM_PREPARE = 'sfs_cms.admin.contents.update_form_prepare';
    public const ADMIN_CONTENTS_UPDATE_FORM_INIT = 'sfs_cms.admin.contents.update_form_init';
    public const ADMIN_CONTENTS_UPDATE_FORM_VALID = 'sfs_cms.admin.contents.update_form_valid';
    public const ADMIN_CONTENTS_UPDATE_APPLY = 'sfs_cms.admin.contents.update_apply';
    public const ADMIN_CONTENTS_UPDATE_SUCCESS = 'sfs_cms.admin.contents.update_success';
    public const ADMIN_CONTENTS_UPDATE_FAILURE = 'sfs_cms.admin.contents.update_failure';
    public const ADMIN_CONTENTS_UPDATE_FORM_INVALID = 'sfs_cms.admin.contents.update_form_invalid';
    public const ADMIN_CONTENTS_UPDATE_VIEW = 'sfs_cms.admin.contents.update_view';
    public const ADMIN_CONTENTS_UPDATE_EXCEPTION = 'sfs_cms.admin.contents.update_exception';
    // CONTENT ROUTES EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENTS_ROUTES_INITIALIZE = 'sfs_cms.admin.contents.routes_initialize';
    public const ADMIN_CONTENTS_ROUTES_LOAD_ENTITY = 'sfs_cms.admin.contents.routes_load_entity';
    public const ADMIN_CONTENTS_ROUTES_NOT_FOUND = 'sfs_cms.admin.contents.routes_not_found';
    public const ADMIN_CONTENTS_ROUTES_FOUND = 'sfs_cms.admin.contents.routes_found';
    public const ADMIN_CONTENTS_ROUTES_FORM_PREPARE = 'sfs_cms.admin.contents.routes_form_prepare';
    public const ADMIN_CONTENTS_ROUTES_FORM_INIT = 'sfs_cms.admin.contents.routes_form_init';
    public const ADMIN_CONTENTS_ROUTES_FORM_VALID = 'sfs_cms.admin.contents.routes_form_valid';
    public const ADMIN_CONTENTS_ROUTES_APPLY = 'sfs_cms.admin.contents.routes_apply';
    public const ADMIN_CONTENTS_ROUTES_SUCCESS = 'sfs_cms.admin.contents.routes_success';
    public const ADMIN_CONTENTS_ROUTES_FAILURE = 'sfs_cms.admin.contents.routes_failure';
    public const ADMIN_CONTENTS_ROUTES_FORM_INVALID = 'sfs_cms.admin.contents.routes_form_invalid';
    public const ADMIN_CONTENTS_ROUTES_VIEW = 'sfs_cms.admin.contents.routes_view';
    public const ADMIN_CONTENTS_ROUTES_EXCEPTION = 'sfs_cms.admin.contents.routes_exception';
    // CONTENT DELETE EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENTS_DELETE_INITIALIZE = 'sfs_cms.admin.contents.delete_initialize';
    public const ADMIN_CONTENTS_DELETE_LOAD_ENTITY = 'sfs_cms.admin.contents.delete_load_entity';
    public const ADMIN_CONTENTS_DELETE_NOT_FOUND = 'sfs_cms.admin.contents.delete_not_found';
    public const ADMIN_CONTENTS_DELETE_FOUND = 'sfs_cms.admin.contents.delete_found';
    public const ADMIN_CONTENTS_DELETE_FORM_PREPARE = 'sfs_cms.admin.contents.delete_form_prepare';
    public const ADMIN_CONTENTS_DELETE_FORM_INIT = 'sfs_cms.admin.contents.delete_form_init';
    public const ADMIN_CONTENTS_DELETE_FORM_VALID = 'sfs_cms.admin.contents.delete_form_valid';
    public const ADMIN_CONTENTS_DELETE_APPLY = 'sfs_cms.admin.contents.delete_apply';
    public const ADMIN_CONTENTS_DELETE_SUCCESS = 'sfs_cms.admin.contents.delete_success';
    public const ADMIN_CONTENTS_DELETE_FAILURE = 'sfs_cms.admin.contents.delete_failure';
    public const ADMIN_CONTENTS_DELETE_FORM_INVALID = 'sfs_cms.admin.contents.delete_form_invalid';
    public const ADMIN_CONTENTS_DELETE_VIEW = 'sfs_cms.admin.contents.delete_view';
    public const ADMIN_CONTENTS_DELETE_EXCEPTION = 'sfs_cms.admin.contents.delete_exception';
    // CONTENT UNPUBLISH EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENTS_UNPUBLISH_INITIALIZE = 'sfs_cms.admin.contents.unpublish_initialize';
    public const ADMIN_CONTENTS_UNPUBLISH_LOAD_ENTITY = 'sfs_cms.admin.contents.unpublish_load_entity';
    public const ADMIN_CONTENTS_UNPUBLISH_NOT_FOUND = 'sfs_cms.admin.contents.unpublish_not_found';
    public const ADMIN_CONTENTS_UNPUBLISH_FOUND = 'sfs_cms.admin.contents.unpublish_found';
    public const ADMIN_CONTENTS_UNPUBLISH_APPLY = 'sfs_cms.admin.contents.unpublish_apply';
    public const ADMIN_CONTENTS_UNPUBLISH_SUCCESS = 'sfs_cms.admin.contents.unpublish_success';
    public const ADMIN_CONTENTS_UNPUBLISH_FAILURE = 'sfs_cms.admin.contents.unpublish_failure';
    public const ADMIN_CONTENTS_UNPUBLISH_EXCEPTION = 'sfs_cms.admin.contents.unpublish_exception';
    // CONTENT PREVIEW EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENTS_PREVIEW_INITIALIZE = 'sfs_cms.admin.contents.preview_initialize';
    public const ADMIN_CONTENTS_PREVIEW_LOAD_ENTITY = 'sfs_cms.admin.contents.preview_load_entity';
    public const ADMIN_CONTENTS_PREVIEW_NOT_FOUND = 'sfs_cms.admin.contents.preview_not_found';
    public const ADMIN_CONTENTS_PREVIEW_FOUND = 'sfs_cms.admin.contents.preview_found';
    public const ADMIN_CONTENTS_PREVIEW_VIEW = 'sfs_cms.admin.contents.preview_view';
    public const ADMIN_CONTENTS_PREVIEW_EXCEPTION = 'sfs_cms.admin.contents.preview_exception';

    // CONTENT_VERSION CREATE EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_CREATE_INITIALIZE = 'sfs_cms.admin.content_versions.create_initialize';
    public const ADMIN_CONTENT_VERSIONS_CREATE_ENTITY = 'sfs_cms.admin.content_versions.create_create_entity';
    public const ADMIN_CONTENT_VERSIONS_CREATE_FORM_PREPARE = 'sfs_cms.admin.content_versions.create_form_prepare';
    public const ADMIN_CONTENT_VERSIONS_CREATE_FORM_INIT = 'sfs_cms.admin.content_versions.create_form_init';
    public const ADMIN_CONTENT_VERSIONS_CREATE_FORM_VALID = 'sfs_cms.admin.content_versions.create_form_valid';
    public const ADMIN_CONTENT_VERSIONS_CREATE_APPLY = 'sfs_cms.admin.content_versions.create_apply';
    public const ADMIN_CONTENT_VERSIONS_CREATE_SUCCESS = 'sfs_cms.admin.content_versions.create_success';
    public const ADMIN_CONTENT_VERSIONS_CREATE_FAILURE = 'sfs_cms.admin.content_versions.create_failure';
    public const ADMIN_CONTENT_VERSIONS_CREATE_FORM_INVALID = 'sfs_cms.admin.content_versions.create_form_invalid';
    public const ADMIN_CONTENT_VERSIONS_CREATE_VIEW = 'sfs_cms.admin.content_versions.create_view';
    public const ADMIN_CONTENT_VERSIONS_CREATE_EXCEPTION = 'sfs_cms.admin.content_versions.create_exception';
    // CONTENT_VERSION IMPORT EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_IMPORT_INITIALIZE = 'sfs_cms.admin.content_versions.import_initialize';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_ENTITY = 'sfs_cms.admin.content_versions.import_import_entity';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FORM_PREPARE = 'sfs_cms.admin.content_versions.import_form_prepare';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FORM_INIT = 'sfs_cms.admin.content_versions.import_form_init';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FORM_VALID = 'sfs_cms.admin.content_versions.import_form_valid';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_APPLY = 'sfs_cms.admin.content_versions.import_apply';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_SUCCESS = 'sfs_cms.admin.content_versions.import_success';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FAILURE = 'sfs_cms.admin.content_versions.import_failure';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FORM_INVALID = 'sfs_cms.admin.content_versions.import_form_invalid';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_VIEW = 'sfs_cms.admin.content_versions.import_view';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_EXCEPTION = 'sfs_cms.admin.content_versions.import_exception';
    // CONTENT_VERSION LIST EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_LIST_INITIALIZE = 'sfs_cms.admin.content_versions.list_initialize';
    public const ADMIN_CONTENT_VERSIONS_LIST_FILTER_FORM_PREPARE = 'sfs_cms.admin.content_versions.list_filter_form_prepare';
    public const ADMIN_CONTENT_VERSIONS_LIST_FILTER_FORM_INIT = 'sfs_cms.admin.content_versions.list_filter_form_init';
    public const ADMIN_CONTENT_VERSIONS_LIST_FILTER = 'sfs_cms.admin.content_versions.list_filter';
    public const ADMIN_CONTENT_VERSIONS_LIST_VIEW = 'sfs_cms.admin.content_versions.list_view';
    public const ADMIN_CONTENT_VERSIONS_LIST_EXCEPTION = 'sfs_cms.admin.content_versions.list_exception';
    // CONTENT_VERSION LOCK EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_LOCK_INITIALIZE = 'sfs_cms.admin.content_versions.lock_initialize';
    public const ADMIN_CONTENT_VERSIONS_LOCK_LOAD_ENTITY = 'sfs_cms.admin.content_versions.lock_load_entity';
    public const ADMIN_CONTENT_VERSIONS_LOCK_NOT_FOUND = 'sfs_cms.admin.content_versions.lock_not_found';
    public const ADMIN_CONTENT_VERSIONS_LOCK_FOUND = 'sfs_cms.admin.content_versions.lock_found';
    public const ADMIN_CONTENT_VERSIONS_LOCK_APPLY = 'sfs_cms.admin.content_versions.lock_apply';
    public const ADMIN_CONTENT_VERSIONS_LOCK_SUCCESS = 'sfs_cms.admin.content_versions.lock_success';
    public const ADMIN_CONTENT_VERSIONS_LOCK_FAILURE = 'sfs_cms.admin.content_versions.lock_failure';
    public const ADMIN_CONTENT_VERSIONS_LOCK_EXCEPTION = 'sfs_cms.admin.content_versions.lock_exception';
    // CONTENT_VERSION RECOMPILE EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_RECOMPILE_INITIALIZE = 'sfs_cms.admin.content_versions.recompile_initialize';
    public const ADMIN_CONTENT_VERSIONS_RECOMPILE_LOAD_ENTITY = 'sfs_cms.admin.content_versions.recompile_load_entity';
    public const ADMIN_CONTENT_VERSIONS_RECOMPILE_NOT_FOUND = 'sfs_cms.admin.content_versions.recompile_not_found';
    public const ADMIN_CONTENT_VERSIONS_RECOMPILE_FOUND = 'sfs_cms.admin.content_versions.recompile_found';
    public const ADMIN_CONTENT_VERSIONS_RECOMPILE_APPLY = 'sfs_cms.admin.content_versions.recompile_apply';
    public const ADMIN_CONTENT_VERSIONS_RECOMPILE_SUCCESS = 'sfs_cms.admin.content_versions.recompile_success';
    public const ADMIN_CONTENT_VERSIONS_RECOMPILE_FAILURE = 'sfs_cms.admin.content_versions.recompile_failure';
    public const ADMIN_CONTENT_VERSIONS_RECOMPILE_EXCEPTION = 'sfs_cms.admin.content_versions.recompile_exception';
    // CONTENT_VERSION PREVIEW EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_PREVIEW_INITIALIZE = 'sfs_cms.admin.content_versions.preview_initialize';
    public const ADMIN_CONTENT_VERSIONS_PREVIEW_LOAD_ENTITY = 'sfs_cms.admin.content_versions.preview_load_entity';
    public const ADMIN_CONTENT_VERSIONS_PREVIEW_NOT_FOUND = 'sfs_cms.admin.content_versions.preview_not_found';
    public const ADMIN_CONTENT_VERSIONS_PREVIEW_FOUND = 'sfs_cms.admin.content_versions.preview_found';
    public const ADMIN_CONTENT_VERSIONS_PREVIEW_EXCEPTION = 'sfs_cms.admin.content_versions.preview_exception';
    // CONTENT_VERSION PUBLISH EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_PUBLISH_INITIALIZE = 'sfs_cms.admin.content_versions.publish_initialize';
    public const ADMIN_CONTENT_VERSIONS_PUBLISH_LOAD_ENTITY = 'sfs_cms.admin.content_versions.publish_load_entity';
    public const ADMIN_CONTENT_VERSIONS_PUBLISH_NOT_FOUND = 'sfs_cms.admin.content_versions.publish_not_found';
    public const ADMIN_CONTENT_VERSIONS_PUBLISH_FOUND = 'sfs_cms.admin.content_versions.publish_found';
    public const ADMIN_CONTENT_VERSIONS_PUBLISH_APPLY = 'sfs_cms.admin.content_versions.publish_apply';
    public const ADMIN_CONTENT_VERSIONS_PUBLISH_SUCCESS = 'sfs_cms.admin.content_versions.publish_success';
    public const ADMIN_CONTENT_VERSIONS_PUBLISH_FAILURE = 'sfs_cms.admin.content_versions.publish_failure';
    public const ADMIN_CONTENT_VERSIONS_PUBLISH_EXCEPTION = 'sfs_cms.admin.content_versions.publish_exception';
    // CONTENT_VERSION EXPORT EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_EXPORT_INITIALIZE = 'sfs_cms.admin.content_versions.export_initialize';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_LOAD_ENTITY = 'sfs_cms.admin.content_versions.export_load_entity';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_NOT_FOUND = 'sfs_cms.admin.content_versions.export_not_found';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_FOUND = 'sfs_cms.admin.content_versions.export_found';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_APPLY = 'sfs_cms.admin.content_versions.export_apply';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_SUCCESS = 'sfs_cms.admin.content_versions.export_success';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_FAILURE = 'sfs_cms.admin.content_versions.export_failure';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_EXCEPTION = 'sfs_cms.admin.content_versions.export_exception';
    // CONTENT VERSION CLEANUP EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_CLEANUP_INITIALIZE = 'sfs_cms.admin.content_versions.cleanup_initialize';
    public const ADMIN_CONTENT_VERSIONS_CLEANUP_LOAD_ENTITY = 'sfs_cms.admin.content_versions.cleanup_load_entity';
    public const ADMIN_CONTENT_VERSIONS_CLEANUP_NOT_FOUND = 'sfs_cms.admin.content_versions.cleanup_not_found';
    public const ADMIN_CONTENT_VERSIONS_CLEANUP_FOUND = 'sfs_cms.admin.content_versions.cleanup_found';
    public const ADMIN_CONTENT_VERSIONS_CLEANUP_APPLY = 'sfs_cms.admin.content_versions.cleanup_apply';
    public const ADMIN_CONTENT_VERSIONS_CLEANUP_SUCCESS = 'sfs_cms.admin.content_versions.cleanup_success';
    public const ADMIN_CONTENT_VERSIONS_CLEANUP_FAILURE = 'sfs_cms.admin.content_versions.cleanup_failure';
    public const ADMIN_CONTENT_VERSIONS_CLEANUP_EXCEPTION = 'sfs_cms.admin.content_versions.cleanup_exception';
    // CONTENT VERSION INFO EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_INFO_INITIALIZE = 'sfs_cms.admin.content_versions.info_initialize';
    public const ADMIN_CONTENT_VERSIONS_INFO_LOAD_ENTITY = 'sfs_cms.admin.content_versions.info_load_entity';
    public const ADMIN_CONTENT_VERSIONS_INFO_NOT_FOUND = 'sfs_cms.admin.content_versions.info_not_found';
    public const ADMIN_CONTENT_VERSIONS_INFO_FOUND = 'sfs_cms.admin.content_versions.info_found';
    public const ADMIN_CONTENT_VERSIONS_INFO_FORM_PREPARE = 'sfs_cms.admin.content_versions.info_form_prepare';
    public const ADMIN_CONTENT_VERSIONS_INFO_FORM_INIT = 'sfs_cms.admin.content_versions.info_form_init';
    public const ADMIN_CONTENT_VERSIONS_INFO_FORM_VALID = 'sfs_cms.admin.content_versions.info_form_valid';
    public const ADMIN_CONTENT_VERSIONS_INFO_APPLY = 'sfs_cms.admin.content_versions.info_apply';
    public const ADMIN_CONTENT_VERSIONS_INFO_SUCCESS = 'sfs_cms.admin.content_versions.info_success';
    public const ADMIN_CONTENT_VERSIONS_INFO_FAILURE = 'sfs_cms.admin.content_versions.info_failure';
    public const ADMIN_CONTENT_VERSIONS_INFO_FORM_INVALID = 'sfs_cms.admin.content_versions.info_form_invalid';
    public const ADMIN_CONTENT_VERSIONS_INFO_VIEW = 'sfs_cms.admin.content_versions.info_view';
    public const ADMIN_CONTENT_VERSIONS_INFO_EXCEPTION = 'sfs_cms.admin.content_versions.info_exception';
    // CONTENT VERSION SEO EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_SEO_INITIALIZE = 'sfs_cms.admin.content_versions.seo_initialize';
    public const ADMIN_CONTENT_VERSIONS_SEO_ENTITY = 'sfs_cms.admin.content_versions.seo_entity';
    public const ADMIN_CONTENT_VERSIONS_SEO_FORM_PREPARE = 'sfs_cms.admin.content_versions.seo_form_prepare';
    public const ADMIN_CONTENT_VERSIONS_SEO_FORM_INIT = 'sfs_cms.admin.content_versions.seo_form_init';
    public const ADMIN_CONTENT_VERSIONS_SEO_FORM_VALID = 'sfs_cms.admin.content_versions.seo_form_valid';
    public const ADMIN_CONTENT_VERSIONS_SEO_APPLY = 'sfs_cms.admin.content_versions.seo_apply';
    public const ADMIN_CONTENT_VERSIONS_SEO_SUCCESS = 'sfs_cms.admin.content_versions.seo_success';
    public const ADMIN_CONTENT_VERSIONS_SEO_FAILURE = 'sfs_cms.admin.content_versions.seo_failure';
    public const ADMIN_CONTENT_VERSIONS_SEO_FORM_INVALID = 'sfs_cms.admin.content_versions.seo_form_invalid';
    public const ADMIN_CONTENT_VERSIONS_SEO_VIEW = 'sfs_cms.admin.content_versions.seo_view';
    public const ADMIN_CONTENT_VERSIONS_SEO_EXCEPTION = 'sfs_cms.admin.content_versions.seo_exception';
    // CONTENT VERSION DELETE EVENTS, ALL OF THEM ARE INTERNAL
    public const ADMIN_CONTENT_VERSIONS_DELETE_INITIALIZE = 'sfs_cms.admin.content_versions.delete_initialize';
    public const ADMIN_CONTENT_VERSIONS_DELETE_LOAD_ENTITY = 'sfs_cms.admin.content_versions.delete_load_entity';
    public const ADMIN_CONTENT_VERSIONS_DELETE_NOT_FOUND = 'sfs_cms.admin.content_versions.delete_not_found';
    public const ADMIN_CONTENT_VERSIONS_DELETE_FOUND = 'sfs_cms.admin.content_versions.delete_found';
    public const ADMIN_CONTENT_VERSIONS_DELETE_FORM_PREPARE = 'sfs_cms.admin.content_versions.delete_form_prepare';
    public const ADMIN_CONTENT_VERSIONS_DELETE_FORM_INIT = 'sfs_cms.admin.content_versions.delete_form_init';
    public const ADMIN_CONTENT_VERSIONS_DELETE_FORM_VALID = 'sfs_cms.admin.content_versions.delete_form_valid';
    public const ADMIN_CONTENT_VERSIONS_DELETE_APPLY = 'sfs_cms.admin.content_versions.delete_apply';
    public const ADMIN_CONTENT_VERSIONS_DELETE_SUCCESS = 'sfs_cms.admin.content_versions.delete_success';
    public const ADMIN_CONTENT_VERSIONS_DELETE_FAILURE = 'sfs_cms.admin.content_versions.delete_failure';
    public const ADMIN_CONTENT_VERSIONS_DELETE_FORM_INVALID = 'sfs_cms.admin.content_versions.delete_form_invalid';
    public const ADMIN_CONTENT_VERSIONS_DELETE_VIEW = 'sfs_cms.admin.content_versions.delete_view';
    public const ADMIN_CONTENT_VERSIONS_DELETE_EXCEPTION = 'sfs_cms.admin.content_versions.delete_exception';
}
