<div class="card mb-3">
    <div class="card-header h3">Friends Requests</div>
    <div class="table-responsive">
        @if($friends->count())
                @foreach($friends as $request)
                    <tr>
                        <td class="text-center">{!! $request->displayName($user->id) !!} @if($request->recipient_id != $user->id) (Outgoing) @endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="card-body">
            <p class="text-center">No friends requests(yet!).</p>
        @endif
    </div>
</div>