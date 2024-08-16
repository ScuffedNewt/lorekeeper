<?php

namespace App\Models;

use Config;
use App\Models\Model;

use App\Traits\Commentable;

class Referral extends Model {
  use Commentable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'referral_count', 'data', 'is_active', 'days_active', 'on_every'
  ];

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'referrals';

  public function getDataAttribute($data) {
    $rewards = [];
    if ($data) {
      $assets = parseAssetData(json_decode($data));
      foreach ($assets as $type => $a) {
        $class = getAssetModelString($type, false);
        foreach ($a as $id => $asset) {
          $rewards[] = (object)[
            'rewardable_type' => $class,
            'rewardable_id' => $id,
            'quantity' => $asset['quantity']
          ];
        }
      }
    }
    return $rewards;
  }

  public function getParsedDataAttribute() {
    return json_decode($this->attributes['data']);
  }
}
