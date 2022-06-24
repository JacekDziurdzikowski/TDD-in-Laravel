<?php declare(strict_types=1);

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Person extends Model
{
    public function friends(): Relation
    {
        return $this->belongsToMany(self::class);
    }
}
