<?php

namespace App\Models;

use Config;
use Settings;

use App\Models\Character\Character;

use App\Models\Model;

class TransferRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id', 'recipient_id', 'reason',
        'status', 'staff_id', 'items', 'staff_comments'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transfer_requests';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who initiated the trade.
     */
    public function user() 
    {
        return $this->belongsTo('App\Models\User\User', 'sender_id');
    }

    /**
     * Get the user who received the trade.
     */
    public function recipient() 
    {
        return $this->belongsTo('App\Models\User\User', 'recipient_id');
    }

    /**
     * Get the staff member who approved the character transfer.
     */
    public function staff() 
    {
        return $this->belongsTo('App\Models\User\User', 'staff_id');
    }

    /**********************************************************************************************
    
        SCOPES

    **********************************************************************************************/

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed')->orWhere('status', 'Rejected');
    }

    /**
     * Scope a query to sort transfers oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to sort transfers by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    /**********************************************************************************************
    
        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the admin URL (for processing purposes) of the transfer
     *
     * @return string
     */
    public function getAdminUrlAttribute()
    {
        return url('admin/transfer-requests/edit/'.$this->id);
    }

    /**
     * Get the viewing URL of the transfer
     *
     * @return string
     */
    public function getViewUrlAttribute()
    {
        return url('transfer-requests/view/'.$this->id);
    }

    /**********************************************************************************************
    
        OTHER FUNCTIONS

    **********************************************************************************************/
    
}
