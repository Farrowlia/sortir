<?php

namespace App\Services;

use App\Entity\Campus;

class SearchSortie
{

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var string
     */
    public $q = '';

    /**
     * @var Campus
     */
    public $campus;

//    /**
//     * @var null|integer
//     */
//    public $max;
//
//    /**
//     * @var null|integer
//     */
//    public $min;

    /**
     * @var boolean
     */
    public $archive = false;

}
