// Dashboard Service - Handles dashboard operations
const DashboardService = {
    // Initialize dashboard view
    async init() {
        if (!AuthService.isAuthenticated()) {
            window.location.hash = '#/login';
            return;
        }

        this.loadDashboard();
    },

    // Load dashboard data
    async loadDashboard() {
        const user = AuthService.getUser();
        const isAdmin = AuthService.isAdmin();

        // Update welcome message
        if (user) {
            const welcomeTitle = document.getElementById('welcomeTitle');
            const welcomeSubtitle = document.getElementById('welcomeSubtitle');
            
            if (welcomeTitle) {
                welcomeTitle.textContent = `Welcome back, ${this.escapeHtml(user.name)}!`;
            }
            if (welcomeSubtitle) {
                welcomeSubtitle.textContent = isAdmin 
                    ? 'Manage the library system and all resources.' 
                    : 'Browse books and manage your borrowings.';
            }
        }

        // Hide admin-only sections
        document.querySelectorAll('.admin-only').forEach(el => {
            el.style.display = isAdmin ? 'block' : 'none';
        });

        // Load stats
        await this.loadStats(isAdmin);

        // Load user's active borrowings if not admin
        if (!isAdmin && user) {
            await this.loadUserBorrowings(user.id);
        } else {
            const myBorrowingsSection = document.getElementById('myBorrowingsSection');
            if (myBorrowingsSection) {
                myBorrowingsSection.style.display = 'none';
            }
        }
    },

    // Load dashboard statistics
    async loadStats(isAdmin) {
        const statsDiv = document.getElementById('dashboardStats');
        if (!statsDiv) return;

        try {
            const [booksResult, borrowingsResult] = await Promise.all([
                API.getBooks(),
                isAdmin ? API.getBorrowings() : API.getActiveBorrowings()
            ]);

            let statsHTML = `
                <div class="col-md-4">
                    <div class="card card-shadow">
                        <div class="card-body">
                            <h5 class="card-title">Total Books</h5>
                            <h2 class="display-4">${booksResult.success ? (booksResult.count || (Array.isArray(booksResult.data) ? booksResult.data.length : 0)) : 0}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-shadow">
                        <div class="card-body">
                            <h5 class="card-title">${isAdmin ? 'Total Borrowings' : 'My Active Borrowings'}</h5>
                            <h2 class="display-4">${borrowingsResult.success ? (borrowingsResult.count || (Array.isArray(borrowingsResult.data) ? borrowingsResult.data.length : 0)) : 0}</h2>
                        </div>
                    </div>
                </div>
            `;

            if (isAdmin) {
                const usersResult = await API.getUsers();
                statsHTML += `
                    <div class="col-md-4">
                        <div class="card card-shadow">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <h2 class="display-4">${usersResult.success ? (usersResult.count || (Array.isArray(usersResult.data) ? usersResult.data.length : 0)) : 0}</h2>
                            </div>
                        </div>
                    </div>
                `;
            }

            statsDiv.innerHTML = statsHTML;
        } catch (error) {
            console.error('Error loading stats:', error);
            statsDiv.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading statistics</div></div>';
        }
    },

    // Load user's active borrowings
    async loadUserBorrowings(userId) {
        const borrowingsList = document.getElementById('myBorrowingsList');
        if (!borrowingsList) return;

        try {
            const result = await API.getBorrowingsByUser(userId);

            if (result.success && result.data && Array.isArray(result.data)) {
                const activeBorrowings = result.data.filter(b => !b.return_date);

                if (activeBorrowings.length > 0) {
                    borrowingsList.innerHTML = `
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Book ID</th>
                                        <th>Book Title</th>
                                        <th>Borrow Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${activeBorrowings.slice(0, 5).map(b => `
                                        <tr>
                                            <td>${b.book_id || ''}</td>
                                            <td>${this.escapeHtml(b.book_title || 'N/A')}</td>
                                            <td>${b.borrow_date || 'N/A'}</td>
                                            <td><span class="badge bg-warning">Active</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        ${activeBorrowings.length > 5 ? `<p class="text-muted">Showing 5 of ${activeBorrowings.length} active borrowings. <a href="#/borrowings">View all</a></p>` : ''}
                    `;
                } else {
                    borrowingsList.innerHTML = '<p class="text-muted">You have no active borrowings.</p>';
                }
            } else {
                borrowingsList.innerHTML = '<p class="text-muted">You have no active borrowings.</p>';
            }
        } catch (error) {
            console.error('Error loading user borrowings:', error);
            borrowingsList.innerHTML = '<p class="text-danger">Error loading borrowings.</p>';
        }
    },

    // Escape HTML to prevent XSS
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

