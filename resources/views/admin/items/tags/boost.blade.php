<h3>Boost Item</h3>
<p>
    This is where you can specifiy which percentages during the pairing process this item boosts. The percentage is only applied if you chose a setting or rarity!
</p>
<hr />
<div id="settings" style="opacity: {{ isset($tag->getData()['rarity_id']) ? '0.5' : '1' }};">
    <h3>Settings</h3>
    <p>
        Settings option allows you to override the default site settings for ONE specified setting option.
        <br>
        <strong>Example:</strong> If you set the setting to <strong>female inheritance</strong> and the percentage to <strong>75</strong>,
        then the chance of offspring being female will be 75% instead of the default 50%.
        <br>
        <span class="text-danger">This will only apply to the setting you choose!</span>
    </p>
    {!! Form::label('Setting Type') !!}
    <div class="row">
        <div class="col" id="percentage">
            {!! Form::number('setting_chance', $tag->getData()['setting_chance'] ?? 50, ['class' => 'form-control setting-percent', isset($tag->getData()['rarity_id']) ? 'disabled' : '']) !!}
        </div>
        <div class="col">
            {!! Form::select('setting', $settings, $tag->getData()['setting'] ?? null, ['class' => 'form-control mr-2', 'placeholder' => 'Select Setting', 'id' => 'settingSelect', isset($tag->getData()['rarity_id']) ? 'disabled' : '']) !!}
        </div>
    </div>
</div>
<hr />
<div id="rarities" style="opacity: {{ isset($tag->getData()['setting']) ? '0.5' : '1' }};">
    <h3>Rarities</h3>
    <p>This will increase the chance of the chosen rarity being the character's rarity, and traits being chosen of this rarity, by the specified percentage.</p>
    {!! Form::label('Rarity') !!}
    <div class="row mb-3">
        <div class="col">
            {!! Form::number('rarity_chance', $tag->getData()['rarity_chance'] ?? 50, ['class' => 'form-control rarity-percent', isset($tag->getData()['setting']) ? 'disabled' : '']) !!}
        </div>
        <div class="col">
            {!! Form::select('rarity_id', $rarities, $tag->getData()['rarity_id'] ?? null, ['class' => 'form-control mr-2', 'placeholder' => 'Select Rarity', 'id' => 'raritySelect', isset($tag->getData()['setting']) ? 'disabled' : '']) !!}
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#settingSelect').on('change', function() {
            if ($(this).val() == '') {
                $('#rarities').css('opacity', '1');
                $('#raritySelect').prop('disabled', false);
                $('.rarity-percent').prop('disabled', false);
            } else {
                console.log('test');
                $('#rarities').css('opacity', '0.5');
                $('#raritySelect').prop('disabled', true);
                $('.rarity-percent').prop('disabled', true);
            }
            if ($(this).val() == 1) {
                $('#percentage').find('.col').hide();
                $('#percentage').append('<div class="col info alert alert-info mt-2">This setting will remove the need for parents to be of the opposite sex, if set.</div>');
            } else {
                $('#percentage').find('.col').show();
                $('#percentage').find('.info').remove();
            }
        });

        $('#raritySelect').on('change', function() {
            if ($(this).val() == '') {
                $('#settings').css('opacity', '1');
                $('#settingSelect').prop('disabled', false);
                $('.setting-percent').prop('disabled', false);
            } else {
                $('#settings').css('opacity', '0.5');
                $('#settingSelect').prop('disabled', true);
                $('.setting-percent').prop('disabled', true);
            }
        });
    });
</script>
