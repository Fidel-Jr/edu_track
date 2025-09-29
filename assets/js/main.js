$(document).ready(function () {
    let sidebarUserCollapsed = false; // track user preference for desktop

    // Toggle sidebar on button click
    $('#sidebar-toggle').click(function () {
        if ($(window).width() < 768) {
            // For mobile: show/hide overlay menu
            $('body').removeClass('sidebar-collapsed');
            $('body').toggleClass('sidebar-active');
        } else {
            // For tablet/desktop: toggle collapsed
            $('body').toggleClass('sidebar-collapsed');
            sidebarUserCollapsed = $('body').hasClass('sidebar-collapsed'); // save preference
        }
    });

    // Close sidebar when clicking overlay
    $('#overlay').click(function () {
        $('body').removeClass('sidebar-active');
    });

    // Update sidebar state on window resize
    $(window).resize(function () {
        if ($(window).width() >= 992) {
            // Desktop and larger
            $('body').removeClass('sidebar-active icons-only');

            // restore user’s last preference
            if (sidebarUserCollapsed) {
                $('body').addClass('sidebar-collapsed');
            } else {
                $('body').removeClass('sidebar-collapsed');
            }
        } else if ($(window).width() >= 768) {
            // Tablet
            $('body').removeClass('sidebar-active icons-only').addClass('sidebar-collapsed');
        } else {
            // Mobile
            $('body').removeClass('sidebar-active icons-only sidebar-collapsed');
        }
    });

    // Trigger resize on load to set initial state
    $(window).trigger('resize');

    // Add active class to clicked nav items
    $('.nav-link').click(function () {
        $('.nav-link').removeClass('active');
        $(this).addClass('active');

        // On mobile, close sidebar after clicking a link
        if ($(window).width() < 768) {
            $('body').removeClass('sidebar-active');
        }
    });
});

// Basic filter functionality (for demonstration)
        document.getElementById('applyFilters').addEventListener('click', function() {
            alert('Filters applied! (This is a demo. In a real application, this would filter the table data.)');
        });
        
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('dayFilter').value = '';
            document.getElementById('monthFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('studentFilter').value = '';
            alert('Filters reset!');
        });