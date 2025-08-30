@php
    $runbooks = \App\Models\Runbook::where('type', 'Design Update')->get();
@endphp

<div class="card runbook-slide-card" id="runbookCard" style="width: 40vw; position: fixed; top: 20px; right: 20px; z-index: 1050;">
    <button id="toggleBtn" type="button" class="runbook-toggle-btn">
        &raquo;&raquo;
    </button>
    <div class="card-body" style="overflow-y: scroll; max-height: 95vh;">
        <h5 class="card-title">
            Design Update Runbooks
        </h5>
        <div class="card-text">
            <div id="runbookSearchWrapper" class="input-group input-group-sm">
                <input id="runbookSearch" type="text" class="form-control" placeholder="Search runbooks…">
                <div class="input-group-append">
                    <button id="runbookClear" class="btn btn-outline-secondary" type="button" title="Clear">&times;</button>
                </div>
            </div>
            @foreach ($runbooks as $runbook)
                <div class="card {{ $loop->last ? '' : 'mb-3' }}">
                    <div class="card-header" href="#runbook-{{ $runbook->id }}" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="runbook-{{ $runbook->id }}">
                        {{ $runbook->title }}
                    </div>
                    <div class="collapse" id="runbook-{{ $runbook->id }}">
                        <div class="card-body mb-0">
                            {!! parseRunbooks($runbook->text) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    const card = $('#runbookCard');
    const btn = $('#toggleBtn');
    let isRunbookHidden = localStorage.getItem('isRunbookHidden') === 'true';
    if (isRunbookHidden) {
        $('#runbookCard').addClass('hidden');
        btn.html('&laquo;&laquo;');
    }

    btn.on('click', () => {
        card.toggleClass('hidden');
        isRunbookHidden = card.hasClass('hidden');
        localStorage.setItem('isRunbookHidden', isRunbookHidden);
        btn.html(card.hasClass('hidden') ? '&laquo;&laquo;' : '&raquo;&raquo;');
    });

    (function() {
        const $container = $('#runbookCard .card-text');
        const $cards = () => $container.children('.card');
        const $input = $('#runbookSearch');
        const $clearBtn = $('#runbookClear');

        const debounce = (fn, ms = 150) => {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), ms);
            };
        };

        function primeOriginalHtml($elList) {
            $elList.each(function() {
                const $el = $(this);
                if (!$el.data('orig-html')) {
                    $el.data('orig-html', this.innerHTML);
                }
            });
        }

        function clearHighlights($scope) {
            $scope.find('mark.runbook-highlight').each(function() {
                const parent = this.parentNode;
                parent.replaceChild(document.createTextNode(this.textContent), this);
                parent.normalize();
            });

            $scope.each((_, el) => el.normalize());
        }

        function escapeRegExp(str) {
            return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function highlightWithinElement(el, term) {
            if (!term) {
                return;
            }

            const pattern = escapeRegExp(term);
            const reTest = new RegExp(escapeRegExp(term), 'i');
            const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, {
                acceptNode(node) {
                    if (!node.nodeValue || !node.nodeValue.trim()) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    if (['SCRIPT', 'STYLE'].includes(node.parentNode.nodeName)) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return NodeFilter.FILTER_ACCEPT;
                }
            });
            const textNodes = [];
            while (walker.nextNode()) {
                textNodes.push(walker.currentNode);
            }

            textNodes.forEach(node => {
                const original = node.nodeValue;
                if (!reTest.test(original)) {
                    return;
                }

                const reGlobal = new RegExp(pattern, 'gi');
                const frag = document.createDocumentFragment();
                let lastIndex = 0;
                original.replace(reGlobal, (match, idx) => {
                    if (idx > lastIndex) {
                        frag.appendChild(document.createTextNode(original.slice(lastIndex, idx)));
                    }
                    const mark = document.createElement('mark');
                    mark.className = 'runbook-highlight';
                    mark.textContent = match;
                    frag.appendChild(mark);
                    lastIndex = idx + match.length;

                    return match;
                });
                if (lastIndex < original.length) {
                    frag.appendChild(document.createTextNode(original.slice(lastIndex)));
                }
                node.parentNode.replaceChild(frag, node);
            });
        }

        function expandCollapse($collapse) {
            if (!$collapse.length) {
                return;
            }
            if (!$collapse.hasClass('show')) {
                $collapse.collapse('show');
            }
        }

        function expandMatchingCollapses($scope, q) {
            if (!q) {
                return
            };
            const ql = q.toLowerCase();

            $scope.find('.collapse').each(function() {
                const $col = $(this);
                const id = $col.attr('id');

                const innerText = $col.text().toLowerCase();
                const $header = $scope.find(
                    `[data-toggle="collapse"][aria-controls="${id}"],` +
                    `[data-target="#${id}"],` +
                    `a[href="#${id}"]`
                ).first();

                const headerText = ($header.text() || '').toLowerCase();

                if (innerText.includes(ql) || headerText.includes(ql)) {
                    $col.collapse('show');
                    $col.parents('.collapse').each(function() {
                        $(this).collapse('show');
                    });
                }
            });
        }

        function runSearch() {
            const q = $input.val().trim();
            const hasQuery = q.length > 0;

            clearHighlights($container);

            if (!hasQuery) {
                $cards().show();
                return;
            }

            $cards().each(function() {
                const $subCard = $(this);
                const $header = $subCard.children('.card-header');
                const $collapse = $subCard.find('.collapse').first();
                const $bodyScope = $subCard.find('.card-body').first();

                const headerText = $header.text();
                const bodyText = $bodyScope.text();

                const matchesHeader = headerText.toLowerCase().includes(q.toLowerCase());
                const matchesBody = bodyText.toLowerCase().includes(q.toLowerCase());
                const matches = matchesHeader || matchesBody;

                if (matches) {
                    $subCard.show();

                    if (matchesHeader) {
                        highlightWithinElement($header.get(0), q);
                    }
                    if (matchesBody) {
                        highlightWithinElement($bodyScope.get(0), q);
                    }

                    expandCollapse($collapse);
                    expandMatchingCollapses($subCard, q);
                } else {
                    $subCard.hide();
                }
            });
        }

        const debouncedSearch = debounce(runSearch, 120);
        primeOriginalHtml($('#runbookCard .card-header, #runbookCard .card-body'));
        $input.on('input', debouncedSearch);
        $clearBtn.on('click', function() {
            $input.val('');
            clearHighlights($container);
            $cards().show();
            $input.trigger('focus');
        });
        $input.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $clearBtn.click();
            }
        });
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'f') {
                e.preventDefault();
                $input.trigger('focus').select();
            }
        });
    })();
</script>
