<?php

namespace Redking\Bundle\CoreRestBundle\Event;

/**
 * Events triggered by the bundle
 */
final class Events
{
    /**
     * Triggered before a form is bind with an object
     */
    const PRE_BIND = 'redking_core_rest.pre_bind';

    /**
     * Triggered after a form is bind with an object
     */
    const POST_BIND = 'redking_core_rest.post_bind';

    /**
     * Triggered before an object is persisted
     */
    const PRE_PERSIST = 'redking_core_rest.pre_persist';

    /**
     * Triggered after an object is persisted
     */
    const POST_PERSIST = 'redking_core_rest.post_persist';

    /**
     * Triggered when a list of objects is retrieved from database
     */
    const GET_OBJECTS = 'redking_core_rest.get_objects';

    /**
     * Triggered when an object is retrieved from database
     */
    const GET_OBJECT = 'redking_core_rest.get_object';

    /**
     * Triggered before an object is updated from database
     */
    const PRE_UPDATE_OBJECT = 'redking_core_rest.pre_update_object';

    /**
     * Triggered after an object is updated from database
     */
    const UPDATE_OBJECT = 'redking_core_rest.update_object';

    /**
     * Triggered before an object is deleted from database
     */
    const PRE_DELETE_OBJECT = 'redking_core_rest.pre_delete_object';

    /**
     * Triggered after an object is deleted from database
     */
    const DELETE_OBJECT = 'redking_core_rest.delete_object';
}
