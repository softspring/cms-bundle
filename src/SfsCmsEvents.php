<?php

namespace Softspring\CmsBundle;

class SfsCmsEvents
{
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
