// Navigation Service - Handles navigation state and UI updates
const NavigationService = {
    // Initialize navigation service
    init() {
        this.updateNavigation();
        this.setupEventHandlers();
        this.checkAuthOnLoad();
    },

    // Update navigation based on authentication status
    updateNavigation() {
        const token = localStorage.getItem("lms_token");
        const isAuth = token && token !== undefined;
        
        if (isAuth) {
            const user = AuthService.getUser();
            const isAdmin = AuthService.isAdmin();
            
            // Show/hide auth links
            $('#loginNav').hide();
            $('#registerNav').hide();
            $('#profileNav').removeClass('d-none');
            $('#logoutNav').removeClass('d-none');
            
            // Show/hide admin-only links
            $('.admin-only').toggle(isAdmin);
        } else {
            $('#loginNav').show();
            $('#registerNav').show();
            $('#profileNav').addClass('d-none');
            $('#logoutNav').addClass('d-none');
            $('.admin-only').hide();
        }
    },

    // Setup event handlers
    setupEventHandlers() {
        // Logout handler
        $('#logoutBtn').on('click', (e) => {
            e.preventDefault();
            AuthService.logout();
        });
        
        // Update navigation on hash change
        $(window).on('hashchange', () => {
            this.updateNavigation();
        });
    },

    // Check authentication on page load
    checkAuthOnLoad() {
        const protectedRoutes = [
            '#/dashboard', 
            '#/profile', 
            '#/users', 
            '#/books', 
            '#/authors', 
            '#/genres', 
            '#/borrowings'
        ];
        const currentHash = window.location.hash || '#/dashboard';
        const token = localStorage.getItem("lms_token");
        
        if (!token && protectedRoutes.some(route => currentHash.startsWith(route))) {
            window.location.hash = '#/login';
        }
    }
};

