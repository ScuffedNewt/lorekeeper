<script>
    var $gridButton = $('.grid-view-button');
    var $gridView = $('#gridView');
    var $navButton = $('.nav-view-button');
    var $navView = $('#navView');

    var view = null;
    initView();

    $gridButton.on('click', function(e) {
        e.preventDefault();
        setView('grid');
    });
    $navButton.on('click', function(e) {
        e.preventDefault();
        setView('nav');
    });

    function initView() {
        view = window.localStorage.getItem('lorekeeper_garden_view');
        if (!view) view = 'nav';
        setView(view);
    }

    function setView(status) {
        view = status;

        if (view == 'grid') {
            $gridView.removeClass('hide');
            $gridButton.addClass('active');
            $navView.addClass('hide');
            $navButton.removeClass('active');
            window.localStorage.setItem('lorekeeper_garden_view', 'grid');
        } else if (view == 'nav') {
            $navView.removeClass('hide');
            $navButton.addClass('active');
            $gridView.addClass('hide');
            $gridButton.removeClass('active');
            window.localStorage.setItem('lorekeeper_garden_view', 'nav');
        }
    }
</script>