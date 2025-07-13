<?php

namespace Modules\Perspektivakrym\Entities;

use Illuminate\Database\Eloquent\Model;

class OldDoc extends Model
{
    protected $table = 'perspektivakrym_old_doc';
    protected $fillable = ['doc', 'deal'];
}
