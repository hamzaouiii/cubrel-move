<?php 
namespace App\Contracts;

interface ModuleHandler
{
    /**
     * Return data required by the Admin/Modules/List Inertia page.
     *
     * @param array $params optional params (e.g. request filters, pagination)
     * @return array props to pass to Inertia
     */
    public function getListData(array $params = []): array;
}
