<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse;

use Illuminate\Database\Eloquent\Model;

class ClickhouseModel extends Model
{
    public $timestamps   = false;
    public $incrementing = false;
}
