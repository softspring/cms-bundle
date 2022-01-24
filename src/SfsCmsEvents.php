<?php

namespace Softspring\CmsBundle;

/**
 * Class SfsCmsEvents
 */
class SfsCmsEvents
{
    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_SITES_LIST_VIEW = 'sfs_cms.admin.sites.list_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_SITES_CREATE_INITIALIZE = 'sfs_cms.admin.sites.create_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_SITES_CREATE_FORM_VALID = 'sfs_cms.admin.sites.create_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_SITES_CREATE_SUCCESS = 'sfs_cms.admin.sites.create_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_SITES_CREATE_FORM_INVALID = 'sfs_cms.admin.sites.create_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_SITES_CREATE_VIEW = 'sfs_cms.admin.sites.create_view';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_SITES_READ_VIEW = 'sfs_cms.admin.sites.read_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_SITES_UPDATE_INITIALIZE = 'sfs_cms.admin.sites.update_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_SITES_UPDATE_FORM_VALID = 'sfs_cms.admin.sites.update_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_SITES_UPDATE_SUCCESS = 'sfs_cms.admin.sites.update_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_SITES_UPDATE_FORM_INVALID = 'sfs_cms.admin.sites.update_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_SITES_UPDATE_VIEW = 'sfs_cms.admin.sites.update_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_SITES_DELETE_INITIALIZE = 'sfs_cms.admin.sites.delete_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_SITES_DELETE_FORM_VALID = 'sfs_cms.admin.sites.delete_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_SITES_DELETE_SUCCESS = 'sfs_cms.admin.sites.delete_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_SITES_DELETE_FORM_INVALID = 'sfs_cms.admin.sites.delete_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_SITES_DELETE_VIEW = 'sfs_cms.admin.blocks.delete_view';

    /**
     * @Event("Softspring\CoreBundle\Event\GetResponseRequestEvent")
     */
    const ADMIN_BLOCKS_LIST_INITIALIZE = 'sfs_cms.admin.blocks.list_initialize';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_BLOCKS_LIST_VIEW = 'sfs_cms.admin.blocks.list_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_BLOCKS_CREATE_INITIALIZE = 'sfs_cms.admin.blocks.create_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_BLOCKS_CREATE_FORM_VALID = 'sfs_cms.admin.blocks.create_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_BLOCKS_CREATE_SUCCESS = 'sfs_cms.admin.blocks.create_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_BLOCKS_CREATE_FORM_INVALID = 'sfs_cms.admin.blocks.create_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_BLOCKS_CREATE_VIEW = 'sfs_cms.admin.blocks.create_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_BLOCKS_READ_INITIALIZE = 'sfs_cms.admin.blocks.read_initialize';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_BLOCKS_READ_VIEW = 'sfs_cms.admin.blocks.read_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_BLOCKS_UPDATE_INITIALIZE = 'sfs_cms.admin.blocks.update_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_BLOCKS_UPDATE_FORM_VALID = 'sfs_cms.admin.blocks.update_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_BLOCKS_UPDATE_SUCCESS = 'sfs_cms.admin.blocks.update_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_BLOCKS_UPDATE_FORM_INVALID = 'sfs_cms.admin.blocks.update_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_BLOCKS_UPDATE_VIEW = 'sfs_cms.admin.blocks.update_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_BLOCKS_DELETE_INITIALIZE = 'sfs_cms.admin.blocks.delete_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_BLOCKS_DELETE_FORM_VALID = 'sfs_cms.admin.blocks.delete_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_BLOCKS_DELETE_SUCCESS = 'sfs_cms.admin.blocks.delete_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_BLOCKS_DELETE_FORM_INVALID = 'sfs_cms.admin.blocks.delete_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_BLOCKS_DELETE_VIEW = 'sfs_cms.admin.blocks.delete_view';

    /**
     * @Event("Softspring\CoreBundle\Event\GetResponseRequestEvent")
     */
    const ADMIN_LAYOUTS_LIST_INITIALIZE = 'sfs_cms.admin.layouts.list_initialize';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_LAYOUTS_LIST_VIEW = 'sfs_cms.admin.layouts.list_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_LAYOUTS_CREATE_INITIALIZE = 'sfs_cms.admin.layouts.create_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_LAYOUTS_CREATE_FORM_VALID = 'sfs_cms.admin.layouts.create_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_LAYOUTS_CREATE_SUCCESS = 'sfs_cms.admin.layouts.create_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_LAYOUTS_CREATE_FORM_INVALID = 'sfs_cms.admin.layouts.create_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_LAYOUTS_CREATE_VIEW = 'sfs_cms.admin.layouts.create_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_LAYOUTS_READ_INITIALIZE = 'sfs_cms.admin.layouts.read_initialize';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_LAYOUTS_READ_VIEW = 'sfs_cms.admin.layouts.read_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_LAYOUTS_UPDATE_INITIALIZE = 'sfs_cms.admin.layouts.update_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_LAYOUTS_UPDATE_FORM_VALID = 'sfs_cms.admin.layouts.update_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_LAYOUTS_UPDATE_SUCCESS = 'sfs_cms.admin.layouts.update_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_LAYOUTS_UPDATE_FORM_INVALID = 'sfs_cms.admin.layouts.update_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_LAYOUTS_UPDATE_VIEW = 'sfs_cms.admin.layouts.update_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_LAYOUTS_DELETE_INITIALIZE = 'sfs_cms.admin.layouts.delete_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_LAYOUTS_DELETE_FORM_VALID = 'sfs_cms.admin.layouts.delete_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_LAYOUTS_DELETE_SUCCESS = 'sfs_cms.admin.layouts.delete_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_LAYOUTS_DELETE_FORM_INVALID = 'sfs_cms.admin.layouts.delete_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_LAYOUTS_DELETE_VIEW = 'sfs_cms.admin.layouts.delete_view';

