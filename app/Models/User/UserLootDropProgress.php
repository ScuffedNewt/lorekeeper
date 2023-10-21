<?php

namespace App\Models\User;

use App\Models\Model;

class UserLootDropProgress extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'loot_table_id', 'rolls',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_loot_drop_progress';

    /**
     * The primary key of the model.
     *
     * @var string
     */
    public $primaryKey = 'user_id';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user this set of progress belongs to.
     */
    public function user() {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the loot table this progress belongs to.
     */
    public function lootTable() {
        return $this->belongsTo('App\Models\Loot\LootTable');
    }
}
