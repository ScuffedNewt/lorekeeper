<hr class="my-4 w-75" />
<h4>{{ ucfirst($type) }} Rewards</h4>

<table class="table table-sm">
    <thead>
        <tr>
            <th width="70%">Reward</th>
            <th width="30%">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($object->objectRewards as $reward)
            <tr>
                <td>{!! $reward->reward->displayName !!}</td>
                <td>{{ $reward->quantity }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<hr class="my-4 w-75" />