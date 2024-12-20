You can ignore this section if you use an image for the wheel, although text will be layered on top of it!

<div id="segmentStyleAcc" class="p-3">
    <div class="card">
        <div class="card-header ">

            <a class="btn btn-link collapse-toggle collapsed mr-5" type="button" data-toggle="collapse" data-target="#segmentStyleCollapse" aria-expanded="true" aria-controls="segmentStyleCollapse">
                <h5 class="mb-0">Colors & Text</h5>
            </a>
        </div>
        <div id="segmentStyleCollapse" class="collapse" data-parent="#segmentStyleAcc">
            <div class="card-body">

                <table class="table table-sm" id="segmentTable">
                    <thead>
                        <tr>
                            <th width="5%"> Segment </th>
                            <th width="45%">Text</th>
                            <th width="45%">Color</th>
                        </tr>
                    </thead>
                    <tbody id="segmentTableBody">
                        @if ($totalSegments)
                            @for ($i = 0; $i < $totalSegments; $i++)
                                <tr class="segment-row">
                                    <td>{!! Form::number('segment_style[number][]', $i + 1, ['class' => 'form-control bg-dark text-light', 'readonly' => 'true']) !!}</td>
                                    <td>{!! Form::text('segment_style[text][]', $segments[$i]['text'] ?? '', ['class' => 'form-control bg-dark text-light']) !!}</td>
                                    <td>
                                        <div class="input-group cp">
                                            {!! Form::text('segment_style[color][]', $segments[$i]['fillStyle'] ?? null, ['class' => 'form-control']) !!}
                                            <span class="input-group-append">
                                                <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
