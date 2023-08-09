<?php namespace App\Http\Controllers\Admin;

use Auth;
use DB;
use Exception;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Raffle\RaffleGroup;
use App\Models\Raffle\Raffle;
use App\Models\Raffle\RaffleTicket;

use App\Models\User\User;
use App\Services\RaffleService;
use App\Services\RaffleManager;

use App\Http\Controllers\Controller;

class PairingController extends Controller
{
    /**
     * Shows the pairing settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPairingSettings(Request $request)
    {
        $raffles = Raffle::query();
        if ($request->get('is_active')) $raffles->where('is_active', $request->get('is_active'));
        else $raffles->where('is_active', '!=', 2);
        $raffles = $raffles->orderBy('group_id')->orderBy('order');

        return view('admin.raffle.index', [
            'raffles' => $raffles->get(),
            'groups' => RaffleGroup::whereIn('id', $raffles->pluck('group_id')->toArray())->get()->keyBy('id')
        ]);
    }

    /**
     * Creates or edits pairing settings.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  App\Services\RaffleService  $service
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postPairingSettings(Request $request, RaffleService $service, $id = null)
    {
        $data = $request->only(['name', 'is_active', 'winner_count', 'group_id', 'order']);
        $raffle = null;
        if (!$id) $raffle = $service->createRaffle($data);
        else if ($id) $raffle = $service->updateRaffle($data, Raffle::find($id));
        if ($raffle) {
            flash('Raffle ' . ($id ? 'updated' : 'created') . ' successfully!')->success();
            return redirect()->back();
        }
        else {
            flash('Couldn\'t create raffle.')->error();
            return redirect()->back()->withInput();  
        }
    }

}
