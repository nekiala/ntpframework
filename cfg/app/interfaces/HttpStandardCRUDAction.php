<?php
/**
 * Created by PhpStorm.
 * User: KIALA
 * Date: 12/03/2016
 * Time: 12:21
 */

namespace cfg\app\interfaces;


interface HttpStandardCRUDAction
{
    function createAction();
    function listAction();
    function listsAction();
    function editAction();
    function deleteAction();

    /**
     * show details
     * @param null $id
     * @return mixed
     */
    function showAction($id = null);
}