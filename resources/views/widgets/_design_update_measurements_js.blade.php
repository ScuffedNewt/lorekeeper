<script>
    $(document).ready(function() {
        var $img = $('#request-image');
        var $svg = $('#measure-svg');
        var svg = $svg[0];

        var state = {
            mode: 'idle',
            active: null,
            drag: null,
            handle: null,
            startPt: null,
            selected: null
        };
        var viewW = 0,
            viewH = 0;

        function svgPoint(evt) {
            var pt = svg.createSVGPoint();
            pt.x = evt.clientX;
            pt.y = evt.clientY;
            var m = svg.getScreenCTM().inverse();
            return pt.matrixTransform(m);
        }

        function createMeasure(x1, y1, x2, y2) {
            var g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            g.setAttribute('class', 'measure');

            var line = document.createElementNS(svg.namespaceURI, 'line');
            line.setAttribute('class', 'measure-line');
            line.setAttribute('data-role', 'line');

            var h1 = document.createElementNS(svg.namespaceURI, 'circle');
            h1.setAttribute('class', 'measure-handle');
            h1.setAttribute('data-role', 'handle1');

            var h2 = document.createElementNS(svg.namespaceURI, 'circle');
            h2.setAttribute('class', 'measure-handle');
            h2.setAttribute('data-role', 'handle2');

            var label = document.createElementNS(svg.namespaceURI, 'text');
            label.setAttribute('class', 'measure-label');
            label.setAttribute('data-role', 'label');

            g.append(line, h1, h2, label);
            svg.appendChild(g);
            setLine(g, x1, y1, x2, y2);
            return g;
        }

        function getLine(g) {
            var $line = $(g).find('.measure-line');
            return {
                x1: parseFloat($line.attr('x1')),
                y1: parseFloat($line.attr('y1')),
                x2: parseFloat($line.attr('x2')),
                y2: parseFloat($line.attr('y2')),
            };
        }

        function setLine(g, x1, y1, x2, y2) {
            var $g = $(g);
            $g.find('.measure-line').attr({
                x1: x1,
                y1: y1,
                x2: x2,
                y2: y2
            });
            $g.find('[data-role="handle1"]').attr({
                cx: x1,
                cy: y1
            });
            $g.find('[data-role="handle2"]').attr({
                cx: x2,
                cy: y2
            });
            positionLabel(g);
        }

        function dist(x1, y1, x2, y2) {
            var dx = x2 - x1,
                dy = y2 - y1;
            return Math.hypot(dx, dy);
        }

        function positionLabel(g) {
            var L = getLine(g);
            var midx = (L.x1 + L.x2) / 2,
                midy = (L.y1 + L.y2) / 2;
            $(g).find('[data-role="label"]').attr({
                x: midx + 6,
                y: midy - 6
            });
        }

        function setSelected(g) {
            if (state.selected && state.selected !== g) {
                $(state.selected).removeClass('selected');
            }
            state.selected = g;
            if (g) {
                $(g).addClass('selected');
                svg.appendChild(g);
            }
        }

        function setViewBox() {
            var iw = $img[0].naturalWidth,
                ih = $img[0].naturalHeight;
            if (!iw || !ih) {
                return;
            }
            if (iw == viewW && ih == viewH) {
                return;
            }
            viewW = iw;
            viewH = ih;
            $svg.attr('viewBox', '0 0 ' + iw + ' ' + ih);
        }

        if ($img[0].complete) {
            setViewBox();
        } else {
            $img.one('load', setViewBox);
        }

        $svg.on('mousedown', function(e) {
            if (e.button !== 0) {
                return;
            }
            var $t = $(e.target);
            var role = $t.attr('data-role');
            var p = svgPoint(e);

            if (role == 'handle1' || role == 'handle2') {
                var g = $t.closest('.measure')[0];
                setSelected(g);
                state.mode = 'resizing';
                state.active = g;
                state.handle = role;
            } else if (role == 'line') {
                var g2 = $t.closest('.measure')[0];
                setSelected(g2);
                state.mode = 'moving';
                state.active = g2;
                var L = getLine(g2);
                state.drag = {
                    start: p,
                    orig: L
                };
                $svg.css('cursor', 'grabbing');
            } else {
                state.mode = 'drawing';
                state.startPt = p;
                state.active = createMeasure(p.x, p.y, p.x, p.y);
                setSelected(state.active);
            }
            e.preventDefault();
        });

        $(document).on('mousemove', function(e) {
            if (state.mode == 'idle' || !state.active) {
                return;
            }
            var p = svgPoint(e);

            if (state.mode == 'drawing') {
                setLine(state.active, state.startPt.x, state.startPt.y, p.x, p.y);
            } else if (state.mode == 'resizing') {
                var L = getLine(state.active);
                if (state.handle == 'handle1') {
                    setLine(state.active, p.x, p.y, L.x2, L.y2);
                } else {
                    setLine(state.active, L.x1, L.y1, p.x, p.y);
                }
            } else if (state.mode == 'moving') {
                var dx = p.x - state.drag.start.x;
                var dy = p.y - state.drag.start.y;
                setLine(state.active,
                    state.drag.orig.x1 + dx, state.drag.orig.y1 + dy,
                    state.drag.orig.x2 + dx, state.drag.orig.y2 + dy
                );
            }
        });

        $(document).on('mouseup', function() {
            if (state.mode !== 'idle') {
                state.mode = 'idle';
                state.active = null;
                state.drag = null;
                state.handle = null;
                $svg.css('cursor', 'crosshair');
            }
        });

        $(document).on('keydown', function(e) {
            if ((e.key == 'Delete' || e.key == 'Backspace') && state.selected) {
                $(state.selected).remove();
                state.selected = null;
                e.preventDefault();
            } else if (e.key == 'Escape') {
                $svg.find('.measure').remove();
                state.mode = 'idle';
                state.active = null;
                state.selected = null;
                state.drag = null;
                state.handle = null;
                $svg.css('cursor', 'crosshair');
                e.preventDefault();
            }
        });
    });
</script>
