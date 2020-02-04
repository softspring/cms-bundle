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
}