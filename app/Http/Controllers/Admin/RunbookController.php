<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Runbook;
use App\Services\RunbookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RunbookController extends Controller {
    /**
     * Shows the runbook index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.runbooks.runbooks', [
            'runbooks' => Runbook::orderBy('title')->paginate(20),
        ]);
    }

    /**
     * Shows the create runbook runbook.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateRunbook() {
        return view('admin.runbooks.create_edit_runbook', [
            'runbook' => new Runbook,
        ]);
    }

    /**
     * Shows the edit runbook runbook.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditRunbook($id) {
        $runbook = Runbook::find($id);
        if (!$runbook) {
            abort(404);
        }

        return view('admin.runbooks.create_edit_runbook', [
            'runbook' => $runbook,
        ]);
    }

    /**
     * Creates or edits a runbook.
     *
     * @param App\Services\RunbookService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditRunbook(Request $request, RunbookService $service, $id = null) {
        $id ? $request->validate(Runbook::$updateRules) : $request->validate(Runbook::$createRules);
        $data = $request->only([
            'title', 'type', 'text', 'data', 'is_public',
        ]);
        if ($id && $service->updateRunbook(Runbook::find($id), $data, Auth::user())) {
            flash('Runbook updated successfully.')->success();
        } elseif (!$id && $runbook = $service->createRunbook($data, Auth::user())) {
            flash('Runbook created successfully.')->success();

            return redirect()->to('admin/runbooks/edit/'.$runbook->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the runbook deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteRunbook($id) {
        $runbook = Runbook::find($id);

        return view('admin.runbooks._delete_runbook', [
            'runbook' => $runbook,
        ]);
    }

    /**
     * Deletes a runbook.
     *
     * @param App\Services\RunbookService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteRunbook(Request $request, RunbookService $service, $id) {
        if ($id && $service->deleteRunbook(Runbook::find($id))) {
            flash('Runbook deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/runbooks');
    }

    /**
     * Gets the runbook regeneration modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRegenRunbook($id) {
        $runbook = Runbook::find($id);

        return view('admin.runbooks._regen_runbook', [
            'runbook' => $runbook,
        ]);
    }

    /**
     * Regenerates a runbook.
     *
     * @param App\Services\RunbookService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRegenRunbook(Request $request, RunbookService $service, $id) {
        if ($id && $service->regenRunbook(Runbook::find($id))) {
            flash('Runbook regenerated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/runbooks/edit/'.$id);
    }
}
