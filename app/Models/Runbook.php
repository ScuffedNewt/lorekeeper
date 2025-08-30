<?php

namespace App\Models;

class Runbook extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'type', 'text', 'data', 'is_public',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'runbooks';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'title'   => 'required|unique:runbooks|between:3,25|alpha_dash',
        'text'    => 'nullable',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'title'   => 'required|between:3,25|alpha_dash',
        'text'    => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the children associated with the runbook.
     */
    public function children() {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active (Open or Pending) update requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->is_staff) {
            return $query;
        }

        return $query->where('is_public', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the URL of the design update request.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('runbook/'.str_replace(' ', '-', strtolower($this->title)));
    }
}
