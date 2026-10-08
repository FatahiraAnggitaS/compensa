<?php

use App\Support\DecimalFormatter;

it('formats IDR without converting fixed-precision strings to float', function () {
    expect(DecimalFormatter::idr('9999999999999999.99'))
        ->toBe('Rp 9.999.999.999.999.999,99')
        ->and(DecimalFormatter::decimal('2.5'))->toBe('2,50');
});
