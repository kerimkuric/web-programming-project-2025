// Borrowing Service - Handles all borrowing-related operations
const BorrowingService = {
    // Get all borrowings
    async getAll() {
        try {
            const result = await API.getBorrowings();
            return result;
        } catch (error) {
            console.error('Error fetching borrowings:', error);
            return { success: false, message: 'Failed to fetch borrowings' };
        }
    },

    // Get borrowings by user
    async getByUser(userId) {
        try {
            const result = await API.getBorrowingsByUser(userId);
            return result;
        } catch (error) {
            console.error('Error fetching user borrowings:', error);
            return { success: false, message: 'Failed to fetch borrowings' };
        }
    },

    // Get active borrowings
    async getActive() {
        try {
            const result = await API.getActiveBorrowings();
            return result;
        } catch (error) {
            console.error('Error fetching active borrowings:', error);
            return { success: false, message: 'Failed to fetch active borrowings' };
        }
    },

    // Create a new borrowing
    async create(borrowingData) {
        const validation = this.validateBorrowing(borrowingData);
        if (!validation.valid) {
            return { success: false, message: validation.message };
        }

        try {
            const result = await API.createBorrowing(borrowingData);
            if (result.success) {
                toastr.success('Book borrowed successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error creating borrowing:', error);
            return { success: false, message: 'Failed to create borrowing' };
        }
    },

    // Return a book
    async returnBook(id) {
        try {
            const result = await API.returnBook(id);
            if (result.success) {
                toastr.success('Book returned successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error returning book:', error);
            return { success: false, message: 'Failed to return book' };
        }
    },

    // Validate borrowing data
    validateBorrowing(borrowingData) {
        if (!borrowingData.book_id || isNaN(borrowingData.book_id)) {
            return { valid: false, message: 'Valid book is required' };
        }
        if (!borrowingData.user_id || isNaN(borrowingData.user_id)) {
            return { valid: false, message: 'Valid user is required' };
        }
        if (!borrowingData.borrow_date) {
            return { valid: false, message: 'Borrow date is required' };
        }
        return { valid: true };
    },

    // Initialize borrowings view
    async initBorrowingsView() {
        if (!AuthService.isAuthenticated()) {
            window.location.hash = '#/login';
            return;
        }

        await this.loadBorrowings();
        this.setupBorrowingForm();
    },

    // Load and display borrowings
    async loadBorrowings() {
        const user = AuthService.getUser();
        const isAdmin = AuthService.isAdmin();
        
        let result;
        if (isAdmin) {
            result = await this.getAll();
        } else {
            result = await this.getByUser(user.id);
        }

        const borrowingsTableBody = document.getElementById('borrowingsTableBody');

        if (!borrowingsTableBody) return;

        if (result.success && result.data) {
            const borrowings = Array.isArray(result.data) ? result.data : [];
            if (borrowings.length === 0) {
                borrowingsTableBody.innerHTML = '<tr><td colspan="6" class="text-center">No borrowings found</td></tr>';
                return;
            }

            borrowingsTableBody.innerHTML = borrowings.map(borrowing => `
                <tr>
                    <td>${borrowing.id || ''}</td>
                    <td>${this.escapeHtml(borrowing.book_title || 'N/A')}</td>
                    <td>${this.escapeHtml(borrowing.user_name || 'N/A')}</td>
                    <td>${borrowing.borrow_date || 'N/A'}</td>
                    <td>${borrowing.return_date || '<span class="badge bg-warning">Active</span>'}</td>
                    <td>
                        ${!borrowing.return_date ? `<button class="btn btn-sm btn-success" onclick="BorrowingService.returnBorrowing(${borrowing.id})">Return</button>` : ''}
                    </td>
                </tr>
            `).join('');
        } else {
            borrowingsTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading borrowings</td></tr>';
            if (result.message) toastr.error(result.message);
        }
    },

    // Setup borrowing form
    setupBorrowingForm() {
        const form = document.getElementById('borrowingForm');
        if (!form) return;

        // Load books and users for dropdowns
        this.loadBooksAndUsers();

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const user = AuthService.getUser();
            const borrowingData = {
                book_id: parseInt(formData.get('book_id')),
                user_id: AuthService.isAdmin() ? parseInt(formData.get('user_id')) : user.id,
                borrow_date: formData.get('borrow_date') || new Date().toISOString().split('T')[0]
            };

            const result = await this.create(borrowingData);
            if (result.success) {
                form.reset();
                await this.loadBorrowings();
            }
        });
    },

    // Load books and users for dropdowns
    async loadBooksAndUsers() {
        try {
            const [booksResult, usersResult] = await Promise.all([
                API.getBooks(),
                AuthService.isAdmin() ? API.getUsers() : Promise.resolve({ success: false })
            ]);

            const bookSelect = document.getElementById('borrowingBook');
            const userSelect = document.getElementById('borrowingUser');

            if (bookSelect && booksResult.success && booksResult.data) {
                bookSelect.innerHTML = '<option value="">Select Book</option>' +
                    booksResult.data.map(book => 
                        `<option value="${book.id}">${this.escapeHtml(book.title)}</option>`
                    ).join('');
            }

            if (userSelect && usersResult.success && usersResult.data && AuthService.isAdmin()) {
                userSelect.innerHTML = '<option value="">Select User</option>' +
                    usersResult.data.map(user => 
                        `<option value="${user.id}">${this.escapeHtml(user.name)}</option>`
                    ).join('');
            } else if (userSelect && !AuthService.isAdmin()) {
                const user = AuthService.getUser();
                userSelect.innerHTML = `<option value="${user.id}">${this.escapeHtml(user.name)}</option>`;
                userSelect.disabled = true;
            }
        } catch (error) {
            console.error('Error loading books/users:', error);
        }
    },

    // Return borrowing
    async returnBorrowing(id) {
        const result = await this.returnBook(id);
        if (result.success) {
            await this.loadBorrowings();
        } else {
            toastr.error(result.message || 'Failed to return book');
        }
    },

    // Escape HTML to prevent XSS
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

