<div class="alert alert-info">
    <p class="mb-0 font-weight-bold h5">Before you submit, make sure you have completed the following:</p>
    <hr class="w-50 ml-0 mb-1" />
    <ul>
        {{-- exclude form_id --}}
        @foreach (config('lorekeeper.submission_checklists.' . $type) as $key => $item)
            <li>
                {{ Form::checkbox('checklist[]', $item, false, ['class' => 'form-check-input submission-checklist-box']) }}
                {{ Form::label('checklist_' . $item, $item, ['class' => 'form-check-label']) }}
            </li>
        @endforeach
    </ul>
    <h5 class="text-danger">❗❗ Double check everything before clicking submit ❗❗</h5>
    <p>If you did not complete all of the above, your submission may be rejected. Staff are not responsible for any issues that arise from incomplete submissions.</p>
</div>

<script>
    // ensure that the user has checked all the boxes before submitting
    $(document).ready(function() {
        $('form').submit(function(e) {
            e.preventDefault();
            let checkboxes = $('.submission-checklist-box');

            if (checkboxes.filter(':checked').length != checkboxes.length) {
                alert('Please ensure you have checked all the boxes before submitting.');
                return false;
            }

            this.submit();
        });
    });
</script>
