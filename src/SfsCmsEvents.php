<?php

namespace Softspring\CmsBundle;

class SfsCmsEvents
{
    /**
     * @Event("Softspring\Component\Events\GetResponseRequestEvent")
     */
    public const ADMIN_ROUTES_LIST_INITIALIZE = 'sfs_cms.admin.routes.list_initialize';

    /**
     * @Event("Softspring\Component\Events\ViewEvent")
     */
    public const ADMIN_ROUTES_LIST_VIEW = 'sfs_cms.admin.routes.list_view';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseEntityEvent")
     */
    public const ADMIN_ROUTES_CREATE_INITIALIZE = 'sfs_cms.admin.routes.create_initialize';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseFormEvent")
     */
    public const ADMIN_ROUTES_CREATE_FORM_VALID = 'sfs_cms.admin.routes.create_form_valid';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseEntityEvent")
     */
    public const ADMIN_ROUTES_CREATE_SUCCESS = 'sfs_cms.admin.routes.create_success';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseFormEvent")
     */
    public const ADMIN_ROUTES_CREATE_FORM_INVALID = 'sfs_cms.admin.routes.create_form_invalid';

    /**
     * @Event("Softspring\Component\Events\ViewEvent")
     */
    public const ADMIN_ROUTES_CREATE_VIEW = 'sfs_cms.admin.routes.create_view';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseEntityEvent")
     */
    public const ADMIN_ROUTES_READ_INITIALIZE = 'sfs_cms.admin.routes.read_initialize';

    /**
     * @Event("Softspring\Component\Events\ViewEvent")
     */
    public const ADMIN_ROUTES_READ_VIEW = 'sfs_cms.admin.routes.read_view';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseEntityEvent")
     */
    public const ADMIN_ROUTES_UPDATE_INITIALIZE = 'sfs_cms.admin.routes.update_initialize';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseFormEvent")
     */
    public const ADMIN_ROUTES_UPDATE_FORM_VALID = 'sfs_cms.admin.routes.update_form_valid';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseEntityEvent")
     */
    public const ADMIN_ROUTES_UPDATE_SUCCESS = 'sfs_cms.admin.routes.update_success';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseFormEvent")
     */
    public const ADMIN_ROUTES_UPDATE_FORM_INVALID = 'sfs_cms.admin.routes.update_form_invalid';

    /**
     * @Event("Softspring\Component\Events\ViewEvent")
     */
    public const ADMIN_ROUTES_UPDATE_VIEW = 'sfs_cms.admin.routes.update_view';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseEntityEvent")
     */
    public const ADMIN_ROUTES_DELETE_INITIALIZE = 'sfs_cms.admin.routes.delete_initialize';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseFormEvent")
     */
    public const ADMIN_ROUTES_DELETE_FORM_VALID = 'sfs_cms.admin.routes.delete_form_valid';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseEntityEvent")
     */
    public const ADMIN_ROUTES_DELETE_SUCCESS = 'sfs_cms.admin.routes.delete_success';

    /**
     * @Event("Softspring\Component\CrudlController\Event\GetResponseFormEvent")
     */
    public const ADMIN_ROUTES_DELETE_FORM_INVALID = 'sfs_cms.admin.routes.delete_form_invalid';

    /**
     * @Event("Softspring\Component\Events\ViewEvent")
     */
    public const ADMIN_ROUTES_DELETE_VIEW = 'sfs_cms.admin.routes.delete_view';
}
