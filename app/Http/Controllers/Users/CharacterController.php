<?php

namespace App\Http\Controllers\Users;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Character\CharacterFolder;
use App\Models\Character\CharacterTransfer;
use App\Models\User\User;
use App\Services\CharacterManager;
use App\Services\FolderManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CharacterController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Character Controller
    |--------------------------------------------------------------------------
    |
    | Handles displaying of the user's characters and transfers.
    |
    */

    /**
     * Shows the user's characters.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        $characters = Auth::user()->characters()->with('image')->visible()->whereNull('trade_id')->get();

        return view('home.characters', [
            'characters' => $characters,
            'folders'    => ['None' => 'None'] + Auth::user()->folders()->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the user's MYO slots.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMyos() {
        $slots = Auth::user()->myoSlots()->with('image')->get();

        return view('home.myos', [
            'slots' => $slots,
        ]);
    }

    /**
     * Sorts the user's characters.
     *
     * @param App\Services\CharacterManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortCharacters(Request $request, CharacterManager $service) {
        if ($service->sortCharacters($request->only(['sort', 'folder_ids']), Auth::user())) {
            flash('Characters sorted successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the create folder modal.
     */
    public function getCreateFolder() {
        return view('home._create_edit_folder', [
            'folder' => new CharacterFolder,
        ]);
    }

    /**
     * Gets the edit folder modal.
     *
     * @param mixed $id
     */
    public function getEditFolder($id) {
        $folder = CharacterFolder::find($id);
        if (!$folder) {
            abort(404);
        }

        return view('home._create_edit_folder', [
            'folder' => $folder,
        ]);
    }

    /**
     * Posts create / edit folder.
     *
     * @param mixed|null $id
     */
    public function postCreateEditFolder(Request $request, FolderManager $service, $id = null) {
        if ($id) {
            $folder = CharacterFolder::find($id);
            if (!$folder) {
                abort(404);
            }
            if (!$service->editFolder($request->only(['name', 'description']), Auth::user(), $folder)) {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
            } else {
                flash('Folder edited successfully.')->success();
            }

            return redirect()->back();
        } else {
            if (!$service->createFolder($request->only(['name', 'description']), Auth::user())) {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
            } else {
                flash('Folder created successfully.')->success();
            }

            return redirect()->back();
        }
    }

    /**
     * Deletes a folder.
     *
     * @param mixed $id
     */
    public function postDeleteFolder(FolderManager $service, $id) {
        $folder = CharacterFolder::find($id);
        if (!$folder) {
            abort(404);
        }

        if (!$service->deleteFolder($folder)) {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        } else {
            flash('Folder deleted successfully.')->success();
        }

        return redirect()->back();
    }

    /**
     * Shows the user's transfers.
     *
     * @param string $type
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTransfers($type = 'incoming') {
        $transfers = CharacterTransfer::with('sender.rank')->with('recipient.rank')->with('character.image');
        $user = Auth::user();

        switch ($type) {
            case 'incoming':
                $transfers->where('recipient_id', $user->id)->active();
                break;
            case 'outgoing':
                $transfers->where('sender_id', $user->id)->active();
                break;
            case 'completed':
                $transfers->where(function ($query) use ($user) {
                    $query->where('recipient_id', $user->id)->orWhere('sender_id', $user->id);
                })->completed();
                break;
        }

        return view('home.character_transfers', [
            'transfers'      => $transfers->orderBy('id', 'DESC')->paginate(20),
            'transfersQueue' => Settings::get('open_transfers_queue'),
        ]);
    }

    /**
     * Transfers one of the user's own characters.
     *
     * @param App\Services\CharacterManager $service
     * @param int                           $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postHandleTransfer(Request $request, CharacterManager $service, $id) {
        if (!Auth::check()) {
            abort(404);
        }

        $action = $request->get('action');

        if ($action == 'Cancel' && $service->cancelTransfer(['transfer_id' => $id], Auth::user())) {
            flash('Transfer cancelled.')->success();
        } elseif ($service->processTransfer($request->only(['action']) + ['transfer_id' => $id], Auth::user())) {
            if (strtolower($action) == 'approve') {
                flash('Transfer '.strtolower($action).'d.')->success();
            } else {
                flash('Transfer '.strtolower($action).'ed.')->success();
            }
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