    /**
     * @Event("Softspring\CoreBundle\Event\GetResponseRequestEvent")
     */
    const ADMIN_ROUTES_LIST_INITIALIZE = 'sfs_cms.admin.routes.list_initialize';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_ROUTES_LIST_VIEW = 'sfs_cms.admin.routes.list_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_ROUTES_CREATE_INITIALIZE = 'sfs_cms.admin.routes.create_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_ROUTES_CREATE_FORM_VALID = 'sfs_cms.admin.routes.create_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_ROUTES_CREATE_SUCCESS = 'sfs_cms.admin.routes.create_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_ROUTES_CREATE_FORM_INVALID = 'sfs_cms.admin.routes.create_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_ROUTES_CREATE_VIEW = 'sfs_cms.admin.routes.create_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_ROUTES_READ_INITIALIZE = 'sfs_cms.admin.routes.read_initialize';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_ROUTES_READ_VIEW = 'sfs_cms.admin.routes.read_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_ROUTES_UPDATE_INITIALIZE = 'sfs_cms.admin.routes.update_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_ROUTES_UPDATE_FORM_VALID = 'sfs_cms.admin.routes.update_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_ROUTES_UPDATE_SUCCESS = 'sfs_cms.admin.routes.update_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_ROUTES_UPDATE_FORM_INVALID = 'sfs_cms.admin.routes.update_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_ROUTES_UPDATE_VIEW = 'sfs_cms.admin.routes.update_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_ROUTES_DELETE_INITIALIZE = 'sfs_cms.admin.routes.delete_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_ROUTES_DELETE_FORM_VALID = 'sfs_cms.admin.routes.delete_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_ROUTES_DELETE_SUCCESS = 'sfs_cms.admin.routes.delete_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_ROUTES_DELETE_FORM_INVALID = 'sfs_cms.admin.routes.delete_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_ROUTES_DELETE_VIEW = 'sfs_cms.admin.routes.delete_view';

    /**
     * @Event("Softspring\CoreBundle\Event\GetResponseRequestEvent")
     */
    const ADMIN_PAGES_LIST_INITIALIZE = 'sfs_cms.admin.pages.list_initialize';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_PAGES_LIST_VIEW = 'sfs_cms.admin.pages.list_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_PAGES_CREATE_INITIALIZE = 'sfs_cms.admin.pages.create_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_PAGES_CREATE_FORM_VALID = 'sfs_cms.admin.pages.create_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_PAGES_CREATE_SUCCESS = 'sfs_cms.admin.pages.create_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_PAGES_CREATE_FORM_INVALID = 'sfs_cms.admin.pages.create_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_PAGES_CREATE_VIEW = 'sfs_cms.admin.pages.create_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_PAGES_READ_INITIALIZE = 'sfs_cms.admin.pages.read_initialize';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_PAGES_READ_VIEW = 'sfs_cms.admin.pages.read_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_PAGES_UPDATE_INITIALIZE = 'sfs_cms.admin.pages.update_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_PAGES_UPDATE_FORM_VALID = 'sfs_cms.admin.pages.update_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_PAGES_UPDATE_SUCCESS = 'sfs_cms.admin.pages.update_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_PAGES_UPDATE_FORM_INVALID = 'sfs_cms.admin.pages.update_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_PAGES_UPDATE_VIEW = 'sfs_cms.admin.pages.update_view';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_PAGES_DELETE_INITIALIZE = 'sfs_cms.admin.pages.delete_initialize';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_PAGES_DELETE_FORM_VALID = 'sfs_cms.admin.pages.delete_form_valid';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseEntityEvent")
     */
    const ADMIN_PAGES_DELETE_SUCCESS = 'sfs_cms.admin.pages.delete_success';

    /**
     * @Event("Softspring\CrudlBundle\Event\GetResponseFormEvent")
     */
    const ADMIN_PAGES_DELETE_FORM_INVALID = 'sfs_cms.admin.pages.delete_form_invalid';

    /**
     * @Event("Softspring\CoreBundle\Event\ViewEvent")
     */
    const ADMIN_PAGES_DELETE_VIEW = 'sfs_cms.admin.pages.delete_view';
}