<?php

namespace App\Models\User;

use App\Models\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserIp extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip', 'user_id', 'is_known_proxy', 'is_mobile_network',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_ips';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Determine whether an IP should be blocked from registration.
     * Mobile networks are shared addresses and always bypass IP bans.
     *
     * @param string $ip
     */
    public static function isBannedForRegistration($ip) {
        if (self::where('ip', $ip)->where('is_mobile_network', 1)->exists()) {
            return false;
        }

        return self::where('ip', $ip)->where('is_user_banned', 1)->exists();
    }

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user this set of settings belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/

    /**
     * Gets ALL users that have used this IP.
     */
    public function getUsersAttribute() {
        return User::whereIn('id', self::where('ip', $this->ip)->pluck('user_id'));
    }

    /**
     * Gets whether or not this IP is a known proxy/VPN
     * as an icon with a tooltip.
     *
     * @return string
     */
    public function getIsProxyAttribute() {
        $html = '<i class=';
        $isProxy = $this->is_known_proxy;
        if (!isset($isProxy)) {
            $isProxy = Cache::get('proxy_check_'.$this->ip);
        }
        if (!isset($isProxy) && config('lorekeeper.user-ips.trustip.enabled')) {
            if (!class_exists('Trustip') || !config('lorekeeper.user-ips.trustip.api_key')) {
                return null;
            }

            try {
                $result = \Trustip::check($this->ip);
                $proxyValue = data_get($result, 'data.is_proxy');
                if (!isset($proxyValue)) {
                    return null;
                }

                $isProxy = (bool) $proxyValue;
                Cache::forever('proxy_check_'.$this->ip, $isProxy);
                // Backfill unclassified rows only so staff overrides stand; DB::table keeps updated_at ("last used") untouched.
                DB::table('user_ips')->where('ip', $this->ip)->whereNull('is_known_proxy')->update(['is_known_proxy' => $isProxy ? 1 : 0]);
            } catch (\Throwable $e) {
                report($e);

                return null;
            }
        }
        if (!isset($isProxy)) {
            return null;
        }

        if ($isProxy) {
            $html .= '"fas fa-globe text-warning" data-toggle="tooltip" title="This IP address is a known VPN or proxy."';
        } else {
            $html .= '"fas fa-globe text-success" data-toggle="tooltip" title="This IP address is not known to be VPN or proxy."';
        }

        return $html.'></i>';
    }

    /**
     * Gets whether or not this IP is a mobile network as an icon with a tooltip.
     *
     * @return string
     */
    public function getIsMobileAttribute() {
        if (!$this->is_mobile_network) {
            return null;
        }

        return '<i class="fa-solid fa-tower-cell text-info" data-toggle="tooltip" title="This IP address is a mobile network (shared carrier IP) and cannot be banned."></i>';
    }

    /**
     * Gets whether or not this IP is banned as an icon with a tooltip.
     *
     * @return string
     */
    public function getIsBannedAttribute() {
        if (!$this->is_user_banned) {
            return null;
        }

        return '<i class="fa-solid fa-ban text-danger" data-toggle="tooltip" title="This IP address is banned."></i>';
    }

    /**
     * Returns all users in an imploded, formatted string.
     *
     * @param mixed|null $user
     *
     * @return string
     */
    public function usersString($user = null) {
        $users = $this->users;
        if ($user && $user->id) {
            $users->where('id', '!=', $user->id);
        }
        $users = $users->get();

        $userList = [];
        foreach ($users as $user) {
            $userList[] = $user->displayName;
        }
        if (!count($userList)) {
            return '<span class="font-italic text-muted">Not Shared</span>';
        }

        return implode(', ', $userList);
    }
}
