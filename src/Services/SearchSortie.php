<?php

namespace App\Services;

use App\Entity\Campus;
use DateTime;

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

    /**
     * @var null|DateTime
     */
    public $dateMin;

    /**
     * @var null|DateTime
     */
    public $dateMax;

    /**
     * @var boolean
     */
    public $archive = false;

}
