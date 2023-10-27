<?php

return [
    // Number between 0-100. Percentage chance to inherit traits from both parents upon generating offspring. Set 100 for always. Set 0 for never.
    'trait_inheritance' => 100,
    // 0: Any character can be paired. 1: Only male/female characters can be paired.
    'sex_restriction' => 0,
    // 0: Disabled, do not roll sex. 1-100: Chance to generate a male offspring. Must total 100 with the pairing_female_percentage setting.
    'offspring_male_percentage' => 0,
    // 0: Disabled, do not roll sex. 1-100: Chance to generate a male offspring. Must total 100 with the pairing_male_percentage setting.
    'offspring_female_percentage' => 0,
    // 0: Disabled. Number of days to wait between pairing a character.
    'cooldown' => 0,
];
