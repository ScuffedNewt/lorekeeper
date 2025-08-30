<script>
    const $img = $('#request-image');
    const $canvas = $('#request-canvas');
    const canvas = $canvas[0];
    const ctx = canvas.getContext('2d');

    function initialDraw() {
        const iw = $img[0].naturalWidth,
            ih = $img[0].naturalHeight;
        if (!iw || !ih) {
            return;
        }
        if (canvas.width !== iw || canvas.height !== ih) {
            canvas.width = iw;
            canvas.height = ih;
        }
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage($img[0], 0, 0, canvas.width, canvas.height);
    }
    if ($img[0].complete) {
        initialDraw();
    }
    $img.on('load', initialDraw);

    $canvas.on('click', function(event) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const x = Math.floor((event.clientX - rect.left) * scaleX);
        const y = Math.floor((event.clientY - rect.top) * scaleY);

        const pixel = ctx.getImageData(x, y, 1, 1).data;
        const color = `rgb(${pixel[0]}, ${pixel[1]}, ${pixel[2]})`;
        const hsv = rgbToHsv(pixel[0], pixel[1], pixel[2]);

        $('#values').html(`
            <div class="row">
                <div class="col-md-6">
                    <span class="w-100 h-100" style="background-color:${color};display:inline-block;border-radius:1em;"></span>
                </div>
                <div class="col-md-6 text-left">
                    <div>
                        <span class="ml-2 my-auto"><strong>Hex:</strong> ${rgbToHex(pixel[0], pixel[1], pixel[2])}</span>
                    </div>
                    <div>
                        <span class="ml-2 my-auto"><strong>RGB:</strong> ${pixel[0]}, ${pixel[1]}, ${pixel[2]}</span>
                    </div>
                    <div>
                        <span class="ml-2 my-auto"><strong>Hue:</strong> ${hsv.hue}°, <strong>Saturation:</strong> ${hsv.saturation}%, <strong>Value:</strong> ${hsv.value}%</span>
                    </div>
                </div>
            </div>
        `);
    });

    function rgbToHex(r, g, b) {
        return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
    }

    function rgbToHsv(r, g, b) {
        r /= 255, g /= 255, b /= 255;

        let max = Math.max(r, g, b),
            min = Math.min(r, g, b);
        let h, s, v = max;

        let d = max - min;
        s = max === 0 ? 0 : d / max;

        if (max === min) {
            h = 0; // achromatic
        } else {
            switch (max) {
                case r:
                    h = (g - b) / d + (g < b ? 6 : 0);
                    break;
                case g:
                    h = (b - r) / d + 2;
                    break;
                case b:
                    h = (r - g) / d + 4;
                    break;
            }
            h /= 6;
        }

        return {
            'hue': Math.round(h * 360),
            'saturation': Math.round(s * 100),
            'value': Math.round(v * 100)
        };
    }
</script>
